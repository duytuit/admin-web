@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h3>Chi tiết đánh giá APP</h3>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Chi tiết đánh giá APP</li>
    </ol>
</section>
<section class="content">
    <form id="form_rated_service" action="{{ route('admin.rated_service.action') }}" method="post">
        @csrf
        <input type="hidden" name="method" value="" />
        <div class="row">
            <div class="col-xl-6 col-lg-6 col-md-12 block1" style="height: 500px;">
                <div class="row">
                    <div class="col-sm-12">
                        <div style="margin-top: 10px">
                            <span style="font-size: 18px;">Tổng hợp đánh giá</span>
                            <span class="pull-right" id="total_avg"></span>
                        </div>
                        <div class="col-sm-12 chart_1" style="overflow: auto">
                          
                                <table>
                                    <tbody id="getStatisticalEValuateAVG">
                                    </tbody>
                                </table>
                              
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-12 block1" style="height: 500px;">
                <div class="row">
                    <div class="col-sm-12">
                        <div style="margin-top: 10px">
                            <span style="font-size: 18px;">Tổng số ý kiến</span>
                            <span class="pull-right" id="total_comment"></span>
                        </div>
                        <div class="col-sm-12 chart_1" style="overflow: auto">
                            <table class="table table-hover table-striped table-bordered">
                                <tbody id='getListFeedbackNoteEvaluate'>
                                    {{-- @if(@$getListFeedbackNoteEvaluate->count() > 0)
                                    @foreach($getListFeedbackNoteEvaluate as $key => $value)
                                        <tr>
                                            <td>
                                                  <i class="fa fa-commenting-o"></i>
                                                  <span>{{$value->feedback_note}}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="row mbm">
                            <div class="col-sm-3">
                                 {{-- <span class="record-total">Hiển thị {{ $getListFeedbackNoteEvaluate->count() }} / {{ $getListFeedbackNoteEvaluate->total() }} kết quả</span> --}}
                             </div>
                             <div class="col-sm-6 text-center">
                                <div id="pagination-demo"></div>
                             </div>
                             <div class="col-sm-3 text-right">
                                
                             </div>
                     </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 block1">
                <div class="row">
                    <div class="col-lg-12">
                        <h4>Thống kê đánh giá</h4>
                        <div class="col-sm-12">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                    <tr>
                                        <th>Thời gian</th>
                                        <th>Tòa</th>
                                        <th>SĐT</th>
                                        <th>TC1</th>
                                        <th>TC2</th>
                                        <th>TC3</th>
                                        <th>TC4</th>
                                        <th>TC5</th>
                                        <th>TC6</th>
                                        <th>Ý kiến</th>
                                    </tr>
                                </thead>
                                <tbody id="getListEvaluate">
                                   {{-- @if(@$getListEvaluate->count() > 0)
                                        @foreach($getListEvaluate as $key => $value)
                                            <tr>
                                                <td>{{ date('H:i:s d-m-Y',strtotime($value->created_at)) }}</td>
                                                <td>{{ @$value->phone }}</td>
                                                <td>{{ @$value->type_functions[0]->number_start }}</td>
                                                <td>{{ @$value->type_functions[1]->number_start }}</td>
                                                <td>{{ @$value->type_functions[2]->number_start }}</td>
                                                <td>{{ @$value->type_functions[3]->number_start }}</td>
                                                <td>{{ @$value->type_functions[4]->number_start }}</td>
                                                <td>{{ @$value->type_functions[5]->number_start }}</td>
                                                <td>{{ @$value->feedback_note }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="11" class="text-center">Không có kết quả tìm kiếm</td></tr>
                                    @endif --}}
                                </tbody>
                            </table>
                            <div class="row mbm">
                                <div class="col-sm-3">
                                     {{-- <span class="record-total">Hiển thị {{ $getListEvaluate->count() }} / {{ $getListEvaluate->total() }} kết quả</span> --}}
                                 </div>
                                 <div class="col-sm-6 text-center">
                                    <div id="pagination-panel">
                                         {{-- {{ $getListEvaluate->appends(request()->input())->links() }} --}}
                                     </div>
                                 </div>
                                 <div class="col-sm-3 text-right">
                                     <span class="form-inline">
                                         Hiển thị
                                         <select name="per_page" class="form-control" data-target="#form_rated_service">
                                             @php $list = [10, 20, 50, 100, 200]; @endphp
                                             @foreach ($list as $num)
                                                 <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                             @endforeach
                                         </select>
                                     </span>
                                 </div>
                         </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection
<style>
    .stars-outer {
    display: inline-block;
    position: relative;
    font-family: FontAwesome;
    }

    .stars-outer::before {
    content: "\f006 \f006 \f006 \f006 \f006";
    }

    .stars-inner {
    position: absolute;
    top: 0;
    left: 0;
    white-space: nowrap;
    overflow: hidden;
    width: 0;
    }

    .stars-inner::before {
    content: "\f005 \f005 \f005 \f005 \f005";
    color: #f8ce0b;
    }

    .attribution {
    font-size: 12px;
    color: #444;
    text-decoration: none;
    text-align: center;
    position: fixed;
    right: 10px;
    bottom: 10px;
    z-index: -1;
    }
    .attribution:hover {
    color: #1fa67a;
    }
    .star {
	 display: inline-block;
	 margin: 5px;
	 font-size: 20px;
	 color: whitesmoke;
	 position: relative;
}
 .full:before {
	 font-family: fontAwesome;
	 display: inline-block;
	 content: "\f005";
	 position: relative;
	 float: right;
	 z-index: 2;
}
 .half:before {
	 font-family: fontAwesome;
	 content: "\f089";
	 position: absolute;
	 float: left;
	 z-index: 3;
}
 .star-colour {
	 color: #ffd700;
}
</style>
@section('javascript')
<script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
<script>
     
        async function get_StatisticalEValuateAVG() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getStatisticalEValuateAVG = await call_api(method, 'admin/evaluate/getStatisticalEValuateAVG' + param_query);
            if (getStatisticalEValuateAVG.data.length > 0) {
                let html ='';
                let total_avg = 0;

                getStatisticalEValuateAVG.data.forEach(element => {
                   let avg =  element.avg ? element.avg.slice(0, 3): "";
                   total_avg+=element.avg ? parseFloat(element.avg.slice(0, 3)): 0;

                   let name =element.name ? element.name.charAt(0).toUpperCase() + element.name.slice(1) : '';
                     html+='<tr >'+
                               '<td width="68%">'+
                               '<a href="#">'+name+'</a>'+
                               '</td>'+
                               '<td >'+
                               '<div class="rating">'+
                               '    <div class="star">'+
                                '        <span class="half" id="star_0_5" data-value="0.5"></span>'+
                               '         <span class="full" id="star_1" data-value="1"></span>'+
                               '    </div>'+
                               '    <div class="star">'+
                                '        <span class="half" id="star_1_5" data-value="1.5"></span>'+
                               '         <span class="full " id="star_2" data-value="2"></span>'+
                               '    </div>'+
                               '    <div class="star">'+
                                '        <span class="half" id="star_2_5" data-value="2.5"></span>'+
                               '         <span class="full" id="star_3" data-value="3"></span>'+
                               '    </div>'+
                               '    <div class="star">'+
                                '        <span class="half" id="star_3_5" data-value="3.5"></span>'+
                               '         <span class="full" id="star_4" data-value="4"></span>'+
                               '    </div>'+
                               '    <div class="star">'+
                                '        <span class="half" id="star_4_5" data-value="4.5"></span>'+
                               '         <span class="full" id="star_5" data-value="5"></span>'+
                               '    </div>'+
                               '</div>'+
                               '</td>'+
                               '<td>'+ avg +' / 5</td>'+
                            '</tr>'
                });
                $('#total_avg').text("("+parseInt(total_avg) + ' đánh giá)');
                $('#getStatisticalEValuateAVG').append(html);
            }
        }
        // async function get_ListFeedbackNoteEvaluate() {
        //     let param_query_old = "{{ $array_search }}";
        //     let param_query = param_query_old.replaceAll("&amp;", "&")
        //     let per_page = {{$per_page}};
        //     let rs = await call_api_list_padding('#pagination-demo','#getListFeedbackNoteEvaluate','admin/evaluate/getListFeedbackNoteEvaluate',param_query,per_page);
        //     if(rs){
        //         let html ='';
        //         rs.forEach(element => {
        //             console.log(element);
        //              html+= '<tr>'+
        //                     '    <td>'+
        //                     '            <i class="fa fa-commenting-o"></i>'+
        //                     '            <span>'+element.feedback_note+'</span>'+
        //                     '    </td>'+
        //                     '</tr>';
        //         });
        //         $('#getListFeedbackNoteEvaluate').html(html);  
        //     }
        // }
        // $('#pagination-demo').click(function (e) { 
        //     get_ListFeedbackNoteEvaluate();
        // });
        $(document).ready(function() {
            get_StatisticalEValuateAVG();
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            let per_page = {{$per_page}};

            $('#pagination-demo').pagination({
                dataSource:  window.localStorage.getItem("base_url")+'admin/evaluate/getListFeedbackNoteEvaluate' + param_query,
                locator: 'data.list',
                totalNumberLocator: function(response) {
                    $('#total_comment').text("("+response.data.count+ ' ý kiến)')
                    return response.data.count
                },
                alias: {
                    pageNumber: 'page',
                    pageSize: 'limit'
                },
                pageSize: per_page,
                ajax: {
                    beforeSend: function() {
                        // console.log();
                        $('#getListFeedbackNoteEvaluate').html('Loading data ...');
                    }
                },
                callback: function(data, pagination) {
                    if(data){
                        let html ='';
                        data.forEach(element => {
                            html+= '<tr>'+
                                    '    <td>'+
                                    '            <i class="fa fa-commenting-o"></i>'+
                                    '            <span>'+element.feedback_note+'</span>'+
                                    '    </td>'+
                                    '</tr>';
                        });
                        $('#getListFeedbackNoteEvaluate').html(html);  
                    }
                }
            })
            $('#pagination-panel').pagination({
                dataSource:  window.localStorage.getItem("base_url")+'admin/evaluate/getListEvaluate' + param_query,
                locator: 'data.list',
                totalNumberLocator: function(response) {
                    return response.data.count
                },
                alias: {
                    pageNumber: 'page',
                    pageSize: 'limit'
                },
                pageSize: per_page,
                ajax: {
                    beforeSend: function() {
                        // console.log();
                        $('#getListEvaluate').html('Loading data ...');
                    }
                },
                callback: function(data, pagination) {
                    if(data){
                        let html ='';
                        data.forEach(element => {
                            html+= '<tr>'+
                                   '    <td>'+format_date(element.created_at)+'</td>'+
                                   '    <td></td>'+
                                   '    <td>'+ element.phone+'</td>'+
                                   '    <td>'+ element.type_functions[0].number_start+'</td>'+
                                   '    <td>'+ element.type_functions[1].number_start+'</td>'+
                                   '    <td>'+ element.type_functions[2].number_start+'</td>'+
                                   '    <td>'+ element.type_functions[3].number_start+'</td>'+
                                   '    <td>'+ element.type_functions[4].number_start+'</td>'+
                                   '    <td>'+ element.type_functions[5].number_start+'</td>'+
                                   '    <td>'+ element.feedback_note+'</td>'+
                                   '</tr>';
                        });
                        $('#getListEvaluate').html(html);  
                    }
                }
            })

        });
    </script>
@endsection