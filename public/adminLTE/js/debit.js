$(document).on('change', '#choose_apartment', function(e) {
    data = "";
    loadDebitDetail($(this).attr('data-url'), false);
});

$(document).on('change', '#choose_apartment_v2', function(e) {
    data = "";
    loadDebitDetailV2($(this).attr('data-url'), false);
});

$(document).on('change', '#choose_apartment_v2_old', function(e) {
    data = "";
    loadDebitDetailV2_old($(this).attr('data-url'), false);
});

$(document).on('change', '#choose_apartment_phieu_chi', function(e) {
    data = "";
    loadDebitDetailPhieuChi($(this).attr('data-url'), false);
});

$(document).on('change', '#choose_service, #to_date, #from_date, #choose_provisional_receipt', function(e) {
    loadDebitDetail($(this).attr('data-url'), true);
});

$(document).on('click', '.collect_money', function(e) {
    if (!$("#receipt_form").valid()) return;
    var apartmentId = $('#choose_apartment').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var customer_paid = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_description = $('#customer_description').val();
    let remove_duplicate_bill_service = JSON.parse($('.data_receipt').val());
    let result_remove_duplicate_bill_service = remove_duplicate_bill_service.filter((v, i, a) => a.findIndex(t => (t.debit_id === v.debit_id)) === i)
    var data_receipt = JSON.stringify(result_remove_duplicate_bill_service);
    var building_id = $('.building_id').val();
    var paid_money = $('#paid_money').val();
    var customer_paid_string = $('#customer_paid_string').val().replace(/,/g, "");
    var created_date = $('#created_date').val();
    var bank = $('#bank').val();
    var vi_can_ho = $('#vi_can_ho').val();
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    // $(this).prop('disabled', true);
    var customer_payments = $("input[name=customer_payments]");
    var typePayment = customer_payments.filter(":checked").val();
    var that = $(this);
    showLoading();
    $.ajax({
        url: that.attr('data-url') + '/create',
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            type: chooseType,
            typeReceipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            paid_money: paid_money,
            customer_paid_string: customer_paid_string,
            bank: bank,
            vi_can_ho: vi_can_ho,
            apartment_id: apartmentId,
            provisional_receipt: provisionalReceipt,
            created_date: created_date
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                alert(response.message);
                if (response.data.vnpay != '') {
                    window.open(response.data.vnpay, '_blank');
                } else {
                    location.reload();
                }
            } else {
                alert(response.message);
                $(this).prop('disabled', false);
                location.reload();
            }
        }
    });
});

$(document).on('click', '.collect_money_v2', function(e) {

    var apartmentId = $('#choose_apartment_v2').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var ma_khach_hang = $('#ma_khach_hang').val();
    var ten_khach_hang = $('#ten_khach_hang').val();
    var tai_khoan_co = $('#tai_khoan_co').val();
    var tai_khoan_no = $('#tai_khoan_no').val();
    var ngan_hang = $('#ngan_hang').val();
    var customer_paid = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_description = $('#customer_description').val();
    var building_id = $('.building_id').val();
    var paid_money = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_paid_string = $('#customer_paid_string').val().replace(/,/g, "");
    var created_date = $('#created_date').val();
    // var bank = $('#bank').val();
    // var vi_can_ho = $('#vi_can_ho').val();
    var typePayment = $("#customer_payments").val();
    var that = $(this);
    let data_receipt = null;
    if ($('.data_receipt').val()) {
        if (!$("#receipt_form").valid()) return;
        if ($('#customer_paid_string').val() == 0 && chooseType == 2) {
            alert('Bạn chưa nhập số tiền thanh toán');
            $('.data_receipt').val('');
            data = '';
            $('#total_pay').val(0);
            loadDebitDetailV2($(this).attr('data-url'), false);
        }
        let remove_duplicate_bill_service = JSON.parse($('.data_receipt').val());
        let result_remove_duplicate_bill_service = remove_duplicate_bill_service.filter((v, i, a) => a.findIndex(t => (t.debit_id === v.debit_id)) === i);
        var total = 0;
        result_remove_duplicate_bill_service.forEach(item => {
            total += parseInt(item.paid.replace(/,/g, ""));
        });
        let so_tien_nop = parseInt($('#customer_paid_string').val().replace(/,/g, ""));
        if (so_tien_nop < total && chooseType == 2) {
            alert('Số tiền nộp phải lớn hoặc bằng số tiến của tổng dịch vụ muốn thanh toán');
            $('.data_receipt').val('');
            data = '';
            $('#total_pay').val(0);
            loadDebitDetailV2($(this).attr('data-url'), false);
            let total_so_du = formatCurrencyV2($('.total_so_du_hidden').val());
            $('#customer_paid_string').val(total_so_du);
            return;
        }
        data_receipt = JSON.stringify(result_remove_duplicate_bill_service);
    }
    if (!$("#receipt_form").valid()) return;
    // console.log('__'+typePayment);
    // console.log('type_reveeipt__'+typeReceipt);
    showLoading();
    $.ajax({
        url: '/admin/v2/receipt/save',
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            ma_khach_hang: ma_khach_hang,
            ten_khach_hang: ten_khach_hang,
            tai_khoan_co: tai_khoan_co,
            tai_khoan_no: tai_khoan_no,
            ngan_hang: ngan_hang,
            type: chooseType,
            type_receipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            paid_money: paid_money,
            customer_paid_string: customer_paid_string,
            apartment_id: apartmentId,
            created_date: created_date
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                 alert(response.message);
                if (response.data.vnpay != '') {
                    window.open(response.data.vnpay, '_blank');
                } else {
                    location.reload();
                }
            } else {
                alert(response.message);
                $(this).prop('disabled', false);
                location.reload();
            }
        }
    });
});

