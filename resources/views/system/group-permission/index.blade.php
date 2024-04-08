@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            {{$meta_title}}
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Nhóm quyền</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <div class="row form-group">
                    <div class="col-sm-12 pull-right">
                        <a href="{{ route('admin.system.group_permission.create') }}" class="btn btn-info"><i class="fa fa-edit"></i>
                           Thêm nhóm quyền</a>
                    </div>
                </div>
                <form id="form-search-advance" action="{{ route('admin.system.group_permission.index') }}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="row form-group space-5">
                            <div class="col-sm-3">
                                <input type="text" name="name" value="{{ @$data_search['name'] }}"
                                       placeholder="Tên, Email, SĐT, Tavico" class="form-control"/>
                            </div>
                            {{--<div class="col-sm-3">
                                <select name="group_ids" class="form-control select2" style="width: 100%;">
                                    <option value="">Phòng ban</option>
                                    @foreach ($groups as $item)
                                        <option value="{{ $item->gb_id }}" {{ $item->gb_id == $group_ids ? 'selected' : '' }}>{{ $item->gb_title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select name="status" class="form-control">
                                    <option value="">Trạng thái</option>
                                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>--}}
                            <div class="col-sm-1">
                                <button class="btn btn-primary btn-block"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <form id="form-users" action="{{ route('admin.users.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value=""/>
                    <input type="hidden" name="status" value=""/>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th width="140">Tên nhóm</th>
                                <th width="160">Mô tả</th>
                                <th width="5%">Thao tác</th>
{{--                                <th width="140">Người tạo</th>--}}
{{--                                <th width="110">Người update</th>--}}
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($groups as $item)
                                <tr valign="middle">
                                    <td><a href="{{route('admin.system.group_permission.edit',['id'=>$item->id])}}">{{ $item->name?:'' }}</a></td>
                                    <td>{{ $item->description }}</td>
                                    {{--<td>{{ @$item->pubUser->display_name }}</td>
                                    <td>{{ @$item->update_by }}</td>--}}
                                    @if($item->status != 1)
                                        <td><a href="{{route('admin.system.group_permission.destroy',['id'=>$item->id])}}"  class="btn btn-danger"><i class="fa fa-trash-o"></i></a></td>
                                    @else
                                        <td></td>
                                    @endif

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $groups->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $groups->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-users">
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
@endsection
@section('stylesheet')
    <style>
        .onoffswitch {
            position: relative;
            width: 70px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .onoffswitch-checkbox {
            display: none;
        }

        .onoffswitch-label {
            display: block;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #999999;
            border-radius: 16px;
        }

        .onoffswitch-inner {
            display: block;
            width: 200%;
            margin-left: -100%;
            transition: margin 0.3s ease-in 0s;
        }

        .onoffswitch-inner:before, .onoffswitch-inner:after {
            display: block;
            float: left;
            width: 50%;
            height: 21px;
            padding: 0;
            line-height: 21px;
            font-size: 9px;
            color: white;
            font-family: Trebuchet, Arial, sans-serif;
            font-weight: bold;
            box-sizing: border-box;
        }

        .onoffswitch-inner:before {
            content: "ACTIVE";
            padding-left: 12px;
            background-color: #00C0EF;
            color: #FFFFFF;
        }

        .onoffswitch-inner:after {
            content: "INACTIVE";
            background-color: #EEEEEE;
            color: #999999;
            text-align: right;
        }

        .onoffswitch-switch {
            display: block;
            width: 23px;
            height: 23px;
            margin: 1px;
            background: #FFFFFF;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 45px;
            border: 2px solid #999999;
            border-radius: 16px;
            transition: all 0.3s ease-in 0s;
        }

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
            margin-left: 0;
        }

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right: 0px;
        }
    </style>
@endsection
@section('javascript')

    <script>
        sidebar('users', 'users');
        //onoff status
        var requestSend = false;
        $(document).on('click', '.onoffswitch-label', function (e) {
            var div = $(this).parents('div.onoffswitch');
            var input = div.find('input');
            var id = input.attr('data-id');
            if (input.attr('checked')) {
                var checked = 0;
            } else {
                var checked = 1;
            }
            if (!requestSend) {
                requestSend = true;
                $.ajax({
                    url: input.attr('data-url'),
                    type: 'POST',
                    data: {
                        id: id,
                        status: checked
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Không thể thay đổi trạng thái');
                        }
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })
    </script>

@endsection