@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Cư dân 
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Cư dân</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-activity" action="{{route('admin.activitylog.index')}}" method="get">
                    <div class="row">
                        <div id="search-advance" class="search-advance">
                                <div class="col-sm-2">
                                      <input type="text" name="keyword_word" value="{{isset($filter['keyword_word']) ? $filter['keyword_word'] : '' }}" placeholder="Tên, sđt, email" class="form-control"/>
                                </div>
                                <div class="input-group-btn" style="display: block;">
                                    <button type="submit" title="Tìm kiếm" class="btn btn-info" form="form-search-activity"><i class="fa fa-search"></i></button>
                                </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <div class="table-responsive">
                        <form action='{{ route('admin.activitylog.index') }}' method="post" id="form-activity-action">
                            {{ csrf_field() }}
                            <input type="hidden" name="method" value="" />
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                <tr>
                                    <th width="30">STT</th>
                                    <th width="200">Tên tài khoản</th>
                                    <th width="200">email_user</th>
                                    <th width="200">phone_user</th>
                                    <th width="200">email_profile</th>
                                    <th width="200">phone_profile</th>
                                    <th>thông tin</th>
                                    <th>căn hộ</th>
                                    <th>quan hệ</th>
                                    <th>bảng dữ liệu</th>
                                    <th width="30">hành động</th>
                                    <th width="90">Người thao tác</th>
                                    <th width="150">Tòa nhà</th>
                                    <th width="30">thời gian</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($activity_log as $key => $value)
                                  <?php
                                        $get_user = null;
                                        $phone_profile = null;
                                        $email_profile = null;
                                        $email_user = null;
                                        $phone_user = null;
                                        $type = null; // 1:app 2:web
                                        $properties = json_decode($value['properties'],true);

                                        if($value->log_type == 'pub_user'){
                                            $email_user = $properties['email'] ?? null;
                                            $phone_user = $properties['mobile'] ?? null;
                                            if(array_key_exists('info',$properties)){
                                                $properties = (object)$properties;
                                                $get_user = @$properties->info->display_name;
                                                $phone_profile = @$properties->info->phone;
                                                $email_profile = @$properties->info->email;
                                                $type = @$properties->info->type;
                                            }
                                        }

                                        if($value->log_type == 'pub_user_profile'){
                                            $user = App\Models\PublicUser\Users::withTrashed()->find($properties['pub_user_id']);
                                            $email_user = $user->email ?? null;
                                            $phone_user = $user->mobile ?? null;
                                            $get_user = $properties['display_name'];
                                            $email_profile = $properties['email'] ?? null;
                                            $phone_profile = $properties['phone'] ?? null;
                                            $type = $properties['type'];
                                        }

                                        if($value->log_type == 'bdc_customer'){
                                           $customer = @$value->customer()->withTrashed()->first();
                                           $profile_id = $customer->pub_user_profile_id;
                                           $user_profile = App\Models\PublicUser\UserInfo::withTrashed()->find($profile_id);
                                           $customer_type = $properties['type'];
                                           $customer_apartment = @$properties['bdc_apartment']['name'];
                                        }
                                       
                                  ?>

                                    <tr>
                                        <td>{{$key + 1}}</td>
                                        <td>{{@$get_user ?? @$user_profile->display_name}}</td>
                                        <td>{{@$email_user}}</td>
                                        <td>{{@$phone_user}}</td>
                                        <td>{{@$email_profile ?? @$user_profile->email}}</td>
                                        <td>{{@$phone_profile ?? @$user_profile->phone}}</td>
                                        <td>{{$type == 1 ? 'app' : ($type == 2 ? 'web' : '')}}</td>
                                        <td>
                                                @if($value->log_type == 'bdc_customer')
                                                {{ @$customer->bdcApartment->name }}
                                                @endif
                                        </td>
                                        <td colspan="" rowspan="" headers="">
                                            @if($value->log_type == 'bdc_customer')
                                                @if($customer_type == 0)
                                                <span class="tag-relats bg-main" style="">Chủ hộ</span>
                                                @elseif($customer_type == 1)
                                                <span class="tag-relats bg-submain" style="">Vợ/Chồng</span>
                                                @elseif($customer_type == 2)
                                                <span class="tag-relats bg-submain1" style="">Con</span>
                                                @elseif($customer_type == 3)
                                                <span class="tag-relats bg-submain1" style="">Bố mẹ</span>
                                                @elseif($customer_type == 4)
                                                <span class="tag-relats bg-submain3" style="">Anh chị em</span>
                                                @elseif($customer_type == 5)
                                                <span class="tag-relats bg-submain3" style="">Khác</span>
                                                @elseif($customer_type == 6)
                                                <span class="tag-relats bg-submain3" style="">Khách thuê</span>
                                                @elseif($customer_type == 7)
                                                    <span class="tag-relats bg-submain3" style="">Chủ hộ cũ</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{$value->log_type}}</td>
                                        <td>{{$value->description}}</td>
                                        <td>{{@$value->user->email}}</td>
                                        <td>{{@$value->building->name}}</td>
                                        <td>{{$value->created_at}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </form>

                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Hiển thị: {{$display_count}} / {{ $activity_log->total() }} kết quả</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $activity_log->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                        </span>
                    </div>
                </div>
            </div>
            <input type="hidden" value="{{isset($user_info) ? $user_info : ''}}" id="user_info_id_search">
        </div>
    </section>
@endsection
@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script>
        $(document).ready(() => {
            if($('#user_info_id_search').val())
            {
                var user_info_id_search_all = $('#user_info_id_search').val();
                var obj_user_info_id_search_all_all = JSON.parse(user_info_id_search_all);
                var new_user_info_all = [];
                            new_user_info_all.push({
                                id:obj_user_info_id_search_all_all["id"],
                                text:obj_user_info_id_search_all_all["display_name"]
                            });
                
                $('#subject_id').select2({data:new_user_info_all});
                $('#subject_id').find('option').attr('selected', true);
                $('#subject_id').select2();
            }
            get_data_select_customer({
            object: '#subject_id',
            url: '{{url('admin/activity-log/ajax_get_customer')}}',
            data_id: 'id',
            data_text: 'display_name',
            title_default: 'Chọn cư dân'
            });

            function get_data_select_customer(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [{
                                id: '',
                                text: options.title_default
                            }];

                            for (i in json.data) {
                                var item = json.data[i];
                                results.push({
                                    id: item[options.data_id],
                                    text: item[options.data_text]
                                });
                            }
                            return {
                                results: results,
                            };
                        },
                        minimumInputLength: 3,
                    }
                });
            }
          
        });
    
    </script>


@endsection
