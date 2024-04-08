@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Cẩm nang tòa nhà
        <small>Thông tin cẩm nang</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Thông tin cẩm nang</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="box-body">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li @if(request()->exists('handbook_keyword') || (!request()->exists('handbook_keyword') &&
                            !request()->exists('handbook_categories_keyword') && !request()->exists('keyword') )) class="active" @endif"><a
                                data-toggle="tab" href="#bai_viet">Bài
                                viết</a></li>
                        <li @if(request()->exists('handbook_categories_keyword')) class="active" @endif"><a
                                data-toggle="tab" href="#danh_muc">Danh mục</a></li>
                        {{--
                         <li @if(request()->exists('keyword')) class="active" @endif"><a
                                data-toggle="tab" href="#phan_loai">Phân loại</a></li>
                                --}}
                    </ul>
                    <div class="tab-content">
                        @include('building-handbook.tabs.handbook')
                        @include('building-handbook.tabs.handbook-category')
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('javascript')
    <!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script>
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.modal').on('hidden.bs.modal', function(){
            $('.add').text('Thêm mới');
            $(this).find('form')[0].reset();
            $('#errors').empty();
            $('#errors').parent().removeClass('alert alert-danger');
            $('.modal-footer').show();
        });

        $(document).ready(function() {
            // HANDBOOK
            // delete multi handbook
            $('#delete-multi-handbooks').click(function() {
                var ids = [];
                var div = $('div.icheckbox_square-green[aria-checked="true"]');
                div.each(function (index, value) {
                    var id = $(value).find('input.checkSingle').val();
                    if (id) {
                        ids.push(id);
                    }
                });
                if (ids.length == 0) {
                    toastr.error('Vui lòng chọn cẩm nang để thực hiện tác vụ này');
                } else {
                    if (!confirm('Bạn có chắc chắn muốn xóa những cẩm nang này?')) {
                        e.preventDefault();
                    } else {
                        $.ajax({
                            url: $(this).attr('data-action'),
                            type: 'POST',
                            data: {
                                ids: ids
                            },
                            success: function (response) {
                                if (response.success == true) {
                                    toastr.success(response.message);

                                    setTimeout(() => {
                                        location.reload()
                                    }, 1000)
                                } else {
                                    toastr.error('Không thể xóa những cẩm nang này!');
                                }
                            }
                        })
                    }
                }
            });

            // push handbook
            $('.push-handbook').click(function() {
                var id = $(this).attr('data-id');
                $.ajax({
                    url: `building-handbook/${id}/ajax_change_status`,
                    type: 'POST',
                    success: function(response) {
                        location.reload();
                    }
                })
            });

            // HANDBOOK-CATEGORY
            // delete multi handbook-categories
            $('#delete-multi-handbook-categories').click(function(e) {
                var ids = [];
                var div = $('div.icheckbox_square-green[aria-checked="true"]');
                div.each(function(index, value) {
                    var id = $(value).find('input.checkSingle').val();
                    if (id) {
                        ids.push(id);
                    }
                });
                if( ids.length == 0 ) {
                    toastr.error('Vui lòng chọn danh mục để thực hiện tác vụ này');
                } else {
                    if (!confirm('Bạn có chắc chắn muốn xóa những danh mục này?')) {
                    e.preventDefault();
                    } else {
                        $.ajax({
                            url: $(this).attr('data-action'),
                            type: 'POST',
                            data: {
                                ids: ids
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    toastr.success(response.message);

                                    setTimeout(() => {
                                        location.reload()
                                    }, 1000)
                                } else {
                                    toastr.error('Không thể xóa những danh mục này');
                                }
                            }
                        })
                    }
                }
            });

            // edit
            $('.edit').click(function() {
                var category_id = $(this).attr('id');
                $('.add').text('Cập nhật');
                $.ajax({
                    url: 'building-handbook/category/'+category_id+'/edit',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#parent_id').empty();
                        addParent(data.parent_categories);
                        $('#name').val(data.category_name);
                        $('#phone').val(data.category_phone);
                        $('#parent_id').val(data.parent_id);
                        if(data.avatar){
                          $('#fileName').text(data.avatar['name_image']); 
                          $('#fileImage').attr('src',data.avatar['url_image']);
                        }else{
                          $('#fileName').text(''); 
                          $('#fileImage').attr('src','');
                        }
                        $('#bdc_handbook_type_id_123').val(data.bdc_handbook_type_id);
                        $('#createHandbookCategory').modal('show');
                        $('#category_id').val(category_id);
                    }
                })
            });

            // show
            $('.title').click(function() {
                var category_id = $(this).attr('data-id');
                $.ajax({
                    url: 'building-handbook/category/'+category_id+'/edit',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#parent_id').empty();
                        addParent(data.parent_categories);
                        $('#name').val(data.category_name);
                        $('#phone').val(data.phone);
                        $('#parent_id').val(data.parent_id);
                        $('#bdc_handbook_type_id').val(data.bdc_handbook_type_id);
                        $('.modal-footer').hide();
                        $('#createHandbookCategory').modal('show');
                        $('#category_id').val(category_id);
                    }
                })
            });

            // change status in handbook-category
            $('.status').click(function() {
                var data_status = $(this).attr('data-status');
                var id          = $(this).attr('data-id');
                if ( data_status == 1 ) {
                    $(this).removeClass('btn-success');
                    $(this).addClass('btn-danger');
                    $(this).text('Inactive');
                }
                if ( data_status == 0 ) {
                    $(this).removeClass('btn-danger');
                    $(this).addClass('btn-success');
                    $(this).text('Active');
                }

                var self = $(this);
                $.ajax({
                        url: `building-handbook/category/${id}/ajax_change_status`,
                        method: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            self.attr('data-status', response.status);
                        },
                    })
            });

            // create and update in category
            $('.add').click(function() {
                if ( $('.add').text() == 'Thêm mới' ) {
                    var values = $('#modal-handbook-category').serializeArray();
                    if($('#inputFile').val() ){
                    var bla = $('#custId').val();
                    var file_name= $('#inputFile').prop('files')[0].name; 
                        values.push({name:"name_fileupload",value:file_name });
                        values.push({name:"fileBase64",value:bla });
                    }
                    $.ajax({
                        url: 'building-handbook/category/store',
                        method: 'POST',
                        dataType: 'json',
                        data: values,
                        success: function(response) {
                            $('#errors').empty();
                            $('#errors').parent().removeClass('alert alert-danger');
                            if (response.success == true) {
                                toastr.success(response.message);

                                setTimeout(() => {
                                    location.reload()
                                }, 1000)
                            } else {
                                toastr.error('Thêm mới danh mục không thành công!');
                            }
                        },
                        error: function(response) {
                            $('#errors').empty();
                            $('#errors').parent().removeClass('alert alert-danger');
                            showErrors(response.responseJSON.errors);
                        }
                    })
                }

                if( $('.add').text() == 'Cập nhật' ) {
                    var category_id = $('#category_id').val();
                    var values = $('#modal-handbook-category').serializeArray();
                    if($('#inputFile').val() ){
                    var bla = $('#custId').val();
                    var file_name= $('#inputFile').prop('files')[0].name; 
                        values.push({name:"name_fileupload",value:file_name });
                        values.push({name:"fileBase64",value:bla });
                    }
                    $.ajax({
                        url: 'building-handbook/category/'+category_id+'/update',
                        method: 'POST',
                        dataType: 'json',
                        data: values,
                        success: function(response) {
                            if (response.success == true) {
                                toastr.success(response.message);

                                setTimeout(() => {
                                    location.reload()
                                }, 1000)
                            } else {
                                toastr.error('Cập nhật danh mục không thành công!');
                            }
                        },
                        error: function(response) {
                            $('#errors').empty();
                            showErrors(response.responseJSON.errors);
                        }
                    })
                }

                function showErrors(errors) {
                    var ul = $('#errors');
                    ul.parent().addClass('alert alert-danger');
                    $.each(errors, function(i, item) {
                        if (item != '') {
                            ul.append('<li>' + item + '</li>')
                        }
                    });
                }
            })

            //onchange select type category
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
                       // addParent(response.all_categories);
                        if ( !jQuery.isEmptyObject(response.categories) ) {
                            $('#bdc_handbook_category_id').empty();
                            $('#bdc_handbook_category_id').append('<option value="" selected>Danh Mục</option>')
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
                            $('#bdc_handbook_category_id').append('<option value="" selected>Danh Mục</option>')
                        }

                    }
                })
            });

            function addParent(parent_categories) {
                $('#parent_id').empty();
                $('#parent_id').append('<option value="0">Không có</option>');
                if ( !jQuery.isEmptyObject(parent_categories) ) {
                    $.each(parent_categories, function(index, val) {
                            if(val.parent_id == 0){
                               $('#parent_id').append('<option value="'+ val.id +'">'+val.name+'</option>')
                                  $.each(parent_categories, function(index, val1) {
                                    if(val.id == val1.parent_id){
                                     $('#parent_id').append('<option value="'+ val1.id +'">-- '+val1.name+'</option>')
                                        $.each(parent_categories, function(index, val2) {
                                            if(val1.id == val2.parent_id){
                                                $('#parent_id').append('<option value="'+ val2.id +'">------ '+val2.name+'</option>')
                                                 $.each(parent_categories, function(index, val3) {
                                                    if(val2.id == val3.parent_id){
                                                        $('#parent_id').append('<option value="'+ val3.id +'">---------- '+val3.name+'</option>')
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            } 
                    });
                }
            }
        });

        $(document).on('change', 'select[name="per_page_handbook"]', function () {
            $('#form-handbook').submit();
        });

        $(document).on('change', 'select[name="per_page_handbook_category"]', function () {
            $('#form-handbook-category').submit();
        })
</script>
@endsection