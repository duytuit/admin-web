@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Cư dân <a class="btn btn-success" title="Thêm cư dân" data-toggle="modal" data-target="#add-resident"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a> @if( in_array('admin.customers.index_import',@$user_access_router)) <a href="{{ route('admin.customers.index_import') }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;Import Exel</a> @endif <p class="display_mes_summit @if($data_error) error_mes @elseif($data_success) success_mes @endif"> {{$data_cus}} </p>  @if( in_array('admin.customers.export',@$user_access_router)) <a href="{{ route('admin.customers.export') }}" class="btn btn-warning"><i class="fa fa-download"></i>&nbsp;&nbsp;Export Exel</a> @endif
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
                                            <input type="text" class="form-control" name="keyword" placeholder="Nhập từ khóa tìm kiếm" value="{{!empty($data_search['keyword'])?$data_search['keyword']:''}}">
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="email" placeholder="Nhập email tìm kiếm" value="{{!empty($data_search['email'])?$data_search['email']:''}}">
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="phone" placeholder="Nhập số điện thoại tìm kiếm" value="{{!empty($data_search['phone'])?$data_search['phone']:''}}">
                                        </div>
                                        <div class="col-sm-2">
                                            <?php $building_place_id = !empty($data_search['place'])?$data_search['place']:''; ?>
                                            <select name="place" id="ip-place" class="form-control" style="width: 100%">
                                                <option value="">Chọn tòa nhà</option>
                                                @if($building_place_id)
                                                    <option value="{{$building_place_id}}" selected>{{!empty($data_search['name_place']) ? $data_search['name_place'] : ''}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="input-group datetimepicker" data-format="DD-MM-Y">
                                                <input type="text" name="birthday" id="ip-birthday" class="form-control" placeholder="Ngày sinh" value="{{ $data_search['birthday'] ?? old('birthday') ?? ''}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="gender" id="ip-gender"  class="form-control">
                                                <option value="">Chọn giới tính</option>
                                                <?php $search_gender = !empty($data_search['gender'])?$data_search['gender']:''; ?>
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
                                            <select name="apartment" id="ip-apartment"  class="form-control">
                                                <option value="">Chọn căn hộ</option>
                                                <?php $search_apt = !empty($data_search['apartment'])?$data_search['apartment']:''; ?>
                                                @if($search_apt)
                                                    <option value="{{$search_apt->id}}" selected>{{$search_apt->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="type" id="ip-type" class="form-control">
                                                <option value="">Quan hệ với chủ hộ</option>
                                                <?php
                                                    $search_type = !empty($data_search['type'])?$data_search['type']:'';
                                                ?>
                                                <option value="0" @if($search_type == 0) selected @endif>Chủ hộ</option>
                                                <option value="1" @if($search_type == 1) selected @endif>Vợ/Chồng</option>
                                                <option value="2" @if($search_type == 2) selected @endif>Con</option>
                                                <option value="3" @if($search_type == 3) selected @endif>Bố mẹ</option>
                                                <option value="4" @if($search_type == 4) selected @endif>Anh chị em</option>
                                                <option value="5" @if($search_type == 5) selected @endif>Khác</option>
                                                <option value="6" @if($search_type == 6) selected @endif>Khách thuê</option>
                                                <option value="7" @if($search_type == 7) selected @endif>Chủ hộ cũ</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="birthday_day" id="birthday_day" class="form-control">
                                                <option value="">Ngày</option>
                                                <option value="1" @if($data_search['birthday_day'] == 1) selected @endif>Ngày 1</option>
                                                <option value="2" @if($data_search['birthday_day'] == 2) selected @endif>Ngày 2</option>
                                                <option value="3" @if($data_search['birthday_day'] == 3) selected @endif>Ngày 3</option>
                                                <option value="4" @if($data_search['birthday_day'] == 4) selected @endif>Ngày 4</option>
                                                <option value="5" @if($data_search['birthday_day'] == 5) selected @endif>Ngày 5</option>
                                                <option value="6" @if($data_search['birthday_day'] == 6) selected @endif>Ngày 6</option>
                                                <option value="7" @if($data_search['birthday_day'] == 7) selected @endif>Ngày 7</option>
                                                <option value="8" @if($data_search['birthday_day'] == 8) selected @endif>Ngày 8</option>
                                                <option value="9" @if($data_search['birthday_day'] == 9) selected @endif>Ngày 9</option>
                                                <option value="10" @if($data_search['birthday_day'] == 10) selected @endif>Ngày 10</option>
                                                <option value="11" @if($data_search['birthday_day'] == 11) selected @endif>Ngày 11</option>
                                                <option value="12" @if($data_search['birthday_day'] == 12) selected @endif>Ngày 12</option>
                                                <option value="13" @if($data_search['birthday_day'] == 13) selected @endif>Ngày 13</option>
                                                <option value="14" @if($data_search['birthday_day'] == 14) selected @endif>Ngày 14</option>
                                                <option value="15" @if($data_search['birthday_day'] == 15) selected @endif>Ngày 15</option>
                                                <option value="16" @if($data_search['birthday_day'] == 16) selected @endif>Ngày 16</option>
                                                <option value="17" @if($data_search['birthday_day'] == 17) selected @endif>Ngày 17</option>
                                                <option value="18" @if($data_search['birthday_day'] == 18) selected @endif>Ngày 18</option>
                                                <option value="19" @if($data_search['birthday_day'] == 19) selected @endif>Ngày 19</option>
                                                <option value="20" @if($data_search['birthday_day'] == 20) selected @endif>Ngày 20</option>
                                                <option value="21" @if($data_search['birthday_day'] == 21) selected @endif>Ngày 21</option>
                                                <option value="22" @if($data_search['birthday_day'] == 22) selected @endif>Ngày 22</option>
                                                <option value="23" @if($data_search['birthday_day'] == 23) selected @endif>Ngày 23</option>
                                                <option value="24" @if($data_search['birthday_day'] == 24) selected @endif>Ngày 24</option>
                                                <option value="25" @if($data_search['birthday_day'] == 25) selected @endif>Ngày 25</option>
                                                <option value="26" @if($data_search['birthday_day'] == 26) selected @endif>Ngày 26</option>
                                                <option value="27" @if($data_search['birthday_day'] == 27) selected @endif>Ngày 27</option>
                                                <option value="28" @if($data_search['birthday_day'] == 28) selected @endif>Ngày 28</option>
                                                <option value="29" @if($data_search['birthday_day'] == 29) selected @endif>Ngày 29</option>
                                                <option value="30" @if($data_search['birthday_day'] == 30) selected @endif>Ngày 30</option>
                                                <option value="31" @if($data_search['birthday_day'] == 31) selected @endif>Ngày 31</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="birthday_month" id="birthday_month" class="form-control">
                                                <option value="">Tháng</option>
                                                <option value="1" @if($data_search['birthday_month'] == 1) selected @endif>Tháng 1</option>
                                                <option value="2" @if($data_search['birthday_month'] == 2) selected @endif>Tháng 2</option>
                                                <option value="3" @if($data_search['birthday_month'] == 3) selected @endif>Tháng 3</option>
                                                <option value="4" @if($data_search['birthday_month'] == 4) selected @endif>Tháng 4</option>
                                                <option value="5" @if($data_search['birthday_month'] == 5) selected @endif>Tháng 5</option>
                                                <option value="6" @if($data_search['birthday_month'] == 6) selected @endif>Tháng 6</option>
                                                <option value="7" @if($data_search['birthday_month'] == 7) selected @endif>Tháng 7</option>
                                                <option value="8" @if($data_search['birthday_month'] == 8) selected @endif>Tháng 8</option>
                                                <option value="9" @if($data_search['birthday_month'] == 9) selected @endif>Tháng 9</option>
                                                <option value="10" @if($data_search['birthday_month'] == 10) selected @endif>Tháng 10</option>
                                                <option value="11" @if($data_search['birthday_month'] == 11) selected @endif>Tháng 11</option>
                                                <option value="12" @if($data_search['birthday_month'] == 12) selected @endif>Tháng 12</option>
                                               
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                           <div class="input-group datetimepicker" data-format="Y">
                                               <input type="text" name="birthday_from_year" id="ip-birthday" class="form-control" placeholder="Từ năm" value="{{!empty($data_search['birthday_from_year'])?$data_search['birthday_from_year']:''}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                           <div class="input-group datetimepicker" data-format="Y">
                                              <input type="text" name="birthday_to_year" id="ip-birthday1" class="form-control" placeholder="Đến năm" value="{{!empty($data_search['birthday_to_year'])?$data_search['birthday_to_year']:''}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
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
                    @if( in_array('admin.customers.action',@$user_access_router))
                        <form action='{{ route('admin.customers.action') }}' method="post" id="form-customer-action">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="method" value="" />
                                    <table class="table table-hover table-striped table-bordered">
                                        <thead class="bg-primary">
                                        <tr>
                                            <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                            <th width="30">Stt</th>
                                            <th>Họ và tên</th>
                                            <th>Email</th>
                                            <th width="30">Mobile</th>
                                            <th width="90">Ngày sinh</th>
                                            <th width="130">Căn hộ</th>
                                            <th width="30">Mobile active</th>
                                            <th width="150">Thao tác</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($customers as $key => $c)
                                            <tr>
                                                <?php
                                                     $get_profile = App\Models\PublicUser\UserInfo::get_detail_user_info_by_id($c->pub_user_profile_id);
                                                     $user =$get_profile->pub_user_id > 0 ? App\Models\PublicUser\Users::get_detail_user_by_user_id($get_profile->pub_user_id) : null;
                                                     $apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($c->bdc_apartment_id);
                                                ?>
                                                <td><input type="checkbox" name="ids[]" value="{{$c->id}}" class="iCheck checkSingle" /></td>
                                                <td>{{$c->id}}</td>

                                                <td>
                                                    {{$get_profile->display_name??''}}
                                                </td>
                                                <td>{{$get_profile->email??''}}</td>
                                                <td>{{$get_profile->phone??''}}</td>
                                                <td>{{$get_profile->birthday ? date('d/m/Y',strtotime($get_profile->birthday)) :''}}</td>
                                                <td>
                                                    {{ @$apartment->name }}
                                                </td>
                                                <td>{{@$user ? @$user->mobile_active : 0}}</td>
                                                <td colspan="" rowspan="" headers="">
                                                    @if( in_array('admin.customers.edit',@$user_access_router))
                                                        <a href="{{ route('admin.customers.edit',['id'=> $c->pub_user_profile_id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                                    @endif
                                                    @if( in_array('admin.customers.delete',@$user_access_router))
                                                        <a href="{{ route('admin.customers.delete',['id'=> $c->id]) }}" class="btn btn-danger" title="xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa không?')"><i class="fa fa-times"></i></a>
                                                    @endif
                                                    @if(!empty($get_profile->email) || !empty($get_profile->phone))
                                                            <a href="javascript:void(0);" email="{{$get_profile->email}}" phone="{{$get_profile->phone}}" class="btn btn-warning reset_password" title="Reset Password"><i class="fa fa-recycle"></i></a>
                                                    @endif

                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                            
                            @endif

                        </div>
                        <div class="row mbm">
                            <div class="col-sm-3">
                                <span class="record-total">Hiển thị: {{$display_count}} / {{ $customers->total() }} kết quả</span>
                                <p>Tổng số đăng nhập trên App: {{$count_mobile_active}}</p>
                            </div>
                            <div class="col-sm-6 text-center">
                                <div class="pagination-panel">
                                    {{ $customers->appends(Request::all())->onEachSide(1)->links() }}
                                </div>
                            </div>
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
                @if( in_array('admin.customers.insert',@$user_access_router))
                    <form action="{{ route('admin.customers.insert') }}" method="post" id="form-add-resident" class="form-validate form-horizontal">
                        {{ csrf_field() }}
                        <input type="hidden" name="hashtag">
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
                                                <input type="radio" name="sex" id="in-re_sex" class="" value="1" checked />Nam
                                                <input type="radio" name="sex" id="in-re_sex" class="" value="2" />Nữ
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Nhập email</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" name="email" id="in-re_email" autocomplete="nope" class="form-control" placeholder="Email cư dân">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Nhập Số điện thoại</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" name="phone" id="in-re_phone" class="form-control" placeholder="Điện thoại cư dân">
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
                                                <select name="bdc_apartment_id" id="ip-ap_id" class="form-control" style="width: 100%;">
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
                                                    <option value="">Chọn quan hệ</option>
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
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Mật khẩu</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="password" name="pub_pass" id="in-pub_pass" autocomplete="nope" class="form-control" placeholder="Mật khẩu">
                                            </div>

                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Xác nhận mật khẩu</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="password" name="pub_pass_confirm" id="in-pub_pass_confirm" class="form-control" placeholder="Xác nhận mật khẩu">
                                            </div>
                                        </div>
                                                <p class="text-info"> <span class="text-danger">*</span>nếu không nhập mật khẩu hệ thống sẽ tạo mật khẩu và gửi đến email cho cư dân.</p>

                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="summit" value="customer">
                                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                                <button type="button" class="btn btn-primary btn-js-action" form="form-add-resident" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
        <div id="send-mail-resident" class="modal fade" role="dialog">
            <div class="modal-dialog  modal-lg">
                <!-- Modal content-->
                <form action="{{ route('admin.customers.sendMailChecked') }}" method="post" id="form-semd-mail-resident" class="form-validate form-horizontal">
                    {{ csrf_field() }}
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Gửi Email cho cư dân</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row">
                                <input type="text" name="list_customer" class="list_check">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label for="in-re_name">Tiêu đề</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="title_send_mail" id="in-title_send_mail" class="form-control" placeholder="tiêu đề">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label for="in-re_name">Nội dung</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <textarea name="description_send_mail" id="description_send_mail" cols="30" rows="5" placeholder="Nội dung"  class="form-control"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="summit" value="customer">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="submit" class="btn btn-primary" form="form-semd-mail-resident" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <div id="send-sms-resident" class="modal fade" role="dialog">
            <div class="modal-dialog  modal-lg">
                <!-- Modal content-->
                <form action="{{ route('admin.customers.sendSmsChecked') }}" method="post" id="form-semd-sms-resident" class="form-validate form-horizontal">
                    {{ csrf_field() }}
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Gửi sms cho cư dân</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row">
                                <input type="text" name="list_customer" class="list_check">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label for="in-re_name">Nội dung</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <textarea name="description_send_sms" id="description_send_sms" cols="30" rows="5" placeholder="Nội dung"  class="form-control"></textarea>
                                            <span class="countdown"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="summit" value="customer">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="submit" class="btn btn-primary" form="form-semd-sms-resident" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </section>
@endsection
@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script>
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

            $('#in-re_phone,#in-re_name,#in-re_relationship,#in-re_email,#ip-ap_id,#in-pub_pass,#in-pub_pass_confirm').bind('keyup change',delay_key(function (e) {
                $(".alert_pop_add_resident").hide();
                var email = $("#in-re_email").val();
                var phone_number = $("#in-re_phone").val();
                var name = $("#in-re_name").val();
                var rels = $("#in-re_relationship").val();
                var pass = $("#in-pub_pass").val();
                var passconfirm = $("#in-pub_pass_confirm").val();
                var apartment = $("#ip-ap_id").val();
                var html  = '';
                if(email== '' || phone_number == '' || name == '' || rels=='' || pass=='' || passconfirm=='' || apartment==''){
                    $('.btn-js-action').attr({
                        type:'button'
                    }).removeAttrs('onclick');
                }
                if( name != '' && rels!=''){
                    showLoading();
                    $.get('{{ route('admin.customers.ajax_check_type') }}', {
                        type: rels,
                        aparment:  apartment
                    }, function(data) {
                        if(rels == 0 && (email != '' || phone_number != '')){
                            if(data['status']==1){
                                hideLoading();
                                $('.btn-js-action').attr({
                                    type:'submit',
                                    onclick:'return confirm("'+data.message+'")',
                                });
                            }else{
                                hideLoading();
                                $('.btn-js-action').attr({
                                    type:'submit',
                                });
                            }
                        }else if(rels==2 && (email == '' && phone_number == '')){
                            hideLoading();
                            $('.btn-js-action').attr({
                                type:'submit',
                            });
                        }else if(rels==5 && (email == '' && phone_number == '')){
                            hideLoading();
                            $('.btn-js-action').attr({
                                type:'submit'
                            });
                        }else if(rels == 0 && (email == '' && phone_number == '')){
                            hideLoading();
                            $('.btn-js-action').attr({
                                type:'button'
                            }).removeAttrs('onclick');
                            html+='<li>Căn hộ này đã có chủ hộ, nếu bạn muốn thay đổi phải điền đầu đủ trường Email hoặc Số điện thoại</li>';
                        }else if(rels==0 && email != '' && phone_number == ''){
                            if(data['status']==1){
                                hideLoading();
                                $('.btn-js-action').attr({
                                    type:'submit',
                                    onclick:'return confirm("'+data.message+'")',
                                });
                            }else{
                                hideLoading();
                                $('.btn-js-action').attr({
                                    type:'submit',
                                });
                            }
                            // html+='<li>Căn hộ chưa có chủ hộ, nếu muốn thêm phải điền đầy đủ trường Email, số điện thoại và căn hộ</li>';
                        }else if(rels==0 && email == '' && phone_number != ''){
                            if(data['status']==1){
                                hideLoading();
                                $('.btn-js-action').attr({
                                    type:'submit',
                                    onclick:'return confirm("'+data.message+'")',
                                });
                            }else{
                                hideLoading();
                                $('.btn-js-action').attr({
                                    type:'submit',
                                });
                            }
                            // html+='<li>Căn hộ chưa có chủ hộ, nếu muốn thêm phải điền đầy đủ trường Email, số điện thoại và căn hộ</li>';
                        }else if(rels==0 && (email == '' || phone_number == '')){
                            hideLoading();
                            $('.btn-js-action').attr({
                                type:'button'
                            }).removeAttrs('onclick');
                            html+='<li>Căn hộ chưa có chủ hộ, nếu muốn thêm phải điền đầy đủ trường Email, số điện thoại và căn hộ</li>';
                        }else{

                              var vnf_regex = /((09|03|07|08|05|06|02)+([0-9]{8})\b)/g;
                            if(name.length <3 || name.length >=45){
                                html+='<li>Tên dân cư không được nhỏ hơn 3 hoặc lớn hơn 45 ký tự</li>';
                            }if(email != '' && !isValidEmailAddress(email)){
                                html+='<li>Email dân cư không Đúng định dạng</li>';
                            }if(phone_number != '' && isValidEmailAddress(phone_number)){
                                html+='<li>Email dân cư không được nhập ở trường số điện thoại</li>';
                            }if(phone_number != '' && (phone_number.length <=9 || phone_number.length >= 12) || vnf_regex.test(phone_number) == false){
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
                            if(!html){
                                hideLoading();
                                $('.btn-js-action').attr({
                                    type:'submit'
                                }).removeAttrs('onclick');
                            }else{
                                $(".alert_pop_add_resident").show();
                                $(".alert_pop_add_resident ul").html(html);
                                hideLoading();
                            }
                        }
                    });
                }
                setTimeout(function(){
                    if(html){
                        $(".alert_pop_add_resident").show();
                        $(".alert_pop_add_resident ul").html(html);
                        hideLoading();
                    }
                }, 600);

            },800));

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
                if(name.length <3 || name.length >=45){
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
               /* $('#in-re_relationship').on('change',function () {
                    var _this = $(this);
                    $.get('{{ route('admin.customers.ajax_check_type') }}', {
                        type: _this.val(),
                        aparment:  $('#ip-ap_id').val()
                    }, function(data) {
                        if(data.status == 1){
                            $('.btn-js-action').attr({
                                type:'submit',
                                onclick:'return confirm("'+data.message+'")',
                            })
                        }else{
                            $('.btn-js-action').attr('type','submit').removeAttrs('onclick');
                        }
                    });
                });*/

            });
            $('.reset_password').on('click',function () {
                var email = $(this).attr('email');
                var phone = $(this).attr('phone');
                var input = '';
                if((email !='' && phone!='') || (email !='' && phone=='')){
                    input = email;
                }
                if(email =='' && phone!=''){
                    input = phone;
                }
                if (!confirm('Bạn có chắc chắn reset mật khẩu của cư dân này?')) {
                    e.preventDefault();
                } else {
                    $.post('{{ url('/api/v1/reset-password/new') }}', {
                        email:  input
                    }, function(data) {
                        toastr.success(data.message);
                    });
                }


            });
            var checked_cus = [];
            $('body').on('ifChecked', function(event){
                checked_cus.push(event.target.value);
            });
            $('body').on('ifUnchecked', function(event){
                checked_cus.splice(checked_cus.indexOf(event.target.value), 1);
            });
            $('.btn-send-mail').on('click',function () {
                $('#send-mail-resident .list_check').val(checked_cus);
                $('#send-mail-resident').modal('show');
            });

            $('#description_send_sms').trigger('load');
            $('.btn-send-sms').on('click',function () {
                $('#send-sms-resident .list_check').val(checked_cus);
                $('#send-sms-resident').modal('show');
                $('#description_send_sms')

            })
        });
        $('#description_send_sms').on("load propertychange keyup input paste",
            function () {
                var limit = 160;
                var remainingChars = limit - $(this).val().length;
                if (remainingChars <= 0) {
                    $(this).val($(this).val().substring(0, limit));
                }
                $(".countdown").text(remainingChars<=0?0+' Ký tự không dấu':remainingChars+' Ký tự không dấu');
            });

        sidebar('Customers', 'index');
    </script>


@endsection
