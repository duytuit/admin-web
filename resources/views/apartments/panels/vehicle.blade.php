<div class="panel panel-default">
    <div class="panel-heading">Phương tiện <a class="btn btn-success" title="Thêm phương tiện"
                                              data-toggle="modal" data-target="#add-vehicle"><i
                    class="fa fa-plus"></i>&nbsp;&nbsp;Thêm</a>
        <p
                class="display_mes_summit @if($data_error) error_mes @elseif($data_success) success_mes @endif">
            {{$data_vhc}}</p>
    </div>
    <div class="panel-body">
        <table class="table table-striped">
            <thead>
            <tr>
                <th colspan="" rowspan="" headers="">Stt</th>
                <th colspan="" rowspan="" headers="">Tên phương tiện</th>
                <th width="30">Loại phương tiện</th>
                <th width="90">Biển số</th>
                <th width="90">Mã Thẻ</th>
                <th width="30">Mức ưu tiên</th>
                <th width="90">Phí</th>
                <th width="130">Mô tả</th>
                <th width="50">Trạng thái</th>
                <th width="150">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($vehicles as $v)
                <tr>
                    
                    <td>{{$v->id}}</td>
                    
                    <td>{{$v->name}}</td>
                    <td>{{$v->bdcVehiclesCategory->name??''}}</td>
                    <td>{{$v->number}}</td>
                    <td>{{@$v->bdcVehicleCard->code}}</td>
                    <td>{{$v->priority_level}}</td>
                    <td>{{$v->price}}</td>
                    <td>{{$v->description}}</td>
                    <td>
                        <div class="onoffswitch">
                            <input type="checkbox"
                                   name="onoffswitch"
                                   class="onoffswitch-checkbox"
                                   data-id="{{ $v->id }}"
                                   id="myonoffswitch_{{ $v->id }}"
                                   data-url="{{ route('admin.vehicles.status') }}"
                                   value="{{$v->status}}" @if($v->status == true) checked @endif
                            >
                            <label class="onoffswitch-label" for="myonoffswitch_{{ $v->id }}">
                                <span class="onoffswitch-inner"></span>
                                <span class="onoffswitch-switch"></span>
                            </label>
                        </div>
                    </td>
                    <td colspan="" rowspan="" headers="">
                        <a href="{{ route('admin.vehicles.edit',['id'=> $v->id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                        <a href="{{ route('admin.vehicles.delete',['id'=> $v->id]) }}" class="btn btn-danger" title="xóa" onclick="return confirm('Bạn có chắc chắn xóa!');"><i class="fa fa-times"></i></a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
