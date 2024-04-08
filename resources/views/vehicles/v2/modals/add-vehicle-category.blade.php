<div id="add-vehiclecard" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        @if( in_array('admin.v2.vehiclecategory.insert',@$user_access_router))
            <form action="{{ route('admin.v2.vehiclecategory.insert') }}" method="post" id="form-add-vehiclecate" class="form-validate form-horizontal">
                {{ csrf_field() }}
                <input type="hidden" name="hashtag">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Thêm danh mục</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-8">
                                <div class="alert alert-danger alert_pop_add_vehiclecate" style="display: none;">
                                    <ul></ul>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Tên danh mục</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" name="name" id="in-vccname" class="form-control" placeholder="Tên danh mục">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Mô tả</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <textarea name="description" id="description" class="form-control" placeholder="Mô tả"></textarea>
                                            </div>
                                        </div>
                                        <hr>
                                        <h4>Cấu hình</h4>
                                        <br>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Loại giá</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <select name="bdc_price_type_id" class="form-control bdc_price_type_id_category">
                                                    <option value="1" selected>Một giá</option>
                                                    <option value="2">Lũy tiến</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-1">
                                                <button type="button" class="btn btn-success add_progress_price_item" style="float: right; display: none">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="progressive_price" id="progressive_price">
                                        </div>
                                        <div class="card-body progress_price_list">
                                            <div class="temp_one_progress_price_items">
                                                <div class="form-group">
                                                    <div class="col-sm-3">
                                                        <label for="in-re_name">Đơn giá</label>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="price" class="form-control" placeholder="Giá tiền">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Loại xe</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <select name="type" class="form-control">
                                                    <option value="1">Xe đạp</option>
                                                    <option value="2" selected>Xe máy</option>
                                                    <option value="3">Ô tô</option>
                                                    <option value="4">Xe điện</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Ngày áp dụng tính phí</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="date"
                                                       class="form-control pull-right date_picker"
                                                       name="first_time_active" value="{{ old('first_time_active') }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Mã thu</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text"
                                                       class="form-control pull-right code_receipt"
                                                       name="code_receipt" value="{{ old('code_receipt') }}" placeholder="Mã thu">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Ngày chuyển đổi</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="number"
                                                       min="1"
                                                       class="form-control pull-right"
                                                       name="ngay_chuyen_doi" value="{{ old('ngay_chuyen_doi') }}" placeholder="Ngày chuyển đổi">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Ngày tính phí dịch vụ</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="number" value="1" name="bill_date" class="form-control" placeholder="Ngày tính phí dịch vụ">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Ngày thanh toán</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="number" value="10" name="payment_dealine" class="form-control" placeholder="Ngày thanh toán">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Đối tượng thu phí</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <select name="service_group" class="form-control">
                                                    <option value="1" selected>Công ty</option>
                                                    <option value="2" selected>Thu hộ</option>
                                                    <option value="3" selected>Chủ đầu tư</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Trạng thái</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="checkbox" name="status" checked>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                        <button type="button" class="btn btn-primary btn-js-action-vehiclecate" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
<div class="progress_price_one" style="display: none">
    <div class="temp_one_progress_price_items">
        <div class="form-group">
            <div class="col-sm-3">
                <label for="in-re_name">Đơn giá</label>
            </div>
            <div class="col-sm-8">
                <input type="text" name="price" class="form-control" placeholder="Giá tiền">
            </div>
        </div>
    </div>
</div>
<div class="progress_price_multi" style="display: none">
    <div class="progress_price_items">
        <div class="form-group">
            <div class="form-row row progress_price_item col-sm-12">
                <div class="form-group col-md-3">
                    <input type="text" name="progressive[from][0]" class="form-control progress_from" placeholder="Từ">
                </div>
                <div class="form-group col-md-3">
                    <input type="text" name="progressive[to][0]" class="form-control progress_to" placeholder="Đến">
                </div>
                <div class="form-group col-md-3">
                    <input type="text" name="progressive[price][0]" class="form-control progress_price" placeholder="Giá tiền">
                </div>
                <div class="form-group col-md-3">
                    <button type="button" class="btn btn-danger remove_progress_price_item" onclick="removeProgressPrice(this)">
                        <i class="fa fa-minus" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .form-row.row.progress_price_items.col-sm-12 {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }
