<?php

namespace App\Traits;

trait RequestRules
{
    /**
     * get rules by method
     *
     * @param array $rules
     * @return void
     */
    public function rulesByMethod(array $rules = [])
    {
        if ($this->isMethod('PUT')) {
            $result = [];

            $input = $this->all();

            foreach ($rules as $field => $rule) {
                if (array_key_exists($field, $input)) {
                    $result[$field] = $rule;
                }
            }

            return $result;
        }
        if ($this->isMethod('POST')) {

            $result = [];

            $input = $this->all();

            foreach ($rules as $field => $rule) {
                if (array_key_exists($field, $input)) {
                    $result[$field] = $rule;
                }
            }

            return $result;
        }


        return $rules;
    }
}
