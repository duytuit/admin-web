<div class="box box-primary">
    <div class="box-body">
        <form action="" method="get" id="form-search-apartment-group">
            <div class="row">
                <div class="col-sm-2">
                    <a class="btn btn-success form-control" data-toggle="modal" data-target="#add-apartment-group"> <i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới </a>
                </div>
                <div class="col-sm-2">
                    <input type="text" class="form-control" name="name_group" placeholder="Nhập keyword" value="{{ !empty($data_search['name_group']) ? $data_search['name_group'] : '' }}">
                </div>
                <div class="col-sm-1">
                    <button type="submit" title="Tìm kiếm" class="btn btn-info" form="form-search-apartment-group"><i class="fa fa-search"></i></button>
                </div>
                <div class="col-sm-2">
                    <a href="{{ route('admin.v2.apartments.export_apartment_group',Request::all()) }}" class="btn btn-warning form-control">
                        <i class="fa fa-download"></i>&nbsp;&nbsp;
                        Export Exel
                    </a>
                </div>
            </div>
        </form>
       
        <div class="clearfix"></div>
        <br>
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="bg-primary">
                    <tr>
                        <th>STT</th>
                        <th>Tên nhóm</th>
                        <th>Ghi chú</th>
                        <th>Căn hộ</th>
                        <th>Tác vụ</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($apartment_groups as $key=>$value)
                    <tr class="apartment-group-row">
                        <td class="group_id" data-id="{{$value->id}}">{{$key+1}}</td>
                        <td class="group_name" >{{$value->name}}</td>
                        <td class="group_description" >{{$value->description}}</td>
                        <td>
                            @foreach($value->apartments as $name_apr)
                                {{$name_apr->name . ','}}
                            @endforeach
                            <select name="" style="display: none" class="group_list_apartment" id="">
                                @foreach($value->apartments as $name_apr)
                                    <option value="{{$name_apr->id}}" selected="selected">{{$name_apr->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <a class="btn btn-success btn-edit-group"
                               title="sửa"
                            >
                                <i class="fa fa-edit"></i>
                            </a>
                            <a class="btn delete-apartment-group btn-danger"
                               data-id="{{$value->id}}"
                               title="xóa">
                                <i class="fa fa-times"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('apartments.v2.modals.add-apartment-group')
    @include('apartments.v2.modals.edit-apartmetn-group')
</div>
