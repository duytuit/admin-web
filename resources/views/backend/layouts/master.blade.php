<!DOCTYPE html>
<html lang="en">
@include('backend.layouts.head')
<body class="hold-transition skin-blue fixed sidebar-mini sidebar-mini-expand-feature">
    <input type="hidden" id="_building_active_id" value="{{@$building_active}}">
    <input type="hidden" id="_getCateByBuilding" value="{{@$getCateByBuilding}}">
    <div class="wrapper">
        @include('backend.layouts.topbar')
        @include('backend.layouts.sidebar')
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>
    <div id="fade_overlay"><img id="fade_loading" src="{{ asset('images/loadding.gif') }}"/></div>

    @include('backend.layouts.javascript')
    <!-- Custom -->
   
    @yield('javascript')
    <script type="text/javascript" src="{{ url('adminLTE/js/custom.js') }}"></script>
    <script>
        function getToken(){
            window.localStorage.setItem("user_token","Bearer "+"{{App\Commons\Helper::getToken(Auth::user()->id)}}");
            window.localStorage.setItem("user_id","{{Auth::user()->id}}"); 
            window.localStorage.setItem("base_url","{{env('DOMAIN_API','https://apibdc.dxmb.vn/')}}");
            let building_cache = $('#_building_active_id').val(); 
            console.log(building_cache);
            console.log('_____________________');
            let getCateByBuilding = $('#_getCateByBuilding').val();
            if (getCateByBuilding){
                let object_getCateByBuilding = JSON.parse(getCateByBuilding);
                window.localStorage.setItem("apartments",object_getCateByBuilding.apartment);
                window.localStorage.setItem("user_infos",object_getCateByBuilding.user_info);
                window.localStorage.setItem("services",object_getCateByBuilding.service);
                window.localStorage.setItem("departments",object_getCateByBuilding.department);
                window.localStorage.setItem("buildingPlaces",object_getCateByBuilding.buildingPlace);
                window.localStorage.setItem("service_apartments",object_getCateByBuilding.service_apartment);
            }
            let building_curent = $('#get_building_active_id').val(); // tòa nhà hiện tại
            if(building_curent != building_cache){
                $.ajax({
                url: '/admin/building/setBuildingId',
                type: 'POST',
                data: {
                    building_id: building_curent
                },
                success: function (response) {
                    if (response.success == true) {

                        toastr.success(response.message);
                    }
                }
            })
            }
        }
        getToken();
   
        $(document).on('change', 'select[name="change_building"]', function (e) {
            showLoading();
            e.preventDefault();
            $.ajax({
                url: '{{ route('admin.building.changeBuilding') }}',
                type: 'POST',
                data: {
                    building_id: $(this).val()
                },
                success: function (response) {
                    if (response.success == true) {

                        toastr.success(response.message);
                        if (!response.href) {
                            setTimeout(() => {
                                location.reload()
                            }, 2000)
                        } else {
                            setTimeout(() => {
                                window.location.href = response.href
                            }, 2000)
                        }
                    }
                }
            })
        })
        $(document).ready(function () {
            $('.treeview').on('click', function () {
                $('.slimScrollDiv').find('.slimScrollBar').css('display','block');
            });
        });
    </script>

    <!--Thông báo-->
    @include('backend.layouts.notification')
</body>

</html>