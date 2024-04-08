<?php

namespace App\Models;

use App\Models\Model;

class Filter extends Model
{
    protected $guarded = [];

    public static function getAll()
    {
        $filters     = self::all()->groupBy('key');
        $new_filters = [];

        foreach ($filters as $key => $filter) {
            $value = [];
            $title = "";
            foreach ($filter as $item) {
                $value[] = [
                    'id'    => $item->id,
                    'value' => $item->value,
                ];

                $title = $item->title;
            }
            $new_filters[] = [
                'key'   => $key,
                'title' => $title,
                'value' => $value,
            ];
        }
        $new_filters = collect($new_filters);

        return $new_filters;
    }

    public static function updateNumber(array $ids, $type = 'customer')
    {
        $filters = self::whereIn('id', $ids);

        if ($type == 'product') {
            $filters = $filters->increment('number_product', 1);
        }

        if ($type == 'customer') {
            $filters = $filters->increment('number_customer', 1);
        }
    }
}
