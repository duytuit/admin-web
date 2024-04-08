<div class="form-group">
    <div class="col-md-12">
        @php
            $sum_total = collect($coin_apartment)->sum('coin'); 
        @endphp
        <input type="hidden" id="apartment_id" value="{{@$apartment->id}}" />
        <p><strong>Căn hộ: {{@$apartment->name}}</strong></p>
        <p>Tổng tiền thừa: {{number_format($sum_total, 0, '', ',')}}</p>
        @foreach ($coin_apartment as $item)
             <div>{{ $item->bdc_apartment_service_price_id == 0 ? 'Chưa chỉ định' : (@$item->apartmentServicePrice->bdc_vehicle_id > 0 ? @$item->apartmentServicePrice->vehicle->number : @$item->apartmentServicePrice->service->name) }} : {{ number_format($item->coin, 0, '', ',')}}</div>
        @endforeach
        <p></p>
    </div>
</div>
<div class="form-group">
    <div class="col-md-12">
        <button type="button" class="btn btn-sm btn-primary add_phan_bo_item">
            <i class="fa fa-plus" aria-hidden="true"> Thêm phân bổ mới</i>
        </button>
    </div>
</div>
<div class="form-group">
    <div class="card-body phan_bo_list">
    </div>
</div>
<script type="text/javascript" src="{{ url('adminLTE/js/main.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/custom.js') }}"></script>