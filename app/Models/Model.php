<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\Schema;

class Model extends BaseModel
{
    /**
     * Lấy danh sách các trường
     *
     * @return void
     */
    public function getTableColumns()
    {
        return Schema::getColumnListing(self::getTable());
    }

    /**
     * Lấy bản ghi theo điều kiện
     *
     * @param array $where
     * @return Collection
     */
    public static function findBy(array $where = [])
    {
        return self::where($where)->get();
    }

    /**
     * Lấy bản ghi theo ID hoặc tạo mới object
     *
     * @param array $where
     * @return Collection
     */
    public static function findOrNew($id)
    {
        $object = self::find($id);

        if ($object === null) {
            $class  = get_called_class();
            $object = new $class;
        }

        return $object;
    }

    /**
     * Tìm các bản ghi theo điều kiện
     * @param array $options
     * @return Collection|static[]
     */
    public static function searchBy(array $options = [])
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = self::select($select);

        if ($where) {
            $model = $model->where($where);
        }

        return $model->orderByRaw($order_by)->paginate($per_page);
    }

    public static function searchByHas(array $options = [],$repo,$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = self::select($select);

        if ($where) {
            $model = $model->where($where);
        }
        $model = $model->whereHas($repo, function ($query) use ($building_id) {
            $query->where('bdc_building_id', '=', $building_id);
        });
        return $model->orderByRaw($order_by)->paginate($per_page);
    }
    /**
     * Xóa bản ghi theo điều kiện
     *
     * @param array $where
     * @param bool $force
     * @return void
     */
    public static function deleteBy($where, $force = false)
    {
        if ($force) {
            return self::where($where)->get()->forceDelete();
        } else {
            return self::where($where)->get()->delete();
        }
    }

    /**
     * Tìm theo ID
     * @param [type] $cb_id : id phụ
     * @return void
     */
    public static function findById($cb_id)
    {
        return self::where('cb_id', $cb_id)->first();
    }
    public static function convert_vi_to_en($str) {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
        $str = preg_replace("/(Đ)/", "D", $str);
        //$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
        return $str;
    }
}
