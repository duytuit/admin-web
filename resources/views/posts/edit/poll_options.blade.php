<div role="tabpanel" class="tab-pane" id="poll_options">
    <form action="" method="POST" id="form-save-poll-option">
        @csrf
        <input type="hidden" name="poll_option_ids" value="" />
        <div class="panel">
            <div class="panel-heading">
                <h4 class="control-label">
                    Thêm câu hỏi
                    @can('update', app(App\Models\PollOption::class))
                    <a href="javacript:;" class="btn btn-sm btn-social-icon btn-dropbox" data-toggle="modal" data-target="#modal-add-option"><i class="fa fa-plus"></i></a>
                    @endcan
                </h4>
                <span style="color: #c5b8b8;"><i>(Bạn có thể chọn câu hỏi hoặc nhấn nút (+) để thêm mới)</i></span>

                <div class="input-group">
                    <select class="form-control" name="poll_options" id="select-poll-option" style="width: 100%;">
                        <option value="">Chọn câu hỏi bình chọn</option>
                    </select>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-success btnSubmitAddOption" title="Cập nhật">Thêm câu hỏi</button>
                    </div>
                </div>

                <br />
                <div class="alert alert-danger print-error-msg" style="display:none">
                    <ul></ul>
                </div>
                <div class="alert alert-success print-success-msg" style="display:none">
                    <ul></ul>
                </div>
            </div>
            <hr />

            <div class="panel-body" id="view-poll-option-js">
                <div class="form-group">
                    <label class="control-label">Danh sách câu hỏi bình chọn</label>
                </div>

                @if($poll_options)
                @foreach($poll_options as $poll_option)
                @php
                $options = isset($poll_option->options) && is_array($poll_option->options) ? $poll_option->options : [];
                @endphp
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#poll-option-{{ $poll_option->id }}">{{ $poll_option->updated_at->format('d-m-Y H:i:s') }}</a>
                            <a href="{{ route('admin.posts.delete.option', ['id'=> $id, 'poll_id' => $poll_option->id]) }}" class="pull-right text-danger" title="Xóa câu hỏi" style="font-size: 20px; margin-left: 7px;"><i class="fa fa-remove"></i></a>
                            <a href="{{ route('admin.polloptions.edit', ['id' => $poll_option->id]) }}" class="pull-right" title="Sửa câu hỏi" style="font-size: 20px; margin-left: 7px;"><i class="fa fa-edit"></i></a>
                            {{-- <i href="{{ route('admin.posts.registers', ['id' => $id, 'type' => $type]) }}" class="pull-right" title="Kết quả bình chọn" target="_blank" style="font-size: 20px; margin-left: 5px;"><i class="fa fa-gavel"></i></i> --}}
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
                @endforeach
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Modal thêm câu hỏi --}}
<div id="modal-add-option" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        <form action="{{ route('admin.posts.save.option') }}" method="post" id="form-save-poll-option" class="form-validate">
            @csrf

            <input type="hidden" name="hashtag">
            <input type="hidden" name="post_id" value="{{ $id }}">

            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Thêm câu hỏi</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger poll-error-msg" style="display:none">
                        <ul></ul>
                    </div>
                    <div class="alert alert-success poll-success-msg" style="display:none">
                        <ul></ul>
                    </div>

                    <input id="poll_index" type="hidden" value="0">

                    <div class="form-group {{ $errors->has('title') ? 'has-error': '' }}">
                        <label class="control-label">Câu hỏi thăm dò <span class="text-danger">*</span></label>
                        <textarea name="poll_title" placeholder="Câu hỏi" rows="1" class="form-control input-text"></textarea>
                        @if ($errors->has('title'))
                        <em class="help-block">{{ $errors->first('title') }}</em>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="control-label">Câu trả lời</label>

                        @if ($errors->has('options'))
                        <em class="help-block">{{ $errors->first('options') }}</em>
                        @endif

                        <div class="poll_options">
                            <div class="input-group">
                                <span class="input-group-addon btn-handle"><i class="fa fa-arrows"></i></span>
                                <input type="text" name="options[]" value="" class="form-control" placeholder="Câu trả lời">
                                <span class="input-group-btn">
                                    <button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-success btn-add-option"><i class="fa fa-plus"></i> Thêm trả lời</button>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-xs-12">Số câu trả lời tối đa</label>
                        <div class="col-xs-4">
                            <input class="form-control" type="number" name="maximum" value="1" min="1" max="1" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <button class="btn btn-primary btn-save-option" style="margin-right: 5px;"><i class="fa fa-upload"></i>&nbsp;&nbsp;Thêm câu hỏi</button>
                    <input type="submit" class="btn-submit-file hidden" />
                </div>
            </div>
        </form>
    </div>
</div>