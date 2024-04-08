<div class="box-body">
    <div class="row">
        <form id="form-search-cate" class="col-sm-12" action="" method="GET">
            <div id="search-advance" class="search-advance">
                <div class="col-sm-12">
                    <div class="col-sm-2">
                        <select name="area_id_m"
                                class="form-control">
                            <option value="">Chọn khu vực</option>

                        </select>
                    </div>
                    <div class="col-sm-3">
                        <select name="cate_id_m"
                                class="form-control"
                        >
                            <option value="">Chọn danh mục</option>

                        </select>
                    </div>
                    <div class="col-sm-3">
                        <select name="asset_id_m"
                                class="form-control"
                        >
                            <option value="">Chọn tài sản</option>

                        </select>
                    </div>

                    <div class="form-group space-5 col-sm-3" style="/*width: calc(100% - 55px);*/float: left;">
                        <div class="col-sm-12">
                            <input type="text" class="form-control"
                                   name="keyword_maitain_m"
                                   placeholder="Nhập từ khóa tìm kiếm"
                                   value=""
                            >
                        </div>
                    </div>
                    <div class="input-group-btn col-sm-1">
                        <button type="submit" title="Tìm kiếm" class="btn btn-info" form="form-search-cate"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-3">
                        {{--                        <div class="input-group date">--}}
                        <input type="date"
                               class="form-control date_picker"
                               alt="Ngày bắt đầu"
                               name="start_date" value="">
                        {{--                        </div>--}}
                    </div>
                    <div class="col-sm-3">
                        {{--                        <div class="input-group date">--}}
                        <input type="date"
                               alt="Ngày kết thúc"
                               class="form-control date_picker"
                               name="end_date" value="">
                        {{--                        </div>--}}
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>