<script>
    // Câu trả lời
    var $poll_options = $('.poll_options');
    var $poll_index = $('#poll_index');

    $poll_options.sortable({
        handle: '.btn-handle'
    });

    $poll_options.on('click', '.btn-remove', function() {
        if ($('>div', $poll_options).length > 1) {
            $(this).parent().parent().remove();
            var max = $('input[name="maximum"]').attr('max');
            $('input[name="maximum"]').attr('max', max-1);

            var value = $('input[name="maximum"]').val();
            max = $('input[name="maximum"]').attr('max');
            if(value > max){
                $('input[name="maximum"]').val(max);
            }
        }
    });

    $('.btn-add-option').click(function() {
        var index = $poll_index.val();

        index = parseInt(index) + 1;

        $poll_index.val(index);

        var html = '<div class="input-group">' +
            '   <span class="input-group-addon btn-handle"><i class="fa fa-arrows"></i></span>' +
            '   <input type="text" name="options[]" class="form-control" placeholder="Câu trả lời">' +
            '   <span class="input-group-btn">' +
            '       <button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>' +
            '   </span>' +
            '</div>';

        $(".poll_options").append(html);

        var max = $('input[name="maximum"]').attr('max',index + 1);
        
    });
</script>