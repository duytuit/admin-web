@extends('backend.layouts.master')
@section('content')
    <section class="content-header">
        <h1>
            Thông tin lịch bảo trì
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thông tin lịch bảo trì</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row form-group">
                    <form id="form-search-advance" action="{{route('admin.v3.maintenance-asset.index')}}" method="get">
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="keyword" placeholder="Nhập từ khóa tìm kiếm" value="{{!empty($filter['keyword'])?$filter['keyword']:''}}">
                            </div>
{{--                            <div class="col-sm-2">--}}
{{--                                <select class="form-control cycle_year" name="cycle_year">--}}
{{--                                    <option value="{{\Carbon\Carbon::now()->year}}" {{@$filter['year'] == \Carbon\Carbon::now()->year? 'selected' :''}}>{{\Carbon\Carbon::now()->year}}</option>--}}
{{--                                    <option value="{{\Carbon\Carbon::now()->year + 1}}" {{@$filter['year'] == \Carbon\Carbon::now()->year +1? 'selected' :''}}>{{\Carbon\Carbon::now()->year + 1}}</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
                            <div class="col-sm-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form id="form-asset-detail" action="{{ route('admin.v3.maintenance-asset.action') }}" method="post">
                            @csrf
                            <input type="hidden" name="method" value=""/>
                            <input type="hidden" name="status" value=""/>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th >TT</th>
                                        <th >Tên tài sản</th>
                                        <th >Khu vực</th>
                                        <th class="text-center">Lịch bảo trì</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($asset_detail as $key => $item)
                                        @php
                                             // type_maintain: 1 là ngày ,1 là tháng
                                             // maintain_time:
                                             // convert sang ngày:
                                             $timeline = null;
                                             $last_time_maintain = \Carbon\Carbon::parse($item->last_time_maintain);
                                             if($item->type_maintain && $item->maintain_time){
                                                 if($item->type_maintain == 1){
                                                     for ($i=0;$i<12;$i++){
                                                         $timeline[]=$last_time_maintain->addDay((int)$item->maintain_time)->format('d/m/Y');
                                                     }
                                                 }
                                                 if($item->type_maintain == 2){
                                                     for ($i=0;$i<12;$i++){
                                                         $timeline[]=$last_time_maintain->addMonth((int)$item->maintain_time)->format('d/m/Y');
                                                     }
                                                 }
                                             }
                                        @endphp
                                        <tr>
                                            <td>{{@($key + 1) + ($asset_detail->currentpage() - 1) * $asset_detail->perPage()}}</td>
                                            <td >
                                                <div style="width: 250px;">
                                                    <a data-element="{{$item->id}}" target="_blank" href="/admin/v3/maintenance-asset/detail/{{$item->id}}" title="Chi tiết lịch bảo trì">{{ @$item->asset->name}}</a>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="width: 150px">{{ @$item->office->name}}</div>
                                            </td>
                                            <td>
                                                @if(@$timeline)
                                                    <div>
                                                        <ul id='timeline'>
                                                            @foreach($timeline as $key => $item)
                                                                @if($key % 2 == 0)
                                                                    <li class='event down'>
                                                                        <div class='content_timeline'>
                                                                            <div class="avatar">{{$item}}</div>
                                                                        </div>

                                                                        <div class="dot">
                                                                            <span class='circle'></span>
                                                                        </div>
                                                                    </li>
                                                                @else
                                                                    <li class='event up'>
                                                                        <div class='content_timeline'>
                                                                            <div class="avatar">{{$item}}</div>
                                                                        </div>
                                                                        <div class="dot">
                                                                            <span class='circle'></span>
                                                                        </div>
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </td>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="row mbm">
                                <div class="col-sm-3">
                                    <span class="record-total">Hiển thị: {{$asset_detail->count()}} / {{ $asset_detail->total() }} kết quả</span>
                                </div>
                                <div class="col-sm-6 text-center">
                                    <div class="pagination-panel">
                                        {{ $asset_detail->appends(array_merge(Request::all(),['tab'=>""]))->onEachSide(1)->links() }}
                                    </div>
                                </div>
                                <div class="col-sm-3 text-right">
                                <span class="form-inline">
                                    Hiển thị
                                    <select name="per_page" class="form-control" data-target="#form-asset-detail">
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
            </div>
        </div>
    </section>
@endsection
<style>
    #timeline {
        list-style: none;
        width: 100%;
        height: 70px;
        margin: 70px 5px 0;
        border-top: 6px solid #bada55;
        display: table;
        border-spacing: 30px 0;
    }
    #timeline li {
        display: table-cell;
        position: relative;
        min-width: 100px;
    }
    .circle {
        margin: 0 auto;
        top: -10px;
        left: 0;
        right: 0;
        width: 15px;
        height: 15px;
        background: #fff;
        border: 3px solid #f98263;
        border-radius: 50%;
        display: block;
        position: absolute;
    }
    .content_timeline {
        text-align: center;
        position: relative;
    }
    .avatar {
        width: 120px;
        height: 30px;
        display: block;
        border-radius: 5%;
        border: 2px solid #bada55;
        position: absolute;
        margin: 0 auto;
        left: -10px;
        right: 0;
        text-align: center;
        line-height: 25px;
        background: #fffff0;
    }
    .content_timeline:before {
        content: '';
        height: 40px;
        left: 50%;
        margin-left: -2px;
        position: absolute;
    }
    li.down .content_timeline:before {
        border-left: 4px solid #f98262;
        border-bottom: 4px solid #f98262;
        -webkit-border-radius: 0 20px;
        -moz-border-radius: 0 20px;
        border-radius: 0 20px;
    }
    li.down .avatar {
        top: 25px;
        left: -10px;
    }
    li.up .content_timeline {
        margin-top: -40px;
    }
    li.up .content_timeline:before {
        border-left: 4px solid #f98262;
    }
    li.up .date {
        margin-top: 106px;
    }
    li.up .avatar {
        background: #fffff0;
        margin-top: -15px;
    }
    .rectangle {
        border: 1px solid #f98262;
        border-radius: 30px;
        height: 30px;
        width: 200px;
        top: -19px;
        position: relative;
        background: #fffff0;
        line-height: 30px;
        text-align: center;
    }
</style>
@section('javascript')
    <script>

    </script>
@endsection
