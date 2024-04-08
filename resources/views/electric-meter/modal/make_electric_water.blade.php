<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form action="{{route('admin.electricMeter.handle_electric_water')}}" method="POST">
                    @csrf
                    <div class="box-header">
                        <div class="text-center">
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
                            <br><br>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Hạn thanh toán:</label>
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
                        <div class="form-group col-sm-6">
                            <label for="ip-name">Giảm trừ</label>
                            <div  style="display: flex;justify-content: center;">
                                <select class="form-control" name="discount_check">
                                    <option value="">Không</option>
                                    <option value="phan_tram"> % </option>
                                    <option value="gia_tien"> VND </option>
                                </select>   
                                <input style="margin-left: 10px;" type="number" class="form-control" min="0"  name="discount" value="0" placeholder="Giảm trừ">
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Kỳ chốt số:</label>
                            <select name="cycle_name_handle" id="cycle_name_handle" class="form-control change_">
                                <option value="" selected>Kì bảng kê</option>
                                @foreach($cycle_names as $cycle_name)
                                <option value="{{ $cycle_name }}" @if($chose_cycle_name==$cycle_name) selected @endif>{{ $cycle_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Loại dịch vụ:</label>
                            <select name="type_handle" id="type_handle" class="form-control change_" style="width: 100%">
                                <option value="0" {{isset($filter['type']) ? $filter['type'] === 0 ? 'selected' : '' : '' }}>Điện
                                </option>
                                <option value="1" {{isset($filter['type']) ? $filter['type'] === 1 ? 'selected' : '' : '' }}>Nước
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Căn hộ:</label>
                            <div ><span>Số căn hộ được chốt số trong kỳ là: </span><strong id="count_apartment">0</strong><strong> / {{@$electric_meters->total()}}</strong></div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="submit" class="btn btn-lg btn-success" >Thiết lập công nợ</button>
                <button type="button" class="btn btn-lg btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
        </form>
    </div>
</div>
