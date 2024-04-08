@if($camp_assigns)
    <table class="table table-hover table-striped table-bordered" style="overflow: scroll;height: 300px;display: inline-block;width: 100%;">
        <thead class="bg-primary">
        <tr>
            <th><input type="checkbox" @if($result["data"]=='up') class="iCheck checkAll"  data-target=".checkSingle" @elseif($result["data"]=='down') class="iCheckdel checkAlldel"  data-target=".checkSingleDel" @endif  /></th>
            <th>ID</th>
            <th>Khách hàng</th>
            <th>Số điện thoại</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($camp_assigns as $item)
            <tr valign="middle">
                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="@if($result["data"]=='up') iCheck checkAll checkSingle @elseif($result["data"]=='down') iCheckdel checkAlldel checkSingleDel @endif" /></td>
                <td>{{ $item->id }}</td>
                <td>{{ $item->customer_name }}</td>
                <?php $phone_num = substr($item->customer_phone, 0, 4) . '****' . substr($item->customer_phone, 8, 4); ?>
                <td>{{ $phone_num }}</td>
                <td>{{ $item->customer_email }}</td>
                <td>
                    @if($result["data"]=='up')
                        <a href='javascript:;' class="btn btn-sm btn-primary add_allo_ctv" title="Phân bổ" data-assign='{{ $item->id }}' >
                            <i class="fa fa-check-square-o"></i>
                        </a>
                    @elseif($result["data"]=='down')
                        <a href='javascript:;' class="btn btn-sm btn-danger del_allo_ctv" title="Loại bỏ" data-assign='{{ $item->id }}' >
                            <i class="fa fa-times "></i>
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if($result["data"]=='up')
        <a href="javascript:void(0);" class="btn btn-sm btn-primary add_all_check" >Add all</a>
    @elseif($result["data"]=='down')
        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_all_check" >Del all</a>
    @endif

@endif
<script>
    $('input.iCheck').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });
    $('input.iCheckdel').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });
    // check all
    $('input.checkAll,input.checkAlldel').on('ifToggled', function(e) {
        var target = $(this).data('target');

        if (this.checked) {
            $(target).iCheck('check');
        } else {
            $(target).iCheck('uncheck');
        }
    });
    var status_ctv = '{{$result["status"]}}';
    var messages = '{{$result["messages"]}}';
    var data = '{{$result["data"]}}';


    $(function () {
        if(status_ctv == 'NOT_OK'){
            $('.help-block-ctv').html(messages);
        }else{
            $('.help-block-ctv').html('');
        }
        if(data == 'up'){
            $('.add_allo_ctv').on('click',function () {
                var _this = $(this);
                var assign = _this.data('assign');
                $.post('{{ url("/admin/users/ajax-up-campassign") }}', {
                    assign_id : assign,
                    user_id : $('#ip_user_id').val(),
                    _token : '{{ csrf_token() }}',
                }, function(data) {
                    show_campassign($('#ip_user_id').val());
                    show_user_campassign($('#ip_user_id').val());
                });
            });
            $('.add_all_check').click(function () {
                var val = [];
                var sel = $('.list_customer_allocation input:checkbox:checked').map(function(_, el) {
                    return $(el).val();
                }).get();
                $.post('{{ url("/admin/users/ajax-up-campassign") }}', {
                    assign_ids : sel,
                    user_id : $('#ip_user_id').val(),
                    _token : '{{ csrf_token() }}',
                }, function(data) {
                    show_campassign($('#ip_user_id').val());
                    show_user_campassign($('#ip_user_id').val());
                });
            });
        }
        if(data == 'down'){
            $('.del_allo_ctv').on('click',function () {
                var _this = $(this);
                var assign = _this.data('assign');
                $.post('{{ url("/admin/users/ajax-down-campassign") }}', {
                    assign_id : assign,
                    user_id : $('#ip_user_id').val(),
                    _token : '{{ csrf_token() }}',
                }, function(data) {
                    show_campassign($('#ip_user_id').val());
                    show_user_campassign($('#ip_user_id').val());
                });
            });
            $('.del_all_check').click(function () {
                var val = [];
                var sel = $('.list_ctv_allocation input:checkbox:checked').map(function(_, el) {
                    return $(el).val();
                }).get();
                $.post('{{ url("/admin/users/ajax-down-campassign") }}', {
                    assign_ids : sel,
                    user_id : $('#ip_user_id').val(),
                    _token : '{{ csrf_token() }}',
                }, function(data) {
                    show_campassign($('#ip_user_id').val());
                    show_user_campassign($('#ip_user_id').val());
                });
            });
        }
    })
</script>