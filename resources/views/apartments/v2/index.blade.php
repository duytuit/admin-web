@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <ol class="breadcrumb">
{{--            @if( in_array('admin.home',@$user_access_router))--}}
                <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
{{--            @endif--}}

            <li class="active">Căn hộ</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="clearfix"></div>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active">
                        <a href="#apartment" role="tab" data-toggle="tab">
                            Danh sách căn hộ
                        </a>
                    </li>
                    <li>
                        <a href="#apartment-group" role="tab" data-toggle="tab">
                            Danh sách nhóm căn hộ
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="apartment" style="padding: 15px 0;">
                        @include('apartments.v2.tabs.apartment')
                    </div>
                    <div class="tab-pane" id="apartment-group" style="padding: 15px 0;">
                        @include('apartments.v2.tabs.apartment-group')
                    </div>
                </div>
            </div>
        </div>

    </section>



@endsection

@section('javascript')

    <script>
        $(function () {
            get_data_select_apartment({
                object: '#select-re_name',
                url: '{{ url('admin/apartments/ajax_get_resident') }}',
                data_id: 'id',
                data_text: 'display_name',
                title_default: 'Chọn chủ hộ'
            });
            get_data_select_apartment1({
                object: '#ip-place',
                url: '{{ url('admin/apartments/ajax_get_building_place') }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn tòa nhà'
            });
            get_data_select_apartment2({
                object: '#apartment-list',
                url: '{{ url('admin/apartments/ajax_get_apartment_in_group') }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn Căn hộ'
            });
            function get_data_select_apartment(options) {
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
                                    id: item[options.data_id],
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

            function get_data_select_apartment2(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                apartment_group_id: options.apartment_group_id
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

            $('.btn-edit-group').on('click',function(){

                $("#apartment-list-edit").html("");
                let row = $(this).closest('.apartment-group-row');

                let id = row.find(".group_id").attr('data-id');
                let name = row.find(".group_name").text();
                let description = row.find(".group_description").text();
                let group_list_apartment = row.find(".group_list_apartment").children();

                group_list_apartment.clone().appendTo("#apartment-list-edit");

                $('#id-apartment-group-edit').attr('data-id',id);
                $('#name-apartment-group-edit').val(name.trim());
                $('#description-apartment-group-edit').val(description.trim());

                get_data_select_apartment2({
                    object: '#apartment-list-edit',
                    url: '{{ url('admin/apartments/v2/ajax_get_apartment_in_group') }}',
                    data_id: 'id',
                    data_text: 'name',
                    data_code: 'code',
                    title_default: 'Chọn Căn hộ',
                    apartment_group_id: id
                });

                $('#edit-apartment-group').modal('show');
            })


        });

        $(".btn-js-action-add-apartment-group").on('click',function (){
            let _this = this;
            $(".alert_pop_add_vehicle").hide();

            let name = $('#name-apartment-group').val();

            let description = $('#description-apartment-group').val();

            let list_apartment = $('#apartment-list').val();

            let html = "";

            if (name.length < 2) {
                html+='<li>Tên nhóm căn hộ không được bỏ trống</li>';
            }
            if (list_apartment.length < 2) {
                html+='<li>Hãy chọn ít nhất hai căn hộ để thêm vào nhóm</li>'
            }

            if(html != ''){
                $(".alert_pop_add_vehicle").show();
                $(".alert_pop_add_vehicle ul").html(html);
                hideLoading();
                return;
            }

            let list_id_ap = [];

            list_apartment.forEach((item)=>{
                list_id_ap.push(item);
            })

            list_id_ap = JSON.stringify(list_id_ap);

            showLoading();

            $.ajax({
                url: '/admin/apartment-group/store',
                type: 'POST',
                data: {
                    'name':  name,
                    'description':  description,
                    'list_apartment':  list_id_ap
                },
                success: function (res) {
                    console.log(res);
                    alert('Thêm nhóm căn hộ thành công');
                    hideLoading();
                    location.reload()
                }
            })

        })

        $('.btn-js-action-edit-apartment-group').on('click',function (){
            let _this = this;
            $(".alert_pop_add_vehicle").hide();

            let id = $('#id-apartment-group-edit').attr('data-id');
            let name = $('#name-apartment-group-edit').val();

            let description = $('#description-apartment-group-edit').val();

            let list_apartment = $('#apartment-list-edit').val();

            let html = "";

            if (name.length < 2) {
                html+='<li>Tên nhóm căn hộ không được bỏ trống</li>';
            }
            if (list_apartment.length < 2) {
                html+='<li>Hãy chọn ít nhất hai căn hộ để thêm vào nhóm</li>'
            }

            if(html != ''){
                $(".alert_pop_add_vehicle").show();
                $(".alert_pop_add_vehicle ul").html(html);
                hideLoading();
                return;
            }

            let list_id_ap = [];

            list_apartment.forEach((item)=>{
                list_id_ap.push(item);
            })

            list_id_ap = JSON.stringify(list_id_ap);

            showLoading();

            $.ajax({
                url: '/admin/apartment-group/update',
                type: 'POST',
                data: {
                    'id':id,
                    'name':  name,
                    'description':  description,
                    'list_apartment':  list_id_ap
                },
                success: function (res) {
                    console.log(res);
                    alert('Thêm nhóm căn hộ thành công');
                    hideLoading();
                    location.reload()
                }
            })
        });


        $('.delete-apartment-group').on('click',function (){
            let id = $(this).attr('data-id');
            let check = confirm('Bạn có chắc chắn muốn xóa không?');
            if(check) {
                showLoading();
                console.log(id);

                let ids = [id];

                ids = JSON.stringify(ids);

                $.ajax({
                    url: '/admin/apartment-group/delete',
                    type: 'POST',
                    data: {
                        ids: ids
                    },
                    success: function (response) {

                        if (response.code === 0) {
                            alert("Xóa nhóm căn hộ thành công");
                            location.reload();
                        } else {
                            alert("Xóa nhóm căn hộ thành công");
                            location.reload();
                        }
                        hideLoading();
                    },
                    error: function (e) {
                        console.log(e);
                        hideLoading();
                    }
                })
            }
        })

        $(".btn-js-action-add-apartment-to-group").on('click',function (){
           // $(".apartment-item").each((item,index))
            let apartmentIds = [];
            $(".apartment-item").each(function (index,value){
                if($(value).is(":checked")) {
                    if ($(value).attr('data-id')) {
                        apartmentIds.push(parseInt($(value).attr('data-id')));
                    }
                }
            });

            let idGroup = $('#id-group-apartment').val();
            apartmentIds = JSON.stringify(apartmentIds);
            showLoading();
            $.ajax({
                url: '/admin/apartment-group/addApartment',
                type: 'POST',
                data: {
                    id: idGroup,
                    apartmentIds: apartmentIds
                },
                success: function (response) {

                    if (response.code === 0) {
                        alert("Thêm căn hộ vào nhóm thành công");
                        location.reload();
                    } else {
                        alert("Thêm căn hộ vào nhóm không thành công");
                        location.reload();
                    }
                    hideLoading();
                },
                error: function (e) {
                    console.log(e);
                    hideLoading();
                }
            })




        });

        sidebar('apartments', 'index');
    </script>


@endsection
