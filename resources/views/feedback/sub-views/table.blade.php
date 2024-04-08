@foreach($searchs as $fb)
    <tr>
        <td colspan="" rowspan="" headers="">{{$fb->id}}</td>
        <td colspan="" rowspan="" headers="">{{$fb->title}}Sửa chữa cửa nhà vệ sinh</td>
        <td colspan="" rowspan="" headers="">{{$fb->type}}</td>
        <td colspan="" rowspan="" headers="">{{$fb->pubUserProfile->display_name}}</td>
        <td colspan="" rowspan="" headers="">{{$fb->created_at}}</td>
        <td colspan="" rowspan="" headers="">
            @if($fb->status == 0)
                Chưa xử lý
            @elseif($fb->status == 1)
                Đã xử lý
            @endif
        </td>
        <td colspan="" rowspan="" headers=""><a href="javascript:void(0);">Xem</a></td>
        <td colspan="" rowspan="" headers="">
            <a href="javascript:void(0);" class="btn btn-success" title="Chi tiết"><i class="fa fa-share-square-o"></i></a>
            {{--                                                    <a href="javascript:void(0);" class="btn btn-danger" title="xóa"><i class="fa fa-times"></i></a>--}}
        </td>
    </tr>
@endforeach