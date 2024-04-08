@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Cẩm nang tòa nhà
        <small>Cập nhật</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Cẩm nang tòa nhà</li>
    </ol>
</section>

<section class="content">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <form
        data-action="{{ ($id == 0) ? route('admin.building-handbook.store') : route('admin.building-handbook.update', ['id' => $id]) }}"
        method="POST" id="create_handbook" class="form-validate">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                <div class="box">
                    <div class="box-body no-padding">
                        <div class="nav-tabs-custom no-margin">

                            <div class="tab-content">
                                <!-- Thông tin cẩm nang -->
                                <div class="tab-pane active" id="partner">
                                    <div class="row">
                                        <div
                                            class="col-sm-12 col-xs-12 form-group div_title">
                                            <label class="control-label">Tiêu đề <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" placeholder="Tiêu đề"
                                                value="{{ $bdh->title ?? old('title') ?? $bdh->title ?? ''}}" />
                                            <div class="message_zone"></div>
                                        </div>
                                    </div>

                                    <div class="form-group div_content">
                                        <label class="control-label">Nội dung</label>
                                        <div class="message_zone"></div>
                                        <!-- <textarea id="description" name="content" rows="10" class="form-control mceEditor"> -->
                                         <textarea name="content" rows="10" class="form-control mceEditor">
                                          {{ $bdh->content ?? old('content') ?? $bdh->content ?? '' }}
                                        </textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="box">
                    <div class="box-body">
                        <div class="tab-content">
                            <div class="form-group div_bdc_handbook_type_id">
                                <label class="control-label">Phân loại</label>
                                <select name="bdc_handbook_type_id" class="form-control select3" style="width: 100%;"
                                    id="bdc_handbook_type_id">
                                    <option value="">Chọn phân loại</option>
                                    @foreach ($types as $type)
                                    <option value="{{ $type->id }}" {{ $type->id == @$bdh_type ? 'selected' : '' }}>
                                        {{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <div class="message_zone"></div>
                            </div>
                            <div class="form-group div_bdc_handbook_category_id">
                                <label class="control-label">Danh mục</label>
                                <select name="bdc_handbook_category_id" class="form-control select3"
                                    style="width: 100%;" id="bdc_handbook_category_id">
                                    <option value="">Chọn danh mục</option>
                                    @isset($type_categories)
                                    @foreach ($type_categories as $type_category)
                                             <option value="{{ $type_category->id }}"
                                             {{ @$type_category->id == @$bdh_category ? 'selected' : '' }}>
                                             {{ $type_category->name }}</option>
                                    @endforeach
                                    @endisset
                                </select>
                                <div class="message_zone"></div>
                            </div>
                            <div class="form-group div_order">
                                <label class="control-label">Link Video</label>
                                 <input type="text" name="url_video" value="{{ $bdh->url_video ?? '' }}"  class="form-control">
                                <div class="message_zone"></div>
                            </div>
                            <div class="form-group div_order">
                                <label class="control-label">Đối tác</label>
                                <select name="bdc_business_partners_id" class="form-control select3"
                                    style="width: 100%;" id="bdc_business_partners_id">
                                    <option value="">Chọn đối tác</option>
                                    @isset($partners)
                                    @foreach ($partners as $items)
                                             <option value="{{ $items->id }}"
                                             {{ $items->id == @$bdh_partners ? 'selected' : '' }}>
                                             {{ $items->name }}</option>
                                    @endforeach
                                    @endisset
                                </select>
                                <div class="message_zone"></div>
                            </div>
                             <!-- <div class="form-group div_order">
                                <label class="control-label">Thứ tự</label>
                                 <input type="number" name="order" min="1" value="1" class="form-control">
                                <div class="message_zone"></div>
                            </div> -->
                            <div class="form-group" style="margin-bottom: 25px;">
                                        <div>
                                            <label  class="btn col-md-12" style="background-color: #76bde6;border-color: #61bff5;font-weight: bold;">Upload Avatar
                                                <i class="fa fa-files-o" style="font-size: large;"></i>
                                                <input id='inputFile' name="inputFile" type="file" accept="image/*" style="display: none;"/>
                                            </label>
                                        </div>
                                        <div class="form-group" >
                                            <div id="fileName"  style="display: inline-flex;margin-top: 6px;">{{ $bdh_avatar['name_image'] ?? '' }}</div>
                                            <div style="display: inline-flex;float: right;">
                                            <i id="iconRemoveFile" class="fa fa-remove" style="display: none;margin-left: 5px;margin-top: 4px;cursor: pointer;font-size: x-large;"></i>
                                            </div> 
                                       </div>   
                                       <div>
                                            <img src="{{ $bdh_avatar['url_image'] ?? '' }}" id="fileImage" alt="" style="max-width: 100px;" />
                                       </div> 
                            </div>
                             <div class="form-group">
                                <div class="form-group">
                                    <label class="control-label">Bài giới thiệu</label><br />
                                    <div>
                                        <label class="switch">
                                            <input type="checkbox" name="feature" value="0"
                                                {{ @$bdh_feature != 0 ? '' : 'checked' }} />
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-group">
                                    <label class="control-label">Trạng thái</label><br />
                                    <div>
                                        <label class="switch">
                                            <input type="checkbox" name="status" value="1"
                                                {{ @$bdh_status != 1 ? '' : 'checked' }} />
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="form-group">
                                <label class="control-label">Logo</label>
                                <div class="input-group input-image" data-file="image">
                                    <input type="text" name="logo" value="" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
                                </div>
                            </div> --}}
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-sm btn-success" id="save_handbook" title="Cập nhật">
                                <i class="fa fa-save"></i>&nbsp;&nbsp;{{!empty($bdh) ? 'Cập nhật' : 'Thêm mới'}}</button>

                            <a href="{{ route('admin.building-handbook.index') }}" class="btn btn-danger btn-sm"><i
                                    class="fa fa-reply"></i> Quay lại</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <input type="hidden" id="custId">

    <!-- @include('building-handbook.modal.handbook_category') -->
