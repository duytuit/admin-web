<script>
// Hình ảnh
var $images = $('#images');
var $image_index = $('#image_index');

$images.on('click', '.btn-remove', function() {
    if ($('tbody > tr', $images).length > 1) {
        $(this).parent().parent().remove();
    }
});

// kcfinder input
$images.on('click', '.btn-select', function() {
    var $parent = $(this).parent().parent();

    window.KCFinder = {
        callBack: function(url) {
            $('img', $parent).attr('src', url);
            $('.input-image', $parent).val(url);
            window.KCFinder = null;
        }
    };

    window.open('/kcfinder/browse.php?type=image', 'kcfinder_textbox',
        'status=0, toolbar=0, location=0, menubar=0, directories=0, resizable=1, scrollbars=0, width=900, height=600'
    );
});

$images.on('click', '.btn-add', function() {
    var index = $image_index.val();

    index = parseInt(index) + 1;

    $image_index.val(index);

    var html = '<tr>' +
        '   <td>' +
        '       <img src="/images/no-img-xs.jpg" width="100" height="100" alt="no image">' +
        '   </td>' +
        '   <td>' +
        '       <input type="hidden" class="input-image" name="images[' + index + '][src]" value="">' +
        '       <input type="text" class="form-control" name="images[' + index + '][sort_order]" value="' + index + '">' +
        '   </td>' +
        '   <td>' +
        '       <button type="button" class="btn btn-primary btn-select"><i class="fa fa-image"></i></button> ' +
        '       <button type="button" class="btn btn-danger btn-remove"><i class="fa fa-trash"></i></button> ' +
        '   </td>' +
        '</tr>';

    $('tbody', $images).append(html);
});
</script>