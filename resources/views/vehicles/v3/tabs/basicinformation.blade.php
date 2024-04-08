<table style="width: 100%; padding: 10px;">
<tr>
    <td style="width: 500px;float: left; text-align: center;" >
        <div class="panel panel-default" style="border-radius: 30px">
            <div class="panel-body">
                <div class="box box-primary">
                    <div class="box-body " style="height: 230px;">
                        <form style="display: inline-block;">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                <?php $currentDate = date('Y-m-d');  ?>
                                    <table>
                                        <tr>
                                            <td class="padding-tb" style="text-align: left;">
                                                <strong style="font-size: 30px;" >Tổng lượt xe ({{$currentDate}})</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="padding-tb" style="text-align: left;">
                                                 Tổng phương tiện đỗ trong bãi: {{$totalVehicleInPark}} 
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="padding-tb" style="text-align: left;">
                                                 Tổng xe ô tô đỗ trong bãi: {{$totalVehicleInPark_car}} 
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="padding-tb" style="text-align: left;">
                                                 Tổng xe máy đỗ trong bãi: {{$totalVehicleInPark_motor}} 
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="padding-tb" style="text-align: left;">
                                                 Tổng xe điện đỗ trong bãi: {{$totalVehicleInPark_electricBike}} 
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="padding-tb" style="text-align: left;">
                                            
                                                 Tổng xe đạp đỗ trong bãi: {{$totalVehicleInPark_bicycle}} 
                                            </td>
                                        </tr>
                                        <tr> 
                                            <td class="padding-tb" style="text-align: left;">  
                                            Tổng các loại xe ra / xe vào: {{($VehicleOut_Daily)}} / {{$VehicleIn_Daily}} 
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
                <div class="box box-primary">
                    <div class="box-body " style="height: 160px;">
                        <form style="display: inline-block;">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <table>
                                        <tr>
                                            <td class="padding-tb" style="text-align: center;">
                                                <img style="max-height: 80px;" src="https://cdn.dxmb.vn/media/buildingcare/2023/1002/0306-3692485.png" alt="image_moto"></img>
                                            </td>
                                            <td style="color: #e13f3f;">
                                                <h1><strong> 100 </strong></h1>  
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="padding-tb" style="text-align: center;">
                                            <h4><strong> Lượt vào <img alt="moto_dt" src="https://media1.giphy.com/media/QHtLljSIwLmjjXuf7T/200.gif?cid=6c09b952ka3ft0fm7e7gqipiwziok45jogvmtaxdlq6u2v90&ep=v1_internal_gif_by_id&rid=200.gif&ct=s" style="width: 40px;"> 10 </strong></h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="padding-tb" style="text-align: center;">
                                            <h4><strong> Lượt ra <img alt="moto_dt" src="https://vmsco.vn/wp-content/uploads/2016/04/arrow-1.gif" style="width: 40px;"> 10 </strong></h4>
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
                <div class="box box-primary">
                    <div class="box-body " style="height: 160px;">
                        <form style="display: inline-block;">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <table>
                                        <tr>
                                            <td class="padding-tb" style="text-align: center;">
                                                <img style="max-height: 80px;" src="https://cdn.dxmb.vn/media/buildingcare/2023/1002/0306-img-466072.png" alt="car_image"></img>
                                            </td>
                                            <td style="color: #e13f3f;text-align: center;">
                                                <h1><strong> 99 </strong></h1>  
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="padding-tb" style="text-align: center;">
                                                <h4><strong> Lượt vào <img alt="oto_dt" src="https://media1.giphy.com/media/QHtLljSIwLmjjXuf7T/200.gif?cid=6c09b952ka3ft0fm7e7gqipiwziok45jogvmtaxdlq6u2v90&ep=v1_internal_gif_by_id&rid=200.gif&ct=s" style="width: 40px;"> 10 </strong></h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="padding-tb" style="text-align: center;">
                                            <h4><strong> Lượt ra <img alt="oto_dt" src="https://vmsco.vn/wp-content/uploads/2016/04/arrow-1.gif" style="width: 40px;"> 10 </strong></h4>
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