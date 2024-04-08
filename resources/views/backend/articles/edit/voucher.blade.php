<div role="tabpanel" class="tab-pane" id="voucher">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Ngày bắt đầu</label>
                <div class="input-group datetimepicker">
                    <input type="text" name="start_at" value="{{ $article->start_at ? $article->start_at->format('d-m-Y H:i:s') : '' }}" class="form-control" placeholder="Ngày bắt đầu"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Ngày kết thúc</label>
                <div class="input-group datetimepicker">
                    <input type="text" name="end_at" value="{{ $article->end_at ? $article->end_at->format('d-m-Y H:i:s') : '' }}" class="form-control" placeholder="Ngày kết thúc"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Mã khuyến mại</label>
                <textarea name="voucher_code" placeholder="Mã khuyến mại" rows="1" class="form-control input-text">{{ $article->voucher_code }}</textarea>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Số lượng</label>
                <textarea name="number" placeholder="Số lượng" rows="1" class="form-control input-text">{{ $article->number }}</textarea>
            </div>
        </div>
    </div>


    @if ($id)
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">QR Code</label>
                <div class="qr-code">
                    @php
                    $user_id = 1;
                    $token = encrypt("user_id=$user_id");
                    $qr_url = route('articles.vouchers.register', ['id' => $id, 'token' => $token]);
                    $logo = base_path('public/images/logo.jpg');
                    @endphp

                    {!! QrCode::size(300)->generate($qr_url); !!}
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">KQ Khuyến mại</label>
                <div>
                    <a href="{{ route('admin.articles.vouchers', ['id' => $id, 'type' => $type]) }}" class="btn btn-success" target="_blank"><i class="fa fa-calendar"></i> KQ Khuyến mại</a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div><!-- END #event -->