<div class="modal fade" id="editpromotion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa khuyến mãi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" id="edit_promotion">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="alert alert-danger alert_pop_add_promotion-manager" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="title-promotion-manager">Tên khuyến mãi</label>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="text" name="name" id="name-promotion-manager"
                                            class="form-control" placeholder="Tên khuyến mãi"
                                            value="{{ $data['edit_promotion']->name }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="category-promotion-manager">Loại khuyến mãi</label>
                                    </div>
                                    <div class="col-sm-9">
                                        <select class="input-sm promotion-categories"
                                            style="box-shadow: none;
                                        border-color: #3c8dbc;"
                                            name="promotion-categories">
                                            <option value="" selected>Loại khuyến mãi</option>
                                            <option selected value="{{ $data['listTypeService']->id }}">
                                                {{ $data['listTypeService']->name }}</option>
                                            </option>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="category-promotion-manager">Dịch vụ</label>
                                    </div>
                                    <div class="col-sm-9">
                                        <select class="input-sm promotion-service"
                                            style="box-shadow: none;
                                        border-color: #3c8dbc;"
                                            name="promotion-service">
                                            <option value="" selected>Dịch vụ</option>
                                            @foreach ($data['ListService'] as $item)
                                                @if ($item->id == $data['edit_promotion']->service_id)
                                                    <option selected value="{{ $item->id }}">{{ $item->name }}
                                                    </option>
                                                @else
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="time-promotion-manager">Thời gian áp dụng</label>
                                    </div>
                                    <div class="col-sm-9">
                                        từ ngày : <input type="datetime-local" name="time_use_from" id="time_use_from"
                                            value="{{ $data['edit_promotion']->begin }}" id=""> <br> <br> đến
                                        ngày : <input type="datetime-local" name="time_use_to"
                                            value="{{ $data['edit_promotion']->end }}" id="time_use_to">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="discount-promotion-manager">Giá trị khuyến mãi</label>
                                    </div>
                                    <div class="col-sm-9">
                                        <div style="display: flex;" id="promotion-discount-main">
                                            <input type="text" name="value" id="promotion-discount"
                                                autocomplete="off" class="form-control" placeholder="Nhập giá trị"
                                                value="{{ $data['edit_promotion']->discount }}">
                                            <select name="type_discount" id="type_discount" class="form-control"
                                                style="width: 25%">
                                                <option value="">Định dạng</option>
                                                <option value="0"
                                                    @if ($data['edit_promotion']->type_discount == 0) selected @endif>%
                                                </option>
                                                <option value="1"
                                                    @if ($data['edit_promotion']->type_discount == 1) selected @endif>VND</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <input hidden type="text" id="id" value="{{ $data['edit_promotion']->id }}">
                                <input hidden type="text" id="status"
                                    value="{{ $data['edit_promotion']->status }}">

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="submit" class="btn btn-info" id="update_promotion" hidden>Cập nhật</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal" hidden>Hủy</button>
            </div>
        </div>
    </div>
</div>
<script src="/js/validate.js"></script>

<script>
    if ($("#type_discount")[0].value == 1) {
        $('#promotion-discount').change(function() {
            var text = $(this).val().replaceAll('.', '');
            var money = new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(text);
            money = money.replace(' ₫', '')
            $(this).val(money)
        })


        var text = $('#promotion-discount').val().replaceAll('.', '');
        var money = new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(text);
        money = money.replace(' ₫', '')

        $('#promotion-discount').val(money)
    }

    $("#type_discount").change(function() {
        if ($(this).val() == 1) {
            $('#promotion-discount').change(function() {
                var text = $(this).val().replaceAll('.', '');
                var money = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(text);
                money = money.replace(' ₫', '')
                $(this).val(money)
            })
        } else {
            $('#promotion-discount').change(function() {
                var text = $(this).val().replaceAll('.', '');
                $(this).val(text)
            })
        }
    })


    $('#update_promotion').click(function() {
        var dt = new Date();
        var hour = dt.getHours();
        var minute = dt.getMinutes();
        var second = dt.getSeconds();

        if (dt.getHours() < 10) {
            hours = '0' + dt.getHours()
        }
        if (dt.getMinutes() < 10) {
            minute = '0' + dt.getMinutes()
        }
        if (dt.getSeconds() < 10) {
            second = '0' + dt.getSeconds()
        }
        var time = hour + ":" + minute + ":" + second;
        var data = {
            'id': $('#id')[0].value,
            "status": $('#status')[0].value,
            'name': $('#name-promotion-manager')[0].value,
            'service_type': $('.promotion-categories')[0].value,
            'type': 'service_vehicle',
            'service_id': $('.promotion-service')[0].value,
            'discount': $('#promotion-discount')[0].value.replaceAll('.', ''),
            'begin': $('#time_use_from')[0].value.replace('T', ' '),
            'end': $('#time_use_to')[0].value.replace('T', ' '),
            'condition': $('.promotion-categories')[0].value,
            'type_discount': $("#type_discount")[0].value
        }
        var vali = validate('#edit_promotion')
        if (vali == 0) {
            $.ajax({
                method: "POST",
                url: "promotion_manager/update",
                data: data,
                success: function(data) {
                    if (data.error == 0) {
                        toastr.success(data.mess);
                        window.location.reload();

                    } else {
                        toastr.error(data.mess);
                    }
                }
            })
        }

    })
</script>
