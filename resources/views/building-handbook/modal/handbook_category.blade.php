<div class="modal fade" id="createHandbookCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm mới danh mục</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <ul id="errors"></ul>
                </div>
                <form class="form-horizontal" action="" id="modal-handbook-category">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="name" class="col-sm-3 control-label">Tiêu đề</label>

                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control" id="name" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="bdc_handbook_type_id" class="col-sm-3 control-label">Phân loại</label>

                            <div class="col-sm-9">
                                <select name="bdc_handbook_type_id" class="form-control" id="bdc_handbook_type_id_123"
                                    style="width: 100%;">
                                    <option value="">Phân loại</option>
                                    @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="parent_id" class="col-sm-3 control-label">Danh mục cha</label>

                            <div class="col-sm-9">
                                <select name="parent_id" class="form-control" id="parent_id" style="width: 100%;">
                                    <option value="0" selected>Không có</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="col-sm-3 control-label">Điện thoại</label>

                            <div class="col-sm-9">
                                <input type="text" name="phone" class="form-control" id="phone" value="">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 25px;">
                              <div class="col-sm-3 control-label">
                              </div>
                              <div class="col-sm-9">
                                        <div>
                                            <label  class="btn col-md-12" style="background-color: #76bde6;border-color: #61bff5;font-weight: bold;">Upload Avatar
                                                <i class="fa fa-files-o" style="font-size: large;"></i>
                                                <input id='inputFile' name="inputFile" type="file" accept="image/*" style="display: none;"/>
                                            </label>
                                        </div>
                                        <div class="form-group" >
                                            <div id="fileName"  style="display: inline-flex;margin-top: 6px;"> </div>
                                            <div style="display: inline-flex;float: right;">
                                            <i id="iconRemoveFile" class="fa fa-remove" style="display: none;margin-left: 5px;margin-top: 4px;cursor: pointer;font-size: x-large;"></i>
                                            </div> 
                                       </div>   
                                       <div>
                                            <img src="" id="fileImage" alt="" style="max-width: 100px;" />
                                       </div> 
                                </div>
                        </div>
                        {{-- <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Mô tả</label>
                            <div class="col-sm-9">
                                <textarea class="form-control mceEditor" rows="10"></textarea>
                            </div>
                        </div> --}}
                    </div>
                    <input class="hidden" value="123" id="category_id">
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add">Thêm mới</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                    </div>
                </form>
                  <input type="hidden" id="custId">
            </div>

        </div>
    </div>
</div>
<script type="text/javascript" src="/adminLTE/plugins/jquery/jquery.min.js"></script>
<script>
    function getBase64(file) {
        var preview = document.getElementById('fileImage');
        var reader = new FileReader();
        reader.addEventListener("load", function () {
            // convert image file to base64 string
            preview.src = reader.result;
        }, false);
        reader.readAsDataURL(file);
        reader.onload = function () {
           // console.log(reader.result);
            $("#custId").val(reader.result);
        };
        reader.onerror = function (error) {
            console.log('Error: ', error);
        };
    }
    $('#inputFile').change(function(e){
            let fileName = e.target.value.split(/(\\|\/)/g).pop();
            $('#fileName').text(fileName); 
            if(fileName){
                $('#iconRemoveFile').show().css("color", "red");
                $('#fileName').css("margin-left", "15px"); 
            }
          var  files_base64 = document.getElementById('inputFile').files;
            if (files_base64.length > 0) {
                getBase64(files_base64[0]);
            }
        })
       
    $("#iconRemoveFile").click(function() {
            $('#fileName').text(''); 
            $("#inputFile").val(null);
            $("#custId").val(null);
            $('#fileImage').attr('src', '');
            $(this).hide()
     });
   $(document).ready(function() {
        if( $('#fileName').text().length > 0){
            $('#iconRemoveFile').show().css("color", "red");
            $('#fileName').css("margin-left", "15px"); 
        }
        $('#bdc_handbook_type_id_123').change(function() {
                var bdc_handbook_type_id = $(this).val();
                $.ajax({
                    url: "{{route('admin.building-handbook.ajax_get_category')}}",
                    method: 'GET',
                    data: {
                        // _token: $("[name='_token']").val(),
                        bdc_handbook_type_id: bdc_handbook_type_id,
                    },
                    dataType: 'json',
                    success: function(response) {
                        addParent(response.all_categories,bdc_handbook_type_id);
                    }
                })
            });
            function addParent(parent_categories,bdc_handbook_type_id) {
                $('#parent_id').empty();
                $('#parent_id').append('<option value="0">Không có</option>');
                if ( !jQuery.isEmptyObject(parent_categories) ) {
                    $.each(parent_categories, function(index, val) {
                            if(val.parent_id == 0 && val.bdc_handbook_type_id == bdc_handbook_type_id){
                               $('#parent_id').append('<option value="'+ val.id +'">'+val.name+'</option>')
                                  $.each(parent_categories, function(index, val1) {
                                    if(val.id == val1.parent_id){
                                     $('#parent_id').append('<option value="'+ val1.id +'">-- '+val1.name+'</option>')
                                        $.each(parent_categories, function(index, val2) {
                                            if(val1.id == val2.parent_id){
                                                $('#parent_id').append('<option value="'+ val2.id +'">------ '+val2.name+'</option>')
                                                 $.each(parent_categories, function(index, val3) {
                                                    if(val2.id == val3.parent_id){
                                                        $('#parent_id').append('<option value="'+ val3.id +'">---------- '+val3.name+'</option>')
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            } 
                    });
                }
            }
   })
</script>
