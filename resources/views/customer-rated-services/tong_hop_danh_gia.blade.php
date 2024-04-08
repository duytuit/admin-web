@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Tổng hợp đánh giá</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Tổng hợp đánh giá</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="{{ str_contains(url()->current(),'total') ? 'active' : null }}"><a href="{{ route('admin.rated_service.total') }}" >Tổng hợp đánh giá</a></li>
                            <li class="{{ !str_contains(url()->current(),'total') ? 'active' : null }}"><a href="{{ route('admin.rated_service.detail') }}" >Chi tiết đánh giá</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane {{ str_contains(url()->current(),'total') ? 'active' : null }}" id="{{ route('admin.rated_service.total') }}">
                                    <form id="form-search-advance" action="{{ route('admin.rated_service.total') }}" method="get">
                                        <div class="row form-group">
                                            <div class="col-12 col-md-12">
                                               <div class="row col-md-12">
                                                    <div class="col-sm-2">
                                                        <select name="bdc_department_id" class="form-control">
                                                            <option value="" selected>Bộ phận</option>
                                                            @foreach ($bo_phan as $item)
                                                                <option value="{{ @$item->id }}" {{ @$item->id == @$filter['bdc_department_id'] ? 'selected' : '' }}>{{ @$item->name }}</option>
                                                             @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="input-group date">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </div>
                                                            <input type="text" class="form-control pull-right date_picker" name="from_date"
                                                                value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="input-group date">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </div>
                                                            <input type="text" class="form-control pull-right date_picker" name="to_date"
                                                                value="{{ @$filter['to_date'] }}" placeholder="Đến..." autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <a href="javascript:;" class="btn btn-primary open_thiet_lap">Thiết lập</a>
                                                        <a href="{{ route('admin.rated_service.export_total',Request::all()) }}" class="btn btn-success"><i class="fa fa-edit"></i>Export</a>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                </form><!-- END #form-search-advance -->
                                <p><strong> Tổng điểm: </strong>{{@$tong_diem}}</p>
                                <form id="form-tong-hop" action="{{ route('admin.rated_service.action') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="method" value="" />
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                    @if(@$tong_vote)
                                                        @php
                                                            $tong_diem_1 = 0;
                                                            $tong_diem_2 = 0;
                                                            $tong_diem_3 = 0;
                                                            $tong_diem_4 = 0;
                                                            $tong_diem_5 = 0;
                                                            foreach ($tong_vote as $key => $value) {
                                                                if($value->point == -3){
                                                                    $tong_diem_1 += $value->total;
                                                                }
                                                                if($value->point == -1){
                                                                    $tong_diem_2 += $value->total;
                                                                }
                                                                if($value->point == 1){
                                                                    $tong_diem_3 += $value->total;
                                                                }
                                                                if($value->point == 3){
                                                                    $tong_diem_4 += $value->total;
                                                                }
                                                                if($value->point == 5){
                                                                    $tong_diem_5 += $value->total;
                                                                }
                                                            } 
                                                        @endphp
                                                    @endif
                                                <tr>
                                                    <th colspan="6"></th>
                                                    <th>{{@$tong_diem}}</th>
                                                    <th>{{@$tong_danh_gia}}</th>
                                                    <th>{{@$tong_diem_1}}</th>
                                                    <th>{{@$tong_diem_2}}</th>
                                                    <th>{{@$tong_diem_3}}</th>
                                                    <th>{{@$tong_diem_4}}</th>
                                                    <th>{{@$tong_diem_5}}</th>
                                                </tr>
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Nhân viên/ Nhà thầu</th>
                                                    <th>Mã nhân viên/ Nhà thầu</th>
                                                    <th>Bộ phận</th>
                                                    <th>SĐT</th>
                                                    <th>Email</th>
                                                    <th>Tổng điểm</th>
                                                    <th>Tổng lượt đánh giá</th>
                                                    <th>Rất không hài lòng</th>
                                                    <th>Chưa hài lòng</th>
                                                    <th>Bình thường</th>
                                                    <th>Hài lòng</th>
                                                    <th>Rất hài lòng</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @if($ds_nhan_vien->count() > 0)
                                                            @foreach($ds_nhan_vien as $key => $value)
                                                                @php
                                                                    $department = App\Models\Department\Department::get_detail_department_by_id($value->department_id);
                                                                @endphp
                                                                <tr class="list_asset_hand_over">
                                                                    <td>{{ @($key + 1) + ($ds_nhan_vien->currentPage() - 1) * $ds_nhan_vien->perPage() }}</td>
                                                                    <td>{{ @$value->user_info_rated->display_name }}</td>
                                                                    <td>{{ @$value->user_info_rated->staff_code }}</td>
                                                                    <td>{{ @$department->name }}</td>
                                                                    <td>{{ @$value->user->phone }}</td>
                                                                    <td>{{ @$value->user->email }}</td>
                                                                    <td>{{ @$value->total_point}}</td>
                                                                    <td>{{ @$value->total_employee}}</td>
                                                                    <td>
                                                                        @if($value->vote)
                                                                            @php
                                                                                $diem_1 = 0;
                                                                                $diem_2 = 0;
                                                                                $diem_3 = 0;
                                                                                $diem_4 = 0;
                                                                                $diem_5 = 0;
                                                                                foreach ($value->vote as $key => $value) {
                                                                                    if($value->point == -3){
                                                                                        $diem_1 = $value->total;
                                                                                    }
                                                                                    if($value->point == -1){
                                                                                        $diem_2 = $value->total;
                                                                                    }
                                                                                    if($value->point == 1){
                                                                                        $diem_3 = $value->total;
                                                                                    }
                                                                                    if($value->point == 3){
                                                                                        $diem_4 = $value->total;
                                                                                    }
                                                                                    if($value->point == 5){
                                                                                        $diem_5 = $value->total;
                                                                                    }
                                                                                } 
                                                                            @endphp
                                                                        @endif
                                                                        <p>{{$diem_1}}</p>
                                                                    </td>
                                                                    <td>
                                                                        <p>{{$diem_2}}</p>
                                                                    </td>
                                                                    <td>
                                                                        <p>{{$diem_3}}</p>
                                                                    </td>
                                                                    <td>
                                                                        <p>{{$diem_4}}</p>
                                                                    </td>
                                                                    <td>
                                                                        <p>{{$diem_5}}</p>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                    @else
                                                        <tr><td colspan="13" class="text-center">Không có kết quả tìm kiếm</td></tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row mbm">
                                                <div class="col-sm-3">
                                                    <span class="record-total">Hiển thị {{ $ds_nhan_vien->count() }} / {{ $ds_nhan_vien->total() }} kết quả</span>
                                                </div>
                                                <div class="col-sm-6 text-center">
                                                    <div class="pagination-panel">
                                                        {{ $ds_nhan_vien->appends(request()->input())->links() }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 text-right">
                                                    <span class="form-inline">
                                                        Hiển thị
                                                        <select name="per_page" class="form-control" data-target="#form-tong-hop">
                                                            @php $list = [10, 20, 50, 100, 200]; @endphp
                                                            @foreach ($list as $num)
                                                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                                            @endforeach
                                                        </select>
                                                    </span>
                                                </div>
                                        </div>
                                </form><!-- END #form-users --> 
                            </div>
                        </div>
                        <!-- /.tab-content -->
                    </div>
                    <!-- nav-tabs-custom -->
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="create_thiet_lap_danh_gia" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body debit_detail_content">
                    <br>
                    <div class="form-group">
                        <h3 class="text-center">Thiết lập đánh giá</h3>
                    </div>
                    <form action="{{ route('admin.rated_service.update_limit_audit') }}" method="get" id="form-limit-audit" autocomplete="off" >
                        <div class="row form-group">
                            <div class="col-sm-12 form-group">
                                <label for="">Giới hạn lượt đánh giá</label>
                                <select name="limit" id="limit" class="form-control">
                                    <option value="khong_gioi_han" {{  @$building->limit_audit->type == 'khong_gioi_han' ? 'selected' : '' }}>Không giới hạn</option>
                                    <option value="ngay" {{  @$building->limit_audit->type == 'ngay' ? 'selected' : '' }}>Ngày</option>
                                    <option value="thang" {{  @$building->limit_audit->type == 'thang' ? 'selected' : '' }}>Tháng</option>
                                </select>
                                <input type="hidden" name="building_id" value="{{ $building->id }}" />
                            </div>
                            <div class="col-sm-12">
                                <input type="number" class="form-control" min="1" id="gia_tri_gioi_han" name="gia_tri_gioi_han" value="{{  @$building->limit_audit->limit}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary" id="add_thiet_lap" style="width:150px">Lưu</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- @include('asset-apartments.tabs.asset-handover.modal.send-notify') --}}
<style>
    .modal-dialog {
        top: 200px;
    }
</style>
@section('javascript')
    <script>
        //Date picker
        $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
        }).val();
       
        $('.open_thiet_lap').click(function(e) {
            e.preventDefault();
            $('#create_thiet_lap_danh_gia').modal('show');
        });
        $('#limit').change(function(e) {
            e.preventDefault();
            if($('#limit').val() == 'khong_gioi_han'){
                $('#gia_tri_gioi_han').css('display','none')
             }else{
                $('#gia_tri_gioi_han').css('display','block')
             }
        });
        $(document).ready(function () {
             if($('#limit').val() == 'khong_gioi_han'){
                $('#gia_tri_gioi_han').css('display','none')
             }else{
                $('#gia_tri_gioi_han').css('display','block')
             }
        });
    </script>
@endsection
