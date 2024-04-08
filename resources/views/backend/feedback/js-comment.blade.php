<script>
    $(function() {
        // go to comment id
        var $comment = $(location.hash);

        if ($comment.length > 0) {
            location.hash = '';

            $('html, body').animate({
                'scrollTop': $comment.offset().top - 100
            }, 500);

            if ($comment.hasClass('comment-form')) {
                $('textarea', $comment).focus();
            }
        }

        // go to reply input
        $('body').on('click', 'a.btn-reply', function() {
            var target = $(this).data('target');
            var $obj = $(target);
            var top = $obj.offset().top;

            $('html, body').animate({
                'scrollTop': top > 50 ? top - 50 : top
            }, 500);

            $('textarea', $obj).focus();
        });

        // .btn-comment-status
        $('body').on('click', '.btn-comment-status', function(e) {
            var $this = $(this);
            var id = $this.data('id');
            var url = '{{ url("admin/comments/action") }}';
            var status = $this.data('status');
            var _token = $('meta[name="csrf-token"]').attr('content');
            var data = {
                _token: _token,
                method: 'status',
                status: status ? 0 : 1,
                ids: [id]
            };

            $.post(url, data, function(json) {
                if (json.error == 0) {
                    var $body = $('#comment-' + id + ' > .comment-text > .comment-body');

                    if (status == 1) {
                        $this.text('Duyệt');
                        $this.data('status', 0);
                        $body.addClass('bg-danger');
                    } else {
                        $this.text('Bỏ duyệt');
                        $this.data('status', 1);
                        $body.removeClass('bg-danger');
                    }
                }
            });
        });

        // .btn-comment-delete
        $('body').on('click', '.btn-comment-delete', function(e) {
            if (confirm('Có chắc bạn muốn xóa?')) {
                var $this = $(this);
                var id = $this.data('id');
                var url = '{{ url("admin/comments/action") }}';
                var _token = $('meta[name="csrf-token"]').attr('content');
                var data = {
                    _token: _token,
                    method: 'delete',
                    status: status ? 0 : 1,
                    ids: [id]
                };

                $.post(url, data, function(json) {
                    if (json.error == 0) {
                        $('#comment-' + id).remove();
                    }

                });
            }
        });

        // auto height
        $('body').on('input change', '.input-auto-height', function() {
            $(this).css('height', 'auto').css('height', this.scrollHeight + 'px');
        });

        // comment
        $('body').on('keypress', '.input-comment', function(e) {
           
           var url_base = window.location.origin;
            var keyCode = (e.keyCode ? e.keyCode : e.which);

            if (keyCode == 13) {
                if (!e.shiftKey) {
                    var _token = $('meta[name="csrf-token"]').attr('content');
                    var $this = $(this);
                    var action = $this.data('action');
                    var type = $this.data('type');
                    var post_id = $this.data('post_id');
                    var parent_id = $this.data('parent_id');
                    var key_input = $(this).attr('data-textarea_id');
                    if($('#inputFile').val() || $('#inputFile-'+key_input).val()){
                        var fileReader = new FileReader();
                        var file_name=null;
                        if($('#inputFile').val() ){
                            file_name= $('#inputFile').prop('files')[0].name;
                            fileReader.readAsDataURL($('#inputFile').prop('files')[0]);
                        }else{
                            file_name= $('#inputFile-'+key_input).prop('files')[0].name;
                            fileReader.readAsDataURL($('#inputFile-'+key_input).prop('files')[0]);


                        }
                           
                                    fileReader.onload = function () {
                                    var data = fileReader.result;  // data <-- in this var you have the file data in Base64 format
                                    var input = {
                                        '_token': _token,
                                        'type': type,
                                        'post_id': post_id,
                                        'parent_id': parent_id,
                                        'content': $this.val(),
                                        'rating': 0,
                                        'status': 1,
                                        'fileBase64':data,
                                        'name_fileupload':file_name
                                    };
                                    $this.val('').trigger('change');
                                    $.post('{{ url("admin/comments/save") }}', input, function(json) {
                                        if (json.error == 1) {
                                            toastr.warning(json.msg);
                                        }
                                        if (json.error == 0) {
                                            var colors = ['#008a00', '#0050ef', '#6a00ff', '#a20025', '#fa6800', '#825a2c', '#6d8764'];
                                            var item = json.data;
                                            var comment_img = '';
                                            var comment_body = '';
                                            var comment_info = '';
                                            var comment_reply = '';
                                            var comment_form = '';

                                            comment_img += '<div class="img-user img-circle img-sm" style="background: ' + colors[item.user_id % 7] + '">';
                                            if(item.avatar != ''){
                                                comment_img += '    <img src="' + item.avatar + '" title="' + item.username + '">';
                                            }else{
                                                comment_img += '    <strong>' + item.char + '</strong>';
                                            }
                                             
                                            comment_img += '</div>';
                                            comment_body += '<div class="comment-body">';
                                            comment_body += '    <span class="username">' + item.username + '</span>';
                                            comment_body += '    <div class="comment-content">' + '<a target="_blank" href="'+item.url_fileupload+'" style="height:15px">' + item.name_fileupload + '</a>' + '</div>'; 
                                            comment_body += '    <div class="comment-content">' + item.content + '</div>';
                                            comment_body += '</div>';

                                            comment_info += '<div class="comment-info">';
                                            comment_info += '    <a class="text-muted btn-reply" href="javascript:;" data-target="#reply-' + item.parent_id + '">Trả lời</a>';
                                            comment_info += '    &middot; ';
                                            comment_info += '    <a class="text-muted btn-comment-status" href="javascript:;" data-id="' + item.id + '" data-status="' + item.status + '">Bỏ duyệt</a>';
                                            comment_info += '    &middot; ';
                                            comment_info += '    <a class="text-muted btn-comment-delete" href="javascript:;" data-id="' + item.id + '">Xóa</a>';
                                            comment_info += '    &middot; ';
                                            comment_info += '    <span class="text-muted">' + item.created + '</span>';
                                            comment_info += '</div>';

                                            var html = '';
                                            html += '<div class="box-comment" id="comment-' + item.id + '">';
                                            html += '    ' + comment_img;
                                            html += '    <div class="comment-text">';
                                            html += '    ' + comment_body;
                                            html += '    ' + comment_info;
                                            html += '    ' + comment_reply;
                                            html += '    ' + comment_form;
                                            html += '    </div>';
                                            html += '</div>';

                                            if (action == 'comment') {
                                                $('.box-comments').append(html);
                                            }

                                            if (action == 'reply') {
                                                $('#comment-' + item.parent_id + ' .comment-reply').append(html);
                                            }
                                        }
                                    });
                              };
                              
                      }else{
                                    var input = {
                                        '_token': _token,
                                        'type': type,
                                        'post_id': post_id,
                                        'parent_id': parent_id,
                                        'content': $this.val(),
                                        'rating': 0,
                                        'status': 1,
                                    };
                                    $this.val('').trigger('change');
                                    $.post('{{ url("admin/comments/save") }}', input, function(json) {
                                        if (json.error == 1) {
                                            toastr.warning(json.msg);
                                        }
                                        if (json.error == 0) {
                                            var colors = ['#008a00', '#0050ef', '#6a00ff', '#a20025', '#fa6800', '#825a2c', '#6d8764'];
                                            var item = json.data;

                                            var comment_img = '';
                                            var comment_body = '';
                                            var comment_info = '';
                                            var comment_reply = '';
                                            var comment_form = '';

                                            comment_img += '<div class="img-user img-circle img-sm" style="background: ' + colors[item.user_id % 7] + '">';
                                            if(item.avatar != ''){
                                                comment_img += '    <img src="' + item.avatar + '" title="' + item.username + '">';
                                            }else{
                                                comment_img += '    <strong>' + item.char + '</strong>';
                                            }
                                            comment_img += '</div>';

                                            comment_body += '<div class="comment-body">';
                                            comment_body += '    <span class="username">' + item.username + '</span>';
                                            // comment_body += '    <div class="comment-content">' + '<a class="download" href="#" style="height:15px">' + item.username + '</a>' + '</div>';
                                            comment_body += '    <div class="comment-content">' + item.content + '</div>';
                                            comment_body += '</div>';

                                            comment_info += '<div class="comment-info">';
                                            comment_info += '    <a class="text-muted btn-reply" href="javascript:;" data-target="#reply-' + item.parent_id + '">Trả lời</a>';
                                            comment_info += '    &middot; ';
                                            comment_info += '    <a class="text-muted btn-comment-status" href="javascript:;" data-id="' + item.id + '" data-status="' + item.status + '">Bỏ duyệt</a>';
                                            comment_info += '    &middot; ';
                                            comment_info += '    <a class="text-muted btn-comment-delete" href="javascript:;" data-id="' + item.id + '">Xóa</a>';
                                            comment_info += '    &middot; ';
                                            comment_info += '    <span class="text-muted">' + item.created + '</span>';
                                            comment_info += '</div>';

                                            var html = '';
                                            html += '<div class="box-comment" id="comment-' + item.id + '">';
                                            html += '    ' + comment_img;
                                            html += '    <div class="comment-text">';
                                            html += '    ' + comment_body;
                                            html += '    ' + comment_info;
                                            html += '    ' + comment_reply;
                                            html += '    ' + comment_form;
                                            html += '    </div>';
                                            html += '</div>';

                                            if (action == 'comment') {
                                                $('.box-comments').append(html);
                                            }

                                            if (action == 'reply') {
                                                $('#comment-' + item.parent_id + ' .comment-reply').append(html);
                                            }
                                        }
                                    });
                      }
                      
                       if($('#inputFile').val() ){
                            $('#fileName').text(''); 
                            $("#inputFile").val(null);
                            $("#iconRemoveFile").hide()
                        }else{
                            $('#fileName-'+key_input).text(''); 
                            $('#inputFile-'+key_input).val(null);
                            $('#iconRemoveFile-'+key_input).hide()
                        }
                      return false;
                }
            }
        });
    });
</script>