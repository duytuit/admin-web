<div role="tabpanel" class="tab-pane" id="voucher">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Ngày bắt đầu</label>
                <div class="input-group datetimepicker">
                    <input type="text" name="start_at" value="{{ $post->start_at ? $post->start_at->format('d-m-Y H:i:s') : '' }}" class="form-control" placeholder="Ngày bắt đầu"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Ngày kết thúc</label>
                <div class="input-group datetimepicker">
                    <input type="text" name="end_at" value="{{ $post->end_at ? $post->end_at->format('d-m-Y H:i:s') : '' }}" class="form-control" placeholder="Ngày kết thúc"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Mã khuyến mại</label>
                <textarea name="voucher_code" placeholder="Mã khuyến mại" rows="1" class="form-control input-text">{{ $post->voucher_code }}</textarea>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Số lượng</label>
                <textarea name="number" placeholder="Số lượng" rows="1" class="form-control input-text">{{ $post->number }}</textarea>
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
                    $qr_data = [
                        'info' => 'Bạn đang sử dụng voucher: ' . $post->title,
                        'url' => str_after(route('api.v1.posts.checkIn', ['post_id' => $post->id]), url('/api/v1'))
                    ] 
                    @endphp

                    {!! QrCode::size(300)->generate(json_encode($qr_data)); !!}
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">KQ Khuyến mại</label>
                <div>
                    <a href="{{ route('admin.posts.registers', ['id' => $id, 'type' => $type]) }}" class="btn btn-success" target="_blank"><i class="fa fa-calendar"></i> KQ Khuyến mại</a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div><!-- END #event -->