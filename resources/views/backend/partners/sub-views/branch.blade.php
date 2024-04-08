@if(!empty($branches->toArray()['data']))
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr class="bg-primary">
            <th width='15px'>STT</th>
            <th>Tên Chi nhánh</th>
            <th width='15%'>Người đại diện</th>
            <th width='15%'>Hotline</th>
            <th width='20%'>Info</th>
            <th width='20%'>Địa chỉ</th>
        </tr>
    </thead>
    <tbody>
        @php
        $index = ($branches->perPage() * ($branches->currentPage() - 1));
        @endphp
        @foreach($branches as $branche)
        <tr>
            <td class="text-center">{{ $index += 1 }}</td>
            <td>
                <a href='{{ url("/admin/branches/edit/{$branche->id}") }}'> {{ $branche->title }} </a></td>
            <td>{{ $branche->representative }}</td>
            <td>{{ $branche->hotline }}</td>
            <td>{!! $branche->info !!}</td>
            <td>{!! $branche->address !!}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="pull-right link-paginate">
    {{ $branches->links() }}
</div>
@else
    Hiện chưa có chi nhánh nào.
@endif