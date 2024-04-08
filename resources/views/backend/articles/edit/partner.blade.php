<div class="form-group">
    <label class="control-label required">Đối tác</label>
    <select name="partner_id" class="form-control select2" required>
        @php $partner_id = old('partner_id', $article->partner_id); @endphp
        @foreach ($partners as $item)
        <option value="{{ $item->id }}" {{ $item->id == $partner_id ? 'selected' : '' }}>{{ $item->name }}</option>
        @endforeach
    </select>
</div>