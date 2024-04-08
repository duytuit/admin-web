@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Cẩm nang tòa nhà
        <small>Cập nhật</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Cẩm nang tòa nhà</li>
    </ol>
</section>

<section class="content">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ ($id == 0) ? route('admin.building-handbook.type.store') : route('admin.building-handbook.type.update', ['id' => $id]) }}" method="post" id="form-edit-add-category" class="form-validate">
      {{ csrf_field() }}
      <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="box no-border-top">
                  <div class="box-body no-padding">
                      <div class="nav-tabs-custom no-margin">

                          <div class="tab-content">
                              <!-- Thông tin cẩm nang -->
                              <div class="tab-pane active" id="partner">
                                  <div class="row">
                                      <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('name') ? 'has-error': '' }}">
                                          <label class="control-label">Tên <span class="text-danger">*</span></label>
                                          <input type="text" class="form-control" name="name" placeholder="Tên" value="{{ $bdh_type->name ?? old('name') ?? $bdh_type->name ?? ''}}" />
                                      </div>
                                  </div>
                                  <div class="row">
                                      <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('type_company') ? 'has-error': '' }}">
                                          <label class="control-label">Type company<span class="text-danger"> *</span></label>
                                               <select name="type_company" id="type_company" class="form-control">
                                                        <option value="" selected>Chọn</option>
                                                    @if($id == 0)
                                                         @foreach ($type_companys as $value)
                                                           <option value="{{ $value }}"> {{ $value }}</option>
                                                         @endforeach
                                                    @else
                                                         @foreach ($type_companys as $value)
                                                           <option value="{{ $value }}" @if($value == $bdh_type->type_company) selected @endif> {{ $value }}</option>
                                                         @endforeach
                                                    @endif
                                                    
                                                </select>
                                      </div>
                                   </div>
                              </div>
                          </div>
                          <div class="box-footer">
                            <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-category"><i class="fa fa-save"></i>&nbsp;&nbsp;{{!empty($bdh_type) ? 'Cập nhật' : 'Thêm mới'}}</button>
    
                            <a href="{{ route('admin.building-handbook.type.index') }}" class="btn btn-danger btn-sm"><i class="fa fa-reply"></i> Quay lại</a>
                        </div>
                      </div>
                  </div>
              </div>
            </div>
      </div>
    </form>
              
</section>
@endsection

@section('javascript')

<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

@endsection