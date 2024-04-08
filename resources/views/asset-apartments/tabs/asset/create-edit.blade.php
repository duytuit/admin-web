@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý tài sản căn hộ
            <small>Thêm tài sản</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý tài sản căn hộ</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <form data-action="{{ !isset($id) ? route('admin.asset-apartment.asset.store') : route('admin.asset-apartment.asset.update', ['id' => $id]) }}" method="POST" id="them_tai_san">
                @csrf
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-12">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"> Thông tin tài sản</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="form-group div_building_place_id">
                                    <label for="exampleInputEmail1">Chọn tòa nhà:</label>
                                    <select id="ip_place_id_create" name="building_place_id" class="form-control" style="width: 100%;">
                                        <option value="">Chọn tòa nhà</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_asset_category_id">
                                    <label for="exampleInputEmail1">Loại tài sản:</label>
                                    <select name="asset_category_id" class="form-control select3" style="width: 100%;" id="asset_category_id">
                                        <option value="">Loại tài sản</option>
                                        @foreach ($assetCategory as $value)
                                        <option value="{{ $value->id }}" {{ $value->id == @$asset->asset_category_id ? 'selected' : '' }}>{{ $value->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_name">
                                        <label for="exampleInputEmail1">Tên tài sản:</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name') ?? @$asset->name }}">
                                        <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_code">
                                        <label for="exampleInputEmail1">Mã tài sản:</label>
                                        <input type="text" name="code" class="form-control" value="{{ old('code') ?? @$asset->code }}">
                                        <div class="message_zone"></div>
                                </div>
                                @if(!isset($id))
                                    <div class="form-group div_number">
                                        <label for="exampleInputEmail1">Số lượng:</label>
                                        <input type="number" name="number" class="form-control" value="{{ old('number') }}">
                                        <div class="message_zone"></div>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label class="control-label">Chọn file đính kèm</label>
                                    <div class="input-group input-image" data-file="image">
                                        <input type="file" multiple class="text-size text" id="attach_files" name="attach_files[]">
                                        <div class="list_image_task">
                                        </div>
                                        <a class="btn btn-default" id="clear" style="margin-top: 10px;cursor: pointer">Clear</a>
                                    </div>
                                </div>
                                <div class="form-group div_description">
                                    <label for="exampleInputEmail1">Mô tả:</label>
                                    <textarea class="form-control" id="description" name="description" rows="5">{{ old('description') ?? @$asset->description }}</textarea>
                                    <div class="message_zone"></div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                                <a href="{{ route('admin.asset-apartment.asset.index') }}" type="button"
                                   class="btn btn-default pull-left">Quay lại</a>
                                <button type="submit" class="btn btn-success" id="luu_tai_san" style="margin-left: 20px;">Lưu</button>
                        </div>
                        <!-- /.box-footer -->
                        <!-- /.box -->
                    </div>
                    <!--/.col (left) -->
                    <!-- right column -->

                    <!--/.col (right) -->
                </div>
            </form>
        </div>
        <input type="hidden" id="link_file_attach">
        <input type="hidden" value="{{isset($asset) ? $asset->documents : ''}}" id="infos_files_asset">
        <input type="hidden" value="{{isset($building_place_select) ? json_encode($building_place_select) : ''}}" id="building_place_select">
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
            if($("#infos_files_asset").val()){
                let get_list_file =JSON.parse($("#infos_files_asset").val());
                let html_list_file_task='';
                for (let index = 0; index < get_list_file.length; index++) {
                    addLinkFile('#link_file_attach',get_list_file[index],index)
                    html_list_file_task+='<div class="id_file_'+index+'"><a class="download" data-toggle="lightbox" data-gallery="attached-images-gallery" data-value_file="'+index+'" href="'+get_list_file[index]+'" style="height:15px;cursor: pointer;margin-bottom: 3px;">'+get_list_file[index].split('https://media.dxmb.vn/images/building_care/assets/')[1]+'</a><i id="iconRemoveFile" data-id_file_remove="'+index+'" class="fa fa-remove" style="cursor: pointer;color:red;margin-left:7px;"></i></div>';
                }
                $(".list_image_task").append(html_list_file_task);
                console.log(get_list_file)
            }
            if($("#building_place_select").val()){
                 // tòa nhà
                let building_place_select = JSON.parse($("#building_place_select").val());
                let building_place_array = [];
                building_place_array.push({
                                    id:building_place_select.id,
                                    text:building_place_select.text
                                });
                $('#ip_place_id_create').select2({data:building_place_array});
                $('#ip_place_id_create').find('option').attr('selected', true);
                $('#ip_place_id_create').select2();
            }
            // ========================================

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

       })
        //save tài sản
        $('#luu_tai_san').on('click', function (e) {
            var formCreate = new FormData($('#them_tai_san')[0]);
            if($('#link_file_attach').val() ){
                let new_array_files =  [];
                let array_files =  JSON.parse($("#link_file_attach").val());
                for (let index = 0; index < array_files.length; index++) {
                    new_array_files.push(array_files[index].url);
                }
                formCreate.append('attach_link_files', JSON.stringify(new_array_files));
            }
            if (!requestSend) {
                showLoading();
                requestSend = true;
                e.preventDefault();
                $.ajax({
                    url: $('#them_tai_san').attr('data-action'),
                    type: $('#them_tai_san').attr('method'),
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

    </script>
@endsection