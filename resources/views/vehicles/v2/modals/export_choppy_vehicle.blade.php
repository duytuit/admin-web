<div id="search_choppy_vehicle" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        @if( in_array('admin.v2.vehicles.exportChoppyByTypeVehicle',@$user_access_router))
            <form action="{{ route('admin.v2.vehicles.exportChoppyByTypeVehicle',Request::all()) }}" method="GET" id="form-search-choppy" class="form-horizontal">
                {{ csrf_field() }}
                <input type="hidden" name="place_id" value="{{@$data_search['place_id']}}">
                <input type="hidden" name="apartment" value="{{@$data_search['apartment']}}">
                <input type="hidden" name="cate" value="{{@$data_search['cate']}}">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Export excel biến động phương tiện</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger alert_pop_add_vehicle" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="col-sm-5">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                            <input type="date" class="form-control" name="from_date" value="{{@$data_search['form_date']}}">
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                            <input type="date" class="form-control" name="to_date" value="{{@$data_search['to_date']}}">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="input-group-btn">
                                        <button type="submit" title="Export Choppy" class="btn btn-info" form="form-search-choppy"><i class="fa fa-file-excel-o"></i> Export</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </form>
        @endif
    </div>
</div>