$(document).on('click', '.collect_money_v2_old', function(e) {
    if (!$('.data_receipt').val()) {
        return;
    }
    if (!$("#receipt_form").valid()) return;
    // if($('#customer_paid_string').val() == 0){
    //     alert('Bạn chưa nhập số tiền thanh toán');
    //     $('.data_receipt').val('');
    //     data='';
    //     loadDebitDetailV2($(this).attr('data-url'), false);
    // }
    let remove_duplicate_bill_service = JSON.parse($('.data_receipt').val());
    let result_remove_duplicate_bill_service = remove_duplicate_bill_service.filter((v, i, a) => a.findIndex(t => (t.debit_id === v.debit_id)) === i);
    var total = 0;
    result_remove_duplicate_bill_service.forEach(item => {
        total += parseInt(item.paid.replace(/,/g, ""));
    });
    // let so_tien_nop =   parseInt($('#customer_paid_string').val().replace(/,/g, ""));
    // if(so_tien_nop < total){
    //     alert('Số tiền nộp phải lớn hoặc bằng số tiến của tổng dịch vụ muốn thanh toán');
    //     $('.data_receipt').val('');
    //     data='';
    //     loadDebitDetailV2($(this).attr('data-url'), false);
    //     return;
    // }
    var apartmentId = $('#choose_apartment_v2_old').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var ma_khach_hang = $('#ma_khach_hang').val();
    var ten_khach_hang = $('#ten_khach_hang').val();
    var tai_khoan_co = $('#tai_khoan_co').val();
    var tai_khoan_no = $('#tai_khoan_no').val();
    var ngan_hang = $('#ngan_hang').val();
    var customer_paid = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_description = $('#customer_description').val();
    var data_receipt = JSON.stringify(result_remove_duplicate_bill_service);
    var building_id = $('.building_id').val();
    var paid_money = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_paid_string = $('#customer_paid_string').val().replace(/,/g, "");
    var created_date = $('#created_date').val();
    var bank = $('#bank').val();
    var vi_can_ho = $('#vi_can_ho').val();
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    var typePayment = $("#customer_payments").val();
    var that = $(this);
    showLoading();
    $.ajax({
        url: that.attr('data-url') + '/create-old',
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            ma_khach_hang: ma_khach_hang,
            ten_khach_hang: ten_khach_hang,
            tai_khoan_co: tai_khoan_co,
            tai_khoan_no: tai_khoan_no,
            ngan_hang: ngan_hang,
            type: chooseType,
            typeReceipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            paid_money: paid_money,
            customer_paid_string: customer_paid_string,
            bank: bank,
            vi_can_ho: vi_can_ho,
            apartment_id: apartmentId,
            provisional_receipt: provisionalReceipt,
            created_date: created_date
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                alert(response.message);
                if (response.data.vnpay != '') {
                    window.open(response.data.vnpay, '_blank');
                } else {
                    location.reload();
                }
            } else {
                alert(response.message);
                $(this).prop('disabled', false);
                location.reload();
            }
        }
    });
});

