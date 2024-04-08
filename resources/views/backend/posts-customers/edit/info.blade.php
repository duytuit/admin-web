@php
    $user = \Auth::user();
    $per_update = $type. '.update';
    if($type == 'article'){
        $route = 'admin.posts.index';
    }elseif($type == 'event'){
        $route = 'admin.posts.index_event';
    }
@endphp

<div class="box box-primary">
    <div class="box-header with-border">
        Thông tin
    </div>
    <div class="box-body">
        <div class="form-group {{ $errors->has('category_id') ? 'has-error' : '' }}">
            <label class="control-label required">Danh mục</label>
            <select name="category_id" class="form-control" required>
                @php $category_id = old('category_id', $post->category_id); @endphp
                @foreach ($categories as $item)
                    <option value="{{ $item->id }}" {{ $item->id == $category_id ? 'selected' : '' }}>{{ $item->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group {{ $errors->has('category_id') ? 'has-error' : '' }}">
            <label class="control-label required">Bộ phận</label>
            <select name="category_id" class="form-control" required>
                @php $category_id = old('category_id', $post->category_id); @endphp
                @foreach ($categories as $item)
                    <option value="{{ $item->id }}" {{ $item->id == $category_id ? 'selected' : '' }}>{{ $item->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="control-label">Ảnh</label>
            <div class="input-group input-image" data-file="image">
                <input type="text" name="image" value="{{ old('image', $post->image) }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
            </div>
            @if (old('image', $post->image))
                <img src="{{ old('image', $post->image) }}" alt="" style="max-width: 200px;" />
            @endif
        </div>
        <div class="form-group">
            <label class="control-label">Hashtag</label>
            <textarea name="hashtag" placeholder="Hashtag" rows="1" class="form-control input-text">{{ old('hashtag', $post->hashtag) }}</textarea>
        </div>
        <div class="form-group">
            <label class="control-label">Link Video</label>
            <textarea name="url_video" placeholder="link video" rows="1" class="form-control input-text">{{ old('url_video', $post->url_video) }}</textarea>
        </div>
        <div class="form-group">
            <label class="control-label">Hẹn giờ</label>
            <div class="input-group datetimepicker">
                <input type="text" name="publish_at" value="{{ old('publish_at', $post->publish_at ?? $now) }}" class="form-control" placeholder="Hẹn giờ">
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
        </div>
        @if ($type == 'voucher')
            <div class="form-group">
                <label class="control-label">Loại hiển thị</label>
                <div class="notify-group">
                    @php
                        $kind = $post->kind?$post->kind:'normal';
                    @endphp
                    <label class="control-label">
                        <input type="radio" name="kind" value="normal" class="iCheck" {{ $kind == 'normal' ? 'checked' : '' }}>
                        Tin thường
                    </label>
                    <label class="control-label">
                        <input type="radio" name="kind" value="pin" class="iCheck" {{ $kind == 'pin' ? 'checked' : '' }}>
                        Tin ghim
                    </label>
                    <label class="control-label">
                        <input type="radio" name="kind" value="slide" class="iCheck" {{ $kind == 'slide' ? 'checked' : '' }}>
                        Tin slide
                    </label>
                </div>
            </div>
        @endif

        @if ($type == 'voucher')
            @include('backend.posts.edit.partner')
        @endif

        @include('backend.posts.edit.notify')

        <div class="form-group">
            <label class="control-label">Trạng thái</label>
            <div>
                @php
                    $status = $old ? old('status') : $post->status;
                @endphp
                <label class="switch">
                    <input type="checkbox" name="status" value="1" {{ $status ? 'checked' : '' }} />
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-success" form="form-posts" value="submit">{{ $id ? 'Cập nhật' : 'Thêm mới' }}</button>
            &nbsp;  @if(in_array($route,@$user_access_router))
                <a href="{{ route($route) }}" class="btn btn-danger" form="form-posts" value="submit">Quay lại</a>
            @endif
        </div>
    </div>
</div>