<div class="modal fade" id="createCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
    <div class="modal-dialog shift" role="document" style="padding: 20px 0;">
        <div class="modal-content" style="border-radius: 5px;">
            <div class="modal-header" style="
            border-top-right-radius: 5px;
            border-top-left-radius: 5px;
            color: white;
            background-color: #3c8dbc;
            padding: 5px;
            border-bottom: 0;
            ">
                <h5 class="modal-title" style="margin-top: 2px;">Thêm danh mục công việc</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" class="col-sm-12" style="padding:0;">
                <div>
                    <ul id="category-errors"></ul>
                </div>
                <form class="form-horizontal" action="" method="POST" id="form-category-workdiary">
                    {{ csrf_field() }}
                    <input type="hidden" name="building_id" value="{{ @$building_id }}">
                    <input type="hidden" name="id" id="cat_workdiary_id">
                    <div class="box-body">
                        <div class="form-group" style="padding: 0 45px;"> 
                            <div>
                                <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span> Tên danh mục công việc:</label>
                            </div>
                            <input type="text" name="name" class="form-control" id="category_workdiary_name" value="">
                        </div>
                        <div class="form-group" style="padding: 0 45px;"> 
                            <div>
                                <label class="control-label">Trạng thái:</label>
                            </div>
                            <label class="switch" style="margin-top: 10px;">
                                <input type="checkbox" name="status" value="1" id="cat_workdiary_status" />
                                <span class="slider round"></span>
                            </label>
                       </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add-category-workdiary">Lưu</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                    </div>
                   
                </form>
            </div>

        </div>
    </div>
</div>
