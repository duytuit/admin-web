$(document).ready(function () {
    //Icheck
    $('input.iCheck').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });

     //Icheck Blue
     $('input.blueCheck').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
    });

    //Icheck Red
    $('input.iCheck_Red').iCheck({
        checkboxClass: 'icheckbox_square-red',
        radioClass: 'iradio_square-red',
        increaseArea: '20%' // optional
    });

    // check all
    $('input.checkAll').on('ifToggled', function (e) {
        var target = $(this).data('target');

        if (this.checked) {
            $(target).iCheck('check');
        } else {
            $(target).iCheck('uncheck');
        }
    });

    // check all
    $('input.blueCheck').on('ifToggled', function (e) {
        var id = $(this).data('id');
        var url = $(this).data('url');
        var status = $(this).data('status');
        var method_custom = $(this).data('method');
        var _token = $('meta[name="csrf-token"]').attr('content');
        var data = {
            _token: _token,
            method: 'status',
            method_custom : method_custom,
            status: status ? 0 : 1,
            ids: [id]
        };

        $.post(url, data, function (json) {
              if(json.success == false){
                toastr.warning(json.message);
              }
              location.reload(); 
        });
    });

    // select 2
    $('select.select2').select2({
        language: 'vi',
    });

    // show / hide target
    $('[data-toggle="show"]').click(function () {
        var target = $(this).data('target');
        $(target).toggle();
    });

    // show / hide target
    $('[data-visible]').click(function () {
        var visible = $(this).data('visible');
        var target = $(this).data('target');
        if (visible == 'show') {
            $(target).show();
        } else {
            $(target).hide();
        }
    });

    // ul.nav-tabs > li.active open
    if (location.hash) {
        $('[data-toggle="tab"][href="' + location.hash + '"]').trigger('click');
    }

    $('[data-toggle="tab"]').click(function () {
        location.hash = $(this).attr('href');
    });

    // chặn Enter xuống dòng ở .input-text
    $('.input-text').on('keypress', function (e) {
        if (e.which == 13) {
            e.preventDefault();
            $(this).closest('form').submit();
        }
    });

    // .btn-action
    $('a.btn-action').click(function () {
        var target = $(this).data('target');
        var $form = $(target);
        var method = $(this).data('method');
        var is_confirm = true;
        if (!confirm('Có chắc bạn muốn thao tác này?')) {
            is_confirm = false;
        }
        $('input[name=method]', $form).val(method);
        
        if (method == 'active') {
            $('input[name=method]', $form).val('status');
            $('input[name=status]', $form).val(1);
        } else if (method == 'inactive') {
            $('input[name=method]', $form).val('status');
            $('input[name=status]', $form).val(0);
        } else if (method == 'update_first_time_pay') {
            $('input[name=method]', $form).val('update_first_time_pay');
            $form.append('<input type="hidden" name="update_time_pay" value="'+ $('input[name=update_time_pay]').val()+'" />');
        }
        else if (method == 'update_last_time_pay') {
            $('input[name=method]', $form).val('update_last_time_pay');
            $form.append('<input type="hidden" name="update_time_pay" value="'+ $('input[name=update_time_pay]').val()+'" />');
        }
        else if (method == 'update_dateline') {
            $('input[name=method]', $form).val('update_dateline');
            $form.append('<input type="hidden" name="update_dateline" value="'+ $('input[name=update_dateline]').val()+'" />');
        }
        else if (method == 'update_price_type') {
            $('input[name=method]', $form).val('update_price_type');
            $form.append('<input type="hidden" name="price_type" value="'+ $('#update_bdc_price_type_id').val()+'" />');
        }
        //Sửa trạng thái
        else if (method == 'update_status_bill') {
            $('input[name=method]', $form).val('update_status_bill');
            $form.append('<input type="hidden" name="update_status_bill" value="'+ $('#update_status_bill').val()+'" />');
        }
        //Xóa bill and debit
        else if (method == 'del_bill_debit') {
            $('input[name=method]', $form).val('del_bill_debit');
        }
        //Phục hồi bill and debit
        else if (method == 'restore_bill_debit') {
            $('input[name=method]', $form).val('restore_bill_debit');
        }
        else if (method == 'update_status') {
            $('input[name=method]', $form).val('update_status');
            $form.append('<input type="hidden" name="status" value="'+ $('#status_service').val()+'" />');
        }
        else if (method == 'capnhat_ngay_hach_toan') {
            $form.append('<input type="hidden" name="ngay_hach_toan" value="'+ $('.history_transaction_accounting_from_date').val()+'" />');
        }
        if (is_confirm) {
            $form.submit();
        }

        return false;
    });

    // .btn-delete
    $('a.btn-delete').click(function () {
        if (confirm('Có chắc bạn muốn xóa?')) {
            var id = $(this).data('id');
            var url = $(this).data('url');
            var _token = $('meta[name="csrf-token"]').attr('content');
            var data = {
                _token: _token,
                method: 'delete',
                ids: [id]
            };

            $.post(url, data, function (json) {
                location.reload();
            });
        }
    });

    // .btn-status
    $('a.btn-status').click(function (e) {
        var id = $(this).data('id');
        var url = $(this).data('url');
        var status = $(this).data('status');
        var method_custom = $(this).data('method');
        var _token = $('meta[name="csrf-token"]').attr('content');
        var data = {
            _token: _token,
            method: 'status',
            method_custom : method_custom,
            status: status ? 0 : 1,
            ids: [id]
        };

        $.post(url, data, function (json) {
            if(json.success == false){
                toastr.warning(json.message);
            }
            location.reload();
        });
    });

    // set status detail feeback
    $("#btn-set-status").on('click', function () {
        var _this = $(this);
        var id = _this.data('id');
        var url = _this.data('url');
        var _token = $('meta[name="csrf-token"]').attr('content');
        var data = {
            _token: _token,
            method: 'status',
            status: 1,
            ids: [id]
        };

        $.post(url, data, function (json) {
            $(".tag_status").html("<span>Đã hoàn thành</span>");
            _this.remove();
        });
    });
    // set status detail feeback
    $("#btn-set-un-status").on('click', function () {
        var _this = $(this);
        var id = _this.data('id');
        var url = _this.data('url');
        var _token = $('meta[name="csrf-token"]').attr('content');
        var data = {
            _token: _token,
            method: 'status',
            status: 0,
            ids: [id]
        };

        $.post(url, data, function (json) {
            location.reload();
        });
    });

    // per_page
    $('select[name="per_page"]').change(function () {
        var target = $(this).data('target');
        var $form = $(target);

        $('input[name=method]', $form).val('per_page');

        $form.submit();
    });

});

