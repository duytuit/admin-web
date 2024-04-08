<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\NbRecords;
use App\Models\NbWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NbWalletController extends Controller
{

    /**
     * @var mixed
     */
    protected $_user;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new NbWallet();
        $this->_user = $this->getApiUser();
    }

    /**
     * Lấy danh sách tài khoản
     *
     * @param Request $request
     * - (string) user_id: User ID
     * @return \Illuminate\Http\JsonResponse $nbwallet
     */
    public function getAll(Request $request)
    {
        $per_page = 10;
        $columns = $this->model->getTableColumns();
        $order_by = $this->_sort($request, $columns);
        $items = $this->model
            ->where('user_id', $this->_user->id)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        $allItems = [];
        foreach ($items as $item) {
            $allItems[] = $this->createStandardData($item);
        }

        return $this->responseSuccess($allItems);
    }

    /**
     * Lưu bản ghi vào db
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        //validate
        $validator = $this->validateData($request);

        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->validateFail($error->first(), $error->toArray());
        }

        $id = (int)$request->id;
        $columns = $this->model->getTableColumns();
        $input = $request->only($columns);

        if ($id) {
            $owner = $this->isOwner($id);
            if ($owner['owner'] === false) {
                return $this->responseError($owner['message'], $owner['error_code']);
            }
        }

        $input['user_id'] = $this->_user->id;

        $item = $this->model::findOrNew($id);
        $item->fill($input)->save();

        $result = $this->createStandardData($item);

        return $this->responseSuccess($result);
    }

    /**
     * Chi tiết tài khoản
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $id = (int)$request->id;
        $owner = $this->isOwner($id);
        if ($owner['owner'] === false) {
            return $this->responseError($owner['message'], $owner['error_code']);
        }

        $item = $owner['model'];

        $result = $this->createStandardData($item, true);
        return $this->responseSuccess($result);
    }

    public function destroy(Request $request)
    {
        $id = (int)$request->id;

        $owner = $this->isOwner($id);
        if ($owner['owner'] === false) {
            return $this->responseError($owner['message'], $owner['error_code']);
        }

        $item = $owner['model'];

        if ($this->model->destroy($id)) {
            NbRecords::where('wallet_id', $item->id)->delete();
            return $this->responseSuccess([], 'Delete success');
        }

        return $this->responseError('Something went wrong !!!', Config::get('code.something_went_wrong'));
    }

    protected function validateData($request)
    {
        function rulesByMethod($request, array $rules = [])
        {
            if ($request->isMethod('PUT')) {
                $result = [];
                $input = $request->all();

                foreach ($rules as $field => $rule) {
                    if (array_key_exists($field, $input)) {
                        $result[$field] = $rule;
                    }
                }

                return $result;
            }

            return $rules;
        }

        $rules = [
            'wallet_name' => 'required',
            'currency_code' => 'required',
            'wallet_balance' => 'required|numeric'
        ];

        $messages = [];
        $attributes = [
            'wallet_name' => 'tên tài khoản',
            'currency_code' => 'đơn vị tiền tệ',
            'wallet_balance' => 'số Tiền'
        ];

        $validator = Validator::make($request->all(), rulesByMethod($request, $rules), $messages, $attributes);

        return $validator;
    }

    protected function createStandardData($item, $load_record = false)
    {
        $wallet = [
            'id' => $item->id,
            'user_id' => $item->user_id,
            'wallet_name' => $item->wallet_name,
            'currency_code' => $item->currency_code,
            'wallet_description' => $item->wallet_description,
            'wallet_balance' => $item->wallet_balance,
//            'save_to_report' => $item->save_to_report,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];

        if (!$load_record) return $wallet;

        $records = [];
        if (!$item->records->isEmpty()) {
            foreach ($item->records as $record) {
                $records[] = [
                    'id' => $record->id,
                    'wallet_id' => $record->wallet_id,
                    'category' => $record->category,
                    'record_date' => strtotime($record->record_date),
                    'record_description' => $record->record_description,
                    'record_type' => $record->record_type,
                    'amount' => $record->amount,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ];
            }
        }
        $wallet['records'] = $records;

        return $wallet;
    }
}
