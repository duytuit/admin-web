<!DOCTYPE html>
<html lang="en">

@include('backend.layouts.head')

<body class="hold-transition skin-blue fixed sidebar-mini sidebar-mini-expand-feature">
    <div>
        @yield('content')
    </div>
    @include('backend.layouts.javascript')
    <!-- Custom -->
    <script type="text/javascript" src="{{ url('adminLTE/js/main.js') }}"></script>
    @yield('javascript')
    <script type="text/javascript" src="{{ url('adminLTE/js/custom.js') }}"></script>
</body>

</html>