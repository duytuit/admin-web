@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h3>Báo cáo thông kê</h3>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Báo cáo thông kê</li>
        </ol>
    </section>
    <section class="content">
        <form id="form-search-advance" >
            <div id="search-advance" class="search-advance">
                <div class="row form-group space-5">
                    <div class="col-sm-2" style="padding-left:0">
                        <div class="input-group date">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <input id="from_date" type="text" class="form-control date_picker" name="from_date"
                                   value="{{@$filter['from_date']}}" placeholder="Từ..." autocomplete="off">
                        </div>
                    </div>
                    <div class="col-sm-2" style="padding-left:0">
                        <div class="input-group date">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <input id="to_date" type="text" class="form-control date_picker" name="to_date"
                                   value="{{@$filter['to_date']}}" placeholder="Đến..." autocomplete="off">
                        </div>
                    </div>
                    <div class="col-sm-2" style="padding-left:0">
                    <div class="form-group">
                                    <select name="company_id" id="company_id" class="form-control" onchange='pushdatabuilding()'>
                                        @foreach ($company as $value)
                                            <option value="{{ $value->id }}" @if(@$filter['company_id'] == $value->id) selected @endif > {{ $value->name }}</option>
                                        @endforeach
                                 </select>
                    </div> 
                </div>
                <div class="col-sm-2" style="padding-left:0">
                    <div class="form-group"> 
                    <select name="buildings_id" id="bdc_buildings_id" class="form-control" style="width: 100%;">
                                <option value="" selected>Tòa...</option>
                                <?php $buildings = isset($buildings) ? $buildings : '' ?>
                                    @if($buildings)
                                    <option value="{{$buildings->id}}" selected>{{$buildings->name}}</option>
                                    @endif
                            </select>
                    </div>
                    </div>
                        <button type="btn" onclick=Download() class="btn btn-info"><i class="fa fa-search"></i> Xuất báo cáo</button>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection
@section('javascript')
    <script>
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();

    document.addEventListener('DOMContentLoaded', onPageLoaded);

    function onPageLoaded() {
        pushdatabuilding();
    }

function Download(){
    var companyId = $("#company_id").val();
    var buildingID= $('#bdc_buildings_id').val();
    var fromdate= $('#from_date').val();
    var todate= $('#to_date').val();
    if (buildingID === 'All') { 
      const url = `https://bdcadmin.dxmb.vn/admin/v2/apartments/get-apartment-company?fillter=All&company_id=${companyId}&from_date=${fromdate}&to_date=${todate}`; 
      window.open(url, '_blank');
    }
    else
    {
    const url = `https://bdcadmin.dxmb.vn/admin/v2/apartments/get-apartment-company?building_id=${buildingID}&from_date=${fromdate}&to_date=${todate}`; 
    window.open(url, '_blank');
    }
    setTimeout(function() {
    location.reload(); 
  }, 2000);
    }

        function pushdatabuilding() {
        var companyId = $("#company_id").val()
         $.ajax({
        url: '{{ url('admin/service-apartment/ajaxGetSelectBuildings') }}',
        dataType: 'json',
        data: { company_id: companyId },
        success: function(response) {
            $('#bdc_buildings_id').empty();
            $('#bdc_buildings_id').append('<option value="All">Tất cả</option>');
        for (var i = 0; i < response.length; i++) {
            var building = response[i];
            console.log(response[i].name);
            var option = '<option value="' + building.id + '">' + building.name + '</option>';
            $('#bdc_buildings_id').append(option);
            }
        }
    });
}
    </script>
@endsection