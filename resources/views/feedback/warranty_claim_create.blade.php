@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Thêm mới đăng ký bảo hành tài sản căn hộ
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thêm mới đăng ký bảo hành tài sản căn hộ</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm mới đăng ký bảo hành tài sản căn hộ</div>

                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="alert alert-danger alert_pop_add" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="form-group">
                            <form data-action="{{ !isset($id) ? route('admin.feedback.warrantyClaimStore') : route('admin.feedback.warrantyClaimUpdate', ['id' => $id]) }}" method="POST" id="form-edit-apartment" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <input type="hidden" name="bdc_building_id"  class="form-control" placeholder="Tên căn hộ" value="{{$building_id}}">
                                <div class="form-group div_bdc_apartment_id">
                                    <label for="exampleInputEmail1">Căn hộ:</label>
                                    <select name="bdc_apartment_id" id="ip_apartment_create" style="width: 100%;" class="form-control" data-url="{{ route('admin.apartments.ajax_get_customer') }}">
                                        <option value="">Căn hộ</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_asset_id">
                                        <label for="exampleInputEmail1">Mã tài sản:</label>
                                        <select id="asset_id" class="form-control">
                                        </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_title">
                                    <label for="ip-name">Tiêu đề</label>
                                    <input type="text" name="title" id="title" value="{{ old('title') ?? @$warranty_claim->title }}" class="form-control" placeholder="Tiêu đề" >
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_content">
                                    <label for="ip-description">Nôi dung</label>
                                    <textarea name="content" id="content" cols="30" rows="5" placeholder="Nội dung" class="form-control">{{ old('content') ?? @$warranty_claim->content }}</textarea>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_attached">
                                <label class="control-label">Chọn file đính kèm</label>
                                    <div class="input-group input-image" data-file="image">
                                        <input type="file" multiple class="text-size text" id="attach_files" name="attach_files[]">
                                        <div class="list_image_task">
                                        </div>
                                        <a class="btn btn-default" id="clear" style="margin-top: 10px;cursor: pointer">Clear</a>
                                    </div>
                                </div>
                                <div class="form-group div_start_time">
                                    <label for="ip-name">Thời gian bắt đầu</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control date_picker" value="{{ old('start_time') ?? @$warranty_claim->start_time }}" name="start_time" id="start_time" placeholder="Từ..." autocomplete="off">
                                    </div>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_end_time">
                                    <label for="ip-name">Thời gian kết thúc</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control date_picker" value="{{ old('end_time') ?? @$warranty_claim->end_time }}" name="end_time" id="end_time" placeholder="Đến..." autocomplete="off">
                                    </div>
                                    <div class="message_zone"></div>
                                </div>
                                <hr>
                                <div class="form-group div_full_name">
                                    <label for="ip-name">Họ và tên</label>
                                    <input type="text" name="full_name" id="full_name" value="{{ old('full_name') ?? @$warranty_claim->full_name }}" class="form-control" placeholder="Họ và tên" >
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_email">
                                    <label for="ip-name">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') ?? @$warranty_claim->email }}" class="form-control" placeholder="Email" >
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_phone">
                                    <label for="ip-name">Số điện thoại</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') ?? @$warranty_claim->phone }}" class="form-control" placeholder="Số điện thoại" >
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group">
                                    <a href="{{ route('admin.feedback.warrantyClaim') }}" type="button"
                                    class="btn btn-sm btn-default pull-left">Quay lại</a>
                                    <button type="submit" class="btn btn-sm btn-success btn-js-action-add" style="margin-left: 20px;" title="Cập nhật">
                                        <i class="fa fa-save"></i>&nbsp;Thêm mới
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="link_file_attach">
        <input type="hidden" value="{{isset($asset_handover) ? $asset_handover->documents : ''}}" id="infos_files_asset_handover">
        <input type="hidden" value="{{isset($apartment_select) ? json_encode($apartment_select) : ''}}" id="apartment_select">
        <input type="hidden" value="{{isset($asset_select) ? json_encode($asset_select) : ''}}" id="asset_select">
    </section>

@endsection
<link rel="stylesheet" href="/adminLTE/plugins/lightbox/ekko-lightbox.css" />
@section('javascript')
<script src="/adminLTE/plugins/lightbox/ekko-lightbox.min.js"></script>
    <script>
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
       $('#ip_apartment_create').on('change', function (e) {
            var apartmentId = $('#ip_apartment_create').val();
            if(apartmentId){
                showLoading();
                $('input[name="full_name"]').val('');
                $('input[name="email"]').val('');
                $('input[name="phone"]').val('');
                $.ajax({
                    url: $(this).attr('data-url') + '?apartment_id=' + apartmentId,
                    type: 'GET',
                    success: function (response) {
                        hideLoading(); 
                        if(response.data){
                            $('input[name="full_name"]').val(response.data.customer_name);
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
         //save đăng ký bảo hành
         $('.btn-js-action-add').on('click', function (e) {
            var formCreate = new FormData($('#form-edit-apartment')[0]);
            if($('#link_file_attach').val() ){
                let new_array_files =  [];
                let array_files =  JSON.parse($("#link_file_attach").val());
                for (let index = 0; index < array_files.length; index++) {
                    new_array_files.push(array_files[index].url);
                }
                formCreate.append('attach_link_files', JSON.stringify(new_array_files));
            }
            if($('#asset_id').val()){
                var asset_ids = $('#asset_id').val();
                formCreate.append('asset_id', asset_ids);
            }
            var desc = CKEDITOR.instances['content'].getData();
            formCreate.append('description', desc);
            if (!requestSend) {
                showLoading();
                requestSend = true;
                e.preventDefault();
                $.ajax({
                    url: $('#form-edit-apartment').attr('data-action'),
                    type: $('#form-edit-apartment').attr('method'),
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
        $(document).ready(function () {
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
            //Date picker
            $('input.date_picker').datepicker({
                autoclose: true,
                dateFormat: "dd-mm-yy"
            }).val();
            get_data_select_create({
                object: '#ip_apartment_create',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });

            function get_data_select_create(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                place_id: null,
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
            $("#ip_apartment_create").on('change', function() {
                $("#asset_id").val('');
                if ($("#ip_apartment_create").val()) {
                    get_data_select_code_asset({
                        object: '#asset_id',
                        url: '{{ url('admin/asset-apartment/asset/ajaxGetSelect') }}',
                        data_id: 'id',
                        data_text: 'code',
                        title_default: 'Mã tài sản'
                    });
                }
            });
            function get_data_select_code_asset(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                asset_category_id: "no_category_id",
                                bdc_apartment_id: $("#ip_apartment_create").val()
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
        });
    </script>

@endsection
