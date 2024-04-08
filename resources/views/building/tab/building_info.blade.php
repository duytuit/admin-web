<div id="thong_tin_toa_nha" class="tab-pane fade in active">
    <div class="pull-right">
        <button class="btn btn-warning" data-toggle="modal" data-target="#createBuildingInfo">Thêm thông tin</button>
    </div>
    <br>
    <br>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered">
            <thead class="bg-primary">
            <tr>
                <th>STT</th>
                <th>Nội dung</th>
                <th>Số lượng</th>
                <th>Ghi chú</th>
                <th>Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @if($building_infos->count() > 0)
                @foreach($building_infos as $key => $building_info)
                <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $building_info->content }}</td>
                <td>{{ $building_info->quantity }}</td>
                <td>{{ @$building_info->note }}</td>
                <td>
                    <a data-id="{{ $building_info->id }}" data-action="{{ route('admin.building.editInfo') }}"
                       class="btn btn-xs btn-primary edit-info" title="Sửa thông tin"><i
                                class="fa fa-pencil"></i></a>
                    <a class="btn btn-xs btn-danger delete-building-info"
                       data-url="{{ route('admin.building.destroyInfo', $building_info->id) }}"
                       title="Xóa thông tin"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>
@include('building.modal.create_building_info')