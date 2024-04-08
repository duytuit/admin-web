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
                    <div class="col-sm-6">
                        <label for="content" class="control-label">Căn hộ</label>
                        <input type="hidden" id="apartment_id" name="apartment_id" value="{{ $apartment->id }}">
                        <input type="text" class="form-control" id="apartment_name" name="apartment_name" value="{{ $apartment->name }}">
                        <div class="message_zone_data"></div>
                    </div>
                    <div class="col-sm-6">
                        <label for="content" class="control-label">Chủ hộ</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ $customer_name }}">
                        <div class="message_zone_data"></div>
                    </div>
                </div>
                <div class="form-group data_quantity">
                    <div class="col-sm-12">
                        <label for="quantity" class="control-label">Dịch vụ</label>
                        <select class="form-control" id="service_id" name="service_id" data-url="{{route('api.apartmentserviceprice.index')}}">
                            <?php 
                                $list_services        = $services;
                                $ServiceNoVehicle = collect($list_services);
                                $_serviceNoVehicle = $ServiceNoVehicle->where('bdc_vehicle_id', 0)->all();
                            ?>
                            <option value="">Chọn dịch vụ</option>
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
                    <div class="col-sm-4">
                        <label for="quantity" class="control-label">Giá dịch vụ</label>
                        <input type="text" class="form-control" id="service_price" name="service_price" value="" autocomplete="off" readonly="readonly">
                        <div class="message_zone_data"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="quantity" class="control-label">Ngày tính phí tiếp theo</label>
                        <input type="text" class="form-control" id="from_date_previous" name="from_date_previous" autocomplete="off" readonly="readonly">
                        <input type="hidden"  id="cycle_name_debit">
                        <div class="message_zone_data"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="quantity" class="control-label">Ngày chốt</label>
                        <input type="number" class="form-control" id="ngay_chot" name="ngay_chot" autocomplete="off"  readonly="readonly">
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
<script>
    $(document).on('change', '#service_id', function (e) {
        $id = $(this).val();
        if(!$id){
            return;
        }
        showLoading();
        $.ajax({
            url: $(this).attr('data-url') + '/filterById/' + $id,
            type: 'GET',
            success: function (response) {
                hideLoading();
                if(response.error_code == 200) {
                    $('#service_price').val(response.data.aparmentServicePrice.price);
                    $('#from_date_previous').val(response.data.aparmentServicePrice.last_time_pay);
                    $('#ngay_chot').val(response.data.aparmentServicePrice.ngay_chot);
                    $('#cycle_name_debit').val(response.data.aparmentServicePrice.cycle_name);
                }else{
                    alert(response.message);
                }
            }
        });
    });
</script>