@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Chi tiết trạng thái
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">{{ $heading }}</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                    <form id="form-search" action="{{ route('admin.notification.campain-detail', ['id'=>$id]) }}" method="get">
                        <div class="row form-group">
                            <div class="col-sm-7">
                                <p >Tiêu đề:{{$campain->title}}</p>
                                <p>
                                    Tổng Email: {{json_decode($campain->total)->email}} Thành công: {{@$campain->sended_email ?? 0}} Thất bại: {{@$campain->response->failMail ?? 0}}
                               </p> 
                               <p>
                                    Tổng App: {{json_decode($campain->total)->app}} Thành công: {{@$campain->sended_app ?? 0}} Thất bại: {{@$campain->response->failApp ?? 0}}
                               </p> 
                               <p>
                                    Tổng Sms: {{json_decode($campain->total)->sms}} Thành công: {{@$campain->sended_sms ?? 0}} Thất bại: {{@$campain->response->failSms ?? 0}}
                               </p>
                            </div>
                            <div class="col-sm-2">
                                    <select class="form-control" name="type" data-type="{{@$filter['type']}}" id="select-type">
                                      <option value="email">Email</option>
                                      <option value="app">App</option>
                                      <option value="sms">Sms</option>
                                    </select>
                            </div>
                            <div class="col-sm-3 text-right">

                                <div class="input-group">
                                    <input type="text" name="keyword" value="{{ @$filter['keyword'] }}" placeholder="Tìm liên hệ"
                                        class="form-control" />
                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-info"><span
                                                class="fa fa-search"></span></button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form><!-- END #form-search -->

                <form id="form-posts" action="{{ route('admin.notification.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th width="300">Contact</th>
                                    <th width="100">Trạng thái</th>
                                    <th width="400">Nguyên nhân</th>
                                    <th width="150">Ngày gửi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($posts as $item)
                                    <tr valign="middle">
                                        <td>{{@$item->contact}}</td>
                                        @if ($item->status == true)
                                            <td>Thành công</td>
                                        @else
                                            <td>Thất bại</td>
                                        @endif
                                        <td>{{ json_encode( @$item->reason)}}</td>
                                        <td>{{ @$item->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $posts->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $posts->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control" data-target="#form-posts">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                        <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>
                                            {{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
<script>
    $(function(){
        var type = $('#select-type').data('type');
        $('#select-type').val(type);

        $('#select-type').change(function(){
            window.location.href = location.origin+location.pathname+"?type="+$('#select-type').val()+"&keyword="+$('input[name=keyword]').val();
        });
    })
</script>
@endsection
