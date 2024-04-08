<div class="modal fade" id="showModalYear" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form action="{{route('admin.v2.debit.detail-handling-year')}}" method="POST">
                    @csrf
                    <div class="box-header with-border text-center">
                        <h5 class="box-title"><b>Khoản phí</b></h5><br><br>
                        Kỳ năm
                        <select name="cycle_year">
                            <option value="{{\Carbon\Carbon::now()->year - 1}}">{{\Carbon\Carbon::now()->year - 1}}</option>
                            <option value="{{\Carbon\Carbon::now()->year}}" selected="selected">{{\Carbon\Carbon::now()->year}}</option>
                            <option value="{{\Carbon\Carbon::now()->year + 1}}">{{\Carbon\Carbon::now()->year + 1}}</option>
                        </select>
                        <br><br>
                        <div class="col-sm-3"></div>
                        <div class="form-group col-sm-6">
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
                        <div class="col-sm-3"></div>
                    </div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th> <input type="checkbox" class="iCheck checkAll" data-target=".checkSingle"/>  Tất cả</th>
                            <th style="text-align: center">Miễn phí</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($serviceBuildings_cycle_year as $serviceBuilding)
                            <tr>
                                <td><input type="checkbox" name="ids[{{$serviceBuilding->id}}]" value="{{$serviceBuilding->id}}"
                                           class="iCheck checkSingle"/>  {{$serviceBuilding->name}}</td>
                                <td style="text-align: center">
                                    <input type="checkbox" name="frees[{{$serviceBuilding->id}}]" value="0" class="checkboxes frees check-check"/>
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
