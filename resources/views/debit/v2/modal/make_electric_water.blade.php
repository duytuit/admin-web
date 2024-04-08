<div class="modal fade" id="HandleElectricMeter" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form id="form_electric_meter" action="{{route('admin.electricMeter.handle_electric_water')}}" method="POST">
                    @csrf
                    <div class="box-header">
                        <div class="form-group text-center" style="width: 50%; margin: 0 auto;">
                            <h5 class="box-title"><b>Khoản phí</b></h5><br><br>
                            Kỳ tính  
                            {{-- <input name="cycle_name" type="number" min="1" max="12" required class="box-title">  --}}
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
                                <!-- /.input group -->
                            </div>

                            <div style="width: 50%; margin: 0 auto;">
                                <h4><strong>Căn hộ tính phí</strong></h4>
                                <div class="form-group">
                                    <select class="form-control" name="check_all_apartment" id="customer_handler_electric_water">
                                        <option value="" selected>Tất cả căn hộ</option>
                                        <option value="can_ho"> Căn hộ </option>
                                        <option value="nhom_can_ho"> Nhóm căn hộ </option>
                                    </select>   
                                </div>
                            </div>
                            <div style="width: 50%; margin: 0 auto;">
                                <select class="form-control" id="nhom_can_ho_electric_water" style="display: none;" name="nhom_can_ho">
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
                       
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th width="100"> <input type="checkbox" class="iCheck checkAll" data-target=".checkSingle"/> Tất cả</th>
                                <th width="250">Tháng kỹ thuật viên chốt số</th>
                                <th width="150" style="text-align: center">Giảm trừ</th>
                            </tr>
                            <tr>
                                <th colspan="2"></th>
                                <th  style="display: flex;justify-content: center;">
                                    <select class="form-control" name="discount_check">
                                            <option value="">Không</option>
                                            <option value="phan_tram"> % </option>
                                            <option value="gia_tien"> VND </option>
                                    </select>   
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr class="list_item_electric_meter">
                                    <td><input type="checkbox" name="ids[electric]" value="0"
                                            class="iCheck checkSingle"/> Điện</td>
                                    <td>
                                        <select name="cycle_name_handle_electric" id="cycle_name_handle_electric" class="form-control change_">
                                            <option value="" selected>Tháng chốt số</option>
                                            @foreach($cycle_names_electric as $value)
                                                <option value="{{ $value->month_create }}">{{ $value->month_create }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </td>
                                    <td style="text-align: center">
                                        <input type="number" class="form-control" min="0"  name="discount[0]" value="0" placeholder="Giảm trừ">
                                    </td>
                                </tr>
                                <tr class="list_item_electric_meter">
                                    <td><input type="checkbox" name="ids[meter]" value="1"
                                            class="iCheck checkSingle"/> Nước</td>
                                    <td>
                                        <select name="cycle_name_handle_meter" id="cycle_name_handle_meter" class="form-control change_">
                                            <option value="" selected>Tháng chốt số</option>
                                            @foreach($cycle_names_meter as $value)
                                                <option value="{{ $value->month_create }}">{{ $value->month_create }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="text-align: center">
                                        <input type="number" class="form-control" min="0"  name="discount[1]" value="0" placeholder="Giảm trừ">
                                    </td>
                                </tr>
                                <tr class="list_item_electric_meter">
                                    <td><input type="checkbox" name="ids[meter_hot]" value="2"
                                            class="iCheck checkSingle"/> Nước Nóng</td>
                                    <td>
                                        <select name="cycle_name_handle_meter_hot" id="cycle_name_handle_meter_hot" class="form-control change_">
                                            <option value="" selected>Tháng chốt số</option>
                                            @foreach($cycle_names_meter_hot as $value)
                                                <option value="{{ $value->month_create }}">{{ $value->month_create }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="text-align: center">
                                        <input type="number" class="form-control" min="0"  name="discount[1]" value="0" placeholder="Giảm trừ">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="submit" onclick="return confirm('Vui lòng không chọn cả mục điện và nước khi thiết lập công nợ, có thể dẫn đến sai số liệu, vui lòng chọn lần lượt. Nếu bạn không chọn cả 2 cùng lúc: Nhấn OK để xác nhận !')" class="btn btn-lg btn-success handle_electric_meter" >Thiết lập công nợ</button>
                <button type="button" class="btn btn-lg btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
        </form>
    </div>
</div>
