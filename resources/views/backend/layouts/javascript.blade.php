<!-- jQuery 3 -->
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-3.5.1/jquery-3.5.1.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-ui/jquery-ui.min.js') }}"></script>

<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button);
</script>

<!-- Plugins -->
<script type="text/javascript" src="{{ url('adminLTE/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/fastclick/fastclick.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/select2/js/select2.full.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/iCheck/icheck.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-toastr/toastr.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-toastr/ui-toastr-notifications.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery.scrollTo-2.1.2/jquery.scrollTo.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/kinetic-v5.1.0/kinetic-v5.1.0.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/enjoyhint/enjoyhint.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/pagination.min.js') }}"></script>

<!-- form validate -->
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-validate/jquery.validate.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-validate/form-validation.js') }}"></script>

<!-- AdminLTE -->
<script type="text/javascript" src="{{ url('adminLTE/js/adminlte.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/bootstrap-select.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/main.js'). "?v=" . \Carbon\Carbon::now()->timestamp  }}"></script>
<!-- TinyMCE -->
<!-- <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script> -->

<!-- CKEDITOR -->

{{-- <script src="//cdn.ckeditor.com/4.6.2/standard/ckeditor.js"></script> --}}
<script type="text/javascript" src="{{ url('ckeditor/ckeditor.js') }}"></script>
<script>
     CKEDITOR.replace('content', {
        language: 'vi',
        height: 600,
	    filebrowserBrowseUrl: "{{ url('ckfinder/ckfinder.html') }}",
        filebrowserImageBrowseUrl: "{{ url('ckfinder/ckfinder.html?type=Images') }}",
        filebrowserFlashBrowseUrl: "{{ url('ckfinder/ckfinder.html?type=Flash') }}",
        filebrowserUploadUrl: "{{route('admin.upload.upload_ckeditor', ['_token' => csrf_token()])}}"+'&folder='+"{{auth()->user() ? auth()->user()->id : null}}",
        filebrowserUploadMethod: 'form'
    } ); 
</script>
