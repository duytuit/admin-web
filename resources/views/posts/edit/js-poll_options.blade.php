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

    
    get_data_select_user({
        object: '#select-poll-option',
        data_id: 'id',
        data_text1: 'title',
        title_default: 'Chọn câu hỏi bình chọn'
    });

    function get_data_select_user(options) {
        $(options.object).select2({
            ajax: {
                url: '{{ route("admin.polloptions.getAll") }}',
                dataType: 'json',
                data: function(params) {
                    var query = {
                        search: params.term,
                    }
                    return query;
                },
                processResults: function(json, params) {
                    var results = [{
                        id: '',
                        text: options.title_default
                    }];

                    for (i in json.data) {
                        var item = json.data[i];
                        results.push({
                            id: item[options.data_id],
                            text: item[options.data_text1]
                        });
                    }
                    return {
                        results: results,
                    };
                },
            }
        });
    }

    var poll_option_ids = [];

    $('.btn-save-option').click(function(e){
        e.preventDefault();

        var _token = $("[name='_token']").val();
        var post_id = {{ $id }};
        var poll_title = $("textarea[name='poll_title']").val();
        var input = $('#form-save-poll-option').serializeArray();
        var options = getValueOption(input);
  
        $.ajax({
            url: "{{ route('admin.posts.save.option') }}",
            type: 'POST',
            data: {
                _token: _token,
                post_id: post_id,
                title: poll_title,
                options: options,
            },
            success: function(data) {
                if ($.isEmptyObject(data.errors)) {
                    printSuccessMsg('Thêm câu hỏi bình chọn thành công.', '.poll-success-msg', '.poll-error-msg');
                    console.log(data)
                    poll_option_ids.push(data.id);
                    $("input[name='poll_option_ids']").val(poll_option_ids);
                    $('#view-poll-option-js').append(data.view);
                    setTimeout(function(){ 
                        $("#modal-add-option").modal('hide');
                    }, 1000);
                } else {
                    printErrorMsg(data.errors, '.poll-success-msg', '.poll-error-msg');
                }
            }
        });
    });

    $('.btnSubmitAddOption').click(function(e){
        var _token = $("[name='_token']").val();
        var post_id = {{ $id }};
        var poll_options = $("select[name='poll_options']").val();
        var poll_ids = $("input[name='poll_option_ids']").val();

        $.ajax({
            url: "{{ route('admin.posts.add.option') }}",
            type: 'POST',
            data: {
                _token: _token,
                post_id: post_id,
                poll_options: poll_options,
                poll_ids: poll_ids,
            },
            success: function(data) {
                if ($.isEmptyObject(data.errors)) {
                    printSuccessMsg("Thêm câu hỏi thành công.", '.print-success-msg', '.print-error-msg');
                    poll_option_ids.push(poll_options);
                    $("input[name='poll_option_ids']").val(poll_option_ids);
                    $('#view-poll-option-js').append(data);
                    setTimeout(function(){ 
                        $('.print-success-msg').css('display', 'none');
                    }, 1000);
                } else {
                    printErrorMsg(data.errors, '.print-success-msg', '.print-error-msg');
                    setTimeout(function(){ 
                        $('.print-error-msg').css('display', 'none');
                    }, 1000);
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

    function printSuccessMsg(msg, success, error) {
        $(success).find("ul").html('');
        $(error).find("ul").html('');

        $(error).css('display', 'none');
        $(success).css('display', 'block');
        
        $(success).find("ul").append('<li>' + msg + '</li>');
    }

    function getValueOption(input){
        var options = [];
        input.forEach(function(e) {
            if(e['name'] == 'options[]'){
                options.push(e['value']);
            }
        });
        
        return options;
    }
</script>