<div class="container" style="width: 100%;">
    <div class="row">
        <div class="col-xl-7 col-lg-7 col-md-12 block1">
            <div class="row">
                <div class="col-lg-12">
                    <h3>Tổng hợp doanh thu</h3>
                    <div class="col-sm-12 chart_1">
                        <canvas id="_getStatPayment"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-lg-5 col-md-12 block1">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-3">
                        <h3>Phải thu cuối kỳ</h3>
                    </div>
                    <div class="col-sm-9 chart_2">
                        <canvas id="_getBalanceChange"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-lg-5 col-md-12 block1">
            <div class="col-sm-12">
                <div class="col-sm-2">
                    <h3>Dòng tiền</h3>
                </div>
                <div class="col-sm-10">
                    <div class="_container">
                        <div class="item item--1"> 
                            <div class="chart_8">
                                <canvas id="_getCashFlow"></canvas>
                            </div>
                        </div>
                        <div class="item item--4">
                            <div class="col-sm-12" style="margin-top: 15px;">
                                <div id="legend_getCashFlow"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-4 col-md-12 block1" style="height: 404px;">
            <div class="col-sm-12">
                <h3>Ý kiến - kiến nghị</h3>
                <div class="col-sm-12" style="display: flex;
                justify-content: center;">
                    <div class="chart_4">
                        <canvas id="_getStatFeedback"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-lg-8 col-md-12" style="padding: 0"> 
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 display_flex block2">
                <div class="col-sm-12 chart_5">
                    <img src="{{ asset('images/Home-icon.png') }}" width="50" height="50" >
                    <strong class="font_30" id="apartment_count"></strong><span style="margin-top: 32px;"> Căn hộ</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 display_flex block2">
                <div class="col-sm-12 chart_5">
                    <img src="{{ asset('images/user-group-296.png') }}" width="50" height="50" >
                    <strong class="font_30" id="uses_count"></strong><span style="margin-top: 32px;"> Cư dân</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 display_flex block2">
                <div class="col-sm-12 chart_5">
                    <img src="{{ asset('images/login-64.png') }}" width="50" height="50" >
                    <strong class="font_30" id="login_app"></strong><span style="margin-top: 32px;">Tài khoản đã đăng nhập</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 display_flex block2">
                <div class="col-sm-12 chart_5">
                    <img src="{{ asset('images/Advertising_icon-icons.com_54216.png') }}" width="50" height="50" >
                    <strong class="font_30" id="notify_count"></strong><span style="margin-top: 32px;"> Thông báo</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 display_flex block2">
                <div class="col-sm-12 chart_5">
                    <img src="{{ asset('images/shining-star-2776.png') }}" width="50" height="50" >
                    <strong class="font_30" id="category_id"></strong><span style="margin-top: 32px;"> Sự kiện</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 display_flex block2">
                <div class="col-sm-12 chart_5">
                    <img src="{{ asset('images/asset_3-512_78347.png') }}" width="50" height="50" >
                    <strong class="font_30" id="interaction_user_comment"></strong>
                    <p style="margin-top: 33px;">Người / Bình luận</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-7 col-lg-7 col-md-12 block1">
            <div class="row">
                <div class="col-sm-12">
                    <h3>Lượng xe ra - vào</h3>
                    <div class="col-sm-12 chart_1">
                       <canvas id="_getStatVehicleReg"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-lg-5 col-md-12 block1" style="min-height: 477px;">
            <div class="col-sm-12">
                <h3>Phương tiện</h3>
                <div class="_container">
                    <div class="item item--1"> 
                        <div class="chart_13">
                            <canvas id="_getStatVehicle"></canvas>
                        </div>
                    </div>
                    <div class="item item--4">
                        <div class="col-sm-12">
                            <div id="legend_getStatVehicle"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-5 col-lg-5 col-md-12 block3">
                        <div class="box-body">
                            <div class="box-header">
                                <a href="{{ route('admin.posts.index') }}"><h5 class="box-title">Thông báo cư dân</h5></a>
                            </div>
                            <table class="table no-border table-responsive">
                                <thead>
                                <tr>
                                    <th width="1%">STT</th>
                                    <th>Tiêu đề</th>
                                    <th width="15%">Danh mục</th>
                                    <th width="25%">Ngày tạo</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($posts->count() > 0)
                                        @foreach($posts as $key => $post)
                                            <tr>
                                                <td>{{ @($key + 1)}}</td>
                                                <td>
                                                    <a href="{{route('admin.posts.edit',['id'=>$post->id])}}">{{$post->title}}</a>
                                                </td>
                                                <td>{{ @$post->category->title }}</td>
                                                <td>{{date('d-m-Y H:i',strtotime($post->publish_at))}}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <div class="pull-right"><a href=""><i class="fa fa-hand-o-right"></i> Xem tất cả</a></div>
                        </div>
                </div>
                <div class="col-xl-7 col-lg-7 col-md-12 block3">
                    <div class="box-body">
                        <div class="box-header">
                            <a href="{{ route('admin.feedback.index') }}"><h5 class="box-title">Ý kiến cư dân</h5></a>
                        </div>
                        <table class="table no-border table-responsive">
                            <thead>
                            <tr>
                                <th width="1%">STT</th>
                                <th>Tiêu đề</th>
                                <th>Ý kiến</th>
                                <th width="15%">Căn hộ</th>
                                <th width="15%">Ngày tạo</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if($modelFeedBack->count() > 0)
                                    @foreach($modelFeedBack as $key => $value)
                                        <tr>
                                            <td>{{ @($key + 1)}}</td>
                                            <td>
                                                <a href="{{ route('admin.feedback.detail', ['id' => $value->id]) }}">{{ $value->title }}</a>
                                            </td>
                                            <td>{{ @$value->content }}</td>
                                            <td>{{ @$value->bdcApartment->name }}</td>
                                            <td>{{date('d-m-Y H:i',strtotime($value->created_at))}}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="pull-right"><a href="{{ route('admin.feedback.index') }}"><i class="fa fa-hand-o-right"></i> Xem tất cả</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>