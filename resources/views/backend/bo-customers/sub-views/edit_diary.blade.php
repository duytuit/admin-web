<div class="alert alert-danger print-error-msg" style="display:none">
    <ul></ul>
</div>

<input type="hidden" name="diary[cd_id]" value="{{ $diary ? $diary->cd_id : '' }}" {{ $diary ? '' : 'disabled' }}/>
<input type="hidden" name="diary[cd_customer_id]" value="{{ $customer_id }}" />

<div class="form-group">
    <label class="col-sm-3 control-label" style="padding-top: 0px;">Dự án quan tâm <span class="text-danger">*</span></label>
    <div class="col-sm-9">
        <select class="form-control select2" id="select-project-edit-diary" name="diary[project_id]" style="width: 100%">
            <option value="">Chọn dự án</option>
            @foreach ($projects as $project)
            <option value="{{ $project->cb_id }}" @if($diary && $diary->project_id == $project->cb_id) selected @endif >{{ $project->cb_title }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label" style="padding-top: 0px;">KH phản hồi</label>
    <div class="col-sm-9">
        <label style="margin-right: 15px;">
            <input type="radio" name="diary[status]" value="1" class="iCheck" @if( ($diary && $diary->status == 1) || !$diary )  checked @endif />
            Quan tâm
        </label>
        <label>
            <input type="radio" name="diary[status]" value="0" class="iCheck" @if( $diary && $diary->status == 0) checked @endif />
            Không quan tâm
        </label>
    </div>
</div>


<div class="form-group">
    <label class="col-sm-3 control-label">Điểm số <span class="text-danger">*</span></label>
    <div class="col-sm-9" style="font-size: 12px;>
        <div class="rating">
            <input id="input-1" name="diary[cd_rating]" class="rating rating-loading" data-min="0" data-max="5" data-step="1" value=" {{ $diary ? $diary->cd_rating : '5' }}" data-size="xs">
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label" style="padding-top: 0px;">Ghi chú</label>
    <div class="col-sm-9">
        <textarea class="miniEditor form-control" name="diary[cd_description]">{{ $diary ? $diary->cd_description : '' }}</textarea>
    </div>
</div>


{{-- rating --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/js/star-rating.min.js"></script>
<script type="text/javascript" src="/adminLTE/plugins/select2/js/select2.full.min.js"></script>

<script type="text/javascript">
$("input.rating").rating();
</script>
{{-- TinyMCE --}}
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

<script>
//Icheck
$('input.iCheck').iCheck({
    checkboxClass: 'icheckbox_square-green',
    radioClass: 'iradio_square-green',
    increaseArea: '20%' // optional
});

// Chọn dự án cho khách hàng
$(function() {
    $('#select-project-edit-diary').select2({
        ajax: {
            url: '{{ url("/admin/bo-customers/ajax/get-all-project") }}',
            dataType: 'json',
            data: function(params) {
                var query = {
                    search: params.term,
                }
                return query;
            },
            processResults: function(json, params) {
                var results = [];

                for (i in json.data) {
                    var item = json.data[i];
                    results.push({
                        id: item.cb_id,
                        text: item.cb_title
                    });
                }
                return {
                    results: results,
                };
            },
            minimumInputLength: 3,
        }
    });
});

</script>