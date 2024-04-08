<div class="modal fade" id="detailtask" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="padding-right:0px;">
  <div class="modal-dialog detailwork" role="document" style="padding: 20px 0;">
    <div class="modal-content" style="border-radius: 5px;">
      <div class="modal-header" style="
            border-top-right-radius: 5px;
            border-top-left-radius: 5px;
            color: white;
            background-color: #3c8dbc;
            padding: 5px;
            border-bottom: 0;
            ">
        <h5 class="modal-title" style="margin-top: 2px;">Chi tiết công việc</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" class="col-sm-12" style="padding:0;">
        <div style="display:none">
          <ul id="errors"></ul>
        </div>
        <form class="form-horizontal" action="" id="modal-partners-category" style="font-family: inherit; display: flex;">
          <div class="col-sm-8">
            <h3 style="font-weight: bold;color:#0570f5" id="info-task-name">Kiểm tra đảm bảo vận hành các thiết bị</h3>
            <div class="work-info"><span class="label label-success info-task-status"></span></div>
            <div class="work-info"><span style="font-weight: bold">Mô tả: </span><span id="info-task-description"></span></div>
            <div style="font-weight: bold;">Checklist</div>
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>BT</th>
                  <th>KBT</th>
                  <th></th>
                  <th></th>
                </tr>
              </thead>
              <tbody class="info-subtask">
                
              </tbody>
            </table>
            <div style="text-align: right;" id="complete_subtak"></div>
            <hr style="height:2px;border-width:0;color:gray;background-color:gray">
            <p style="font-weight: bold;">Kết quả công việc</p>
            {{--<div class="dsfds">
              <div class="cont">
                <div class="demo-gallery">
                  <ul id="lightgallery">
                    <li data-responsive="https://sachinchoolur.github.io/lightGallery/static/img/1-375.jpg 375, https://sachinchoolur.github.io/lightGallery/static/img/1-480.jpg 480, https://sachinchoolur.github.io/lightGallery/static/img/1.jpg 800" data-src="https://sachinchoolur.github.io/lightGallery/static/img/1-1600.jpg" data-sub-html="<h4>Fading Light</h4><p>Classic view from Rigwood Jetty on Coniston Water an old archive shot similar to an old post but a little later on.</p>" data-pinterest-text="Pin it" data-tweet-text="share on twitter ">
                      <a href="">
                        <img class="img-responsive" src="https://sachinchoolur.github.io/lightGallery/static/img/thumb-1.jpg">
                        <div class="demo-gallery-poster">
                          <img src="https://sachinchoolur.github.io/lightGallery/static/img/zoom.png">
                        </div>
                      </a>
                    </li>
                    <li data-responsive="https://sachinchoolur.github.io/lightGallery/static/img/2-375.jpg 375, https://sachinchoolur.github.io/lightGallery/static/img/2-480.jpg 480, https://sachinchoolur.github.io/lightGallery/static/img/2.jpg 800" data-src="https://sachinchoolur.github.io/lightGallery/static/img/2-1600.jpg" data-sub-html="<h4>Bowness Bay</h4><p>A beautiful Sunrise this morning taken En-route to Keswick not one as planned but I'm extremely happy I was passing the right place at the right time....</p>" data-pinterest-text="Pin it" data-tweet-text="share on twitter ">
                      <a href="">
                        <img class="img-responsive" src="https://sachinchoolur.github.io/lightGallery/static/img/thumb-2.jpg">
                        <div class="demo-gallery-poster">
                          <img src="https://sachinchoolur.github.io/lightGallery/static/img/zoom.png">
                        </div>
                      </a>
                    </li>
                    <li data-responsive="https://sachinchoolur.github.io/lightGallery/static/img/13-375.jpg 375, https://sachinchoolur.github.io/lightGallery/static/img/13-480.jpg 480, https://sachinchoolur.github.io/lightGallery/static/img/13.jpg 800" data-src="https://sachinchoolur.github.io/lightGallery/static/img/13-1600.jpg" data-sub-html="<h4>Sunset Serenity</h4><p>A gorgeous Sunset tonight captured at Coniston Water....</p>" data-pinterest-text="Pin it" data-tweet-text="share on twitter ">
                      <a href="">
                        <img class="img-responsive" src="https://sachinchoolur.github.io/lightGallery/static/img/thumb-13.jpg">
                        <div class="demo-gallery-poster">
                          <img src="https://sachinchoolur.github.io/lightGallery/static/img/zoom.png">
                        </div>
                      </a>
                    </li>
                    <li data-responsive="https://sachinchoolur.github.io/lightGallery/static/img/4-375.jpg 375, https://sachinchoolur.github.io/lightGallery/static/img/4-480.jpg 480, https://sachinchoolur.github.io/lightGallery/static/img/4.jpg 800" data-src="https://sachinchoolur.github.io/lightGallery/static/img/4-1600.jpg" data-sub-html="<h4>Coniston Calmness</h4><p>Beautiful morning</p>" data-pinterest-text="Pin it" data-tweet-text="share on twitter ">
                      <a href="">
                        <img class="img-responsive" src="https://sachinchoolur.github.io/lightGallery/static/img/thumb-4.jpg">
                        <div class="demo-gallery-poster">
                          <img src="https://sachinchoolur.github.io/lightGallery/static/img/zoom.png">
                        </div>
                      </a>
                    </li>
                    <li data-responsive="https://sachinchoolur.github.io/lightGallery/static/img/13-375.jpg 375, https://sachinchoolur.github.io/lightGallery/static/img/13-480.jpg 480, https://sachinchoolur.github.io/lightGallery/static/img/13.jpg 800" data-src="https://sachinchoolur.github.io/lightGallery/static/img/13-1600.jpg" data-sub-html="<h4>Sunset Serenity</h4><p>A gorgeous Sunset tonight captured at Coniston Water....</p>" data-pinterest-text="Pin it" data-tweet-text="share on twitter ">
                      <a href="">
                        <img class="img-responsive" src="https://sachinchoolur.github.io/lightGallery/static/img/thumb-13.jpg">
                        <div class="demo-gallery-poster">
                          <img src="https://sachinchoolur.github.io/lightGallery/static/img/zoom.png">
                        </div>
                      </a>
                    </li>
                    <li data-responsive="https://sachinchoolur.github.io/lightGallery/static/img/4-375.jpg 375, https://sachinchoolur.github.io/lightGallery/static/img/4-480.jpg 480, https://sachinchoolur.github.io/lightGallery/static/img/4.jpg 800" data-src="https://sachinchoolur.github.io/lightGallery/static/img/4-1600.jpg" data-sub-html="<h4>Coniston Calmness</h4><p>Beautiful morning</p>" data-pinterest-text="Pin it" data-tweet-text="share on twitter ">
                      <a href="">
                        <img class="img-responsive" src="https://sachinchoolur.github.io/lightGallery/static/img/thumb-4.jpg">
                        <div class="demo-gallery-poster">
                          <img src="https://sachinchoolur.github.io/lightGallery/static/img/zoom.png">
                        </div>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>--}}
          </div>
          <div class="col-sm-4" style="background-color: #d1e4ef;border-bottom-right-radius: 5px;">
            <p style="font-weight: bold;">THÔNG TIN</p>
            <div class="info-content">
            </div>
            <p style="font-weight: bold;">KIỂM TRA</p>
            <div class="work-info">Bạn cần xác nhận để giúp cv được hoàn thành</div>
            <div class="comment-info-task" style="margin-bottom: 10px;">
              
            </div>
            {{--<div class="form-group" style="margin-top: 10px;">
              <div class="col-sm-8">
                <select id="status_worktask" class="form-control select2" style="width: 100%;">
                  @foreach($status_worktask as $key => $value)
                  <option value="{{$value['text']}}">{{$value['value']}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-sm-4">
                <button type="button" class="btn btn-warning btn-sm btn-confirm" style="width: 100%;margin: 0.1em">Xác nhận</button>
              </div>
            </div>--}}
            <!-- trưởng bộ phận || trưởng ban quản lý -->
              {{--<div class="form-group" style="margin-top: 10px;">
                  <div class="col-sm-8">
                    <button type="button" class="btn btn-primary btn-sm btn-confirm-success" style="width: 100%;margin: 0.1em">Hoàn thành</button>
                  </div>
                  <div class="col-sm-4">
                    <button type="button" class="btn btn-warning btn-sm btn-confirm-return" style="width: 100%;margin: 0.1em">Trả về</button>
                  </div>
              </div>--}}
              <div class="control-status-task">
              </div>   
          </div>
          <input class="hidden" id="partners_id">
          <div class="modal-footer" style="padding:0;">
          </div>
        </form>
        <input type="hidden" id="show_task_id">
        <input type="hidden" id="base64_file_feedback_subtask_id">
        <input type="hidden" id="base64_file_feedback_task_id">
        <input type="hidden" id="status_task">
      </div>

    </div>
  </div>
</div>
<link rel="stylesheet" href="/adminLTE/plugins/lightbox/ekko-lightbox.css" />
@section('stylesheet')
<style>
  @media (min-width: 768px) {
    .detailwork {
      width: 1100px;
      margin: 30px auto;
    }
  }

  .work-info {
    margin-bottom: 10px;
  }

  .abc>tr {
    border-bottom: 1px solid #cecbcb;
    display: block;
  }

  .abc>tr:last-child {
    border-bottom: none;
  }

  /* .radio-label input {
    display: none;
  }

  .radio-label {
    cursor: pointer;
    position: absolute;
    top: 15px;
  }

  .radio-label span {
    position: relative;
    line-height: 22px;
    font: normal normal normal 14px/1 FontAwesome;
  }


  .radio-label span:before {
    content: '';
  }


  .radio-label span:before {
    border: 1px solid #d698b7;
    width: 20px;
    height: 20px;
    margin-right: 1px;
    display: inline-block;
    vertical-align: top;
  }

  .radio-label span:after {
    content: "\f00c";
    color: #fff;
    background: #ea0578;
    width: 18px;
    height: 18px;
    position: absolute;
    top: -1px;
    left: 1px;
    font-size: 17px;
    transition: 300ms;
    opacity: 0;
  }

  .radio-label input:checked+span:after {
    opacity: 1;
  } */


  .small {
    font-size: 11px;
    color: #999;
    display: block;
    margin-top: -10px
  }

  .cont {
    text-align: center;
  }


  .demo-gallery>ul {
    margin-bottom: 0;
    padding-left: 15px;
  }

  .demo-gallery>ul>li {
    margin-bottom: 15px;
    width: 180px;
    display: inline-block;
    margin-right: 15px;
    list-style: outside none none;
  }

  .demo-gallery>ul>li a {
    border: 3px solid #FFF;
    border-radius: 3px;
    display: block;
    overflow: hidden;
    position: relative;
    float: left;
  }

  .demo-gallery>ul>li a>img {
    -webkit-transition: -webkit-transform 0.15s ease 0s;
    -moz-transition: -moz-transform 0.15s ease 0s;
    -o-transition: -o-transform 0.15s ease 0s;
    transition: transform 0.15s ease 0s;
    -webkit-transform: scale3d(1, 1, 1);
    transform: scale3d(1, 1, 1);
    height: 100%;
    width: 100%;
  }

  .demo-gallery>ul>li a:hover>img {
    -webkit-transform: scale3d(1.1, 1.1, 1.1);
    transform: scale3d(1.1, 1.1, 1.1);
  }

  .demo-gallery>ul>li a:hover .demo-gallery-poster>img {
    opacity: 1;
  }

  .demo-gallery>ul>li a .demo-gallery-poster {
    background-color: rgba(0, 0, 0, 0.1);
    bottom: 0;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    -webkit-transition: background-color 0.15s ease 0s;
    -o-transition: background-color 0.15s ease 0s;
    transition: background-color 0.15s ease 0s;
  }

  .demo-gallery>ul>li a .demo-gallery-poster>img {
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    opacity: 0;
    position: absolute;
    top: 50%;
    -webkit-transition: opacity 0.3s ease 0s;
    -o-transition: opacity 0.3s ease 0s;
    transition: opacity 0.3s ease 0s;
  }

  .demo-gallery>ul>li a:hover .demo-gallery-poster {
    background-color: rgba(0, 0, 0, 0.5);
  }

  .demo-gallery .justified-gallery>a>img {
    -webkit-transition: -webkit-transform 0.15s ease 0s;
    -moz-transition: -moz-transform 0.15s ease 0s;
    -o-transition: -o-transform 0.15s ease 0s;
    transition: transform 0.15s ease 0s;
    -webkit-transform: scale3d(1, 1, 1);
    transform: scale3d(1, 1, 1);
    height: 100%;
    width: 100%;
  }

  .demo-gallery .justified-gallery>a:hover>img {
    -webkit-transform: scale3d(1.1, 1.1, 1.1);
    transform: scale3d(1.1, 1.1, 1.1);
  }

  .demo-gallery .justified-gallery>a:hover .demo-gallery-poster>img {
    opacity: 1;
  }

  .demo-gallery .justified-gallery>a .demo-gallery-poster {
    background-color: rgba(0, 0, 0, 0.1);
    bottom: 0;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    -webkit-transition: background-color 0.15s ease 0s;
    -o-transition: background-color 0.15s ease 0s;
    transition: background-color 0.15s ease 0s;
  }

  .demo-gallery .justified-gallery>a .demo-gallery-poster>img {
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    opacity: 0;
    position: absolute;
    top: 50%;
    -webkit-transition: opacity 0.3s ease 0s;
    -o-transition: opacity 0.3s ease 0s;
    transition: opacity 0.3s ease 0s;
  }

  .demo-gallery .justified-gallery>a:hover .demo-gallery-poster {
    background-color: rgba(0, 0, 0, 0.5);
  }

  .demo-gallery .video .demo-gallery-poster img {
    height: 48px;
    margin-left: -24px;
    margin-top: -24px;
    opacity: 0.8;
    width: 48px;
  }

  .demo-gallery.dark>ul>li a {
    border: 3px solid #04070a;
  }
</style>
@endsection