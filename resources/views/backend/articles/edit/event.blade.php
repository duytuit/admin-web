<div role="tabpanel" class="tab-pane" id="event">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Ngày bắt đầu</label>
                <div class="input-group datetimepicker">
                    <input type="text" name="start_at" value="{{ old('start_at', $article->start_at ?? $now) }}" class="form-control" placeholder="Ngày bắt đầu"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Ngày kết thúc</label>
                <div class="input-group datetimepicker">
                    <input type="text" name="end_at" value="{{ old('end_at', $article->end_at ?? $now) }}" class="form-control" placeholder="Ngày kết thúc"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label">Địa điểm</label>
        <textarea name="address" placeholder="Địa điểm" rows="3" class="form-control">{{ old('address', $article->address) }}</textarea>
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
                    $qr_url = route('articles.events.register', ['id' => $id, 'token' => $token]);
                    $logo = base_path('public/images/logo.jpg');
                    @endphp

                    {!! QrCode::size(300)->generate($qr_url); !!}
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">KQ Sự kiện</label>
                <div>
                    <a href="{{ route('admin.articles.events', ['id' => $id, 'type' => $type]) }}" class="btn btn-success" target="_blank"><i class="fa fa-calendar"></i> KQ Sự kiện</a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div><!-- END #event -->