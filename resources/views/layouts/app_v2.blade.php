<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('backend.layouts.head')
<body>
    <div id="app" class="wrapper">
        <main class="content">
            @yield('content')
        </main>
    </div>
    <div id="fade_overlay"><img id="fade_loading" src="{{ asset('images/loadding.gif') }}"/></div>
    @include('backend.layouts.javascript')
    <script type="text/javascript" src="{{ url('adminLTE/js/main.js') }}"></script>
    @yield('javascript')
</body>
</html>
