<div class="tab-pane {{ isset($filter['tab']) && $filter['tab']=='list'?'active':''}}" id="list-maintenance" style="padding: 15px 0;">
    <div>
        <a href="{{ route('admin.v3.maintenance-asset.exportList',$request->all()) }}" class="btn btn-warning">
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
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer clearfix">
            </div>
        </div>
    </div>
</div>
