<div id="thiet_lap" class="tab-pane">
  <div class=row">
    <div class="box-header">
      <h3 class="box-title">Chỉnh sửa thông tin</h3>
    </div>
    <br><br>
  </div>
  <div class="clearfix"></div>
  <div class="row">
    <div class="box-header">
      <h3 class="box-title">Thêm mới thông tin thanh toán</h3>
    </div>
    <br><br>
    <div class="form-horizontal">
      <form class="form-horizontal" data-action="{{ route('admin.building.updateDepartmentIdAndDebitDate') }}" method="post"
        id="form_update_config">
        {{ csrf_field() }}
        <input type="hidden" name="bdc_building_id" value="{{ @$building->id }}">
        <div class="form-group div_bank_account">
          <label class="col-md-3 control-label">Bộ phận giám sát</label>

          <div class="col-md-6">
            <select name="bdc_department_id" class="form-control">
              <option value="" selected>Chọn bộ phận</option>
              @foreach($departments as $department)
              <option value="{{ $department->id }}" @if($department->id==$building->bdc_department_id) selected @endif>
                {{ $department->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group div_bank_name">
          <label class="col-md-3 control-label">Ngày chốt công nợ</label>

          <div class="col-md-6">
            <input type="number" class="form-control valid" id="debit_date" name="debit_date"
              value="{{@$building->debit_date }}">
          </div>
        </div>

        <div class="form-group">
          <div class="col-md-6 col-md-offset-4">
            <button type="button" class="btn btn-primary" id="update_config">
              <i class="fa fa-btn fa-check"></i> Update
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>