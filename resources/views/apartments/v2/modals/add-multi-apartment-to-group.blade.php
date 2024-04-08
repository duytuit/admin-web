<div id="add-apartment-to-group"  class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <form action="POST" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Chọn nhóm</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="description-document-building">Chọn nhóm</label>
                                </div>
                                <div class="col-sm-9">
                                    <select name="" id="id-group-apartment" class="form-control">
                                        @foreach($apartment_groups as $key=>$apartment_group)
                                        <option value="{{$apartment_group->id}}" {{$key==0?'selected':''}} >{{$apartment_group->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <button type="button" class="btn btn-primary btn-js-action-add-apartment-to-group" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                </div>
            </div>
        </form>
    </div>
</div>