function sidebar(nav, sub) {
    var $nav = $('[data-nav="' + nav + '"]');

    $nav.addClass('menu-open');
    $nav.find('.treeview-menu').show();
    if (sub !== undefined) {
        var $sub = $('[data-sub="' + sub + '"]', $nav);
        $sub.addClass('active');
    }
}

function removeMessageErrorCreate(create_label) {
    $(create_label).removeClass('has-error');
    if ($(create_label).find('.help-block').length) {
        $(create_label).find('.help-block').remove();
    }
}

function showErrorsCreate(errors, idCreate, classMessage) {
    $('div.form_create').find('.help-block').remove();
    $('div.form_create').find('.has-error').removeClass('has-error');
    $.each(errors, function(i, item) {
        var classCreate = idCreate + i.replace('.', '_');
        var create_label = $(classCreate);
        if (item != '') {
            removeMessageErrorCreate(classCreate);
            $(classCreate).addClass('has-error');
            $(classCreate).find(classMessage).append('<span class="help-block"><strong>' +
                item + '</strong></span>');
        } else {
            removeMessageErrorCreate(classCreate);
        }
    });
}

//show image loading
function showLoading(){
    var xPos = $(window).width() / 2;
    xPos -= 45;
    $('#fade_loading').css('left', xPos + 'px');
    $("#fade_overlay").show();
}
// hidden image loading
function hideLoading(){
    $("#fade_overlay").hide();
}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Authorization': window.localStorage.getItem("user_token")
    }
});

