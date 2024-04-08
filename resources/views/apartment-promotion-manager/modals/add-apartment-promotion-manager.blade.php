<style>
    .image-upload-item {
        width: 50px;
        height: 50px;
        position: relative;
        margin: 5px;
        border: 1px solid;
        display: flex;
        justify-content: flex-end;
        align-items: flex-start;
    }

    #dvPreview {
        display: flex;
        flex-wrap: wrap;
    }

    #dvPreviewEdit {
        display: flex;
        flex-wrap: wrap;
    }

    .image-upload-item i {
        color: #000;
        position: relative;
        top: 0;
        right: 0;
        font-size: 11px;
        z-index: 100;
        cursor: pointer;
    }

    .image-upload-item img {
        height: 100%;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
    }

    #fileupload {
        color: #ffffff;
    }

    #fileuploadEdit {
        color: #ffffff;
    }

    #file_other {
        color: #ffffff;
    }

    #file-upload-item-edit {
        color: #ffffff;
    }
</style>
<div id="add-apartment-promotion-manager" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <form action="POST" id="form-add-apartment-promotion-manager" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Thêm khuyến mãi căn hộ</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_add_apartment-promotion-manager" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <input type="hidden" id="apartment_promotion_id">
                                <input type="hidden" id="service_id">
                                <div class="col-sm-3">
                                    <label for="category-apartment-promotion-manager">Căn hộ</label>
                                </div>
                                <div class="col-sm-9 apartment">
                                    <select name="apartment_id" class="form-control" id="bdc_apartment_id" style="width: 100%">
                                        <option value="" selected>Căn hộ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="category-apartment-promotion-manager">Dịch vụ căn hộ</label>
                                </div>
                                <div class="col-sm-9 service_apartment">
                                    <select name="service_price_id" class="form-control" id="service_price_id" style="width: 100%">
                                        <option value="" selected> Vui lòng chọn dịch căn hộ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="title-apartment-promotion-manager"> khuyến mãi</label>
                                </div>
                                <div class="col-sm-9 promotion">
                                    <select name="promotion_id" class="form-control" id="promotion_id" style="width: 100%">
                                        <option value="" selected>Vui lòng chọn khuyến mãi</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="period-apartment-promotion-manager">kỳ</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="form-group" style="margin: 0;">
                                        <select class="input-sm cycle_month" name="cycle_month">
                                            <option value="01" @if(\Carbon\Carbon::now()->month == 1) selected @endif>01</option>
                                            <option value="02" @if(\Carbon\Carbon::now()->month == 2) selected @endif>02</option>
                                            <option value="03" @if(\Carbon\Carbon::now()->month == 3) selected @endif>03</option>
                                            <option value="04" @if(\Carbon\Carbon::now()->month == 4) selected @endif>04</option>
                                            <option value="05" @if(\Carbon\Carbon::now()->month == 5) selected @endif>05</option>
                                            <option value="06" @if(\Carbon\Carbon::now()->month == 6) selected @endif>06</option>
                                            <option value="07" @if(\Carbon\Carbon::now()->month == 7) selected @endif>07</option>
                                            <option value="08" @if(\Carbon\Carbon::now()->month == 8) selected @endif>08</option>
                                            <option value="09" @if(\Carbon\Carbon::now()->month == 9) selected @endif>09</option>
                                            <option value="10" @if(\Carbon\Carbon::now()->month == 10) selected @endif>10</option>
                                            <option value="11" @if(\Carbon\Carbon::now()->month == 11) selected @endif>11</option>
                                            <option value="12" @if(\Carbon\Carbon::now()->month == 12) selected @endif>12</option>
                                        </select>
                                        /
                                        <select class="input-sm cycle_year" name="cycle_year">
                                            <option value="{{\Carbon\Carbon::now()->year - 1}}">{{\Carbon\Carbon::now()->year - 1}}</option>
                                            <option value="{{\Carbon\Carbon::now()->year}}" selected="selected">{{\Carbon\Carbon::now()->year}}</option>
                                            <option value="{{\Carbon\Carbon::now()->year + 1}}">{{\Carbon\Carbon::now()->year + 1}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i
                            class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <button type="button" class="btn btn-primary save_apartment_promotion_manager"
                        form="form-add-verhicle" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác
                        nhận</button>
                </div>
            </div>
        </form>
    </div>
</div>
