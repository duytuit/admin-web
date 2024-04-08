@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Chọn dịch vụ tòa nhà</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý dịch vụ</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <form action="{{route('admin.v2.service.building.postChoose')}}" method="post">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle"/></th>
                                <th>STT</th>
                                <th>Tên dịch vụ</th>
                                <th>Kỳ TT</th>
                                <th>Ngày chốt</th>
                                <th>Hạn thanh toán</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($services->count() > 0)
                                @foreach($services as $key => $service)
                                        <tr>
                                            <td><input @if(in_array($service->service_code, array_keys($buildingServices))) checked @endif @if(in_array($service->service_code, array_keys($buildingServices)) && $buildingServices[$service->service_code]) disabled @endif type="checkbox" name="ids[]" value="{{ $service->id }}"
                                                       class="iCheck checkSingle"/></td>
                                            <td>{{ @($key + 1) + ($services->currentPage() - 1) * $services->perPage() }}</td>
                                            <td>{{ @$service->name }}</td>
                                            <td>{{ @$service->period->name }}</td>
                                            <td>{{ @$service->bill_date }}</td>
                                            <td>{{ @$service->payment_deadline }}</td>
                                        </tr>
                                    @endforeach
                            @else
                                <tr>Không có kết quả tìm kiếm</tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm-4">
                            <a style="border: none" href="{{ route('admin.v2.service.building.create') }}"><i
                                        class="fa fa-plus" aria-hidden="true"></i>
                                Thêm dịch vụ tòa nhà</a>
                        </div>
                        <div class="col-sm-2 text-center">
                            <button type="submit" class="btn btn-success"><i class="fa fa-floppy-o"
                                                                             aria-hidden="true"></i>
                                Áp dụng
                            </button>
                        </div>
                        <div class="col-sm-2 text-center">
                            <a href="{{ route('admin.v2.service.building.index') }}" class="btn btn-warning"><i
                                        class="fa fa-ban" aria-hidden="true"></i>
                                Hủy</a>
                        </div>
                        <div class="col-sm-4">
                        </div>
                    </div>
                </form>
                <form id="form-service-building" action="{{route('admin.v2.service.building.action')}}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="">
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $services->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $services->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-service-building">
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
@endsection

@section('javascript')
@endsection