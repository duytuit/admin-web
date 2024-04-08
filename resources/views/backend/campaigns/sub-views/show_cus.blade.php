<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr class="bg-primary">
           {{-- <th width="20px">
                <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
            </th>--}}
            <th width="30">#</th>
            <th width="">Khách hàng</th>
            <th width="10%">SĐT</th>
            <th width="15%">Email</th>
            <th width="12%">Nguồn khách hàng</th>
            <th width="10%">Mức độ</th>
            <th width="15%">Sale</th>
{{--            <th width="10%">Thao tác</th>--}}
        </tr>
        </thead>
        <tbody>
        @foreach($customers as $customer)
            <tr>
{{--                <td><input type="checkbox" class="iCheck checkSingle" value="{{$customer->id}}" name="ids[]" /></td>--}}
                <td>{{ $customer->id }}</td>
                <td>
                    <a href='javascript:;'> {{ $customer->customer_name }} </a>
                </td>
                <td>{{ $customer->customer_phone }} </td>
                <td>{{ $customer->customer_email }} </td>
                <td class="text-uppercase">{{ $customer->source ?: '' }} </td>
                <td>
                    @if ($customer->status !== null)
                        <a href="javascript:;" class="btn-status label label-sm label-{{ $customer->feedback == 1 ? 'success' : 'danger' }}">
                            {{ $customer->feedback == 1 ? 'Quan tâm' : 'Không quan tâm' }}
                        </a>
                    @endif

                </td>
                <td>{{ $customer->staff->ub_account_tvc ?? 'Sale' }} </td>
                {{--<td>
                    @if ($customer->check_diary === 0)
                        <a href='{{ route("admin.campaign_assign.edit_diary", ['id' => $customer->id]) }}' type="button" class="btn btn-sm btn-warning" title="Phản hồi" data-diary="0" data-assigned="{{ $customer->id }}">
                            <i class="fa fa-weixin"></i>
                        </a>
                    @else
                        <a href='javascript:;' class="btn btn-sm btn-success js-btn-add-edit-diary" title="Xem phản hồi" data-diary='{{$customer->campaign->diary_id}}' data-toggle="modal" data-target="#campaign-assign-diary">
                            <i class="fa fa-eye"></i>
                        </a>
                    @endif
                </td>--}}
            </tr>
        @endforeach
        </tbody>
    </table>
</div>