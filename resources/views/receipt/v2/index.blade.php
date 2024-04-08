@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý kế toán
        <small>Quản lý phiếu thu</small>
        <a href="{{route('admin.v2.receipt.export_thu_tien_tavico',Request::all())}}" class="btn btn-success">Xuất thu tiền tavico</a>
        <a href="{{route('admin.v2.receipt.exportFilterThuChi',Request::all())}}" class="btn btn-success">Xuất ra excel</a>
        <a href="{{route('admin.v2.receipt.exportDetailFilter',Request::all())}}" class="btn btn-success">Xuất ra chi tiết excel</a>
        <a href="{{route('admin.v2.receipt.exportDetailFilter_v2',Request::all())}}" class="btn btn-success">Xuất ra bảng kê thu tiền</a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Quản lý phiếu thu</li>
    </ol>
</section>


<section class="content">
    <div class="box box-primary">
        <div class="box-body font-weight-bold">
            <h3 style="float: left">Quản lý phiếu thu</h3>
            @if (\Auth::user()->isadmin == 1)
                <div class="col-sm-1">
                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left"
                        style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li>
                            <a type="button" class="btn-action" data-target="#form-receipt" data-method="del_receipt">Xóa</a>
                        </li>
                    </ul>
                </div>
            @endif
            <div>
                <a href=" {{ route('admin.v2.receipt.create') }}" class="btn btn-warning">Lập phiếu thu</a>
                <a href=" {{ route('admin.v2.receipt.phieu_dieu_chinh') }}" class="btn btn-warning">Lập phiếu điều chỉnh</a>
                <a href="{{ route('admin.v2.provisionalreceipt.create') }}" class="btn btn-warning">Lập phiếu thu khác</a>
                <a href="{{ route('admin.v2.receipt.create_payment_slip') }}" class="btn btn-warning">Lập phiếu chi</a>
                <a href="{{ route('admin.v2.provisionalreceipt.createPaymentSlip') }}" class="btn btn-warning">Lập phiếu chi khác</a>
            </div>
        </div>
        <div class="box-body">
            <form id="form-search-advance" action="{{route('admin.v2.receipt.index')}}" method="get">
                <div id="search-advance" class="search-advance">
                    <div class="row space-5">
                        <div class="col-sm-2">
                            <input class="form-control" type="text" placeholder="Tìm kiếm từ khóa..." name="user_id_receipt_code"
                                value="{{ @$filter['user_id_receipt_code'] }}" />
                        </div>
                        <div class="col-sm-2">
                            <select name="type_payment" class="form-control">
                                <option value="" selected>Hình thức...</option>
                                @foreach (@$get_type_receipt as $item)
                                    <option value="{{$item->config}}"  @if(@$filter['type_payment'] == $item->config) selected @endif >{{$item->title}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2" style="padding-left:0">
                            <select name="ip_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                                <option value="">Chọn tòa nhà</option>
                                <?php $place_building = isset($get_place_building) ? $get_place_building : '' ?>
                                @if($place_building)
                                <option value="{{$place_building->id}}" selected>{{$place_building->name}}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-1" style="padding-left:0">
                            <select name="bdc_apartment_id" id="ip-apartment"  class="form-control">
                                <option value="">Căn hộ</option>
                                    <?php $apartment = isset($get_apartment) ? $get_apartment: '' ?>
                                @if($apartment)
                                <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-2" style="padding-left:0">
                            <select name="user_id" id="user_id"  class="form-control select2">
                                <option value="">Người thu</option>
                                @foreach (@$user_info as $item)
                                    <option value="{{$item->id}}"  @if(@$filter['user_id'] == $item->id) selected @endif >{{$item->email}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                     <div class="row space-5" style="margin-top: 5px;">
                           <div class="col-sm-2">
                             <label for="">Ngày hạch toán</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="from_date"
                                    value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="input-group date" style="margin-top: 25px;">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="to_date"
                                    value="{{ @$filter['to_date'] }}" placeholder="Đến..." autocomplete="off">
                            </div>
                        </div>
                         <div class="col-sm-2">
                            <label for="">Ngày lập phiếu</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="created_at_from_date"
                                    value="{{ @$filter['created_at_from_date'] }}" placeholder="Từ..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="input-group date" style="margin-top: 25px;">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right date_picker" name="created_at_to_date"
                                    value="{{ @$filter['created_at_to_date'] }}" placeholder="Đến..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <label for="">Kiểu phiếu</label>
                            <select name="receipt_code_type[]" id="receipt_code_type" class="form-control select2" multiple>
                                <option value="" selected>Kiểu phiếu ..</option>
                                @foreach($type_receipt as $value)
                                    <option value="{{ $value['text'] }}" >{{ $value['value'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-info" style="margin-top: 25px;"><i class="fa fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                     </div>
                </div>
            </form>
            <label style="margin-top: 15px;">Tổng: {{number_format($sum_cost)}} VND</label>
            <div class="table-responsive">
                <form id="form-receipt" action="{{route('admin.v2.receipt.action')}}" method="post">
                @csrf
                <input type="hidden" name="method" value="">
                <table class="table table-hover table-striped table-bordered">
                    <thead class="bg-primary">
                        <tr>
                            @if (Auth::user()->isadmin == 1)
                            <th><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                             <th>Dự án</th>
                            @endif
                            <th>STT</th>
                            <th>Mã chứng từ</th>
                            <th>Hình thức</th>
                            <th>Loại phiếu</th>
                            <th>Ngày lập phiếu</th>
                            <th>Ngày hạch toán</th>
                            <th>Căn hộ</th>
                            <th>Tòa</th>
                            <th>Khách hàng</th>
                            <th>Số tiền</th>
                            <th>Nội dung</th>
                            <th>Người tạo</th>
                            <th>Người xoá</th>
                            <th>Thao tác</th>
                            <th>In phiếu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipts as $lock => $receipt)
                                @php
                                    $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($receipt->bdc_apartment_id);
                                    $builsingPlace =$apartment ? App\Models\Building\BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id) : null;
                                    $user = App\Models\PublicUser\Users::get_detail_user_by_user_id($receipt->user_id);
                                    $_building = \App\Models\Building\Building::get_detail_building_by_building_id($receipt->bdc_building_id);
                                    // $tien_thua = App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository::sum_coin_by_accounting(@$receipt->id);
                                    // $coin = App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository::sum_coin($receipt->id);
                                    // $total_receipt = @$receipt->PaymentDetail->sum('paid') + $coin - $tien_thua;

                                @endphp
                            <tr @if($receipt->deleted_at != null) class="danger" style="text-decoration: line-through;" @endif>
                                @if (Auth::user()->isadmin == 1)
                                 <td><input type="checkbox" name="ids[]" value="{{$receipt->id}}" class="iCheck checkSingle" /></td>
                                 <td> {{@$_building->name}}</td>
                                @endif
                                <td>
                                    <a target="_blank" href="/admin/activity-log/log-action?row_id={{$receipt->id}}"> {{ $receipt->id }}</a>
                                </td>
                                <td>
                                    {{@$receipt->receipt_code}}
                                </td>
                                <td>
                                    {{ @$receipt->type_payment !=null ? App\Commons\Helper::loai_danh_muc[$receipt->type_payment] : ''}}
                                </td>
                                <td>
                                    {{ @$receipt->type !=null ? App\Commons\Helper::loai_danh_muc[$receipt->type] : ''}}
                                </td>
                                <td>{{date('d/m/Y', strtotime(@$receipt->created_at))}}</td>
                                <td>{{@$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----' }}</td>
                                <td>{{@$apartment->name}}</td>
                                <td>{{@$builsingPlace->code}}</td>
                                <td>{{@$receipt->customer_name}}</td>
                                <td style="text-align: right;">{{number_format($receipt->cost)}}</td>
                                <td>{{@$receipt->description}}</td>
                                <td>{{@$user->email}}</td>
                                <td>
                                    @if($receipt->deleted_at != null)
                                        <small>
                                            @php
                                                 $user_del = App\Models\PublicUser\Users::withTrashed()->find($receipt->updated_by);
                                            @endphp
                                            {{ @$user_del->email }}<br />
                                            {{ @$receipt->updated_at->format('Y-m-d H:i') }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($receipt->deleted_at == null)
                                        <a style="float: left" href="{{route('admin.v2.receipt.edit',$receipt->id)}}" class="btn btn-sm btn-warning margin-r-5">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if( (in_array('admin.v2.receipt.destroy',@$user_access_router) && $receipt->deleted_at == null) && (Auth::user()->id != 36889) )
                                            <a href="javascript:;" data-url="{{route('admin.v2.receipt.show_huyphieuthu',['receiptId'=> $receipt->id])}}" class="btn btn-sm btn-danger xoa_phieu_thu" title="Phân bổ">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                        <a style="float: left" href="{{ route('admin.v2.receipt.receiptCode',$receipt->receipt_code) }}" target="_blank"
                                            class="btn btn-sm btn-info margin-r-5"><i class="fa fa-print"></i></a>
                                </td>
                               
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="row mbm">
                <div class="col-sm-3">
                    <span class="record-total">Tổng: {{ $receipts->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $receipts->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#form-receipt">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
            </form>
        </div>
    </div>
    <input type="hidden" value="{{isset($filter) ? json_encode(@$filter['receipt_code_type']) : ''}}" id="receipt_code_types">
</section>
<div id="form_xoa_phieu" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><strong>Hủy phiếu thu</strong></h4>
            </div>
            <div class="modal-body chi_tiet_tien_thua">
                {{-- <div class="form-group">
                    <div class="col-md-12">
                        <p>Tổng tiền thừa</p>
                        <div> dịch vụ 1</div>
                        <div> dịch vụ 2</div>
                        <div> dịch vụ 3</div>
                        <div> dịch vụ 4</div>
                        <div> dịch vụ 5</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-sm btn-primary add_phan_bo_item">
                            <i class="fa fa-plus" aria-hidden="true"> Thêm phân bổ mới</i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="card-body phan_bo_list">
                    </div>
                </div> --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success save_phieu_thu">Hủy phiếu thu</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('javascript')
<script>
    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
     $(function(){
             $('.xoa_phieu_thu').click(function (e) {
                 e.preventDefault();
                 let url  = $(this).attr('data-url');
                 showLoading();
                 $.ajax({
                     url: url,
                     type: 'GET',
                     success: function (response) {
                         hideLoading();
                         if (response.success == true) {
                             $('.chi_tiet_tien_thua').html(response.data.html);
                             $('#form_xoa_phieu').modal('show');
                             if(response.data.status) $('.save_phieu_thu').show(); else $('.save_phieu_thu').hide();
                         }
                     }
                 });
             });

         $('.save_phieu_thu').click(function (e) {
             showLoading();
             $.ajax({
                 url: '{{route('admin.v2.receipt.save_huyphieuthu')}}' ,
                 data: {
                     receiptId: $('#receiptIdXoa').val(),
                     note: $('#note').val(),
                 },
                 type: 'POST',
                 success: function (response) {
                     hideLoading();
                     if(response.data.status === 0) {
                         alert("Hủy thành công!");
                         $('#form_xoa_phieu').modal('hide');
                         location.reload();
                     } else {
                         alert(response.data.mess);
                     }
                 }
             });
         });
             get_data_select_apartment1({
                object: '#ip-place_id',
                url: '{{ url('admin/apartments/ajax_get_building_place') }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn tòa nhà'
            });
            function get_data_select_apartment1(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [{
                                id: '',
                                text: options.title_default
                            }];

                            for (i in json.data) {
                                var item = json.data[i];
                                results.push({
                                    id: item[options.data_id],
                                    text: item[options.data_text]+' - '+item[options.data_code]
                                });
                            }
                            return {
                                results: results,
                            };
                        },
                        minimumInputLength: 3,
                    }
                });
            }
           get_data_select({
                object: '#ip-apartment',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            $("#ip-place_id").on('change', function(){ 
                if($("#ip-place_id").val()){
                    get_data_select({
                    object: '#ip-apartment',
                    url: '{{ url('admin/apartments/ajax_get_apartment_with_place') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn căn hộ'
                    });
                }
            });
            function get_data_select(options) {
                    $(options.object).select2({
                        ajax: {
                            url: options.url,
                            dataType: 'json',
                            data: function(params) {
                                var query = {
                                    search: params.term,
                                    place_id: $("#ip-place_id").val(),
                                }
                                return query;
                            },
                            processResults: function(json, params) {
                                var results = [{
                                    id: '',
                                    text: options.title_default
                                }];

                                for (i in json.data) {
                                    var item = json.data[i];
                                    results.push({
                                        id: item[options.data_id],
                                        text: item[options.data_text]
                                    });
                                }
                                return {
                                    results: results,
                                };
                            },
                            minimumInputLength: 3,
                        }
                    });
                }
           
        })
        $(document).ready(function () {
            
            if($('#receipt_code_types').val() != '[]'){
                let receipt_code_types = $('#receipt_code_types').val();
                let obj_receipt_code_types = Object.values(JSON.parse(receipt_code_types));
                console.log(obj_receipt_code_types);
                let new_receipt_code_types = [];
                for (let index = 0; index < obj_receipt_code_types.length; index++) {
                    let receipt_type = obj_receipt_code_types[index];
                    console.log(receipt_type);
                    new_receipt_code_types.push(
                        receipt_type
                    )
                }
                console.log(new_receipt_code_types);
                $('#receipt_code_type').val(new_receipt_code_types).trigger('change');
            }
        });
</script>
@endsection