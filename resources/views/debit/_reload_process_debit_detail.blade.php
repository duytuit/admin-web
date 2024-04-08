@if($data != null)
    @foreach ($data as $item)
        <tr>
        <td>{{$item["apartment_id"]}}</td>
        <td>{{$item["apartment_name"]}}</td>
        <td>{{$item["service_id"]}}</td>
        <td>{{$item["service_name"]}}</td>
        <td>{{$item["message"]}}</td>
        </tr>
    @endforeach
@endif