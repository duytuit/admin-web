@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <div>
        <span>Bàn Giao Căn Hộ </span>
        <a class="btn btn-success open_create_apartment_hand_over"><i class="fa fa-plus"></i>Thêm mới</a>
        <a href="{{ route('admin.apartment.handover.index_import') }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i>Import Exel</a>
    </div>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Căn hộ</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search-apartment" action="{{ route('admin.apartment.handover.index') }}" method="get">
                <div class="col-sm-1">
                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:" type="button" class="btn-action" data-target="#form-apartmennt-list" data-method="delete">
                                <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                            </a>
                            <a href="javascript:" class="btn_action_create_notify">
                                <i class="fa fa-send text-success"></i>&nbsp; Gửi thông báo
                            </a>
                        </li>

                    </ul>
                </div>
                <div class="col-sm-11">

                    <div id="search-advance" class="search-advance">
                        <div class="row ">
                            <div class="form-group space-5" style="width: calc(100% - 55px);float: left;">
                                <div class="col-sm-2">
                                    <select name="pub_user_profile_id" id="pub_user_profile_id" class="form-control select2" style="width: 100%">
                                        <option value="">Chọn khách hàng</option>
                                        @foreach($customers_v2 as $key => $value)
                                        <option value="{{$value->pub_user_profile_id}}" {{isset($filter_apartments['pub_user_profile_id']) ? $filter_apartments['pub_user_profile_id']  == $value->pub_user_profile_id ? 'selected' : '' : '' }}>{{@$value->pubUserProfile->display_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2" style="padding-left:0">
                                    <select name="ip_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                                        <option value="">Chọn tòa nhà</option>
                                    </select>
                                </div>
                                <div class="col-sm-2" style="padding-left:0">
                                    <select name="bdc_apartment_id" id="ip-apartment" class="form-control">
                                        <option value="">Căn hộ</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select class="form-control" id="status_confirm" name="status_confirm" style="width: 100%">
                                        <option value="">Trạng thái xác nhận</option>
                                        @foreach($list_apartment_handover as $key => $value)
                                        <option value="{{$value['text']}}" {{isset($filter_apartments['status_confirm']) ? $filter_apartments['status_confirm']  == $value['text'] ? 'selected' : '' : '' }}>{{$value['value']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select class="form-control" id="search_status_success_handover" name="status_success_handover" style="width: 100%">
                                        <option value="">Trạng thái bàn giao</option>
                                        <option value="0" {{isset($filter_apartments['status_success_handover']) ? $filter_apartments['status_success_handover']  == 0 ? 'selected' : '' : '' }}>Chưa bàn giao</option>
                                        <option value="1" {{isset($filter_apartments['status_success_handover']) ? $filter_apartments['status_success_handover']  == 1 ? 'selected' : '' : '' }}>Đã bàn giao</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <div class="input-group-btn">
                                        <a href="{{ route('admin.apartment.handover.export',Request::all()) }}" class="btn btn-warning btn-sm"><i class="fa fa-download"></i>Export Exel</a>
                                    </div>
                                </div>
                            </div>
                             <div class="form-group space-5" style="width: calc(100% - 55px);float: left;">
                                <div class="col-sm-2">
                                      <input type="text" class="form-control date_picker" name="from_date_search" id="from_date_search" value="{{$filter_apartments['from_date_search']??'' }}" placeholder="Từ..." autocomplete="off">
                                </div>
                                <div class="col-sm-2" style="padding-left:0">
                                      <input type="text" class="form-control date_picker" name="to_date_search" id="to_date_search" value="{{$filter_apartments['to_date_search']??'' }}" placeholder="Đến..." autocomplete="off">
                                </div>
                                <div class="col-sm-2">
                                    <div class="input-group-btn">
                                        <button type="submit" title="Tìm kiếm" class="btn btn-info" style="margin-right: 130px;" form="form-search-apartment"><i class="fa fa-search"></i> Tìm</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </form><!-- END #form-search-advance -->
            <div class="clearfix"></div>
            <form id="form-apartmennt-list" action="{{ route('admin.apartment.handover.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th width="90">Khách hàng</th>
                                <th width="60">Căn hộ</th>
                                <th width="60">Sđt</th>
                                <th width="60">Email</th>
                                <th width="50">Địa chỉ</th>
                                <th width="30">Ngày dự kiến bàn giao</th>
                                <th width="100">Trạng thái xác nhận</th>
                                <th width="150">Ghi chú</th>
                                <th width="50">Kết quả bàn giao</th>
                                <th width="50">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $c)
                            <tr class="list_apartment_hand_over">
                                <td><input type="checkbox" name="ids[]" value="{{$c->id}}" class="iCheck checkSingle" /></td>
                                <td>{{@$c->pubUserProfile->display_name??''}}</td>
                                <td>{{@$c->bdcApartment->name}}</td>
                                <td>{{@$c->pubUserProfile->phone}}</td>
                                <td>{{@$c->pubUserProfile->email??''}}</td>
                                <td>{{@$c->pubUserProfile->address??''}}</td>
                                <td>
                                    {{--<input type="text" class="form-control date_picker custom_date_picker" data-id="{{$c->id}}" id="from_date_{{$c->id}}" value="{{substr($c->date_handover, 0, 10)}}">--}}
                                    <input type="text" class="form-control date_picker custom_date_picker" data-id="{{$c->id}}" id="from_date_{{$c->id}}" value="{{$c->handover_date ? date('d-m-Y', strtotime($c->handover_date)) : '----/--/--'}}">
                                </td>
                                <td>
                                    <select class="form-control customer_status_confirm" style="width:100%" data-id="{{$c->id}}">
                                        @foreach($list_apartment_handover as $key => $value)
                                        <option value="{{$value['text']}}" @if($value['text']==$c->status_confirm) selected @endif>{{$value['value']}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" value="{{$c->note_confirm ?? ''}}" class="form-control customer_note_confirm" data-id="{{$c->id}}">
                                </td>
                                <td>
                                    <div class="checkbox">
                                        <label class="customer_status_success_handover" data-id="{{$c->id}}">
                                            <input type="checkbox" class="customer_status_success_handover_checkbox" @if($c->status_success_handover == 1) checked @endif>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                   <a href="javascript:void(0);" email="{{@$c->pubUserProfile->email??''}}" phone="{{@$c->pubUserProfile->phone}}" class="btn btn-warning reset_password" title="Reset Password"><i class="fa fa-recycle"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Hiển thị: {{ $customers->count() }} / {{ $customers->total() }} Kết quả</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                                {{ $customers->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-search-apartment">
                                @php $list = [5,10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form><!-- END #form-users -->
        </div>
        <input type="hidden" value="{{isset($filter_apartments['ip_place_id']) ? $filter_apartments['ip_place_id'] : ''}}" id="ip_place_id_search">
        <input type="hidden" value="{{isset($filter_apartments['bdc_apartment_id']) ? $filter_apartments['bdc_apartment_id'] : ''}}" id="bdc_apartment_id_search">
        <input type="hidden" value="{{isset($get_apartment) ?json_encode($get_apartment) : ''}}" id="get_apartment">
        <input type="hidden" value="{{isset($get_place_building) ?json_encode($get_place_building) : ''}}" id="get_place_building">
    </div>
</section>



@endsection
@include('apartment-handover.modal.note')
@include('apartment-handover.modal.create')
@include('apartment-handover.modal.send-notify')

@section('javascript')

<script>
    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
    $('.reset_password').on('click',function () {
                var email = $(this).attr('email');
                var phone = $(this).attr('phone');
                var input = '';
                if((email !='' && phone!='') || (email !='' && phone=='')){
                    input = email;
                }
                if(email =='' && phone!=''){
                    input = phone;
                }
                if (!confirm('Bạn có chắc chắn reset mật khẩu của tài khoản này?')) {
                    e.preventDefault();
                } else {
                    $.post('{{ url('/admin/manage-user/reset-pass') }}', {
                        email:  input
                    }, function(data) {
                        toastr.success(data.message);
                    });
                }


            });
    $(document).ready(() => {
        if($('#bdc_apartment_id_search').val())
        {
            var bdc_apartment_id_all = $('#get_apartment').val();
            var obj_bdc_apartment_id_all = JSON.parse(bdc_apartment_id_all);
            var new_bdc_apartment_id_all = [];
                        new_bdc_apartment_id_all.push({
                            id:obj_bdc_apartment_id_all["id"],
                            text:obj_bdc_apartment_id_all["name"]
                        });
            
            $('#ip-apartment').select2({data:new_bdc_apartment_id_all});
            $('#ip-apartment').find('option').attr('selected', true);
            $('#ip-apartment').select2();
        }
        if($('#ip_place_id_search').val())
        {
            var get_place_building_all = $('#get_place_building').val();
            var obj_get_place_building_all = JSON.parse(get_place_building_all);
            var new_get_place_building_all = [];
                        new_get_place_building_all.push({
                            id:obj_get_place_building_all["id"],
                            text:obj_get_place_building_all["name"]
                        });
            
            $('#ip-place_id').select2({data:new_get_place_building_all});
            $('#ip-place_id').find('option').attr('selected', true);
            $('#ip-place_id').select2();
        }
       $('#custom_template_email').change(function() {
          let value = $(this).val();
          if(value == 'noi_dung'){
               $('#form_noi_dung').show();
               $('#form_mac_dinh').hide();
          }else{
               $('#form_noi_dung').hide();
               $('#form_mac_dinh').show();
          }

        });
        
        if($('#custom_template_email').val() == 'noi_dung'){
             $('#form_noi_dung').show();
             $('#form_mac_dinh').hide();
        }else{
             $('#form_noi_dung').hide();
             $('#form_mac_dinh').show();
        }
    });
    $(function() {
        get_data_select_apartment1({
            object: '#ip-place_id',
            url: '{{url('admin/apartments/ajax_get_building_place')}}',
            data_id: 'id',
            data_text: 'name',
            data_code: 'code',
            title_default: 'Chọn tòa nhà'
        });

        function get_data_select_apartment1(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function(params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function(json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text] + ' - ' + item[options.data_code]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }
        get_data_select({
            object: '#ip-apartment',
            url: '{{ url('admin/apartments/ajax_get_apartment') }}',
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn căn hộ'
        });
        $("#ip-place_id").on('change', function() {
            if ($("#ip-place_id").val()) {
                get_data_select({
                    object: '#ip-apartment',
                    url: '{{ url('admin/apartments/ajax_get_apartment_with_place') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn căn hộ'
                });
            }
        });

        function get_data_select(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            place_id: $("#ip-place_id").val(),
                        }
                        return query;
                    },
                    processResults: function(json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }
        //    =======================================

        get_data_select_apartment_create({
            object: '#ip_place_id_create',
            url: '{{ url('admin/apartments/ajax_get_building_place') }}',
            data_id: 'id',
            data_text: 'name',
            data_code: 'code',
            title_default: 'Chọn tòa nhà'
        });

        function get_data_select_apartment_create(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function(params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function(json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text] + ' - ' + item[options.data_code]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }
        get_data_select_create({
            object: '#ip_apartment_create',
            url: '{{ url('admin/apartments/ajax_get_apartment') }}',
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn căn hộ'
        });
        $("#ip_place_id_create").on('change', function() {
            if ($("#ip_place_id_create").val()) {
                get_data_select_create({
                    object: '#ip_apartment_create',
                    url: '{{ url('admin/apartments/ajax_get_apartment_with_place') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn căn hộ'
                });
            }
        });

        function get_data_select_create(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            place_id: $("#ip_place_id_create").val(),
                        }
                        return query;
                    },
                    processResults: function(json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }

    })
    sidebar('apartments', 'index');

    $('.open_create_apartment_hand_over').click(function() {
        $('#create_apartment_handover').modal('show');
    })
    $('.btn_action_create_notify').click(function() {

        var ids = [];
        var div = $('div.icheckbox_square-green[aria-checked="true"]');
        div.each(function(index, value) {
            var id = $(value).find('input.checkSingle').val();
            if (id) {
                ids.push(id);
            }
        });

        $('#ip_apartment_send_notify').val(ids).change();

        $('#send_notify_apartment').modal('show');
    })

    // Thêm mới

    $('.add-apartment-handover').click(function() {

        var values = $('#modal-apartment-handover').serializeArray();

        $.ajax({
            url: "{{ route('admin.apartment.handover.save') }}",
            type: 'POST',
            data: values,
            success: function(response) {
                if (response.success == true) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message);
                }
            }
        });

    })

    $('.post_save').click(function() {
        var desc = CKEDITOR.instances['content'].getData();
        var values = $('#create_form_notify_apartment').serializeArray();
        values.find(input => input.name == 'content').value = desc;
        var customers_id = $('#ip_apartment_send_notify').val();
        var json_customers_id = JSON.stringify(customers_id);
        values.push({
            name: "customers_id",
            value: json_customers_id
        });

        $.ajax({
            url: "{{ route('admin.posts_customers.save_apartment') }}",
            type: 'POST',
            data: values,
            success: function(response) {
                if (response.success == true) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            }
        });

    })

    $(".list_apartment_hand_over").on("click", ".customer_status_success_handover", function() {
        var _token = $('meta[name="csrf-token"]').attr('content');
        var id_customer = $(this).parents(".list_apartment_hand_over").find('.customer_status_success_handover').attr("data-id");
        if ($(this).parents(".list_apartment_hand_over").find('.customer_status_success_handover_checkbox').is(":checked")) {

            $.ajax({
                url: "{{ route('admin.apartment.handover.change_success_handover') }}",
                type: 'POST',
                data: {
                    _token: _token,
                    id: id_customer,
                    success_handover: 1
                },
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                }
            });
        } else {
            $.ajax({
                url: "{{ route('admin.apartment.handover.change_success_handover') }}",
                type: 'POST',
                data: {
                    _token: _token,
                    id: id_customer,
                    success_handover: 0
                },
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);

                    }
                }
            });
        }


    });
    $(".list_apartment_hand_over").on("keydown", ".customer_note_confirm", function(e) {
        if (e.which == 13) {
            e.preventDefault();
            var _token = $('meta[name="csrf-token"]').attr('content');
            var id_customer = $(this).parents(".list_apartment_hand_over").find('.customer_note_confirm').attr("data-id");
            $.ajax({
                url: "{{ route('admin.apartment.handover.change_note_confirm') }}",
                type: 'POST',
                data: {
                    _token: _token,
                    id: id_customer,
                    note_confirm: $(this).parents(".list_apartment_hand_over").find('.customer_note_confirm').val()
                },
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);

                    }
                }
            });
        }
    });
    $(".list_apartment_hand_over").on("change", ".customer_status_confirm", function(e) {
        e.preventDefault();
        var _token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: "{{ route('admin.apartment.handover.change_status_confirm') }}",
            type: 'POST',
            data: {
                _token: _token,
                id: $(this).parents(".list_apartment_hand_over").find('.customer_status_confirm').attr("data-id"),
                status_confirm: $(this).parents(".list_apartment_hand_over").find('.customer_status_confirm').val()
            },
            success: function(response) {
                if (response.success == true) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);

                }
            }
        });
    });

    $(".list_apartment_hand_over").on("change", ".custom_date_picker", function(e) {
        e.preventDefault();
        var _token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: "{{ route('admin.apartment.handover.change_date_handover') }}",
            type: 'POST',
            data: {
                _token: _token,
                id: $(this).attr("data-id"),
                date_handover: $(this).val()
            },
            success: function(response) {
                if (response.success == true) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);

                }
            }
        });
    });
</script>


@endsection