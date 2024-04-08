@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Thêm mới căn hộ
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thêm mới căn hộ</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm căn hộ</div>

                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="alert alert-danger alert_pop_add" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="form-group">
                            <form action="" method="post" id="form-edit-apartment">
                                {{ csrf_field() }}
                                <input type="hidden" name="building_id"  class="form-control" placeholder="Tên căn hộ" value="{{$building_id}}">
                                <div class="form-group">
                                    <label for="ip-name">Tên căn hộ</label>
                                    <input type="text" name="name" id="ip-name" class="form-control" placeholder="Tên căn hộ" value="{{!empty($apatment->name)?$apatment->name:old('name')}}">
                                </div>
                                <div class="form-group">
                                    <label for="ip-place">Tòa nhà</label>
{{--                                    <input type="text" name="building_place_id" id="ip-place" class="form-control" placeholder="Tòa nhà" value="{{!empty($apatment->building_place_id)?$apatment->building_place_id:old('building_place_id')}}">--}}
                                    <select name="building_place_id" id="ip-place" class="form-control" style="width: 100%">
                                        <option value="">Chọn tòa nhà</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="ip-floor">Số tầng</label>
                                    <input type="text" name="floor" id="ip-floor" class="form-control" placeholder="Tầng căn hộ" value="{{!empty($apatment->floor)?$apatment->floor:old('floor')}}">
                                </div>
                                <div class="form-group">
                                    <label for="ip-acreage">Diện tích(m<sup>2</sup>)</label>
                                    <input type="text" name="area" id="ip-acreage" class="form-control" placeholder="Diện tích căn hộ" value="{{!empty($apatment->area)?$apatment->area:''}}">
                                </div>
                                <div class="form-group">
                                    <label for="ip-description">Mô tả</label>
                                    <textarea name="description" id="id-description" cols="30" rows="5" placeholder="Mô tả căn hộ" class="form-control">{{!empty($apatment->description)?$apatment->description:old('description')}}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="select-status">Tình trạng</label>

                                    <select name="status" id="select-status" class="form-control">
                                        <?php $status = !empty($apatment->status)?$apatment->status:old('status'); ?>
                                        <!-- 0:Để không, 1:cho thuê, 2: muốn cho thuê, 3:đang ở -->
                                        <option value="0" @if($status == 0) selected @endif>Để không</option>
                                        <option value="1" @if($status == 1) selected @endif>Đang cho thuê</option>
                                        <option value="2" @if($status == 2) selected @endif>Muốn cho thuê</option>
                                        <option value="3" @if($status == 3) selected @endif>Đang ở</option>
                                        <option value="4" @if($status == 4) selected @endif>Mới bàn giao</option>
                                        <option value="5" @if($status == 5) selected @endif>Đang cải tạo</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="ip-acreage">Mã căn</label>
                                    <input type="text" name="code" id="ip-code" class="form-control" placeholder="Mã căn hộ" value="---">
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-sm btn-success btn-js-action-add" title="Cập nhật">
                                        <i class="fa fa-save"></i>&nbsp;Thêm mới
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('javascript')

    <script>
        get_data_select_apartment1({
            object: '#ip-place',
            url: '{{ url('admin/apartments/ajax_get_building_place') }}',
            data_id: 'id',
            data_text: 'name',
            data_code: 'code',
            title_default: 'Chọn tòa nhà'
        });
        function get_data_select_apartment1(options) {
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
                                id: item[options.data_code],
                                text: item[options.data_text]+' - '+item[options.data_code]
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

        $(".btn-js-action-add").on('click',function () {
            var _this = $(this);
            $(".alert_pop_add").hide();
            var name = $("#ip-name").val();
            var floor = $("#ip-floor").val();
            var place = $("#ip-place").val();
            var area = $("#ip-acreage").val();
            var status = $("#select-status").val();
            var html = '';
            if(name.length <1 || name.length >46){
                html+='<li>Tên căn hộ không được nhỏ hơn 2 hoặc lớn hơn 45 chữ số</li>';
            }if( place.length <=0){
                html+='<li>Trường tòa nhà không được để trống</li>';
            }if(floor.length == '' || floor.length >5){
                html+='<li>Trường tầng là 1 số không quá 5 chữ số và không bỏ trống</li>';
            }if(area.length == '' || area.length >8 || $.isNumeric(area) == false){
                html+='<li>Trường diện tích là 1 số không quá 8 chữ số và không bỏ trống</li>';
            }if( status.length <=0){
                html+='<li>Trường tình trạng không được để trống</li>';
            }
            if(html){
                $(".alert_pop_add").show();
                $(".alert_pop_add ul").html(html);
            }else{
                $("#form-edit-apartment").submit();
            }
        });
        $("#ip-name").on('keyup',function () {
            var place = $("#ip-place").val();
            var floor = $("#ip-floor").val();
            if(place == '' && floor == ''){
                $("#ip-code").val($(this).val());
            }else if(place != '' && floor == ''){
                $("#ip-code").val(place +'-'+ $(this).val());
            }else if(place == '' && floor != ''){
                $("#ip-code").val(floor +'-'+ $(this).val());
            }else{
                $("#ip-code").val(place +'-'+floor +'-'+ $(this).val());
            }
        });
        $("#ip-place").on('change',function () {
            var name = $("#ip-name").val();
            var floor = $("#ip-floor").val();
            if(name == '' && floor == ''){
                $("#ip-code").val($(this).val());
            }else if(name != '' && floor == ''){
                $("#ip-code").val( $(this).val()+'-'+name);
            }else if(name == '' && floor != ''){
                $("#ip-code").val($(this).val()+'-'+floor);
            }else{
                $("#ip-code").val($(this).val() +'-'+floor +'-'+ name);
            }
        });
        $("#ip-floor").on('keyup',function () {
            var name = $("#ip-name").val();
            var place = $("#ip-place").val();
            if(name == '' && place == ''){
                $("#ip-code").val($(this).val());
            }else if(name != '' && place == ''){
                $("#ip-code").val( $(this).val()+'-'+name);
            }else if(name == '' && place != ''){
                $("#ip-code").val(place+'-'+$(this).val());
            }else{
                $("#ip-code").val(place +'-'+$(this).val() +'-'+ name);
            }
        });

        sidebar('apartments', 'create');
    </script>

@endsection
