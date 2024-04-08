@extends('backend.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Menu Manage</div>

                <div class="panel-body">
                     <div class="topic-viewmore">
                        <a href="{!! route('admin.system.menu.create') !!}">Add New</a>
                    </div>

                    <hr>

                     <table class="table table-striped">
                        <thead>
                        <tr>
                            <th colspan="" rowspan="" headers="">#</th>
                            <th colspan="" rowspan="" headers="">Menu Name</th>
                            <th>Icon</th>
                            <th colspan="" rowspan="" headers="">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $key=>$item)
                        <tr>
                            <td colspan="" rowspan="" headers="">No.{{ $key+1 }}</td>
                            <td colspan="" rowspan="" headers="">{{ $item->name }}</td>
                            <td colspan="" rowspan="" headers=""><i class="fa {{ $item->icon_web }}"></i></td>
                            <td colspan="" rowspan="" headers="">
                                <a href="{{ route('admin.system.menu.edit', $item->id) }}" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                <a data-url="{{ route('admin.system.menu.destroy', $item->id) }}" class="btn btn-sm btn-danger delete-menu" title="Xóa"><i class="fa fa-trash-o"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="pager">
                        {{-- @include('pagination', ['result'=>$data]) --}}
                    </div>
                    {{-- .pager --}}
                </div>
                <div class="row mbm">
                    <div class="col-sm-6 pull-left">
                        <span class="record-total">Hiển thị {{ $data->count() }} / {{ $data->total() }} kết quả</span>
                    </div>
                    <div class="col-sm-6 pull-right">
                        <div class="pagination-panel">
                            {{ $data->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')

<script>
    deleteSubmit('.delete-menu');
    sidebar('event', 'index');
</script>

@endsection