@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Ý kiến phản hồi
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Ý kiến phản hồi</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-sm-8">
            <div class="box box-primary">
                <div class="box-body">
                    @php
                    $images = json_decode($feedback->attached,true)['images'] ?? [];
                    $files = json_decode($feedback->attached,true)['files'] ?? [];
                    if($feedback->new == 1){
                        $user_info = App\Models\PublicUser\V2\UserInfo::where('user_id',$feedback->user_id)->first();
                    }else{
                        $user_info = App\Models\PublicUser\UserInfo::find($feedback->user_id);
                    }
                    @endphp
                    <div class="form-group">
                        <h4 class="feedback-title">[ {{ $types[$feedback->type] ?? '' }} ] {{ $feedback->title }}
                            <div class="tag_status">
                                @if($feedback->repair_status == 'hoan_thanh')
                                    <span>Đã hoàn thành</span>
                                @elseif($feedback->status == 1)
                                     <a href="javascript:void(0);" id="btn-set-un-status" class="btn-status-set" data-url="{{ route('admin.feedback.action') }}" data-id="{{ $feedback->id }}"><span>Hoàn thành</span></a>
                                @endif
                            </div>
                        </h4>
                        <div class="feedback-content">{!! $feedback->content !!}</div>
                    </div>
                    <div class="form-group">
                        <strong class="feedback-customer text-muted">{{  @$user_info->display_name??@$user_info->full_name?? 'không rõ'}}</strong>
                        &middot;
                        <span class="feedback-created_at text-muted">{{ $feedback->created_at->diffForHumans($now) }}</span>
                    </div>
                    @if ($images)
                    <div class="form-group">
                        <label>Ảnh đính kèm</label>
                        <div class="row attached-images space-5">
                            @foreach ($images as $src)
                            <div class="col-sm-2 col-xs-6">
                                <a href="{{ $src }}" data-toggle="lightbox" data-gallery="attached-images-gallery">
                                    <img class="img-responsive" src="{{ $src }}" alt="image" width="150" height="100" style="width: 150px;height: 100px;">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if ($files)
                    <div class="form-group">
                        <label>File đính kèm</label>
                        <div class="attached-files">
                            @foreach ($files as $src)
                            <div>
                                <a href="{{ $src }}" target="_blank"><i class="fa fa-link"></i> {{ pathinfo($src, PATHINFO_BASENAME) }}</a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="form-group">
                    <div class="btn-set-detail">
                       @if(Request()->type == 'warranty_claim')
                           <a href="{{ route('admin.feedback.warrantyClaim') }}" type="button" class="btn btn-sm btn-default pull-right">Quay lại</a>
                        @endif
                        @if($feedback->repair_status != 'hoan_thanh')
                            @if( in_array('admin.feedback.action',@$user_access_router))
                                @if($feedback->type == 'repair_apartment' || $feedback->type == 'warranty_claim')
                                    <a href="javascript:void(0);" id="btn-set-change-status" class="btn-status-set" data-url="{{ route('admin.feedback.repairChangeStatus') }}" data-id="{{ $feedback->id }}">Hoàn thành</a>
                                @else
                                    @if($feedback->status != 1)
                                        <a href="javascript:void(0);" id="btn-set-status" class="btn-status-set" data-url="{{ route('admin.feedback.action') }}" data-id="{{ $feedback->id }}">Hoàn thành</a>
                                    @endif
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
                <div class="box-footer box-comments">
                    @foreach ($feedback->comments as $keyitem => $item)
                    @php
                    if($item->new == 1){
                        $user_info_1 = App\Models\PublicUser\V2\UserInfo::where('user_id',$item->user_id)->first();
                    }else{
                        $user_info_1 = App\Models\PublicUser\UserInfo::find($item->user_id);
                    }
                    $comment_images =$item->files ? json_decode($item->files) : null;
                    $comment_files =$item->files ? json_decode($item->files) : null;
                    @endphp
                    <div class="box-comment" id="comment-{{ $item->id }}">
                        <!-- User image -->
                        <div class="img-user img-circle img-sm" style="background: {{ $colors[$item->user_id % 7] }}">
                            @if($user_info_1)
                                @if($user_info_1->avatar)
                                    <img src="{{$user_info_1->avatar}}" alt="{{ @$user_info_1->full_name??@$user_info_1->display_name?? 'không rõ'}}" style="border-radius: 50%;">
                                @else
                                    @php
                                        $words = explode(' ', @$user_info_1->full_name??@$user_info_1->display_name?? 'không rõ');
                                        $name = end($words);
                                        $char = substr($name, 0, 1);
                                    @endphp
                                    <strong>{{ strtoupper($char) }}</strong>
                                @endif

                            @endif
                        </div>
                        <div class="comment-text">
                            <div class="comment-body {{ $item->status ? '' : 'bg-danger' }}">
                                <span class="username">{{ @$user_info_1->full_name??@$user_info_1->display_name?? 'không rõ' }}</span>
                                @if($comment_images && count($comment_images->images) > 0)
                                     @php
                                         $comment_images= $comment_images->images;
                                     @endphp
                                    @if($comment_images)
                                        @foreach($comment_images as $_comment_images)
                                                @php 
                                                    $arrUrl = explode("/", $_comment_images);
                                                    $urlName = end($arrUrl);
                                                @endphp
                                                <div class="comment-content-file-item">
                                                    @if (\app\Commons\Helper::check_file_type_is_image($_comment_images))
                                                        <a target="_blank" href="{{ $_comment_images }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_images }}" class="set-custom-img" ></a>
                                                    @else
                                                        <a class="download" href="{{ $_comment_images }}" style="height:15px">
                                                            {{ $urlName }}
                                                        </a>
                                                    @endif
                                                </div>
                                        @endforeach
                                     @endif
                                @endif
                                @if($comment_files && count($comment_files->files) > 0)
                                    @php
                                        $comment_files = $comment_files->files;
                                    @endphp
                                    @if($comment_files)
                                        @foreach($comment_files as $_comment_files)
                                                @php 
                                                    $arrUrl = explode("/", $_comment_files);
                                                    $urlName = end($arrUrl);
                                                @endphp
                                                <div class="comment-content-file-item">
                                                    @if (\app\Commons\Helper::check_file_type_is_image($_comment_files))
                                                        <a target="_blank" href="{{ $_comment_files }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_files }}" class="set-custom-img" ></a>
                                                    @else
                                                        <a target="_blank" href="{{ $_comment_files }}" style="height:15px">
                                                            {{ $urlName }}
                                                        </a>
                                                    @endif
                                                </div>
                                        @endforeach
                                    @endif
                                @endif
                                @if((!$comment_files && $item->url_fileupload) || (!$comment_files && $item->url_fileupload))
                                    @php 
                                        $urlDecode = json_decode($item->url_fileupload);
                                    @endphp
                                    @if($urlDecode != null)
                                        @foreach($urlDecode as $_urlDecode)
                                        @php 
                                            $arrUrl = explode("/", $_urlDecode);
                                            $urlName = end($arrUrl);
                                            
                                        @endphp
                                        <div class="comment-content-file-item">
                                            
                                            @if (\app\Commons\Helper::check_file_type_is_image($_urlDecode))
                                                <a target="_blank" href="{{ url('/').$_urlDecode }}" style="height:15px;display: inline-flex;"><img src="{{ url('/').$_urlDecode }}" class="set-custom-img" ></a>
                                            @else
                                                <a class="download" href="{{ route('admin.comments.download_file',['downloadfile'=> $_urlDecode]) }}" style="height:15px">
                                                    {{ $urlName }}
                                                </a>
                                            @endif
                                           
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="comment-content-file-item">
                                            @if (\app\Commons\Helper::check_file_type_is_image($item->url_fileupload))
                                                <a target="_blank" href="{{ url('/').$item->url_fileupload }}" style="height:15px;display: inline-flex;"><img src="{{ url('/').$item->url_fileupload }}" class="set-custom-img" ></a>
                                            @else
                                                <a class="download" href="{{ route('admin.comments.download_file',['downloadfile'=> $item->url_fileupload]) }}" style="height:15px"  >
                                                    {{ $item->name_fileupload }}
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                                <div class="comment-content">{!! $item->content !!}</div>
                            </div><!-- /.comment-body -->

                            <div class="comment-info">
                                <a class="text-muted btn-comment-status" href="javascript:;" data-id="{{ $item->id }}" data-status="{{ $item->status }}">{{ $item->status ? 'Bỏ duyệt' : 'Duyệt' }}</a>
                                &middot;

                                <a class="text-muted btn-comment-delete" href="javascript:;" data-id="{{ $item->id }}">Xóa</a>
                                &middot;
                                <span class="text-muted">{{ $item->created_at->diffForHumans($now) }}</span>
                            </div><!-- /.comment-info -->
                            <div class="comment-reply">
                                @php
                                     $list_comment = $item->comments->where('post_id',$item->post_id);
                                @endphp
                                @foreach ($list_comment as $key => $reply)
                                    @php
                                    if($reply->new == 1){
                                        $user_info_2 = App\Models\PublicUser\V2\UserInfo::where('user_id',$reply->user_id)->first();
                                    }else{
                                        $user_info_2 = App\Models\PublicUser\UserInfo::find($reply->user_id);
                                    }
                                    $comment_1_images =$reply->files ? json_decode($reply->files) : null;
                                    $comment_1_files =$reply->files ? json_decode($reply->files) : null;
                                    @endphp
                                    <div class="box-comment" id="comment-{{ $reply->id }}">
                                        <div class="img-user img-circle img-sm" style="background: {{ $colors[$reply->user_id % 7] }}">
                                            @if($user_info_2)
                                                @if($user_info_2->avatar)
                                                    <img src="{{$user_info_2->avatar}}" alt="{{ @$user_info_2->display_name??@$user_info_2->full_name?? 'không rõ'}}" style="border-radius: 50%;">
                                                @else
                                                    @php
                                                        $words = explode(' ', @$user_info_2->display_name??@$user_info_2->full_name?? 'không rõ');
                                                        $name = end($words);
                                                        $char = substr($name, 0, 1);
                                                    @endphp
                                                    <strong>{{ strtoupper($char) }}</strong>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="comment-text">
                                            <div class="comment-body {{ $reply->status ? '' : 'bg-danger' }}">
                                                <span class="username">{{ @$user_info_2->display_name??@$user_info_2->full_name?? 'không rõ' }}</span>
                                                @if($comment_1_images && count($comment_1_images->images) > 0)
                                                    @php
                                                        $comment_1_images= $comment_1_images->images;
                                                    @endphp
                                                    @if($comment_1_images)
                                                        @foreach($comment_1_images as $_comment_1_images)
                                                                @php 
                                                                    $arrUrl = explode("/", $_comment_1_images);
                                                                    $urlName = end($arrUrl);
                                                                @endphp
                                                                <div class="comment-content-file-item">
                                                                    @if (\app\Commons\Helper::check_file_type_is_image($_comment_1_images))
                                                                        <a target="_blank" href="{{ $_comment_1_images }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_1_images }}" class="set-custom-img" ></a>
                                                                    @else
                                                                        <a class="download" href="{{ $_comment_1_images }}" style="height:15px">
                                                                            {{ $urlName }}
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                        @endforeach
                                                    @endif
                                                @endif
                                                @if($comment_1_files && count($comment_1_files->files) > 0)
                                                    @php
                                                        $comment_1_files = $comment_1_files->files;
                                                    @endphp
                                                    @if($comment_1_files)
                                                        @foreach($comment_1_files as $_comment_1_files)
                                                                @php 
                                                                    $arrUrl = explode("/", $_comment_1_files);
                                                                    $urlName = end($arrUrl);
                                                                @endphp
                                                                <div class="comment-content-file-item">
                                                                    @if (\app\Commons\Helper::check_file_type_is_image($_comment_1_files))
                                                                        <a target="_blank" href="{{ $_comment_1_files }}" style="height:15px;display: inline-flex;"><img src="{{ $_comment_1_files }}" class="set-custom-img" ></a>
                                                                    @else
                                                                        <a target="_blank" href="{{ $_comment_1_files }}" style="height:15px">
                                                                            {{ $urlName }}
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                        @endforeach
                                                    @endif
                                                @endif
                                                @if((!$comment_1_files && $reply->url_fileupload) || (!$comment_1_images && $reply->url_fileupload))
                                                    @php 
                                                        $urlDecode = json_decode($reply->url_fileupload);
                                                    @endphp
                                                    @if($urlDecode != null)
                                                        @foreach($urlDecode as $_urlDecode)
                                                            @php 
                                                                $arrUrl = explode("/", $_urlDecode);
                                                                $urlName = end($arrUrl);
                                                            @endphp
                                                            <div class="comment-content-file-reply">
                                                                @if (\app\Commons\Helper::check_file_type_is_image($_urlDecode))
                                                                    <a target="_blank" href="{{ url('/').$_urlDecode }}" style="height:15px"><img src="{{ url('/').$_urlDecode }}" class="set-custom-img" ></a>
                                                                @else
                                                                    <a class="download" href="{{ route('admin.comments.download_file',['downloadfile'=> $_urlDecode]) }}" style="height:15px">
                                                                        {{ $urlName }}
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="comment-content-file-reply">
                                                            @if (\app\Commons\Helper::check_file_type_is_image($item->url_fileupload))
                                                                <a target="_blank" href="{{ url('/').$item->url_fileupload }}" style="height:15px;display: inline-flex;"><img src="{{ url('/').$item->url_fileupload }}" class="set-custom-img"></a>
                                                            @else
                                                                <a class="download" href="{{ route('admin.comments.download_file',['downloadfile'=> $item->url_fileupload]) }}" style="height:15px"  >
                                                                    {{ $item->name_fileupload }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                                <div class="comment-content">{!! nl2br($reply->content) !!}</div>
                                            </div>
                                            <div class="comment-info">
                                                <a class="text-muted btn-comment-status" href="javascript:;" data-id="{{ $reply->id }}" data-status="{{ $reply->status }}">{{ $reply->status ? 'Bỏ duyệt' : 'Duyệt' }}</a>
                                                &middot;
                                                <a class="text-muted btn-comment-delete" href="javascript:;" data-id="{{ $reply->id }}">Xóa</a>
                                                &middot;
                                                <span class="text-muted">{{ $reply->created_at->diffForHumans($now) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div><!-- /.comment-reply -->
                            @if( in_array('admin.comments.save',@$user_access_router))
                                @if($feedback->status != 1)
                                    <div class="comment-form" id="reply-{{ $item->id }}">
                                        <div class="attach-file" style="display: flex">
                                            <div id="fileName-{{ @$keyitem }}"> </div> 
                                            
                                            <i id="iconRemoveFile-{{ @$keyitem }}" class="fa fa-remove" style="display: none;margin-left: 5px;"></i>
                                        </div>
                                        <img class="img-responsive img-circle img-sm" src="/adminLTE/img/user-default.png" alt="Alt Text">
                                        <div class="img-push" style="position: relative;">
                                            <textarea data-textarea_id="{{@$keyitem}}" data-post_id="{{ $feedback->id }}" data-type="feedback" data-parent_id="{{ $item->id }}" data-action="reply" class="form-control input-comment input-auto-height" rows="1" placeholder="Viết bình luận"></textarea>
                                             <label
                                                style="background-color: #3c8dbc; right: 10px; top: 2px; position: absolute; display: flex; align-items: center;justify-content: center;"class="img-responsive img-circle img-sm"  >
                                            <i class="fa fa-files-o" style="font-size: large;"></i>
                                            <input id='inputFile-{{ @$keyitem }}' type="file" style="display: none;" data-input='{{@$keyitem}}' />
                                            </label>
                                        </div>
                                    </div><!-- /.comment-form -->
                                @endif
                            @endif
                        </div><!-- /.comment-text -->
                    </div><!-- /.box-comment -->
                    @endforeach
                </div>
                @if( in_array('admin.comments.save',@$user_access_router))
                    @if($feedback->status != 1)
                        <div class="box-footer">
                            <div class="attach-file" style="display: flex">
                                <div id="fileName"> </div> 

                                <i id="iconRemoveFile" class="fa fa-remove" style="display: none;margin-left: 5px;"></i>
                            </div>
                            <form id="reply" action="#" method="post">
                                
                                <img class="img-responsive img-circle img-sm" src="/adminLTE/img/user-default.png" alt="Alt Text">
                                <!-- .img-push is used to add margin to elements next to floating images -->
                                <div class="img-push" style="position: relative;">
                                    <textarea data-post_id="{{ $feedback->id }}" data-type="feedback" data-parent_id="0" data-action="comment" class="form-control input-comment input-auto-height" rows="1" placeholder="Viết bình luận"></textarea >
                                    <label  style="background-color: #3c8dbc; right: 10px; top: 2px; position: absolute; display: flex; align-items: center;justify-content: center;" class="img-responsive img-circle img-sm"  >
                                            <i class="fa fa-files-o" style="font-size: large;"></i>
                                            <input id='inputFile' type="file" style="display: none;"/>
                                   </label>
                                </div>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>
        <div class="col-sm-4">
            <div class="box box-primary">
                <div class="box-body">
                    <h4>Comments mới nhất</h4>
                    <ul class="listcomments">
                        @foreach($listComments as $lc)
                            @php
                            if($lc->new == 1){
                                $user_info_3 = App\Models\PublicUser\V2\UserInfo::where('user_id',$lc->user_id)->first();
                            }else{
                                $user_info_3 = App\Models\PublicUser\UserInfo::find($lc->user_id);
                            }
                            @endphp
                            <li>
                                <div class="img-user img-circle img-sm" style="background: {{ $colors[$lc->user_id % 7] }};line-height: 0px !important;">
                                    @if($user_info_3)
                                        @if($user_info_3->avatar)
                                            <img src="{{$user_info_3->avatar}}" alt="{{@$user_info_3->display_name??@$user_info_3->full_name?? 'không rõ' }}" style="border-radius: 50%;width: 30px;height: 30px;">
                                        @else
                                            @php
                                                $words = explode(' ', @$user_info_3->display_name??@$user_info_3->full_name?? 'không rõ');
                                                $name = end($words);
                                                $char = substr($name, 0, 1);
                                            @endphp
                                            <strong>{{ strtoupper($char) }}</strong>
                                        @endif
                                    @endif
                                </div>
                                <div class="info_comment" style="width: calc(100% - 30px);float: left;padding-left: 5px;">
                                    <p>{{ @$user_info_3->display_name??@$user_info_3->full_name?? 'không rõ' }}</p>
                                    <p>{{$lc->content}}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

</section>
@endsection

@section('stylesheet')

<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="/adminLTE/plugins/lightbox/ekko-lightbox.css" />

@endsection

@section('javascript')

@include('backend.feedback.js-comment')

<script src="/adminLTE/plugins/lightbox/ekko-lightbox.min.js"></script>
<script>
    $(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });
    $(document).ready(function(){
        $('#inputFile').change(function(e){
            let fileName = e.target.value.split(/(\\|\/)/g).pop();
            $('#fileName').text(fileName); 
            if(fileName){
                $('#iconRemoveFile').show().css("color", "red");
                $('#fileName').css("margin-left", "40px"); 
            }
        })
        $("#iconRemoveFile").click(function() {
            $('#fileName').text(''); 
            $("#inputFile").val(null);
            $(this).hide()
        });
        $('.comment-form input').change(function(e){
             var key_input = $(this).attr('data-input');
             var file_name_comment= $('#inputFile-'+key_input).prop('files')[0].name;
             $('#fileName-'+key_input).text(file_name_comment); 
             if(file_name_comment){
                $('#iconRemoveFile-'+key_input).show().css("color", "red");
                $('#fileName-'+key_input).css("margin-left", "40px"); 
            }
            $('#iconRemoveFile-'+key_input).click(function() {
            $('#fileName-'+key_input).text(''); 
            $('#inputFile-'+key_input).val(null);
            $(this).hide()
        });
                
        })
    });
     $("#btn-set-change-status").on('click', function () {
        var _this = $(this);
        var id = _this.data('id');
        var url = _this.data('url');
        var _token = $('meta[name="csrf-token"]').attr('content');
        var data = {
            _token: _token,
            method: 'status',
            status: 1,
            ids: [id]
        };
        showLoading();
        $.post(url, data, function (json) {
            hideLoading(); 
            $(".tag_status").html("<span>Đã hoàn thành</span>");
            _this.remove();
        });
    });
</script>

<script>
    sidebar('feedback');
</script>

@endsection