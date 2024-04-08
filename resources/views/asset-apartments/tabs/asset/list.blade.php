@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Tài sản-Lịch bảo trì</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý tài sản</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="{{ str_contains(url()->current(),'handover') ? 'active' : null }}"><a href="{{ route('admin.asset-apartment.asset-handover.index') }}" >Bàn giao tài sản</a></li>
                            <li class="{{ !str_contains(url()->current(),'handover') ? 'active' : null }}"><a href="{{ route('admin.asset-apartment.asset.index') }}" >Tài sản</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane {{ !str_contains(url()->current(),'handover') ? 'active' : null }}" id="{{ route('admin.asset-apartment.asset.index') }}">
                                <div class="row form-group">
                                        <div class="col-12 col-md-2">
                                            <a href="{{ route('admin.asset-apartment.asset.create') }}" class="btn btn-success"><i class="fa fa-edit"></i>Thêm tài sản</a>
                                            <a href="{{ route('admin.asset-apartment.asset.import') }}" class="btn btn-success "><i class="fa fa-edit"></i>Import</a>
                                        </div>
                                        <div class="col-12 col-md-8">
                                                <form id="form-search-advance" action="{{ route('admin.asset-apartment.asset.index') }}" method="get">
                                                    <div id="search-advance" class="search-advance">
                                                        <div class="row">
                                                            <div class="col-sm-1">
                                                                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a href="javascript:" type="button" class="btn-action" data-target="#form-asset" data-method="delete">
                                                                            <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <input type="text" name="keyword" class="form-control"
                                                                    placeholder="Nhập mã tài sản, tên tài sản" value="{{ @$filter['keyword'] }}">
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form><!-- END #form-search-advance -->
                                        </div>
                                        <div class="col-12 col-md-1">
                                            <a href="{{ route('admin.asset-apartment.asset.export') }}" class="btn btn-success"><i class="fa fa-edit"></i>Export</a>
                                        </div>
                                </div>
                                <form id="form-asset" action="{{ route('admin.asset-apartment.asset.action') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="method" value="" />
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                                    <th>STT</th>
                                                    <th>Mã tài sản</th>
                                                    <th>Tên tài sản</th>
                                                    <th>Loại tài sản</th>
                                                    <th>Căn hộ</th>
                                                    <th>Tòa nhà</th>
                                                    <th>Ghi chú</th>
                                                    <th width="10%">Thao tác</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @if($assets->count() > 0)
                                                            @foreach($assets as $key => $value)
                                                                <tr>
                                                                    @if(!@$value->apartment)
                                                                       <td><input type="checkbox" name="ids[]" value="{{ $value->id }}" class="iCheck checkSingle" /></td>
                                                                    @else
                                                                       <td></td>
                                                                    @endif
                                                                    <td>{{ @($key + 1) + ($assets->currentPage() - 1) * $assets->perPage() }}</td>
                                                                    <td>{{ $value->code }}</td>
                                                                    <td>{{ $value->name }}</td>
                                                                    <td>{{ @$value->asset_category->title }}</td>
                                                                    <td>{{ @$value->apartment->name }}</td>
                                                                    <td>{{ @$value->building_place->name }}</td>
                                                                    <td>{{ $value->description }}</td>
                                                                    <td>
                                                                        @if(!@$value->apartment)
                                                                        <a href="{{ route('admin.asset-apartment.asset.edit', $value->id) }}"
                                                                            class="btn btn-xs btn-primary" title="Sửa tài sản"><i
                                                                                        class="fa fa-pencil"></i></a>
                                                                        @endif
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
                                                    <span class="record-total">Hiển thị {{ $assets->count() }} / {{ $assets->total() }} kết quả</span>
                                                </div>
                                                <div class="col-sm-6 text-center">
                                                    <div class="pagination-panel">
                                                        {{ $assets->appends(request()->input())->links() }}
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
