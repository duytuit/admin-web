@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách quản lý hạch toán giao dịch
        <label class="label label-sm label-success">Thời gian đồng bộ: {{@$last_time->data->time_update ? date('h:i:s d/m/Y',strtotime($last_time->data->time_update)) : '--/--/----'}}</label>
        <label class="label label-sm label-success">Giao dịch gần nhất: {{@$last_time->data->last_time_pay ? date('h:i:s d/m/Y',strtotime($last_time->data->last_time_pay)) : '--/--/----'}}</label>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">quản lý hạch toán giao dịch</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            
                <form id="form-search" action="{{ route('admin.history-transaction-accounting.index') }}" method="get">

                    <div class="row form-group">
                        <div class="col-sm-4">
                            @if( in_array('admin.history-transaction-accounting.action',@$user_access_router))
                                <span class="btn-group">
                                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li><a class="btn-action" data-target="#form-history-transaction-accounting" data-method="confirm_hach_toan" href="javascript:;"><i class="fa fa-plus"></i> Xác nhận hạch toán</a></li>
                                        <li><a class="btn-action" data-target="#form-history-transaction-accounting" onclick="return confirm('Bạn có chắc chắn muốn hủy không?')" data-method="huy_hach_toan" href="javascript:;" return=><i class="fa fa-plus"></i> Hủy hạch toán</a></li>
                                    </ul>
                                </span>
                            @endif
                            @if( in_array('admin.history-transaction-accounting.create',@$user_access_router))
                                <a href="{{ route('admin.history-transaction-accounting.import') }}" class="btn btn-info"><i class="fa fa-edit"></i> Import</a>
                                <a href="{{ route('admin.history-transaction-accounting.import_vietqr') }}" class="btn btn-info"><i class="fa fa-edit"></i> Import VietQR</a>
                                <a href="{{ route('admin.history-transaction-accounting.export',Request::all()) }}" class="btn btn-success"><i class="fa fa-edit"></i> Export</a>
                            @endif
                        </div>
                        <div class="col-sm-8">
                            <div class="col-sm-12">
                                <div class="col-sm-3">
                                    <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Nhập từ khóa" class="form-control" />
                                </div>
                                <div class="col-sm-3">
                                    <select name="bdc_apartment_id" id="ip-apartment"  class="form-control">
                                        <option value="">Căn hộ</option>
                                        <?php $apartment = @$filter['apartment'] ? @$filter['apartment'] : '' ?>
                                        @if($apartment)
                                            <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <select name="status" class="form-control">
                                        <option value="" selected>Trạng thái</option>
                                        <option value="huy_hach_toan" @if(@$filter['status'] ==  'huy_hach_toan') selected @endif>Hủy</option>
                                        <option value="da_hach_toan" @if(@$filter['status'] ==  'da_hach_toan') selected @endif>Đã hạch toán</option>
                                        <option value="cho_hach_toan" @if(@$filter['status'] ==  'cho_hach_toan') selected @endif>Chờ hạch toán</option>
                                        <option value="view" @if(@$filter['status'] ==  'view') selected @endif>Giao dịch chưa xử lý</option>
                                        <option value="viewed" @if(@$filter['status'] ==  'viewed') selected @endif>Giao dịch đã xử lý</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 text-right">
                                    <button type="submit" class="btn btn-info"><span class="fa fa-search"></span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search -->
                <form id="form-history-transaction-accounting" action="{{ route('admin.history-transaction-accounting.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th width="3%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th>STT</th>
                                    <th>Giao dịch ID</th>
                                    <th>Căn hộ</th>
                                    <th>Tên khách hàng</th>
                                    <th>Hình thức</th>
                                    <th>Số tiền nộp</th>
                                    <th colspan="3" style="text-align: center;">Chi tiết</th>
                                    <th>Tiền thừa</th>
                                    <th>Nội dung</th>
                                    <th>Trạng thái</th>
                                    <th>Gợi ý căn hộ</th>
                                    <th>Ngày hạch toán</th>
                                    <th>Người tạo</th>
                                    <th>Người xác nhận</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($history_transaction_accountings as $item)
                                    @php
                                        $user_created_by = App\Models\PublicUser\Users::get_detail_user_by_user_id($item->created_by);
                                        $user_confirm_by = App\Models\PublicUser\Users::get_detail_user_by_user_id($item->user_confirm);
                                        $aparment_suggestions = $item->aparment_suggestions ? json_decode($item->aparment_suggestions) : null;
                                    @endphp
                                    <tr>
                                        @if ($item->status === 'huy_hach_toan' || $item->status === 'da_hach_toan' || $item->status === 'view')
                                           <td></td>
                                        @else
                                           <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                        @endif
                                        <td>{{ @($key + 1) + ($history_transaction_accountings->currentPage() - 1) * $history_transaction_accountings->perPage() }}</td>
                                        <td>{{ @$item->ngan_hang }}</td>
                                        <td>{{ $item->customer_address }}</td>
                                        <td>{{ $item->customer_name }}</td>
                                        <td>Chuyển khoản</td>
                                        <td>{{ number_format($item->cost) }}</td>
                                        <td colspan="3" >
                                            @if ($item->detail)
                                                @php
                                                    $detail = json_decode($item->detail);
                                                @endphp
                                            <table class="table table-striped table-bordered">
                                                    <tr style="background-color: lightgray">
                                                        <th>Dịch vụ</th>
                                                        <th>Phát sinh</th>
                                                        <th width="150">Thời gian</th>
                                                        <th>Phải trả</th>
                                                        <th>Số tiền hạch toán</th>
                                                    </tr>
                                                    @foreach ($detail as $value)
                                                        <tr>
                                                            <td>{{@$value->name}}</td>
                                                            <td>{{number_format($value->sumery)}}</td>
                                                            <td>{{date('d/m/y', strtotime(@$value->from_date))}} - {{date('d/m/y', strtotime(@$value->to_date))}}</td>
                                                            <td>{{number_format($value->sumery - $value->paid)}}</td>
                                                            <td>{{number_format(@$value->new_paid)}}</td>
                                                        </tr>
                                                    @endforeach
                                            </table>
                                          @endif
                                        </td>
                                        <td>{{ $item->account_balance ? number_format($item->account_balance) : 0}}</td>
                                        <td>{{ $item->remark }}</td>
                                        <td>
                                            @if ($item->status === 'cho_hach_toan')
                                                    <label class="label label-sm label-warning">chờ hạch toán</label>
                                            @elseif($item->status === 'da_hach_toan')
                                                    <label class="label label-sm label-success">đã hạch toán</label>
                                            @elseif($item->status === 'huy_hach_toan')
                                                    <label class="label label-sm label-danger">hủy</label>
                                            @elseif($item->status === 'view')
                                                    <label style="cursor: pointer;" class="label label-sm label-primary change_status"  data-id="{{ $item->id }}" data-apartments="{{ $item->aparment_suggestions }}"  data-trans_id="{{ $item->ngan_hang }}" style="cursor: pointer;">giao dịch chưa xử lý</label>
                                            @elseif($item->status === 'viewed')
                                                    <label class="label label-sm label-info">giao dịch đã xử lý</label>        
                                            @endif
                                        </td>
                                        <td>
                                            @if ($aparment_suggestions)
                                                @foreach ($aparment_suggestions as $key_1 => $item_1)
                                                    @if($key_1 == 0)
                                                       {{ $item_1->name }}
                                                    @else
                                                       {{ '|'.$item_1->name }}
                                                    @endif
                                                @endforeach
                                            @endif
                                        </td>
                                        <td> {{ $item->create_date }}</td>
                                        <td>
                                            <small>
                                                {{ @$user_created_by->email }}<br />
                                                {{ $item->updated_at->format('Y-m-d H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                {{ @$user_confirm_by->email }}<br />
                                                {{ $item->confirm_date ? $item->confirm_date : '' }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $history_transaction_accountings->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $history_transaction_accountings->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control" data-target="#form-history-transaction-accounting">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>
                </form><!-- END #form-history-transaction-accounting -->
        </div>
    </div>
    <div id="view_confirm_apartment" class="modal fade" role="dialog">
        <div class="modal-dialog custom-dialog">
            <!-- Modal content-->
            <form id="form_view_confirm_apartment">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Xác nhận giao dịch của căn hộ</h4>
                    </div>
                    <input type="hidden" id="id_history">
                    <input type="hidden" id="id_trans">
                    <div class="modal-body">
                        <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-12 form-group">
                                    <label>Xác nhận giao dịch cho căn hộ: </label>
                                    <p>Căn hộ gợi ý:</p>
                                    <div class="_list_aparmtent form-group">

                                    </div>
                                    <select name="bdc_apartment_id" id="ip_apartment" class="form-control" style="width: 100%"></select>
                            </div>
                            <div class="col-sm-12 detail_debit">
                                {{-- <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr style="background-color: lightgray">
                                            <th>Dịch vụ</th>
                                            <th>Phát sinh</th>
                                            <th width="150">Thời gian</th>
                                            <th>Phải trả</th>
                                            <th>Số tiền hạch toán</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($detail as $value)
                                        <tr>
                                            <td>Tiền nước của tòa VP</td>
                                            <td>555,000</td>
                                            <td>01/07/22 - 01/08/22</td>
                                            <td>555,000</td>
                                            <td><input type="text" class="form-control customer_paid_string" name="paid" value="{{ number_format(555000) }}"></td>
                                        </tr>
                                        <tr>
                                            <td>Tiền nước của tòa VP</td>
                                            <td>555,000</td>
                                            <td>01/07/22 - 01/08/22</td>
                                            <td>555,000</td>
                                            <td><input type="text" class="form-control customer_paid_string" name="paid" value="{{ number_format(555000) }}"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table> --}}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary pull-right save_status"><i class="fa fa-save"></i> Xác nhận</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                       
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection

@section('javascript')
<script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . '?v=' . \Carbon\Carbon::now()->timestamp }}"></script>
    <script>
            $('.detail_debit').on('input','input.customer_paid_string', function(e){  
                $(this).val(formatCurrency(this));
            }).on('keypress','input.customer_paid_string',function(e){
                if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
            }).on('paste','input.customer_paid_string', function(e){   
                var cb = e.originalEvent.clipboardData || window.clipboardData;      
                if(!$.isNumeric(cb.getData('text'))) e.preventDefault();
            });
           $('.change_status').click(function (e) { 
                e.preventDefault();
                $('#view_confirm_apartment').modal('show');
                var apartments = $(this).attr('data-apartments');
                let object_apartments = apartments ? JSON.parse(apartments) : null;
                $('#id_history').val($(this).attr('data-id'));
                $('#id_trans').val($(this).attr('data-trans_id'));
                if(object_apartments && object_apartments.length > 0){
                    $.each(object_apartments,function (i, item) {
                        $('._list_aparmtent').append(('<span class="label label-sm label-info"> '+item.name+'</span>'));
                    });
                }
              

                
           });
           async function import_excel() {
            let method = 'post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var form_data = new FormData($('#form-transaction-vietqr')[0]);
            var export_excel = await call_api_export(method, 'payment/impExlPayment' + param_query,form_data)
           }
           $('.save_status').click(function (e) { 
            e.preventDefault();
            $.ajax({
                    url: "{{route('admin.history-transaction-accounting.confirm_transaction')}}",
                    type: 'PUT',
                    data: {
                        id: $('#id_history').val(),
                        tranId: $('#id_trans').val(),
                        apartment_id: $('#ip-apartment').val()
                    },
                    success: function (response) {
                        toastr.success(response.message);
                        setTimeout(() => {
                                location.reload()
                        }, 2000)
                    }
                });
           });
           get_data_select({
                object: '#ip-apartment,#ip_apartment',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            $("#ip-place_id").on('change', function(){ 
                if($("#ip-place_id").val()){
                    get_data_select({
                    object: '#ip-apartment,#ip_apartment',
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
    </script>
@endsection