</section>
@endsection
@section('javascript')


<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    function getBase64(file) {
        var preview = document.getElementById('fileImage');
        var reader = new FileReader();
        reader.addEventListener("load", function () {
            // convert image file to base64 string
            preview.src = reader.result;
        }, false);
        reader.readAsDataURL(file);
        reader.onload = function () {
           // console.log(reader.result);
            $("#custId").val(reader.result);
        };
        reader.onerror = function (error) {
            console.log('Error: ', error);
        };
    }
    $('#inputFile').change(function(e){
            let fileName = e.target.value.split(/(\\|\/)/g).pop();
            $('#fileName').text(fileName); 
            if(fileName){
                $('#iconRemoveFile').show().css("color", "red");
                $('#fileName').css("margin-left", "15px"); 
            }
          var  files_base64 = document.getElementById('inputFile').files;
            if (files_base64.length > 0) {
                getBase64(files_base64[0]);
            }
        })
       
    $("#iconRemoveFile").click(function() {
            $('#fileName').text(''); 
            $("#inputFile").val(null);
            $("#custId").val(null);
            $('#fileImage').attr('src', '');
            $(this).hide()
     });
    $(document).ready(function() {
        if( $('#fileName').text().length > 0 ){
            $('#iconRemoveFile').show().css("color", "red");
            $('#fileName').css("margin-left", "15px"); 
        }
        $('#bdc_handbook_type_id').change(function() {
            var bdc_handbook_type_id = $(this).val();
            $.ajax({
                url: "{{route('admin.building-handbook.ajax_get_category')}}",
                method: 'GET',
                data: {
                    // _token: $("[name='_token']").val(),
                    bdc_handbook_type_id: bdc_handbook_type_id,
                },
                dataType: 'json',
                success: function(response) {
                    //console.log(response);
                    if ( !jQuery.isEmptyObject(response.categories) ) {
                        $('#bdc_handbook_category_id').empty();
                        $('#bdc_handbook_category_id').append('<option value="">Chọn danh mục</option>')
                        $.each(response.categories, function(index, val) {
                            if(val.parent_id == 0){
                               $('#bdc_handbook_category_id').append('<option value="'+ val.id +'">'+val.name+'</option>')
                                  $.each(response.categories, function(index, val1) {
                                    if(val.id == val1.parent_id){
                                     $('#bdc_handbook_category_id').append('<option value="'+ val1.id +'">-- '+val1.name+'</option>')
                                        $.each(response.categories, function(index, val2) {
                                            if(val1.id == val2.parent_id){
                                                $('#bdc_handbook_category_id').append('<option value="'+ val2.id +'">------ '+val2.name+'</option>')
                                                 $.each(response.categories, function(index, val3) {
                                                    if(val2.id == val3.parent_id){
                                                        $('#bdc_handbook_category_id').append('<option value="'+ val3.id +'">---------- '+val3.name+'</option>')
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            } 
                        });
                    } else {
                        $('#bdc_handbook_category_id').empty();
                        $('#bdc_handbook_category_id').append('<option value="">Chọn danh mục</option>')
                        if( confirm("Chưa có danh mục cho phân loại này! Bạn có muốn tạo danh mục") ) {
                            // $.each(response.all_categories, function(index, val) {
                            // $('#parent_id').append('<option value="'+ val.id +'">'+val.name+'</option>')
                            // })
                            // $('#createHandbookCategory').modal('show');
                        }
                    }
                }
            })
        });

        // create category
        // $('.add').click(function() {
        //     $.ajax({
        //         url: "{{route('admin.building-handbook.category.store')}}",
        //         method: 'POST',
        //         dataType: 'json',
        //         data: $('#modal-handbook-category').serialize(),
        //         // data: $('#create_handbook').serialize(),
        //         success: function(response) {
        //             if (response.success == true) {
        //                 toastr.success(response.message);

        //                 setTimeout(() => {
        //                     location.reload()
        //                 }, 1000)
        //             } else {
        //                 toastr.error('Thêm mới danh mục không thành công!');
        //             }
        //         },
        //         error: function(response) {
        //             $('#errors').empty();
        //             showErrors(response.responseJSON.errors);
        //         }
        //     })

        //     function showErrors(errors) {
        //         var ul = $('#errors');
        //         ul.parent().addClass('alert alert-danger');
        //         $.each(errors, function(i, item) {
        //             if (item != '') {
        //                 ul.append('<li>' + item + '</li>')
        //             }
        //         });
        //     }
        //     })
    })

    //submit form save bai viet
    submitAjaxFormNew('#save_handbook', '#create_handbook', '.div_', '.message_zone');
    var requestSend = false;
    function submitAjaxFormNew(idButton, idForm, classError, classShowError) {
    $(document).on('click', idButton, function (e) {
        var bla = $('#custId').val();
        // var desc = tinymce.get("description").getContent();
        var desc = CKEDITOR.instances['content'].getData();
            var values = $(idForm).serializeArray();
            values.find(input => input.name == 'content').value = desc;
            if($('#inputFile').val() ){
              var file_name= $('#inputFile').prop('files')[0].name; 
                values.push({name:"name_fileupload",value:file_name });
                values.push({name:"fileBase64",value:bla });
            }
            var formCreate = $(idForm);
            if (!requestSend) {
                showLoading();
                requestSend = true;
                e.preventDefault();
                $.ajax({
                    url: formCreate.attr('data-action'),
                    type: formCreate.attr('method'),
                    data: values,
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                            if (!response.href) {
                                setTimeout(() => {
                                    location.reload()
                                }, 4000)
                            } else {
                                setTimeout(() => {
                                    window.location.href = response.href
                                }, 4000)
                            }
                        }else if (response.success == false) {
                            toastr.error(response.message);
                            if (!response.href) {
                                setTimeout(() => {
                                    location.reload()
                                }, 4000)
                            } else {
                                setTimeout(() => {
                                    window.location.href = response.href
                                }, 4000)
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
                        showErrorsCreate(response.responseJSON.errors, classError, classShowError);
                        hideLoading();
                        requestSend = false;
                    }
                })
            } else {
                e.preventDefault();
            }          
        
    });
}
</script>
@endsection