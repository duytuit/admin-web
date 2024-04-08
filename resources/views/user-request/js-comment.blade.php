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
                var id_request = $this.data('id_request');
                del_comment(id,id_request);
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
                    var parent_id = $this.data('parent_id');
                    var key_input = $(this).attr('data-textarea_id');
                    var id_request = $(this).attr('data-id_request');
                    console.log(($('#inputFile').prop('files')));
                    if($('#inputFile').val()){
                        let formData = new FormData();
                        let files = $('#inputFile').prop('files');
                        for (let index = 0; index < files.length; index++) {
                            formData.append('file',files[index]);
                        }
                        formData.append('parent_id',parent_id);
                        formData.append('content', $this.val());
                        formData.append('id_request',id_request);
                        post_comment(formData);
                    }else if($('#inputFile-'+key_input).val()){
                        let formData = new FormData();
                        let files = $('#inputFile-'+key_input).prop('files');
                        for (let index = 0; index < files.length; index++) {
                            formData.append('file',files[index]);
                        }
                        formData.append('parent_id',parent_id);
                        formData.append('content', $this.val());
                        formData.append('id_request',id_request);
                        post_comment(formData);
                    }else{
                        let formData = new FormData();
                        formData.append('parent_id',parent_id);
                        formData.append('content', $this.val());
                        formData.append('id_request',id_request);
                        post_comment(formData);
                    }
                    return false;
                }
            }
        });
        async function post_comment(param){
            console.log(param);
            let method='post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            let _result = await call_api_form_data(method, 'admin/addUserReqComment'+param_query,param);
            console.log(_result);
            toastr.success(_result.mess);
            setTimeout(function(){
                location.reload();
            }, 500);
        }
        async function del_comment(id,id_request){
            let method='get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            param_query +="&id="+id;
            let _result = await call_api(method, 'admin/deleteUserReqComment'+param_query,null);
            console.log(_result);
            toastr.success(_result.mess);
            setTimeout(function(){
                location.reload();
            }, 500);
        }
    });
</script>