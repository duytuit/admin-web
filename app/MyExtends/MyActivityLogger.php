<?php

namespace App\MyExtends;

use Spatie\Activitylog\ActivityLogger;

class MyActivityLogger extends ActivityLogger
{

    public function withField($param)
    {
        foreach($param as $field => $value) {
            $this->getActivity()->{$field} = $value;
        }

        return $this;
    }

}
