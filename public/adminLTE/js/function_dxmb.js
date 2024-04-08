function formatCurrency(input) {
    var number = input.value.replace(/[,.]/g, '');
    return new Intl.NumberFormat().format(number).toString().replace(/\./g, ',');
}

function formatCurrencyV2(value) {
    var number = value.replace(/[,.]/g, '');
    return new Intl.NumberFormat().format(number).replace(/\./g, ',');
}

function loadDebitDetail(data_url, isReference) {
    showLoading();
    var apartmentId = $('#choose_apartment').val();
    var serviceId = $('#choose_service').val();
    var type = $('#choose_type').val();
    var toDate = $('#to_date').val();
    var fromDate = $('#from_date').val();
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    if (!isReference) {
        provisionalReceipt = 0;
        $('#choose_provisional_receipt').empty();
    }
    $('#customer_fullname').val('');
    $('#customer_address').val('');
    $('#customer_paid').val('');
    $('.data_receipt').val('');
    var url = data_url + '/filterByBill/' + apartmentId + '/' + serviceId + '/' + type + '?to_date=' + toDate + '&from_date=' + fromDate + '&provisional_receipt=' + provisionalReceipt;
    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            hideLoading();
            if (response.error_code == 200) {
                if (response.data.paid_money !== undefined) {
                    // $('.paid_money').show();
                    $('#paid_money').val(response.data.paid_money);
                } else {
                    // $('.paid_money').hide();
                    $('#paid_money').val(0);
                }
                $('.result_receipt').html(response.data.html);
                $('#customer_fullname').val(response.data.customer_name);
                $('#customer_address').val(response.data.customer_address);
                $('#vi_can_ho').val(formatCurrencyV2(response.data.vi_can_ho.toString()));
                if (!isReference) {
                    $('#choose_provisional_receipt').append('<option value="0">Lựa chọn tham chiếu...</option>');
                    $.each(response.data.provisionalReceipts, function (i, d) {
                        $('#choose_provisional_receipt').append('<option value="' + d.id + '">' + d.receipt_code + '</option>');
                    });
                }
            } else {
                $('#vi_can_ho').val(0);
                if (response.error_code === 404) {
                    alert(response.message);
                } else {
                    alert('Kiểu phiếu thu không chính xác. Mời chọn lại');
                }
                $('.result_receipt').html('');
            }
        }
    });
}

