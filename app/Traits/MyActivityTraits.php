<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use App\MyExtends\MyActivityLogger;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\PublicUser\UserInfo;

trait MyActivityTraits
{

    use LogsActivity {
        bootLogsActivity as blockLogsActivity;
    }

    protected static function bootLogsActivity()
    {
        static::eventsToBeRecorded()->each(function ($eventName) {
            return static::$eventName(function (Model $model) use ($eventName) {
                if (!$model->shouldLogEvent($eventName)) {
                    return;
                }

                $description = $model->getDescriptionForEvent($eventName);

                $logName = $model->getLogNameToUse($eventName);

                if ($description == '') {
                    return;
                }

                $attrs = $model->attributeValuesToBeLogged($eventName);

                if ($model->isLogEmpty($attrs) && !$model->shouldSubmitEmptyLogs()) {
                    return;
                }

                //Xử lý thêm field cho logs
                $log_type = str_singular($model->getTable());

                $param = [
                    'log_type' => $log_type,
                    'bdc_building_id' => @$model->info->bdc_building_id ?? $model->bdc_building_id ?? @$model->bdcApartment->building_id,
                ];
                //End xử lý thêm filed
                $detai_history = $model->toArray();
                if($log_type == 'bdc_customer'){

                    $user_profile = UserInfo::withTrashed()->find($detai_history['pub_user_profile_id'])->toArray();

                    array_push($detai_history,$user_profile);
                }
                $logger = app(MyActivityLogger::class)
                    ->useLog($logName)
                    ->performedOn($model)
                    ->withProperties($detai_history)
                    ->withField($param);

                if (method_exists($model, 'tapActivity')) {
                    $logger->tap([$model, 'tapActivity'], $eventName);
                }

                $logger->log($description);
            });
        });
    }

}
