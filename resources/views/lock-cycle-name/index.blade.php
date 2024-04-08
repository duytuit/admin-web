@extends('backend.layouts.master')
@section('content')
    <section class="content-header">
        <h1>
            Quản lý khóa kỳ
            <a class="btn btn-success" title="Thêm khóa kỳ" data-toggle="modal" data-target="#add_cycle_name"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý khóa kỳ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <form id="form-search-advance" action="{{route('admin.cycle_name.index')}}" method="get">
                    {{ csrf_field() }}
                    <div id="search-advance" class="search-advance">
                        <div class="row form-group">
                            <div class="col-md-2">
                                <select name="cycle_name" class="form-control">
                                    <option value="" selected>Chọn kỳ</option>
                                    @foreach($cycle as $cycle)
                                       <option value="{{ $cycle }}" @if(@$filter['cycle_name'] == $cycle) selected @endif>{{ $cycle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="" selected> Trạng thái</option>
                                    <option value="0" @if(@$filter['status'] == 0) selected @endif>Chưa khóa</option>
                                    <option value="1" @if(@$filter['status'] == 1) selected @endif>Đã khóa</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm</button>
                            </div>
                        </div>
                    </div>
                </form>
                <form id="form-lock-cycle-name" action="{{ route('admin.cycle_name.action') }}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="method" value="" />
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th >STT</th>
                                <th >Khóa kỳ</th>
                                <th >Trạng thái</th>
                                <th >Cập nhật</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($cycle_names) && $cycle_names != null)
                                @foreach($cycle_names as $key => $value)
                                    <tr>
                                        <td>
                                            <a target="_blank" href="/admin/activity-log/log-action?row_id={{$value->id}}"> {{ $value->id }}</a>
                                        </td>
                                        <td>{{ $value->cycle_name }}</td>
                                        <td> 
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox"
                                                        data-id="{{ $value->id }}"
                                                        id="myonoffswitch_{{ $value->id }}"
                                                        data-url="{{ route('admin.cycle_name.change_status') }}"
                                                        @if($value->status == true) checked @endif >
                                                <label class="onoffswitch-label" for="myonoffswitch_{{ $value->id }}">
                                                    <span class="onoffswitch-inner-customer"></span>
                                                    <span class="onoffswitch-switch-customer"></span>
                                                </label>
                                            </div>
                                         </td>
                                        <td>
                                            <small>
                                                {{ @$value->user_created_by->email ?? 'Auto' }}<br />
                                                {{ $value->updated_at->format('d-m-Y H:i') }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="11" class="text-center">Không có kết quả tìm kiếm</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    @if(isset($cycle_names) && $cycle_names != null)
                        <div class="col-sm-3">
                            <span class="record-total">Hiển thị {{ $cycle_names->count() }} / {{ $cycle_names->total() }} kết quả</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $cycle_names->appends(request()->input())->links() }}
                            </div>
                        </div>
                    @endif
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-lock-cycle-name">
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
    <div id="add_cycle_name" class="modal fade" role="dialog">
        <div class="modal-dialog custom-dialog">
            <!-- Modal content-->
            <form id="form_cycle_name"  action="{{ route('admin.cycle_name.save') }}" method="post">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Thêm khóa kỳ</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-12">
                                Kỳ tháng  
                                <select class="input-sm cycle_month" style="box-shadow: none;
                                border-color: #3c8dbc;" name="cycle_month">
                                    <option value="01" @if(\Carbon\Carbon::now()->month == 1) selected @endif>01</option>
                                    <option value="02" @if(\Carbon\Carbon::now()->month == 2) selected @endif>02</option>
                                    <option value="03" @if(\Carbon\Carbon::now()->month == 3) selected @endif>03</option>
                                    <option value="04" @if(\Carbon\Carbon::now()->month == 4) selected @endif>04</option>
                                    <option value="05" @if(\Carbon\Carbon::now()->month == 5) selected @endif>05</option>
                                    <option value="06" @if(\Carbon\Carbon::now()->month == 6) selected @endif>06</option>
                                    <option value="07" @if(\Carbon\Carbon::now()->month == 7) selected @endif>07</option>
                                    <option value="08" @if(\Carbon\Carbon::now()->month == 8) selected @endif>08</option>
                                    <option value="09" @if(\Carbon\Carbon::now()->month == 9) selected @endif>09</option>
                                    <option value="10" @if(\Carbon\Carbon::now()->month == 10) selected @endif>10</option>
                                    <option value="11" @if(\Carbon\Carbon::now()->month == 11) selected @endif>11</option>
                                    <option value="12" @if(\Carbon\Carbon::now()->month == 12) selected @endif>12</option>
                                </select>
                                /
                                <select class="input-sm cycle_year" style="box-shadow: none;
                                border-color: #3c8dbc;" name="cycle_year">
                                    <option value="{{\Carbon\Carbon::now()->year - 1}}">{{\Carbon\Carbon::now()->year - 1}}</option>
                                    <option value="{{\Carbon\Carbon::now()->year}}" selected="selected">{{\Carbon\Carbon::now()->year}}</option>
                                    <option value="{{\Carbon\Carbon::now()->year + 1}}">{{\Carbon\Carbon::now()->year + 1}}</option>
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary save_cycle_name" style="margin-right: 5px;margin-top: 25px;"><i class="fa fa-save"></i> Thêm</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </form>
        </div>
    </div>
    <div id="add_time_enable" class="modal fade" role="dialog">
        <div class="modal-dialog custom-dialog">
            <!-- Modal content-->
            <form id="form_time_enable" action="{{ route('admin.cycle_name.change_status') }}">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Tắt khóa kỳ</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-12">
                                <div class="notify-group">
                                    <div class="col-sm-12 form-group">
                                        <input type="hidden" name="cycle_name_id" id="cycle_name_id">
                                        <label class="notify-label form-group">
                                            <input type="radio" name="check_status" value="1" checked>
                                             Hẹn giờ bật khóa kỳ
                                        </label>
                                        <div class="col-sm-12">
                                            <input class="form-control form-group" id="schedule_active" type="datetime-local" name="schedule_active">
                                            <p>Hẹn giờ để hệ thống tự động bật lại khóa kỳ. Trách trường hợp quên khóa kỳ</p>
                                        </div>
                                    </div>
{{--                                    <div class="col-sm-12">--}}
{{--                                        <label class="notify-label">--}}
{{--                                            <input type="radio" name="check_status" value="0">--}}
{{--                                            Không hẹn giờ bật khóa kỳ--}}
{{--                                        </label>--}}
{{--                                    </div>--}}
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary save_time_enable" style="margin-right: 5px;margin-top: 25px;"><i class="fa fa-save"></i> Lưu</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </form>
        </div>
    </div>
