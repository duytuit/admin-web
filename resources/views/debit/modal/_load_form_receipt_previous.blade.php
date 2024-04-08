@if($apartment && $customer)
    <?php 
        $customerInfo = @$customer ? App\Repositories\PublicUsers\PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
        $flag = true;
        if($customerInfo == null) {
            $flag = false;
        }
        $customer_name = $customerInfo->full_name;
        $customer_address = $customerInfo->address;
    ?>
    @if($flag)
        <form class="form-horizontal" method="POST" data-action="{{ route('admin.building.building-info-store') }}" id="create_info">
            <div class="box-body">
                <div class="form-group data_content">
                    <label for="content" class="col-sm-2 control-label">Kỳ tháng</label>
                    <div class="col-sm-10 form-inline">
                        {{-- <input type="text" class="form-control" id="cycle_name" name="cycle_name"> --}}
                        <select name="cycle_month" id="cycle_month" class="form-control">
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
                        <select name="cycle_year" id="cycle_year" class="form-control">
                            <option value="{{\Carbon\Carbon::now()->year - 1}}">{{\Carbon\Carbon::now()->year - 1}}</option>
                            <option value="{{\Carbon\Carbon::now()->year}}" selected="selected">{{\Carbon\Carbon::now()->year}}</option>
                            <option value="{{\Carbon\Carbon::now()->year + 1}}">{{\Carbon\Carbon::now()->year + 1}}</option>
                        </select>
                        <div class="message_zone_data"></div>
                    </div>
                </div>
                <div class="form-group data_content">
                    <label for="content" class="col-sm-2 control-label">Căn hộ</label>
                    <div class="col-sm-10">
                        <input type="hidden" id="apartment_id" name="apartment_id" value="{{ $apartment->id }}">
                        <input type="text" class="form-control" id="apartment_name" name="apartment_name" value="{{ $apartment->name }}">
                        <div class="message_zone_data"></div>
                    </div>
                </div>
                <div class="form-group data_content">
                    <label for="content" class="col-sm-2 control-label">Chủ hộ</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ $customer_name }}">
                        <div class="message_zone_data"></div>
                    </div>
                </div>
                {{-- <div class="form-group data_content">
                    <label for="content" class="col-sm-2 control-label">Địa chỉ</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="customer_address" name="customer_address" value="{{ $customer_address }}">
                        <div class="message_zone_data"></div>
                    </div>
                </div> --}}
                <div class="form-group data_quantity">
                    <label for="quantity" class="col-sm-2 control-label">Dịch vụ</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="service_id" name="service_id" data-url="{{route('api.apartmentserviceprice.index')}}">
                            <?php 
                                $list_services        = $services;
                                $ServiceNoVehicle = collect($list_services);
                                $_serviceNoVehicle = $ServiceNoVehicle->where('bdc_vehicle_id', 0)->all();
                            ?>
                            @foreach ($_serviceNoVehicle as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                            @foreach ($services as $value_1)
                                  <?php 
                                    $vehicle = $value_1->vehicle()->where('status',1)->first();
                                    if($vehicle != null) {
                                        $value_1->name = $vehicle->number;
                                    }
                                  ?>
                                @if($vehicle != null)
                                   <option value="{{ $value_1->id }}">{{ $value_1->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <div class="message_zone_data"></div>
                    </div>
                </div>
                <div class="form-group data_quantity">
                    <label for="quantity" class="col-sm-2 control-label">Giá dịch vụ</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="service_price" name="service_price" value="" autocomplete="off" readonly="readonly">
                        <div class="message_zone_data"></div>
                    </div>
                </div>
                <div class="form-group data_quantity">
                    <label for="quantity" class="col-sm-2 control-label">Từ ngày</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control date_picker" id="from_date_previous" name="from_date_previous" value="{{ @$lastTimePay }}" autocomplete="off">
                        <div class="message_zone_data"></div>
                    </div>
                </div>
                <div class="form-group data_quantity">
                    <label for="quantity" class="col-sm-2 control-label">Đến ngày</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control date_picker" id="to_date_previous" name="to_date_previous" autocomplete="off">
                        <div class="message_zone_data"></div>
                    </div>
                </div>
                <div class="form-group data_quantity">
                    <label for="quantity" class="col-sm-2 control-label">Tiền phát sinh</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control tien_phat_sinh" id="sumery" name="sumery">
                        <div class="message_zone_data"></div>
                    </div>
                </div>
            </div>
        </form>
        <script>
            $('input.date_picker').datepicker({
                autoclose: true,
                dateFormat: "dd-mm-yy"
            }).val();
        </script>
        <script type="text/javascript" src="{{ url('adminLTE/js/validate-form-dxmb.js') }}"></script>
    @else
        <div class="box-body">
            <label>Căn hộ chưa có chủ hộ.</label>
        </div>
    @endif
@else
    <div class="box-body">
        <label>Chưa lựa chọn căn hộ hoặc không tìm thấy chủ căn hộ.</label>
    </div>
@endif
<script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
<script>
    $(document).on('change', '#service_id', function (e) {
        $id = $(this).val();
        showLoading();
        $.ajax({
            url: $(this).attr('data-url') + '/filterById/' + $id,
            type: 'GET',
            success: function (response) {
                hideLoading();
                if(response.error_code == 200) {

                    $('#service_price').val(formatCurrencyV2(response.data.aparmentServicePrice.price.toString()));

                    $('#from_date_previous').val(response.data.aparmentServicePrice.last_time_pay);
                }else{
                    alert(response.message);
                }
            }
        });
    });
</script>