$(document).on('click', '.collect_money_review', function(e) {
    if (!$("#receipt_form").valid()) return;
    var apartmentId = $('#choose_apartment').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var customer_paid = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_description = $('#customer_description').val();
    let remove_duplicate_bill_service = JSON.parse($('.data_receipt').val());
    let result_remove_duplicate_bill_service = remove_duplicate_bill_service.filter((v, i, a) => a.findIndex(t => (t.debit_id === v.debit_id)) === i)
    var data_receipt = JSON.stringify(result_remove_duplicate_bill_service);
    var building_id = $('.building_id').val();
    var paid_money = $('#paid_money').val();
    var created_date = $('#created_date').val();
    var bank = $('#bank').val();
    var vi_can_ho = $('#vi_can_ho').val();
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    // $(this).prop('disabled', true);
    var customer_payments = $("input[name=customer_payments]");
    var typePayment = customer_payments.filter(":checked").val();
    var that = $(this);
    showLoading();
    $.ajax({
        url: that.attr('data-url') + '/viewer',
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            type: chooseType,
            typeReceipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            paid_money: paid_money,
            bank: bank,
            vi_can_ho: vi_can_ho,
            apartment_id: apartmentId,
            provisional_receipt: provisionalReceipt,
            created_date: created_date
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                $('#modal-content-receipt').html(response.data.html);
                $('#ShowReviewReceipt').modal('show');
            } else {
                if (response.message) {
                    alert(response.message);
                }
                $(this).prop('disabled', false);
                location.reload();
            }
        }
    });
});
$(document).on('click', '.collect_money_review_v2', function(e) {
    var apartmentId = $('#choose_apartment_v2').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var customer_paid = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_description = $('#customer_description').val();
    var building_id = $('.building_id').val();
    var paid_money = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_paid_string = $('#customer_paid_string').val().replace(/,/g, "");
    var created_date = $('#created_date').val();
    var bank = $('#bank').val();
    var vi_can_ho = $('#vi_can_ho').val();
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    var typePayment = $("#customer_payments").val();
    var that = $(this);
    let data_receipt = null;
    if ($('.data_receipt').val()) {
        if (!$("#receipt_form").valid()) return;
        if ($('#customer_paid_string').val() == 0 && chooseType == 2) {
            alert('Bạn chưa nhập số tiền thanh toán');
            $('.data_receipt').val('');
            data = '';
            $('#total_pay').val(0);
            loadDebitDetailV2($(this).attr('data-url'), false);
        }
        let remove_duplicate_bill_service = JSON.parse($('.data_receipt').val());
        let result_remove_duplicate_bill_service = remove_duplicate_bill_service.filter((v, i, a) => a.findIndex(t => (t.debit_id === v.debit_id)) === i);
        var total = 0;
        result_remove_duplicate_bill_service.forEach(item => {
            total += parseInt(item.paid.replace(/,/g, ""));
        });
        let so_tien_nop = parseInt($('#customer_paid_string').val().replace(/,/g, ""));
        if (so_tien_nop < total && chooseType == 2) {
            alert('Số tiền nộp phải lớn hoặc bằng số tiến của tổng dịch vụ muốn thanh toán');
            $('.data_receipt').val('');
            data = '';
            $('#total_pay').val(0);
            loadDebitDetailV2($(this).attr('data-url'), false);
            let total_so_du = formatCurrencyV2($('.total_so_du_hidden').val());
            $('#customer_paid_string').val(total_so_du);
            return;
        }

        data_receipt = JSON.stringify(result_remove_duplicate_bill_service);
    }
    if (!$("#receipt_form").valid()) return;
    showLoading();
    $.ajax({
        url: '/admin/v2/receipt/viewer',
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            type: chooseType,
            typeReceipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            customer_paid_string: customer_paid_string,
            paid_money: paid_money,
            bank: bank,
            vi_can_ho: vi_can_ho,
            apartment_id: apartmentId,
            provisional_receipt: provisionalReceipt,
            created_date: created_date
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                $('#modal-content-receipt').html(response.data.html);
                $('#ShowReviewReceipt').modal('show');
            } else {
                if (response.message) {
                    alert(response.message);
                }
                $(this).prop('disabled', false);
                location.reload();
            }
        }
    });
});

