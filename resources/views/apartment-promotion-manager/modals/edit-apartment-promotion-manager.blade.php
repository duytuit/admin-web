<div class="modal fade" id="edit-apartment-promotion-manager" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa căn hộ khuyến mãi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" id="edit_promotion edit-apartment-promotion-manager">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="alert alert-danger alert_pop_add_promotion-manager" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="title-promotion-manager">Tên căn hộ</label>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="text" name="apartment" id="apartment_name"
                                            value="{{ $data['apartment_name'] }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-9">
                                        <div class="form-group row">
                                            <div class="col-sm-3">
                                                <label for="period-apartment-promotion-manager">kỳ</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <select name="edit_begin_cycle_name" id="begin_cycle_name"
                                                    class="form-control">
                                                    <option value="" selected>Kỳ</option>
                                                </select>
                                            </div>
                                        </div>
                                        <input hidden type="input" value="{{ $data['cycle_name'] }}"
                                            name="cycle_name">
                                    </div>
                                </div>
                                <input hidden type="text" id="id_pr_apartment" value="{{ $data['id'] }}">
                                <input hidden type="text" id="apartment_id" value="{{ $data['apartment_id'] }}">

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

<script>
    var cycle_name = $('input[name="cycle_name"]')[0].value
    for (let index = 1; index <= 12; index++) {

        if (index < 10) {
            index = '0' + index
        }
        var date = new Date().getFullYear() + '' + index
        if (date == cycle_name) {
            $('select[name="edit_begin_cycle_name"]').append(
                new Option(date, date, false, true))
        } else {
            $('select[name="edit_begin_cycle_name"]').append(
                new Option(date, date))
        }
    }
    var data = {
        "id": $('#id_pr_apartment')[0].value,
        "apartment_id": $('#apartment_id')[0].value
    }
    $('select[name="edit_begin_cycle_name"]').change(function() {
        data['cycle_name'] = $(this)[0].value
    })

    $('#update_promotion').click(function() {
        $.ajax({
            method: "POST",
            url: "apartment_promotion_manager/update",
            data: data,
            success: function(data) {
                data = JSON.parse(data)
                if (data.status == true) {
                    toastr.success(data.mess);
                    window.location.reload();
                } else {
                    toastr.error(data.mess);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(XMLHttpRequest);
            }
        })
    })
</script>
