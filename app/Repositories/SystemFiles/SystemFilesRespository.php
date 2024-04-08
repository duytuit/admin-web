<?php

namespace App\Repositories\SystemFiles;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use File;

class SystemFilesRespository extends Repository {




    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\SystemFiles\SystemFiles::class;
    }

    public function checkFile($request,$inputname='file_doc')
    {
        $file = $request->$inputname;
        $result=[
            'data'=>'',
            'error'=>'',
            'status'=>'',
        ];
        $expensions= ['csv','doc','docx','djvu','odp','ods','odt','pps','ppsx','ppt','pptx','pdf','ps','eps','rtf','txt','wks','wps','xls','xlsx','xps','tif','tiff','gif','jpeg','jpg','jif','jfif','jp2','jpx','j2k','j2c','png'];
        if(!in_array($file->getClientOriginalExtension(),$expensions)){
            $result=['error'=>'Chỉ hỗ trợ upload file documents','status'=>'NOT_OK'];

        }
        if($file->getSize() > 2000000) {
            $result=['error'=>'Kích thước file không được lớn hơn 2MB','status'=>'NOT_OK'];
        }
        if($result['error']){
            return $result;
        }

        $forder = date('d-m-Y');
        $directory = 'media/files';
        if(!is_dir($directory)){
            mkdir($directory);
            if(!is_dir($directory.'/'.$forder)) {
                mkdir($directory.'/'.$forder);
            }
        }

        $ext= $file->getClientOriginalExtension();
        $name = str_replace('.'.$ext,'',$file->getClientOriginalName());

        $mainFilename = $name.'-'.date('d-m-Y-h-i-s');

        $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
        $result=['data'=>['name'=>$mainFilename,'type'=>$ext,'url'=>$directory.'/'.$forder.'/'.$mainFilename.".".$ext],'error'=>'','status'=>'OK'];
        return $result;
    }

    public function checkModulFile($modul,$id,$per_page)
    {
        return $this->model->where('model_type','=',$modul)->where('model_id','=',$id)->paginate($per_page);
    }

    public function countFileApt($modul,$id,$per_page)
    {
        return $this->model->where('model_type','=',$modul)->where('model_id','=',$id)->count();
    }

    public function getModulFile($modul,$id)
    {
        return $this->model->where(['model_type'=>$modul,'model_id'=>$id])->first();
    }

    public function deleteModulFile($modul,$id)
    {
        $files = $this->getModulFile($modul, $id);
        if ($files->count() > 0) {
            foreach ($files as $file)
            {
                if (File::exists($file->url)) {
                    unlink($file->url);
                }
                $file->delete();
            }
        }
    }

    public function getOne($colums = 'id', $id)
    {
        $row = $this->model->where($colums, $id)->first();
        return $row;
    }

    public function searchFiles($request = '', $where = [], $perpage = 20)
    {

        $default = [
            'select' => '*',
            'where' => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);
        $model = $this->model->select($options['select'])->distinct('vehicle_category_id');
        $model = $model->where('model_type', '=', 'apartment');
        if (!empty($request->keyword)) {
            $model = $model->where('name', 'like', '%' . $request->keyword . '%');
        }
        if (!empty($request->apartment)) {
            $model = $model->where('model_id', '=', $request->apartment);
        }

        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }

    public function checkMultiFile($file,$type,$building_id,$user)
    {
        $result=[
            'data'=>'',
            'error'=>'',
            'status'=>'',
        ];
        $expensions= ['csv','doc','docx','djvu','odp','ods','odt','pps','ppsx','ppt','pptx','pdf','ps','eps','rtf','txt','wks','wps','xls','xlsx','xps','tif','tiff','gif','jpeg','jpg','jif','jfif','jp2','jpx','j2k','j2c','png'];
        if(!in_array($file->getClientOriginalExtension(),$expensions)){
            $result=['error'=>'Chỉ hỗ trợ upload file documents','status'=>'NOT_OK'];

        }
        if($file->getSize() > 2000000) {
            $result=['error'=>'Kích thước file không được lớn hơn 2MB','status'=>'NOT_OK'];
        }
        if($result['error']){
            return $result;
        }

        $ext= $file->getClientOriginalExtension();
        $name = str_replace('.'.$ext,'',$file->getClientOriginalName());

        switch ($type) {
            case 'avatar': {
                $directory = 'media/users';
                $forder = str_slug($user->id);
                if(!is_dir($directory)){
                    mkdir($directory);
                    if(!is_dir($directory.'/'.$forder)) {
                        mkdir($directory.'/'.$forder);
                    }
                }
                $mainFilename = str_slug('avatar');
                $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                break;
            }
            case 'fb_form': {
                $directory = 'media/form_template';
                $forder = date('d-m-Y');
                if(!is_dir($directory)){
                    mkdir($directory);
                    if(!is_dir($directory.'/'.$forder)) {
                        mkdir($directory.'/'.$forder);
                    }
                }
                $mainFilename =  str_slug($name).'-'.date('d-m-Y-h-i-s');
                $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                break;
            }
            case 'banks': {
                $directory = 'media/banks';
                $forder = date('d-m-Y');
                if(!is_dir($directory)){
                    mkdir($directory);
                    if(!is_dir($directory.'/'.$forder)) {
                        mkdir($directory.'/'.$forder);
                    }
                }
                $mainFilename =  str_slug($name).'-'.date('d-m-Y-h-i-s');
                $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                break;
            }
            default : {
                $forder = date('d-m-Y');
                $directory = 'media/files';
                if(!is_dir($directory)){
                    mkdir($directory);
                    if(!is_dir($directory.'/'.$forder)) {
                        mkdir($directory.'/'.$forder);
                    }
                }
                $mainFilename = str_slug($name).'-'.date('d-m-Y-h-i-s');
                $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                break;
            }
        }

        $result=['data'=>['name'=>$mainFilename,'type'=>$ext,'url'=>$directory.'/'.$forder.'/'.$mainFilename.".".$ext],'error'=>'','status'=>'OK'];
        return $result;
    }
}
