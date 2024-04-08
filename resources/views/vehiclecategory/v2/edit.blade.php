@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Sửa loại phương tiện
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Sửa loại phương tiện</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Sửa loại phương tiện</div>

                <div class="panel-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="form-group">
                        <form action="{{route('admin.v2.vehiclecategory.update',['id'=>$vehiclecate->id])}}" method="post" id="form-edit-vehiclecate">
                            {{ csrf_field() }}
                            <div class="col-sm-6">
                                <input type="hidden" id="id_vehicle_cate" value="{{$vehiclecate->id}}" >
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="ip-name">Tên danh mục</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text"
                                               name="name"
                                               id="in-vccname"
                                               class="form-control"
                                               placeholder="Tên danh mục"
                                               value="{{ $vehiclecate->name ?? old('name') ?? ''}}"
                                        >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Mô tả</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <textarea name="description"
                                                  id="description"
                                                  class="form-control"
                                                  placeholder="Mô tả">{{$vehiclecate->description ?? old('description') ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <h4>Cấu hình</h4>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Loại giá</label>
                                    </div>
                                    <div class="col-sm-8">
                                        @if(isset($progressive))
                                            <select name="bdc_price_type_id" class="form-control bdc_price_type_id_category">
                                                <option value="1" {{$progressive->bdc_price_type_id==1 ? 'selected' : ''}}>Một giá</option>
                                                <option value="2" {{$progressive->bdc_price_type_id==2 ? 'selected' : ''}} >Lũy tiến</option>
                                            </select>
                                        @else
                                            <select name="bdc_price_type_id" class="form-control bdc_price_type_id_category">
                                                <option value="1" selected>Một giá</option>
                                                <option value="2">Lũy tiến</option>
                                            </select>
                                        @endif
                                    </div>
                                    @if(isset($progressive) && $progressive->bdc_price_type_id==2)
                                        <div class="col-sm-1">
                                            <button type="button" class="btn btn-success add_progress_price_item" style="float: right">
                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="col-sm-1">
                                            <button type="button" class="btn btn-success add_progress_price_item" style="float: right; display: none">
                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <input type="hidden" name="progressive_price" id="progressive_price">
                                </div>
                                <div class="card-body progress_price_list form-group row">
                                    @if(isset($progressive))
                                        @if($progressive->bdc_price_type_id==1)
                                            <div class="temp_one_progress_price_items col-sm-12">
                                                <div class="form-group row">
                                                    <div class="col-sm-3">
                                                        <label for="in-re_name">Đơn giá</label>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <input type="text"
                                                               name="price"
                                                               class="form-control"
                                                               placeholder="Giá tiền"
                                                               value="{{$progressive_prices[0]->price}}"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            @foreach($progressive_prices as $progressive_price)
                                                <div class="progress_price_items">
                                                    <div class="form-group">
                                                        <div class="form-row row progress_price_item col-sm-12">
                                                            <div class="form-group col-md-3">
                                                                <input type="text"
                                                                       name="progressive[from][0]"
                                                                       class="form-control progress_from"
                                                                       value="{{$progressive_price->from}}"
                                                                       placeholder="Từ">
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                <input type="text"
                                                                       name="progressive[to][0]"
                                                                       class="form-control progress_to"
                                                                       value="{{$progressive_price->to}}"
                                                                       placeholder="Đến">
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                <input type="text"
                                                                       name="progressive[price][0]"
                                                                       class="form-control progress_price"
                                                                       value="{{$progressive_price->price}}"
                                                                       placeholder="Giá tiền">
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                <button type="button"
                                                                        class="btn btn-danger remove_progress_price_item"
                                                                        onclick="removeProgressPrice(this)">
                                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    @else
                                        <div class="temp_one_progress_price_items col-sm-12">
                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <label for="in-re_name">Đơn giá</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <input type="text" name="price" class="form-control" placeholder="Giá tiền">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Loại xe</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="type" class="form-control">
                                            <option value="1"  {{$vehiclecate->type==1 ? 'selected':'' }}>Xe đạp</option>
                                            <option value="2"  {{$vehiclecate->type==2 ? 'selected':'' }}>Xe máy</option>
                                            <option value="3"  {{$vehiclecate->type==3 ? 'selected':'' }}>Ô tô</option>
                                            <option value="4"  {{$vehiclecate->type==4 ? 'selected':'' }}>Xe điện</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Ngày áp dụng tính phí</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="date"
                                               class="form-control pull-right date_picker"
                                               name="first_time_active"
                                               value="{{ $vehiclecate->first_time_active }}"
                                        >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Mã thu</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text"
                                               class="form-control pull-right"
                                               name="code_receipt"
                                               value="{{ $vehiclecate->code_receipt }}"
                                        >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Ngày chuyển đổi</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number"
                                               class="form-control pull-right"
                                               name="ngay_chuyen_doi"
                                               min="1"
                                               value="{{ $vehiclecate->ngay_chuyen_doi }}"
                                        >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Ngày tính phí dịch vụ</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number"
                                               value="{{ $vehiclecate->bill_date ?? 1}}"
                                               name="bill_date"
                                               class="form-control"
                                               placeholder="Ngày tính phí dịch vụ"
                                        >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Ngày thanh toán</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number"
                                               value="{{ $vehiclecate->payment_deadline ?? 10}}"
                                               name="payment_dealine"
                                               class="form-control"
                                               placeholder="Ngày thanh toán"
                                        >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Đối tượng thu phí</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="service_group" class="form-control">
                                            <option value="1" {{$vehiclecate->service_group==1 ? 'selected':'' }} >Công ty</option>
                                            <option value="2" {{$vehiclecate->service_group==2 ? 'selected':'' }} >Thu hộ</option>
                                            <option value="3" {{$vehiclecate->service_group==3 ? 'selected':'' }} >Chủ đầu tư</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Trạng thái</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="checkbox" name="status" {{ (isset($vehiclecate->status) && $vehiclecate->status==1) ?'checked':''}}>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-success btn-js-action-vehiclecate" title="Cập nhật" form="form-edit-vehiclecate">
                                    <i class="fa fa-save"></i>&nbsp;Cập nhật
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="progress_price_one" style="display: none">
    <div class="temp_one_progress_price_items">
        <div class="form-group">
            <div class="col-sm-3">
                <label for="in-re_name">Đơn giá</label>
            </div>
            <div class="col-sm-8">
                <input type="text" name="price" class="form-control" placeholder="Giá tiền">
            </div>
        </div>
    </div>
</div>
<div class="progress_price_multi" style="display: none">
    <div class="progress_price_items">
        <div class="form-group">
            <div class="form-row row progress_price_item col-sm-12">
                <div class="form-group col-md-3">
                    <input type="text" name="progressive[from][0]" class="form-control progress_from" placeholder="Từ">
                </div>
                <div class="form-group col-md-3">
                    <input type="text" name="progressive[to][0]" class="form-control progress_to" placeholder="Đến">
                </div>
                <div class="form-group col-md-3">
                    <input type="text" name="progressive[price][0]" class="form-control progress_price" placeholder="Giá tiền">
                </div>
                <div class="form-group col-md-3">
                    <button type="button" class="btn btn-danger remove_progress_price_item" onclick="removeProgressPrice(this)">
                        <i class="fa fa-minus" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

    <script>
        sidebar('apartments', 'create');
        function removeProgressPrice(that) {
            $(that).closest(".progress_price_items").remove();
            $(".progress_price_list").find(".progress_price_items").each(function (key, value) {
                $(this).find("input").each(function () {
                    this.name = this.name.replace(/\d+/, key);
                });
            });
        }

        $(function () {

            $(".add_progress_price_item").click(function (e) {
                var avails = $(".progress_price_items");
                var clone = avails.eq(0).clone();
                $(".progress_price_list").append(clone).find(".progress_price_items").each(function (key, value) {
                    $(this).find("input").each(function () {
                        this.name = this.name.replace(/\d+/, key);
                    });
                });
                e.preventDefault();
            });

            $(".bdc_price_type_id_category").on("change", function () {
                var priceTypeValue = $(this).val();
                console.log(1234);
                console.log(priceTypeValue);
                if (priceTypeValue == 1) {
                    $(".add_progress_price_item").prop("disabled", true);
                    $(".add_progress_price_item").css("display", "none");
                    var avails = $(".temp_one_progress_price_items");
                    var clone = avails.eq(0).clone();
                    $(".progress_price_list").html(clone);
                } else {
                    $(".add_progress_price_item").prop("disabled", false);
                    $(".add_progress_price_item").css("display", "block");
                    var avails = $(".progress_price_items");
                    var clone = avails.eq(0).clone();
                    $(".progress_price_list").html(clone);
                }
            });

            $(".btn-js-action-vehiclecate").on('click', function (e) {
                var _this = $(this);
                e.preventDefault();
                // return;
                $(".alert_pop_add_vehiclecate").hide();
                var code = $("#in-vccname").val();
                let id = $("#id_vehicle_cate").val();
                if (code.length <= 3 || code.length >= 45) {
                    $(".alert_pop_add_vehiclecate").show();
                    $(".alert_pop_add_vehiclecate ul").html('<li>Tên loại phương tiện không được nhỏ hơn 3 hoặc lớn hơn 45 ký tự</li>')
                } else {
                    showLoading();
                    $.ajax({
                        url: '/admin/v2/vehiclecategory/checkVehicleNameCategory',
                        type: 'POST',
                        data: {
                            'name': code,
                            'id': id
                        },
                        success: function (res) {
                            console.log(res);
                            if(res.data.count===0) {
                                let progress = [];
                                $('.progress_price_list .progress_price_item').each((index, e) => {
                                    console.log(e);
                                    console.log($(e).find('.progress_from').val());
                                    progress.push({
                                        from: $(e).find('.progress_from').val(),
                                        to: $(e).find('.progress_to').val(),
                                        price: $(e).find('.progress_price').val()
                                    });

                                });

                                progress = JSON.stringify(progress);

                                $('#progressive_price').val(progress);

                                $("#form-edit-vehiclecate").submit();
                            }
                            else {
                                $(".alert_pop_add_vehiclecate").show();
                                $(".alert_pop_add_vehiclecate ul").html('<li>Tên loại phương tiện đã tồn tại</li>')
                                hideLoading();
                            }
                        },
                        error: function (e) {
                            console.log(e)
                        }
                    })


                }
            });
        });
    </script>

@endsection
