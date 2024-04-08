@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        {{$meta_title}}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Quản lý tài liệu</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="clearfix"></div>
            <ul class="nav nav-tabs" role="tablist">
                <li class="active">
                    <a href="#document_building" role="tab" data-toggle="tab">
                        Quản lý tài liệu ban quản lý
                    </a>
                </li>
                <li>
                    <a href="#document_apartment" role="tab" data-toggle="tab">
                        Quản lý tài liệu căn hộ
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                @include('v3.documents.tabs.document-building')
                @include('v3.documents.tabs.document-apartment')
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')

    <script>
        $(function () {
            $("#file_other").change(function () {
                if (typeof (FileReader) != "undefined") {
                    var filePreview = $("#filePreview");
                    // dvPreview.html("");
                    // var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
                    $($(this)[0].files).each(function () {
                        var file = $(this);
                        if (file[0].name.toLowerCase()) {
                            var reader = new FileReader();
                            reader.onload = function (e) {
                                let boxChild = $("<div class='file-upload-item'><span class='image' data-name data-base=''></span>&ensp;<i class='fa fa-close btn-remove-image' onclick='removeThisFile(this)'></i></div>");
                                // boxChild.children('.image').attr("src", e.target.result);
                                boxChild.children('.image').attr("data-base", e.target.result);
                                boxChild.children('.image').attr("data-name", file[0].name.toLowerCase());
                                boxChild.children('.image').text(file[0].name.toLowerCase())
                                // img.attr("src", e.target.result);
                                filePreview.append(boxChild);
                            }
                            reader.readAsDataURL(file[0]);
                        } else {
                            alert(file[0].name + " is not a valid image file.");
                            filePreview.html("");
                            return false;
                        }
                    });
                } else {
                    alert("This browser does not support HTML5 FileReader.");
                }
            });

            $("#file_other_edit").change(function () {
                if (typeof (FileReader) != "undefined") {
                    var filePreview = $("#filePreviewEdit");
                    // dvPreview.html("");
                    // var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
                    $($(this)[0].files).each(function () {
                        var file = $(this);
                        if (file[0].name.toLowerCase()) {
                            var reader = new FileReader();
                            reader.onload = function (e) {
                                let boxChild = $("<div class='file-upload-item'><span class='image' data-name data-base=''></span>&ensp;<i class='fa fa-close btn-remove-image' onclick='removeThisFile(this)'></i></div>");
                                // boxChild.children('.image').attr("src", e.target.result);
                                boxChild.children('.image').attr("data-base", e.target.result);
                                boxChild.children('.image').attr("data-name", file[0].name.toLowerCase());
                                boxChild.children('.image').text(file[0].name.toLowerCase())
                                // img.attr("src", e.target.result);
                                filePreview.append(boxChild);
                            }
                            reader.readAsDataURL(file[0]);
                        } else {
                            alert(file[0].name + " is not a valid image file.");
                            filePreview.html("");
                            return false;
                        }
                    });
                } else {
                    alert("This browser does not support HTML5 FileReader.");
                }
            });

            $("#file_other1").change(function () {
                if (typeof (FileReader) != "undefined") {
                    var filePreview = $("#filePreview_1");
                    // dvPreview.html("");
                    // var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
                    $($(this)[0].files).each(function () {
                        var file = $(this);
                        if (file[0].name.toLowerCase()) {
                            var reader = new FileReader();
                            reader.onload = function (e) {
                                let boxChild = $("<div class='file-upload-item'><span class='image' data-name data-base=''></span>&ensp;<i class='fa fa-close btn-remove-image' onclick='removeThisFile(this)'></i></div>");
                                // boxChild.children('.image').attr("src", e.target.result);
                                boxChild.children('.image').attr("data-base", e.target.result);
                                boxChild.children('.image').attr("data-name", file[0].name.toLowerCase());
                                boxChild.children('.image').text(file[0].name.toLowerCase())
                                // img.attr("src", e.target.result);
                                filePreview.append(boxChild);
                            }
                            reader.readAsDataURL(file[0]);
                        } else {
                            alert(file[0].name + " is not a valid image file.");
                            filePreview.html("");
                            return false;
                        }
                    });
                } else {
                    alert("This browser does not support HTML5 FileReader.");
                }
            });

            $("#file_other1_edit").change(function () {
                if (typeof (FileReader) != "undefined") {
                    var filePreview = $("#filePreview_1_edit");
                    // dvPreview.html("");
                    // var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
                    $($(this)[0].files).each(function () {
                        var file = $(this);
                        if (file[0].name.toLowerCase()) {
                            var reader = new FileReader();
                            reader.onload = function (e) {
                                let boxChild = $("<div class='file-upload-item'><span class='image' data-name data-base=''></span>&ensp;<i class='fa fa-close btn-remove-image' onclick='removeThisFile(this)'></i></div>");
                                // boxChild.children('.image').attr("src", e.target.result);
                                boxChild.children('.image').attr("data-base", e.target.result);
                                boxChild.children('.image').attr("data-name", file[0].name.toLowerCase());
                                boxChild.children('.image').text(file[0].name.toLowerCase())
                                // img.attr("src", e.target.result);
                                filePreview.append(boxChild);
                            }
                            reader.readAsDataURL(file[0]);
                        } else {
                            alert(file[0].name + " is not a valid image file.");
                            filePreview.html("");
                            return false;
                        }
                    });
                } else {
                    alert("This browser does not support HTML5 FileReader.");
                }
            });


            $('.btn-js-action-add-document-building').on('click', function (e) {
                e.preventDefault();

                let alert_pop_add_document_building = $('.alert_pop_add_document_building');

                alert_pop_add_document_building.hide();

                let title = $('#title-document-building').val();
                let description = $('#description-document-building').val();
                let attach_files = [];

                $('#filePreview span').each((index, value) => {
                    attach_files.push({
                        file_name: $(value).attr('data-name'),
                        hash_file: $(value).attr('data-base')
                    })
                })

                // let listFiles = [];

                // let file = null;

                let formData = new FormData();

                attach_files.forEach((item)=>{
                    let file = dataURLtoFile(item.hash_file,item.file_name);
                    // listFiles.push(file);
                    formData.append('attach_files[]',file);
                });

                formData.append('title',title);
                formData.append('description',description);
                formData.append('document_type',1);

                if (title === "") {
                    alert_pop_add_document_building.show();
                    alert_pop_add_document_building.html('<li>Tiêu đề không được để  trông</li>')
                } else if (description === "") {
                    alert_pop_add_document_building.show();
                    alert_pop_add_document_building.html('<li>Mô tả không được để  trông</li>')
                } else if (attach_files.length == 0) {
                    alert_pop_add_document_building.show();
                    alert_pop_add_document_building.html('<li>File không được để  trông</li>')
                } else {
                    // attach_files = JSON.stringify(attach_files);

                    console.log("post")

                    showLoading();
                    $.ajax({
                        url: '/admin/v3/document',
                        type: 'POST',
                        data: formData,
                        contentType: false, //tell jquery to avoid some checks
                        processData: false,
                        success: function (res) {
                            console.log(res);
                            if(res.code===0) {
                                alert("Thêm tài liệu thành công");
                                location.reload();
                            }
                            else {
                                alert("Thêm tài liệu không thành công");
                                location.reload();
                            }
                            hideLoading();
                        },
                        error: function (e) {
                            console.log(e);
                            alert("Thêm tài liệu không thành công");
                            location.reload();
                            hideLoading();
                        }
                    })

                }

            });

            $('.btn-js-action-edit-document-building').on('click', function (e) {
                e.preventDefault();

                let alert_pop_edit_document_building = $('.alert_pop_edit_document_building');

                alert_pop_edit_document_building.hide();

                let id = $('#id-document-building-edit').attr('data-id');
                let title = $('#title-document-building-edit').val();
                let description = $('#description-document-building-edit').val();
                let attach_files = [];

                $('#filePreviewEdit span').each((index, value) => {
                    attach_files.push({
                        file_name: $(value).attr('data-name'),
                        hash_file: $(value).attr('data-base')
                    })
                })

                let formData = new FormData();

                attach_files.forEach((item)=>{
                    let file = dataURLtoFile(item.hash_file,item.file_name);
                    formData.append('attach_files[]',file);
                });

                formData.append('id',id);
                formData.append('title',title);
                formData.append('description',description);
                formData.append('document_type',1);

                if (title === "") {
                    alert_pop_edit_document_building.show();
                    alert_pop_edit_document_building.html('<li>Tiêu đề không được để  trông</li>')
                } else if (description === "") {
                    alert_pop_edit_document_building.show();
                    alert_pop_edit_document_building.html('<li>Mô tả không được để  trông</li>')
                } else if (attach_files.length == 0) {
                    alert_pop_edit_document_building.show();
                    alert_pop_edit_document_building.html('<li>File không được để  trông</li>')
                } else {
                    attach_files = JSON.stringify(attach_files);

                    showLoading();
                    $.ajax({
                        url: '/admin/v3/document/update',
                        type: 'POST',
                        data: formData,
                        contentType: false, //tell jquery to avoid some checks
                        processData: false,
                        success: function (res) {
                            if(res.code===0) {
                                alert("Cập nhật tài liệu thành công");
                                location.reload();
                            }
                            else {
                                alert("Cập nhật tài liệu không thành công");
                                location.reload();
                            }
                            $('#edit-document-building').modal('hide');
                            hideLoading();
                        },
                        error: function (e) {
                            console.log(e);
                            alert("Thêm tài liệu không thành công");
                            location.reload();
                            hideLoading();
                        }
                    })

                }

            });

            $('.edit-document-building').on('click', function (){
                $('#filePreviewEdit').html("");
                let row = $(this).closest('.document-row');

                let id = row.find('.document_id').attr('data-id');
                // let title = row.find('.document_title').text();
                // let description = row.find('.document_description').text();
                // let attach_file = row.find('.attach_file').children();
                // attach_file.clone().appendTo('#filePreviewEdit');

                showLoading();

                $.ajax({
                    url: '/admin/v3/document/show',
                    type: 'POST',
                    data: {
                        id: id,
                    },
                    success: function (res) {
                        console.log(res);
                        let document = res.data;
                        document.attach_file.forEach((item,index)=>{
                            row.find('.image').eq(index).attr('data-base',item.hash_code);
                        });

                        let attach_file = row.find('.attach_file').children();
                        attach_file.clone().appendTo('#filePreviewEdit');
                        $('#title-document-building-edit').val(document.title);
                        $('#description-document-building-edit').val(document.description);
                        $('#id-document-building-edit').attr('data-id',document.id);
                        $('#edit-document-building').modal('show');

                        hideLoading();
                    },
                    error: function (e) {
                        console.log(e);
                    }
                })


            });

            $('.edit-document-apartment').on('click', function (){
                $('#filePreview_1_edit').html("");
                let apartment_list_edit = $('#apartment_list-edit');
                let apartment_group_list_edit = $('#apartment_group_list-edit');
                apartment_list_edit.html("");
                apartment_group_list_edit.html("");
                let row = $(this).closest('.document-row');

                let id = row.find('.document_id').attr('data-id');
                let title = row.find('.document_title').text();
                let description = row.find('.document_description').text();
                // let attach_file = row.find('.attach_file').children();
                // attach_file.clone().appendTo('#filePreview_1_edit');
                let apart_list = row.find('.document_ap_list').children();
                apart_list.clone().appendTo("#apartment_list-edit");
                let ap_gr_list = row.find('.document_ap_gr_list').children();
                ap_gr_list.clone().appendTo("#apartment_group_list-edit");
                let document_type = row.find('.document_type').attr('data-id');

                get_data_select2({
                    object: '#apartment_group_list-edit',
                    url: '{{ url('admin/v3/document/get_list_apartment_group') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn nhóm Căn hộ'
                });

                get_data_select2({
                    object: '#apartment_list-edit',
                    url: '{{ url('admin/v3/document/get_list_apartment') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn Căn hộ'
                });
                let inputType = $('input[type=radio][name=document_type_edit]');

                if (document_type==2) {
                    apartment_list_edit.prop("disabled", false);
                    apartment_group_list_edit.prop("disabled", true);
                    inputType.filter('[value=2]').prop('checked', true);
                    inputType.filter('[value=3]').prop('checked', false);
                }
                else {
                    apartment_list_edit.prop("disabled", true);
                    apartment_group_list_edit.prop("disabled", false);
                    inputType.filter('[value=2]').prop('checked', false);
                    inputType.filter('[value=3]').prop('checked', true);
                }

                $('#title-document-apartment-edit').val(title.trim());
                $('#description-document-apartment-edit').val(description.trim());
                $('#id-document-apartment-edit').attr('data-id',id);

                showLoading();

                $.ajax({
                    url: '/admin/v3/document/show',
                    type: 'POST',
                    data: {
                        id: id,
                    },
                    success: function (res) {
                        console.log(res);
                        let document = res.data;
                        document.attach_file.forEach((item,index)=>{
                            row.find('.image').eq(index).attr('data-base',item.hash_code);
                        });

                        let attach_file = row.find('.attach_file').children();
                        attach_file.clone().appendTo('#filePreview_1_edit');
                        // $('#title-document-building-edit').val(document.title);
                        // $('#description-document-building-edit').val(document.description);
                        // $('#id-document-building-edit').attr('data-id',document.id);
                        $('#edit-document-apartment').modal('show');

                        hideLoading();
                    },
                    error: function (e) {
                        console.log(e);
                    }
                })

                // $('#edit-document-apartment').modal('show');

            });

            let apartment_list = $('#apartment_list');
            let apartment_group_list = $('#apartment_group_list');
            apartment_list.select2();
            apartment_group_list.select2();
            apartment_list.prop("disabled", true);

            $('input[type=radio][name=document_type]').change(function() {
                let apartment_list = $('#apartment_list');
                let apartment_group_list = $('#apartment_group_list');
                let apartment_group_list_edit = $('#apartment_group_list-edit');
                let apartment_list_edit = $('#apartment_list-edit');
                // apartment_list.select2();
                // apartment_group_list.select2();
                // apartment_list_edit.select2();
                // apartment_group_list_edit.select2();

                get_data_select2({
                    object: '#apartment_group_list',
                    url: '{{ url('admin/v3/document/get_list_apartment_group') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn nhóm Căn hộ'
                });

                get_data_select2({
                    object: '#apartment_list',
                    url: '{{ url('admin/v3/document/get_list_apartment') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn Căn hộ'
                });

                if(this.value==3) {
                    apartment_list.prop("disabled", true);
                    apartment_list_edit.prop("disabled", true);
                    apartment_group_list.prop("disabled", false);
                    apartment_group_list_edit.prop("disabled", false);
                }
                else {
                    apartment_list.prop("disabled", false);
                    apartment_list_edit.prop("disabled", false);
                    apartment_group_list.prop("disabled", true);
                    apartment_group_list_edit.prop("disabled", true);
                }
            });

            $('input[type=radio][name=document_type_edit]').change(function() {

                // apartment_list.select2();
                // apartment_group_list.select2();
                // apartment_list_edit.select2();
                // apartment_group_list_edit.select2();



                get_data_select2({
                    object: '#apartment_group_list-edit',
                    url: '{{ url('admin/v3/document/get_list_apartment_group') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn nhóm Căn hộ'
                });

                get_data_select2({
                    object: '#apartment_list-edit',
                    url: '{{ url('admin/v3/document/get_list_apartment') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn Căn hộ'
                });

                let apartment_list = $('#apartment_list');
                let apartment_group_list = $('#apartment_group_list');
                let apartment_group_list_edit = $('#apartment_group_list-edit');
                let apartment_list_edit = $('#apartment_list-edit');

                if(this.value==3) {
                    apartment_list.prop("disabled", true);
                    apartment_list_edit.prop("disabled", true);
                    apartment_group_list.prop("disabled", false);
                    apartment_group_list_edit.prop("disabled", false);
                }
                else {
                    apartment_list.prop("disabled", false);
                    apartment_list_edit.prop("disabled", false);
                    apartment_group_list.prop("disabled", true);
                    apartment_group_list_edit.prop("disabled", true);
                }
            });

            $('.btn-js-action-add-document-apartment').on('click', function (e) {
                e.preventDefault();

                let alert_pop_add_document_apartment = $('.alert_pop_add_document_apartment');

                alert_pop_add_document_apartment.hide();

                let title = $('#title-document-apartment').val();
                let description = $('#description-document-apartment').val();
                let attach_files = [];
                let apartment_list = $('#apartment_list').val();
                let apartment_group_list = $('#apartment_group_list').val();

                $('#filePreview_1 span').each((index, value) => {
                    attach_files.push({
                        file_name: $(value).attr('data-name'),
                        hash_file: $(value).attr('data-base')
                    })
                })

                let formData = new FormData();

                attach_files.forEach((item)=>{
                    let file = dataURLtoFile(item.hash_file,item.file_name);
                    // listFiles.push(file);
                    formData.append('attach_files[]',file);
                });

                let document_type = $('input[name="document_type"]:checked').val();

                let document_type_ids = [];

                console.log(document_type);

                formData.append('title',title);
                formData.append('description',description);
                formData.append('document_type',document_type);

                if(document_type==2) {
                    apartment_list.forEach((item)=>{
                        document_type_ids.push(item);
                    })
                    if(document_type_ids.length==0) {
                        alert_pop_add_document_apartment.show();
                        alert_pop_add_document_apartment.html('<li>Chọn ít nhất một căn hộ</li>')
                        return;
                    }
                }
                else if(document_type==3) {
                    apartment_group_list.forEach((item)=>{
                        document_type_ids.push(item);
                    })
                    if(document_type_ids.length==0) {
                        alert_pop_add_document_apartment.show();
                        alert_pop_add_document_apartment.html('<li>Chọn ít nhất một nhóm căn hộ</li>')
                        return;
                    }
                }

                if (title === "") {
                    alert_pop_add_document_apartment.show();
                    alert_pop_add_document_apartment.html('<li>Tiêu đề không được để  trông</li>')
                } else if (description === "") {
                    alert_pop_add_document_apartment.show();
                    alert_pop_add_document_apartment.html('<li>Mô tả không được để  trông</li>')
                } else if (attach_files.length == 0) {
                    alert_pop_add_document_apartment.show();
                    alert_pop_add_document_apartment.html('<li>File không được để  trông</li>')
                }
                else {
                    document_type_ids = JSON.stringify(document_type_ids);
                    // attach_files = JSON.stringify(attach_files);
                    formData.append('document_type_ids',document_type_ids);
                    showLoading();
                    $.ajax({
                        url: '/admin/v3/document',
                        type: 'POST',
                        data: formData,
                        contentType: false, //tell jquery to avoid some checks
                        processData: false,
                        success: function (res) {
                            $('#add-document-apartment').modal('hide')
                            if(res.code===0) {
                                alert("Thêm tài liệu thành công");
                                location.reload();
                            }
                            else {
                                alert("Thêm tài liệu không thành công");
                                location.reload();
                            }
                            hideLoading();
                        },
                        error: function (e) {
                            console.log(e);
                            alert("Thêm tài liệu không thành công");
                            location.reload();
                            hideLoading();
                        }
                    })
                }

            });

            $('.btn-js-action-edit-document-apartment').on('click', function (e) {
                e.preventDefault();

                let alert_pop_edit_document_apartment = $('.alert_pop_edit_document_apartment');

                alert_pop_edit_document_apartment.hide();

                let id = $('#id-document-apartment-edit').attr('data-id');
                let title = $('#title-document-apartment-edit').val();
                let description = $('#description-document-apartment-edit').val();
                let attach_files = [];
                let apartment_list = $('#apartment_list-edit').val();
                let apartment_group_list = $('#apartment_group_list-edit').val();

                $('#filePreview_1_edit span').each((index, value) => {
                    attach_files.push({
                        file_name: $(value).attr('data-name'),
                        hash_file: $(value).attr('data-base')
                    })
                })

                let formData = new FormData();

                attach_files.forEach((item)=>{
                    let file = dataURLtoFile(item.hash_file,item.file_name);
                    // listFiles.push(file);
                    formData.append('attach_files[]',file);
                });

                formData.append('id',id);
                formData.append('title',title);
                formData.append('description',description);

                let document_type = $('.document-type-edit input[name="document_type_edit"]:checked').val();

                let document_type_ids = [];

                console.log(document_type);

                formData.append('document_type',document_type);

                if(document_type==2) {
                    apartment_list.forEach((item)=>{
                        document_type_ids.push(item);
                    })
                    if(document_type_ids.length==0) {
                        alert_pop_edit_document_apartment.show();
                        alert_pop_edit_document_apartment.html('<li>Chọn ít nhất một căn hộ</li>')
                        return;
                    }
                }
                else if(document_type==3) {
                    apartment_group_list.forEach((item)=>{
                        document_type_ids.push(item);
                    })
                    if(document_type_ids.length==0) {
                        alert_pop_edit_document_apartment.show();
                        alert_pop_edit_document_apartment.html('<li>Chọn ít nhất một nhóm căn hộ</li>')
                        return;
                    }
                }

                if (title === "") {
                    alert_pop_edit_document_apartment.show();
                    alert_pop_edit_document_apartment.html('<li>Tiêu đề không được để  trông</li>')
                } else if (description === "") {
                    alert_pop_edit_document_apartment.show();
                    alert_pop_edit_document_apartment.html('<li>Mô tả không được để  trông</li>')
                } else if (attach_files.length == 0) {
                    alert_pop_edit_document_apartment.show();
                    alert_pop_edit_document_apartment.html('<li>File không được để  trông</li>')
                }
                else {
                    document_type_ids = JSON.stringify(document_type_ids);
                    // attach_files = JSON.stringify(attach_files);
                    formData.append('document_type_ids',document_type_ids);
                    showLoading();
                    $.ajax({
                        url: '/admin/v3/document/update',
                        type: 'POST',
                        data: formData,
                        contentType: false, //tell jquery to avoid some checks
                        processData: false,
                        success: function (res) {
                            $('#add-document-apartment').modal('hide')
                            if(res.code===0) {
                                alert("Cập nhật tài liệu thành công");
                                location.reload();
                            }
                            else {
                                alert("Cập nhật tài liệu không thành công");
                                location.reload();
                            }
                            $('#edit-document-apartment').modal('hide');
                            hideLoading();
                        },
                        error: function (e) {
                            console.log(e);
                            alert("Thêm tài liệu không thành công");
                            location.reload();
                            hideLoading();
                        }
                    })
                }
            })


            $('.delete-asset').on('click',function (){
                let id = $(this).attr('data-id');
                let check = confirm('Bạn có chắc chắn muốn xóa không?');
                if(check) {
                    showLoading();
                    console.log(id);

                    let ids = [id];

                    ids = JSON.stringify(ids);

                    $.ajax({
                        url: '/admin/v3/document/delete',
                        type: 'POST',
                        data: {
                            ids: ids
                        },
                        success: function (response) {

                            if (response.code === 0) {
                                alert("Xóa tài liệu thành công");
                                $("#asset_area").load(" #asset_area");
                                location.reload();
                            } else {
                                alert("Xóa tài liệu không thành công");
                                location.reload();
                            }
                            hideLoading();
                        },
                        error: function (e) {
                            console.log(e);
                            hideLoading();
                        }
                    })
                }
            })

            get_data_select2({
                object: '#apartment_group_list',
                url: '{{ url('admin/v3/document/get_list_apartment_group') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn nhóm Căn hộ'
            });

            get_data_select2({
                object: '#apartment_list',
                url: '{{ url('admin/v3/document/get_list_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn Căn hộ'
            });

            get_data_select2({
                object: '#apartment_group_list-edit',
                url: '{{ url('admin/v3/document/get_list_apartment_group') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn nhóm Căn hộ'
            });

            get_data_select2({
                object: '#apartment_list-edit',
                url: '{{ url('admin/v3/document/get_list_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn Căn hộ'
            });

            function get_data_select2(options) {
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

            function deleteDocument(id) {
                let check = confirm('Bạn có chắc chắn muốn xóa không?');
                if(check) {
                    showLoading();
                    console.log(id);

                    let ids = [id];

                    ids = JSON.stringify(ids);

                    $.ajax({
                        url: '/admin/v3/document/delete',
                        type: 'POST',
                        data: {
                            ids: ids
                        },
                        success: function (response) {

                            if (response.code === 0) {
                                alert("Xóa tài liệu thành công");
                                $("#asset_area").load(" #asset_area");
                                location.reload();
                            } else {
                                alert("Xóa tài liệu không thành công");
                                location.reload();
                            }
                            hideLoading();
                        },
                        error: function (e) {
                            console.log(e);
                            hideLoading();
                        }
                    })
                }
            }

            function dataURLtoFile(dataurl, filename) {

                var arr = dataurl.split(','),
                    mime = arr[0].match(/:(.*?);/)[1],
                    bstr = atob(arr[1]),
                    n = bstr.length,
                    u8arr = new Uint8Array(n);

                while(n--){
                    u8arr[n] = bstr.charCodeAt(n);
                }

                return new File([u8arr], filename, {type:mime});
            }

        })
    </script>
@endsection
