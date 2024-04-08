<div id="bai_viet"
    class="tab-pane @if(request()->exists('handbook_keyword') || (!request()->exists('handbook_keyword') && !request()->exists('handbook_categories_keyword') && !request()->exists('keyword'))) active @else fade @endif ">
    <div class="box-header with-border">
        <div class="row form-group">
            <div class="col-sm-8 col-xs-12 ">
                <a href="{{ route('admin.building-handbook.create') }}" type="buttom" class="btn btn-info"><i
                        class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới bài viết</a>
            </div>
        </div>
        <form action="" method="get" id="form-search">
            <div class="clearfix"></div>
            <div id="search-advance" class="search-advance">
                <div class="row form-group space-5">
                    <div class="col-sm-2">
                        <input type="text" name="handbook_keyword" class="form-control"
                            placeholder="Nhập nội dung tìm kiếm" value="{{ @$filter_handbook['handbook_keyword'] }}">
                    </div>
                    <div class="col-sm-2">
                        <select name="bdc_handbook_category_id" class="form-control" id="bdc_handbook_category_id">
                            <option value="" selected>Danh Mục</option>
                           {{--
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                @if(@$filter_handbook['bdc_handbook_category_id']==$category->id)
                                selected
                                @endif>{{ $category->name }}</option>
                            @endforeach
                            --}}
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select name="bdc_handbook_type_id" class="form-control"  id="bdc_handbook_type_id">
                            <option value="" selected>Phân loại</option>
                            @foreach($types as $type)
                            <option value="{{ $type->id }}" @if(@$filter_handbook['bdc_handbook_type_id']==$type->id)
                                selected
                                @endif>{{ $type->name }}</option>
                            @endforeach 
                            
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select name="pub_profile_id" class="form-control">
                            <option value="" selected>Người tạo</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" @if(@$filter_handbook['pub_profile_id']==$user->id) selected
                                @endif>{{ $user->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select name="status" class="form-control">
                            <option value="" selected>Trạng thái</option>
                            <option value="0" @if(@$filter_handbook['status']===0) selected @endif>Lưu nháp</option>
                            <option value="1" @if(@$filter_handbook['status']===1) selected @endif>Đã đăng</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>
        </form>
        <div class="row form-group">
            <div class="col-sm-8">
                <span class="btn-group">
                    <a data-action="{{ route('admin.building-handbook.del_multi') }}" class="btn btn-danger"
                        id="delete-multi-handbooks"><i class="fa fa-trash-o"></i> Xóa mục đã chọn</a>
                </span>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <form action="{{ route('admin.building-handbook.action') }}" method="post" id="form-handbook">
        {{ csrf_field() }}
        @method('post')
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="bg-primary">
                            <th width='20px'>
                                <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                            </th>
                            <th width='20px'>STT</th>
                            <th width='40%'>Tiêu đề</th>
                            <th width='15%'>Danh mục</th>
                            <th width='5%'>Phân loại</th>
                            <th width='10%'>Đối tác</th>
                            <th width='5%'>Trạng thái</th>
                            <th width='10%'>Người tạo</th>
                            <th width='10%'>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$handbooks->isEmpty())
                        @foreach($handbooks as $key => $bdh)
                        <tr>
                            <td><input type="checkbox" class="iCheck checkSingle" value="{{$bdh->id}}" name="ids[]" />
                            </td>
                            <td>{{ @($key + 1) + ($handbooks->currentPage() - 1) * $handbooks->perPage() }}</td>
                            <td>
                                <a href="{{ route('admin.building-handbook.edit', ['id' => $bdh->id]) }}">
                                    {{ @$bdh->title }} </a>
                            </td>
                            <td>
                                {{@$bdh->handbook_category->name}}
                            </td>
                             <td>
                               {{@$bdh->handbook_category->handbook_type->name}} 
                            </td>
                             <td>
                                {{@$bdh->businesspartners->name}} 
                            </td>
                            <td>
                                @if( $bdh->status == 1 )
                                Đã đăng
                                @else
                                Lưu nháp
                                @endif
                            </td>
                            <td>
                                {{@$bdh->pub_profile->display_name}}
                            </td>
                            <td>
                                <a href="{{ route('admin.building-handbook.edit', ['id' => $bdh->id]) }}" type="button"
                                    class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>

                                <a title="Xóa" href="javascript:;"
                                    data-url="{{ route('admin.building-handbook.delete') }}" data-id="{{ $bdh->id }}"
                                    class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>

                                @if( $bdh->status != 1 )
                                <a data-id="{{ $bdh->id }}" type="button" class="btn btn-sm btn-warning push-handbook"
                                    title="Đăng bài"><i class="fa fa-check-square-o"></i></a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="7" class="text-center">
                                <p>Chưa có danh sách cẩm nang nào</p>
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
                    <span class="record-total">Tổng: {{ $handbooks->count() }} / {{ $handbooks->total() }} bản
                        ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $handbooks->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page_handbook" class="form-control" data-target="#form-permission">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page_handbook ? 'selected' : '' }}>{{ $num }}
                            </option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
        </div>
    </form>
</div>