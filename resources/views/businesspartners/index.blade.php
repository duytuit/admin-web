@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý đối tác
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Thông tin đối tác</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="box-body">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li><a data-toggle="tab" href="#doi_tac">Thông tin đối tác</a></li>
                    </ul>
                    <div class="tab-content">
                        @include('businesspartners.tabs.businesspartner-info')
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
                var partners_id = $(this).attr('id');
                $('.add').text('Cập nhật');
                $.ajax({
                    url: 'partners/'+partners_id+'/edit',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#partners_id').val(data.id);
                        $('#name').val(data.name);
                        $('#contact').val(data.contact);
                        $('#address').val(data.address);
                        $('#mobile').val(data.mobile);
                        $('#email').val(data.email);
                        $('#representative').val(data.representative);
                        $('#position').val(data.position);
                        $('#description').val(data.description);
                        $('#createBusinessPartner').modal('show');
                    }
                })
            });

            // create and update in category
            $('.add').click(function() {
               if($('#modal-partners-category').validate().form()){
                    if ( $('.add').text() == 'Thêm mới' ) {
                    var values = $('#modal-partners-category').serializeArray();
                    $.ajax({
                        url: 'partners/store',
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
                                toastr.error('Thêm mới đối tác không thành công!');
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
                    var partners_id = $('#partners_id').val();
                    $.ajax({
                        url: 'partners/'+partners_id+'/update',
                        method: 'POST',
                        dataType: 'json',
                        data: $('#modal-partners-category').serialize(),
                        success: function(response) {
                            if (response.success == true) {
                                toastr.success(response.message);

                                setTimeout(() => {
                                    location.reload()
                                }, 1000)
                            } else {
                                toastr.error('Cập nhật đối tác không thành công!');
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
        $(document).on('change', 'select[name="per_page_business_partners"]', function () {
            $('#form-partners').submit();
        });

        $(document).on('change', 'select[name="per_page_service_partners"]', function () {
            $('#form-service-partners').submit();
        })


        // --------------------------------------------------------------
          // edit
            $('.edit-service-partners').click(function() {
                var service_partners_id = $(this).attr('id');
                $('.add-service-partners').text('Cập nhật');
                $.ajax({
                    url: 'service-partners/'+service_partners_id+'/edit',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#service_partners_id').val(data.id);
                        $('#timeorder').val(data.timeorder);
                        $('#description').val(data.description);
                        $('#createServicePartner').modal('show');
                    }
                })
            });

            // create and update in category
            $('.add-service-partners').click(function() {
                if ( $('.add-service-partners').text() == 'Thêm mới' ) {
                    var values = $('#modal-service-partners-category').serializeArray();
                    $.ajax({
                        url: 'service-partners/store',
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
                                toastr.error('Thêm mới đăng ký dịch vụ không thành công!');
                            }
                        },
                        error: function(response) {
                            $('#errors').empty();
                            $('#errors').parent().removeClass('alert alert-danger');
                            showErrors(response.responseJSON.errors);
                        }
                    })
                }

                if( $('.add-service-partners').text() == 'Cập nhật' ) {
                    var service_partners_id = $('#service_partners_id').val();
                    $.ajax({
                        url: 'service-partners/'+service_partners_id+'/update',
                        method: 'POST',
                        dataType: 'json',
                        data: $('#modal-service-partners-category').serialize(),
                        success: function(response) {
                            if (response.success == true) {
                                toastr.success(response.message);

                                setTimeout(() => {
                                    location.reload()
                                }, 1000)
                            } else {
                                toastr.error('Cập nhật đăng ký không thành công!');
                            }
                        },
                        error: function(response) {
                            $('#errors').empty();
                            showErrors(response.responseJSON.errors);
                        }
                    })
                }
            });
            
          $(function () {
              get_data_select_partners({
                object: '#ip-partners',
                url: '{{ url('admin/service-partners/ajax_get_partners') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn đối tác'
            });
            get_data_select_handbooks({
                object: '#ip-handbooks',
                url: '{{ url('admin/service-partners/ajax_get_building_handbooks') }}',
                data_id: 'id',
                data_text: 'title',
                title_default: 'Chọn bài viết'
            });
            function get_data_select_partners(options) {
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

            function get_data_select_handbooks(options) {
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
          })
           $('#modal-partners-category').validate({
                rules: {
                    name: {
                        minlength: 2,
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                     mobile: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10,
                    }
                },
                messages: {
                    name: "trường tên đối tác phải bắt buộc!",     
                    email: "trường email không đúng định dạng!",   
                    mobile: {
                        required: "Vui lòng nhập số điện thoại !",
                        digits: "Số điện thoại không hợp lệ !",
                        minlength: "Trường số điện thoại chỉ chấp nhận 10 chữ số",
                        maxlength: "Trường số điện thoại chỉ chấp nhận 10 chữ số",
                    },   
                },
                
                submitHandler: function(form) {
                    form.submit();
                }
                
            });
</script>
@endsection