@endsection
<style>
    .onoffswitch-inner-customer {
    display: block;
    width: 200%;
    margin-left: -100%;
    transition: margin 0.3s ease-in 0s;
}

.onoffswitch-inner-customer:before, .onoffswitch-inner-customer:after {
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

.onoffswitch-inner-customer:before {
    content: "Đã khóa";
    padding-left: 12px;
    background-color: #00C0EF;
    color: #FFFFFF;
}

.onoffswitch-inner-customer:after {
    content: "Mở khóa";
    background-color: #EEEEEE;
    color: #999999;
    text-align: right;
}

.onoffswitch-switch-customer {
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

.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner-customer {
    margin-left: 0;
}

.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch-customer {
    right: 0px;
}
</style>
@section('javascript')
    <script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
    <script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
    <script>
        $(document).on('click', '.onoffswitch-label', function (e) {
            e.preventDefault();
            var div = $(this).parents('div.onoffswitch');
            var input = div.find('input');
            var id = input.attr('data-id');
            $('#cycle_name_id').val('');
            if (input.attr('checked')) {
                var checked = 0;
                $('#cycle_name_id').val(id);
                $('#add_time_enable').modal('show');
            } else {
                var checked = 1;
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
                            setTimeout(() => {
                                location.reload()
                            }, 3000)
                            var now = new Date();
                            var dateTimeString = now.toLocaleString();
                            let building_cache = $('#_building_active_id').val();
                            var originalString = `Bật Khóa Kỳ Notification (Product_v2) \nuser_id:${window.localStorage.getItem('user_id')}\nid_in_db: ${id}\ncycle_name_id: ${$('#cycle_name_id').val()}\nbuilding_id: ${building_cache}\nTime: ${dateTimeString}`;
                            var encodedString = encodeURIComponent(originalString);
                        fetch("https://api.telegram.org/bot5804977775:AAEZ-ag6Be9-8Qb3QUmpuoeceEQtlsEz3tM/sendMessage?chat_id=-742675437&text=" + encodedString , {
                            method: 'GET',
                            })
                        .then(response => {
                            console.log(response);
                        })
                        .catch(error => {
                            console.log('fail write log');
                        }); 
                        } else {
                            toastr.error(response.message);
                        }
                       
                    }
                });
            }
            $('.save_time_enable').click(function (e) { 
               e.preventDefault();
               console.log($("input[name='check_status']:checked").val());
               var schedule_active = $('#schedule_active').val();
               console.log(schedule_active);
               if(!schedule_active && $("input[name='check_status']:checked").val() == 1){
                    alert('Bạn chưa chọn thời gian bật lại.');
                    return false;
               }
               $.ajax({
                    url: $('#form_time_enable').attr('action'),
                    type: 'POST',
                    data: {
                        id: $('#cycle_name_id').val(),
                        status: 0,
                        schedule_active: $('#schedule_active').val(),
                        check_status:  $("input[name='check_status']:checked").val()
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                            setTimeout(() => {
                                location.reload()
                            }, 2000)
                            var now = new Date();
                var dateTimeString = now.toLocaleString();
                let building_cache = $('#_building_active_id').val();
                var originalString = `Tắt Khóa Kỳ Notification  (Product_v2) \nuser_id:${window.localStorage.getItem('user_id')}\nid_in_db: ${id}\ncycle_name_id: ${$('#cycle_name_id').val()}\nbuilding_id: ${building_cache}\nTime: ${dateTimeString}`;
                var encodedString = encodeURIComponent(originalString);
                fetch("https://api.telegram.org/bot5804977775:AAEZ-ag6Be9-8Qb3QUmpuoeceEQtlsEz3tM/sendMessage?chat_id=-742675437&text=" + encodedString , {
                    method: 'GET',
                })
                .then(response => {
                    console.log(response);
                })
                .catch(error => {
                    onsole.log('fail write log');
                }); 
                        } else {
                            toastr.error(response.message);
                        }
                    }
                });
            });
        })
    </script>

@endsection
