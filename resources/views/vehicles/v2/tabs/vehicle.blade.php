<div class="panel panel-default">
    <div class="panel-body">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-customer" action="" method="post" style="display: inline-block;">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-sm-2">
                                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;margin-bottom: 15px;">Tác vụ&nbsp;<span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:" type="button" class="btn-action" data-toggle="modal" data-target="#form-vehicles-list" data-method="delete">
                                            <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                        </a>
                                    </li>
        
                                </ul>
                            <a class="btn btn-success" title="Thêm Phương tiện" data-toggle="modal" data-target="#add-vehicle"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                        </div>
                        <div class="col-sm-10 form-group">
                            <div id="search-advance" class="search-advance">
                                <div>
                                    <div class="form-group space-5" style="/*width: calc(100% - 55px);*/float: left;">
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="keyword" placeholder="Nhập từ khóa tìm kiếm" value="{{!empty($data_search['keyword'])?$data_search['keyword']:''}}">
                                        </div>
                                        <div class="col-sm-2" style="padding-left:0">
                                            <select name="place_id" id="place_id" class="form-control" style="width: 100%;">
                                                <option value="">Tòa nhà</option>
                                                <?php $place_building = isset($get_place_building) ? $get_place_building : '' ?>
                                                @if($place_building)
                                                    <option value="{{$place_building->id}}" selected>{{$place_building->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="apartment" id="ip-apartment"  class="form-control">
                                                <option value="">Căn hộ</option>
                                               <?php $apartment = isset($get_apartment) ?$get_apartment:'' ?>
                                                @if($apartment)
                                                    <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <select name="cate" id="ip-cate"  class="form-control">
                                                <option value="">Chọn Loại phương tiện</option>
                                                <?php $cate = isset($data_search['cate'])?$data_search['cate']:'' ?>
                                                @foreach($vehiclecates as $category)
                                                    @if($cate && $cate->id==$category->id)
                                                        <option value="{{$category->id}}" selected>{{$category->name}}</option>
                                                    @else
                                                        <option value="{{$category->id}}">{{$category->name}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-1">
                                            <div class="input-group-btn">
                                                <button type="submit" title="Tìm kiếm" id="tim_kiem" class="btn btn-info" form="form-search-customer"><i class="fa fa-search"></i></button>
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
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                <tr>
                                    <th width="1%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th width="1%">Stt</th>
                                    <th width="130">Căn hộ</th>
                                    <th width="130">Tên phương tiện</th>
                                    <th width="30">Loại phương tiện</th>
                                    <th width="90">Biển số</th>
                                    <th width="90">Mã Thẻ</th>
                                    <th width="30">Mức ưu tiên</th>
                                    <th width="90">Phí</th>
                                    <th width="90">Ngày bắt đầu tính phí</th>
                                    <th width="90">Ngày kết thúc tính phí</th>
                                    <th width="130">Mô tả</th>
                                    <th width="50">Trạng thái</th>
                                    <th width="50">Người cập nhật</th>
                                    <th width="150">Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($vehicles as $v)
                                    @php
                                        $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($v->bdc_apartment_id);
                                        $user = App\Models\PublicUser\Users::get_detail_user_by_user_id($v->user_id);
                                        $category = App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id($v->vehicle_category_id);
                                        //$apartmentServicePrice = App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository::getInfoServiceApartmentByVehicle($v->id);
                                        $vehicle_card = App\Models\VehicleCards\VehicleCards::get_detail_vehicle_card_by_id($v->id);
                                    @endphp
                                    <tr @if($v->deleted_at != null) class="danger" style="text-decoration: line-through;" @endif>
                                        <td><input type="checkbox" name="ids[]" value="{{$v->id}}" class="iCheck checkSingle" /></td>
                                        <td>
                                            <a target="_blank" href="/admin/activity-log/log-action?row_id={{$v->id}}"> {{ $v->id }}</a>
                                        </td>
                                        <td>{{$apartment->name??''}}</td>
                                        <td>{{$v->name}}</td>
                                        <td>{{$category->name??''}}</td>
                                        <td>{{$v->number}}</td>
                                        <td>{{@$v->vehicleCard->code}}</td>
                                        <td>{{$v->priority_level}}</td>
                                        <td class="text-right">{{number_format( $v->price)}}</td>
                                        <td>{{@$v->first_time_active ? date('d-m-Y',strtotime(@$v->first_time_active)) : ''}}</td>
                                        <td>{{@$v->finish ? date('d-m-Y',strtotime(@$v->finish)) : ''}}</td>
                                        <td>{{$v->description}}</td>
                                        <td>
                                            @if ( in_array('admin.v2.vehicles.edit',@$user_access_router) &&  $v->deleted_at == null)
                                                <div class="onoffswitch">
                                                    <input type="checkbox"
                                                        name="onoffswitch"
                                                        class="onoffswitch-checkbox"
                                                        data-id="{{ $v->id }}"
                                                        id="myonoffswitch_{{ $v->id }}"
                                                        data-url="{{ route('admin.v2.vehicles.status') }}"
                                                        value="{{$v->status}}" @if($v->status == true) checked @endif
                                                    >
                                                    <label class="onoffswitch-label" for="myonoffswitch_{{ $v->id }}">
                                                        <span class="onoffswitch-inner"></span>
                                                        <span class="onoffswitch-switch"></span>
                                                    </label>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            
                                            <small>
                                                {{@$user->email}}<br />
                                                {{ $v->created_at->format('d-m-Y H:i') }}
                                            </small>
                                        </td>
                                        <td colspan="" rowspan="" headers="">
                                            @if ( in_array('admin.v2.vehicles.edit',@$user_access_router) &&  $v->deleted_at == null)
                                                <a href="{{ route('admin.v2.vehicles.edit',['id'=> $v->id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                            @endif
                                            @if ( in_array('admin.v2.vehicles.delete',@$user_access_router) &&  $v->deleted_at == null)
                                                <a href="{{ route('admin.v2.vehicles.delete',['id'=> $v->id]) }}" class="btn btn-danger" title="xóa" onclick="return confirm('Bạn có chắc chắn xóa!');"><i class="fa fa-times"></i></a>  
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row mbm">
                            <div class="col-sm-3">
                                <span class="record-total">Hiển thị: {{$vehicles->count()}} / {{ $vehicles->total() }} kết quả</span>
                            </div>
                            <div class="col-sm-6 text-center">
                                <div class="pagination-panel">
                                    {{ $vehicles->appends(array_merge(Request::all(),['tab'=>""]))->onEachSide(1)->links() }}
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
        @include('vehicles.v2.modals.add-vehicle')
        <div class="clearfix"></div>
        @include('vehicles.v2.modals.export_choppy_vehicle')
    </div>
</div>
