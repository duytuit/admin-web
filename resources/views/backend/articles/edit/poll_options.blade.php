<div role="tabpanel" class="tab-pane" id="poll_options">
    @php
    $poll_options = old('poll_options', $article->poll_options);
    $options = isset($poll_options['options']) && is_array($poll_options['options']) ? $poll_options['options'] : [];
    @endphp

    <input id="poll_index" type="hidden" value="{{ count($options) }}">

    <div class="form-group">
        <label class="control-label">Câu hỏi thăm dò</label>
        <textarea name="poll_options[title]" placeholder="Câu hỏi" rows="1" class="form-control input-text">{{ $poll_options['title'] ?? '' }}</textarea>
    </div>
    <div class="form-group">
        <label class="control-label">Câu trả lời</label>
        @if (empty($options))
        <div class="poll_options">
            <div class="input-group">
                <span class="input-group-addon btn-handle"><i class="fa fa-arrows"></i></span>
                <input type="text" name="poll_options[options][0]" value="" class="form-control" placeholder="Câu trả lời">
                <span class="input-group-btn">
                    <button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>
                </span>
            </div>
        </div>
        @else
        <div class="poll_options">
            @foreach($options as $item)
            <div class="input-group">
                <span class="input-group-addon btn-handle"><i class="fa fa-arrows"></i></span>
                <input type="text" name="poll_options[options][{{ $loop->index }}]" value="{{ $item }}" class="form-control" placeholder="Câu trả lời">
                <span class="input-group-btn">
                    <button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    <div class="form-group">
        <button type="button" class="btn btn-success btn-add-option"><i class="fa fa-plus"></i> Thêm
            trả lời</button>
        <button type="button" class="btn btn-warning btn-poll-result"><i class="fa fa-eye"></i> KQ Bình chọn</button>
    </div>
</div>