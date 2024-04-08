<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form action="{{route('admin.debit.detail-handling')}}" method="POST">
                    @csrf
                    <div class="box-header with-border">
                        <div class="form-group text-center" style="width: 50%; margin: 0 auto;">
                        <h5 class="box-title"><b>Khoản phí</b></h5><br><br>
                        Kỳ tháng  
                        <select name="cycle_month">
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
                        <select name="cycle_year">
                            <option value="{{\Carbon\Carbon::now()->year - 1}}">{{\Carbon\Carbon::now()->year - 1}}</option>
                            <option value="{{\Carbon\Carbon::now()->year}}" selected="selected">{{\Carbon\Carbon::now()->year}}</option>
                            <option value="{{\Carbon\Carbon::now()->year + 1}}">{{\Carbon\Carbon::now()->year + 1}}</option>
                        </select>
                        </div>

                        <div class="form-group" style="width: 50%; margin: 0 auto;">
                            <label>Ngày thanh toán:</label>
                            <div>
                                <input type="text" placeholder="ngày thanh toán" class="form-control pull-right date_picker"
                                       name="payment_deadline"
                                       id="datepicker" value="{{date('d-m-Y',strtotime($paymentDeadlineBuilding))}}">
                            </div>
                            <!-- /.input group -->
                        </div>

                    </div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th> <input type="checkbox" class="iCheck checkAll" data-target=".checkSingle"/>  Tất cả</th>
                            <th>Ngày</th>
                            {{-- <th><input id="myCheckAll" type="checkbox" class="myCheckAll check-check"/>  Miễn phí</th> --}}
                            <th style="text-align: center">Miễn phí</th>
                            <th style="text-align: center">Xử lý lại</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($serviceBuildings_cycle_month as $serviceBuilding)
                            <tr>
                                <td><input type="checkbox" name="ids[{{$serviceBuilding->id}}]" value="{{$serviceBuilding->id}}"
                                           class="iCheck checkSingle"/>  {{$serviceBuilding->name}}</td>
                                <?php $submonth = \Carbon\Carbon::now()->subMonth(1)->toDateString();
                                $billDateDay = $serviceBuilding->bill_date;
                                $daySubMonth = \Carbon\Carbon::now()->subMonth(1)->day;
                                $billDate = \Carbon\Carbon::now()->subMonth(1)->subDays($daySubMonth)->addDays($billDateDay)->format('d-m-Y');?>
                                <td>
                                    <input type="text" class="form-control date_picker" placeholder="ngày bắt đầu" name="bill_date[{{$serviceBuilding->id}}]" value="{{$billDate}}">
                                </td>
                                <td style="text-align: center">
                                    <input type="checkbox" name="frees[{{$serviceBuilding->id}}]" value="0" class="checkboxes frees check-check"/>
                                </td>
                                <td style="text-align: center">
                                    <input type="checkbox" name="process_again[{{$serviceBuilding->id}}]" value="0" class="checkboxes frees check-check"/>
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
