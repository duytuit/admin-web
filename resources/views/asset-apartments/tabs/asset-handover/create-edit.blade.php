@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý bàn giao tài sản căn hộ
            <small>Thêm tài sản</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý bàn giao tài sản căn hộ</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <form data-action="{{ !isset($id) ? route('admin.asset-apartment.asset-handover.store') : route('admin.asset-apartment.asset-handover.update', ['id' => $id]) }}" method="POST" id="them_ban_giao_tai_san">
                @csrf
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-6">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"> Thông tin bàn giao tài sản</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="form-group div_name">
                                    <label for="exampleInputEmail1">Chọn tòa nhà:</label>
                                    <select id="ip_place_id_create" class="form-control" style="width: 100%;">
                                        <option value="">Chọn tòa nhà</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_apartment_id">
                                    <label for="exampleInputEmail1">Căn hộ:</label>
                                    <select name="apartment_id" id="ip_apartment_create" style="width: 100%;" class="form-control" data-url="{{ route('admin.apartments.ajax_get_customer') }}">
                                        <option value="">Căn hộ</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_asset_category_id">
                                        <label for="exampleInputEmail1">Loại tài sản:</label>
                                        <select class="form-control select3" style="width: 100%;" id="asset_category_id">
                                            <option value="">Loại tài sản</option>
                                            @foreach ($assetCategory as $value)
                                            <option value="{{ $value->id }}">{{ $value->title }}</option>
                                            @endforeach
                                        </select>
                                        <div class="message_zone"></div>
                                </div>
                                @if(!isset($id))
                                    <div class="form-group div_asset_ids">
                                        <label for="exampleInputEmail1">Mã tài sản:</label>
                                        <select multiple="multiple" id="asset_id" class="form-control">
                                        </select>
                                        <div class="message_zone"></div>
                                    </div>
                                @else
                                    <div class="form-group div_asset_ids">
                                        <label for="exampleInputEmail1">Mã tài sản:</label>
                                        <select id="asset_id" class="form-control">
                                        </select>
                                        <div class="message_zone"></div>
                                    </div>
                                @endif
                                <div class="form-group div_date_expected">
                                    <label for="exampleInputEmail1">Ngày dự kiến bàn giao:</label>
                                    <input type="text" class="form-control date_picker" name="date_expected" id="date_expected" value="{{ old('date_expected') ?? (@$asset_handover->date_expected ? date('d-m-Y', strtotime(@$asset_handover->date_expected)) : '' )}}" placeholder="Ngày dự kiến" autocomplete="off">
                                </div>
                                <div class="form-group div_warranty_period">
                                    <label for="exampleInputEmail1">Thơi gian bảo hành (Tháng):</label>
                                    <input type="number" name="warranty_period" class="form-control" value="{{ old('warranty_period') ?? @$asset_handover->warranty_period }}">
                                    <div class="message_zone"></div>
                                </div>
                                
                                <div class="form-group div_description">
                                    <label for="exampleInputEmail1">Ghi chú:</label>
                                    <textarea class="form-control" id="description" name="description" rows="5">{{ old('description') ?? @$asset_handover->description }}</textarea>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_status">
                                    <label for="exampleInputEmail1">Trạng thái:</label>
                                    <select class="form-control" id="status" name="status" style="width: 100%">
                                        @foreach($list_asset_handover as $key => $value)
                                            <option value="{{$value['text']}}" {{ $value['text'] == 2 ? 'selected' : '' }}>{{$value['value']}}</option>
                                        @endforeach
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                            </div>
                        </div>
                       
                        <!-- /.box-footer -->
                        <!-- /.box -->
                    </div>
                    <!--/.col (left) -->
                    <!-- right column -->
                    <div class="col-md-6">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Thông tin khách hàng</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="form-group div_customer">
                                    <label for="exampleInputEmail1">Tên khách hàng:</label>
                                    <input type="text" name="customer" class="form-control"
                                           value="{{ old('customer') ?? @$asset_handover->customer }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_email">
                                    <label for="exampleInputEmail1">Email:</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email') ?? @$asset_handover->email }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_phone">
                                    <label for="exampleInputEmail1">Phone:</label>
                                    <input type="text" name="phone" class="form-control"
                                           value="{{ old('phone') ?? @$asset_handover->phone }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Chọn file đính kèm</label>
                                    <div class="input-group input-image" data-file="image">
                                        <input type="file" multiple class="text-size text" id="attach_files" name="attach_files[]">
                                        <div class="list_image_task">
                                        </div>
                                        <a class="btn btn-default" id="clear" style="margin-top: 10px;cursor: pointer">Clear</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                                <a href="{{ route('admin.asset-apartment.asset-handover.index') }}" type="button"
                                   class="btn btn-default pull-left">Quay lại</a>
                                <button type="submit" class="btn btn-success" id="luu_ban_giao_tai_san" style="margin-left: 20px;">Lưu</button>
                        </div>
                        <!-- /.box-footer -->
                        <!-- /.box -->
                    </div>
                    <!--/.col (right) -->
                </div>
            </form>
        </div>
        <input type="hidden" id="link_file_attach">
        <input type="hidden" value="{{isset($asset_handover) ? $asset_handover->documents : ''}}" id="infos_files_asset_handover">
        <input type="hidden" value="{{isset($apartment_select) ? json_encode($apartment_select) : ''}}" id="apartment_select">
        <input type="hidden" value="{{isset($asset_select) ? json_encode($asset_select) : ''}}" id="asset_select">
    </section>
