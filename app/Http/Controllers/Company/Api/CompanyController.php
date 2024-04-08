<?php

namespace App\Http\Controllers\Company\Api;

use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Models\PublicUser\Users;
use App\Models\PremiumTime\PremiumTime;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\PublicUsers\PublicUsersRespository;
use App\Repositories\Building\CompanyRepository;
use App\Repositories\Building\CompanyStaffRepository;
use App\Repositories\Permissions\UserPermissionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;

class CompanyController extends Controller
{
    use ApiResponse;

    const DUPLICATE = 19999;
    const LOGIN_FAIL = 10000;
    private $_userRepo;
    private $_userProfileRepo;
    private $_companyRepo;
    private $_companyStaffRepo;
    private $_userPermissionRepo;

    /**
     * Constructor.
     */
    public function __construct(
        PublicUsersRespository $userRepo,
        PublicUsersProfileRespository $userProfileRepo,
        CompanyRepository $companyRepo,
        CompanyStaffRepository $companyStaffRepo,
        UserPermissionRepository $userPermissionRepo
    )
    {
        //$this->middleware('jwt.auth');
        $this->_userRepo = $userRepo;
        $this->_userProfileRepo = $userProfileRepo;
        $this->_companyRepo = $companyRepo;
        $this->_companyStaffRepo = $companyStaffRepo;
        $this->_userPermissionRepo = $userPermissionRepo;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email',
            'phone'         => 'required|numeric',
            'permissions'   => 'required',
            'time_active'   => 'required|date',

            'company_name'  => 'required',
            'company_code'  => 'required',

            'staff_name'    => 'required',
            'staff_code'    => 'required',
            'app_id'        => 'required',
            'building_id'   => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        $userInfo = \Auth::guard('public_user')->user()->infoWeb->where('bdc_building_id', $request->building_id)->first();
        if (!$userInfo) {
            return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
        }

        DB::beginTransaction();
        try{

            if( $this->_companyRepo->checkCompanyExist($request->input('company_code')) ) {
                DB::rollBack();
            return $this->responseError(['Công ty đã tồn tại'], 204);
            }

            $company = $this->_companyRepo->create([
                'name'     => $request->input('company_name'),
                'code'     => $request->input('company_code'),
                'type'     => 1,
                'admin_id' => @$request->input('admin_id')
            ]);

            $user = $this->_userRepo->checkExit($request->input('email'));
            if( !$user ) {
                $user = $this->_userRepo->create([
                    'email'    => $request->input('email'),
                    'password' => Hash::make('bdc123456'),
                ]);
            }

            $this->_companyRepo->update(['admin_id' => $user->id], $company->id);
            $staff = $this->_companyStaffRepo->create([
                'pub_user_id'    => $user->id,
                'type'           => true,
                'bdc_company_id' => $company->id,
                'name'           => $request->input("staff_name"),
                'email'          => $request->input("email"),
                'code'           => $request->input("staff_code"),
                'phone'          => $request->input("phone"),
                // 'address' => @$request->input("staff_address"),
                // 'image' => @$request->input("staff_image"),
                // 'active' => @$request->input("staff_active")
            ]);

            $this->_userPermissionRepo->updatePermissionApi($user->id, $request->input('permissions'));

            PremiumTime::create([
                'premium_time' => $request->input('time_active'),
                'created_by'   => \Auth::guard('public_user')->user()->id,
            ]);

            // $this->_userProfileRepo->create([
            //     'pub_user_id' => @$staff->pub_user_id,
            //     'display_name' => @$staff->name,
            //     'email' => @$staff->user->email,
            //     'staff_code' => @$staff->code,
            //     'phone' => @$staff->phone,
            //     'bdc_building_id' => $request->input('building_id'),
            //     'type' => Users::USER_WEB,
            //     'status' => true,
            //     'app_id' => $request->input('app_id')
            // ]);

            DB::commit();
            return $this->responseSuccess(['Thêm mới công ty thành công'], 'Success', 200);

        } catch(\Exception $e) {
            DB::rollBack();
            return $this->responseError(['Thêm mới công ty thất bại'], 204);
        }

    }

}
