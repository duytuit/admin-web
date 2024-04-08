@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Permission Manage</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Permission Manage</li>
        </ol>
    </section>
    <section class="content">
        <div class="container-fluid box box-primary">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Permission Manage</div>
                        <div class="panel-body">
                             <div class="topic-viewmore">
                                <a href="{!! route('admin.system.permission.create') !!}">Add New</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-solid">
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="box-group" id="accordion">
                                <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                                @if($data->count() > 0)
                                    @foreach($data as $module)
                                        <div class="panel box box-primary">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{ $module->id }}" aria-expanded="{{ $active_module == $module->id ? 'true' : 'false' }}" class="{{ $active_module != $module->id ? 'collapsed' : '' }}">
                                            <div class="box-header with-border bg-light-blue">
                                                <div class="text-create-recipt">
                                                    <i class="fa {{ $module->icon_web }}"></i>  {{ $module->name }}
                                                </div>
                                            </div>
                                            </a>
                                            <div id="collapse{{ $module->id }}" class="panel-collapse collapse {{ $active_module == $module->id ? 'in' : '' }}" aria-expanded="{{ $active_module == $module->id ? 'true' : 'false' }}" style="{{ $active_module != $module->id ? 'height: 0px;' : '' }}">
                                                <div class="box-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead class="bg-olive">
                                                            <tr>
                                                                <th colspan="" rowspan="" headers="">#</th>
                                                                <th colspan="" rowspan="" headers="">Name</th>
                                                                <th colspan="" rowspan="" headers="">Router name</th>
                                                                <th colspan="" rowspan="" headers="">Icon</th>
                                                                <th colspan="" rowspan="" headers="">Has menu</th>
                                                                <th colspan="" rowspan="" headers="">Action</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody class="tablecontents">
                                                               @if($module->permissions->count() > 0)
                                                                   <?php
                                                                        $check_double_per = array();
                                                                        $count_per = 0;
                                                                    ?>
                                                                    @foreach ($module->permissions()->where('has_menu',1)->groupBy('route_name')->orderBy('route_name','asc')->get() as $key_1 => $item_1)
                                                                        <?php
                                                                            $count_per++;
                                                                        ?>
                                                                        <tr class="row1" data-id="{{$item_1->id}}">
                                                                            <td colspan="" rowspan="" headers="">No.{{ $count_per }}</td>
                                                                            <td style="font-weight: bold;font-size: 15px;" class="item_cur_cursor">{{ $item_1->title }}</td>
                                                                            <td colspan="" rowspan="" headers="">{{ $item_1->route_name }}</td>
                                                                            <td colspan="" rowspan="" headers=""><i class="fa {{ $item_1->icon_web }}"></i></td>
                                                                            <td colspan="" rowspan="" headers="">@if($item_1->has_menu == 1) <span class="btn btn-xs btn-warning"><i class="fa fa-check"></i></span> @endif</td>
                                                                            <td colspan="" rowspan="" headers="">
                                                                                <a href="{{ route('admin.system.permission.edit', $item_1->id) }}" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                                                                <a data-url="{{ route('admin.system.permission.destroy', $item_1->id) }}"  class="btn btn-sm btn-danger delete-permission" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                            @foreach ($module->permissions()->where('has_menu',0)->groupBy('route_name')->orderBy('route_name','asc')->get() as $key_2 => $item_2)
                                                                                @if(str_contains($item_1->route_name, 'admin.') && str_contains($item_2->route_name, 'admin.') && explode('.',$item_1->route_name)[1] == explode('.',$item_2->route_name)[1] && !in_array($item_2->route_name, $check_double_per))
                                                                                    <?php
                                                                                      array_push($check_double_per,$item_2->route_name);
                                                                                      $count_per++;
                                                                                    ?>
                                                                                    <tr class="row1" data-id="{{$item_2->id}}">
                                                                                        <td colspan="" rowspan="" headers="">No.{{ $count_per }}</td>
                                                                                        <td style="padding-left: 30px;" class="item_cur_cursor" colspan="" rowspan="" headers="">{{ $item_2->title }}</td>
                                                                                        <td colspan="" rowspan="" headers="">{{ $item_2->route_name }}</td>
                                                                                        <td colspan="" rowspan="" headers=""><i class="fa {{ $item_2->icon_web }}"></i></td>
                                                                                        <td colspan="" rowspan="" headers="">@if($item_2->has_menu == 1) <span class="btn btn-xs btn-warning"><i class="fa fa-check"></i></span> @endif</td>
                                                                                        <td colspan="" rowspan="" headers="">
                                                                                            <a href="{{ route('admin.system.permission.edit', $item_2->id) }}" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                                                                            <a data-url="{{ route('admin.system.permission.destroy', $item_2->id) }}"  class="btn btn-sm btn-danger delete-permission" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                                                                        </td>
                                                                                    </tr>
                                                                                @endif
                                                                            @endforeach
                                                                    @endforeach
                                                                    @foreach ($module->permissions()->where('has_menu',0)->groupBy('route_name')->orderBy('route_name','asc')->get() as $key_3 => $item_3)
                                                                                @if(!in_array($item_3->route_name, $check_double_per))
                                                                                    <?php
                                                                                      array_push($check_double_per,$item_3->route_name);
                                                                                      $count_per++;
                                                                                    ?>
                                                                                    <tr class="row1" data-id="{{$item_3->id}}">
                                                                                        <td colspan="" rowspan="" headers="">No_has_menu.{{ $count_per }}</td>
                                                                                        <td colspan="" rowspan="" headers="" class="item_cur_cursor">{{ $item_3->title }}</td>
                                                                                        <td colspan="" rowspan="" headers="">{{ $item_3->route_name }}</td>
                                                                                        <td colspan="" rowspan="" headers=""><i class="fa {{ $item_3->icon_web }}"></i></td>
                                                                                        <td colspan="" rowspan="" headers="">@if($item_3->has_menu == 1) <span class="btn btn-xs btn-warning"><i class="fa fa-check"></i></span> @endif</td>
                                                                                        <td colspan="" rowspan="" headers="">
                                                                                            <a href="{{ route('admin.system.permission.edit', $item_3->id) }}" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                                                                            <a data-url="{{ route('admin.system.permission.destroy', $item_3->id) }}"  class="btn btn-sm btn-danger delete-permission" title="Xóa"><i class="fa fa-trash-o"></i></a>
                                                                                        </td>
                                                                                    </tr>
                                                                                @endif
                                                                    @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </div>
    </section>

@endsection
<style>
    .item_cur_cursor{
        cursor: move;
    }
</style>
@section('javascript')

<script>
    deleteSubmit('.delete-permission');
    sidebar('event', 'index');
    $(document).ready(function () {
        $( ".tablecontents" ).sortable({
            items: "tr",
            cursor: 'move',
            opacity: 0.6,
            disabled: false,
            update: function() {
                sendOrderToServer();
            }
        });         
     }); 
     function sendOrderToServer() {
        var order = [];
        var token = $('meta[name="csrf-token"]').attr('content');
        
        $('tr.row1').each(function(index,element) {
            order.push({
                id: $(this).attr('data-id'),
                position: index+1
            });
        });
        $.ajax({
            type: "POST", 
            dataType: "json", 
            url: "{{ url('/admin/system/check_index_position') }}",
            data: {
                order: order,
                _token: token
            },
            success: function(response) {
                if (response.status == "success") {
                } else {
                }
            }
        });
     }              
</script>

@endsection