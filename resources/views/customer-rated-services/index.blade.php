@extends('layouts.app_v2')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div>
                <div>
                    <div class="box-header">
                        <img src="{{ asset('adminLTE/img/banner-danhgidv-01.jpg') }}" style="max-width: 100%;"
                            class="header_img" alt="no image">
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form id="form_audit" role="form" style="display: grid;
                        justify-content: center;">
                        @php
                          if(Request::has('employee_id')){
                            $check_value = explode('_',Request::get('employee_id'));
                            $employee_id = Request::get('employee_id') ? $check_value[0] : null;
                            $department_id = count($check_value) > 1 ? $check_value[1] : null;
                          }
                          if(Request::has('department_id')){
                            $check_value = explode('_',Request::get('department_id'));
                            $department_id = Request::get('department_id') ? $check_value[0] : null;
                            $building_id = count($check_value) > 1 ? $check_value[1] : null;
                          }
                        @endphp
                        <input type="hidden" name="employee_id" value="{{@$employee_id}}" />
                        <input type="hidden" name="department_id" value="{{@$department_id}}" />
                        <input type="hidden" name="building_id" value="{{@$building_id}}" />
                        <input type="hidden" name="user_id" value="{{ @$user_id }}" />
                        <input type="hidden" name="apartment_id" value="{{ Request::get('apartment_id') }}" />
                        <div class="box-body form-audit-service">
                          
                            @if(!$user_id)
                                <p class="text-center"><strong>Cho biết thông tin của bạn?</strong></p>
                                <p class="text-center">Thông tin người được đánh giá</p>
                                <div class="form-group div_customer" style="position: relative;">
                                    <input type="text" class="form-control input-audit-border-none" name="customer" id="exampleInputEmail1"
                                        placeholder="Tên khách hàng *">
                                    <i class="fa fa-user pos-abs"></i>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_phone" style="position: relative;">
                                    <input type="text" class="form-control input-audit-border-none" name="phone" id="exampleInputEmail1"
                                        placeholder="Số điện thoại *">
                                    <i class="fa fa-phone pos-abs"></i>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_apartment_name" style="position: relative;">
                                    <input type="text" class="form-control input-audit-border-none" name="apartment_name" id="exampleInputPassword1"
                                        placeholder="Căn hộ *">
                                    <i class="fa fa-home pos-abs"></i>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" name="vang_lai" value="2" class="iCheck_Red"/>
                                    <span> Vãng lai <span style="font-style: italic;">(nếu bạn không phải là cư dân)</span></span>
                                </div>
                            @endif
                            @if(@$departments)
                            <div class="form-group">
                                <p><strong>Bạn muốn đánh giá chất lượng, dịch vụ</strong></p>
                                <p><strong>của bộ phận nào?</strong></p>
                            </div>
                            <div class="row">
                                @foreach ($departments as $key => $item)
                                    @if ($key == 0)
                                        <div class="column">
                                            <input type="radio" name="department_ids[]" value="{{$item->id}}"  checked  class="iCheck_Red"/>
                                            <span>{{$item->name}}</span>
                                        </div>
                                    @else
                                        <div class="column">
                                            <input type="radio" name="department_ids[]" value="{{$item->id}}"  class="iCheck_Red"/>
                                            <span>{{$item->name}}</span>
                                        </div>
                                    @endif
                                  
                                @endforeach
                            </div>
                            @endif
                            <p class="text-center"><strong>Xin quý khách cho biết mức độ hài</strong></p>
                            <p class="text-center"><strong>lòng về dịch vụ của chúng tôi</strong></p>
                            <p class="text-center">Chọn một trong các tiêu chí sau đây:</p>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="col-sm-6 label-center">
                                        <input type="radio" id="control_01" name="danh_gia" value="-3">
                                        <label class="audit-label" for="control_01">
                                            <p class="text-center-normal">&#128545; Rất không hài lòng</p>
                                        </label>
                                    </div>
                                    <div class="col-sm-6 label-center">
                                        <input type="radio" id="control_02" name="danh_gia" value="-1">
                                        <label class="audit-label" for="control_02">
                                            <p class="text-center-normal">&#128532; Chưa hài lòng</p>
                                        </label>
                                    </div>
                                    <div class="col-sm-6 label-center">
                                        <input type="radio" id="control_03" name="danh_gia" value="1">
                                        <label class="audit-label" for="control_03">
                                            <p class="text-center-normal">&#128528; Bình thường</p>
                                        </label>
                                    </div>
                                    <div class="col-sm-6 label-center">
                                        <input type="radio" id="control_04" name="danh_gia" value="3">
                                        <label class="audit-label" for="control_04">
                                            <p class="text-center-normal">&#128578; Hài lòng</p>
                                        </label>
                                    </div>
                                    <div style="width: 100%;display: flex;justify-content: center">
                                        <div class="col-sm-6">
                                            <input type="radio" id="control_06" name="danh_gia" value="5" checked>
                                            <label class="audit-label" for="control_06">
                                                <p class="text-center-normal">&#128077; Rất hài lòng</p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" rows="3" name="y_kien_khac" placeholder="Góp ý"
                                    style="border-radius: 10px;resize: none;"></textarea>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <div class="form-group">
                                {{-- <button type="submit" class="btn btn-block btn-danger btn-lg sub_danh_gia"
                                    style="border-radius: 5px;">Gửi đánh giá</button> --}}
                                  <a href="javascript:;" class="btn btn-block btn-danger btn-lg sub_danh_gia" style="border-radius: 5px;">Gửi đánh giá</a>   
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="createDebitDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <label class="audit-label-header"></label>
            <div class="modal-content">
                <div class="modal-body debit_detail_content">
                    <br>
                    <div class="form-group">
                        <p class="text-center"><strong>Quý khách đã đánh giá thành công!</strong></p>
                        <div class="text-center">Cảm ơn đóng góp của Quý khách hàng</div>
                        <div class="text-center">trong việc đánh giá để Asahi Japan nâng</div>
                        <div class="text-center">cao chất lượng dịch vụ</div>
                    </div>
                    <div class="form-group">
                        <div class="text-center">
                            <button type="button" class="audit-confirm" id="add_debit_detail_previous">Đã hiểu</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="createDebitDetail_fail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <label class="audit-label-header-fail"></label>
        <div class="modal-content">
            <div class="modal-body debit_detail_content">
                <br>
                <div class="form-group">
                    <p class="text-center"><strong>Đánh giá không thành công!</strong></p>
                    <div class="text-center">Quý khách đã quá lượt đánh giá</div>
                    <div class="text-center">Vui lòng thử lại sau</div>
                </div>
                <div class="form-group">
                    <div class="text-center">
                        <button type="button" class="audit-confirm" id="add_debit_detail_previous">Đã hiểu</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<style>
