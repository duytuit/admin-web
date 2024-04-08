<div id="dang_ky_dich_vu"
    class="tab-pane @if(request()->exists('service_partners_keyword') || (!request()->exists('service_partners_keyword') &&
                            !request()->exists('business_partners_keyword')))  active @else fade @endif ">
    <div class="box-header with-border">
    <div class="form-group">
            <div class="col-sm-10">
                    <form action="" method="get" id="form-search">
                        <div class="clearfix"></div>
                        <div id="search-advance" class="search-advance">
                            <div class="row form-group space-5">
                                <div class="col-sm-2">
                                    <input type="text" name="service_partners_keyword" class="form-control"
                                        placeholder="Nhập nội dung tìm kiếm" value="{{ @$filter_service_partners['service_partners_keyword'] }}">              
                                </div>
                                <div class="col-sm-2">
                                <select name="bdc_handbook_id" id="ip-handbooks" class="form-control" style="width: 100%">
                                        <option value="">Chọn bài viết</option>
                                </select>                      
                                </div>
                                <div class="col-sm-2">
                                <select name="bdc_business_partners_id" id="ip-partners" class="form-control" style="width: 100%">
                                        <option value="">Chọn đối tác</option>
                                </select>                      
                                </div>
                                <div class="col-sm-2">
                                    <select name="status" class="form-control">
                                        <option value="" selected>Trạng thái</option>
                                        <option value="0" @if(@$filter_service_partners['status']==0) selected @endif>Chưa duyệt</option>
                                        <option value="1" @if(@$filter_service_partners['status']==1) selected @endif>Đã duyệt</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                                </div>
                                  <div class="col-sm-2">
                                    <a href="{{route('admin.business-partners.export',Request::all())}}" class="btn btn-success">Xuất ra excel</a>
                                </div>
                            </div>
                        </div>
                    </form>
            </div>
    </div>
    </div>
    <!-- /.box-header -->
    <form action="{{ route('admin.service-partners.action') }}" method="post" id="form-service-partners">
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
                            <th width='15%'>Tên khách hàng</th>
                            <th width='10%'>SĐT</th>
                            <th width='10%'>Email</th>
                            <th width='20%'>Thời gian đặt (Từ - Đến)</th>
                            <th width='10%'>Thời gian tạo</th>
                            <th width='20%'>Ghi chú</th>
                             <th width='5%'>Trạng thái</th>
                            <th width='10%'>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                         @if(!$service_partners->isEmpty())
                        @foreach($service_partners as $key => $items)
                        <tr>
                            <!-- <td><input type="checkbox" class="iCheck checkSingle" value="{{$items->id}}" name="ids[]" />
                            </td> -->
                            <td>{{ @($key + 1) + ($service_partners->currentPage() - 1) * $service_partners->perPage() }}</td>
                            <td>
                                {{ $items->customer }} </a>
                            </td>
                            <td>
                                {{ $items->phone}}
                            </td>
                             <td>
                               {{ $items->email}} 
                            </td>
                            <td>
                                {{ $items->timeorder}} 
                            </td>
                            <td>
                               {{ $items->updated_at}} 
                            </td>
                             <td>
                               {{ $items->description}} 
                            </td>
                            <td>
                                <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ route('admin.service-partners.change-status') }}" data-id="{{ $items->id }}" data-status="{{ $items->status }}" class="btn-status label label-sm label-{{ $items->status ? 'success' : 'danger' }}">
                                     {{ $items->status ? 'Duyệt' : 'Chưa duyệt' }}
                                </a>
                            </td>
                            <td>
                            <a id="{{$items->id}}" type="button" class="btn btn-sm btn-info edit-service-partners"
                                    title="Sửa"><i class="fa fa-edit"></i></a>

                                    <a title="Xóa" href="javascript:;"
                                    data-url="{{ route('admin.service-partners.delete') }}"
                                    data-id="{{ $items->id }}" class="btn btn-sm btn-delete btn-danger"><i
                                        class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="10" class="text-center">
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
                    <span class="record-total">Tổng: {{ $service_partners->count() }} /
                        {{ $service_partners->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $service_partners->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page_service_partners" class="form-control" data-target="#form-service-partners">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page_service_partners ? 'selected' : '' }}>{{ $num }}
                            </option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
        </div>
        </div>
    </form>
</div>
@include('businesspartners.modals.servicepartner')


