<?php

namespace App\Models;

use App\Models\Model;

class UrlAlias extends Model
{
    /**
     * Lưu bản ghi
     *
     * @param string $uri
     * @param string $slug
     * @param string $suffix
     * @return mixed
     */
    public static function saveAlias(string $uri, string $alias, string $suffix = '.html')
    {
        $slug  = str_replace($suffix, '', $alias);
        $slug  = str_slug($slug);
        $alias = $slug . $suffix;

        $obj = self::where('uri', $uri)->first();
        if (!$obj) {
            $obj = new UrlAlias();
        }

        // Tạo alias mới nếu cần
        if ($obj->alias && ($obj->alias != $alias)) {
            $items = self::where('alias', 'LIKE', $slug . '%')->get();
            if ($items) {
                $list = $items->pluck('alias');
                $i    = 1;
                while ($list->contains($alias)) {
                    $alias = $slug . '-' . $i . $suffix;
                    $i++;
                }
            }
        }

        $obj->uri   = $uri;
        $obj->alias = $alias;
        $obj->save();

        return $obj;
    }

    /**
     * Lấy bản ghi theo $slug
     *
     * @param string $slug
     * @return mixed
     */
    public static function getBySlug(string $slug)
    {
        return self::where('slug', $slug)->first();
    }

    /**
     * Lấy bản ghi theo $uri
     *
     * @param string $uri
     * @return mixed
     */
    public static function getByUri(string $uri)
    {
        return self::where('uri', $uri)->first();
    }
}
