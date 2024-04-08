<div role="tabpanel" class="tab-pane" id="attaches">
    @php
    $attaches = old('attaches', $article->attaches);
    $attaches = is_array($attaches) ? $attaches : [];
    @endphp

    <input id="attach_index" type="hidden" value="{{ count($attaches) }}">

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th>Đính kèm</th>
                    <th width="100">Thứ tự</th>
                    <th width="100">Tác vụ</th>
                </tr>
            </thead>
            <tbody>
                @if ($attaches)
                @foreach ($attaches as $item)
                <tr>
                    <td>
                        <input type="text" class="form-control input-attach" name="attaches[{{ $loop->index }}][src]" value="{{ $item['src'] ?? '' }}">
                    </td>
                    <td>
                        <input type="text" class="form-control" name="attaches[{{ $loop->index }}][sort_order]" value="{{ $item['sort_order'] ?? 0 }}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary btn-select"><i class="fa fa-upload"></i></button>
                        <button type="button" class="btn btn-danger btn-remove"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><button type="button" class="btn btn-success btn-add"><i class="fa fa-plus"></i> Thêm file</button></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>