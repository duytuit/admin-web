<div id="thong_tin_lien_he" class="tab-pane">
    <div class="contact-box">
        <div class="col-sm-2">
            <div class="text-center">
                <i class="fa fa-building fa-5x"></i>
                <div class="m-t-xs font-bold">{{ @$building->name }}</div>
                {!! QrCode::size(200)->generate($qr_data); !!}
                <div>
                    <a href="{{ route('admin.ajax.download_qrcode') }}" type="button" class="btn btn-success download_qrcode">
                       Tải QRcode
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <h3><strong>{{ @$building->name }}</strong></h3>
            <p><i class="fa fa-map-marker"></i> {{ @$building->address }}</p>
            <address>
                <strong>Số điện thoại liên hệ</strong><br>

                <i class="fa fa-mobile-phone"></i> {{ @$building->phone }}
            </address>
            <address>
                <strong>Hòm thư</strong><br>

                <i class="fa fa-envelope"></i> {{ @$building->email }}
            </address>
            {{-- <address>
                <strong>Ngày chốt công nợ</strong><br>

                <i class="fa fa-edit"></i>Ngày {{ @$building->debit_date }} hàng tháng
                @php
                $time = time();
                $date = date('d-m-Y', $time);
                $dayOfDate = date("d", $time);
                $checkDate = (int)$dayOfDate >= @$building->debit_date && (int)$dayOfDate < ((int)@$building->debit_date
                    + 3);
                    @endphp
                    @if($checkDate && !$building->debit_active)
                    <a href="" class="btn btn-danger btn-sm">Chốt công nợ</a>
                    @endif
            </address> --}}
            <address>
                <strong>Giới thiệu tòa nhà</strong><br>

                {{ @$building->description }}
            </address>
            <address>
                <strong>Bộ phận giám sát</strong><br>

                {{ @$building->department->name ?? null}}
            </address>
            <address>
                <strong>Người quản lý</strong><br>
                @if(@$building->manager)
                    {{ @$building->manager->getUserInfoId($building->id)->display_name ?? null }}
                @endif
            </address>

        </div>
        <div class="col-sm-4" style="text-align: right">
            <a href="{{ route('admin.building.edit') }}" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i>
                Chỉnh sửa</a>
        </div>
        <div class="clearfix"></div>

    </div>
</div>