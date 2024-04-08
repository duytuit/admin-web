<div role="tabpanel" class="tab-pane active" id="general">
    <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
        <label class="control-label required">Tiêu đề</label>
        <textarea name="title" placeholder="Tiêu đề" rows="1" class="form-control input-text" required>{{ old('title', $article->title) }}</textarea>
        @if ($errors->has('title'))
        <em class="help-block">{{ $errors->first('title') }}</em>
        @endif
    </div>
    <div class="form-group">
        <label class="control-label">Đường dẫn</label>
        <textarea name="alias" placeholder="Đường dẫn" rows="1" class="form-control input-text">{{ old('alias', $article->alias) }}</textarea>
    </div>
    <div class="form-group">
        <label class="control-label">Mô tả ngắn</label>
        <textarea name="summary" placeholder="Mô tả ngắn" rows="3" class="form-control miniEditor">{{ old('summary', $article->summary) }}</textarea>
    </div>
    <div class="form-group">
        <label class="control-label">Nội dung</label>
        <textarea name="content" placeholder="Nội dung" rows="10" class="form-control mceEditor">{{ old('content', $article->content) }}</textarea>
    </div>
</div><!-- END #general -->