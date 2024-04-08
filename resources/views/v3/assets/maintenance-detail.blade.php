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
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th width="50">TT</th>
                                <th width="100">Tiều đề</th>
                                <th width="100">Ngày bắt đầu thực hiện</th>
                                <th width="100">Danh mục</th>
                                <th width="150">Khu vực</th>
                                <th width="100">Trạng thái</th>
                                <th width="100">Người thực hiện</th>
                            </tr>
                            </thead>
                            <tbody id="list_table">
                            </tbody>
                        </table>
                        <div class="box-footer clearfix"></div>
                        <div class="row">
                            <div class="col-sm-3">
                            </div>
                            <div class="col-sm-6 text-center">
                                <div id="pagination_list_table"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{$status_task_html}}" id="all_status_task_html">
    </section>
@endsection
@section('javascript')
    <script>
        let object_table = null;
        $(document).ready(function() {
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            param_query +="&id={{$id}}";
            $('#pagination_list_table').pagination({
                dataSource:  window.localStorage.getItem("base_url")+'admin/asset/getListTaskByAssetId' + param_query,
                locator: 'data.list',
                totalNumberLocator: function(response) {
                    return response.data.count
                },
                alias: {
                    pageNumber: 'page',
                    pageSize: 'limit'
                },
                pageSize: 10,
                ajax: {
                    beforeSend: function() {
                        $('#list_table').html('Loading data ...');
                    }
                },
                callback: function(data, pagination) {
                    if(data){
                        let html ='';
                        let stt=0;
                        object_table = data;
                        let object_all_status_task = JSON.parse($('#all_status_task_html').val());
                        data.forEach(element => {
                            stt++;
                            let status = '';
                            Object.entries(object_all_status_task).forEach(([key, val]) => {
                                 if(key == element.status){
                                     status = val;
                                 }
                            })

                            html+= '<tr>'+
                                '    <td>'+stt+'</td>'+
                                '    <td>'+element.title+'</td>'+
                                '    <td>'+element.start_date+'</td>'+
                                '    <td>'+element.asset_cate_name+'</td>'+
                                '    <td>'+element.asset_name+'</td>'+
                                '    <td>'+status+'</td>'+
                                '    <td></td>'+
                                '</tr>';
                        });
                        $('#list_table').html(html);
                    }
                }
            })
        })
    </script>
@endsection