.modal-dialog {
    top: 200px;
}
.modal-content {
    border-radius: 10px !important;
}
.form-control:active,.form-control:focus {
    border-color: red!important;
}
.help-block{
    font-size: 12px;
}
/* Create four equal columns that floats next to each other */
.column {
  float: left;
  width: 50%;
  padding: 10px;
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}

/* Responsive layout - makes a two column-layout instead of four columns */
@media screen and (max-width: 900px) {
  .column  {
    width: 50%;
  }
}

/* Responsive layout - makes the two columns stack on top of each other instead of next to each other */
@media screen and (max-width: 200px) {
  .column  {
    width: 100%;
  }
}
</style>
@section('javascript')
    <script>
        $('.sub_danh_gia').click(function(e) {
            e.preventDefault();
            var formCreate = new FormData($('#form_audit')[0]);
                showLoading();
                //$('#createDebitDetail').modal('show');
                $.ajax({
                    url: "{{ url('/audit-service/store') }}",
                    type: "POST",
                    data: formCreate,
                    contentType: false,
                    processData: false, 
                    success: function (response) {
                        if (response.success == true) {
                            $('#createDebitDetail').modal('show');
                            setTimeout(() => {
                                location.reload()
                            }, 5000)
                        }else if (response.success == false) {
                            $('#createDebitDetail_fail').modal('show');
                            setTimeout(() => {
                                location.reload()
                            }, 5000)
                        } else {
                            toastr.error('Có lỗi! Xin vui lòng thử lại');
                            setTimeout(() => {
                                location.reload()
                            }, 2000)
                        }
                        hideLoading();
                    },
                    error: function (response) {
                        $(document).find('.has-error').removeClass('has-error');
                        if ($(document).find('.help-block').length) {
                            $(document).find('.help-block').remove();
                        }
                        showErrorsCreate(response.responseJSON.errors, '.div_', '.message_zone');
                        hideLoading();
                    }
                })
        });
        $('.audit-confirm').click(function(e) {
            e.preventDefault();
            $('#createDebitDetail').modal('hide');
            $('#createDebitDetail_fail').modal('hide');
            if(window.location.href.indexOf("&token") >= 0)
            {
                window.location.replace("http://app.handle/close");
            }
            //location.reload()
        })
    </script>
@endsection
