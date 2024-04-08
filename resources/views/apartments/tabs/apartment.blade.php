<div class="box box-primary">
    <h1>
        @if( in_array('admin.apartments.create',@$user_access_router))
            <a href="{{ route('admin.apartments.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới
            </a>
        @endif
        @if( in_array('admin.apartments.index_import',@$user_access_router))
            <a href="{{ route('admin.apartments.index_import') }}" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;
                Import Exel
            </a>
        @endif
        @if( in_array('admin.apartments.export',@$user_access_router))
            <a href="{{ route('admin.apartments.export',$data_search) }}" class="btn btn-warning">
                <i class="fa fa-download"></i>&nbsp;&nbsp;
                Export Exel
            </a>
        @endif
    </h1>
    <div class="box-body ">
        <form id="form-search-apartment" action="" method="post">
            {{ csrf_field() }}
            <div class="col-sm-1">
                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="javascript:" type="button" class="btn-action" data-target="#form-apartmennt-list" data-method="delete">
                            <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                        </a>
                        @if(\Auth::user()->isadmin == 1)
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-apartmennt-list" data-method="restore_apartment">
                                    <i class="fa fa-plus text-danger"></i>&nbsp; Khôi phục
                                </a>
                        @endif
                    </li>
                    <li>
                        <a href=""
                           class="btn"
                           data-toggle="modal"
                           data-target="#add-apartment-to-group"
                        >
                            <i class="fa fa-plus"></i>&nbsp;&nbsp; Thêm vào nhóm
                        </a>
                    </li>

                </ul>
            </div>
            <div class="col-sm-11">
                <div id="search-advance" class="search-advance">
                    <div class="row ">
                        <div class="form-group space-5" style="width: calc(100% - 55px);float: left;">
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="name" placeholder="Nhập keyword" value="{{ !empty($data_search['name']) ? $data_search['name'] : '' }}">
                                <input type="hidden" name="search_key" value="{{ !empty($data_search['search_key']) ? $data_search['search_key'] : '' }}">
                            </div>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="floor" placeholder="Số tầng" value="{{ !empty($data_search['floor']) ? $data_search['floor'] : '' }}">
                            </div>
                            <div class="col-sm-2">
                               
                                <?php $building_place_id = !empty($data_search['building_place_id'])?$data_search['building_place_id']:''; ?>
                                <select name="place" id="ip-place" class="form-control" style="width: 100%">
                                    <option value="">Chọn tòa nhà</option>
                                    @if($building_place_id)
                                        <option value="{{$building_place_id}}" selected>{{!empty($name_place) ? $name_place : ''}}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <select class="form-control" id="select-re_name" name="re_name" style="width: 100%">
                                    <option value="">Chọn chủ hộ</option>
                                    <?php $re_name = !empty($data_search['re_name']) ? $data_search['re_name'] : '';?>
                                    @if($re_name)
                                        <option value="{{$re_name}}" selected>{{!empty($data_search['name_profile']) ? $data_search['name_profile'] : ''}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <?php $name = '';$ap_role = $data_search['status'] ?? '';
                                //                                        dd($ap_role);
                                ?>
                                <select name="status" id="select-ap-role" class="form-control">
                                    <option value="false">Chọn Trạng thái</option>
                                    <option value="0" @if($ap_role == '0') selected @endif>Để không</option>
                                    <option value="1" @if($ap_role == '1') selected @endif>Cho thuê</option>
                                    <option value="2" @if($ap_role == '2') selected @endif>Muốn cho thuê</option>
                                    <option value="3" @if($ap_role == '3') selected @endif>Đang ở</option>
                                    <option value="4" @if($ap_role == '4') selected @endif>Mới bàn giao</option>
                                    <option value="5" @if($ap_role == '5') selected @endif>Đang cải tạo</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group-btn">
                            <button type="submit" title="Tìm kiếm" class="btn btn-info" form="form-search-apartment"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </form><!-- END #form-search-advance -->
        <div class="clearfix"></div>
        @if( in_array('admin.apartments.action',@$user_access_router))
            <form id="form-apartmennt-list" action="{{ route('admin.apartments.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                            <th width="30">Stt</th>
                            <th width="60">Căn hộ</th>
                            <th width="60">Mã hộ</th>
                            <th width="200">Chủ hộ</th>
                            <th width="50">Tòa</th>
                            <th width="30">Tầng</th>
                            <th width="30">Số người</th>
                            <th width="80">Số phương tiện</th>
                            <th width="90">Tập tin đính kèm</th>
                            <th width="130">Tình trạng</th>
                            <th width="130">Nhóm căn hộ</th>
                            <th width="150">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($apartments as $key => $item)
                            <tr @if($item->deleted_at != null) class="danger" style="text-decoration: line-through;" @endif>
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" data-id="{{isset($item->apartmentGroup->name)?"":$item->id}}" class="iCheck checkSingle apartment-item" /></td>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ @$item->code }}</td>
                                {{--                                        <td>{{ $item->id }}</td>--}}
                                <td>
                                    <?php  
                                         $check_remove_apartment = App\Models\BdcDebitDetail\DebitDetail::where('bdc_apartment_id',$item->id)->first();
                                         $name='';
                                    ?>
                                    @foreach($item->bdcCustomers as $k => $re)
                                        <?php
                                        if($re->type == 0){
                                            if($re->pubUserProfile){
                                                $name .= ', '.empty($re->pubUserProfile->display_name)?', '.$re->pubUserProfile->display_name:'';
                                            }
                                        }
                                        ?>
                                    @endforeach
                                    {{ trim($name,', ') }}
                                </td>
                                <td>
                                    @if($item->buildingPlace)
                                        {{ $item->buildingPlace->code }}
                                    @endif
                                </td>
                                <td>{{ $item->floor }}</td>
                                <td align="left">{{count($item->bdcCustomers)}}</td>
                                <td align="left">{{count($item->bdcVehicles)}}</td>
                                <td>
                                    <?php $count_file = count($item->systemFile)?>
                                    {{$count_file}}
                                </td>
                                <td>
                                    @if($item->status == 0)
                                        Để không
                                    @elseif($item->status == 1)
                                        Cho thuê
                                    @elseif($item->status == 2)
                                        Muốn cho thuê
                                    @elseif($item->status == 3)
                                        Đang ở
                                    @elseif($item->status == 4)
                                        Mới bàn giao
                                    @elseif($item->status == 5)
                                        Đang cải tạo
                                    @endif
                                </td>
                                <td>
                                    {{$item->apartmentGroup->name??""}}
                                </td>
                                <td colspan="" rowspan="" headers="">
                                    @if( in_array('admin.apartments.action',@$user_access_router) )
                                        <a href="{{route('admin.apartments.edit',['id'=>$item->id])}}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                        @if (!$check_remove_apartment)
                                              <a href="{{ url('admin/apartments/'.$item->id.'/del') }}" class="btn btn-danger" title="xóa"><i class="fa fa-times"></i></a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Hiển thị: {{$apartments->count()}} / {{ $apartments->total() }} Kết quả</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $apartments->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                                <span class="form-inline">
                                    Hiển thị
                                    <select name="per_page" class="form-control" data-target="#form-apartmennt-list">
                                        @php $list = [5,10, 20, 50, 100, 200]; @endphp
                                        @foreach ($list as $num)
                                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                        @endforeach
                                    </select>
                                </span>
                    </div>
                </div>
            </form><!-- END #form-users -->
        @endif
    </div>
    @include('apartments.modals.add-multi-apartment-to-group')
</div>
