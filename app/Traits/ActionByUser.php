<?php

namespace App\Traits;

use App\Helpers\dBug;
use App\Util\Debug\Log;
use App\Util\Debug\LogAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class ModelEventLogger
 * @package App\Traits
 *
 *  Automatically Log Add, Update, Delete events of Model.
 */
trait ActionByUser {

    protected static function boot()
    {
        $event = [
            'retrieved',
            'creating',
            'created',
            'updating',
            'updated',
            'saving',
            'saved',
            'deleting',
            'deleted',
            'restoring',
            'restored'
        ];
        parent::boot();

        static::updated(function (Model $model) {
            $query= DB::getQueryLog();
            $lastQuery= end($query);
            // dBug::trackingPhpErrorV2($model);
            LogAction::logDatabaseAction($model->table,'update',@$model->id,json_encode($model->original),json_encode($model->attributes), json_encode($lastQuery));
        });
        static::deleted(function (Model $model) {
            $query= DB::getQueryLog();
            $lastQuery= end($query);
            LogAction::logDatabaseAction($model->table,'delete',@$model->id,'',json_encode($model->attributes), json_encode($lastQuery));
        });
        static::created(function (Model $model) {
            $query= DB::getQueryLog();
            $lastQuery= end($query);
            LogAction::logDatabaseAction($model->table,'insert',@$model->id,'',json_encode($model->attributes), json_encode($lastQuery));
        });
        
        // static::restored(function (Model $model) {
        //     $query= DB::getQueryLog();
        //     $lastQuery= end($query);
        //     if(isset($lastQuery['query']) && isset($lastQuery['bindings']))$lastQuery= self::getFinalSql($lastQuery['query'],$lastQuery['bindings']);
        //     LogAction::logDatabaseAction($model->table,'restore',$model->id,'',json_encode($model->attributes), json_encode($lastQuery));
        // });

    }
    public static function getFinalSql($sql_str,$bindings)
    {
        $wrapped_str = str_replace('?', "'?'", $sql_str);
        return str_replace_array('?', $bindings, $wrapped_str);
    }

} 