var requestSend = false;
function showModalForm(buttonEdit, idModal) {
    $(document).on('click', buttonEdit, function (e) {
        if (!requestSend) {
            showLoading();
            requestSend = true;
            e.preventDefault();
            $.ajax({
                url: $(this).attr('data-action'),
                type: 'POST',
                data: {
                    id: $(this).attr('data-id')
                },
                success: function (response) {
                    hideLoading();
                    $('div.modal-insert').html(response);
                    $(idModal).modal('show');
                    requestSend = false;
                },
            })
        }
    });
}

function submitAjaxForm(idButton, idForm, classError, classShowError) {
    $(document).on('click', idButton, function (e) {
        var formCreate = $(idForm);
        if (!requestSend) {
            showLoading();
            requestSend = true;
            e.preventDefault();
            $.ajax({
                url: formCreate.attr('data-action'),
                type: formCreate.attr('method'),
                data: formCreate.serialize(),
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

function deleteSubmit(deleteButton) {
    $(document).on('click', deleteButton, function (e) {
        if (!confirm('Bạn có chắc chắn muốn xóa?')) {
            e.preventDefault();
        } else {
            showLoading();
            $.ajax({
                url: $(this).attr('data-url'),
                type: 'DELETE',
                success: function (response) {
                    if (response.success == true) {
                        toastr.success(response.message);

                        setTimeout(() => {
                            location.reload()
                        }, 2000)
                    } else {
                        if (response.message) {
                            toastr.warning(response.message);
                        }  else {
                            toastr.error('Không thể item này!');
                        }
                    }
                    hideLoading();
                }
            })
        }

    });
}
 function call_api(method,url,param) {
    return new Promise((resolve, reject) => {
        $.ajax({   
            url:window.localStorage.getItem("base_url")+url,
            method: method,
            data: param,
            success: function (response) {
                resolve(response)
            },
            error: function(error){
                reject(error)
            }
        })
    })
}
function call_api_export(method,url,param) {
    return new Promise((resolve, reject) => {
        $.ajax({   
            url:window.localStorage.getItem("base_url")+url,
            method: method,
            data: param,
            contentType: false, //tell jquery to avoid some checks
            processData: false,
            xhrFields: {
               responseType: 'blob'
            },
            success: function (response) {
                resolve(response)
            },
            error: function(error){
                reject(error)
            }
        })
    })
}
function call_api_form_data(method,url,param) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url:window.localStorage.getItem("base_url")+url,
            method: method,
            data: param,
            contentType: false, //tell jquery to avoid some checks
            processData: false,
            success: function (response) {
                resolve(response)
            },
            error: function(error){
                reject(error)
            }
        })
    })
}
function call_api_data_json(method,url,param) {
    return new Promise((resolve, reject) => {
        $.ajax({   
            url:window.localStorage.getItem("base_url")+url,
            method: method,
            data: JSON.stringify(param),
            headers: {'Content-Type':"application/json"},
            processData: false,
            success: function (response) {
                resolve(response)
            },
            error: function(error){
                reject(error)
            }
        })
    })
}
function call_api_list_padding(element,render_element, url,param_query,per_page,_class='') {
    return new Promise((resolve, reject) => {
        $(element).pagination({
            dataSource: window.localStorage.getItem("base_url")+url+param_query,
            locator: 'data.list',
            totalNumberLocator: function(response) {
              return response.data.count
            },
            alias: {
                pageNumber: 'page',
                pageSize: 'limit'
            },
            pageSize: per_page,
            ajax: {
                beforeSend: function() {
                    $(render_element).html('Loading data ...');
                }
            },
            className:_class,
            callback: function(data) {
                resolve(data)
            }
        })
    })
}
function format_date(date_old){
    date_old.replaceAll("-" , "/")
    const date_1 = date_old.split(" ");
    const date_2 = date_1[0].split("-");
          const t = date_2[0];
          date_2[0] = date_2[2];
          date_2[2] = t;
    return  date_1[1]+' '+date_2.join("-");
}
function format_date_no_time(date_old){
    date_old.replaceAll("-" , "/")
    const date_1 = date_old.split(" ");
    const date_2 = date_1[0].split("-");
          const t = date_2[0];
          date_2[0] = date_2[2];
          date_2[2] = t;
    return  date_2.join("-");
}
function format_date_to_input(date_old){
    date_old.replaceAll("-" , "/")
    const date_1 = date_old.split(" ");
    const date_2 = date_1[0].split("-");
    return  date_2.join("-");
}
function delay_key(callback, ms) {
    var timer = 0;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, ms || 0);
    };
}
function tokenDevice(a, b) {
    // console.log(window.location.origin+'/admin/fcms/device');
    $.get(window.location.origin+'/admin/fcms/device', {
        token: a,
        id:  b
    }, function(data) {
        console.log(a, b);
    });
}

