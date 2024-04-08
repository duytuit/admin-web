@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Nhóm menu
        <small>Cập nhật</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

<section class="content">
    @php
    $old = old();
    @endphp

    <form action="{{ route('admin.group_menus.update', ['id' => $id]) }}" method="post" id="form-edit-group-menu" class="form-validate">
        @csrf
        <div class="row">
            <div class="col-sm-8 col-xs-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('title') ? 'has-error': '' }}">
                                <label class="control-label">Tên nhóm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" placeholder="Tên nhóm menu" value="{{ $group_menu->title ?? old('title') ?? '' }}" />
                                @if ($errors->has('title'))
                                <em class="help-block">{{ $errors->first('title') }}</em>
                                @endif
                            </div>
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('app_id') ? 'has-error': '' }}">
                                <label class="control-label">App<span class="text-danger">*</span></label>
                                <select id="select_app_project" class="form-control" name="app_id" style="width: 100%">
                                    <option value="">Chọn app</option>
                                    @if(!empty($group_menu) && ($group_menu->app_id || old('app_id')) )
                                    @php
                                    $app_id = old('app_id') ?? $group_menu->app_id ?? '' ;
                                    $app = \App\Models\AppProject::find($app_id);
                                    @endphp
                                    <option value="{{ $app->id }}" selected="">{{ $app->name }}</option>
                                    @endif
                                </select>
                                @if ($errors->has('app_id'))
                                <em class="help-block">{{ $errors->first('app_id') }}</em>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label">Chọn menu cho app</label>
                            <div class="table-responsive">
                                @php
                                $menus = $old ? old('menu_ids', []) : ($group_menu->menu_ids ?? []);
                                @endphp
                                <table id="roles" class="table table-hover table-striped table-bordered tree">
                                    <thead>
                                        <tr class="bg-primary">
                                            <th>Danh sách menu</th>
                                            <th width="40">
                                                <input type="checkbox" class="role-all" data-target=".role-item">
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($parent_menu as $index => $menu)
                                        @php $checked_1 = in_array($menu->id, $menus) ? 'checked' : ''; @endphp
                                        <tr class="treegrid-{{ $menu->id }}">
                                            <td><strong>{{ $menu->title }}</strong></td>
                                            <td>
                                                <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}" class="role-item item-{{ $index }}" data-target=".group-{{ $index }}" {{ $checked_1 }} />
                                            </td>
                                        </tr>
                                        @foreach($item_menu as $key => $items)
                                        @if($key == $menu->id)
                                        @foreach ($items as $value)
                                        @php $checked_2 = in_array($value->id, $menus) ? 'checked' : ''; @endphp
                                        <tr class="treegrid-{{ $value->id }} treegrid-parent-{{ $menu->id }}">
                                            <td>{{ $value->title }}</td>
                                            <td>
                                            <input type="checkbox" name="menu_ids[]" value="{{ $value->id }}" class="role-group group-{{ $index }}" data-target=".permission-{{ $index }}-{{ $key }}" {{ $checked_2 }} />
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endif
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        @can('update', app(App\Models\Exchange::class))
                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-group-menu">
                            <i class="fa fa-save"></i>&nbsp;&nbsp;{{  $id ? 'Cập nhật' : 'Thêm mới'}}
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('javascript')

<script type="text/javascript" src="/adminLTE/plugins/treegrid/jquery.treegrid.js"></script>
<script type="text/javascript" src="/adminLTE/plugins/treegrid/jquery.treegrid.bootstrap3.js"></script>
<link rel="stylesheet" href="/adminLTE/plugins/treegrid/jquery.treegrid.css">
<script type="text/javascript">
    $('.tree').treegrid();
</script>

<script>
    $('#roles input').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });
    
    $('#roles .role-permission').on('ifToggled', function(e) {
        //all_checked();
    });
    
    $('#roles input[data-target]').on('ifToggled', function(e) {
        var $this = $(this);
        var target = $this.data('target');
        var $target = $(target);   

        if (this.checked) {
            $target.iCheck('check');
        } else {
            $target.iCheck('uncheck');
        }
            //all_checked();
    });
    
    // all_checked();
    
    // function all_checked() {
    //     parent_checked('.role-group');
    //     parent_checked('.role-item');
    //     parent_checked('.role-all');
    // }
    
    // function parent_checked(selector) {
    //     $(selector).each(function() {
    //         var $this = $(this);
    //         var target = $this.data('target');
    //         if ($(target).length == $(target + ':checked').length) {
    //             $this.iCheck('check');
    //         } else {
    //             $this.iCheck('uncheck');
    //         }
    //     });
    // }
</script>

<script>
    get_data_select2({
            object: '#select_app_project',
            url: '{{ route("admin.app_projects.get_apps") }}',
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn app project'
        });
    
        function get_data_select2(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
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
                                text: item[options.data_text]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }
</script>

<script>
    sidebar('menus', 'group');
</script>
@endsection