$(document).on('click', '.collect_money_review_v2_old', function(e) {
    if (!$('.data_receipt').val()) {
        return;
    }
    if (!$("#receipt_form").valid()) return;
    // if($('#customer_paid_string').val() == 0){
    //     alert('Bạn chưa nhập số tiền thanh toán');
    //     $('.data_receipt').val('');
    //     data='';
    //     loadDebitDetailV2($(this).attr('data-url'), false);
    // }
    let remove_duplicate_bill_service = JSON.parse($('.data_receipt').val());
    let result_remove_duplicate_bill_service = remove_duplicate_bill_service.filter((v, i, a) => a.findIndex(t => (t.debit_id === v.debit_id)) === i);
    var total = 0;
    result_remove_duplicate_bill_service.forEach(item => {
        total += parseInt(item.paid.replace(/,/g, ""));
    });
    // let so_tien_nop =   parseInt($('#customer_paid_string').val().replace(/,/g, ""));
    // if(so_tien_nop < total){
    //     alert('Số tiền nộp phải lớn hoặc bằng số tiến của tổng dịch vụ muốn thanh toán');
    //     $('.data_receipt').val('');
    //     data='';
    //     loadDebitDetailV2($(this).attr('data-url'), false);
    //     return;
    // }
    var apartmentId = $('#choose_apartment_v2_old').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var customer_paid = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_description = $('#customer_description').val();
    var data_receipt = JSON.stringify(result_remove_duplicate_bill_service);
    var building_id = $('.building_id').val();
    var paid_money = $('#customer_paid_string').val().replace(/,/g, "");
    var created_date = $('#created_date').val();
    var bank = $('#bank').val();
    var vi_can_ho = $('#vi_can_ho').val();
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    var typePayment = $("#customer_payments").val();
    var that = $(this);
    showLoading();
    $.ajax({
        url: that.attr('data-url') + '/viewer-old',
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            type: chooseType,
            typeReceipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            paid_money: paid_money,
            bank: bank,
            vi_can_ho: vi_can_ho,
            apartment_id: apartmentId,
            provisional_receipt: provisionalReceipt,
            created_date: created_date
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                $('#modal-content-receipt').html(response.data.html);
                $('#ShowReviewReceipt').modal('show');
            } else {
                if (response.message) {
                    alert(response.message);
                }
                $(this).prop('disabled', false);
                location.reload();
            }
        }
    });
});

$(document).on('click', '.update_collect_money', function(e) {
    if (!$("#receipt_form").valid()) return;
    var receipt_id = $('#receipt_id').val();
    var apartmentId = $('#choose_apartment').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var customer_paid = $('#customer_paid').val();
    var customer_description = $('#customer_description').val();
    var data_receipt = $('.data_receipt').val();
    var building_id = $('.building_id').val();
    var paid_money = $('#paid_money').val();
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    var customer_payments = $("input[name=customer_payments]");
    var typePayment = customer_payments.filter(":checked").val();
    var that = $(this);
    showLoading();
    $.ajax({
        url: that.attr('data-url') + '/update/' + receipt_id,
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            type: chooseType,
            typeReceipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            paid_money: paid_money,
            provisional_receipt: provisionalReceipt
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                alert(response.message);
                //console.log(response.data);
                if (response.data.vnpay != '') {
                    window.open(response.data.vnpay, '_blank');
                } else {
                    window.location.href = that.attr('data-url-main');
                }
            } else {
                alert(response.message);
                $(this).prop('disabled', false);
            }
        }
    });
});

$(document).on('click', '.print_and_collect_money', function(e) {
    if (!$("#receipt_form").valid()) return;
    var apartmentId = $('#choose_apartment').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var customer_paid = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_description = $('#customer_description').val();
    let remove_duplicate_bill_service = JSON.parse($('.data_receipt').val());
    let result_remove_duplicate_bill_service = remove_duplicate_bill_service.filter((v, i, a) => a.findIndex(t => (t.debit_id === v.debit_id)) === i)
    var data_receipt = JSON.stringify(result_remove_duplicate_bill_service);
    var building_id = $('.building_id').val();
    var paid_money = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_paid_string = $('#customer_paid_string').val().replace(/,/g, "");
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    var created_date = $('#created_date').val();
    var vi_can_ho = $('#vi_can_ho').val();
    // $(this).prop('disabled', true);
    var customer_payments = $("input[name=customer_payments]");
    var typePayment = customer_payments.filter(":checked").val();
    var that = $(this);
    showLoading();
    $.ajax({
        url: that.attr('data-url') + '/create',
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            type: chooseType,
            typeReceipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            paid_money: paid_money,
            customer_paid_string: customer_paid_string,
            vi_can_ho: vi_can_ho,
            apartment_id: apartmentId,
            provisional_receipt: provisionalReceipt,
            created_date: created_date
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                alert(response.message);
                window.open(response.data.url_pdf, '_blank');
                location.reload();
                //window.location.href = that.attr('data-url-main');
            } else {
                alert(response.message);
                $(this).prop('disabled', false);
                location.reload();
            }
        }
    });
});

