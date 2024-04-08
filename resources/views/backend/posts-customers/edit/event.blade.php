<div role="tabpanel" class="tab-pane" id="event">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Ngày bắt đầu</label>
                <div class="input-group datetimepicker">
                    <input type="text" name="start_at" value="{{ old('start_at', $post->start_at ?? $now) }}" class="form-control" placeholder="Ngày bắt đầu"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Ngày kết thúc</label>
                <div class="input-group datetimepicker">
                    <input type="text" name="end_at" value="{{ old('end_at', $post->end_at ?? $now) }}" class="form-control" placeholder="Ngày kết thúc"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label">Địa điểm</label>
        <textarea name="address" placeholder="Địa điểm" rows="3" class="form-control">{{ old('address', $post->address) }}</textarea>
    </div>
    @if ($id)
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label">QR Code đăng ký</label>
                    <div class="qr-code">
                        @php
                            $qr_data = [
                            'info' => 'Bạn vừa đăng ký tham gia sự kiện: ' . $post->title,
                            'url' => route('api.v1.addRegister', ['post_id' => $post->id,'building_id'=>$building_id,'type'=>'event'])
                            ]
                        @endphp

                        {!! QrCode::size(300)->generate(json_encode($qr_data)); !!}
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label">QR Code check in</label>
                    <div class="qr-code">
                        @php
                            $qr_checkin = [
                            'info' => 'Bạn đang check in tham gia sự kiên: ' . $post->title,
                            'url' => route('api.v1.checkIn', ['post_id' => $post->id,'building_id'=>$building_id])
                            ]
                        @endphp

                        {!! QrCode::size(300)->generate(json_encode($qr_checkin)); !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label">KQ Sự kiện</label>
            <div>
                @if( in_array('admin.posts.registers',@$user_access_router))
                    <a href="{{ route('admin.posts.registers', ['id' => $id, 'type' => $type]) }}" class="btn btn-success" target="_blank"><i class="fa fa-calendar"></i> KQ Sự kiện</a>
                @endif
            </div>
        </div>
    @endif
</div><!-- END #event -->