function loadDebitDetailV2(data_url, isReference) {
    showLoading();
    var apartmentId = $('#choose_apartment_v2').val();
    var serviceId = $('#choose_service').val();
    var type = $('#choose_type').val();
    var toDate = $('#to_date').val();
    var fromDate = $('#from_date').val();
    $('#total_pay').val(0);
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    if (!isReference) {
        provisionalReceipt = 0;
        $('#choose_provisional_receipt').empty();
    }
    $('#customer_fullname').val('');
    $('#customer_paid_string').val(0);
    $('#customer_address').val('');
    $('#customer_paid').val('');
    $('.data_receipt').val('');
    var url = '/admin/v2/receipt/filterByBill/' + apartmentId + '/' + type + '?provisional_receipt=' + provisionalReceipt;
    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            hideLoading();
            if (response.error_code == 200) {
                //console.log(response.data);
                if (response.data.paid_money !== undefined) {
                    // $('.paid_money').show();
                    $('#paid_money').val(response.data.paid_money);
                } else {
                    // $('.paid_money').hide();
                    $('#paid_money').val(0);
                }
                $('.result_receipt').html(response.data.html);
                if ($('#list_promotions').val()) {
                    let promotion = JSON.parse($('#list_promotions').val())
                    console.log(promotion)

                    $('.result_receipt .list_info > tr').each(function () {
                        let promotion_options = '<option value="" selected>--Chọn--</option>';
                        Object.entries(promotion).forEach(([key1, val1]) => {
                            let service_id = $(this).find('.service_id').val();
                            let service_price = $(this).find('.service_price').val();
                            if (val1.service_id == service_id) {
                                promotion_options += "<option value=" + val1.id + '_' + service_price + " >" + val1.name + "</option>";
                            }
                        });
                        $(this).find('.promotion_apartment_list').html('<select onChange="chosePromotion(this)" class="form-control chose_service">' + promotion_options + '</select>' +
                            '<div class="promotion_apartment"></div>')
                    });

                }
                $('#customer_fullname').val(response.data.customer_name);
                $('#customer_address').val(response.data.customer_address);
                $('#ma_khach_hang').val(response.data.ma_khach_hang);
                $('#ten_khach_hang').val(response.data.ten_khach_hang);
                $('#vi_can_ho').val(formatCurrencyV2(response.data.vi_can_ho.toString()));
                $('.total_so_du').html('Căn hộ có tiền thừa hạch toán : ' + formatCurrencyV2(response.data.total_so_du.toString()) + ' VND !');
                $('.total_so_du_hidden').val(response.data.total_so_du);
                if (response.data.total_so_du > 0) {
                    $('.detail_tien_thua').html('');
                    $.each(response.data.detail_service_so_du, function (i, d) {
                        //console.log(d.apartment_service_price.service);
                        //let name_service = d.bdc_apartment_service_price_id > 0 ? d.apartment_service_price.service.name : 'Chưa chỉ định';
                        $('.detail_tien_thua').append('<div><span>' + d.dich_vu + ' : ' + formatCurrencyV2(d.coin.toString()) + ' VND </span></div>');
                    });
                    $('.detail_tien_thua').css('display', 'block');
                    $('#detail_list').css('display', 'block');
                } else {
                    $('.detail_tien_thua').html('');
                    $('.detail_tien_thua').css('display', 'none');
                    $('#detail_list').css('display', 'none');
                }
                if (!isReference) {
                    $('#choose_provisional_receipt').append('<option value="0">Lựa chọn tham chiếu...</option>');
                    $.each(response.data.provisionalReceipts, function (i, d) {
                        $('#choose_provisional_receipt').append('<option value="' + d.id + '">' + d.receipt_code + '</option>');
                    });
                }
            } else {
                $('#vi_can_ho').val(0);
                if (response.error_code === 404) {
                    alert(response.message);
                } else {
                    alert('Kiểu phiếu thu không chính xác. Mời chọn lại');
                }
                $('.result_receipt').html('');
            }
        }
    });
}

function loadDebitDetailV2_old(data_url, isReference) {
    showLoading();
    var apartmentId = $('#choose_apartment_v2_old').val();
    var serviceId = $('#choose_service').val();
    var type = $('#choose_type').val();
    var toDate = $('#to_date').val();
    var fromDate = $('#from_date').val();
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    if (!isReference) {
        provisionalReceipt = 0;
        $('#choose_provisional_receipt').empty();
    }
    $('#customer_fullname').val('');
    $('#customer_paid_string').val(0);
    $('#customer_address').val('');
    $('#customer_paid').val('');
    $('.data_receipt').val('');
    var url = data_url + '/filterByBill-old/' + apartmentId + '/' + serviceId + '/' + type + '?provisional_receipt=' + provisionalReceipt;
    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            hideLoading();
            if (response.error_code == 200) {
                if (response.data.paid_money !== undefined) {
                    // $('.paid_money').show();
                    $('#paid_money').val(response.data.paid_money);
                } else {
                    // $('.paid_money').hide();
                    $('#paid_money').val(0);
                }
                $('.result_receipt').html(response.data.html);
                $('#customer_fullname').val(response.data.customer_name);
                $('#customer_address').val(response.data.customer_address);
                $('#ma_khach_hang').val(response.data.ma_khach_hang);
                $('#ten_khach_hang').val(response.data.ten_khach_hang);
                $('#vi_can_ho').val(formatCurrencyV2(response.data.vi_can_ho.toString()));
                if (!isReference) {
                    $('#choose_provisional_receipt').append('<option value="0">Lựa chọn tham chiếu...</option>');
                    $.each(response.data.provisionalReceipts, function (i, d) {
                        $('#choose_provisional_receipt').append('<option value="' + d.id + '">' + d.receipt_code + '</option>');
                    });
                }
            } else {
                $('#vi_can_ho').val(0);
                if (response.error_code === 404) {
                    alert(response.message);
                } else {
                    alert('Kiểu phiếu thu không chính xác. Mời chọn lại');
                }
                $('.result_receipt').html('');
            }
        }
    });
}