$(document).on('click', '.print_and_collect_money_v2', function(e) {
    var apartmentId = $('#choose_apartment_v2').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var ma_khach_hang = $('#ma_khach_hang').val();
    var ten_khach_hang = $('#ten_khach_hang').val();
    var tai_khoan_co = $('#tai_khoan_co').val();
    var tai_khoan_no = $('#tai_khoan_no').val();
    var ngan_hang = $('#ngan_hang').val();
    var customer_paid = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_description = $('#customer_description').val();
    let data_receipt = null;
    var building_id = $('.building_id').val();
    var paid_money = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_paid_string = $('#customer_paid_string').val().replace(/,/g, "");
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    var created_date = $('#created_date').val();
    var vi_can_ho = $('#vi_can_ho').val();
    var typePayment = $("#customer_payments").val();
    var that = $(this);
    if ($('.data_receipt').val()) {
        if (!$("#receipt_form").valid()) return;
        if ($('#customer_paid_string').val() == 0 && chooseType == 2) {
            alert('Bạn chưa nhập số tiền thanh toán');
            $('.data_receipt').val('');
            data = '';
            $('#total_pay').val(0);
            loadDebitDetailV2($(this).attr('data-url'), false);
        }
        let remove_duplicate_bill_service = JSON.parse($('.data_receipt').val());
        let result_remove_duplicate_bill_service = remove_duplicate_bill_service.filter((v, i, a) => a.findIndex(t => (t.debit_id === v.debit_id)) === i);
        var total = 0;
        result_remove_duplicate_bill_service.forEach(item => {
            total += parseInt(item.paid.replace(/,/g, ""));
        });
        let so_tien_nop = parseInt($('#customer_paid_string').val().replace(/,/g, ""));
        if (so_tien_nop < total && chooseType == 2) {
            alert('Số tiền nộp phải lớn hoặc bằng số tiến của tổng dịch vụ muốn thanh toán');
            $('.data_receipt').val('');
            data = '';
            $('#total_pay').val(0);
            loadDebitDetailV2($(this).attr('data-url'), false);
            let total_so_du = formatCurrencyV2($('.total_so_du_hidden').val());
            $('#customer_paid_string').val(total_so_du);
            return;
        }
        data_receipt = JSON.stringify(result_remove_duplicate_bill_service);
    }
    if (!$("#receipt_form").valid()) return;
    showLoading();
    $.ajax({
        url: '/admin/v2/receipt/save',
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            ma_khach_hang: ma_khach_hang,
            ten_khach_hang: ten_khach_hang,
            tai_khoan_co: tai_khoan_co,
            tai_khoan_no: tai_khoan_no,
            ngan_hang: ngan_hang,
            type: chooseType,
            type_receipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            paid_money: paid_money,
            customer_paid_string: customer_paid_string,
            vi_can_ho: vi_can_ho,
            apartment_id: apartmentId,
            provisional_receipt: provisionalReceipt,
            created_date: created_date
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                alert(response.message);
                window.open(response.data.url_pdf, '_blank');
                location.reload();
                //window.location.href = that.attr('data-url-main');
            } else {
                alert(response.message);
                $(this).prop('disabled', false);
                location.reload();
            }
        }
    });
});

