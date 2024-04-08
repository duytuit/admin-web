<select name="name_service_aparmtent" class="form-control">
    @if ($apartment_service)
        @foreach($apartment_service as $value)
            <option value="{{ $value->id }}" >{{ $value->name }}</option>
        @endforeach
    @endif
</select>