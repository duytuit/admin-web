<div id="doi_tac"
    class="tab-pane active">
        <div class="row">
            <div class="col-xs-12">
                 <div class="col-sm-2">
                     <button class="btn btn-info" data-toggle="modal" data-target="#createBusinessPartner"><i
                            class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới đối tác</button>
                 </div>
                <div class="col-sm-8">
                    <form action="" method="get" id="form-search">
                        <div id="search-advance" class="search-advance">
                            <div class="row form-group">
                                <div class="col-sm-10">
                                    <input type="text" name="business_partners_keyword" class="form-control"
                                        placeholder="Nhập nội dung tìm kiếm" value="{{ @$filter_business_partners['business_partners_keyword'] }}">
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <!-- /.box-header -->
    <form action="{{ route('admin.business-partners.action') }}" method="post" id="form-partners">
        {{ csrf_field() }}
        @method('post')
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="bg-primary">
                            <!-- <th width='20px'>
                                <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                            </th> -->
                            <th width='20px'>STT</th>
                            <th width='30%'>Tên đối tác</th>
                            <th width='15%'>SĐT</th>
                            <th width='10%'>Email</th>
                            <th width='10%'>Địa chỉ</th>
                            <th width='10%'>Đơn vị cung cấp</th>
                            <th width='10%'>Trạng thái</th>
                            <th width='20%'>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if(!$business_partners->isEmpty())
                        @foreach($business_partners as $key => $partners)
                        <tr>
                            <!-- <td><input type="checkbox" class="iCheck checkSingle" value="{{$partners->id}}" name="ids[]" />
                            </td> -->
                            <td>{{ @($key + 1) + ($business_partners->currentPage() - 1) * $business_partners->perPage() }}</td>
                            <td>
                                {{ $partners->name }} </a>
                            </td>
                            <td>
                                {{ $partners->mobile}}
                            </td>
                             <td>
                               {{ $partners->email}} 
                            </td>
                            <td>
                                {{ $partners->address}} 
                            </td>
                            <td>
                               {{ $partners->contact}} 
                            </td>
                            <td>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox"
                                        data-id="{{ $partners->id }}"
                                        id="myonoffswitch_{{ $partners->id }}"
                                        data-url="{{ route('admin.business-partners.change-status') }}"
                                        @if($partners->status == 1) checked @endif >
                                        <label class="onoffswitch-label" for="myonoffswitch_{{ $partners->id }}">
                                            <span class="onoffswitch-inner"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                </div>
                            </td>
                            <td>
                            <a id="{{$partners->id}}" type="button" class="btn btn-sm btn-info edit"
                                    title="Sửa"><i class="fa fa-edit"></i></a>

                                    <a title="Xóa" href="javascript:;"
                                    data-url="{{ route('admin.business-partners.delete') }}"
                                    data-id="{{ $partners->id }}" class="btn btn-sm btn-delete btn-danger"><i
                                        class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="9" class="text-center">
                                <p>Chưa có danh sách đối tác nào</p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                <input type="submit" class="js-submit-form-index hidden" value="" />
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix">
        <div class="row">
                <div class="col-sm-3">
                    <span class="record-total">Tổng: {{ $business_partners->count() }} /
                        {{ $business_partners->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $business_partners->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page_business_partners" class="form-control" data-target="#form-permission">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page_business_partners ? 'selected' : '' }}>{{ $num }}
                            </option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
        </div>
    </form>
</div>
@include('businesspartners.modals.businesspartner')
@section('stylesheet')
    <style>
        .onoffswitch {
            position: relative;
            width: 70px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .onoffswitch-checkbox {
            display: none;
        }

        .onoffswitch-label {
            display: block;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #999999;
            border-radius: 16px;
        }

        .onoffswitch-inner {
            display: block;
            width: 200%;
            margin-left: -100%;
            transition: margin 0.3s ease-in 0s;
        }

        .onoffswitch-inner:before, .onoffswitch-inner:after {
            display: block;
            float: left;
            width: 50%;
            height: 21px;
            padding: 0;
            line-height: 21px;
            font-size: 9px;
            color: white;
            font-family: Trebuchet, Arial, sans-serif;
            font-weight: bold;
            box-sizing: border-box;
        }

        .onoffswitch-inner:before {
            content: "ACTIVE";
            padding-left: 12px;
            background-color: #00C0EF;
            color: #FFFFFF;
        }

        .onoffswitch-inner:after {
            content: "INACTIVE";
            background-color: #EEEEEE;
            color: #999999;
            text-align: right;
        }

        .onoffswitch-switch {
            display: block;
            width: 23px;
            height: 23px;
            margin: 1px;
            background: #FFFFFF;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 45px;
            border: 2px solid #999999;
            border-radius: 16px;
            transition: all 0.3s ease-in 0s;
        }

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
            margin-left: 0;
        }

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right: 0px;
        }
    </style>
@endsection