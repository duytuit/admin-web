@php
$options = isset($poll_option->options) && is_array($poll_option->options) ? $poll_option->options : [];
@endphp
<div class="panel panel-primary">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" href="#poll-option-{{ $poll_option->id }}">{{ $poll_option->updated_at->format('d-m-Y H:i:s') }}</a>
            <a href="{{ route('admin.polloptions.edit', ['id' => $poll_option->id]) }}" class="pull-right" title="Sửa câu hỏi" style="font-size: 20px; margin-left: 7px;"><i class="fa fa-edit"></i></a>
        </h4>
    </div>
    <div id="poll-option-{{ $poll_option->id }}" class="panel-collapse collapse in">
        <div class="form-horizontal">
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label" style="padding-top: 0px;">Câu hỏi</label>
                    <div class="col-sm-10">
                        {{ $poll_option->title }}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="padding-top: 0px;">Câu trả lời</label>
                    <div class="col-sm-10">
                        <ol>
                            @foreach($options as $item)
                            <li> {{ $item }} </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>