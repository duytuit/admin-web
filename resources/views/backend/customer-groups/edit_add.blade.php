@extends('backend.layouts.master')

@section('stylesheet')
<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
@endsection

@section('content')
<section class="content-header">
    <h1>
        Nhóm khách hàng
        <small>Cập nhật</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Khách hàng</li>
    </ol>
</section>

@can('view', app(App\Models\CustomerGroup::class))
<section class="content">
    <div class="row">
        <div class="col-sm-8 col-xs-12">
            <div class="box no-border-top">
                <div class="box-body no-padding">
                    <div class="nav-tabs-custom no-margin">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#infomation" data-toggle="tab">Thông tin cơ bản</a></li>
                            @if( $id )
                            <li class=""><a href="#customers" data-toggle="tab">Danh sách thành viên</a></li>
                            @endif
                        </ul>

                        <div class="tab-content">
                            <!-- Thông tin cơ bản nhóm khách hàng -->

                            <div class="tab-pane active" id="infomation">
                                <form action="" method="post" id="form-edit-add-group" class="form-validate" autocomplete="off">
                                    {{ csrf_field() }}
                                    <div class="form-group {{ $errors->has('name') ? 'has-error': '' }}">
                                        <label class="control-label">Tên nhóm khách hàng <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" placeholder="Tên nhóm khách hàng" value="{{ $group->name ?? old('name') ?? ''}}" />
                                        @if ($errors->has('name'))
                                        <em class="help-block">{{ $errors->first('name') }}</em>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Trạng thái</label><br />
                                        <div>
                                            @php
                                            $group_status = $group->status ?? old('group_status') ?? '';
                                            @endphp
                                            <label class="switch">
                                                <input type="checkbox" name="group_status" value="1" {{ $group->status === '0'  ? '' : 'checked' }} />
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Tiêu chí nhóm khách hàng --}}
                                    @include('backend.customer-groups.sub-views.criterion')

                                    <hr />
                                    @can('update', app(App\Models\CustomerGroup::class))
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-group">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;{{ $id ? 'Cập nhật' : 'Thêm mới'}}
                                    </button>
                                    @endcan
                                </form>
                            </div>

                            <!-- Danh sách khách hàng thuộc nhóm -->
                            @if($id)
                            <div class="tab-pane" id="customers">
                                @php
                                $customers = $group->getCustomer()->paginate(20);
                                @endphp
                                @if(!empty($customers->items()))
                                <form action="{{ url('admin/customer-groups/delete-customer') }}" method="post" id="form-delete-customer">
                                    {{ csrf_field() }}
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr class="bg-primary">
                                                <th width='20px'>
                                                    <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                                </th>
                                                <th width='15px' class="text-center">#</th>
                                                <th>Họ tên</th>
                                                <th width='15%'>Email</th>
                                                <th width='15%'>SĐT</th>
                                                <th width='30%'>Địa chỉ</th>
                                                <th width='11%'>Trạng thái</th>
                                                <th width='7%'>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <input type="hidden" name="group" value="{{ $group->id }}" />
                                            <input type="hidden" name="hashtag_group" value="" />
                                            @foreach( $customers as $customer )
                                            <tr>
                                                <td><input type="checkbox" class="iCheck checkSingle" value="{{ $customer->cb_id }}" name="ids[]" /></td>
                                                <td>{{ $customer->id }}</td>
                                                <td>
                                                    <a href='{{ url("/admin/bo-customers/edit/{$customer->cb_id}") }}'> {{ $customer->cb_name }} </a>
                                                </td>
                                                <td>{{ $customer->cb_email }}</td>
                                                <td>{{ $customer->cb_phone }}</td>
                                                <td>{!! $customer->address !!}</td>
                                                <td>
                                                    <span class="btn-status label label-sm label-{{ $customer->status == 1 ? 'success' : 'danger' }}">{{ $customer->status == 1 ? 'Quan tâm' : 'Không quan tâm' }}</span>
                                                </td>
                                                <td>
                                                    @can('delete.customer', app(App\Models\CustomerGroup::class))
                                                    <a title="Xóa" href="javascript:;" data-url="{{ url('admin/customer-groups/delete-customer') }}" data-group={{ $group->id }} data-id="{{ $customer->cb_id }}" class="btn btn-sm btn-remove btn-danger"><i class="fa fa-trash"></i></a>
                                                    @endcan
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </form>

                                <hr />
                                @can('delete.customer', app(App\Models\CustomerGroup::class))
                                <div class="pull-left link-paginate">
                                    <button type="submit" class="btn btn-danger btn-delete-group btn-sm"><i class="fa fa-trash"></i> Xóa nhiều</button>
                                </div>
                                @endcan

                                <div class="pull-right link-paginate">
                                    {{ $customers->fragment('customers')->links() }}
                                </div>
                                <div class="clearfix"></div>
                                @else
                                Hiện chưa có chi nhánh nào.
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endcan
@endsection