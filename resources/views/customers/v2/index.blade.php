@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Cư dân 
            <a class="btn btn-success" title="Thêm cư dân" data-toggle="modal" data-target="#search-resident"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
            @if( in_array('admin.v2.customers.index_import',@$user_access_router)) 
               <a href="{{ route('admin.v2.customers.index_import') }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;Import Exel</a> 
            @endif 
            @if( in_array('admin.v2.customers.export',@$user_access_router))
               <a href="javascript:" class="btn btn-warning export_resident"><i class="fa fa-download"></i>&nbsp;&nbsp;Export Exel</a> 
            @endif
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Cư dân</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-customer" action="" method="post" style="display: inline-block;">
                    {{ csrf_field() }}
                    <div class="col-sm-1">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-customer-action" data-method="delete">
                                    <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                </a>
                            </li>
                            <li>
                                <a  href="javascript:" class="btn-send-mail" type="button" title="Gửi email" data-target="#send-mail-resident"><i class="fa fa-envelope text-danger"></i>&nbsp; Gửi email</a>
                            </li>
                            <li>
                                <a  href="javascript:" class="btn-send-sms" type="button" title="Gửi sms" data-toggle="modal" data-target="#send-mail-resident"><i class="fa fa-commenting text-danger"></i>&nbsp; Gửi sms</a>
                            </li>

                        </ul>
                    </div>
                    <div class="col-sm-11">
                        <div id="search-advance" class="search-advance">
                            <div class=" ">
                                <div class="form-group space-5" style="width: calc(100% - 55px);float: left;">
                                    <div class="col-sm-12" style="padding: 0;">
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="full_name" placeholder="Nhập từ khóa tìm kiếm" value="{{!empty(@$data_search['full_name'])?@$data_search['full_name']:''}}">
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="email" placeholder="Nhập email tìm kiếm" value="{{!empty(@$data_search['email'])?@$data_search['email']:''}}">
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="phone" placeholder="Nhập số điện thoại tìm kiếm" value="{{!empty(@$data_search['phone'])?@$data_search['phone']:''}}">
                                        </div>
                                        <div class="col-sm-2">
                                            <?php $building_place_id = !empty(@$data_search['place'])?@$data_search['place']:''; ?>
                                            <select name="building_place_id" id="ip-place" class="form-control" style="width: 100%">
                                                <option value="">Chọn tòa nhà</option>
                                                @if($building_place_id)
                                                    <option value="{{$building_place_id}}" selected>{{!empty(@$data_search['name_place']) ? @$data_search['name_place'] : ''}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="input-group datetimepicker" data-format="DD-MM-Y">
                                                <input type="text" name="birthday" id="ip-birthday" class="form-control" placeholder="Ngày sinh" value="{{ @$data_search['birthday'] ?? old('birthday') ?? ''}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="gender" id="ip-gender"  class="form-control">
                                                <option value="">Chọn giới tính</option>
                                                <?php $search_gender = !empty(@$data_search['gender'])?@$data_search['gender']:''; ?>
                                                <option value="1" @if($search_gender == 1)  selected @endif>Nam</option>
                                                <option value="2" @if($search_gender == 2)  selected @endif>Nữ</option>
                                                <option value="3" @if($search_gender == 3)  selected @endif>Khác</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group space-5" style="width: calc(100% - 55px);float: left;">
                                    <div class="col-sm-12" style="padding: 0;">
                                        <div class="col-sm-2">
                                            <select name="apartment_id" id="ip-apartment"  class="form-control">
                                                <option value="">Chọn căn hộ</option>
                                                <?php $search_apt = !empty(@$data_search['apartment'])?@$data_search['apartment']:''; ?>
                                                @if($search_apt)
                                                    <option value="{{$search_apt->id}}" selected>{{$search_apt->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="type" id="ip-type" class="form-control">
                                                <option value="" selected>Quan hệ với chủ hộ</option>
                                                <?php
                                                    $search_type = !empty(@$data_search['type'])?@$data_search['type']:'';
                                                ?>
                                                <option value="0" @if($search_type === 0) selected @endif>Chủ hộ</option>
                                                <option value="1" @if($search_type === 1) selected @endif>Vợ/Chồng</option>
                                                <option value="2" @if($search_type === 2) selected @endif>Con</option>
                                                <option value="3" @if($search_type === 3) selected @endif>Bố mẹ</option>
                                                <option value="4" @if($search_type === 4) selected @endif>Anh chị em</option>
                                                <option value="5" @if($search_type === 5) selected @endif>Khác</option>
                                                <option value="6" @if($search_type === 6) selected @endif>Khách thuê</option>
                                                <option value="8" @if($search_type === 8) selected @endif>Cháu</option>
                                                <option value="7" @if($search_type === 7) selected @endif>Chủ hộ cũ</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="birthday_day" id="birthday_day" class="form-control">
                                                <option value="">Ngày</option>
                                                <option value="1" @if(@$data_search['birthday_day'] == 1) selected @endif>Ngày 1</option>
                                                <option value="2" @if(@$data_search['birthday_day'] == 2) selected @endif>Ngày 2</option>
                                                <option value="3" @if(@$data_search['birthday_day'] == 3) selected @endif>Ngày 3</option>
                                                <option value="4" @if(@$data_search['birthday_day'] == 4) selected @endif>Ngày 4</option>
                                                <option value="5" @if(@$data_search['birthday_day'] == 5) selected @endif>Ngày 5</option>
                                                <option value="6" @if(@$data_search['birthday_day'] == 6) selected @endif>Ngày 6</option>
                                                <option value="7" @if(@$data_search['birthday_day'] == 7) selected @endif>Ngày 7</option>
                                                <option value="8" @if(@$data_search['birthday_day'] == 8) selected @endif>Ngày 8</option>
                                                <option value="9" @if(@$data_search['birthday_day'] == 9) selected @endif>Ngày 9</option>
                                                <option value="10" @if(@$data_search['birthday_day'] == 10) selected @endif>Ngày 10</option>
                                                <option value="11" @if(@$data_search['birthday_day'] == 11) selected @endif>Ngày 11</option>
                                                <option value="12" @if(@$data_search['birthday_day'] == 12) selected @endif>Ngày 12</option>
                                                <option value="13" @if(@$data_search['birthday_day'] == 13) selected @endif>Ngày 13</option>
                                                <option value="14" @if(@$data_search['birthday_day'] == 14) selected @endif>Ngày 14</option>
                                                <option value="15" @if(@$data_search['birthday_day'] == 15) selected @endif>Ngày 15</option>
                                                <option value="16" @if(@$data_search['birthday_day'] == 16) selected @endif>Ngày 16</option>
                                                <option value="17" @if(@$data_search['birthday_day'] == 17) selected @endif>Ngày 17</option>
                                                <option value="18" @if(@$data_search['birthday_day'] == 18) selected @endif>Ngày 18</option>
                                                <option value="19" @if(@$data_search['birthday_day'] == 19) selected @endif>Ngày 19</option>
                                                <option value="20" @if(@$data_search['birthday_day'] == 20) selected @endif>Ngày 20</option>
                                                <option value="21" @if(@$data_search['birthday_day'] == 21) selected @endif>Ngày 21</option>
                                                <option value="22" @if(@$data_search['birthday_day'] == 22) selected @endif>Ngày 22</option>
                                                <option value="23" @if(@$data_search['birthday_day'] == 23) selected @endif>Ngày 23</option>
                                                <option value="24" @if(@$data_search['birthday_day'] == 24) selected @endif>Ngày 24</option>
                                                <option value="25" @if(@$data_search['birthday_day'] == 25) selected @endif>Ngày 25</option>
                                                <option value="26" @if(@$data_search['birthday_day'] == 26) selected @endif>Ngày 26</option>
                                                <option value="27" @if(@$data_search['birthday_day'] == 27) selected @endif>Ngày 27</option>
                                                <option value="28" @if(@$data_search['birthday_day'] == 28) selected @endif>Ngày 28</option>
                                                <option value="29" @if(@$data_search['birthday_day'] == 29) selected @endif>Ngày 29</option>
                                                <option value="30" @if(@$data_search['birthday_day'] == 30) selected @endif>Ngày 30</option>
                                                <option value="31" @if(@$data_search['birthday_day'] == 31) selected @endif>Ngày 31</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="birthday_month" id="birthday_month" class="form-control">
                                                <option value="">Tháng</option>
                                                <option value="1" @if(@$data_search['birthday_month'] == 1) selected @endif>Tháng 1</option>
                                                <option value="2" @if(@$data_search['birthday_month'] == 2) selected @endif>Tháng 2</option>
                                                <option value="3" @if(@$data_search['birthday_month'] == 3) selected @endif>Tháng 3</option>
                                                <option value="4" @if(@$data_search['birthday_month'] == 4) selected @endif>Tháng 4</option>
                                                <option value="5" @if(@$data_search['birthday_month'] == 5) selected @endif>Tháng 5</option>
                                                <option value="6" @if(@$data_search['birthday_month'] == 6) selected @endif>Tháng 6</option>
                                                <option value="7" @if(@$data_search['birthday_month'] == 7) selected @endif>Tháng 7</option>
                                                <option value="8" @if(@$data_search['birthday_month'] == 8) selected @endif>Tháng 8</option>
                                                <option value="9" @if(@$data_search['birthday_month'] == 9) selected @endif>Tháng 9</option>
                                                <option value="10" @if(@$data_search['birthday_month'] == 10) selected @endif>Tháng 10</option>
                                                <option value="11" @if(@$data_search['birthday_month'] == 11) selected @endif>Tháng 11</option>
                                                <option value="12" @if(@$data_search['birthday_month'] == 12) selected @endif>Tháng 12</option>
                                               
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                           <div class="input-group datetimepicker" data-format="Y">
                                               <input type="text" name="birthday_from_year" id="ip-birthday" class="form-control" placeholder="Từ năm" value="{{!empty(@$data_search['birthday_from_year'])?@$data_search['birthday_from_year']:''}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                           <div class="input-group datetimepicker" data-format="Y">
                                              <input type="text" name="birthday_to_year" id="ip-birthday1" class="form-control" placeholder="Đến năm" value="{{!empty(@$data_search['birthday_to_year'])?@$data_search['birthday_to_year']:''}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                           </div>
                                        </div>
                                           <div class="input-group-btn" style="display: block;">
                                              <button type="submit" title="Tìm kiếm" class="btn btn-info" form="form-search-customer"><i class="fa fa-search"></i></button>
                                           </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <div class="table-responsive">
                    @if( in_array('admin.v2.customers.action',@$user_access_router))
                        <form action='{{ route('admin.v2.customers.action') }}' method="post" id="form-customer-action">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="method" value="" />
                                    <table class="table table-hover table-striped table-bordered no-select" id="noCopyTable">
                                        <thead class="bg-primary">
                                        <tr>
                                            <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                            <th width="30">Stt</th>
                                            <th>Họ và tên</th>
                                            <th>Email liên hệ</th>
                                            <th width="30">Phone liên hệ</th>
                                            <th width="90">Ngày sinh</th>
                                            <th width="130">Căn hộ</th>
                                            <th width="30">Mobile active</th>
                                            <th width="150">Thao tác</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @if ($residents)
                                                @foreach($residents as $key => $value)
                                                    <tr>
                                                        <td><input type="checkbox" name="ids[]" value="{{$value->user_info_id}}" class="iCheck checkSingle" /></td>
                                                        <td>{{$value->user_id}}</td>
                                                        <td> {{$value->full_name??''}} </td>
                                                        <td>{{$value->email_contact??''}}</td>
                                                        <td>{{$value->phone_contact??''}}</td>
                                                        <td>{{$value->birthday}}</td>
                                                        <td>{{@$value->name }} </td>
                                                        <td>{{@$user ? @$user->mobile_active : 0}}</td>
                                                        <td colspan="" rowspan="" headers="">
                                                            @if( in_array('admin.v2.customers.edit',@$user_access_router))
                                                                <a href="{{ route('admin.v2.customers.edit',['id'=> $value->user_info_id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                                            @endif
                                                            @if( in_array('admin.v2.customers.delete',@$user_access_router))
                                                                <a href="{{ route('admin.v2.customers.delete',['apartment_id'=> 0,'user_info_id'=> $value->user_info_id]) }}" class="btn btn-danger" title="xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa không?')"><i class="fa fa-times"></i></a>
                                                            @endif
                                                            <a href="javascript:void(0);" user_id="{{$value->user_id}}"class="btn btn-warning reset_password" title="Reset Password"><i class="fa fa-recycle"></i></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                            
                            @endif

                        </div>
                        <div class="row mbm">
                            @if ($residents)
                                    <div class="col-sm-3">
                                        <span class="record-total">Hiển thị: {{ @$residents->count()}} / {{ @$residents->total() }} kết quả</span>
                                    </div>
                                    <div class="col-sm-6 text-center">
                                        <div class="pagination-panel">
                                            {{ @$residents->appends(Request::all())->links() }}
                                        </div>
                                    </div>
                            @endif
                            <div class="col-sm-3 text-right">
                                <span class="form-inline">
                                    Hiển thị
                                    <select name="per_page" class="form-control" data-target="#form-customer-action">
                                        @php $list = [10, 20, 50, 100, 200]; @endphp
                                        @foreach ($list as $num)
                                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                </form>
            </div>
        </div>
        <div id="add-resident" class="modal fade" role="dialog">
            <div class="modal-dialog  modal-lg">
                <!-- Modal content-->
                @if( in_array('admin.v2.customers.insert',@$user_access_router))
                    <form action="{{ route('admin.v2.customers.insert') }}" method="post" id="form-add-resident" class="form-validate form-horizontal">
                        {{ csrf_field() }}
                        <input type="hidden" name="hashtag" id="hashtag">
                        <input type="hidden" name="user_info_id" id="user_info_id">
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
                                                <input type="text" name="name" id="in-re_name" class="form-control" placeholder="Tên cư dân">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Giới tính</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="radio" name="sex" id="in-re_sex_female"  value="1" /> Nam
                                                <input type="radio" name="sex" id="in-re_sex_male"   value="2" /> Nữ
                                            </div>
                                        </div>
                                        <div class="info_contact">
                                            <div class="form-group">
                                                <div class="col-sm-2">
                                                    <label>Email liên hệ</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <input type="text" name="email_contact" id="in-re_email" autocomplete="nope" class="form-control" placeholder="Email cư dân">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-2">
                                                    <label>Số điện thoại liên hệ</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <input type="text" name="phone_contact" id="in-re_phone" class="form-control" placeholder="Điện thoại cư dân">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Ngày sinh</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="input-group datetimepicker" data-format="DD-MM-Y">
                                                    <input type="text" name="create_birthday" id="create_birthday" class="form-control" placeholder="Ngày sinh" ><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Căn hộ</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <select name="apartment_id" id="ip-ap_id" class="form-control" style="width: 100%;">
                                                    <option value="">Chọn căn hộ</option>
                                                </select>
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
                                                    <option value="8">Cháu</option>
                                                    <option value="7">Chủ hộ cũ</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Gửi Thông Báo</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="radio" name="is_send" id="is_send_yes"  value="send" /> Yes
                                                <input type="radio" name="is_send" id="is_send_no"   value="unsend" /> No
                                            </div>
                                        </div>
                                        <div class="info_login">
                                            <hr>
                                            <p style="font-weight: bold;font-size:15px;">Thông tin đăng nhập</p>
                                            <div class="form-group">
                                                <div class="form-group" style="width: 600px;margin: 0 auto">
                                                    <div class="col-sm-6">
                                                        <label for="email">Email</label>
                                                        <input type="email" name="email" id="email" autocomplete="nope" class="form-control" placeholder="Email">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label for="phone">Số điện thoại</label>
                                                        <input type="text" name="phone" id="phone" autocomplete="nope" class="form-control" placeholder="Số điện thoại">
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <label for="phone">Mật khẩu</label>
                                                        <input type="text" name="pword" id="pword" autocomplete="nope" class="form-control" placeholder="Mật khẩu">
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-info"> <span class="text-danger">*</span> Hệ thống sẽ gửi thông tin tài khoản, căn hộ đến email cho cư dân.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="submit" id="submit">
                                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                                <button type="button" class="btn btn-primary btn-js-action" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
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
                <form id="form-search-resident" >
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
                                        <input type="text" id="searchEmailPhone" name="keyword" class="form-control" placeholder="Nhập email, số điện thoại">
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" class="btn btn-primary save_info" style="margin-right: 5px;margin-top: 25px;"><i class="fa fa-search"></i>Tìm</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <a href="javascript:;" id="save_info_show_modal" class="text_decoration">(*) Trường hợp cư dân chưa có số điện thoại, email, cmt</a>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                           
                        </div>
                    </div>
                </form>
            </div>
        </div>
@endsection
@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
    <style>
        .custom-dialog{
            top: 200px;
        }
        .no-select {
            -webkit-user-select: none; /* Safari */
            -moz-user-select: none; /* Firefox */
            -ms-user-select: none; /* IE10+/Edge */
            user-select: none; /* Standard */
        }
    </style>
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script>
        //Duong add for private
        const noCopyTable = document.getElementById('noCopyTable');
        noCopyTable.addEventListener('copy', function(event) {
            event.preventDefault(); 
        });
        document.getElementById('noCopyTable').addEventListener('contextmenu', function(event) {
            event.preventDefault();
        });
        document.getElementById('noCopyTable').addEventListener('keydown', function(event) {
            if (event.ctrlKey || event.metaKey) {
                event.preventDefault();
            }
        });

        $(function () {
            get_data_select_apartment({
                object: '#ip-apartment,#ip-ap_id',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            get_data_select_apartment1({
                object: '#ip-place',
                url: '{{ url('admin/apartments/ajax_get_building_place') }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn tòa nhà'
            });
            function get_data_select_apartment(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [{
                                id: '',
                                text: options.title_default
                            }];

                            for (i in json.data) {
                                var item = json.data[i];
                                results.push({
                                    id: item[options.data_id],
                                    text: item[options.data_text]
                                });
                            }
                            return {
                                results: results,
                            };
                        },
                        minimumInputLength: 3,
                    }
                });
            }

            function get_data_select_apartment1(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [{
                                id: '',
                                text: options.title_default
                            }];

                            for (i in json.data) {
                                var item = json.data[i];
                                results.push({
                                    id: item[options.data_id],
                                    text: item[options.data_text]+' - '+item[options.data_code]
                                });
                            }
                            return {
                                results: results,
                            };
                        },
                        minimumInputLength: 3,
                    }
                });
            }

            $(".btn-js-action").on('click',function () {
                var email = $("#in-re_email").val();
                var phone = $("#in-re_phone").val();
                var name = $("#in-re_name").val();
                var pass = $("#in-pub_pass").val();
                var passconfirm = $("#in-pub_pass_confirm").val();
                var apartment = $("#ip-ap_id").val();
                var rels = $("#in-re_relationship").val();

                $(".alert_pop_add_resident").hide();
                $(".alert_pop_add_resident ul").html('');
                var html = '';
                if(name.length <1 || name.length >=45){
                    html+='<li>Tên dân cư không được nhỏ hơn 3 hoặc lớn hơn 45 ký tự</li>';
                }if(email != '' && !isValidEmailAddress(email)){
                    html+='<li>Email dân cư không Đúng định dạng</li>';
                }if(phone != '' && isValidEmailAddress(phone)){
                    html+='<li>Email dân cư không được nhập ở trường số điện thoại</li>';
                }if(phone != '' && (phone.length <=9 || phone.length >= 12)){
                    html+='<li>Số điện thoại dân cư không dưới 10 và không lớn hơn 11 ký tự và không để trống</li>';
                }if(apartment == ''){
                    html +='<li>Phải chọn căn hộ</li>';
                }if(rels == ''){
                    html+='<li>Trường quan hệ không được để trống</li>';
                }if(pass != '' && pass <=8){
                    html +='<li>Mật khẩu phải 8 ký tự trở lên</li>';
                }
                if(pass && passconfirm <=8){
                    html +='<li>Xác nhận Mật khẩu phải 8 ký tự trở lên</li>';
                }
                if(pass != passconfirm){
                    html +='<li>Mật khẩu và Xác nhận mật khẩu không giống nhau</li>';
                }
                if(html){
                    $(".alert_pop_add_resident").show();
                    $(".alert_pop_add_resident ul").append(html);
                }

            });
            $('.reset_password').on('click',function () {
                var user_id = $(this).attr('user_id');
                if (!confirm('Bạn có chắc chắn reset mật khẩu của cư dân này?')) {
                    e.preventDefault();
                } else {
                    $.post('{{ url('/admin/v2/customers/resetPass') }}', {
                        user_id:  user_id
                    }, function(data) {
                        toastr.success(data.message);
                    });
                }


            });
            async function export_excel() {
                let method = 'get';
                let param_query_old = "{{ $array_search }}";
                let param_query = param_query_old.replaceAll("&amp;", "&")
                var headers = null;
                var export_excel = await call_api_export(method, 'admin/exportResidents' + param_query)
                var blob = new Blob(
                        [export_excel],
                        {type:export_excel.type}
                    );
                const url = URL.createObjectURL(blob)
                const link = document.createElement('a')
                link.download = 'ket_qua_export';
                link.href = url
                document.body.appendChild(link)
                link.click()
                document.body.removeChild(link);
            }
            $('.export_resident').click(function (e) { 
                e.preventDefault();
                export_excel();
            });
            var checked_cus = [];
            $('body').on('ifChecked', function(event){
                checked_cus.push(event.target.value);
            });
            $('body').on('ifUnchecked', function(event){
                checked_cus.splice(checked_cus.indexOf(event.target.value), 1);
            });

            $('#description_send_sms').trigger('load');

            $('.save_info').on('click',function (e) {
                    e.preventDefault();
                
                    let input = $('#searchEmailPhone').val();
                    var vnf_regex = /((09|03|07|08|05|06|02)+([0-9]{8})\b)/g;
                    if(isNumeric(input)){
                        if((input.length <=9 || input.length >= 12) || vnf_regex.test(input) == false){
                            html='<li>Số điện thoại dân cư không dưới 10 và không lớn hơn 11 ký tự và không để trống</li>';
                            $(".alert_pop_add_resident").show();
                            $(".alert_pop_add_resident ul").html(html);
                            hideLoading();
                            return;
                        }
                        $('#in-re_phone').val(input);
                        $('#phone').val(input);
                        $('#in-re_email').val('');
                        $('#email').val('');
                    }else{
                        if(!isValidEmailAddress(input)){
                            html='<li>Email dân cư không Đúng định dạng</li>';
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
                        }, function(data) {
                            console.log(data);
                            if(data.data.length == 0){
                                $('input[name=sex]').attr("disabled",false);
                                $('input[name=sex]').attr("disabled",false);
                                $('.info_login').css('display','block');
                                $('#in-re_name').attr('readonly', false);
                                $('#in-re_phone').attr('readonly', false);
                                $('#in-re_email').attr('readonly', false);
                                $('#create_birthday').attr('readonly', false);
                            }else{
                                $('input[name=sex]').attr("disabled",true);
                                $('input[name=sex]').attr("disabled",true);
                                data.data.userInfo.gender == 2 ?  $("#in-re_sex_male").prop("checked", true) :  $("#in-re_sex_female").prop("checked", true);
                                $('#in-re_name').val(data.data.userInfo.full_name).attr('readonly', true);
                                $('#in-re_phone').val(data.data.user.phone).attr('readonly', true);
                                $('#in-re_email').val(data.data.user.email).attr('readonly', true);
                                $('#create_birthday').val(data.data.userInfo.birthday ? moment(data.data.userInfo.birthday).format('DD-MM-YYYY') : '').attr('readonly', true);
                                $('#user_info_id').val(data.data.userInfo.id);
                                $('.info_login').css('display','none');
                            }
                            $('#hashtag').val(2);
                            $('.info_contact').css('display','block');
                            $('#search-resident').modal('hide');
                            $('#add-resident').modal('show');
                    });
            });
            $("#save_info_show_modal").on('click',function () {
                $('#hashtag').val(1);
                $('#phone').val('');
                $('#email').val('');
                $('#in-re_name').val('');
                $('#in-re_phone').val('');
                $('#in-re_email').val('');
                $('#create_birthday').val('');
                $('#user_info_id').val('');
                $('input[name=sex]').attr("disabled",false);
                $('input[name=sex]').attr("disabled",false);
                $('#in-re_name').attr('readonly', false);
                $('#in-re_phone').attr('readonly', false);
                $('#in-re_email').attr('readonly', false);
                $('#create_birthday').attr('readonly', false);
                $('.info_login').css('display','none');
                $('.info_contact').css('display','none');
                $('#search-resident').modal('hide');
                $('#add-resident').modal('show');
            })
            $(".btn-js-action").on('click',function () {
            showLoading();
            $(".alert_pop_add_resident").hide();
            var email = $("#in-re_email").val();
            var phone_number = $("#in-re_phone").val();
            var phone_number_login = $("#phone").val();
            var email_login = $("#email").val();
            var name = $("#in-re_name").val();
            var rels = $("#in-re_relationship").val();
            var html = '';
            var message ='';
            $.get('{{ route('admin.customers.ajax_check_type') }}', {
                type: $('#in-re_relationship').val(),
                aparment:  $("#ip-ap_id").val()
            }, function(data) {
                hideLoading();
                if(name.length <1 || name.length >=45){
                    html+='<li>Tên dân cư không được nhỏ hơn 3 hoặc lớn hơn 45 ký tự</li>';
                }if($('#hashtag').val() == 1 && email != '' && !isValidEmailAddress(email)){
                    html+='<li>Email dân cư không Đúng định dạng</li>';
                }if(email_login != '' && !isValidEmailAddress(email_login)){
                    html+='<li>Email đăng nhập dân cư không Đúng định dạng</li>';
                }if($('#hashtag').val() == 1 && phone_number != '' && isValidEmailAddress(phone_number) ){
                    html+='<li>Email dân cư không được nhập ở trường số điện thoại</li>';
                }if(phone_number_login != '' && isValidEmailAddress(phone_number_login)){
                    html+='<li>Email dân cư không được nhập ở trường số điện thoại</li>';
                }if($('#hashtag').val() == 1 && phone_number != '' && phone_number.length <=9 || phone_number.length >= 12 ){
                    html+='<li>Số điện thoại dân cư không dưới 10 và không lớn hơn 11 ký tự và không để trống</li>';
                }if(phone_number_login != '' && phone_number_login.length <=9 || phone_number_login.length >= 12){
                    html+='<li>Số điện thoại dân cư không dưới 10 và không lớn hơn 11 ký tự và không để trống</li>';
                }if(rels == ''){
                    html+='<li>Trường quan hệ không được để trống</li>';
                }
                if($('#hashtag').val() == 1 && data.status == 1){
                    html+='<li>Căn hộ này đã có chủ hộ</li>';
                }
                if(html){
                    $(".alert_pop_add_resident").show();
                    $(".alert_pop_add_resident ul").html(html);
                    return false;
                }
                if($('#hashtag').val() != 1 && data.status == 1){
                    var confirm = window.confirm(data.message);
                     if(confirm == true){
                        submitForm();
                     }else{
                        return false;
                     }
                }else{
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
                        if(data.success == true){
                            toastr.success(data.message);
                            setTimeout(function() {
                            location.reload();
                            }, 2000);
                        }else{
                            toastr.warning(data.message);
                        }
                    },
                    error: function (data) {
                        hideLoading();
                        toastr.error(data.message);
                        // setTimeout(function() {
                        //     location.reload();
                        // }, 2000);
                    }
                })
        };
        $('#in-re_phone,#phone').bind('keyup change',function (e) {
            $("#in-re_phone").val($(this).val());
            $("#phone").val($(this).val());
        });
        $('#in-re_email,#email').bind('keyup change',function (e) {
            $("#in-re_email").val($(this).val());
            $("#email").val($(this).val());
        });
        function isNumeric(value) {
            return /^-?\d+$/.test(value);
        }
        });
        sidebar('Customers', 'index');
    </script>


@endsection
