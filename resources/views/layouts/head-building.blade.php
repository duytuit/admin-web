<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box" style="border-color: #dd4b39;">
                <span class="info-box-icon bg-red"><i class="fa fa-bomb building-icon"></i><i class="text-icon">250</i></span>

            <div class="info-box-content text-center text-red">
                <span class="text-building-info">Khách quá hạn</span>
                <span class="info-box-number">{{number_format($dashboard['outOfDate'])}}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box" style="border-color: #f39c12 ;">
            <span class="info-box-icon bg-yellow"><i class="fa fa-cloud-upload building-icon"></i><i class="text-icon">250</i></span>

            <div class="info-box-content text-center text-yellow">
                <span class="text-building-info">Mới phát sinh</span>
                <span class="info-box-number">{{number_format($dashboard['NewBorn'])}}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->

    <!-- fix for small devices only -->
    <div class="clearfix visible-sm-block"></div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box" style="border-color: #00a65a  ;">
            <span class="info-box-icon bg-green building-icon"><i class="fa fa-money building-icon"></i><i class="text-icon">250</i></span>

            <div class="info-box-content text-center text-green">
                <span class="text-building-info">Đã thanh toán</span>
                <span class="info-box-number">{{number_format($dashboard['totalPaid'])}}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box" style="border-color: #0073b7;">
            <span class="info-box-icon bg-blue-gradient"><i class="fa fa-shopping-cart building-icon"></i><i class="text-icon">250</i></span>

            <div class="info-box-content text-center text-blue">
                <span class="text-building-info" >Tổng hóa đơn</span>
                <span class="info-box-number">{{number_format($dashboard['totalBill'])}}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
</div>