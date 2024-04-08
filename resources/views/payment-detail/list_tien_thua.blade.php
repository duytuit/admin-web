@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Tiền thừa</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý tiền thừa</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="{{ str_contains(url()->current(),'tien-thua') ? 'active' : null }}"><a href="{{ route('admin.paymen-detail.tien_thua') }}" >Quản lý tiền thừa</a></li>
                            <li class="{{ !str_contains(url()->current(),'tien-thua') ? 'active' : null }}"><a href="{{ route('admin.paymen-detail.hach_toan') }}" >Hạch toán tiền thừa</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane {{ str_contains(url()->current(),'tien-thua') ? 'active' : null }}" id="{{ route('admin.paymen-detail.tien_thua') }}">
                                <div class="row form-group">
                                        <div class="col-12 col-md-8">
                                                <form id="form-search-advance" action="{{ route('admin.paymen-detail.tien_thua') }}" method="get">
                                                    <div id="search-advance" class="search-advance">
                                                        <div class="row">
                                                            <div class="col-md-1">
                                                                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <input type="text" name="keyword" class="form-control"
                                                                    placeholder="Tìm kiếm" value="{{ @$filter['keyword'] }}">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <button class="btn btn-info search-asset"><i class="fa fa-search"></i>Tìm</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form><!-- END #form-search-advance -->
                                        </div>
                                        <div class="col-12 col-md-1">
                                            <a href="{{ route('admin.v2.receipt.create') }}" class="btn btn-warning">Lập phiếu thu</a>
                                            <a href="{{ route('admin.paymen-detail.tien_thua.export') }}" class="btn btn-success"><i class="fa fa-edit"></i>Export</a>
                                        </div>
                                </div>
                                <form id="form-asset" action="{{ route('admin.paymen-detail.tien_thua.action') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="method" value="" />
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Căn hộ</th>
                                                    <th>Tòa</th>
                                                    <th>Khách hàng</th>
                                                    <th>Mã phiếu thu</th>
                                                    <th>Tiền thừa</th>
                                                    <th>Người thu</th>
                                                    <th width="10%">Thao tác</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @if(@$tien_thua->count() > 0)
                                                            @foreach($tien_thua as $key => $value)
                                                                <tr>
                                                                    <td>{{ @($key + 1) + ($tien_thua->currentPage() - 1) * $tien_thua->perPage() }}</td>
                                                                    <td>{{ @$value->apartment->name }}</td>
                                                                    <td>{{ @$value->apartment->buildingPlace->name }}</td>
                                                                    <td>{{ @$value->receipt->customer_name }}</td>
                                                                    <td>{{ @$value->receipt->receipt_code }}</td>
                                                                    <td>{{ number_format(@$value->cost) }}</td>
                                                                    <td>
                                                                        <small>
                                                                            {{ @$value->user->email }}<br />
                                                                            {{ $value->updated_at->format('d-m-Y H:i') }}
                                                                        </small>
                                                                    </td>
                                                                    <td>
                                                                        <a class="btn-show" href="javascript:;" data-url="{{ route('admin.paymen-detail.tien_thua.show') }}" data-id="{{ @$value->bdc_receipt_id }}"  title="Chi tiết tiền thừa"><i class="fa fa-external-link-square"></i> Chi tiết</a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                    @else
                                                        <tr><td colspan="12" class="text-center">Không có kết quả tìm kiếm</td></tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row mbm">
                                               <div class="col-sm-3">
                                                    <span class="record-total">Hiển thị {{ $tien_thua->count() }} / {{ $tien_thua->total() }} kết quả</span>
                                                </div>
                                                <div class="col-sm-6 text-center">
                                                    <div class="pagination-panel">
                                                        {{ $tien_thua->appends(request()->input())->links() }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 text-right">
                                                    <span class="form-inline">
                                                        Hiển thị
                                                        <select name="per_page" class="form-control" data-target="#form-asset">
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
    <div class="modal fade" id="ShowReviewReceipt" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title" style="display: inline-block;
                    font-size: 25px;">Chi tiết phiếu thu</div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body debit_detail_content" id="modal-detail-receipt"></div>
            </div>
        </div>
    </div>
@endsection
<style>
    @media (min-width: 768px)
    {    
         .modal-dialog {
            width: 860px !important;
            margin: 180px auto !important;
        }
    }
        
</style>
@section('javascript')
    <script>
            // .btn-show
            $('.btn-show').click(function () {
                let url = $(this).data('url');
                let id =  $(this).data('id');
                showLoading();
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        id: id
                    },
                    success: function (response) {
                        hideLoading();
                        if (response.error_code == 200) {
                            $('#modal-detail-receipt').html(response.data.html);
                            $('#ShowReviewReceipt').modal('show');
                        } 
                    
                    },
                    error: function (response) {
                        hideLoading();
                        if (response.responseJSON.success == false) {
                            toastr.warning(response.responseJSON.message);
                        } 
                    }
                });
            })
    </script>
@endsection
