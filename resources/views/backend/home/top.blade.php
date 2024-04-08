<div class="row">
    <div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="icon" style="color: white !important;">
                <i class="fa fa-home"></i>
            </div>
            <div class="inner">
                <h3>{{$count_apt}}</h3>

                <p>Căn hộ</p>
            </div>
            <a href="{{route('admin.apartments.index')}}" class="small-box-footer">
                <i class="fa fa-hand-o-right"></i> Xem danh sách
            </a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow-gradient">
            <div class="icon" style="color: white !important;">
                <i class="fa fa-group"></i>
            </div>
            <div class="inner">
                <h3>{{$count_cus}}</h3>

                <p>Cư dân</p>
            </div>
            <a href="{{route('admin.customers.index')}}" class="small-box-footer">
                <i class="fa fa-hand-o-right"></i> Xem danh sách
            </a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-light-blue-active">
            <div class="icon" style="color: white !important;">
                <i class="fa fa-car"></i>
            </div>
            <div class="inner">
                <h3>{{$count_vh}}</h3>
                <p>Phương tiện</p>
            </div>
            {{-- <a href="{{route('admin.vehicles.index')}}" class="small-box-footer">
                <i class="fa fa-hand-o-right"></i> Xem danh sách
            </a> --}}
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6 hidden">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="icon" style="color: white !important;">
                <i class="fa fa-motorcycle"></i>
            </div>
            <div class="inner">
                <h3>65</h3>

                <p>Xe máy</p>
            </div>
            {{-- <a href="{{route('admin.vehicles.index')}}" class="small-box-footer">
                <i class="fa fa-hand-o-right"></i> Xem danh sách
            </a> --}}
        </div>
    </div>
    <!-- ./col -->
</div>