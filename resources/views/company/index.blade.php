@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Công ty
            <small>Quản lý công ty</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
            <li class="active">Quản lý công ty</li>
        </ol>
    </section>
    <section class="content" id="content-partner">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3>Thông tin công ty</h3>
                <div class="row box-body">
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                             <form role="form">
                                <!-- select -->
                                <div class="form-group">
                                    <select name="company_id" id="company_id" class="form-control"  onchange='this.form.submit()'>
                                        @foreach ($company as $value)
                                            <option value="{{ $value->id }}" @if(@$filter['company_id'] == $value->id )selected @endif > {{ $value->name }}</option>
                                        @endforeach
                                 </select>
                                </div>
                                <noscript><input type="submit" value="Submit"></noscript>
                            </form>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <div class="col-sm-6 col-xs-6 col-md-6">
                            <a href="{{ route('admin.company.create',['company_id'=>@$filter['company_id']]) }}" type="button" class="btn btn-primary"><i class="fa fa-edit"></i>&nbsp;&nbsp;Thêm dự án</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-header with-border">
                <h3>Thông tin dự án</h3>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th>STT</th>
                            <th>Tên dự án</th>
                            <th>Địa chỉ</th>
                            <th>Số điện thoại</th>
                            <th>Mô tả</th>
                            <th>Trưởng ban quản lý</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                            @if($buildings->count() > 0)
                                @foreach($buildings as $key => $value)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ @$value->name }}</td>
                                    <td>{{ @$value->address }}</td>
                                    <td>{{ @$value->phone }}</td>
                                    <td>{{ @$value->description }}</td>
                                    <td>{{ @$value->manager->company_staff->name }}</td>
                                    <td>
                                        <div class="onoffswitch-v2">
                                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox-v2" data-id="{{ @$value->id }}"
                                                    id="myonoffswitch_{{ @$value->id }}" data-url="{{ route('admin.company.change-status-building') }}" @if(@$value->status == 1) checked @endif >
                                            <label class="onoffswitch-label-v2" for="myonoffswitch_{{ @$value->id }}">
                                                <span class="onoffswitch-inner"></span>
                                                <span class="onoffswitch-switch"></span>
                                            </label>
                                        </div>
                                    </td>
                                    <td><a href="{{ route('admin.company.edit', $value->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a></td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('stylesheet')
    <style>
        .onoffswitch,.onoffswitch-v2 {
            position: relative;
            width: 70px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .onoffswitch-checkbox,.onoffswitch-checkbox-v2 {
            display: none;
        }

        .onoffswitch-label,.onoffswitch-label-v2 {
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
        .onoffswitch-checkbox-v2:checked + .onoffswitch-label-v2 .onoffswitch-inner {
            margin-left: 0;
        }

        .onoffswitch-checkbox-v2:checked + .onoffswitch-label-v2 .onoffswitch-switch {
            right: 0px;
        }
    </style>
@endsection
@section('javascript')
    <script>
        var requestSend = false;

        //onoff status
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
                        active: checked
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Không thay đổi trạng thái');
                        }
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })
        $(document).on('click', '.onoffswitch-label-v2', function (e) {
            var div = $(this).parents('div.onoffswitch-v2');
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
                            toastr.error('Không thay đổi trạng thái');
                        }
                        location.reload()
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })

    </script>
@endsection