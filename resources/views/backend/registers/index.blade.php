@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        KQ {{ $heading }}dfg
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

<section class="content">
    <h4 class="bg-success" style="margin: 0px 0px 15px; padding: 15px;">{{ $post->voucher_code ? '[' . $post->voucher_code . ']' : '' }} {{ $post->title }} ( <a href="{{ route('admin.posts.edit', ['id' => $post->id, 'type' => $type] ) }}">Chi tiết</a> )</h4>
    <div class="box box-primary">
        <div class="box-body ">
            <form id="form-search" action="{{ route('admin.posts.registers', ['id' => $post->id, 'type' => $type]) }}" method="get">
                <div class="row form-group">
                    <div class="col-sm-8">
                        <span class="btn-group hidden">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                            </ul>
                        </span>
                        @php
                        $param = Request::all();
                        $param['id'] = $post->id;
                        @endphp
                        <a href="{{ route('admin.posts.registers.export', $param) }}" class="btn btn-success" target="_blank"><i class="fa fa-file-excel-o"></i> Xuất excel</a>
                    </div>
                    <div class="col-sm-4 text-right">
                        <div class="input-group">
                            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Nhập từ khóa" class="form-control" />
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-info"><span class="fa fa-search"></span></button>
                                <button type="button" class="btn btn-warning btn-search-advance" data-toggle="show" data-target=".search-advance"><span class="fa fa-filter"></span></button>
                            </div>

                        </div>
                    </div>
                </div>
            </form><!-- END #form-search -->

            <form id="form-search-advance" action="{{ route('admin.posts.registers', ['id' => $post->id, 'type' => $type]) }}" method="get">
                <div id="search-advance" class="search-advance" style="display: {{ $advance ? 'block' : 'none' }};">
                    <div class="row form-group space-5">
                        <div class="col-sm-4">
                            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Người đăng ký" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <input type="text" name="phone" value="{{ $phone }}" placeholder="Điện thoại" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <input type="text" name="email" value="{{ $email }}" placeholder="Email" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <select name="check_in" class="form-control" style="width: 100%;">
                                <option value="">Check In</option>
                                <option value="1" {{ $check_in === '1' ? 'selected' : '' }}>Đã Check In</option>
                                <option value="0" {{ $check_in === '0' ? 'selected' : '' }}>Chưa Check In</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-warning btn-block">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </form><!-- END #form-search-advance -->

            <form id="form-registers" action="{{ route('admin.posts.registers.action', ['id' => $post->id, 'type' => $type]) }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="80">CODE</th>
                                <th width="150">Người đăng ký</th>
                                <th width="120">Điện thoại</th>
                                <th width="120">Email</th>
                                <th width="100">Căn hộ</th>
                                <th width="110">Cập nhật</th>
                                <th width="110">Check In</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($registers as $item)
                               @php
                                    $apartment=null;
                                    if($item->new == 1){
                                        $user_info_1 = App\Models\PublicUser\V2\UserInfo::where('user_id',$item->user_id)->first();
                                        $apartment = $user_info_1 ? App\Models\PublicUser\V2\UserInfoApartment::getApartmentByUserInfo($user_info_1->id): null;
                                    }else{
                                        $user_info_1 = App\Models\PublicUser\UserInfo::find($item->user_id);
                                        $apartment = @$item->pubUserinfo->bdcCustomers;
                                    }
                                    $apartment_name='';
                               @endphp
                                <tr>
                                    <td>{{ $item->code ?: '' }}</td>
                                    <td>{{ @$user_info_1->display_name??@$user_info_1->full_name?? 'không rõ' }}</td>
                                    <td>{{ @$user_info_1->phone??@$user_info_1->phone_contact?? 'không rõ' }}</td>
                                    <td>{{ @$user_info_1->email??@$user_info_1->email_contact?? 'không rõ' }}</td>
                                    <td>
                                        @foreach($apartment as $k => $re)
                                            <?php
                                                 if($item->new == 1){
                                                    $_apartment = App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($re->apartment_id);
                                                    $apartment_name .= $_apartment ? ' '.$_apartment->name : '';
                                                }else{
                                                    $apartment_name .= ' '.$re->bdcApartment->name??'';
                                                }
                                            ?>
                                        @endforeach
                                        {{$apartment_name}}
                                    </td>
                                    <td>{{ $item->updated_at }}</td>
                                    <td>{{ $item->check_in }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $registers->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $registers->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-registers">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </form><!-- END #form-registers -->
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    sidebar('{{ $type }}', 'index');
</script>

@endsection