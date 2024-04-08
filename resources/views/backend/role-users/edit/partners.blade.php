<div role="tabpanel" class="tab-pane" id="partners">
    @can('approve', app(App\Models\RoleUser::class))
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
                Phân quyền
            </span>
            <input name="partner_keyword" id="partner-keyword" data-type="partner" placeholder="Họ tên, Email" rows="1" class="form-control input-text" />
        </div><!-- /input-group -->
    </div>
    @endcan

    <div class="form-group" id="user-list" data-role_id="{{ $id }}">
        @if($role->partners->toArray())
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="bg-primary">
                    <tr>
                        <th width="30">ID</th>
                        <th width="15%">Họ tên</th>
                        <th width="15%">Email</th>
                        <th width="10%">SĐT</th>
                        <th width="25%">Đối tác</th>
                        <th width="25%">Chi nhánh</th>
                        <th width="50">Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($role->partners as $item)
                    <tr valign="middle">
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->full_name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone }}</td>
                        <td>{{ $item->partner_name ?? '' }}</td>
                        <td>{{ $item->branch_name ?? '' }}</td>
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
        @else
        Chưa có Tài khoản nào!
        @endif
    </div>
</div><!-- END #users -->