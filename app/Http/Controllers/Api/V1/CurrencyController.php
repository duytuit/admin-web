<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{


    public function __construct()
    {
        $this->model = new Currency();
        $this->resource = new CurrencyResource(null);
    }

    /**
     * Lấy danh sách tiền tệ
     *
     * @param Request $request
     */
    public function getAll(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $currency = $this->model
            ->orderByRaw('country ASC')
            ->paginate($per_page);

        return $this->resource->many($currency);
    }

    /**
     * Tìm kiếm giá trị theo country or code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function search(Request $request)
    {
        $value = $request->input('value');

        if($value) {
            $currency = $this->model->where('country', 'LIKE', '%'. $value . '%')
                ->orWhere('code', 'LIKE','%' . $value . '%')->paginate(150);
        }else{
            $currency = $this->model->select()->orderByRaw('country ASC')->paginate(150);
        }
        if(!$currency->count()) {
            $json = [
                'errors' => [
                    [
                        'code' => 11001,
                        'title' => 'Record not found',
                    ],
                ],
            ];

            return response()->json($json, 401);
        }
        return $this->resource->many($currency);
    }
}