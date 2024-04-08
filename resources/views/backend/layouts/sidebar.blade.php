<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="_sidebar" style="height: 100vh;overflow: scroll;">
        @if(\Auth::check())
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="{{@$users_profile_active->avatar ?: asset('adminLTE/img/user-default.png') }}"
                         class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p>Tên: {{ @$users_profile_active->display_name }}</p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>
            <div class="text-center">
                <label for="" style="color: #dceef3; text-align: center">Dự án quản lý</label>
            </div>
            <form action="#" method="get" style="padding: 0 10px;">
                <select name="change_building" id="get_building_active_id" class="form-control form-switch select2"
                        style="color: #dceef3;background-color: #222d32;">
                    @if(count($building_users) > 0)
                        @foreach($building_users as $key => $building)
                            <option value="{{ $key }}"
                                    @if($key == $building_active) selected @endif>{{ $building }}</option>
                        @endforeach
                    @endif
                </select>
            </form>
        @endif

        <?php @$user_access_router = App\Services\AppUserPermissions::getAccessRouter(\Auth::user()); ?>
                <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu tree" data-widget="tree">
            <li data-nav="home">
                <a href="{{ route('admin.home') }}">
                    <i class="fa fa-tachometer fa-fw">
                        <div class="icon-bg bg-orange"></div>
                    </i>
                    <span class="menu-title">Trang chủ</span>
                </a>
            </li>
            @if(@$group_menu)
                @foreach($group_menu as $key=>$value)
                    <li class="treeview">
                        <a href="#">
                            <i class="fa {{ @$value->icon_web }}"></i>
                            <span style=" text-transform: uppercase;">{{ @$value->name }}</span>
                        </a>
                        <ul class="treeview-menu">
                            @foreach($value->menus as $k=>$v)
                                @if(\Route::has($v->route_name) && in_array($v->route_name, @$user_access_router))
                                    <li @if($route_current == $v->route_name)
                                        {{ 'class= active id=menu_active_'.$k }}
                                            @endif >
                                        <a href="{{ route($v->route_name) }}"><i
                                                    class="fa {{ @$v->icon_web }}"></i>{!! $v->title !!}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            @endif
        </ul>
    </section>

    <!-- /.sidebar -->
</aside>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', (event) => {
        let check_class_name = document.querySelectorAll('.active');
        if (check_class_name.length > 0) {
            $(check_class_name).parents('li.treeview').addClass('menu-open');
        }
    })
</script>
