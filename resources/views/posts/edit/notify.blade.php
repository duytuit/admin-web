@php
$notify = old('notify', $post->notify);
$notify = is_array($notify) ? $notify : [];

$all_selected = $notify['all_selected'] ?? 0;

$group_selected = $notify['group_selected'] ?? 0;
$group_ids = $notify['group_ids'] ?? [];

$customer_selected = $notify['customer_selected'] ?? 0;
$customer_ids = $notify['customer_ids'] ?? [];

$send_mail = $notify['send_mail'] ?? 0;
$send_sms = $notify['send_sms'] ?? 0;
$send_app = $notify['send_app'] ?? 0;
@endphp

<div class="form-group">
    <label class="control-label">Gửi đến</label>
    <div class="notify-group">
        <label class="notify-label">
            <input type="checkbox" name="notify[send_mail]" value="1" class="iCheck" {{ $send_mail ? 'checked' : '' }}>
            Email
        </label>
        <label class="notify-label">
            <input type="checkbox" name="notify[send_sms]" value="1" class="iCheck" {{ $send_sms ? 'checked' : '' }}>
            SMS
        </label>
        <label class="notify-label">
            <input type="checkbox" name="notify[send_app]" value="1" class="iCheck" {{ $send_app ? 'checked' : '' }}>
            App Notify
        </label>
    </div>
</div>

<div class="form-group">
    <label class="control-label">Thông tin</label>
    <div class="notify-group">
        @php
        $private = ($id == 0) || ($old ? old('private') : $post->private);
        @endphp
        <label class="private-label" data-visible="show" data-target="#private">
            <input type="radio" name="private" value="1" class="iCheck input-private" {{ $private ? 'checked' : '' }}>
            Nội bộ
        </label>
        <label class="private-label" data-visible="hide" data-target="#private">
            <input type="radio" name="private" value="0" class="iCheck input-private" {{ $private ? '' : 'checked' }}>
            Công khai
        </label>
    </div>
</div>

<div class="form-group" id="private" style="display: {{ $private ? 'block' : 'none' }}">
    <label class="control-label">KH nhận tin</label>
    <div class="notify-group">
        <label>
            <input type="checkbox" name="notify[all_selected]" value="1" class="iCheck" {{ $all_selected ? 'checked' : '' }}> Tất cả
        </label>
    </div>
    <div class="notify-group">
        <label data-toggle="show" data-target="#notify-group">
            <input type="checkbox" name="notify[group_selected]" value="1" class="iCheck" {{ $group_selected ? 'checked' : '' }}> Nhóm KH
        </label>
        <div id="notify-group" style="display: {{ $group_selected ? 'block' : 'none' }}; margin: 5px 0px 15px;">
            <select name="notify[group_ids][]" class="form-control select2" style="width: 100%;" multiple>
                @foreach ($groups as $item)
                <option value="{{ $item->id }}" {{ in_array($item->id, $group_ids) ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="notify-group">
        <label data-toggle="show" data-target="#notify-customer">
            <input type="checkbox" name="notify[customer_selected]" value="1" class="iCheck" {{ $customer_selected ? 'checked' : '' }}> Cá nhân
        </label>
        <div id="notify-customer" style="display: {{ $customer_selected ? 'block' : 'none' }}; margin: 5px 0px 15px;">
            <select id="customer_ids" name="notify[customer_ids][]" class="form-control" style="width: 100%;" multiple>
                @foreach ($customers as $item)
                <option value="{{ $item->cb_id }}" selected>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>