<?php

namespace App\Repositories\WorkDiary;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use App\Models\WorkDiary\WorkDiary;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;

class WorkDiaryRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */

    const STATUS = [
        WorkDiary::UN_PROCESS => 'Chưa thực hiện',
        WorkDiary::PROCESSING => 'Đang thực hiện',
        WorkDiary::PROCESSED => 'Đã thực hiện',
        WorkDiary::RE_WORK => 'Cần làm lại',
        WorkDiary::CHECKED => 'Đã kiểm tra',
        WorkDiary::DONE => 'Đã duyệt',
    ];

    const COLOR = [
        WorkDiary::UN_PROCESS => '#fe9c47',
        WorkDiary::PROCESSING => '#26c6da',
        WorkDiary::PROCESSED => '#7460ee',
        WorkDiary::RE_WORK => '#69AA46',
        WorkDiary::CHECKED => '#1e88e5',
        WorkDiary::DONE => '#fc5070',
    ];

    const INFO = [
        WorkDiary::UN_PROCESS => [
            'text' => 'Chưa thực hiện',
            'color' => '#fe9c47',
        ],
        WorkDiary::PROCESSING => [
            'text' => 'Đang thực hiện',
            'color' => '#26c6da',
        ],
        WorkDiary::PROCESSED => [
            'text' => 'Đã thực hiện',
            'color' => '#7460ee',
        ],
        WorkDiary::RE_WORK => [
            'text' => 'Cần làm lại',
            'color' => '#69AA46',
        ],
        WorkDiary::CHECKED => [
            'text' => 'Đã kiểm tra',
            'color' => '#1e88e5',
        ],
        WorkDiary::DONE => [
            'text' => 'Đã duyệt',
            'color' => '#fc5070',
        ],
    ];

    function model()
    {
        return WorkDiary::class;
    }

    public function myPaginate($keyword, $per_page, $active_building, $user_id, $check)
    {
        // created_at and asign_to
        if( !$check ) {
            return $this->model
            ->with('people_hand', 'department', 'pub_profile')
            ->where([
                ['bdc_building_id', $active_building],
                ['assign_to', $user_id]
            ])
            ->orWhere([
                ['bdc_building_id', $active_building],
                ['created_by', $user_id]
            ])
            ->filter($keyword)
            ->orderBy('updated_at', 'DESC')
            ->paginate($per_page);
        }

        // super admin and supervisor
        return $this->model
            ->with('people_hand', 'department', 'pub_profile')
            ->where('bdc_building_id', $active_building)
            ->filter($keyword)
            ->orderBy('updated_at', 'DESC')
            ->paginate($per_page);
    }

    public function getWorkDiary($page, $per_page, $filter, $userInfo, $routePermissions)
    {
        $works = $this->model
            ->with('people_hand', 'department', 'pub_profile.pubusers.departmentUser.department')
            ->select('id', 'title', 'status', 'bdc_department_id', 'assign_to', 'end_at', 'created_by')
            ->where('bdc_building_id', $userInfo->bdc_building_id)
            ->where('created_by', $userInfo->id)
            ->orWhere('updated_by', $userInfo->id)
            ->orWhere('assign_to', $userInfo->id)
            ->filterApp($filter)
            ->orderBy('updated_at', 'desc')
            ->paginate($per_page);
        return $this->showArrayWorkDiary($works, $page, $per_page, $routePermissions);
    }

    public function findWorkDiary($id)
    {
        return $this->model->with('people_hand', 'department', 'pub_profile.pubusers.departmentUser.department')->find($id);
    }

    public function showArrayWorkDiary($collection, $page, $per_page, $routePermissions)
    {
        $data = [];
        $data['data'] = [];
        foreach ($collection as $item) {
            $work = $this->formatData($item);
            $work['editable'] = in_array('admin.work-diary.update', $routePermissions);
            $work['removable'] = in_array('admin.work-diary.delete', $routePermissions);
            $data['data'][] = $work;
        }
        $data['info']['status'] = self::INFO;
        $data['info']['pagination'] = [
            'total' => $collection->total(),
            'last_page' => $collection->lastPage(),
            'per_page' => (int)$per_page,
            'current_page' => (int)$page
        ];
        return $data;
    }

    public function deleteMulti($ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function findWork($work, $user_profile, $permission)
    {
        $data['work'] = [];
        $data['work'] = $this->formatData($work);
        $data['work']['description'] = $work->description;
        $reviewNote = json_decode($work->review_note, true);
        if (!empty($reviewNote)) {
            foreach ($reviewNote as $key => $val) {
                $data['work']['review_note'][$key]['user'] = $user_profile->find($val['user_id'])->display_name;
                $data['work']['review_note'][$key]['previous_status'] = self::STATUS[$val['previous_status']];
                $data['work']['review_note'][$key]['current_status'] = self::STATUS[$val['current_status']];
                $data['work']['review_note'][$key]['note'] = $val['note'];
                $data['work']['review_note'][$key]['date'] = $val['date'];
            };
        }
        $data['info']['status'] = self::INFO;
        $data['info']['buttons'] = $this->checkStatus($work, $permission)??[];
        return $data;
    }

    private function formatData($item)
    {
        $work = [
            'id' => $item->id,
            'title' => $item->title,
            'status' => $item->status,
            'created_by' => [
                'id' => @$item->created_by,
                'name' =>  @$item->pub_profile->display_name,
                'department' => @$item->pub_profile->pubusers->departmentUser->department->name
            ],
            'end_at' => date('d/m/Y', strtotime($item->end_at))
        ];

        if ($item->assign_to == 0)
        {
            $work['assign_to'] = [
                'department' => @$item->department->name
            ];
        } else {
            $work['assign_to'] = [
                'id' => $item->assign_to,
                'name' => @$item->people_hand->display_name,
                'department' => @$item->department->name
            ];
        }

        return $work;
    }

    public function checkStatus($work, $permission)
    {
        $status = $work->status;
        switch ($status) {
            case 0: {
                $nextReport = [];
                $nextReport[0] = self::INFO[$work->status + 1];
                $nextReport[0]['value'] = $work->status + 1;
                $nextReport[1] = self::INFO[$work->status + 2];
                $nextReport[1]['value'] = $work->status + 2;
                return $nextReport;
                break;
            }
            case 1: {
                $nextReport = [];
                $nextReport[0] = self::INFO[$work->status + 1];
                $nextReport[0]['value'] = $work->status + 1;
                return $nextReport;
                break;
            }
            case 2: {
                $nextReport = [];
                $nextReport[0] = self::INFO[$work->status + 1];
                $nextReport[0]['value'] = $work->status + 1;
                $nextReport[1] = self::INFO[$work->status + 2];
                $nextReport[1]['value'] = $work->status + 2;
                return $nextReport;
                break;
            }
            case 3: {
                $nextReport = [];
                $nextReport[0] = self::INFO[WorkDiary::PROCESSING];
                $nextReport[0]['value'] = WorkDiary::PROCESSING;
                $nextReport[1] = self::INFO[WorkDiary::PROCESSED];
                $nextReport[1]['value'] =  WorkDiary::PROCESSING;
                return $nextReport;
                break;
            }
            case 4: {
                $nextReport = [];
                $nextReport[0] = self::INFO[WorkDiary::DONE];
                $nextReport[0]['value'] = WorkDiary::DONE;
                return $nextReport;
                break;
            }
            case 5: {
                break;
            }
        }
    }
    
    public function findByBuildingId($id)
    {
        return $this->model->where('bdc_building_id', $id)->get();
    }

    public function getWorkMenu($building_id)
    {
        $data = $this->model
            ->with('people_hand', 'department', 'pub_profile', 'update_by')
            ->where('bdc_building_id', $building_id)
            ->orderBy('updated_at', 'DESC')
            ->limit(5)
            ->get();
        foreach ($data as $task)
        {
            $task->status = self::STATUS[$task->status];
        }

        return $data;
    }

    public function findByTime($building_id, $from_date, $to_date)
    {

        return $this->model->where('bdc_building_id', $building_id)->whereBetween('updated_at', [$from_date, $to_date])->get();
    }
    public function countItem($building_id)
    {
        return $this->model->where('bdc_building_id', $building_id)->count();
    }
}