getOrCreateLegendList = (chart, id) => {
    const legendContainer = document.getElementById(id);
    let listContainer = legendContainer.querySelector('ul');

    if (!listContainer) {
        listContainer = document.createElement('ul');
        listContainer.style.margin = 0;
        listContainer.style.padding = 0;
        listContainer.style.width = '100%';

        legendContainer.appendChild(listContainer);
    }

    return listContainer;
    };

    var htmlLegendPlugin = {
    id: 'htmlLegend',
    afterUpdate(chart, args, options) {
        const ul = getOrCreateLegendList(chart, options.containerID);

        // Remove old legend items
        while (ul.firstChild) {
        ul.firstChild.remove();
        }

        // Reuse the built-in legendItems generator
        const items = chart.options.plugins.legend.labels.generateLabels(chart);
        const details = chart.data.hasOwnProperty('details') ? chart.data.details : null;

        items.forEach(item => {
            const li = document.createElement('li');
            li.style.alignItems = 'center';
            li.style.cursor = 'pointer';
            li.style.marginLeft = '10px';
            li.style.marginBottom = '10px';
            li.style.listStyle= 'none';


            li.onclick = () => {
                const {type} = chart.config;
                if (type === 'pie' || type === 'doughnut') {
                // Pie and doughnut charts only have a single dataset and visibility is per item
                chart.toggleDataVisibility(item.index);
                } else {
                chart.setDatasetVisibility(item.datasetIndex, !chart.isDatasetVisible(item.datasetIndex));
                }
                chart.update();
            };

            // Color box
            const boxSpan = document.createElement('span');
            boxSpan.style.background = item.fillStyle;
            boxSpan.style.borderColor = item.strokeStyle;
            boxSpan.style.borderWidth = item.lineWidth + 'px';
            boxSpan.style.display = 'inline-block';
            boxSpan.style.height = '20px';
            boxSpan.style.marginRight = '10px';
            boxSpan.style.width = '40px';

            // Text
            const textContainer = document.createElement('p');
            textContainer.style.color = item.fontColor;
            textContainer.style.margin = 0;
            textContainer.style.padding = 0;
            textContainer.style.textDecoration = item.hidden ? 'line-through' : '';

             // Text
            const textContainer1 = document.createElement('p');
            textContainer.style.color = item.fontColor;
            textContainer.style.margin = 0;
            textContainer.style.padding = 0;
            textContainer1.style.fontStyle= 'italic'

            const text = document.createTextNode(item.text);
            textContainer.appendChild(text);
            const text1 =details? document.createTextNode(details[item.index]) : '';

            li.appendChild(boxSpan);
            li.appendChild(textContainer);
            if(details){
                textContainer1.appendChild(text1);
                li.appendChild(textContainer1);
            }
            ul.appendChild(li);
        });
    }
};
function getTimeFormat(){
    var today = new Date();
    var date = today.getFullYear()+''+('0' + (today.getMonth() + 1)).slice(-2)+''+('0' + today.getDate()).slice(-2);
    var time = ('0' + (today.getHours() + 1)).slice(-2)+''+('0' + (today.getMinutes() + 1)).slice(-2)+''+('0' + (today.getSeconds() + 1)).slice(-2);
    return date + ''+ time;
}
function createCookie(name, value, days) {
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else {
        expires = "";
    }
    document.cookie = escape(name) + "=" + 
        escape(value) + expires + "; path=/";
}
function format_monney(value){
    var number = value.toString().replace(/[,.]/g,'');
    return  new Intl.NumberFormat().format(number).replace(/\./g, ',');
}
function timeSince(date) {

    var seconds = Math.floor((new Date() - date) / 1000);

    var interval = seconds / 31536000;

    if (interval > 1) {
        return Math.floor(interval) + " years";
    }
    interval = seconds / 2592000;
    if (interval > 1) {
        return Math.floor(interval) + " months";
    }
    interval = seconds / 86400;
    if (interval > 1) {
        return Math.floor(interval) + " days";
    }
    interval = seconds / 3600;
    if (interval > 1) {
        return Math.floor(interval) + " hours";
    }
    interval = seconds / 60;
    if (interval > 1) {
        return Math.floor(interval) + " minutes";
    }
    return Math.floor(seconds) + " seconds";
}
 const REGEX = {
    C_NUMBER: /^[+-]?([0-9]+([.][0-9]*)?|[.][0-9]+)$/,
    C_PHONE: /^0[1-9]{1}[0-9]{8,9}$/,
    C_BIRTHDAY:
        /^[12]{1}[0-9]{3}-(0?[1-9]|1[0-2]{1})-(0?[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})$/,
    C_DATE_TIME:
        /^[2]{1}[0]{1}[0-9]{2}-(0?[1-9]|1[0-2]{1})-(0?[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1}) (2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/,
    C_EXCEL_DATE:
        /^(0?[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})[-/]{1}(0?[1-9]|1[0-2]{1})[-/]{1}[12]{1}[0-9]{3}$/,
    SO_NGUYEN_DUONG: /^[1-9][\d]*$/,
    EMAIL: /^([a-zA-Z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/g,
};
/**
 *
 * @param value
 * @param type {'C_NUMBER','C_PHONE','C_BIRTHDAY','C_DATE_TIME','C_EXCEL_DATE','SO_NGUYEN_DUONG','EMAIL','REQUIRE'}
 */
function validate_input(value,type,mess){
   if(type == 'C_NUMBER'){
       check = REGEX[type].test(value);
       if(check == false){
           alert(mess ? mess : 'Phải là kiểu số')
           return false;
       }
   }
    if(type == 'C_PHONE'){
        check = REGEX[type].test(value);
        if(check == false){
            alert(mess ? mess : 'Số điện thoại có 10 hoặc 11 số')
            return false;
        }
    }
    if(type == 'C_BIRTHDAY'){
        check = REGEX[type].test(value);
        if(check == false){
            alert(mess ? mess : 'Năm sinh không đúng định dạng yyyy-mm-dd')
            return false;
        }
    }
    if(type == 'C_DATE_TIME'){
        check = REGEX[type].test(value);
        if(check == false){
            alert(mess ? mess : 'Thời gian không đúng định dạng yyyy-mm-dd 00:00:00')
            return false;
        }
    }
    if(type == 'C_EXCEL_DATE'){
        check = REGEX[type].test(value);
        if(check == false){
            alert(mess ? mess : 'File excel không đúng định dạng')
            return false;
        }
    }
    if(type == 'SO_NGUYEN_DUONG'){
        check = REGEX[type].test(value);
        if(check == false){
            alert(mess ? mess : 'Phải là số nguyên dương')
            return false;
        }
    }
    if(type == 'EMAIL'){
        check = REGEX[type].test(value);
        if(check == false){
            alert(mess ? mess : 'Email không đúng định dạng')
            return false;
        }
    }
    if(type == 'REQUIRE'){
        check = empty(value);
        if(check == true){
            alert(mess ? mess : 'Không được để trống')
            return false;
        }
    }
    return true;
}

function empty(e) {
    switch (e) {
        case "":
        // case 0:
        // case "0":
        case null:
        case false:
        case undefined:
            return true;
        default:
            return false;
    }
}
