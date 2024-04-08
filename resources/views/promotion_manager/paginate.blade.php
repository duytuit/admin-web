<form id="form_action" action="{{route('admin.v2.bill.action') }}" method="post">
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered">
            <thead class="bg-primary">
                <tr>
                    <th>STT</th>
                    <th>Tên KM</th>
                    <th>Loại KM</th>
                    <th>Thời gian áp dụng</th>
                    <th>Giá trị KM</th>
                    <th>Người tạo</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th>Tác vụ</th>
                </tr>
            </thead>
            <tbody>
                @if(@$promotion_managers)
                    @foreach($promotion_managers as $key => $promotion_manager)
                        <tr>
                            <td></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    {{-- <div class="row mbm">
        <div class="col-sm-3">
            <span class="record-total">Hiển thị {{ $promotion_managers->count() }} / {{ $promotion_managers->total() }} kết quả</span>
        </div>
        <div class="col-sm-6 text-center">
            <div class="pagination-panel">
                {{ $promotion_managers->appends(request()->input())->links() }}
            </div>
        </div>
        <div class="col-sm-3 text-right">
        <span class="form-inline">
            Hiển thị
            <select name="per_page" class="form-control" data-target="#form_action">
                @php $list = [10, 20, 50, 100, 200]; @endphp
                @foreach ($list as $num)
                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                @endforeach
            </select>
        </span>
        </div>
    </div> --}}
</form>