<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\NbRecords;
use App\Models\NbRecordType;
use App\Models\NbWallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class NbRecordsController extends Controller
{
    /**
     * @var mixed
     */
    protected $_user;

    /**
     * @var NbWallet
     */
    protected $_walletModel;

    /**
     * @var NbRecordType
     */
    protected $_recordType;

    public function __construct()
    {
        $this->model = new NbRecords();
        $this->_user = $this->getApiUser();
        $this->_walletModel = new NbWallet();
        $this->_recordType = new NbRecordType();
    }

    /**
     * lưu dữ liệu vào db
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
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

        $checkWallet = $this->isOwner($input['wallet_id'], $this->_walletModel);
        if ($checkWallet['owner'] === false) {
            return $this->responseError($checkWallet['message'], $checkWallet['error_code']);
        }

        $checkRecordType = $this->checkRecordType($input['record_type']);
        if (!$checkRecordType) {
            return $this->responseError('Vui lòng truyền loại bản ghi hợp lệ', Config::get('code.resource_not_found'));
        }

        if (!$request->record_date) {
            $input['record_date'] = Carbon::now()->format('Y-m-d H:i:s');
        }

        $record = $this->model::findOrNew($id);
        $save = $record->fill($input)->save();

        if ($save) {
            $this->updateBalance($input['amount'], $input['wallet_id'], $input['record_type']);
        }

        $result = $this->createStandardData($record);

        return $this->responseSuccess($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $id = (int)$request->id;

        try {
            $item = $this->model->findOrFail($id);
            $result = $this->createStandardData($item);

            return $this->responseSuccess($result);
        } catch (ModelNotFoundException $exception) {
            return $this->responseError('Record not found', 404);
        }
    }

    /**
     * Kiểm tra Loại bản ghi
     *
     * @param $typeId
     * @return void
     */
    protected function checkRecordType($typeId)
    {
        try {
            $this->_recordType->findOrFail($typeId);
            return true;
        } catch (ModelNotFoundException $e) {
            return false;
        }
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
            'wallet_id' => 'required',
            'category' => 'required',
            'record_type' => 'required|numeric',
            'amount' => 'required|numeric',
        ];
        $messages = [];
        $attributes = [
            'wallet_id' => 'ID tài khoản',
            'category' => 'hạng mục chi',
            'record_type' => 'loại bản ghi',
            'amount' => 'số Tiền'
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        return $validator;
    }

    /**
     * Cập nhật só dư cho ví khi có records được tạo mới hoặc thay đổi
     *
     * @param $amount
     * @param $wallet_id
     * @param $type
     * @param $recordId
     */
    protected function updateBalance($amount, $wallet_id, $type)
    {
        $wallet = NbWallet::find($wallet_id);
        $currentBalance = $wallet->wallet_balance;
        switch ($type) {
            case 1:
                $finalBalance = (float)$currentBalance + $amount;
                break;
            case 2:
                $finalBalance = (float)$currentBalance - $amount;
                break;
            default:
        }
        $wallet->wallet_balance = $finalBalance;
        return $wallet->save();
    }

    protected function createStandardData($record)
    {
        return [
            'id' => $record->id,
            'wallet_id' => $record->wallet_id,
            'category' => $record->category,
            'record_description' => $record->record_description,
            'record_type' => $record->record_type,
            'amount' => $record->amount,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
        ];
    }
}