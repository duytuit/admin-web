@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Danh sách Mã tài khoản kế toán
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Mã tài khoản kế toán</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            
                <form id="form-search" action="{{ route('admin.accounting.account.index') }}" method="get">

                    <div class="row form-group">
                        <div class="col-sm-8">
                            @if( in_array('admin.accounting.account.action',@$user_access_router))
                                <span class="btn-group">
                                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Tác vụ <span class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li><a class="btn-action" data-target="#form-accounting_account" data-method="delete" href="javascript:;"><i class="fa fa-trash"></i> Xóa</a></li>
                                    </ul>
                                </span>
                            @endif
                            @if( in_array('admin.accounting.account.create',@$user_access_router))
                                <a href="{{ route('admin.accounting.account.create') }}" class="btn btn-info"><i class="fa fa-edit"></i> Thêm mới</a>
                                <a href="{{ route('admin.accounting.account.export',Request::all()) }}" class="btn btn-success"><i class="fa fa-edit"></i> Export</a>
                            @endif
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
                <form id="form-accounting_account" action="{{ route('admin.accounting.account.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th width="3%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th width="3%">ID</th>
                                    <th width="8%">Mã TK</th>
                                    <th width="8%">Tên TK</th>
                                    <th width="8%">Người cập nhật</th>
                                    <th width="8%">TK Nợ PT</th>
                                    <th width="8%">TK Có PT</th>
                                    <th width="8%">TK Nợ Báo có</th>
                                    <th width="8%">TK Có Báo có</th>
                                    <th width="8%">TK Nợ Thuế</th>
                                    <th width="8%">TK Có Thuế</th>
                                    <th width="8%">TK Nợ trước VAT</th>
                                    <th width="8%">TK Có trước VAT</th>
                                    <th width="15">Tác vụ</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($accounting_accounts as $item)
                                <tr class="list_accounting">
                                    <td>
                                        @if ($item->default == 0)
                                           <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" />
                                        @endif
                                    </td>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->code }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>
                                        <small>
                                            {{ @$item->user->email }}<br />
                                            {{ $item->updated_at->format('d-m-Y H:i') }}
                                        </small>
                                    </td>
                                    <td class="td-checkbox">
                                        <input type="checkbox" data-url="{{ route('admin.accounting.account.action') }}" {{ $item->tai_khoan_no_pt == 1 ? 'checked' : '' }} data-method="tai_khoan_no_pt" data-id="{{ $item->id }}" data-status="{{ $item->tai_khoan_no_pt }}" class="blueCheck" />
                                    </td>
                                    <td class="td-checkbox">
                                        <input type="checkbox" data-url="{{ route('admin.accounting.account.action') }}" {{ $item->tai_khoan_co_pt == 1 ? 'checked' : '' }} data-method="tai_khoan_co_pt" data-id="{{ $item->id }}" data-status="{{ $item->tai_khoan_co_pt }}" class="blueCheck" />
                                    </td>
                                    <td class="td-checkbox">
                                        <input type="checkbox" data-url="{{ route('admin.accounting.account.action') }}" {{ $item->tai_khoan_no_bao_co == 1 ? 'checked' : '' }} data-method="tai_khoan_no_bao_co" data-id="{{ $item->id }}" data-status="{{ $item->tai_khoan_no_bao_co }}" class="blueCheck" />
                                    </td>
                                    <td class="td-checkbox">
                                        <input type="checkbox" data-url="{{ route('admin.accounting.account.action') }}" {{ $item->tai_khoan_co_bao_co == 1 ? 'checked' : '' }} data-method="tai_khoan_co_bao_co" data-id="{{ $item->id }}" data-status="{{ $item->tai_khoan_co_bao_co }}" class="blueCheck" />
                                    </td>
                                    <td class="td-checkbox">
                                        <input type="checkbox" data-url="{{ route('admin.accounting.account.action') }}" {{ $item->tai_khoan_no_thue == 1 ? 'checked' : '' }} data-method="tai_khoan_no_thue" data-id="{{ $item->id }}" data-status="{{ $item->tai_khoan_no_thue }}" class="blueCheck" />
                                    </td>
                                    <td class="td-checkbox">
                                        <input type="checkbox" data-url="{{ route('admin.accounting.account.action') }}" {{ $item->tai_khoan_co_thue == 1 ? 'checked' : '' }} data-method="tai_khoan_co_thue" data-id="{{ $item->id }}" data-status="{{ $item->tai_khoan_co_thue }}" class="blueCheck" />
                                    </td>
                                    <td class="td-checkbox">
                                        <input type="checkbox" data-url="{{ route('admin.accounting.account.action') }}" {{ $item->tai_khoan_no_truoc_vat == 1 ? 'checked' : '' }} data-method="tai_khoan_no_truoc_vat" data-id="{{ $item->id }}" data-status="{{ $item->tai_khoan_no_truoc_vat }}" class="blueCheck" />
                                    </td>
                                    <td class="td-checkbox">
                                        <input type="checkbox" data-url="{{ route('admin.accounting.account.action') }}" {{ $item->tai_khoan_co_truoc_vat == 1 ? 'checked' : '' }} data-method="tai_khoan_co_truoc_vat" data-id="{{ $item->id }}" data-status="{{ $item->tai_khoan_co_truoc_vat }}" class="blueCheck" />
                                    </td>
                                    <td>
                                        @if ($item->default == 0)
                                            @if( in_array('admin.accounting.account.edit',@$user_access_router))
                                            <a title="Sửa danh mục" href="{{ route('admin.accounting.account.edit', ['id' => $item->id]) }}" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $accounting_accounts->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $accounting_accounts->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <span class="form-inline">
                                Hiển thị
                                <select name="per_page" class="form-control" data-target="#form-accounting_account">
                                    @php $list = [10, 20, 50, 100, 200]; @endphp
                                    @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                    </div>
                </form><!-- END #form-accounting_account -->
        </div>
    </div>
</section>

@endsection