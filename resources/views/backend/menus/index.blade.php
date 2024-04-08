@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách Menu
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Menu</li>
    </ol>
</section>

<section class="content">
    <div class="box box-info">
        <div class="box-header with-border">
            <form action="" method="get" id="form-search">
                {{ csrf_field() }}
                @method('get')
                <div class="row">
                    <div class="col-md-8 col-sm-8 col-xs-12 ">
                        @can('update', app(App\Models\Setting::class))
                        <a href="javascript:;" data-toggle="modal" data-target="#myMenus" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm menu</a>
                        @endcan
                    </div>
                </div>
                <div class="clearfix" style="height: 15px;"></div>
            </form>
        </div>

        <!-- /.box-header -->
        <form action="{{ route('admin.menus.order') }}" method="post" id="form-edit-menu" class="form-validate">
            @csrf
            <div class="box-body">
                <div class="dd" id="nestable-menu">
                    <input type="hidden" name="order" value="" id="nestable-output" />

                    <ol class="dd-list">
                        @foreach ($parent_menu as $menu)
                        <li class="dd-item" data-id="{{ $menu->id }}">
                            <div class="pull-right item_actions">
                                <a href="javascript:;" class="btn btn-sm btn-danger pull-right delete btn-delete-menu" data-url="{{ route('admin.menus.delete') }}" data-id="{{ $menu->id }}">
                                    <i class="fa fa-trash"></i> Xóa
                                </a>
                                <a href="javascript:;" class="btn btn-sm btn-info pull-right edit btn-edit-menu" data-id="{{ $menu->id }}">
                                    <i class="fa fa-edit"></i> Sửa
                                </a>
                            </div>
                            <div class="dd-handle">
                                <span>{{ $menu->title }}</span>
                                <small class="url">{{ $menu->url  }}</small>
                            </div>
                            @foreach($item_menu as $key => $items)
                            @if($key == $menu->id)
                            <ol class="dd-list">
                                @foreach ($items as $value)
                                <li class="dd-item" data-id="{{ $value->id }}">
                                    <div class="pull-right item_actions">
                                        <a href="javascript:;" class="btn btn-sm btn-danger pull-right delete btn-delete-menu" data-url="{{ route('admin.menus.delete') }}" data-id="{{ $value->id }}">
                                            <i class="fa fa-trash"></i> Xóa
                                        </a>
                                        <a href="javascript:;" class="btn btn-sm btn-info pull-right edit btn-edit-menu" data-id="{{ $value->id }}">
                                            <i class="fa fa-edit"></i> Sửa
                                        </a>
                                    </div>
                                    <div class="dd-handle">
                                        <span>{{ $value->title }}</span>
                                        <small class="url">{{ $value->url }}</small>
                                    </div>
                                </li>
                                @endforeach
                            </ol>
                            @endif
                            @endforeach
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </form>
    </div>

    <div id="myMenus" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="{{ route('admin.menus.update') }}" method="post" id="form-add-menu" class="form-validate">
                @csrf
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Thêm menu</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="">

                        <div class="alert alert-danger menu-error-msg" style="display:none">
                            <ul></ul>
                        </div>
                        <div class="alert alert-success menu-success-msg" style="display:none">
                            <ul></ul>
                        </div>

                        <div class="form-group">
                            <label class="control-label">Tên menu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" placeholder="Tên menu" value="" />
                        </div>

                        <div class="form-group">
                            <label class="control-label">Url <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="url" placeholder="Url" value="" />
                        </div>

                        <div class="form-group">
                            <label class="control-label">Icon</label>
                            <input type="text" class="form-control" name="icon" placeholder="icon-class" value="" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                        <button type="submit" class="btn btn-primary btn-add-menu" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection

@section('javascript')
<script type="text/javascript" src="/adminLTE/plugins/nestable/jquery.nestable.js"></script>
<link rel="stylesheet" href="/adminLTE/plugins/nestable/nestable.css">

<script type="text/javascript">
    $(document).ready(function() {

        var updateOutput = function(e)
        {
            var list   = e.length ? e : $(e.target),
                output = list.data('output');
            if (window.JSON) {
                output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
            } else {
                output.val('JSON browser support required for this demo.');
            }
        };

        // activate Nestable for list 1
        $('#nestable-menu').nestable({
            group: 1,
            maxDepth: 2
        })
        .on('change', updateOutput);

        // output initial serialised data
        updateOutput($('#nestable-menu').data('output', $('#nestable-output')));

        $('#nestable-menu').on('click', function(e)
        {
            var target = $(e.target),
                action = target.data('action');
            if (action === 'expand-all') {
                $('.dd').nestable('expandAll');
            }
            if (action === 'collapse-all') {
                $('.dd').nestable('collapseAll');
            }
        });

        $('.dd').on('change', function (e) {
            $("#form-edit-menu").submit();
        });
    });
</script>

@include('backend.menus.js-menu')

<script>
    sidebar('menus', 'index');
</script>

@endsection