@endsection
<link rel="stylesheet" href="/adminLTE/plugins/lightbox/ekko-lightbox.css" />
@section('javascript')
    <!-- TinyMCE -->
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script src="/adminLTE/plugins/lightbox/ekko-lightbox.min.js"></script>
    <script>
        $(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox();
        });
           //Date picker
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();

        $('#clear').click(function(e) {
           $('#link_file_attach').val('');
           $(".list_image_task").children().remove();
        })
        $(".list_image_task").on("click", "#iconRemoveFile", function() {
           var get_id_file =  $(this).attr('data-id_file_remove');
           if($("#link_file_attach").val()){
                var get_list_file =JSON.parse($("#link_file_attach").val());
                for (let index = 0; index < get_list_file.length; index++) {
                        if(get_list_file[index].id == get_id_file){
                            get_list_file.splice(index, 1);
                        }
                }
                $("#link_file_attach").val(JSON.stringify(get_list_file))
           }
           $(this).parents(".list_image_task").find(".id_file_"+get_id_file).remove();
       });
       function addLinkFile(id_input_hidden,link_file,id) {
                var getvalue=[];
                if($(id_input_hidden).val()){
                        getvalue = JSON.parse($(id_input_hidden).val());
                        getvalue.push({
                             id:id,
                             url:link_file
                        });
                }else{
                        getvalue.push({
                             id:id,
                             url:link_file
                        });
                }
                $(id_input_hidden).val(JSON.stringify(getvalue));
       }
       $(document).ready(function() {
            if($("#infos_files_asset_handover").val()){
                let get_list_file =JSON.parse($("#infos_files_asset_handover").val());
                let html_list_file_task='';
                for (let index = 0; index < get_list_file.length; index++) {
                    addLinkFile('#link_file_attach',get_list_file[index],index)
                    html_list_file_task+='<div class="id_file_'+index+'"><a class="download" data-toggle="lightbox" data-gallery="attached-images-gallery" data-value_file="'+index+'" href="'+get_list_file[index]+'" style="height:15px;cursor: pointer;margin-bottom: 3px;">'+get_list_file[index].split('https://media.dxmb.vn/images/building_care/assets/')[1]+'</a><i id="iconRemoveFile" data-id_file_remove="'+index+'" class="fa fa-remove" style="cursor: pointer;color:red;margin-left:7px;"></i></div>';
                }
                $(".list_image_task").append(html_list_file_task);
            }
            if($("#apartment_select").val()){
                // căn hộ
                let apartment_select = JSON.parse($("#apartment_select").val());
                let apartment_array = [];
                apartment_array.push({
                                        id:apartment_select.id,
                                        text:apartment_select.text
                                    });
                $('#ip_apartment_create').select2({data:apartment_array});
                $('#ip_apartment_create').find('option').attr('selected', true);
                $('#ip_apartment_create').select2();
            }
            if($("#asset_select").val()){
                 // tài sản
                let asset_select = JSON.parse($("#asset_select").val());
                let asset_array = [];
                asset_array.push({
                                    id:asset_select.id,
                                    text:asset_select.text
                                });
                $('#asset_id').select2({data:asset_array});
                $('#asset_id').find('option').attr('selected', true);
                $('#asset_id').select2();
            }
            // =====================================căn hộ=====================================
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
            // =====================================tài sản=====================================
            
            get_data_select_code_asset({
                        object: '#asset_id',
                        url: '{{ url('admin/asset-apartment/asset/ajaxGetSelect') }}',
                        data_id: 'id',
                        data_text: 'code',
                        title_default: 'Mã tài sản'
            });
            function get_data_select_code_asset(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                asset_category_id: $("#asset_category_id").val()
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [];
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
        //save ban giao tài sản
        $('#luu_ban_giao_tai_san').on('click', function (e) {
            var formCreate = new FormData($('#them_ban_giao_tai_san')[0]);
            if($('#link_file_attach').val() ){
                let new_array_files =  [];
                let array_files =  JSON.parse($("#link_file_attach").val());
                for (let index = 0; index < array_files.length; index++) {
                    new_array_files.push(array_files[index].url);
                }
                formCreate.append('attach_link_files', JSON.stringify(new_array_files));
            }
            if($('#asset_id').val().length > 0){
                var asset_ids = $('#asset_id').val();

                var obj_asset_ids = JSON.stringify(asset_ids);

                formCreate.append('asset_ids', obj_asset_ids);
            }
            
            if (!requestSend) {
                showLoading();
                requestSend = true;
                e.preventDefault();
                $.ajax({
                    url: $('#them_ban_giao_tai_san').attr('data-action'),
                    type: $('#them_ban_giao_tai_san').attr('method'),
                    data: formCreate,
                    contentType: false,
                    processData: false, 
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                            if (!response.href) {
                                setTimeout(() => {
                                    location.reload()
                                }, 2000)
                            } else {
                                setTimeout(() => {
                                    window.location.href = response.href
                                }, 2000)
                            }
                        }else if (response.success == false) {
                            toastr.error(response.message);
                            if (!response.href) {
                                setTimeout(() => {
                                    location.reload()
                                }, 2000)
                            } else {
                                setTimeout(() => {
                                    window.location.href = response.href
                                }, 2000)
                            }
                        } else {
                            toastr.error('Có lỗi! Xin vui lòng thử lại');
                            setTimeout(() => {
                                location.reload()
                            }, 2000)
                        }
                        hideLoading();
                        requestSend = false;
                    },
                    error: function (response) {
                        $(document).find('.has-error').removeClass('has-error');
                        if ($(document).find('.help-block').length) {
                            $(document).find('.help-block').remove();
                        }
                        showErrorsCreate(response.responseJSON.errors, '.div_', '.message_zone');
                        hideLoading();
                        requestSend = false;
                    }
                })
            } else {
                e.preventDefault();
            }
        });
        $('#attach_files').on('change', function(e) {
            var form_data = new FormData();                  
           
            var totalfiles = e.target.files.length;
            for (var index = 0; index < totalfiles; index++) {
                form_data.append('attach_files[]', e.target.files[index]);
            }
            showLoading();
            e.preventDefault();
            $.ajax({
                url: '/api/admin/v1/upload/attach-files',
                processData: false,
                mimeType: "multipart/form-data",
                contentType: false,
                type: 'POST',
                data: form_data,
                success: function(response) {
                    var get_response =JSON.parse(response);
                    if (get_response.success == true) {
                        let html_list_file_task='';
                        for (let index = 0; index < get_response.data.length; index++) {
                            addLinkFile('#link_file_attach',get_response.data[index],index)
                            html_list_file_task+='<div class="id_file_'+index+'"><a class="download" data-toggle="lightbox" data-gallery="attached-images-gallery" data-value_file="'+index+'" href="'+get_response.data[index]+'" style="height:15px;cursor: pointer;margin-bottom: 3px;">'+get_response.data[index].split('https://media.dxmb.vn/images/building_care/assets/')[1]+'</a><i id="iconRemoveFile" data-id_file_remove="'+index+'" class="fa fa-remove" style="cursor: pointer;color:red;margin-left:7px;"></i></div>';
                        }
                        $(".list_image_task").append(html_list_file_task);
                    } 
                    hideLoading(); 
                },
                error: function (e) {
                  hideLoading();
                  console.log(e);
                }
            });
        });
        $('#ip_apartment_create').on('change', function (e) {
            var apartmentId = $('#ip_apartment_create').val();
            if(apartmentId){
                showLoading();
                $('input[name="customer"]').val('');
                $('input[name="email"]').val('');
                $('input[name="phone"]').val('');
                $.ajax({
                    url: $(this).attr('data-url') + '?apartment_id=' + apartmentId,
                    type: 'GET',
                    success: function (response) {
                        hideLoading(); 
                        if(response.data){
                            $('input[name="customer"]').val(response.data.customer_name);
                            $('input[name="email"]').val(response.data.email);
                            $('input[name="phone"]').val(response.data.phone);
                        }
                    },
                    error: function (e) {
                      hideLoading();
                    }
                });
            }
            
        });

    </script>
@endsection