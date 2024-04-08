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

@can('view', app(App\Models\Feedback::class))
<section class="content">
    <div class="row">
        <div class="col-sm-8">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="form-group">
                        <h4 class="feedback-title">[ {{ $types[$feedback->type] ?? '' }} ] {{ $feedback->title }}</h4>
                        <div class="feedback-content">{{ $feedback->content }}</div>
                    </div>
                    <div class="form-group">
                        <strong class="feedback-customer text-muted">{{ $feedback->customer ? $feedback->customer->name : '' }}</strong>
                        &middot;
                        <span class="feedback-created_at text-muted">{{ $feedback->created_at->diffForHumans($now) }}</span>
                        &middot;
                        @php
                        $rating = (int)$feedback->rating;
                        $empty = 5 - $rating;
                        @endphp
                        <span class="feedback-rating rating">
                            @for($i=1; $i<=$rating; $i++) <i class="fa fa-star"></i> @endfor
                                @for($i=1; $i<=$empty; $i++) <i class="fa fa-star-o"></i> @endfor
                        </span>
                    </div>
                    @php
                    $images = $feedback->attached['images'] ?? [];
                    $files = $feedback->attached['files'] ?? [];
                    @endphp
                    @if ($images)
                    <div class="form-group">
                        <label>Ảnh đính kèm</label>
                        <div class="row attached-images space-5">
                            @foreach ($images as $src)
                            @php $thumb = '/thumb/150x120/' . trim($src, '/'); @endphp
                            <div class="col-sm-2 col-xs-6">
                                <a href="{{ $src }}" data-toggle="lightbox" data-gallery="attached-images-gallery">
                                    <img class="img-responsive" src="{{ $thumb }}" alt="image" width="150" height="100">
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
                <div class="box-footer box-comments">
                    @foreach ($feedback->comments as $item)
                    <div class="box-comment" id="comment-{{ $item->id }}">
                        <!-- User image -->
                        <div class="img-user img-circle img-sm" style="background: {{ $colors[$item->user_id % 7] }}">
                            @php
                            $words = explode(' ', $item->user->name);
                            $name = end($words);
                            $char = substr($name, 0, 1);
                            @endphp
                            <strong>{{ strtoupper($char) }}</strong>
                        </div>
                        <div class="comment-text">
                            <div class="comment-body {{ $item->status ? '' : 'bg-danger' }}">
                                <span class="username">{{ $item->user->name }}</span>
                                <div class="comment-content">{!! $item->content !!}</div>
                            </div><!-- /.comment-body -->

                            <div class="comment-info">
                                @can('reply', app(App\Models\Feedback::class))
                                <a class="text-muted btn-reply" href="javascript:;" data-target="#reply-{{ $item->id }}">Trả lời</a>
                                &middot;
                                @endcan

                                @can('approve', app(App\Models\Feedback::class))
                                <a class="text-muted btn-comment-status" href="javascript:;" data-id="{{ $item->id }}" data-status="{{ $item->status }}">{{ $item->status ? 'Bỏ duyệt' : 'Duyệt' }}</a>
                                &middot;
                                @endcan

                                @can('delete', app(App\Models\Feedback::class))
                                <a class="text-muted btn-comment-delete" href="javascript:;" data-id="{{ $item->id }}">Xóa</a>
                                &middot;
                                @endcan
                                <span class="text-muted">{{ $item->created_at->diffForHumans($now) }}</span>
                            </div><!-- /.comment-info -->
                        </div><!-- /.comment-text -->
                    </div><!-- /.box-comment -->
                    @endforeach
                </div>
                <div class="box-footer">
                    @can('reply', app(App\Models\Feedback::class))
                    <form id="reply" action="#" method="post">
                        <img class="img-responsive img-circle img-sm" src="/adminLTE/img/user-default.png" alt="Alt Text">
                        <!-- .img-push is used to add margin to elements next to floating images -->
                        <div class="img-push">
                            <textarea data-post_id="{{ $feedback->id }}" data-type="feedback" data-parent_id="0" data-action="comment" class="form-control input-comment input-auto-height" rows="1" placeholder="Viết bình luận"></textarea>
                        </div>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-sm-4"></div>
    </div>

</section>
@endcan
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
</script>

<script>
    sidebar('feedback');
</script>

@endsection