@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Sửa căn hộ
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Sửa căn hộ</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="clearfix"></div>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#general" role="tab" data-toggle="tab">Thông tin chung</a></li>
                    <li><a href="#diary_aparment" role="tab" data-toggle="tab">Nhật ký căn hộ</a></li>
                    <li><a href="#service" role="tab" data-toggle="tab">Dịch vụ sử dụng</a></li>
                    <li><a href="#document" role="tab" data-toggle="tab">Tài liệu căn hộ</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="general" style="padding: 15px 0;">
                        <div class="panel panel-default">
                            <div class="panel-heading">Sửa căn hộ</div>

                            <div class="panel-body">
                                <div class="alert alert-danger alert_pop_add_edit" style="display: none;">
                                    <ul></ul>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <form action="" method="post" id="form-edit-apartment">
                                            {{ csrf_field() }}
                                            <div class="form-group">
                                                <label for="ip-name">Tên căn hộ</label>
                                                <input type="text" name="name" id="ip-name-apt" class="form-control"
                                                       placeholder="Tên căn hộ"
                                                       value="{{!empty($apatment->name)?$apatment->name:''}}">
                                            </div>
                                            <div class="form-group">
                                                <label for="ip-place">Tòa nhà</label>
                                                <select name="building_place_id" id="ip-place" class="form-control"
                                                        style="width: 100%">
                                                    @foreach($building_place as $value)
                                                        <option value="{{$value->id}}" {{$apatment->building_place_id == $value->id ? 'selected' : '' }}>{{$value->name}}
                                                            - {{$value->code}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="ip-floor">Số tầng</label>
                                                <input type="text" name="floor" id="ip-floor" class="form-control"
                                                       placeholder="Tầng căn hộ"
                                                       value="{{!empty($apatment->floor)?$apatment->floor:''}}">
                                            </div>
                                            <div class="form-group">
                                                <label for="ip-acreage">Diện tích(m<sup>2</sup>)</label>
                                                <input type="text" name="area" id="ip-acreage" class="form-control"
                                                       placeholder="Diện tích căn hộ"
                                                       value="{{!empty($apatment->area)?$apatment->area:''}}">
                                            </div>
                                            <div class="form-group">
                                                <label for="ip-description">Mô tả</label>
                                                <textarea name="description" id="id-description" cols="30" rows="5"
                                                          placeholder="Mô tả căn hộ"
                                                          class="form-control">{{!empty($apatment->description)?$apatment->description:''}}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="ip-acreage">Mã căn</label>
                                                <input type="text" name="code" id="ip-code" class="form-control"
                                                       placeholder="Diện tích căn hộ"
                                                       value="{{!empty($apatment->code)?$apatment->code:''}}">
                                            </div>
                                            <div class="form-group">
                                                <label>Mã khách hàng</label>
                                                <input type="text" name="code_customer" class="form-control"
                                                       placeholder="Mã khách hàng"
                                                       value="{{!empty($apatment->code_customer)?$apatment->code_customer:''}}">
                                            </div>
                                            <div class="form-group">
                                                <label>Tên khách hàng</label>
                                                <input type="text" name="name_customer" class="form-control"
                                                       placeholder="Tên khách hàng"
                                                       value="{{!empty($apatment->name_customer)?$apatment->name_customer:''}}">
                                            </div>
                                            <div class="form-group">
                                                <label>Mã công tơ điện</label>
                                                <input type="text" name="code_electric" class="form-control"
                                                       placeholder="Mã công tơ điện"
                                                       value="{{!empty($apatment->code_electric)?$apatment->code_electric:''}}">
                                            </div>
                                            <div class="form-group">
                                                <label>Mã công tơ nước</label>
                                                <input type="text" name="code_water" class="form-control"
                                                       placeholder="Mã công tơ nước"
                                                       value="{{!empty($apatment->code_water)?$apatment->code_water:''}}">
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="form-group">
                                                <label for="ip-role">Tình trạng</label>

                                                <select name="status" id="select-ap-role-edit" class="form-control">
                                                    <option value="">Chọn Trạng thái</option>
                                                    <option value="0" @if($status == '0') selected @endif>Để không
                                                    </option>
                                                    <option value="1" @if($status == '1') selected @endif>Cho thuê
                                                    </option>
                                                    <option value="2" @if($status == '2') selected @endif>Muốn cho
                                                        thuê
                                                    </option>
                                                    <option value="3" @if($status == '3') selected @endif>Đang ở
                                                    </option>
                                                    <option value="4" @if($status == '4') selected @endif>Mới bàn giao
                                                    </option>
                                                    <option value="5" @if($status == '5') selected @endif>Đang cải tạo
                                                    </option>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <button type="button" class="btn btn-sm btn-success btn-save-edit"
                                                        title="Cập nhật" form="form-edit-apartment">
                                                    <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="panel-heading">Tập tin <a href="javascript:void(0);"
                                                                          class="btn btn-success"
                                                                          title="Thêm cư dân" data-toggle="modal"
                                                                          data-target="#add-file"><i
                                                    class="fa fa-plus"></i>&nbsp;&nbsp;Thêm</a></div>
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tên file</th>
                                            <th width="130">Thao tác</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($files as $k => $f)
                                            <tr>
                                                <td>{{$k+1}}</td>
                                                <td>{{$f->name}}</td>
                                                <td>
                                                    <a href="{{ url($f->url) }}"
                                                       class="tag-relats bg-submain2 s-file-trash"><i
                                                                class="fa fa-eye"></i></a>
                                                    @if( in_array('admin.systemfiles.download',@$user_access_router))
                                                        <a href="{{ route('admin.systemfiles.download',['path'=>$f->url]) }}"
                                                           class="tag-relats bg-submain2 s-file-trash"><i
                                                                    class="fa fa-download"></i></a>
                                                    @endif
                                                    @if( in_array('admin.systemfiles.download',@$user_access_router))
                                                        <a href="{{ route('admin.systemfiles.delete',['id'=> $f->id]) }}"
                                                           class="tag-relats bg-submain s-file-trash"><i
                                                                    class="fa fa-trash"></i></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">Cư dân <a class="btn btn-success" title="Thêm cư dân"
                                                                 data-toggle="modal"
                                                                 data-target="#search-resident"><i
                                            class="fa fa-plus"></i>&nbsp;&nbsp;Thêm</a>
                                <p
                                        class="display_mes_summit @if($data_error) error_mes @elseif($data_success) success_mes @endif">
                                    {{$data_cus}} </p>
                            </div>
                            <div class="panel-body">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th colspan="" rowspan="" headers="">Stt</th>
                                        <th colspan="" rowspan="" headers="">Họ và tên</th>
                                        <th colspan="" rowspan="" headers="">Email</th>
                                        <th colspan="" rowspan="" headers="">Mobile</th>
                                        <th colspan="" rowspan="" headers="">Quan hệ</th>
                                        <th colspan="" rowspan="" headers="">Thao tác</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($residents as $key => $item)
                                        <tr>
                                            <td colspan="" rowspan="" headers="">{{ $key + 1 }}</td>
                                            <td colspan="" rowspan="" headers=""><a
                                                        href="javascript:void(0);">{{ @$item->user_info_first->full_name }}</a>
                                            </td>
                                            <td colspan="" rowspan=""
                                                headers="">{{ @$item->user_info_first->email_contact }}</td>
                                            <td colspan="" rowspan=""
                                                headers="">{{ @$item->user_info_first->phone_contact }}</td>
                                            <td colspan="" rowspan="" headers="">
                                                @if($item->type == 0)
                                                    <span class="tag-relats bg-main" style="">Chủ hộ</span>
                                                @elseif($item->type == 1)
                                                    <span class="tag-relats bg-submain" style="">Vợ/Chồng</span>
                                                @elseif($item->type == 2)
                                                    <span class="tag-relats bg-submain1" style="">Con</span>
                                                @elseif($item->type == 3)
                                                    <span class="tag-relats bg-submain1" style="">Bố mẹ</span>
                                                @elseif($item->type == 4)
                                                    <span class="tag-relats bg-submain3" style="">Anh chị em</span>
                                                @elseif($item->type == 5)
                                                    <span class="tag-relats bg-submain3" style="">Khác</span>
                                                @elseif($item->type == 6)
                                                    <span class="tag-relats bg-submain3" style="">Khách thuê</span>
                                                @elseif($item->type == 7)
                                                    <span class="tag-relats bg-submain3" style="">Chủ hộ cũ</span>
                                                @endif
                                            </td>
                                            <td colspan="" rowspan="" headers="">
                                                @if( in_array('admin.customers.edit',@$user_access_router))
                                                    <a href="{{ route('admin.v2.customers.edit',['id'=> @$item->user_info_first->id]) }}"
                                                       class="btn btn-success" title="Chi tiết"><i
                                                                class="fa fa-share-square-o"></i></a>
                                                @endif
                                                @if( in_array('admin.v2.customers.del_customer',@$user_access_router))
                                                    <a href="{{ route('admin.v2.customers.del_customer',['apartment_id'=> 0,'user_info_id'=>@$item->user_info_first->id]) }}"
                                                       onclick="return confirm('Bạn có chắc chắn muốn xóa không?')"
                                                       class="btn btn-danger" title="xóa"><i
                                                                class="fa fa-times"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @include('apartments.v2.panels.vehicle')
                    </div>
                    <div class="tab-pane" id="diary_aparment">
                        <div class="box-diary" style="padding: 15px 0;">
                            <form action="" method="post" id="form-search-diary">
                                <div class="box-input-diary" style="width: calc(100% - 50px);display: inline-block">
                                    <div class="">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <input type="text" name="keyword" id="ip-keyword-fb"
                                                       placeholder="Nhập từ khóa"
                                                       class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <select name="status" id="ip-status-fb" class="form-control">
                                                    <option value="">Tình trạng</option>
                                                    <option value="0">Chưa xử lý</option>
                                                    <option value="1">Đã xử lý</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <select name="type" id="ip-type-fb" class="form-control">
                                                    <option value="">Loại yêu cầu</option>
                                                    <option value="fback">Ý kiến</option>
                                                    <option value="request">Form yêu cầu</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-btn" style="float: right;">
                                    <a class="btn btn-success btn-action-log"><i class="fa fa-search"></i></a>
                                </div>
                            </form>
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th colspan="" rowspan="" headers="">STT</th>
                                            <th colspan="" rowspan="" headers="">Tiêu đề</th>
                                            <th colspan="" rowspan="" headers="">Loại ý kiến</th>
                                            <th colspan="" rowspan="" headers="">Người gửi</th>
                                            <th colspan="" rowspan="" headers="">Ngày gửi</th>
                                            <th colspan="" rowspan="" headers="">Tình trạng</th>
                                            <th colspan="" rowspan="" headers="">Tác vụ</th>
                                        </tr>
                                        </thead>
                                        <tbody class="table_fb">
                                        @foreach($feedbacks as $fb)
                                            <tr>
                                                <td colspan="" rowspan="" headers="">{{@$fb->id}}</td>
                                                <td colspan="" rowspan="" headers="">{{@$fb->title}}</td>
                                                <td colspan="" rowspan="" headers="">{{@$fb->type}}</td>
                                                <td colspan="" rowspan=""
                                                    headers="">{{@$fb->pubUserProfile->display_name}}</td>
                                                <td colspan="" rowspan="" headers="">{{@$fb->created_at}}</td>
                                                <td colspan="" rowspan="" headers="">
                                                    <a title="Thay đổi trạng thái" href="javascript:;"
                                                       class="btn-status label label-sm label-{{ $fb->status ? 'success' : 'danger' }}">
                                                        {{ $fb->status ? 'Đã xử lý' : 'Chưa xử lý' }}
                                                    </a>
                                                </td>
                                                <td colspan="" rowspan="" headers="">
                                                    @if( in_array('admin.feedback.detail',@$user_access_router))
                                                        <a href="{{route('admin.feedback.detail',['id'=>$fb->id])}}"
                                                           class="btn btn-success" title="Chi tiết"><i
                                                                    class="fa fa-share-square-o"></i></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="service">
                        <div class="col-sm-11 form-control-static">
                            @if( in_array('admin.v2.service.apartment.create',@$user_access_router))
                                <a href="{{ route('admin.v2.service.apartment.create') }}" type="buttom"
                                   class="btn btn-success pull-right"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm dịch vụ</a>
                            @endif
                        </div>
                        <div class="box-service" style="padding: 15px 0;">
                            <?php $i = 0 ?>
                            @foreach($services as $key => $sv)
                                @if(count($sv->apartmentServicePrices)!=0 )
                                        <?php $i++; ?>
                                    <div class="col-sm-12">
                                        <div class="box-title-service" style="display: inline-block;width: 100%;">
                                            <div class="col-sm-6">
                                                <p style="font-size: 17px;font-weight: bold;">{{$i}}. {{$sv->name}} -
                                                    Căn hộ
                                                    {{$apatment->name}}</p>
                                            </div>
                                        </div>
                                        <div class="box-table-service">
                                            <div class="panel panel-default">
                                                <div class="panel-body">
                                                    <table class="table table-striped">
                                                        <thead>
                                                        <tr>
                                                            <th width='8%'>Stt</th>
                                                            <th width='22%' colspan="2">Tên sản phẩm</th>
                                                            <th width='7%'>Số lượng</th>
                                                            <th width='20%'>Đơn giá</th>
                                                            <th width='10%'>Trạng thái</th>
                                                            <th width='3%'>Ngày bắt đầu</th>
                                                            <th width='23%'>Ghi chú</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($sv->apartmentServicePrices as $keyi => $item)
                                                            <tr>
                                                                <td width='8%'>{{$keyi+1}}
                                                                </td>
                                                                <td width='13%'>{{$item->name}}</td>
                                                                <td width='9%'>{{@$item->vehicle->number}}</td>
                                                                <td width='7%'>1</td>
                                                                <td width='20%'>{{ number_format($item->price)}}
                                                                    @if($item->floor_price != 0 && $sv->type == 2)
                                                                        ({{$item->floor_price}} * {{$item->apartment->area}} m<sup>2</sup>)
                                                                    @endif</td>
                                                                <td width='10%'>@if($sv->status == 0)
                                                                        Inactive
                                                                    @else
                                                                        Active
                                                                    @endif</td>
                                                                <td width='10%'>{{date("d/m/Y", strtotime(@$item->first_time_active))}}</td>
                                                                <td width='23%'>{{$item->description}}</td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="tab-pane" id="document">
                        <div class="col-sm-11 form-control-static">
                            <a href="" type="buttom"
                               data-toggle="modal"
                               data-target="#add-document-apartment"
                               class="btn btn-success pull-right"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm tài
                                liệu</a>
                        </div>
                        <div class="col-sm-12 row">
                            <h3>Tài liệu căn hộ</h3>
                            <div class="col-sm-6">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th>TT</th>
                                            <th>Tiêu đề</th>
                                            <th>File</th>
                                            <th>Tác vụ</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $count = 1 ?>
                                        @foreach($documents as $document)
                                            @if(count($document->apartments)>0)
                                                <tr>
                                                    <td>{{$count++}}</td>
                                                    <td>{{$document->title}}</td>
                                                    <td>
                                                        @foreach($document->attach_file as $file )
                                                            <a target="_blank"
                                                               href="{{"https://media.dxmb.vn".$file->hash_file}}">{{$file->file_name}}</a>
                                                            <br>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-danger delete-asset"
                                                           data-id="{{$document->id}}"
                                                           title="Xóa tài liệu">
                                                            <i class="fa fa-times"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <h3>Tài liệu nhóm căn
                                hộ: {{ isset($apatment->apartmentGroup) ? $apatment->apartmentGroup->name:""}}</h3>
                            <div class="col-sm-6">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th>TT</th>
                                            <th>Tiêu đề</th>
                                            <th>File</th>
                                            <th>Tác vụ</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $count = 1 ?>
                                        @foreach($documents as $document)
                                            @if(count($document->apartment_groups)>0)
                                                <tr>
                                                    <td>{{$count++}}</td>
                                                    <td>{{$document->title}}</td>
                                                    <td>
                                                        @foreach($document->attach_file as $file )
                                                            <a target="_blank"
                                                               href="{{"https://media.dxmb.vn".$file->hash_file}}">{{$file->file_name}}</a>
                                                            <br>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-danger delete-asset"
                                                           data-id="{{$document->id}}"
                                                           title="Xóa tài liệu">
                                                            <i class="fa fa-times"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="add-resident" class="modal fade" role="dialog">
            <div class="modal-dialog  modal-lg">
                <!-- Modal content-->
                @if( in_array('admin.v2.customers.insert',@$user_access_router))
                    <form action="{{ route('admin.v2.customers.insert') }}" method="post" id="form-add-resident"
                          class="form-validate form-horizontal">
                        {{ csrf_field() }}
                        <input type="hidden" name="hashtag" id="hashtag">
                        <input type="hidden" name="user_info_id" id="user_info_id">
                        <input type="hidden" name="apartment_id" value="{{$id}}">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Thêm mới Cư dân</h4>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                                    <ul></ul>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label for="in-re_name">Tên cư dân</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" name="name" id="in-re_name" class="form-control"
                                                       placeholder="Tên cư dân">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Giới tính</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="radio" name="sex" id="in-re_sex_female" value="1"/> Nam
                                                <input type="radio" name="sex" id="in-re_sex_male" value="2"/> Nữ
                                            </div>
                                        </div>
                                        <div class="info_contact">
                                            <div class="form-group">
                                                <div class="col-sm-2">
                                                    <label>Email liên hệ</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <input type="text" name="email_contact" id="in-re_email"
                                                           autocomplete="nope" class="form-control"
                                                           placeholder="Email cư dân">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-2">
                                                    <label>Số điện thoại liên hệ</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <input type="text" name="phone_contact" id="in-re_phone"
                                                           class="form-control" placeholder="Điện thoại cư dân">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Ngày sinh</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="input-group datetimepicker" data-format="DD-MM-Y">
                                                    <input type="text" name="create_birthday" id="create_birthday"
                                                           class="form-control" placeholder="Ngày sinh"><span
                                                            class="input-group-addon"><i
                                                                class="fa fa-calendar"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Quan hệ</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <select name="type" id="in-re_relationship" class="form-control">
                                                    <option value="0">Chủ hộ</option>
                                                    <option value="1">Vợ/Chồng</option>
                                                    <option value="2">Con</option>
                                                    <option value="3">Bố mẹ</option>
                                                    <option value="4">Anh chị em</option>
                                                    <option value="5">Khác</option>
                                                    <option value="6">Khách thuê</option>
                                                    <option value="7">Chủ hộ cũ</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="info_login">
                                            <hr>
                                            <p style="font-weight: bold;font-size:15px;">Thông tin đăng nhập</p>
                                            <div class="form-group">
                                                <div class="form-group" style="width: 600px;margin: 0 auto">
                                                    <div class="col-sm-6">
                                                        <label for="email">Email</label>
                                                        <input type="email" name="email" id="email" autocomplete="nope"
                                                               class="form-control" placeholder="Email">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label for="phone">Số điện thoại</label>
                                                        <input type="text" name="phone" id="phone" autocomplete="nope"
                                                               class="form-control" placeholder="Số điện thoại">
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <label for="phone">Mật khẩu</label>
                                                        <input type="text" name="pword" id="pword" autocomplete="nope"
                                                               class="form-control" placeholder="Mật khẩu">
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-info"><span class="text-danger">*</span> Hệ thống sẽ gửi
                                                thông tin tài khoản, căn hộ đến email cho cư dân.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="submit" id="submit">
                                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i
                                            class="fa fa-close"></i>&nbsp;&nbsp;Hủy
                                </button>
                                <button type="button" class="btn btn-primary btn-js-action" style="margin-right: 5px;">
                                    <i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận
                                </button>
                                {{-- <a href="javascript:;" type="button" class="btn btn-primary btn-js-action"  data-target="#form-add-resident"  style="margin-right: 5px;"><i class="fa fa-save"></i> Xác nhận</a> --}}
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
        <div id="search-resident" class="modal fade" role="dialog">
            <div class="modal-dialog custom-dialog">
                <!-- Modal content-->
                <form id="form-search-resident">
                    {{ csrf_field() }}
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Nhập thông tin cư dân</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-12">
                                    <div class="col-sm-10">
                                        <label> Tìm theo số điện thoại, email, cmt</label>
                                        <input type="text" id="searchEmailPhone" name="keyword" class="form-control"
                                               placeholder="Nhập email, số điện thoại">
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" class="btn btn-primary save_info"
                                                style="margin-right: 5px;margin-top: 25px;"><i class="fa fa-search"></i>Tìm
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <a href="javascript:;" id="save_info_show_modal" class="text_decoration">(*) Trường
                                        hợp cư dân chưa có số điện thoại, email, cmt</a>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">

                        </div>
                    </div>
                </form>
            </div>
        </div>
        @include('apartments.v2.modals.add-vehicle')
        <div id="add-file" class="modal fade" role="dialog">
            <div class="modal-dialog  modal-lg">
                <!-- Modal content-->
                @if( in_array('admin.v2.apartments.createfile',@$user_access_router))
                    <form action="{{ route('admin.v2.apartments.createfile',['id'=>$id]) }}" method="POST"
                          id="form-add-file-aparment" class="form-validate form-horizontal" autocomplete="off"
                          enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Thêm mới file</h4>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger alert_pop_add_file" style="display: none;">
                                    <ul></ul>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label for="in-re_name">Tên file</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" name="name" id="in-name-file" class="form-control"
                                                       placeholder="Tên file">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>file</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="file" name="file_apartment" id="ip-file"
                                                       class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Mô tả</label>
                                            </div>
                                            <div class="col-sm-8">
                                        <textarea name="description" id="textarea-vc_description" class="form-control"
                                                  cols="30" rows="5" placeholder="Mô tả file"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i
                                            class="fa fa-close"></i>&nbsp;&nbsp;Hủy
                                </button>
                                <button type="button" class="btn btn-primary btn-js-action-file"
                                        form="form-add-file-aparment"
                                        style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
        @include('apartments.v2.modals.add-documnet')
    </section>
@endsection
@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
    <style>
        .text_decoration {
            text-decoration: underline;
        }
    </style>
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script>

        $('#select-vc_type').on('change', function () {
            $.ajax({
                url: '/admin/vehicles/getPriceVehicle',
                type: 'POST',
                data: {
                    apartment_id: $('#in-ap_id').val(),
                    vehicle_category_id: $('#select-vc_type').val()
                },
                success: function (res) {
                    let {progressive_prices, progressivePrice: progressive_price} = res.data;

                    let select_progressive_prices = "";
                    progressive_prices.forEach((item) => {
                        const {price, id, name, priority_level} = item;
                        const {priority_level: priority_level1} = progressive_price;
                        if (priority_level === priority_level1) {
                            select_progressive_prices += `<option value=${id} selected>${name} - ${price} VNĐ - Mức ${priority_level}</option>`;
                        } else {
                            if (priority_level < priority_level1) {
                                select_progressive_prices += `<option value=${id} >${name} - ${price} VNĐ - Mức ${priority_level}</option>`;
                            }
                        }
                    })
                    let list_progressives = $('#progressive_price_id');
                    list_progressives.empty();
                    list_progressives.prepend(select_progressive_prices);
                },
                error: function (e) {
                    console.log(e);
                }
            })
        });
        $('.save_info').on('click', function (e) {
            e.preventDefault();

            let input = $('#searchEmailPhone').val();
            var vnf_regex = /((09|03|07|08|05|06|02)+([0-9]{8})\b)/g;
            if (isNumeric(input)) {
                if ((input.length <= 9 || input.length >= 12) || vnf_regex.test(input) == false) {
                    html = '<li>Số điện thoại dân cư không dưới 10 và không lớn hơn 11 ký tự và không để trống</li>';
                    $(".alert_pop_add_resident").show();
                    $(".alert_pop_add_resident ul").html(html);
                    hideLoading();
                    return;
                }
                $('#in-re_phone').val(input);
                $('#phone').val(input);
                $('#in-re_email').val('');
                $('#email').val('');
            } else {
                if (!isValidEmailAddress(input)) {
                    html = '<li>Email dân cư không Đúng định dạng</li>';
                    $(".alert_pop_add_resident").show();
                    $(".alert_pop_add_resident ul").html(html);
                    hideLoading();
                    return;
                }
                $('#in-re_phone').val('');
                $('#phone').val('');
                $('#in-re_email').val(input);
                $('#email').val(input);
            }
            $('#in-re_name').val('');
            $('#create_birthday').val('');
            $('#user_info_id').val('');
            $(".alert_pop_add_resident").hide();
            $(".alert_pop_add_resident ul").html('');
            $.post('{{ url('/admin/v2/customers/searchResident') }}', {
                text_search: input,
            }, function (data) {
                console.log(data);
                if (data.data.length == 0) {
                    $('input[name=sex]').attr("disabled", false);
                    $('input[name=sex]').attr("disabled", false);
                    $('.info_login').css('display', 'block');
                    $('#in-re_name').attr('readonly', false);
                    $('#in-re_phone').attr('readonly', false);
                    $('#in-re_email').attr('readonly', false);
                    $('#create_birthday').attr('readonly', false);
                } else {
                    $('input[name=sex]').attr("disabled", true);
                    $('input[name=sex]').attr("disabled", true);
                    data.data.userInfo.gender == 2 ? $("#in-re_sex_male").prop("checked", true) : $("#in-re_sex_female").prop("checked", true);
                    $('#in-re_name').val(data.data.userInfo.full_name).attr('readonly', true);
                    $('#in-re_phone').val(data.data.user.phone).attr('readonly', true);
                    $('#in-re_email').val(data.data.user.email).attr('readonly', true);
                    $('#create_birthday').val(data.data.userInfo.birthday ? moment(data.data.userInfo.birthday).format('DD-MM-YYYY') : '').attr('readonly', true);
                    $('#user_info_id').val(data.data.userInfo.id);
                    $('.info_login').css('display', 'none');
                }
                $('#hashtag').val(2);
                $('.info_contact').css('display', 'block');
                $('#search-resident').modal('hide');
                $('#add-resident').modal('show');
            });
        });
        $("#save_info_show_modal").on('click', function () {
            $('#hashtag').val(1);
            $('#phone').val('');
            $('#email').val('');
            $('#in-re_name').val('');
            $('#in-re_phone').val('');
            $('#in-re_email').val('');
            $('#create_birthday').val('');
            $('#user_info_id').val('');
            $('input[name=sex]').attr("disabled", false);
            $('input[name=sex]').attr("disabled", false);
            $('#in-re_name').attr('readonly', false);
            $('#in-re_phone').attr('readonly', false);
            $('#in-re_email').attr('readonly', false);
            $('#create_birthday').attr('readonly', false);
            $('.info_login').css('display', 'none');
            $('.info_contact').css('display', 'none');
            $('#search-resident').modal('hide');
            $('#add-resident').modal('show');
        })
        $(".btn-js-action-vehicle").on('click', function () {
            var _this = $(this);
            $(".alert_pop_add_vehicle").hide();
            _this.attr('type', 'button');
            var vehicle_number = $("#in-vc_vehicle_number").val();
            var name = $("#in-vc_name").val();
            var type = $("#select-vc_type").val();
            var apt = $("#ip-ap_id").val();
            var html = '';
            let first_time_active = $("#first_time_active").val();
            if (name.length <= 2 || name.length >= 50) {
                html += '<li>Tên phương tiện không được nhỏ hơn 3 hoặc lớn hơn 50 ký tự</li>';
            }
            if (vehicle_number == '' || vehicle_number.length <= 5 || vehicle_number.length > 13) {
                html += '<li>Biển số không được nhỏ hơn 6 hoặc lớn hơn 12 ký tự</li>';
            }
            if (apt == '') {
                html += '<li>Trường Căn hộ không được để trống</li>';
            }
            if (type == '') {
                html += '<li>Trường loại phương tiện không được để trống</li>';
            }
            if (first_time_active == '') {
                html += '<li>Ngày áp dụng tính phí không được để trống</li>';
            }

            if (html != '') {
                $(".alert_pop_add_vehicle").show();
                $(".alert_pop_add_vehicle ul").html(html);
                hideLoading();
                return;
            }

            showLoading();

            $.ajax({
                url: '/admin/vehicles/checkNumberVehicle',
                type: 'POST',
                data: {
                    'number': vehicle_number
                },
                success: function (res) {
                    if (res.data.count === 0) {
                        $('#form-add-verhicle').submit();
                    } else {
                        html += '<li>Biển số xe đã tồn tại trên hệ thống vui lòng kiểm tra lại</li>';
                        $(".alert_pop_add_vehicle").show();
                        $(".alert_pop_add_vehicle ul").html(html);
                        hideLoading();
                    }
                }
            })
        });
        $(".btn-js-action").on('click', function () {
            showLoading();
            $(".alert_pop_add_resident").hide();
            var email = $("#in-re_email").val();
            var phone_number = $("#in-re_phone").val();
            var phone_number_login = $("#phone").val();
            var email_login = $("#email").val();
            var name = $("#in-re_name").val();
            var rels = $("#in-re_relationship").val();
            var html = '';
            var message = '';
            $.get('{{ route('admin.customers.ajax_check_type') }}', {
                type: $('#in-re_relationship').val(),
                aparment: '{{$id}}'
            }, function (data) {
                hideLoading();
                if (name.length < 3 || name.length >= 45) {
                    html += '<li>Tên dân cư không được nhỏ hơn 3 hoặc lớn hơn 45 ký tự</li>';
                }
                if ($('#hashtag').val() == 1 && email != '' && !isValidEmailAddress(email)) {
                    html += '<li>Email dân cư không Đúng định dạng</li>';
                }
                if (email_login != '' && !isValidEmailAddress(email_login)) {
                    html += '<li>Email đăng nhập dân cư không Đúng định dạng</li>';
                }
                if ($('#hashtag').val() == 1 && phone_number != '' && isValidEmailAddress(phone_number)) {
                    html += '<li>Email dân cư không được nhập ở trường số điện thoại</li>';
                }
                if (phone_number_login != '' && isValidEmailAddress(phone_number_login)) {
                    html += '<li>Email dân cư không được nhập ở trường số điện thoại</li>';
                }
                if ($('#hashtag').val() == 1 && phone_number != '' && phone_number.length <= 9 || phone_number.length >= 12) {
                    html += '<li>Số điện thoại dân cư không dưới 10 và không lớn hơn 11 ký tự và không để trống</li>';
                }
                if (phone_number_login != '' && phone_number_login.length <= 9 || phone_number_login.length >= 12) {
                    html += '<li>Số điện thoại dân cư không dưới 10 và không lớn hơn 11 ký tự và không để trống</li>';
                }
                if (rels == '') {
                    html += '<li>Trường quan hệ không được để trống</li>';
                }
                if ($('#hashtag').val() == 1 && data.status == 1) {
                    html += '<li>Căn hộ này đã có chủ hộ</li>';
                }
                if (html) {
                    $(".alert_pop_add_resident").show();
                    $(".alert_pop_add_resident ul").html(html);
                    return false;
                }
                if ($('#hashtag').val() != 1 && data.status == 1) {
                    var confirm = window.confirm(data.message);
                    if (confirm == true) {
                        submitForm();
                    } else {
                        return false;
                    }
                } else {
                    submitForm();
                }
            });

        });

        function submitForm() {
            var formCreate = new FormData($('#form-add-resident')[0]);
            $.ajax({
                url: "{{ route('admin.v2.customers.insert') }}",
                type: 'POST',
                data: formCreate,
                contentType: false,
                processData: false,
                success: function (data) {
                    hideLoading();
                    if (data.success == true) {
                        toastr.success(data.message);
                    } else {
                        toastr.warning(data.message);
                    }
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                },
                error: function (data) {
                    hideLoading();
                    toastr.success(data.message);
                    // setTimeout(function() {
                    //     location.reload();
                    // }, 2000);
                }
            })
        };
        $('#in-re_phone,#phone').bind('keyup change', function (e) {
            $("#in-re_phone").val($(this).val());
            $("#phone").val($(this).val());
        });
        $('#in-re_email,#email').bind('keyup change', function (e) {
            $("#in-re_email").val($(this).val());
            $("#email").val($(this).val());
        });
        $(".btn-js-action-file").on('click', function () {
            var _this = $(this);
            $(".alert_pop_add_file").hide();
            var name = $("#in-name-file").val();
            if (name.length <= 0) {
                $(".alert_pop_add_file").show();
                $(".alert_pop_add_file ul").html('<li>Tên file không được bỏ trống</li>')
            } else if (name.length <= 3 || name.length >= 255) {
                $(".alert_pop_add_file").show();
                $(".alert_pop_add_file ul").html('<li>Tên file không được nhỏ hơn 3 hoặc lớn hơn 255 ký tự</li>')
            } else {
                $("#form-add-file-aparment").submit();
            }
        });
        $(".btn-save-edit").on('click', function () {
            var _this = $(this);
            $(".alert_pop_add_edit").hide();
            var apt = $("#ip-name-apt").val();
            var floor = $("#ip-floor").val();
            var area = $("#ip-acreage").val();
            var status = $("#select-ap-role-edit").val();
            var html = '';
            if (apt.length < 1 || apt.length > 46) {
                html += '<li>Tên căn hộ không được nhỏ hơn 2 hoặc lớn hơn 45 chữ số</li>';
            }
            if (floor.length == '' || floor.length > 5) {
                html += '<li>Trường tầng là 1 số không quá 5 chữ số và không bỏ trống</li>';
            }
            if (area.length == '' || area.length > 8 || $.isNumeric(area) == false) {
                html += '<li>Trường diện tích là 1 số không quá 8 chữ số và không bỏ trống</li>';
            }
            if (status.length <= 0) {
                html += '<li>Trường tình trạng không được để trống</li>';
            }
            if (html != '') {
                $(".alert_pop_add_edit").show();
                $(".alert_pop_add_edit ul").html(html)
            } else {
                $('#form-edit-apartment').submit();
            }
        });
        $(document).ready(function () {
            $('.tag_check_file').on('click', function () {
                var _this = $(this);
                $.get('{{ route('admin.systemfiles.ajax_change_status') }}', {
                    status: $(this).find('span').attr('status'),
                    id: $(this).data('id')
                }, function (data) {
                    if (data.status === 1) {
                        _this.html('<span class="tag-relats bg-submain2" status="1">Yes</span>');
                    } else {
                        _this.html('<span class="tag-relats bg-submain" status="0">No</span>');
                    }
                });
            });
        });
        $(".btn-action-log").on('click', function () {
            $.get('{{ route('admin.feedback.ajax_search_feedback') }}', {
                keyword: $("#ip-keyword-fb").val(),
                status: $("#ip-status-fb").val(),
                type: $("#ip-type-fb").val(),
                apartment_id: '{{$id}}'
            }, function (data) {
                $(".table_fb").html(data);
            });
        });

        $('.delete-asset').on('click', function () {
            let id = $(this).attr('data-id');
            let check = confirm('Bạn có chắc chắn muốn xóa không?');
            if (check) {
                showLoading();
                console.log(id);

                let ids = [id];

                ids = JSON.stringify(ids);

                $.ajax({
                    url: '/admin/v3/document/delete',
                    type: 'POST',
                    data: {
                        ids: ids
                    },
                    success: function (response) {

                        if (response.code === 0) {
                            alert("Xóa tài liệu thành công");
                            $("#asset_area").load(" #asset_area");
                            location.reload();
                        } else {
                            alert("Xóa tài liệu không thành công");
                            location.reload();
                        }
                        hideLoading();
                    },
                    error: function (e) {
                        console.log(e);
                        hideLoading();
                    }
                })
            }
        })

        $("#file_other1").change(function () {
            if (typeof (FileReader) != "undefined") {
                var filePreview = $("#filePreview_1");
                // dvPreview.html("");
                // var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
                $($(this)[0].files).each(function () {
                    var file = $(this);
                    if (file[0].name.toLowerCase()) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            let boxChild = $("<div class='file-upload-item'><span class='image' data-name data-base=''></span>&ensp;<i class='fa fa-close btn-remove-image' onclick='removeThisFile(this)'></i></div>");
                            // boxChild.children('.image').attr("src", e.target.result);
                            boxChild.children('.image').attr("data-base", e.target.result);
                            boxChild.children('.image').attr("data-name", file[0].name.toLowerCase());
                            boxChild.children('.image').text(file[0].name.toLowerCase())
                            // img.attr("src", e.target.result);
                            filePreview.append(boxChild);
                        }
                        reader.readAsDataURL(file[0]);
                    } else {
                        alert(file[0].name + " is not a valid image file.");
                        filePreview.html("");
                        return false;
                    }
                });
            } else {
                alert("This browser does not support HTML5 FileReader.");
            }
        });

        function isNumeric(value) {
            return /^-?\d+$/.test(value);
        }

        $(document).on('click', '.onoffswitch-label', function (e) {
            let div = $(this).parents('div.onoffswitch');
            let input = div.find('input');
            let id = input.attr('data-id');
            let checked = 1;
            if (confirm('Thay đổi trạng thái sẽ ảnh hưởng tới cư dân')) {
                if (input.attr('checked')) {
                    checked = 0;
                } else {
                    checked = 1;
                }
                showLoading();
                $.ajax({
                    url: input.attr('data-url'),
                    type: 'PUT',
                    data: {
                        id: id,
                        status: checked
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Không thay đổi trạng thái');
                            e.preventDefault();
                        }
                        hideLoading();
                    }
                });
            } else {
                e.preventDefault();
            }
        });

        $('.btn-js-action-add-document-apartment').on('click', function (e) {
            e.preventDefault();

            let alert_pop_add_document_apartment = $('.alert_pop_add_document_apartment');

            alert_pop_add_document_apartment.hide();

            let title = $('#title-document').attr('data-value');
            let description = $('#title-document').attr('data-value');
            let attach_files = [];
            let id = $('#id-apartment-document').attr('data-id');

            $('#filePreview_1 span').each((index, value) => {
                attach_files.push({
                    file_name: $(value).attr('data-name'),
                    hash_file: $(value).attr('data-base')
                })
            })

            let formData = new FormData();

            attach_files.forEach((item) => {
                let file = dataURLtoFile(item.hash_file, item.file_name);
                // listFiles.push(file);
                formData.append('attach_files[]', file);
            });

            let document_type = 2;
            let document_type_ids = [];

            formData.append('title', title);
            formData.append('description', description);
            formData.append('document_type', document_type);

            document_type_ids.push(id);

            if (attach_files.length == 0) {
                alert_pop_add_document_apartment.show();
                alert_pop_add_document_apartment.html('<li>File không được để  trông</li>')
            } else {
                document_type_ids = JSON.stringify(document_type_ids);
                // attach_files = JSON.stringify(attach_files);
                formData.append('document_type_ids', document_type_ids);
                showLoading();
                $.ajax({
                    url: '/admin/v3/document',
                    type: 'POST',
                    data: formData,
                    contentType: false, //tell jquery to avoid some checks
                    processData: false,
                    success: function (res) {
                        $('#add-document-apartment').modal('hide')
                        if (res.code === 0) {
                            alert("Thêm tài liệu thành công");
                            location.reload();
                        } else {
                            alert("Thêm tài liệu không thành công");
                            location.reload();
                        }
                        hideLoading();
                    },
                    error: function (e) {
                        console.log(e);
                        alert("Thêm tài liệu không thành công");
                        location.reload();
                        hideLoading();
                    }
                })
            }

        });

        function dataURLtoFile(dataurl, filename) {

            var arr = dataurl.split(','),
                mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[1]),
                n = bstr.length,
                u8arr = new Uint8Array(n);

            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }

            return new File([u8arr], filename, {type: mime});
        }

        sidebar('apartments', 'edit');
    </script>

@endsection
