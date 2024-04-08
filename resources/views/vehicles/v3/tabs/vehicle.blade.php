<div class="panel panel-default" style="border-radius: 30px">
    <div class="panel-body">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-customer" action="" method="get" style="display: inline-block;">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <div id="search-advance" class="search-advance">
                                <div>
                                    <div class="form-group space-5" style="/*width: calc(100% - 55px);*/float: left;">
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="keyword" placeholder="Nhập từ khóa tìm kiếm" value="{{!empty($data_search['keyword'])?$data_search['keyword']:''}}">
                                        </div>
                                        <div class="col-sm-2" style="padding-left:0">
                                            <select name="type_dir" id="type_dir" class="form-control" style="width: 100%;">
                                                <option value="">Phân loại</option>
                                                <option value="IN" selected>Vào</option>
                                                <option value="OUT" selected>Ra</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="apartment" id="ip-apartment"  class="form-control">
                                                <option value="">Thời gian</option>
                                               <?php $apartment = isset($get_apartment) ?$get_apartment:'' ?>
                                                @if($apartment)
                                                    <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <select name="type_vehi" id="ip-cate"  class="form-control">
                                                <option value="">Chọn Loại phương tiện</option>
                                                <option value="Motor" selected>Xe Máy</option>
                                                <option value="Car" selected>Ô Tô</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-1">
                                            <div class="input-group-btn">
                                                <button type="submit" title="Tìm kiếm" id="tim_kiem" class="btn btn-info" form="form-search-customer"><i class="fa fa-search"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="input-group-btn">
                                                <button title="Xuất Excel" id="exportexcel" class="btn btn-info" form="form-search-customer"> Export Excel </button>
                                            </div>
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->
                @if( in_array('admin.v2.vehicles.action',@$user_access_router))
                    <form id="form-vehicles-list" action="{{ route('admin.v2.vehicles.action') }}" method="post">
                        @csrf
                        <input type="hidden" name="method" value="" />
                        <input type="hidden" name="status" value="" />
                        <div class="table-responsive" style="max-height: 1000px;">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                <tr>
                                    <th width="1%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th width="1%">Stt</th>
                                    <th width="130">Phân Loại</th>
                                    <th width="130">Thời gian</th>
                                    <th width="30">Hình ảnh biển số</th>
                                    <th width="30">Hình ảnh toàn cảnh</th>
                                    <th width="90">Loại xe</th>
                                    <th width="90">Biển kiểm soát</th>
                                    <th width="30">Ghi chú</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($Vehicle_event as $v)
                                    <tr >
                                        <td><input type="checkbox" name="ids[]" value="{{$v->id}}" class="iCheck checkSingle" /></td>
                                        <td>
                                            <a target="_blank" href="/admin/activity-log/log-action?row_id={{$v->id}}"> {{ $v->id }}</a>
                                        </td>
                                        <td>{{$v->LaneDirection??''}}</td>
                                        <td>{{($v->EventDateTime)}}</td>
                                        <td> <img onclick="window.location.href='{{$v->VehicleImagePath}}'" style="width: 200px" src="{{$v->VehicleImagePath}}" alt="bien_so"> </td>
                                        <td> <img onclick="window.location.href='{{$v->OwnerImagePath}}'" style="width: 200px" src="{{$v->OwnerImagePath}}" alt="bien_so"> </td>
                                        <td>{{$v->VehicleType}}</td>
                                        <td>{{$v->PlateNumber}}</td>
                                        <td>{{$v->MonthlyTicketDescription}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row mbm">
                            <div class="col-sm-3">
                                <span class="record-total">Hiển thị: 200 kết quả</span>
                            </div>
                            <div class="col-sm-6 text-center">
                                <div class="pagination-panel">
                                </div>
                            </div>
                            <div class="col-sm-3 text-right">
                                <span class="form-inline">
                                    Hiển thị
                                    <select name="per_page" class="form-control" data-target="#form-vehicles-list">
                                        @php $list = [10, 20, 50, 100, 200]; @endphp
                                        @foreach ($list as $num)
                                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