function loadDebitDetailPhieuChi(data_url, isReference) {
    showLoading();
    var apartmentId = $('#choose_apartment_phieu_chi').val();
    var serviceId = $('#choose_service').val();
    // var type = $('#choose_type').val();
    // var toDate = $('#to_date').val();
    // var fromDate = $('#from_date').val();
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    if (!isReference) {
        provisionalReceipt = 0;
        $('#choose_provisional_receipt').empty();
    }
    $('#customer_fullname').val('');
    $('#customer_address').val('');
    $('#customer_paid').val('');
    $('.data_receipt').val('');
    var url = data_url + '/filterByBillPhieuChi/' + apartmentId + '/' + serviceId;
    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            hideLoading();
            if (response.error_code == 200) {
                if (response.data.paid_money !== undefined) {
                    // $('.paid_money').show();
                    $('#paid_money').val(response.data.paid_money);
                } else {
                    // $('.paid_money').hide();
                    $('#paid_money').val(0);
                }
                $('.result_receipt').html(response.data.html);
                $('#customer_fullname').val(response.data.customer_name);
                $('#customer_address').val(response.data.customer_address);
                if (!isReference) {
                    $('#choose_provisional_receipt').append('<option value="0">Lựa chọn tham chiếu...</option>');
                    $.each(response.data.provisionalReceipts, function (i, d) {
                        $('#choose_provisional_receipt').append('<option value="' + d.id + '">' + d.receipt_code + '</option>');
                    });
                }
            } else {
                if (response.error_code === 404) {
                    alert(response.message);
                } else {
                    alert('Kiểu phiếu thu không chính xác. Mời chọn lại');
                }
                $('.result_receipt').html('');
            }
        }
    });
}

function checkService(element) {
    var currentPaid = $('#paid_money').val() == '' ? 0 : $('#paid_money').val();
    var currentPaidService = $(element).closest('.checkbox_parent').find('input.total_payment').val();
    var currentPaidServiceInt = currentPaidService.replace(/,/g, "");
    var service_id = $(element).closest('.checkbox_parent').find('input.service_id').val();
    var apartment_service_price_id = $(element).closest('.checkbox_parent').find('input.apartment_service_price_id').val();
    var billCode = $(element).closest('.checkbox_parent').find('input.bill_code').val();
    var version = $(element).closest('.checkbox_parent').find('input.debit_version').val();
    var debitId = $(element).closest('.checkbox_parent').find('input.debit_id').val();
    var totalPaid = 0;
    var apartmentId = $('#choose_apartment').val();
    var type = $('#choose_type').val();
    console.log(apartmentId);
    if ($(element).is(":checked")) {
        totalPaid = parseInt(currentPaid) + parseInt(currentPaidServiceInt);
        service_id = service_id == undefined ? 0 : service_id;
        apartment_service_price_id = apartment_service_price_id == undefined ? 0 : apartment_service_price_id;
        data += '{"debit_id":' + debitId + ',"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidService + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
    } else {
        service_id = service_id == undefined ? 0 : service_id;
        var dataReceipt = $.parseJSON($('.data_receipt').val());
        $flag = false;
        $.each(dataReceipt, function (i, item) {
            if (item.bill_code == billCode && item.apartment_service_price_id == apartment_service_price_id) {
                var paid = item.paid;
                var paidInt = paid.replace(/,/g, "");
                totalPaid = parseInt(currentPaid) - parseInt(paidInt);
                var removeElement = '{"debit_id":' + debitId + ',"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + paid + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
                data = data.replace(removeElement, '');
                $flag = true;
                return true;
            }
        });
        if (!$flag) {
            totalPaid = parseInt(currentPaid) - parseInt(currentPaidServiceInt);
            var removeElement = '{"debit_id":' + debitId + ',"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidService + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
            data = data.replace(removeElement, '');
        }
    }

    var newData = data.substring(0, data.length - 1);
    var totalPaidString = formatCurrencyV2(totalPaid.toString());

    // $('#customer_paid').val(totalPaid);
    $('#customer_paid_string').val(totalPaidString);
    $('#paid_money').val(totalPaid);
    $('#paid_money_string').val(totalPaidString);
    $('.data_receipt').val('[' + newData + ']');
    $('.service_ids').val(service_ids);
}

