<?php

namespace App\Http\Controllers\ServicePartners;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use Illuminate\Support\Facades\Cookie;
use App\Repositories\BusinessPartners\BusinessPartnerRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\BuildingHandbook\BuildingHandbookRepository;
use App\Repositories\ServicePartners\ServicePartnersRepository;
use Illuminate\Support\Facades\Auth;
use Validator;

class ServicePartnerController extends BuildingController
{
    private $businessPartnerRepository;
    private $servicePartnersRepository;
    private $publicUsersProfileRespository;
    private $buildingHandbookRepository;
    // private $auth_id;

    /**
     * Constructor.
     */
    public function __construct(
        Request $request,
        BusinessPartnerRepository $businessPartnerRepository,
        ServicePartnersRepository $servicePartnersRepository,
        PublicUsersProfileRespository $publicUsersProfileRespository,
        BuildingHandbookRepository $buildingHandbookRepository
    )
    {
        //$this->middleware('route_permision');
        $this->businessPartnerRepository = $businessPartnerRepository;
        $this->servicePartnersRepository = $servicePartnersRepository;
        $this->publicUsersProfileRespository = $publicUsersProfileRespository;
        $this->buildingHandbookRepository = $buildingHandbookRepository;
        parent::__construct($request);
    }
     public function update(Request $request, $id = 0)
    {
        $data = $request->except('_token');
        $this->servicePartnersRepository->update($data, $id);
        $responseData = [
            'success' => true,
            'message' => 'Cập nhật đăng ký dịch vụ thành công!',
            'href' => route('admin.service-partners.index')
        ];

        return response()->json($responseData);
    }
    public function exportExcel(Request $request)
    {
        return $this->servicePartnersRepository->filterExportExcel($this->building_active_id, $request->all());
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id = 0)
    {
        $data['id'] = $id;

        $service_partners = $this->servicePartnersRepository->find($id);
      
        return \response()->json($service_partners);

    }
    public function delete(Request $request)
    {
        $id = $request->input('ids');
        $this->servicePartnersRepository->delete(['id' => $id]);

        $request->session()->flash('success', 'Xóa đăng ký dịch vụ thành công!');
    }

    public function changeStatus(Request $request)
    {
        $data = $request->except('ids');
        if($request->status){
             $data['approved_id']= Auth::id();
             $data['confirm_date']= date('d-m-Y H:i:s');
        }else{
             $data['approved_id']= '';
             $data['confirm_date']='';
        }
        $this->servicePartnersRepository->find($request->ids[0])->update($data);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }
     public function ajaxGetSelectPartners(Request $request)
    {

        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->businessPartnerRepository->searchByAll(['where' => $where], $this->building_active_id));
        }
        return response()->json($this->businessPartnerRepository->searchByAll(['select' => ['id', 'name']], $this->building_active_id));
    }
    public function ajaxGetSelectBuildingHandbooks(Request $request)
    {
        if ($request->search) {
            $where[] = ['title', 'like', '%' . $request->search . '%'];
            return response()->json($this->buildingHandbookRepository->searchByAll(['where' => $where], $this->building_active_id));
        }
        return response()->json($this->buildingHandbookRepository->searchByAll(['select' => ['id', 'title']], $this->building_active_id));
    }
    public function action(Request $request)
    {
        if ($request->has('per_page_business_partners')) {
            $per_page = $request->input('per_page_business_partners', 10);
            Cookie::queue('per_page_business_partners', $per_page, 60 * 24 * 30);
            Cookie::queue('tab_business_partners', $request->tab);
        }

        if ($request->has('per_page_service_partners')) {
            $per_page_maintenance = $request->input('per_page_service_partners', 10);
            Cookie::queue('per_page_service_partners', $per_page_maintenance, 60 * 24 * 30);
            Cookie::queue('tab_service_partners', $request->tab);
        }

        return redirect()->back()->with('tab', $request->tab);
    }
}
