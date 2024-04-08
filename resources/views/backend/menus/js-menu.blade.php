<script>
    $('a.btn-delete-menu').click(function () {
        if (confirm('Có chắc bạn muốn xóa?')) {
            var id = $(this).data('id');
            var url = $(this).data('url');
            var _token = $('meta[name="csrf-token"]').attr('content');
            var data = {
                _token: _token,
                method: 'delete',
                id: id
            };

            $.post(url, data, function (json) {
                location.reload();
            });
        }
    });

    $('a.btn-edit-menu').click(function () {
        var id = $(this).data('id');
        
        $.ajax({
            url: "{{ route('admin.menus.show') }}",
            type: 'GET',
            data: {
                id: id,
            },
            success: function(data) {
                if ($.isEmptyObject(data.errors)) {
                    $('#myMenus').modal('show');
                
                    $("input[name='id']").val(id);
                    $("input[name='title']").val(data.data.title);
                    $("input[name='url']").val(data.data.url);
                    $("input[name='icon']").val(data.data.icon);
                } else {
                    alert(data.errors)
                }
            }
        });
    });

    $('.btn-add-menu').click(function(e){
        e.preventDefault();

        var _token = $("[name='_token']").val();
        var title = $("input[name='title']").val();
        var url = $("input[name='url']").val();
        var icon = $("input[name='icon']").val();
        var parent_id = $("select[name='parent_id']").val();
  
        $.ajax({
            url: "{{ route('admin.menus.validator_update') }}",
            type: 'POST',
            data: {
                _token: _token,
                title: title,
                url: url,
                icon: icon,
                parent_id: parent_id,
            },
            success: function(data) {
                if ($.isEmptyObject(data.errors)) {
                    $('#form-add-menu').submit();
                } else {
                    printErrorMsg(data.errors, '.menu-success-msg', '.menu-error-msg');
                }
            }
        });
    });

    function printErrorMsg(msg, success, error) {
        $(success).find("ul").html('');
        $(error).find("ul").html('');

        $(success).css('display', 'none');
        $(error).css('display', 'block');
        $.each(msg, function(key, value) {
            $(error).find("ul").append('<li>' + value + '</li>');
        });
    }
</script>