$(document).on('click', '.print_and_collect_money_v2_old', function(e) {
    if (!$('.data_receipt').val()) {
        return;
    }
    if (!$("#receipt_form").valid()) return;
    // if($('#customer_paid_string').val() == 0){
    //     alert('Bạn chưa nhập số tiền thanh toán');
    //     $('.data_receipt').val('');
    //     data='';
    //     loadDebitDetailV2($(this).attr('data-url'), false);
    // }
    let remove_duplicate_bill_service = JSON.parse($('.data_receipt').val());
    let result_remove_duplicate_bill_service = remove_duplicate_bill_service.filter((v, i, a) => a.findIndex(t => (t.debit_id === v.debit_id)) === i);
    var total = 0;
    result_remove_duplicate_bill_service.forEach(item => {
        total += parseInt(item.paid.replace(/,/g, ""));
    });
    // let so_tien_nop =   parseInt($('#customer_paid_string').val().replace(/,/g, ""));
    // if(so_tien_nop < total){
    //     alert('Số tiền nộp phải lớn hoặc bằng số tiến của tổng dịch vụ muốn thanh toán');
    //     $('.data_receipt').val('');
    //     data='';
    //     loadDebitDetailV2($(this).attr('data-url'), false);
    //     return;
    // }
    var apartmentId = $('#choose_apartment_v2_old').val();
    var chooseType = $('#choose_type').val();
    var typeReceipt = $('#type_receipt').val();
    var customer_fullname = $('#customer_fullname').val();
    var customer_address = $('#customer_address').val();
    var ma_khach_hang = $('#ma_khach_hang').val();
    var ten_khach_hang = $('#ten_khach_hang').val();
    var tai_khoan_co = $('#tai_khoan_co').val();
    var tai_khoan_no = $('#tai_khoan_no').val();
    var ngan_hang = $('#ngan_hang').val();
    var customer_paid = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_description = $('#customer_description').val();
    var data_receipt = JSON.stringify(result_remove_duplicate_bill_service);
    var building_id = $('.building_id').val();
    var paid_money = $('#customer_paid_string').val().replace(/,/g, "");
    var customer_paid_string = $('#customer_paid_string').val().replace(/,/g, "");
    var provisionalReceipt = $('#choose_provisional_receipt').val();
    var created_date = $('#created_date').val();
    var vi_can_ho = $('#vi_can_ho').val();
    var typePayment = $("#customer_payments").val();
    var that = $(this);
    showLoading();
    $.ajax({
        url: that.attr('data-url') + '/create-old',
        type: 'POST',
        data: {
            customer_fullname: customer_fullname,
            customer_address: customer_address,
            customer_total_paid: customer_paid,
            customer_description: customer_description,
            data_receipt: data_receipt,
            ma_khach_hang: ma_khach_hang,
            ten_khach_hang: ten_khach_hang,
            tai_khoan_co: tai_khoan_co,
            tai_khoan_no: tai_khoan_no,
            ngan_hang: ngan_hang,
            type: chooseType,
            typeReceipt: typeReceipt,
            type_payment: typePayment,
            building_id: building_id,
            paid_money: paid_money,
            customer_paid_string: customer_paid_string,
            vi_can_ho: vi_can_ho,
            apartment_id: apartmentId,
            provisional_receipt: provisionalReceipt,
            created_date: created_date
        },
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                alert(response.message);
                window.open(response.data.url_pdf, '_blank');
                location.reload();
                //window.location.href = that.attr('data-url-main');
            } else {
                alert(response.message);
                $(this).prop('disabled', false);
                location.reload();
            }
        }
    });
});

$(document).on('click', '.add_new_debit_detail', function(e) {
    var apartmentId = $('#choose_apartment').val();
    showLoading();
    $.ajax({
        url: $(this).attr('data-url') + '/loadFormReceiptPrevious/' + apartmentId,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                $('.debit_detail_content').html(response.data.html);
            } else {
                $('.debit_detail_content').html(response.data.message);
            }
        }
    });
});

$(document).on('click', '.add_new_debit_detail_v2', function(e) {
    var apartmentId = $('#choose_apartment_v2').val();
    if (!$('#choose_apartment_v2').val()) {
        alert('Bạn chưa chọn căn hộ.');
        return;
    }
    $('.sidebar-mini-expand-feature').addClass("modal-open");
    $('#createDebitDetail').css('display', 'block');
    $('#createDebitDetail').modal('show');
    $('.progress_price_list').empty();
    thang_bang_ke = null;
    year_bang_ke = null;
    next_thang_bang_ke = null;
    month = null;
    year = null;
    from_date = null;
    to_date = null;
    phi_phat_sinh = null;
    chiet_khau = null;
    thanh_tien = null;
    showLoading();
    $.ajax({
        url: $(this).attr('data-url') + '/v2/loadFormReceiptPrevious/' + apartmentId,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                $('.debit_detail_content').html(response.data.html);
            } else {
                $('.debit_detail_content').html(response.data.message);
            }
        }
    });
});

