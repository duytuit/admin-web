@extends('backend.layouts.master')
@section('content')

    <section class="content-header">
        <h1>
            Chiến dịch
            <small>Cập nhật</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
            <li class="active">Sửa câu hỏi bài viết</li>
        </ol>
    </section>

    <section class="content">
        @if( in_array('admin.polloptions.save',@$user_access_router))
            <form action='{{ route('admin.polloptions.save', ['id' => $id]) }}' method="post" id="form-edit-add-poll" class="form-validate" autocomplete="off">
                @csrf

                <div class="row">
                    <div class="col-xs-8">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div role="tabpanel" class="tab-pane" id="poll_options">
                                    @php
                                        $options = isset($poll_option->options) && is_array($poll_option->options) ? $poll_option->options : [];
                                    @endphp

                                    <input id="poll_index" type="hidden" value="{{ count($options) }}">

                                    <div class="form-group {{ $errors->has('title') ? 'has-error': '' }}">
                                        <label class="control-label">Câu hỏi thăm dò <span class="text-danger">*</span></label>
                                        <textarea name="title" placeholder="Câu hỏi" rows="1" class="form-control input-text">{{ $poll_option->title ?? '' }}</textarea>
                                        @if ($errors->has('title'))
                                            <em class="help-block">{{ $errors->first('title') }}</em>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Câu trả lời</label>

                                        @if ($errors->has('options'))
                                            <em class="help-block">{{ $errors->first('options') }}</em>
                                        @endif

                                        @if (empty($options))
                                            <div class="poll_options">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-arrows"></i></span>
                                                    <input type="text" name="options[]" value="" class="form-control" placeholder="Câu trả lời">
                                                    <span class="input-group-btn">
                                                <button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>
                                            </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="poll_options">
                                                @foreach($options as $item)
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="fa fa-arrows"></i></span>
                                                        <input type="text" name="options[]" value="{{ $item }}" class="form-control" placeholder="Câu trả lời">
                                                        <span class="input-group-btn">
                                                <button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>
                                            </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-success btn-add-option"><i class="fa fa-plus"></i> Thêm trả lời</button>
                                    </div>

                                    <div class="form-group row {{ $errors->has('maximum') ? 'has-error': '' }}">
                                        <label class="control-label col-xs-12">Số câu trả lời tối đa</label>
                                        <div class="col-xs-4">
                                            <input class="form-control" type="number" name="maximum" value="{{ old('maximum') ?? $poll_option->maximum ?? 1 }}" min="1" max="{{ $options ? count($options) : 1 }}" />
                                            @if ($errors->has('maximum'))
                                                <em class="help-block">{{ $errors->first('maximum') }}</em>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('post_id') ? 'has-error': '' }}">
                                        <label class="control-label col-xs-12">Bài viết liên quan</label>
                                        <div class="col-xs-12">
                                            <select class="form-control" name="post_id" id="select-post-option" style="width: 100%;">
                                                <option value="">Chọn bài viết liên quan</option>
                                                @if($poll_option->post_id)
                                                    <option value="{{$poll_option->post_id}}" selected="selected">{{$post[0]->title}}</option>
                                                @endif

                                            </select>
                                            @if ($errors->has('post_id'))
                                                <em class="help-block">{{ $errors->first('post_id') }}</em>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-poll">
                                    <i class="fa fa-save"></i>&nbsp;&nbsp;{{  $id ? 'Cập nhật' : 'Thêm mới'}}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-4">
                    </div>
                </div>
            </form>
        @endif
    </section>

@endsection

@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>

    @include('backend.poll_options.js-poll_options')

    <script>
        var id = '{{ $id }}';
        if(id){
            sidebar('poll-options', 'index');
        } else {
            sidebar('poll-options', 'create');
        }
        get_data_select_user({
            object: '#select-post-option',
            data_id: 'id',
            data_text1: 'title',
            title_default: 'Chọn bài viết liên quan'
        });

        function get_data_select_user(options) {
            $(options.object).select2({
                ajax: {
                    url: "{{ route("admin.polloptions.getAllPosts") }}",
                    dataType: 'json',
                    data: function(params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function(json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text1]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                }
            });
        }
    </script>
@endsection