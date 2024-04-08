<div role="tabpanel" class="tab-pane" id="images">
    @php
    $images = old('images', $article->images);
    $images = is_array($images) ? $images : [];
    @endphp

    <input id="image_index" type="hidden" value="{{ count($images) }}">

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th width="150">Hình ảnh</th>
                    <th>Thứ tự</th>
                    <th width="120">Tác vụ</th>
                </tr>
            </thead>
            <tbody>
                @if ($images)
                @foreach ($images as $item)
                <tr>
                    <td>
                        <img src="{{ $item['src'] ?? '/images/no-img-xs.jpg' }}" width="100" height="100" alt="no image">
                    </td>
                    <td>
                        <input type="hidden" class="input-image" name="images[{{ $loop->index }}][src]" value="{{ $item['src'] ?? '' }}">
                        <input type="text" class="form-control" name="images[{{ $loop->index }}][sort_order]" value="{{ $item['sort_order'] ?? 0 }}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary btn-select"><i class="fa fa-image"></i></button>
                        <button type="button" class="btn btn-danger btn-remove"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><button type="button" class="btn btn-success btn-add"><i class="fa fa-plus"></i> Thêm ảnh</button></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>