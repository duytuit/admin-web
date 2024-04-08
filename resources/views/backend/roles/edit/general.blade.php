<div role="tabpanel" class="tab-pane active" id="general">
    <div class=form-group {{ $errors->has('title') ? 'has-error' : '' }}">
        <label class="control-label required">Tên nhóm quyền</label>
        <textarea name="title" placeholder="Tên nhóm quyền" rows="1" class="form-control input-text" required>{{ old('title', $role->title) }}</textarea>
        @if ($errors->has('title'))
        <em class="help-block">{{ $errors->first('title') }}</em>
        @endif
    </div>
    
    <div class="form-group">
        <label class="control-label">Mô tả</label>
        <textarea name="summary" placeholder="Mô tả" rows="2" class="form-control">{{ old('summary', $role->summary) }}</textarea>
    </div>
    <div class="form-group">
        <label class="control-label">Phân quyền</label>
        <div class="table-responsive">
            @php
            $permissions = $old ? old('permissions', []) : ($role->permissions ?? []);
            $permissions = array_keys($permissions);
            @endphp
            <table id="roles" class="table table-hover table-striped table-bordered tree">
                <thead>
                    <tr class="bg-primary">
                        <th>Nhóm quyền</th>
                        <th width="40">
                            <input type="checkbox" class="role-all" data-target=".role-item">
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($list as $role => $item)
                    <tr class="treegrid-{{$role}}">
                        <td><strong>{{ $item['title'] }}</strong></td>
                        <td>
                            <input type="checkbox" class="role-item item-{{ $role }}" data-target=".group-{{ $role }}">
                        </td>
                    </tr>
                    @foreach($item['group'] as $index => $group)
                    @php
                    $key = $role . $index;
                    @endphp
                    <tr class="treegrid-{{$key}} treegrid-parent-{{$role}}">
                        <td>{{ $group['title'] }}</td>
                        <td>
                            <input type="checkbox" class="role-group group-{{ $role }}" data-target=".permission-{{ $role }}-{{ $index }}">
                        </td>
                    </tr>

                    @foreach($group['permissions'] as $permission => $name)
                    @php $checked = in_array($permission, $permissions) ? 'checked' : ''; @endphp
                    <tr class="treegrid-parent-{{$key}}">
                        <td>{{ $name }}</td>
                        <td>
                            <input name="permissions[]" type="checkbox" value="{{ $permission }}" class="role-permission permission-{{ $role }}-{{ $index }}" {{ $checked }} />
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <hr />
    <div class="form-group">
        <button type="submit" class="btn btn-success" form="form-roles" value="submit">{{ $id ? 'Cập nhật' : 'Thêm mới' }}</button>
        &nbsp;
        <a href="{{ route('admin.roles.index') }}" class="btn btn-danger" form="form-roles" value="cancel">Quay lại</a>
    </div>
</div><!-- END #general -->

<script type="text/javascript" src="/adminLTE/plugins/treegrid/jquery.treegrid.js"></script>
<link rel="stylesheet" href="/adminLTE/plugins/treegrid/jquery.treegrid.css">

<script type="text/javascript">
    $('.tree').treegrid();
</script>