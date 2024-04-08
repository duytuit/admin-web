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
                    <small>(Đã xử lý công nợ kì hiện tại - <i class="text-red"
                                                              style="font-weight: bolder">{{\Carbon\Carbon::now()->month}}
                            /{{\Carbon\Carbon::now()->year}}</i>)
                    </small>
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
                        <a href="" class="btn bg-olive"><i class="fa fa-file-excel-o"></i>
                            Export excel</a>
                    </div>
                </div>
                <form id="form-search-advance" action="{{route('admin.debit.detail')}}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="row space-5">
                            <div class="col-sm-2">
                                <select name="bdc_building_id" class="form-control building-list">
                                    <option value="" selected>Tòa nhà</option>
                                    @foreach($buildings as $building)
                                        <option value="{{ $building->id }}"  @if(@$filter['bdc_building_id'] ==  $building->id) selected @endif>{{ $building->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select name="bdc_apartment_id" class="form-control apartment-list">
                                    <option value="" selected>Căn hộ</option>
                                    @if(isset($apartments))
                                        @foreach($apartments as $key => $apartment)
                                            <option value="{{ $key }}"  @if(@$filter['bdc_apartment_id'] ==  $key) selected @endif>{{ $apartment }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select name="bdc_service_id" class="form-control">
                                    <option value="" selected>Dịch vụ</option>
                                    @foreach($serviceBuildingFilter as $serviceBuilding)
                                        <option value="{{ $serviceBuilding->id }}"  @if(@$filter['bdc_service_id'] ==  $serviceBuilding->id) selected @endif>{{ $serviceBuilding->name }}</option><th colspan="2">{{$serviceBuilding->name}}</th>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <form id="form-permission" action="{{ route('admin.debit.detail.action') }}" method="post">
                    @csrf
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th rowspan="2">STT</th>
                            <th rowspan="2">Căn hộ</th>
                            @foreach($serviceBuildings as $serviceBuilding)
                                <th colspan="2">{{$serviceBuilding->name}}</th>
                            @endforeach
                            <th rowspan="2">Tổng nợ</th>
                            <th rowspan="2">Tổng phát sinh</th>
                        </tr>
                        <tr>
                            @foreach($serviceBuildings as $serviceBuilding)
                                <th>Nợ</th>
                                <th>P.s</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @if($debits->count() > 0)
                        @foreach($debits as $key => $debit)
                            @php
                                $sumery = 0;
                                $paid = 0;
                            @endphp
                            <tr>
                                <td>{{ array_search($key, $apartmentsUseService) + 1}}</td>
                                <td>{{ $apartmentShow[$key] }}</td>
                                @foreach($serviceBuildings as $serviceBuilding)
                                    @php
                                        $sumery += $debit[$serviceBuilding->id]['sumery'];
                                        $paid += $debit[$serviceBuilding->id]['paid'];
                                    @endphp
                                    <td style="text-align: right;">{{ number_format($debit[$serviceBuilding->id]['sumery']) }}</td>
                                    <td style="text-align: right;">{{ number_format($debit[$serviceBuilding->id]['paid']) }}</td>
                                @endforeach
                                <td style="text-align: right;">{{ number_format($sumery) }}</td>
                                <td style="text-align: right;">{{ number_format($paid) }}</td>
                            </tr>
                        @endforeach
                            @else
                            <tr><td colspan="{{ $serviceBuildings->count() + 4 }}" class="text-center">Không có kết quả tìm kiếm</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Hiển thị {{ $debits->count() }} / {{ $debits->total() }} kết quả</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $debits->appends(request()->input())->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-permission">
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
    </section>
    @include('debit.modal.make_receipt_detail')
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
    <!-- TinyMCE -->
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script>
        //Date picker
        $('input[type="date"]').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
    </script>
    <script>
        $(document).on('change', '.building-list', function (e) {
            e.preventDefault();
            var id = $(this).children(":selected").val();
            $.ajax({
                url: '{{route('admin.debit.getApartment')}}',
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