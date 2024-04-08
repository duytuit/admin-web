@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Hạch toán tiền thừa</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý hạch toán tiền thừa</li>
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
                            <div class="tab-pane {{ !str_contains(url()->current(),'tien-thua') ? 'active' : null }}" id="{{ route('admin.paymen-detail.hach_toan') }}">
                                <div class="row form-group">
                                        <div class="col-12 col-md-8">
                                                <form id="form-search-advance" action="{{ route('admin.paymen-detail.hach_toan') }}" method="get">
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
                                            <a href="{{ route('admin.paymen-detail.hach_toan.export') }}" class="btn btn-success"><i class="fa fa-edit"></i>Export</a>
                                        </div>
                                </div>
                                <form id="form-asset" action="{{ route('admin.paymen-detail.hach_toan.action') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="method" value="" />
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Mã phiếu thu</th>
                                                    <th>Số tiền</th>
                                                    <th>Công nợ dịch vụ</th>
                                                    <th>Kỳ</th>
                                                    <th>Căn hộ</th>
                                                    <th>Bảng kê</th>
                                                    <th>Người hạch toán</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @if(@$hach_toan->count() > 0)
                                                            @foreach($hach_toan as $key => $value)
                                                                <tr>
                                                                    <td>{{ @($key + 1) + ($hach_toan->currentPage() - 1) * $hach_toan->perPage() }}</td>
                                                                    <td>{{ @$value->receipt->receipt_code }}</td>
                                                                    <td>{{ number_format(@$value->cost) }}</td>
                                                                    <td>{{ @$value->debitdetail->title }}</td>
                                                                    <td>{{ @$value->debitdetail->cycle_name }}</td>
                                                                    <td>{{ @$value->apartment->name }}</td>
                                                                    <td>{{ @$value->debitdetail->bill->bill_code }}</td>
                                                                    <td>
                                                                        <small>
                                                                            {{ @$value->user->email }}<br />
                                                                            {{ $value->updated_at->format('d-m-Y H:i') }}
                                                                        </small>
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
                                                    <span class="record-total">Hiển thị {{ $hach_toan->count() }} / {{ $hach_toan->total() }} kết quả</span>
                                                </div>
                                                <div class="col-sm-6 text-center">
                                                    <div class="pagination-panel">
                                                        {{ $hach_toan->appends(request()->input())->links() }}
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
@endsection

@section('javascript')
    <script>
    </script>
@endsection