function checkServiceV2(element) {

    var currentPaid = $('#total_pay').val().replace(/,/g, "");
    var currentPaidService = $(element).closest('.checkbox_parent').find('input.total_payment').val();
    var currentPaidServiceInt = currentPaidService.replace(/,/g, "");
    var apartment_service_price_id = $(element).closest('.checkbox_parent').find('input.apartment_service_price_id').val();
    var billCode = $(element).closest('.checkbox_parent').find('input.bill_code').val();
    var total_payment_current = $(element).closest('.checkbox_parent').find('input.total_payment_current').val();
    var debitId = $(element).closest('.checkbox_parent').find('input.debit_id').val();
    var promotionId = $(element).closest('.checkbox_parent').find('.promotion_apartment').data('promotion') ? $(element).closest('.checkbox_parent').find('.promotion_apartment').data('promotion') : null;
    var promotionPrice = $(element).closest('.checkbox_parent').find('.promotion_apartment').data('promotion_price') ? $(element).closest('.checkbox_parent').find('.promotion_apartment').data('promotion_price') : null;
    var chi_dinh_hach_toan = $(element).closest('.checkbox_parent').find('select.chi_dinh_hach_toan').val() ? $(element).closest('.checkbox_parent').find('select.chi_dinh_hach_toan').val() : null;
    var totalPaid = 0;
    var apartmentId = $('#choose_apartment_v2').val();
    var type = $('#choose_type').val();
    console.log(apartmentId);
    let discount = $(element).closest('.checkbox_parent').find('input.debit_discount').val().replace(/,/g, "");
    //let discount = 0;
    if ($(element).is(":checked")) {
        $(element).closest('.checkbox_parent').find('input.total_payment').attr('readonly', true);
        $(element).closest('.checkbox_parent').find('input.debit_discount').attr('readonly', true);
        $(element).closest('.checkbox_parent').find('.chose_service').attr('disabled', 'disabled');
        totalPaid = parseInt(currentPaid) + parseInt(currentPaidServiceInt);
        apartment_service_price_id = apartment_service_price_id == undefined ? 0 : apartment_service_price_id;
        data += '{"debit_id":' + debitId + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidServiceInt + '","type": ' + type + ',"apartment_service_price_id":' + apartment_service_price_id + ',"total_payment_current":' + total_payment_current + ',"chi_dinh_hach_toan":' + chi_dinh_hach_toan + ',"chiet_khau":' + discount + ',"promotion_id":' + promotionId + ',"promotion_price":' + promotionPrice + '},';
    } else {
        $(element).closest('.checkbox_parent').find('input.total_payment').attr('readonly', false);
        $(element).closest('.checkbox_parent').find('input.debit_discount').attr('readonly', false);
        $(element).closest('.checkbox_parent').find('.chose_service').removeAttr('disabled');
        var dataReceipt = $.parseJSON($('.data_receipt').val());
        $flag = false;
        $.each(dataReceipt, function (i, item) {
            if (item.bill_code == billCode && item.apartment_service_price_id == apartment_service_price_id) {
                var paid = item.paid;
                var paidInt = paid.replace(/,/g, "");
                totalPaid = parseInt(currentPaid) - parseInt(paidInt);
                var removeElement = '{"debit_id":' + debitId + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + paid + '","type": ' + type + ',"apartment_service_price_id":' + apartment_service_price_id + ',"total_payment_current":' + total_payment_current + ',"chi_dinh_hach_toan":' + chi_dinh_hach_toan + ',"chiet_khau":' + discount + ',"promotion_id":' + promotionId + ',"promotion_price":' + promotionPrice + '},';
                data = data.replace(removeElement, '');
                $flag = true;
                return true;
            }
        });
        if (!$flag) {
            totalPaid = parseInt(currentPaid) - parseInt(currentPaidServiceInt);
            var removeElement = '{"debit_id":' + debitId + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidServiceInt + '","type": ' + type + ',"apartment_service_price_id":' + apartment_service_price_id + ',"total_payment_current":' + total_payment_current + ',"chi_dinh_hach_toan":' + chi_dinh_hach_toan + ',"chiet_khau":' + discount + ',"promotion_id":' + promotionId + ',"promotion_price":' + promotionPrice + '},';
            data = data.replace(removeElement, '');
        }
    }

    var newData = data.substring(0, data.length - 1);
    var totalPaidString = formatCurrencyV2(totalPaid.toString());

    $('#total_pay').val(totalPaidString);
    $('#customer_paid_string').val(totalPaidString);
    $('#paid_money').val(totalPaid);
    $('#total_pay').val(totalPaidString);
    $('.data_receipt').val('[' + newData + ']');
}

