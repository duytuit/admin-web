<div class="tab-pane {{$filter['tab']=='table'?'active':''}}" id="maintenance-asset" style="padding: 15px 0;">
    <div>
        <a href="{{ route('admin.v3.maintenance-asset.exportMonth',$request->all()) }}" class="btn btn-warning">
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
                            <th width="140">Tên tài sản</th>
                            <th width="140">Khu vực</th>
                            @foreach($listMonth as $key => $value)
                                <th width="110">Tháng {{$value}}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                </div>
            </div>
        </div>
    </div>
</div>
<script>
</script>