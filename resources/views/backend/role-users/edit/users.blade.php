<div role="tabpanel" class="tab-pane active" id="users">
    @can('approve', app(App\Models\RoleUser::class))
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
                Phân quyền
            </span>
            <input name="keyword" id="keyword" data-type="user" placeholder="Họ tên, Email, SĐT, Mã Tavico" rows="1" class="form-control input-text" />
        </div><!-- /input-group -->
    </div>
    @endcan

    
    <div class="form-group" id="user-list" data-role_id="{{ $id }}">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="bg-primary">
                    <tr>
                        <th width="30">ID</th>
                        <th width="160">Họ tên</th>
                        <th width="160">Tavico</th>
                        <th width="160">Email</th>
                        <th width="110">SĐT</th>
                        <th>Phòng ban</th>
                        <th width="50">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($role->users as $item)
                    <tr valign="middle">
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->username }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone }}</td>
                        <td>{{ $item->group->gb_title ?? '' }}</td>
                        <td class="text-center">
                            @can('delete', app(App\Models\RoleUser::class))
                            <a title="Xóa người dùng" href="javascript:;" data-url="{{ route('admin.roles.users.action', ['id' => $id]) }}" data-id="{{ $item->id }}" data-status="1" class="btn btn-sm btn-status btn-danger">
                                <i class="fa fa-trash"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div><!-- END #users -->