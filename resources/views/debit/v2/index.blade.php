@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Quản lý công nợ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý công nợ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Công nợ tổng hợp
                </h3>
            </div>
            <div class="box-body">
                {{-- @include('layouts.head-building') --}}
            </div>
            <div class="box-body">
                <div class="row form-group pull-right">
                    <div class=" col-md-12 ">
                        <a data-toggle="modal" data-target="#showModal" class="btn btn-primary"><i
                                    class="fa fa-scissors"></i>
                            Xử lý công nợ</a>
                        <a href="{{ route('admin.receipt.create') }}" class="btn bg-orange"><i class="fa fa-money"></i>
                            Lập phiếu thu</a>
                        <?php
                            $param = "?bdc_apartment_id=".@$filter['bdc_apartment_id']."&from_date=".@$filter['from_date']."&to_date=".@$filter['to_date']."&status=".@$filter['status'];
                        ?>
                        <a href="{{ route('admin.v2.debit.exportFilter') }}{{$param}}" class="btn bg-olive"><i class="fa fa-file-excel-o"></i>
                            Export excel</a>
                        <a href="{{ route('admin.progressive.importexcel') }}" class="btn btn-success">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            Import điện nước
                        </a>
                    </div>
                </div>
                <form id="form-search-advance" action="{{route('admin.v2.debit.index')}}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="row space-5">
                            {{-- <div class="col-md-1">
                                <select name="bdc_building_id" class="form-control building-list">
                                    <option value="" selected>Tòa nhà</option>
                                    @foreach($buildings as $building)
                                        <option value="{{ $building->id }}"  @if(@$filter['bdc_building_id'] ==  $building->id) selected @endif>{{ $building->name }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="col-md-1 search-advance">
                                <select name="bdc_apartment_id" class="form-control apartment-list selectpicker" data-live-search="true">
                                    <option value="" selected>Căn hộ</option>
                                    @if(isset($apartments))
                                        @foreach($apartments as $key => $apartment)
                                            <option value="{{ $key }}"  @if(@$filter['bdc_apartment_id'] ==  $key) selected @endif>{{ $apartment }}</option>
                                        @endforeach
                                        @endif
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="from_date" id="from_date" value="{{@$filter['from_date']}}" placeholder="Từ..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="to_date" id="to_date" value="{{@$filter['to_date']}}" placeholder="Đến..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <select name="status" class="form-control status-list ">
                                    <option value="" selected> Trạng thái</option>
                                    <option value="0" @if(@$filter['status'] == 0) selected @endif>Chờ thanh toán</option>
                                    <option value="1" @if(@$filter['status'] == 1) selected @endif>Đã thanh toán</option>
                                    <option value="2" @if(@$filter['status'] == 2) selected @endif>Quá hạn</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th>STT</th>
                            <th>Tên KH</th>
                            <th>Căn hộ</th>
                            <th>Tòa nhà</th>
                            {{-- <th>Dư nợ đầu kỳ</th> --}}
                            <th>Nợ phát sinh trong kỳ</th>
                            <th>Đã thanh toán</th>
                            <th>Dư nợ cuối kỳ</th>
                            <th>Kỳ</th>
                            <th>TT lần cuối</th>
                            {{-- <th>T.Toán</th> --}}
                            {{-- <th>Trạng thái</th> --}}
                        </tr>
                        </thead>
                        <tbody>
                        @if($debits->count() > 0)
                            @foreach($debits as $key => $debit)
                                <tr>
                                    <td>{{ @($key + 1) + ($debits->currentpage() - 1) * $debits->perPage()  }}</td>
                                    <td>@foreach(@$debit->apartment->bdcCustomers as $value)
                                            @if(@$value->type == 0)
                                                {{@$value->pubUserProfile->display_name}}
                                            @endif
                                        @endforeach</td>
                                    <td><a href="{{route('admin.v2.debit.show',$debit->bdc_apartment_id)}}">{{@$debit->apartment->name}}</a></td>
                                    <th>{{@$debit->building->name}}</th>
                                    {{-- <td>{{number_format(@$debit->previous_owed)}}</td> --}}
                                    <td style="text-align: right;">{{number_format(@$debit->total)}}</td>
                                    <td style="text-align: right;">{{number_format(@$debit->total_paid)}}</td>
                                    <td style="text-align: right;">{{number_format(@$debit->total - @$debit->total_paid - @$debit->total_free)}}</td>
                                    <td >{{@$debit->name}}</td>
                                    <td>{{date('d/m/Y', strtotime(@$debit->created_at))}}</td>
                                    {{-- <td><a href="" class="btn bg-yellow">Lập PT</a></td> --}}
                                    {{-- <td>
                                        @if(@$debit->status == 0)
                                            <a class="btn btn-info">Chờ thanh toán</a>
                                        @elseif(@$debit->status == 1)
                                            <a class="btn btn-success">Đã thanh toán</a>
                                        @else
                                            <a href="" class="btn bg-danger">Đã quá hạn, nhắc?</a>
                                        @endif
                                    </td> --}}
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
                @php
                    $month = isset($filter['month']) ? $filter['month'] : date('m');
                @endphp
                <a href="{{route('admin.v2.debit.detail', ['month' => $month])}}" class=""><h4><i class="fa fa-hand-o-right"
                                                                                     aria-hidden="true"></i>
                    Chi tiết công nợ</h4></a>
                <form id="form-service-company" action="{{route('admin.service.company.action')}}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="">
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $debits->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $debits->appends(Request::all())->onEachSide(1)->links() }}
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
                </form>
            </div>
        </div>
    </section>
    @include('debit.v2.modal.make_receipt_detail')
@endsection
@section('stylesheet')
    <style>
        input.check-check {
            /* Double-sized Checkboxes */
            -ms-transform: scale(2); /* IE */
            -moz-transform: scale(2); /* FF */
            -webkit-transform: scale(2); /* Safari and Chrome */
            -o-transform: scale(2); /* Opera */
            padding: 10px;
        }

    </style>
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function () {
            $(document).on('change', '.building-list', function (e) {
                e.preventDefault();
                var id = $(this).children(":selected").val();
                $.ajax({
                    url: '{{route('admin.v2.debit.getApartment')}}',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {
                        var $apartment = $('.apartment-list');
                        $apartment.empty();
                        $apartment.append('<option value="" selected>Căn hộ</option>');
                        $.each(response, function (index, val) {
                            if (index != 'debug') {
                                $apartment.append('<option value="' + index + '">' + val + '</option>')
                            }
                        });
                    }
                })
            });
            $("#datepicker").datepicker({
                format: "mm-yyyy",
                autoclose: true,
                viewMode: "months",
                minViewMode: "months"
            }).val();
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#myCheckAll').change(function () {
                if ($(this).is(":checked")) {
                    $('.checkboxes').prop("checked", true);
                    $('.checkboxes').val(1);
                } else {
                    $('.checkboxes').prop("checked", false);
                    $('.checkboxes').val(0);
                }
            });
            //Date picker
            $('input.date_picker').datepicker({
                autoclose: true,
                dateFormat: "dd-mm-yy"
            }).val();

            $('.frees').change(function () {
                if (this.checked) {
                    $(this).val(1);
                } else {
                    $(this).val(0);
                }
            });
        });
    </script>

@endsection