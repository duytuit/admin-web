<div id="list-maintenance" style="padding: 15px 0;">
    <div>
        <a href="{{ route('admin.v3.maintenance-asset.exportList',array_merge($request->all(),['asset_id_m'=>$asset->id])) }}" class="btn btn-warning">
            <i class="fa fa-download"></i>&nbsp;&nbsp;
            Export Excel
        </a>
    </div>
    <br>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="box box-primary">
                <div class="table-responsive table-bordered">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th width="50">TT</th>
                            <th width="140">Tiêu đề</th>
                            <th width="140">Danh mục</th>
                            <th width="110">Khu vực</th>
                            <th width="110">Kết quả bảo trì</th>
                            <th width="110">Trạng thái</th>
                            <th width="110">Tác vụ</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $count_list=0; ?>
                        @foreach($maintenance_times as $key => $maintenance_time)
                            <?php $count_list++ ?>
                            @if(isset($maintenance_time->asset->id))
                                <tr class="maintain-time-row">
                                    <td>
                                        {{ $count_list}}
                                    </td>
                                    <td>
                                        <a href="{{route('admin.v3.maintenance-asset.detail',$maintenance_time->id)}}">
                                            {{$maintenance_time->title}}
                                        </a>
                                    </td>
                                    <td>
                                        {{$asset_cate[$asset->asset_category_id]??""}}
                                    </td>
                                    <td>
                                        {{  $asset_area[$asset->area_id]??"" }}
                                    </td>
                                    <td>
                                        {{$maintenance_time->description}}
                                    </td>
                                    <td>
                                        {{$maintenance_time->status==1?"Đã hoàn thành":"Chưa bảo trì"}}
                                    </td>
                                    <td>
                                        <div class="col-sm-2 id-maintain-time" data-id="{{$maintenance_time->id}}">
                                            <a class="update_status_maintain">

                                                @if($maintenance_time->status==1)

                                                    <i class="fa fa-check-square"></i>
                                                @else
                                                    <i class="fa fa-square-o"></i>
                                                @endif
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>