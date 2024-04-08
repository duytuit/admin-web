@if ($apartment_service)
    <select name="name_service_aparmtent" class="form-control">
            @foreach($apartment_service as $value)
                <option value="{{ $value->id }}" >{{ $value->name }}</option>
            @endforeach
    </select>
@endif