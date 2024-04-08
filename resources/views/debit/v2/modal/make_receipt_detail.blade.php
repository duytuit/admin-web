<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form action="{{route('admin.v2.debit.detail-handling')}}" method="POST">
                    @csrf
                    <div class="box-header with-border">
                        <div class="form-group text-center" style="width: 50%; margin: 0 auto;">
                            <h5 class="box-title"><b>Khoản phí</b></h5><br><br>
                            Kỳ tháng  
                            <select class="input-sm cycle_month" style="box-shadow: none;
                            border-color: #3c8dbc;" name="cycle_month">
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
                            <select class="input-sm cycle_year" style="box-shadow: none;
                            border-color: #3c8dbc;" name="cycle_year">
                                <option value="{{\Carbon\Carbon::now()->year - 1}}">{{\Carbon\Carbon::now()->year - 1}}</option>
                                <option value="{{\Carbon\Carbon::now()->year}}" selected="selected">{{\Carbon\Carbon::now()->year}}</option>
                                <option value="{{\Carbon\Carbon::now()->year + 1}}">{{\Carbon\Carbon::now()->year + 1}}</option>
                            </select>
                        </div>
                        <div class="form-group" style="width: 50%; margin: 0 auto;">
                            <label>Ngày thanh toán:</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="date" class="form-control pull-right date_picker"
                                       name="payment_deadline"
                                       id="datepicker" value="{{$paymentDeadlineBuilding}}">
                            </div>
                        </div>
                        <div style="width: 50%; margin: 0 auto;">
                            <h4><strong>Căn hộ tính phí</strong></h4>
                            <div class="form-group">
                                <select class="form-control" name="check_all_apartment" id="customer_handler">
                                    <option value="" selected>Tất cả căn hộ</option>
                                    <option value="can_ho"> Căn hộ </option>
                                    <option value="nhom_can_ho"> Nhóm căn hộ </option>
                                </select>   
                            </div>
                        </div>
                        <div style="width: 50%; margin: 0 auto;">
                            <select class="form-control" id="nhom_can_ho" style="display: none;" name="nhom_can_ho">
                                <option value="">Chọn nhóm căn hộ</option>
                                @foreach ($apartmentGroup as $value)
                                   <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                            </select>
                            <select class="form-control select2 col-sm-2" multiple id="can_ho" name="can_ho[]" style="width: 100%;display: none;">
                                @foreach ($apartments as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th> <input type="checkbox" class="iCheck checkAll" data-target=".checkSingle"/> Tất cả</th>
                            <th width="150">Ngày bắt đầu</th>
                            <th width="150">Ngày kết thúc</th>
                            <th width="150" style="text-align: center">
                                <select class="form-control" name="discount_check">
                                    <option value="">Giảm trừ</option>
                                    <option value="phan_tram"> % </option>
                                    <option value="gia_tien"> VND </option>
                                </select>   
                            </th>
                            <th width="250" style="text-align: center">Lý do</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($serviceBuildings_cycle_month as $serviceBuilding)
                            <tr class="list_item">
                                <td><input type="checkbox" name="ids[{{$serviceBuilding->id}}]" value="{{$serviceBuilding->id}}"
                                           class="iCheck checkSingle"/>  {{$serviceBuilding->name}}</td>
                                <?php 
                                    $current_date = \Carbon\Carbon::now();
                                    $billDateDay = $serviceBuilding->bill_date;
                                    $ngay_bat_dau = "{$billDateDay}-{$current_date->month}-{$current_date->year}";
                                    $ngay_ket_thuc = date('d-m-Y', strtotime($ngay_bat_dau. "+1 months"));
                                ?>
                                 <td>
                                    <input type="text" class="form-control date_picker ngay_bat_dau" data-ngay_bat_dau="{{$billDateDay}}" name="start[{{$serviceBuilding->id}}]" value="{{$ngay_bat_dau}}" placeholder="ngày bắt đầu">
                                </td>
                                <td>
                                    <input type="text" class="form-control date_picker ngay_ket_thuc" name="end[{{$serviceBuilding->id}}]" value="{{$ngay_ket_thuc}}" placeholder="ngày kết thúc">
                                </td>
                                <td style="text-align: center">
                                    <input type="number" class="form-control" min="0"  name="discount[{{$serviceBuilding->id}}]" step="any" value="0" placeholder="Giảm trừ">
                                </td>
                                <td style="text-align: center">
                                    <input type="text" class="form-control"  name="discount_note[{{$serviceBuilding->id}}]" placeholder="lý do giảm trừ">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="submit" class="btn btn-lg btn-success" >Thiết lập công nợ</button>
                <button type="button" class="btn btn-lg btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
        </form>
    </div>
</div>
<style>
    .modal-dialog {
            width: 950px !important;
            margin: 30px auto;
    }
</style>