function checkServiceAll(element) {
    let totalPaid = 0;
    data = '';
    $('#total_pay').val(0);
    if ($(element).is(":checked")) // nếu tích all
    {
        $('#table_receipt_debit > tbody  > tr').each(function () {
            $(this).closest('.checkbox_parent').find('input.total_payment').attr('readonly', true);
            $(this).closest('.checkbox_parent').find('input.debit_discount').attr('readonly', true);
            $(this).closest('.checkbox_parent').find('.chose_service').attr('disabled', 'disabled');
            $(this).find("input#check_box_debit_receipt").prop('checked', true);
            let currentPaid = $('#total_pay').val().replace(/,/g, "");
            let currentPaidService = $(this).closest('.checkbox_parent').find('input.total_payment').val();
            let currentPaidServiceInt = currentPaidService.replace(/,/g, "");
            let apartment_service_price_id = $(this).closest('.checkbox_parent').find('input.apartment_service_price_id').val();
            let billCode = $(this).closest('.checkbox_parent').find('input.bill_code').val();
            let total_payment_current = $(this).closest('.checkbox_parent').find('input.total_payment_current').val()
            let debitId = $(this).closest('.checkbox_parent').find('input.debit_id').val();
            var promotionId = $(this).closest('.checkbox_parent').find('.promotion_apartment').data('promotion') ? $(this).closest('.checkbox_parent').find('.promotion_apartment').data('promotion') : null;
            var promotionPrice = $(this).closest('.checkbox_parent').find('.promotion_apartment').data('promotion_price') ? $(this).closest('.checkbox_parent').find('.promotion_apartment').data('promotion_price') : null;
            let discount = $(this).closest('.checkbox_parent').find('input.debit_discount').val().replace(/,/g, "");
            // let discount = 0;
            console.log(discount);
            var chi_dinh_hach_toan = $(this).closest('.checkbox_parent').find('select.chi_dinh_hach_toan').val() ? $(this).closest('.checkbox_parent').find('select.chi_dinh_hach_toan').val() : null;
            let apartmentId = $('#choose_apartment_v2').val();
            let type = $('#choose_type').val();

            totalPaid += parseInt(currentPaid) + parseInt(currentPaidServiceInt);
            apartment_service_price_id = apartment_service_price_id == undefined ? 0 : apartment_service_price_id;
            data += '{"debit_id":' + debitId + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidServiceInt + '","type": ' + type + ',"apartment_service_price_id":' + apartment_service_price_id + ',"total_payment_current":' + total_payment_current + ',"chi_dinh_hach_toan":' + chi_dinh_hach_toan + ',"chiet_khau":' + discount + ',"promotion_id":' + promotionId + ',"promotion_price":' + promotionPrice + '},';
        });
        var newData = data.substring(0, data.length - 1);
        var totalPaidString = formatCurrencyV2(totalPaid.toString());

        $('#total_pay').val(totalPaidString);
        $('#customer_paid_string').val(totalPaidString);
        $('#paid_money').val(totalPaid);
        $('.data_receipt').val('[' + newData + ']');
    } else {
        $('#table_receipt_debit > tbody  > tr').each(function (index, tr) {
            $(this).find("input#check_box_debit_receipt").prop('checked', false);
            $(this).closest('.checkbox_parent').find('input.total_payment').attr('readonly', false);
            $(this).closest('.checkbox_parent').find('input.debit_discount').attr('readonly', false);
            $(this).closest('.checkbox_parent').find('.chose_service').removeAttr('disabled');
        });
        $('#total_pay').val(0);
        $('#customer_paid_string').val(0);
        $('#paid_money').val(0);
        $('.data_receipt').val('');
    }

}

