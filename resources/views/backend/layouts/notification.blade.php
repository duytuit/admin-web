<script>
                toastr.options = {
                    "closeButton": false,
                    "debug": false,
                    "positionClass": "toast-top-right",
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
            </script>

            @if (\Session::has('message'))
                @php $message = \Session::get('message'); @endphp
                <script>
                    toastr.{{ $message['status'] }}('{{ $message['msg'] }}');
                </script>
            @endif

            @if (\Session::has('success'))
            <script>
                toastr.success('{{ \Session::get('success') }}');
            </script>
            @endif

            @if (\Session::has('warning'))
            <script>
                toastr.warning('{{ \Session::get('warning') }}');
            </script>
            @endif

            @if (\Session::has('info'))
            <script>
                toastr.info('{{ \Session::get('info') }}');
            </script>
            @endif

            @if (\Session::has('error'))
            <script>
                toastr.error('{{ \Session::get('error') }}');
            </script>
            @endif



            @if ($errors->any())
            <script>
                var messages = '';
                @foreach ($errors->all() as $error)
                    messages = messages +'{{ $error }} ';
                @endforeach
                toastr.error(messages);
            </script>
            @endif