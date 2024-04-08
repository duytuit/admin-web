@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách chi tiết thu tiền
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">chi tiết thu tiền</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
                <div class="row form-group">
                    <div class="col-12 col-md-8">
                        <form id="form-search" action="{{ route('admin.paymen-detail.index') }}" method="get">
                            <div class="col-sm-4">
                                <input type="text" name="keyword" value="{{ @$filter['keyword'] }}" placeholder="tìm kiếm mã phiếu thu, căn hộ, chủ hộ" class="form-control" />
                            </div>
                            <div class="col-sm-2">
                                <select name="cycle_name" class="form-control">
                                    <option value="" selected>Kì bảng kê</option>
                                    @foreach($cycle_names as $cycle_name)
                                        <option value="{{ $cycle_name }}"  @if(@$filter['cycle_name'] ==  $cycle_name) selected @endif>{{ $cycle_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select name="bdc_service_id" class="form-control select2" style="with:100%">
                                    <option value="" selected>Dịch vụ...</option>
                                    @foreach($serviceBuildingFilter as $serviceBuilding)
                                        <option value="{{ $serviceBuilding->id }}"  @if(@$filter['bdc_service_id'] ==  $serviceBuilding->id) selected @endif>{{ $serviceBuilding->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-1">
                                <button type="submit" class="btn btn-info"><span class="fa fa-search"></span></button>
                            </div>
                        </form><!-- END #form-search -->
                    </div>
                    <div class="col-12 col-md-2">
                        <a href="{{ route('admin.v2.receipt.create') }}" class="btn btn-warning">Lập phiếu thu</a>
                        <a href="{{ route('admin.paymen-detail.export', Request::all()) }}" class="btn btn-success"><i class="fa fa-edit"></i>Export</a>
                    </div>
            </div>
                <form id="form-paymen-detail" action="{{ route('admin.paymen-detail.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th width="3%">STT</th>
                                    <th >Mã phiếu thu</th>
                                    <th >Căn hộ</th>
                                    <th >Khách hàng</th>
                                    <th >Dịch vụ</th>
                                    <th >Sản phẩm</th>
                                    <th >Kỳ</th>
                                    <th >Số tiền</th>
                                    <th >Người thu</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($payment_details as $key => $item)
                                <tr>
                                    <td>{{ @($key + 1) + ($payment_details->currentPage() - 1) * $payment_details->perPage() }}</td>
                                    <td>{{ @$item->receipt->receipt_code }}</td>
                                    <td>{{ @$item->receipt->customer_address }}</td>
                                    <td>{{ @$item->receipt->customer_name }}</td>
                                    <td>{{ @$item->service->name }}</td>
                                    <td>{{ @$item->debitdetail->title }}</td>
                                    <td>{{ $item->cycle_name }}</td>
                                    <td>{{ $item->cost }}</td>
                                    <td>
                                        <small>
                                            {{ @$item->user->email }}<br />
                                            {{ $item->updated_at->format('d-m-Y H:i') }}
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $payment_details->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $payment_details->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control" data-target="#form-paymen-detail">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>
                </form><!-- END #form-paymen-detail -->
        </div>
    </div>
</section>

@endsection