$(document).on('click', '.add_new_debit_detail_v2_old', function(e) {
    var apartmentId = $('#choose_apartment_v2_old').val();
    showLoading();
    $.ajax({
        url: $(this).attr('data-url') + '/loadFormReceiptPrevious/' + apartmentId,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.error_code == 200) {
                $('.debit_detail_content').html(response.data.html);
            } else {
                $('.debit_detail_content').html(response.data.message);
            }
        }
    });
});

$(document).on('click', '#add_debit_detail_previous', function(e) {
    if (!$("#create_info").valid()) return;
    var apartmentId = $('#choose_apartment').val();
    var customerName = $('#customer_name').val();
    var customerAddress = $('#customer_address').val();
    var serviceId = $('#service_id').val();
    var toDatePrevious = $('#to_date_previous').val();
    var fromDatePrevious = $('#from_date_previous').val();
    var sumery = $('#sumery').val();
    var cycleName = $('#cycle_year').val() + '' + $('#cycle_month').val();
    showLoading();
    var that = $(this);
    $.ajax({
        url: that.attr('data-url') + '/createDebitPrevious',
        type: 'POST',
        data: {
            apartmentId: apartmentId,
            customerName: customerName,
            customerAddress: customerAddress,
            serviceId: serviceId,
            toDatePrevious: toDatePrevious,
            fromDatePrevious: fromDatePrevious,
            sumery: sumery,
            cycleName: cycleName
        },
        success: function(response) {
            hideLoading();
            alert(response.message);
            if (response.error_code == 200) {
                loadDebitDetail(that.attr('data-url-receipt'));
                $('#createDebitDetail').hide();
                $('#createDebitDetail').removeClass('in');
                $('.modal-backdrop').remove();
            }
        }
    });
});

$(document).on('click', '#add_debit_detail_previous_v2', function(e) {
    if (!$("#create_info").valid() && !$('.check_list_cong_no').val()) return;
    if ($('.progress_price_list').children().length > 0) {
        var apartmentId = $('#choose_apartment_v2').val();
        var customerName = $('#customer_name').val();
        var customerAddress = $('#customer_address').val();
        var serviceId = $('#service_id').val();
        var toDatePrevious = $('#to_date_previous').val();
        var fromDatePrevious = $('#from_date_previous').val();

        var sub_task_template_infos = [];
        $('.progress_price_items').each(function() {
            let new_month = $(this).find("[name='month']").val();
            let new_year = $(this).find("[name='year']").val();
            let new_from_date = $(this).find("[name='from_date']").val();
            let new_to_date = $(this).find("[name='to_date']").val();
            let new_phi_phat_sinh = $(this).find("[name='phi_phat_sinh']").val();
            let new_chiet_khau = $(this).find("[name='chiet_khau']").val();
            let new_thanh_tien = $(this).find("[name='thanh_tien']").val();
            sub_task_template_infos.push({
                cycle_name: new_year + new_month,
                from_date: new_from_date,
                to_date: new_to_date,
                phi_phat_sinh: new_phi_phat_sinh,
                chiet_khau: new_chiet_khau,
                thanh_tien: new_thanh_tien,
            });

        })
        showLoading();
        var that = $(this);
        $.ajax({
            url: that.attr('data-url') + '/v2/createDebitPrevious',
            type: 'POST',
            data: {
                apartmentId: apartmentId,
                customerName: customerName,
                customerAddress: customerAddress,
                serviceId: serviceId,
                toDatePrevious: toDatePrevious,
                fromDatePrevious: fromDatePrevious,
                form_list_cong_no: JSON.stringify(sub_task_template_infos)
            },
            success: function(response) {
                hideLoading();
                alert(response.message);
                if (response.error_code == 200) {
                    data = "";
                    $('.sidebar-mini-expand-feature').removeClass("modal-open");
                    $('#choose_apartment_v2').val(apartmentId);
                    loadDebitDetailV2(that.attr('data-url-receipt'), false);
                    $('#createDebitDetail').hide();
                    $('#createDebitDetail').removeClass('in');
                    $('.modal-backdrop').remove();
                }
            }
        });
    } else {
        alert('chưa có công nợ nào được tạo.');
        return;
    }

});