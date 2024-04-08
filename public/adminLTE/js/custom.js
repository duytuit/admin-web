$(document).ready(function() {

    //Icheck
    $('input.iCheck').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });

     //Icheck
    $('input.iCheck_Red').iCheck({
        checkboxClass: 'icheckbox_square-red',
        radioClass: 'iradio_square-red',
        increaseArea: '20%' // optional
    });

    // check all
    $('input.checkAll').on('ifToggled', function(e) {
        var target = $(this).data('target');

        if (this.checked) {
            $(target + ":not(:disabled)").iCheck('check');
        } else {
            $(target + ":not(:disabled)").iCheck('uncheck');
        }
    });

    // select 2
    $('select.select2').select2()

    // datetimepicker
    if ($.isFunction($.fn.datetimepicker)) {
        $('.datetimepicker').each(function(){
            var format = $(this).data('format');

            if(format == undefined) {
                format = 'Y-MM-DD HH:mm:ss';
            }

            $(this).datetimepicker({
                'format': format
            });
        });        

        $('.datetimepicker input').click(function() {
            $(this).parent().find('.input-group-addon').trigger('click');
        });
    } else {
        console.log('function "datetimepicker" is not exist.');
    }

    // kcfinder input
    $('body').on('click', '[data-file] button', function() {
        var $parent = $(this).parent().parent();
        var type = $parent.data('file');

        window.KCFinder = {
            callBack: function(url) {
                $('input', $parent).first().val(url);
                window.KCFinder = null;
            }
        };

        window.open('/kcfinder/browse.php?type=' + type, 'kcfinder_textbox',
            'status=0, toolbar=0, location=0, menubar=0, directories=0, resizable=1, scrollbars=0, width=900, height=600'
        );
    });

    // ul.nav-tabs > li.active open
    if (location.hash) {
        $('[data-toggle="tab"][href="' + location.hash + '"]').trigger('click');
    }

    $('[data-toggle="tab"]').click(function() {
        location.hash = $(this).attr('href');
    });

    // .btn-remove
    $('a.btn-remove').click(function () {
        if (confirm('Có chắc bạn muốn xóa?')) {
            var id = $(this).data('id');
            var url = $(this).data('url');
            var group = $(this).data('group');
            var _token = $('meta[name="csrf-token"]').attr('content');
            var data = {
                _token: _token,
                method: 'delete',
                ids: [id],
                group: group,
            };

            $.post(url, data, function (json) {
                location.reload();
            });
        }
    });

});
function isValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
}
function call_api_server(method,url,param) {
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