function checkServiceAll_v1(element) {
    let totalPaid = 0;
    data = '';
    $('#paid_money_string').val(0);
    $('#paid_money').val(0);
    $('#customer_paid_string').val(0);
    if ($(element).is(":checked")) // nếu tích all
    {
        $('#table_receipt_debit > tbody  > tr').each(function () {
            $(this).closest('.checkbox_parent').find('input.total_payment').attr('readonly', true);
            $(this).find("input#check_box_debit_receipt").prop('checked', true);
            let currentPaid = $('#paid_money').val().replace(/,/g, "");
            var currentPaidService = $(this).closest('.checkbox_parent').find('input.total_payment').val();
            var currentPaidServiceInt = currentPaidService.replace(/,/g, "");
            var service_id = $(this).closest('.checkbox_parent').find('input.service_id').val();
            var apartment_service_price_id = $(this).closest('.checkbox_parent').find('input.apartment_service_price_id').val();
            var billCode = $(this).closest('.checkbox_parent').find('input.bill_code').val();
            var version = $(this).closest('.checkbox_parent').find('input.debit_version').val();
            var debitId = $(this).closest('.checkbox_parent').find('input.debit_id').val();
            let apartmentId = $('#choose_apartment').val();
            let type = $('#choose_type').val();

            totalPaid += parseInt(currentPaid) + parseInt(currentPaidServiceInt);
            apartment_service_price_id = apartment_service_price_id == undefined ? 0 : apartment_service_price_id;
            data += '{"debit_id":' + debitId + ',"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidService + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
        });
        var newData = data.substring(0, data.length - 1);
        var totalPaidString = formatCurrencyV2(totalPaid.toString());

        $('#paid_money_string').val(totalPaidString);
        $('#customer_paid_string').val(totalPaidString);
        $('#paid_money').val(totalPaid);
        $('.data_receipt').val('[' + newData + ']');
    } else {
        $('#table_receipt_debit > tbody  > tr').each(function (index, tr) {
            $(this).find("input#check_box_debit_receipt").prop('checked', false);
            $(this).closest('.checkbox_parent').find('input.total_payment').attr('readonly', false);
        });
        $('#paid_money_string').val(0);
        $('#customer_paid_string').val(0);
        $('#paid_money').val(0);
        $('.data_receipt').val('');
    }

}

function checkServiceV2_old(element) {
    // if($('#choose_type').val() == 1 && $('#customer_paid_string').val() == 0){
    //     alert('Bạn chưa nhập số tiền thanh toán');
    //     loadDebitDetailV2($(this).attr('data-url'), false);
    // }
    var currentPaid = $('#customer_paid_string').val().replace(/,/g, "");
    var currentPaidService = $(element).closest('.checkbox_parent').find('input.total_payment').val();
    var currentPaidServiceInt = currentPaidService.replace(/,/g, "");
    var service_id = $(element).closest('.checkbox_parent').find('input.service_id').val();
    var apartment_service_price_id = $(element).closest('.checkbox_parent').find('input.apartment_service_price_id').val();
    var billCode = $(element).closest('.checkbox_parent').find('input.bill_code').val();
    var version = $(element).closest('.checkbox_parent').find('input.debit_version').val();
    var total_payment_current = $(element).closest('.checkbox_parent').find('input.total_payment_current').val();
    var debitId = $(element).closest('.checkbox_parent').find('input.debit_id').val();
    var totalPaid = 0;
    var apartmentId = $('#choose_apartment_v2_old').val();
    var type = $('#choose_type').val();
    console.log(apartmentId);
    if ($(element).is(":checked")) {
        totalPaid = parseInt(currentPaid) + parseInt(currentPaidServiceInt);
        service_id = service_id == undefined ? 0 : service_id;
        apartment_service_price_id = apartment_service_price_id == undefined ? 0 : apartment_service_price_id;
        data += '{"debit_id":' + debitId + ',"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidServiceInt + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + ',"total_payment_current":' + total_payment_current + '},';
    } else {
        service_id = service_id == undefined ? 0 : service_id;
        var dataReceipt = $.parseJSON($('.data_receipt').val());
        $flag = false;
        $.each(dataReceipt, function (i, item) {
            if (item.bill_code == billCode && item.apartment_service_price_id == apartment_service_price_id) {
                var paid = item.paid;
                var paidInt = paid.replace(/,/g, "");
                totalPaid = parseInt(currentPaid) - parseInt(paidInt);
                var removeElement = '{"debit_id":' + debitId + ',"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + paid + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + ',"total_payment_current":' + total_payment_current + '},';
                data = data.replace(removeElement, '');
                $flag = true;
                return true;
            }
        });
        if (!$flag) {
            totalPaid = parseInt(currentPaid) - parseInt(currentPaidServiceInt);
            var removeElement = '{"debit_id":' + debitId + ',"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidServiceInt + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + ',"total_payment_current":' + total_payment_current + '},';
            data = data.replace(removeElement, '');
        }
    }

    var newData = data.substring(0, data.length - 1);
    var totalPaidString = formatCurrencyV2(totalPaid.toString());

    $('#customer_paid_string').val(totalPaidString);
    $('#paid_money').val(totalPaid);
    $('#paid_money_string').val(totalPaidString);
    $('.data_receipt').val('[' + newData + ']');
    $('.service_ids').val(service_ids);
}