</style>

{{--@section('javascript')--}}
{{--<script>--}}
{{--    sidebar('event', 'index');--}}
{{--</script>--}}
{{--<script>--}}
{{--    function removeProgressPrice(that) {--}}
{{--        $(that).closest(".progress_price_items").remove();--}}
{{--        $(".progress_price_list").find(".progress_price_items").each(function (key, value) {--}}
{{--            $(this).find("input").each(function () {--}}
{{--                this.name = this.name.replace(/\d+/, key);--}}
{{--            });--}}
{{--        });--}}
{{--    }--}}

{{--    $(".add_progress_price_item").click(function (e) {--}}
{{--        var avails = $(".progress_price_items");--}}
{{--        var clone = avails.eq(0).clone();--}}
{{--        $(".progress_price_list").append(clone).find(".progress_price_items").each(function (key, value) {--}}
{{--            $(this).find("input").each(function () {--}}
{{--                this.name = this.name.replace(/\d+/, key);--}}
{{--            });--}}
{{--        });--}}
{{--        e.preventDefault();--}}
{{--    });--}}

{{--    $(".bdc_price_type_id_category").on("change", function () {--}}
{{--        var priceTypeValue = $(this).val();--}}
{{--        console.log(1234);--}}
{{--        console.log(priceTypeValue);--}}
{{--        if (priceTypeValue == 1) {--}}
{{--            $(".add_progress_price_item").prop("disabled", true);--}}
{{--            $(".add_progress_price_item").css("display", "none");--}}
{{--            var avails = $(".temp_one_progress_price_items");--}}
{{--            var clone = avails.eq(0).clone();--}}
{{--            $(".progress_price_list").html(clone);--}}
{{--        } else {--}}
{{--            $(".add_progress_price_item").prop("disabled", false);--}}
{{--            $(".add_progress_price_item").css("display", "block");--}}
{{--            var avails = $(".progress_price_items");--}}
{{--            var clone = avails.eq(0).clone();--}}
{{--            $(".progress_price_list").html(clone);--}}
{{--        }--}}
{{--    });--}}

{{--    $(".btn-js-action-vehiclecate").on('click', function () {--}}
{{--        var _this = $(this);--}}
{{--        $(".alert_pop_add_vehiclecate").hide();--}}
{{--        var code = $("#in-vccname").val();--}}
{{--        if (code.length <= 3 || code.length >= 45) {--}}
{{--            $(".alert_pop_add_vehiclecate").show();--}}
{{--            $(".alert_pop_add_vehiclecate ul").html('<li>Tên loại phương tiện không được nhỏ hơn 3 hoặc lớn hơn 45 ký tự</li>')--}}
{{--        } else {--}}

{{--            $.ajax({--}}
{{--                url: '/admin/v2/vehiclecategory/checkVehicleNameCategory',--}}
{{--                type: 'POST',--}}
{{--                data: {--}}
{{--                    'name': code--}}
{{--                },--}}
{{--                success: function (res) {--}}
{{--                    if(res.data.count===0) {--}}
{{--                        let progress = [];--}}
{{--                        $('.progress_price_list .progress_price_item').each((index, e) => {--}}
{{--                            console.log(e);--}}
{{--                            console.log($(e).find('.progress_from').val());--}}
{{--                            progress.push({--}}
{{--                                from: $(e).find('.progress_from').val(),--}}
{{--                                to: $(e).find('.progress_to').val(),--}}
{{--                                price: $(e).find('.progress_price').val()--}}
{{--                            });--}}

{{--                        });--}}

{{--                        progress = JSON.stringify(progress);--}}

{{--                        $('#progressive_price').val(progress);--}}

{{--                        $("#form-add-vehiclecate").submit();--}}
{{--                    }--}}
{{--                    else {--}}
{{--                        $(".alert_pop_add_vehiclecate").show();--}}
{{--                        $(".alert_pop_add_vehiclecate ul").html('<li>Tên loại phương tiện đã tồn tại</li>')--}}
{{--                    }--}}
{{--                },--}}
{{--                error: function (e) {--}}
{{--                    console.log(e)--}}
{{--                }--}}
{{--            })--}}


{{--        }--}}
{{--    });--}}

{{--</script>;--}}
{{--@endsection--}}
