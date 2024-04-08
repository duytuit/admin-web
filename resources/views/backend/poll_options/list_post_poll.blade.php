@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách bình chọn
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active"> Danh sách bình chọn</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <div class="form-group">
                 <a href="{{ route('admin.polloptions.export',['id'=>$id]) }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Xuất excel</a>
             </div>
            <form id="form-search-advance" action="" method="get">
                <div id="search-advance" class="search-advance" style="">
                    <div class="row form-group space-5">
                        <div class="col-sm-4">
                            <input type="text" name="apartment_name" value="{{@$filter['apartment_name']}}" placeholder="Căn hộ" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-warning btn-block">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </form><!-- END #form-search-advance -->

            <form id="form-registers" action="{{ route('admin.polloptions.action') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th width="80">STT</th>
                                <th width="150">Người bình chọn</th>
                                <th width="150">Phương án bình chọn</th>
                                <th width="120">Điện thoại</th>
                                <th width="120">Email</th>
                                <th width="100">Căn hộ</th>
                                <th width="110">Cập nhật</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($postPoll as $key => $item)
                                 @php
                                     $user_info = App\Models\PublicUser\V2\UserInfo::where('user_id',$item->user_id)->first();
                                     $user_apartment = $user_info ? App\Models\Apartments\V2\UserApartments::where(['user_info_id'=> $user_info->id,'building_id'=>$buildingId])->first():'';
                                     $apartment = $user_apartment ? App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($user_apartment->apartment_id): '';
                                     $PollOption = App\Models\PollOption::find($item->poll_id);
                                     $choose = '';
                                     if($PollOption){
                                        if($item->poll_key){
                                            $poll_key = json_decode($item->poll_key);
                                            $i=0;
                                            $count = count($PollOption->options);
                                            foreach ($PollOption->options as $key_1 => $item_1) {
                                                foreach ($poll_key as $item_2) {
                                                    if($item_2 == $key_1){
                                                        $i++;
                                                        $choose.= $item_1 . ($i < $count-1 ? ',': '');
                                                    }
                                                }
                                               
                                            }
                                        }
                                     }
                                   
                                 @endphp
                                <tr>
                                    <td>{{@($key + 1) + ($postPoll->currentpage() - 1) * $postPoll->perPage()}}</td>
                                    <td>{{$user_info->full_name}}</td>
                                    <td>{{$choose}}</td>
                                    <td>{{$user_info->phone_contact}}</td>
                                    <td>{{$user_info->email_contact}}</td>
                                    <td>{{$apartment->name}}</td>
                                    <td> 
                                       {{ $item->updated_at->format('d-m-Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $postPoll->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $postPoll->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-post-poll">
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
    sidebar('list_post_poll', 'index');
</script>

@endsection