<div class="box box-primary">
    <div class="box-header with-border">
        Thông tin
    </div>
    <div class="box-body">
        <div class="form-group {{ $errors->has('category_id') ? 'has-error' : '' }}">
            <label class="control-label required">Danh mục</label>
            <select name="category_id" class="form-control" required>
                @php $category_id = old('category_id', $article->category_id); @endphp
                @foreach ($categories as $item)
                <option value="{{ $item->id }}" {{ $item->id == $category_id ? 'selected' : '' }}>{{ $item->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="control-label">Ảnh</label>
            <div class="input-group input-image" data-file="image">
                <input type="text" name="image" value="{{ old('image', $article->image) }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
            </div>
            @if (old('image', $article->image))
            <img src="{{ old('image', $article->image) }}" alt="" style="max-width: 200px;" />
            @endif
        </div>
        <div class="form-group">
            <label class="control-label">Hashtag</label>
            <textarea name="hashtag" placeholder="Hashtag" rows="1" class="form-control input-text">{{ old('hashtag', $article->hashtag) }}</textarea>
        </div>
        <div class="form-group">
            <label class="control-label">Hẹn giờ</label>
            <div class="input-group datetimepicker">
                <input type="text" name="publish_at" value="{{ old('publish_at', $article->publish_at ?? $now) }}" class="form-control" placeholder="Hẹn giờ">
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
        </div>

        @if ($type == 'voucher')
        @include('backend.articles.edit.partner')
        @endif

        @include('backend.articles.edit.notify')

        <div class="form-group">
            <label class="control-label">Trạng thái</label>
            <div>
                @php
                $status = ($id == 0) || ($old ? old('status') : $article->status);
                @endphp
                <label class="switch">
                    <input type="checkbox" name="status" value="1" {{ $status ? 'checked' : '' }} />
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-success" form="form-articles" value="submit">{{ $id ? 'Cập nhật' : 'Thêm mới' }}</button>
            &nbsp;
            <a href="{{ route('admin.articles.index', ['type' => $type]) }}" class="btn btn-danger" form="form-articles" value="submit">Quay lại</a>
        </div>
    </div>
</div>