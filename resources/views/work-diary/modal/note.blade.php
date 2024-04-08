<div class="modal fade" id="noteWork" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ghi chú</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div>
          <ul id="errors"></ul>
        </div>
        <form class="form-horizontal"
          action="{{route('admin.work-diary.ajax_update_review_note', ['id' => $task->id])}}" id="form-note">
          <div class="box-body">
            <div class="form-group">
              <!-- <textarea name="content" id="description" rows="5" class="mceEditor form-control"></textarea> -->
              <textarea name="content" id="content" rows="5" class="mceEditor form-control"></textarea>
            </div>
              <div>
                  <label  class="btn col-md-12" style="background-color: #76bde6;border-color: #61bff5;font-weight: bold;">Upload file
                      <i class="fa fa-files-o" style="font-size: large;"></i>
                      <input id='inputFile' name="inputFile" type="file" style="display: none;"/>
                  </label>
              </div>
              <div class="form-group" style="display:flex">
                  <div id="fileName"> </div> 
                  <i id="iconRemoveFile" class="fa fa-remove" style="display: none;margin-left: 5px;margin-top: 4px;cursor: pointer;"></i>
              </div>
          </div>
          <input class="hidden" value="123" id="category_id">
          <div class="modal-footer d-flex justify-content-center">
            <button type="button" class="btn btn-primary btn-js-add-note">Lưu</button>
            <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>
<script type="text/javascript" src="/adminLTE/plugins/jquery/jquery.min.js"></script>
<script>
      
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#inputFile').change(function(e){
            let fileName = e.target.value.split(/(\\|\/)/g).pop();
            $('#fileName').text(fileName); 
            if(fileName){
                $('#iconRemoveFile').show().css("color", "red");
                $('#fileName').css("margin-left", "15px"); 
            }
        })
        $("#iconRemoveFile").click(function() {
            $('#fileName').text(''); 
            $("#inputFile").val(null);
            $(this).hide()
        });
</script>