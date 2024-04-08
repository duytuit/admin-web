@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý chung
        <small>Thông tin tòa nhà</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Thông tin tòa nhà</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <div class="box-header with-border">
                <h3 class="box-title">Thông tin chung</h3>
            </div>
            <br>
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#thong_tin_toa_nha">Thông tin tòa nhà</a></li>
                        <li><a data-toggle="tab" href="#thong_tin_lien_he">Thông tin liên hệ</a></li>
                        <li><a data-toggle="tab" href="#thong_tin_thanh_toan">Thông tin thanh toán</a></li>
                        <li><a data-toggle="tab" href="#thong_tin_phuong_thuc_thanh_toan">Phương thức thanh toán</a></li>
                    </ul>
                </div>
            </div>
            <br>
            <div class="col-md-12">
                <div class="tab-content">
                    <br>
                    @include('building.tab.building_info')
                    @include('building.tab.basic_info')
                    @include('building.tab.payment_building')
                    @include('building.tab.payment_vpbank')
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('javascript')
<!-- TinyMCE -->
<!-- <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script> -->
<script>
    function removeMessage() {
            $(document).find('.has-error').removeClass('has-error');
            if ($(document).find('.help-block').length) {
                $(document).find('.help-block').remove();
            }
        }
        $('.nav-tabs a').click(function(){
            $(this).tab('show');
            removeMessage();
        });

        $('.modal').on('hidden.bs.modal', function(){
            removeMessage();
            $(this).find('form')[0].reset();
        });
        //create payment info
        submitAjaxForm('#add_payment', '#create_payment', '.div_', '.message_zone');

        //show modal edit payment info
        showModalForm('.edit-payment', '#editBankInfo');

        //submit edit payment info
        submitAjaxForm('#update_payment', '#edit_payment', '.create_', '.message_zone_create');

        //submit create edit payment vpbank info
        submitAjaxForm('#create_update_payment_vpbank', '#create_edit_payment_vpbank', '.create_', '.message_zone_create');

        //save building info
        //submitAjaxForm('#add_info', '#create_info', '.data_', '.message_zone_data');

        //show modal edit building info
        showModalForm('.edit-info', '#editBuildingInfo');

        //submit edit bulding info
        submitAjaxForm('#update_info', '#edit_info', '.update_', '.message_zone_update');

        //delete single payment_info
        deleteSubmit('.delete-payment');

        //delete single bulding info
        deleteSubmit('.delete-building-info');
        var object_list_method_payment = null;
        $(document).ready(function() {
            $('#update_config').click(function() {
                var form_data = $('#form_update_config').serialize();
                var url = $('#form_update_config').attr('data-action');
                var method = $('#form_update_config').attr('method');
                $.ajax({
                    url: url,
                    method: method,
                    data: form_data,
                    success: function(response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Sửa thông tin tòa nhà không thành công!');
                        }
                    }
                })
            })
            //
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")

            $('#pagination-panel').pagination({
                dataSource:  window.localStorage.getItem("base_url")+'payment/getListTransferFees' + param_query,
                locator: 'data',
                totalNumberLocator: function(response) {
                    return response.data.length
                },
                alias: {
                    pageNumber: 'page',
                    pageSize: 'limit'
                },
                pageSize: 10,
                ajax: {
                    beforeSend: function() {
                        // console.log();
                        $('#list_methodpayment').html('Loading data ...');
                    }
                },
                callback: function(data, pagination) {
                    if(data){
                        let html ='';
                        let stt=0;
                        object_list_method_payment = data;
                        data.forEach(element => {
                            stt++;
                            let abc = JSON.stringify(element);
                            html+= '<tr>'+
                                   '    <td>'+stt+'</td>'+
                                   '    <td>'+element.name+'</td>'+
                                   '    <td>'+element.fixed_charge+'</td>'+
                                   '    <td>'+element.fee_percentage+'</td>'+
                                   '    <td>'+
                                   '        <a data-element="'+element.type_payment+'"'+
                                   '        class="btn btn-xs btn-primary edit_method_payment" title="Sửa thông tin"><i'+
                                   '                    class="fa fa-pencil"></i></a>'+
                                   '        <a data-element="'+element.type_payment+'" class="btn btn-xs btn-danger delete_edit_method_payment"'+
                                   '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                                   '    </td>'+
                                   '</tr>';
                        });
                        $('#list_methodpayment').html(html);  
                    }
                }
            })
           
        })

        submitAjaxFormNew('#add_info', '#create_info', '.data_', '.message_zone_data');
        var requestSend = false;
        function submitAjaxFormNew(idButton, idForm, classError, classShowError) {
        $(document).on('click', idButton, function (e) {
                var values = $(idForm).serializeArray();
                values.push({name:"content",value:values.find(input => input.name == 'description').value});
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
                }
                 else {
                    e.preventDefault();
                }          
            
        });
        }
        $('#list_methodpayment').on('click','.edit_method_payment', function (e) {
            e.preventDefault();
            let id = $(this).data('element');
            if(id){
                object_list_method_payment.forEach(element => {
                     if(element.id == id){
                         $('#type_payment').val(element.type_payment).change();
                         $('#fixed_charge').val(element.fixed_charge);
                         $('#fee_percentage').val(element.fee_percentage);
                         $('#method_payment_id').val(element.id);
                     }
                });
            }
           
           
        });
        $('#list_methodpayment').on('click','.delete_edit_method_payment', function (e) {
            e.preventDefault();
            let id = $(this).data('element');
            if(id){
                postDelPayment(id);
            }
        });
        $('#add_method_payment').click(function (e) { 
            e.preventDefault();
            if($('#method_payment_id').val()){ // update
                var form_data = $('#create_method_payment').serializeArray();
                postUpdatePayment(form_data);
            }else{
                var form_data = $('#create_method_payment').serializeArray();
                postAddPayment(form_data);
            }
           
        });
        async function postUpdatePayment(param) {
            let method='post';
            let _result = await call_api(method, 'payment/updateTransferFees',param);
            toastr.success(_result.mess);
            setTimeout(function(){
                location.reload();
            }, 1000);
        }
        async function postAddPayment(param) {
            let method='post';
            let _result = await call_api(method, 'payment/addTransferFees',param);
            toastr.success(_result.mess);
            setTimeout(function(){
                location.reload();
            }, 1000);
        }
        async function postDelPayment(id) {
            let method='post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            param_query +="&id="+id;
            console.log(param_query);
            let _result = await call_api(method, 'payment/deleteTransferFees'+param_query);
            toastr.success(_result.mess);
            setTimeout(function(){
                location.reload();
            }, 1000);
        }
</script>
@endsection
