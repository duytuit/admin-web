
<table style="width: 100%; padding: 10px;">
<tr>
    <td style="width: 500px;float: left; text-align: center;" >
        <div class="panel panel-default" style="border-radius: 30px">
            <div class="panel-body">
                <div class="box box-primary" style="border-radius: 30px;">
                    <div class="box-body " style="height: 100px;">
                        <form style="display: inline-block;">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <table>
                                        <tr>
                                            <td colspan="3" class="padding-tb" style="text-align: left;">
                                                <strong style="font-size: 30px"> Email </strong> 
                                            </td>
                                            <td colspan="3" class="padding-tb" style="text-align: left;"> </td>
                                            <td colspan="3" class="padding-tb" style="text-align: center;color: green;">
                                                <strong style="font-size: 20px"> +99% </strong>
                                            </td>
                                            <td colspan="3" class="padding-tb" style="text-align: right;">
                                                <strong  style="font-size: 30px"> {{$mail_total}} </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="padding-tb">
                                                <img width="50px" alt="" src="https://i.pinimg.com/originals/90/13/f7/9013f7b5eb6db0f41f4fd51d989491e7.gif"> 
                                            </td>
                                            <td colspan="2"> 
                                                <div> <strong> {{$sended_mail}}</strong> </div>
                                                <div> Thành công </div>
                                            </td>
                                            <td colspan="2"> 
                                                <img width="50px" src="https://media0.giphy.com/media/U6kyBGdukSZqaaVAta/giphy.gif?cid=6c09b9529a45x4siecdy2isr539zzt8y2w3gqdxug8pti9v0&ep=v1_stickers_related&rid=giphy.gif&ct=s" alt="imgfail">
                                            </td>
                                            <td colspan="2">
                                                <div><strong> 0</strong></div>
                                                <div> Lỗi </div>
                                            </td>
                                            <td colspan="2">
                                                <img width="50px" src="https://media.istockphoto.com/id/1407160246/vector/danger-triangle-icon.jpg?s=612x612&w=0&k=20&c=BS5mwULONmoEG9qPnpAxjb6zhVzHYBNOYsc7S5vdzYI=" alt="image_warning">
                                            </td>
                                            <td colspan="2">
                                                <div><strong> {{$mail_total - $sended_mail}} </strong></div>
                                                <div>Warning </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </form><!-- END #form-search-advance -->
                    </div>
                </div>
            </div>
        </div>
    </td>
    <td style="width: 500px; text-align: center;">
    <div class="panel panel-default" style="border-radius: 30px">
            <div class="panel-body">
                <div class="box box-primary" style="border-radius: 30px;">
                    <div class="box-body " style="height: 100px;">
                        <form style="display: inline-block;">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                <table>
                                        <tr>
                                            <td colspan="3" class="padding-tb" style="text-align: left;">
                                                <strong style="font-size: 30px"> SMS </strong> 
                                            </td>
                                            <td colspan="3" class="padding-tb" style="text-align: left;"> </td>
                                            <td colspan="3" class="padding-tb" style="text-align: center;color: green;">
                                                <strong style="font-size: 20px"> +99% </strong>
                                            </td>
                                            <td colspan="3" class="padding-tb" style="text-align: right;">
                                                <strong  style="font-size: 30px"> {{$sms_total}}  </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="padding-tb">
                                                <img width="50px" alt="" src="https://i.pinimg.com/originals/90/13/f7/9013f7b5eb6db0f41f4fd51d989491e7.gif"> 
                                            </td>
                                            <td colspan="2"> 
                                                <div> <strong> {{$sended_sms}} </strong> </div>
                                                <div> Thành công </div>
                                            </td>
                                            <td colspan="2"> 
                                                <img width="50px" src="https://media0.giphy.com/media/U6kyBGdukSZqaaVAta/giphy.gif?cid=6c09b9529a45x4siecdy2isr539zzt8y2w3gqdxug8pti9v0&ep=v1_stickers_related&rid=giphy.gif&ct=s" alt="imgfail">
                                            </td>
                                            <td colspan="2">
                                                <div><strong> 0</strong></div>
                                                <div> Lỗi </div>
                                            </td>
                                            <td colspan="2">
                                                <img width="50px" src="https://media.istockphoto.com/id/1407160246/vector/danger-triangle-icon.jpg?s=612x612&w=0&k=20&c=BS5mwULONmoEG9qPnpAxjb6zhVzHYBNOYsc7S5vdzYI=" alt="image_warning">
                                            </td>
                                            <td colspan="2">
                                                <div><strong> {{$sms_total- $sended_sms}} </strong></div>
                                                <div>Warning </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </form><!-- END #form-search-advance -->
                    </div>
                </div>
            </div>
        </div>
    </td>
    <td style="width: 500px;float: right; text-align: center;">
    <div class="panel panel-default" style="border-radius: 30px">
            <div class="panel-body">
                <div class="box box-primary" style="border-radius: 30px;">
                    <div class="box-body " style="height: 100px;">
                        <form style="display: inline-block;">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                <table>
                                        <tr>
                                            <td colspan="3" class="padding-tb" style="text-align: left;">
                                                <strong style="font-size: 30px"> APP </strong> 
                                            </td>
                                            <td colspan="3" class="padding-tb" style="text-align: left;"> </td>
                                            <td colspan="3" class="padding-tb" style="text-align: center;color: green;">
                                                <strong style="font-size: 20px"> +99% </strong>
                                            </td>
                                            <td colspan="3" class="padding-tb" style="text-align: right;">
                                                <strong  style="font-size: 30px"> {{$app_total}} </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="padding-tb">
                                                <img width="50px" alt="" src="https://i.pinimg.com/originals/90/13/f7/9013f7b5eb6db0f41f4fd51d989491e7.gif"> 
                                            </td>
                                            <td colspan="2"> 
                                                <div> <strong> {{$sended_app}}</strong> </div>
                                                <div> Thành công </div>
                                            </td>
                                            <td colspan="2"> 
                                                <img width="50px" src="https://media0.giphy.com/media/U6kyBGdukSZqaaVAta/giphy.gif?cid=6c09b9529a45x4siecdy2isr539zzt8y2w3gqdxug8pti9v0&ep=v1_stickers_related&rid=giphy.gif&ct=s" alt="imgfail">
                                            </td>
                                            <td colspan="2">
                                                <div><strong> 0</strong></div>
                                                <div> Lỗi </div>
                                            </td>
                                            <td colspan="2">
                                                <img width="50px" src="https://media.istockphoto.com/id/1407160246/vector/danger-triangle-icon.jpg?s=612x612&w=0&k=20&c=BS5mwULONmoEG9qPnpAxjb6zhVzHYBNOYsc7S5vdzYI=" alt="image_warning">
                                            </td>
                                            <td colspan="2">
                                                <div><strong> {{$app_total- $sended_app}} </strong></div>
                                                <div>Warning </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </form><!-- END #form-search-advance -->
                    </div>
                </div>
            </div>
        </div>
    </td>
</tr>
</table>
