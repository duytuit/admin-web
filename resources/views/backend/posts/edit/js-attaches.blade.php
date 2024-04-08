<script>
// Hình ảnh
var $attaches = $('#attaches');
var $attach_index = $('#attach_index');

$attaches.on('click', '.btn-remove', function() {
    if ($('tbody > tr', $attaches).length > 1) {
        $(this).parent().parent().remove();
    }
});

// kcfinder input
$attaches.on('click', '.btn-select', function() {
    var $parent = $(this).parent().parent();

    window.KCFinder = {
        callBack: function(url) {
            $('img', $parent).attr('src', url);
            $('.input-attach', $parent).val(url);
            window.KCFinder = null;
        }
    };

    window.open('/kcfinder/browse.php?type=file', 'kcfinder_textbox',
        'status=0, toolbar=0, location=0, menubar=0, directories=0, resizable=1, scrollbars=0, width=900, height=600'
    );
});


$attaches.on('change','.upload_file', function(e) {
    var data_index = $(this).data('index');
    if (e.target.files[0]) {
        let formData = new FormData();
        formData.append('file',e.target.files[0]);
        formData.append('folder',"{{auth()->user() ? auth()->user()->id : null}}");
        $.ajax({
                url: "{{route('api.v1.upload.upload_v2')}}",
                type: 'POST',
                data: formData,
                contentType: false, //tell jquery to avoid some checks
                processData: false,
                success: function (response) {
                    console.log(response);
                    if (response.success == true) {
                        $('.index_'+data_index).val(response.origin);
                        toastr.success(response.msg);

                    } else {
                        toastr.error('thất bại');
                    }
                },
                error: function(response) {
                    toastr.error('đã có lỗi xảy ra.');
                }
        });
        
    }
  
});

$attaches.on('click', '.btn-add', function() {
    var index = $attach_index.val();

    index = parseInt(index) + 1;

    $attach_index.val(index);

    var html = '<tr class="checkbox_parent">' +
        '   <td>' +
        '       <input type="text" class="form-control input-attach index_'+index+'" name="attaches[' + index + '][src]" value="">' +
        '   </td>' +
        '   <td>' +
        '       <input type="text" class="form-control" name="attaches[' + index + '][sort_order]" value="' + index + '">' +
        '   </td>' +
        '   <td>' +
        '       <input id="uploadBtn" type="file" data-index="'+index+'" class="upload_file" style="margin-bottom: 5px;"/>'+
        '       <button type="button" class="btn btn-danger btn-remove"><i class="fa fa-trash"></i></button> ' +
        '   </td>' +
        '</tr>';

    $('tbody', $attaches).append(html);
});
</script>