@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết công nợ căn hộ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết công nợ căn hộ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Chi tiết công nợ căn hộ
                </h3>
            </div>
            <div class="box-body">
                {{-- @include('layouts.head-building') --}}
            </div>
            <div class="box-body">
                <div class="row form-group">
                    <div class="col-sm-4 col-xs-12">
{{--                        <form id="form-search-service" action="{{route('admin.debit.show',$apartment->bdc_apartment_id)}}" method="get">--}}
{{--                            <div class="input-group">--}}
{{--                                <input type="text" class="form-control" name="name" placeholder="Nhập tên dịch vụ"--}}
{{--                                       value="{{@$filter['name']}}">--}}
{{--                                <div class="input-group-btn">--}}
{{--                                    <button type="submit" title="Tìm kiếm" form="form-search-service"--}}
{{--                                            class="btn btn-info submit-search"><i--}}
{{--                                                class="fa fa-search"></i></button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </form>--}}
                    </div>
                    <div class="col-sm-4">
                    </div>
                    <div class="col-sm-4">
                        <a href="{{route('admin.debit.exportShow',$apartment->bdc_apartment_id)}}"
                           class="btn btn-success pull-right"> <i class="fa fa-file-excel-o"></i>  Export excel</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th>STT</th>
                            <th>Căn hộ</th>
                            <th>Tháng</th>
                            <th>Dịch vụ</th>
                            <th>Tổng công nợ</th>
                            <th>Đã thanh toán</th>
                            <th>Còn nợ</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($debits as $lock => $debit)
                                <?php
                                    $_apartment = $apartmentRepository->findById($debit->bdc_apartment_id);
                                ?>
                                <tr>
                                    <td>{{ @($lock + 1) }}</td>
                                    <td>{{@$_apartment->name}}</td>
                                    <td>{{date('m', strtotime(@$debit->created_at))}}</td>
                                    <th>{{@$debit->title}}</th>
                                    <td style="text-align: right;">{{number_format(@$debit->sumery)}}</td>
                                    <td style="text-align: right;">
                                        {{number_format(@$debit->sumery - @$debit->new_sumery)}}

                                    </td>
                                    <td style="text-align: right;">
                                        {{number_format(@$debit->new_sumery)}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- <form id="form-service-company" action="{{route('admin.service.company.action')}}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="">
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $pagination->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $pagination->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-service-company">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                        </div>
                    </div>
                </form> --}}
            </div>
        </div>
    </section>
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>

@endsection