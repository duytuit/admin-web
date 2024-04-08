<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $meta_title }}</title>

    <link rel="shortcut icon" href="{{ url('adminLTE/img/logo-bdc.png') }}">

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ url('adminLTE/plugins/font-awesome/css/font-awesome.min.css') }}" />

    <!-- Plugins -->
    <link rel="stylesheet" href="{{ url('adminLTE/plugins/jquery-ui/jquery-ui.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/plugins/bootstrap/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/plugins/select2/css/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/plugins/iCheck/square/green.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/plugins/iCheck/square/blue.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/plugins/iCheck/square/red.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/plugins/jquery-toastr/toastr.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/plugins/enjoyhint/enjoyhint.css') }}" />

    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ url('adminLTE/css/AdminLTE.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/skins/skin-blue.min.css') }}" />

    <!-- Custom -->
    <link rel="stylesheet" href="{{ url('adminLTE/css/style.css'). "?v=" . \Carbon\Carbon::now()->timestamp  }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/custom.css'). "?v=" . \Carbon\Carbon::now()->timestamp  }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/pagination.css') }}" />
    <link rel="stylesheet" href="{{ url('manifest.json') }}" />

    @yield('stylesheet')

<!-- The core Firebase JS SDK is always required and must be listed first -->
    <script src="https://www.gstatic.com/firebasejs/7.2.3/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.2.3/firebase-messaging.js"></script>
    <!-- TODO: Add SDKs for Firebase products that you want to use
         https://firebase.google.com/docs/web/setup#available-libraries -->
    <script src="https://www.gstatic.com/firebasejs/7.2.3/firebase-analytics.js"></script>

    <script>
        uuid=function(){
            var u = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,
                function(c) {
                    var r = Math.random() * 16 | 0,
                        v = c == 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            return u;
        }
        getDeviceId = function(){
            var current = window.localStorage.getItem("_DEVICEID_")
            if (current) return current;
            var id = uuid();
            window.localStorage.setItem("_DEVICEID_",id);
            console.log(id);
            return id;
        }

        var firebaseConfig = {
            apiKey: "AIzaSyDJ1zzooF1LGyjcZfe7gfLp29jekROG8co",
            authDomain: "building-care-admin.firebaseapp.com",
            databaseURL: "https://building-care-admin.firebaseio.com",
            projectId: "building-care-admin",
            storageBucket: "building-care-admin.appspot.com",
            messagingSenderId: "688178922116",
            appId: "1:688178922116:web:fd8d93a9d23c75110ab8d1",
            measurementId: "G-RXSZFFPRZ9"
        };
        firebase.initializeApp(firebaseConfig);
        firebase.analytics();

        const messaging = firebase.messaging();
        messaging.requestPermission().then(function() {
            console.log('Notification permission granted.');
        }).catch(function(err) {
            console.log('Unable to get permission to notify.', err);
        });
        messaging.getToken().then(function(currentToken) {
            if (currentToken) {
                // console.log(currentToken);
                tokenDevice(currentToken,getDeviceId());
                updateUIForPushEnabled(currentToken);
            } else {
                // console.log('No Instance ID token available. Request permission to generate one.');
                updateUIForPushPermissionRequired();
                setTokenSentToServer(false);
            }
        }).catch(function(err) {
            setTokenSentToServer(false);
        });

        function setTokenSentToServer(sent) {
            window.localStorage.setItem('sentToServer', sent ? '1' : '0');
        }
    </script>
</head>