function checkServiceEditBill(element) {
    var currentPaid = $('#paid_money').val() == '' ? 0 : $('#paid_money').val();
    var currentPaidService = $(element).closest('.checkbox_parent').find('input.total_payment').val();
    console.log(currentPaidService);
    var currentPaidServiceInt = currentPaidService.replace(/,/g, "");
    console.log(currentPaidServiceInt);
    var service_id = $(element).closest('.checkbox_parent').find('input.service_id').val();
    var apartment_service_price_id = $(element).closest('.checkbox_parent').find('input.apartment_service_price_id').val();
    var billCode = $(element).closest('.checkbox_parent').find('input.bill_code').val();
    var totalPaid = 0;
    var apartmentId = $('#choose_apartment').val();
    var type = $('#choose_type').val();

    if ($(element).is(":checked")) {
        totalPaid = parseInt(currentPaid) + parseInt(currentPaidServiceInt);
        service_id = service_id == undefined ? 0 : service_id;
        apartment_service_price_id = apartment_service_price_id == undefined ? 0 : apartment_service_price_id;
        data += '{"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidService + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
    } else {
        service_id = service_id == undefined ? 0 : service_id;
        var dataReceipt = $.parseJSON($('.data_receipt').val());
        $flag = false;
        $.each(dataReceipt, function (i, item) {
            if (item.bill_code == billCode && item.apartment_service_price_id == apartment_service_price_id) {
                var paid = item.paid;
                var paidInt = paid.replace(/,/g, "");
                totalPaid = parseInt(currentPaid) - parseInt(paidInt);
                var removeElement = '{"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + paid + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
                data = data.replace(removeElement, '');
                $flag = true;
                return true;
            }
        });
        if (!$flag) {
            totalPaid = parseInt(currentPaid) - parseInt(currentPaidServiceInt);
            var removeElement = '{"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidService + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
            data = data.replace(removeElement, '');
        }
    }

    var newData = data.substring(0, data.length - 1);
    var totalPaidString = formatCurrencyV2(totalPaid.toString());

    // $('#customer_paid').val(totalPaid);
    // $('#customer_paid_string').val(totalPaidString);
    $('#paid_money').val(totalPaid);
    $('#paid_money_string').val(totalPaidString);
    $('.data_receipt').val('[' + newData + ']');
    $('.service_ids').val(service_ids);
}

