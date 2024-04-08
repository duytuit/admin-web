<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait ApiRequest
{
    /**
     * Get $select for SELECT
     *
     * @param  \Illuminate\Http\Request  $request
     * @param array $allowFields
     * @return array
     */
    protected function _select(Request $request, array $allowFields)
    {
        $fields = $request->input('fields', '*');

        $select = [];

        $fields = explode(',', $fields);
        $fields = array_map('trim', $fields);

        foreach ($fields as $field) {
            if (in_array($field, $allowFields)) {
                $select[] = $field;
            }
        }

        if (empty($select)) {
            $select = $allowFields;
        }

        return $select;
    }

    /**
     * Get condition filters for WHERE
     *
     * @param  \Illuminate\Http\Request  $request
     * @param array $columns
     * @return void
     */
    protected function _filters(Request $request, $columns = [])
    {
        $filters = $request->input('filters', []);

        $where = [];

        $operators = [
            'eq'   => '=',
            'lt'   => '<',
            'lte'  => '<=',
            'gt'   => '>',
            'gte'  => '>=',
            'in'   => 'in',
            'like' => 'like',
        ];

        foreach ($filters as $field => $filter) {
            if (in_array($field, $columns)) {
                $values = explode(':', $filter, 2);

                if (isset($values[1])) {
                    $key   = $values[0];
                    $value = $values[1];
                } else {
                    $key   = 'eq';
                    $value = $values[0];
                }

                if (array_key_exists($key, $operators)) {
                    if ($key == 'in') {
                        $value   = explode(',', $value);
                        $value   = array_map('trim', $value);
                        $where[] = [
                            function ($query) use ($field, $value) {
                                $query->whereIn($field, $value);
                            },
                        ];
                    } else {
                        $where[] = [$field, $operators[$key], $value];
                    }
                }
            }
        }

        return $where;
    }

    /**
     * Get $sort_by for ORDER BY
     *
     * @param  \Illuminate\Http\Request  $request
     * @param array $columns
     * @return string
     */
    protected function _sort(Request $request, $columns = [])
    {
        $sort_by = $request->input('sort_by', 'created_at.desc');

        $list = [];

        $sort_by = explode(',', $sort_by);
        $sort_by = array_map('trim', $sort_by);

        foreach ($sort_by as $item) {
            $values = explode('.', $item);

            $field     = $values[0];
            $direction = $values[1] ?? 'asc';
            $direction = strtoupper($direction);

            if (in_array($field, $columns) && in_array($direction, ['ASC', 'DESC'])) {
                $list[] = $field . ' ' . $direction;
            }
        }

        if (empty($list)) {
            $order_by = 'created_at DESC';
        } else {
            $order_by = implode(',', $list);
        }

        return $order_by;
    }
}
