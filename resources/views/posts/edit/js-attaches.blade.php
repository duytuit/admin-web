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

$attaches.on('click', '.btn-add', function() {
    var index = $attach_index.val();

    index = parseInt(index) + 1;

    $attach_index.val(index);

    var html = '<tr>' +
        '   <td>' +
        '       <input type="text" class="form-control input-attach" name="attaches[' + index + '][src]" value="">' +
        '   </td>' +
        '   <td>' +
        '       <input type="text" class="form-control" name="attaches[' + index + '][sort_order]" value="' + index + '">' +
        '   </td>' +
        '   <td>' +
        '       <button type="button" class="btn btn-primary btn-select"><i class="fa fa-upload"></i></button> ' +
        '       <button type="button" class="btn btn-danger btn-remove"><i class="fa fa-trash"></i></button> ' +
        '   </td>' +
        '</tr>';

    $('tbody', $attaches).append(html);
});
</script>