function checkServicePhieuChi(element) {
    var currentPaid = $('#customer_paid').val() == '' ? 0 : $('#customer_paid').val();
    var currentPaidService = $(element).closest('.checkbox_parent').find('input.total_payment').val();
    var currentPaidServiceInt = currentPaidService.replace(/,/g, "");
    var service_id = $(element).closest('.checkbox_parent').find('input.service_id').val();
    var apartment_service_price_id = $(element).closest('.checkbox_parent').find('input.apartment_service_price_id').val();
    var billCode = $(element).closest('.checkbox_parent').find('input.bill_code').val();
    var version = $(element).closest('.checkbox_parent').find('input.debit_version').val();
    var totalPaid = 0;
    var apartmentId = $('#choose_apartment_phieu_chi').val();
    var type = $('#choose_type').val();
    console.log(apartmentId);
    if ($(element).is(":checked")) {
        totalPaid = parseInt(currentPaid) + parseInt(currentPaidServiceInt);
        service_id = service_id == undefined ? 0 : service_id;
        apartment_service_price_id = apartment_service_price_id == undefined ? 0 : apartment_service_price_id;
        data += '{"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidService + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
    } else {
        service_id = service_id == undefined ? 0 : service_id;
        var dataReceipt = $.parseJSON($('.data_receipt').val());
        $flag = false;
        $.each(dataReceipt, function (i, item) {
            if (item.bill_code == billCode && item.apartment_service_price_id == apartment_service_price_id) {
                var paid = item.paid;
                var paidInt = paid.replace(/,/g, "");
                totalPaid = parseInt(currentPaid) - parseInt(paidInt);
                var removeElement = '{"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + paid + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
                data = data.replace(removeElement, '');
                $flag = true;
                return true;
            }
        });
        if (!$flag) {
            totalPaid = parseInt(currentPaid) - parseInt(currentPaidServiceInt);
            var removeElement = '{"version":' + version + ',"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidService + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
            data = data.replace(removeElement, '');
        }
    }

    var newData = data.substring(0, data.length - 1);
    var totalPaidString = formatCurrencyV2(totalPaid.toString());

    $('#customer_paid').val(totalPaid);
    $('#customer_paid_string').val(totalPaidString);
    $('#paid_money').val(totalPaid);
    $('#paid_money_string').val(totalPaidString);
    $('.data_receipt').val('[' + newData + ']');
    $('.service_ids').val(service_ids);
}

function checkServiceEditBillPhieuChi(element) {
    var currentPaid = $('#paid_money').val() == '' ? 0 : $('#paid_money').val();
    var currentPaidService = $(element).closest('.checkbox_parent').find('input.total_payment').val();
    console.log(currentPaidService);
    var currentPaidServiceInt = currentPaidService.replace(/,/g, "");
    console.log(currentPaidServiceInt);
    var service_id = $(element).closest('.checkbox_parent').find('input.service_id').val();
    var apartment_service_price_id = $(element).closest('.checkbox_parent').find('input.apartment_service_price_id').val();
    var billCode = $(element).closest('.checkbox_parent').find('input.bill_code').val();
    var totalPaid = 0;
    var apartmentId = $('#choose_apartment_phieu_chi').val();
    var type = $('#choose_type').val();

    if ($(element).is(":checked")) {
        totalPaid = parseInt(currentPaid) + parseInt(currentPaidServiceInt);
        service_id = service_id == undefined ? 0 : service_id;
        apartment_service_price_id = apartment_service_price_id == undefined ? 0 : apartment_service_price_id;
        data += '{"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidService + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
    } else {
        service_id = service_id == undefined ? 0 : service_id;
        var dataReceipt = $.parseJSON($('.data_receipt').val());
        $flag = false;
        $.each(dataReceipt, function (i, item) {
            if (item.bill_code == billCode && item.apartment_service_price_id == apartment_service_price_id) {
                var paid = item.paid;
                var paidInt = paid.replace(/,/g, "");
                totalPaid = parseInt(currentPaid) - parseInt(paidInt);
                var removeElement = '{"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + paid + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
                data = data.replace(removeElement, '');
                $flag = true;
                return true;
            }
        });
        if (!$flag) {
            totalPaid = parseInt(currentPaid) - parseInt(currentPaidServiceInt);
            var removeElement = '{"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid":"' + currentPaidService + '","type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
            data = data.replace(removeElement, '');
        }
    }

    var newData = data.substring(0, data.length - 1);
    var totalPaidString = formatCurrencyV2(totalPaid.toString());

    // $('#customer_paid').val(totalPaid);
    // $('#customer_paid_string').val(totalPaidString);
    $('#paid_money').val(totalPaid);
    $('#paid_money_string').val(totalPaidString);
    $('.data_receipt').val('[' + newData + ']');
    $('.service_ids').val(service_ids);
}