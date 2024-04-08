@extends('backend.layouts.master')

@section('content')
<section class="content-header">
  <h1>
    Quản lý kế toán
    <small>Chi tiết công nợ</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
    <li class="active">Chi tiết công nợ</li>
  </ol>
</section>

<section class="content">
  <div class="box box-primary">
    <section class="invoice border-bill">
      <!-- title row -->
      <div class="row">
        <div class="col-xs-12">
          <div class="table-responsive" style="padding-left: 20px">
            <table width="100%">
              {{-- <thead>
                <tr>
                  <td width='50%'>BQL Tòa nhà</td>
                  <td width='50%'>Số PT</td>
                </tr>
              </thead> --}}
              <tbody>
                <tr>
                  <td width='80%'>
                    <b class="text-blue">
                      BQL Tòa nhà
                    </b>
                    <br>
                    <b>
                      {{@$receipt->building->name}}
                    </b>
                  </td>
                  <td width='20%'>Số PT: {{@$receipt->bdc_receipt_total}}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="col-xs-12">
          <h2 class="text-center">
            @if(@$receipt->type == $receiptRepo::PHIEUTHU)
            PHIẾU THU
            @elseif(@$receipt->type == $receiptRepo::PHIEUTHU_TRUOC)
            Phiếu thu khác
            @else
            PHIẾU CHI
            @endif
          </h2>
          <h5 class="text-center"><i>Ngày {{date("d")}} tháng {{date("m")}} năm {{date("Y")}}</i></h5>
        </div>
        <!-- /.col -->
      </div>
      <!-- info row -->
      <div class="table-responsive" style="padding-left: 20px">
        <table width="100%">
          <thead>
            <tr>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td width='20%'>Người nộp tiền :</td>
              <td width="80%">{{@$receipt->customer_name}}</td>
            </tr>
            <tr>
              <td width='20%'>Căn hộ :</td>
              <td width="80%"><a
                  href="{{route('admin.apartments.edit', ['id' => $receipt->bdc_apartment_id])}}">{{@$receipt->apartment->name}}</a>
              </td>
            </tr>
            <tr>
              <td width='20%'>Hình thức :</td>
              <td width="80%">
                @if($receipt->type_payment == 'tien_mat')
                Tiền mặt
                @elseif ($receipt->type_payment == 'chuyen_khoan')
                Chuyển khoản
                @else
                VNPay
                @endif
              </td>
            </tr>
            <tr>
              <td width='20%'>Số tiền nộp :</td>
              <td width="80%">{{@$receipt->cost}}</td>
            </tr>
            <tr>
              <td width='20%'>Bằng chữ :</td>
              <td width="80%">{{@$number_cost}}</td>
            </tr>
            <tr>
              <td width='20%'>Nội dung :</td>
              <td width="80%">{{@$receipt->description}}<a href="{{route('admin.bill.show', ['id' => 0, 'bill_code' => $bill])}}">{{$bill}}</a></td>
            </tr>
          </tbody>
        </table>
        <br>
        <table width="100%" style="padding-left: 20px">
          <thead>
            <tr>
              <th width="20%">Giám đốc</th>
              <th width="20%">Kế toán</th>
              <th width="20%">Thủ quỹ</th>
              <th width="20%">Người lập</th>
              <th width="20%">Người nộp</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td width="20%">(Ký, họ tên)</td>
              <td width="20%">(Ký, họ tên)</td>
              <td width="20%">(Ký, họ tên)</td>
              <td width="20%">(Ký, họ tên)</td>
              <td width="20%">(Ký, họ tên)</td>
            </tr>
          </tbody>
        </table>
      </div>
      <br />
      <hr>
      <!-- /.row -->
      {{-- @php
                $totalService = 0;
                $totalVehicle = 0;
                @endphp --}}
      <!-- Table row -->
      <div class="row border-bill-detail" hidden>
        {{-- @if(count($debit_detail['service']) > 0) --}}
        <div class="box-body box-solid">
          <div class="col-xs-12 table-responsive">
            <table class="table table-striped">
              <thead class="bg-light-blue">
                <th colspan="9" class="text-center">Danh sách dịch vụ</th>
              </thead>
              <tbody>
                <tr>
                  <th>STT</th>
                  <th>Dịch vụ</th>
                  <th>Sản phẩm</th>
                  <th>Nhóm</th>
                  <th>Thời gian</th>
                  <th>Phát sinh</th>
                  <th>Nợ cũ</th>
                  <th>Tổng</th>
                  <th>Thanh toán</th>
                </tr>
                @foreach($receipt->apartment->apartmentServicePrices as $key => $servicePrice)
                <tr>
                  <td> {{ $key + 1 }}</td>
                  <td>{{ $servicePrice->service->name }}</td>
                  {{-- <td>{{ $service->title }}</td>
                  <td class="price-service" align="right">{{ number_format($service->price) }}</td>
                  <td class="amount-service">{{ $service->quantity }}</td>
                  <td align="right">{{ number_format($service->sumery) }}</td>
                  <td align="right">{{ number_format($service->previous_owed) }}</td>
                  <td align="right">{{ number_format($service->new_sumery) }}</td>
                  <td>
                    {{ date('d/m/Y', strtotime($service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}
                  </td>
                </tr> --}}
                @endforeach
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><b>Tổng TT</b></td>
                  <td align="right"><b>12000000</b> <b>VND</b></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        {{-- @endif --}}
        <!-- /.col -->
      </div>
    </section>
  </div>
</section>
@endsection
@section('javascript')
{{-- <script>
        var request = false;
        $(document).on('click', '.edit_row', function (e) {
            e.preventDefault();
            if (!request) {
                request = true;
                var tr = $(this).parents('tr');
                var td = $(this).parents('td');
                var amount = tr.find('td.amount-service');
                var price = tr.find('td.price-service');
                amount.attr('data-value', amount.text());
                price.attr('data-value', price.text());
                amount.html(`<input type="text" class="form-control" name="amount" value="`+ amount.text() +`">`);
                price.html(`<input type="text" class="form-control" name="price" value="`+ price.text() +`">`);
                td.html(`<a href="" class="btn btn-sm btn-success save-row"></i>Lưu</a>
                          <a href="" class="btn btn-sm btn-warning cancel-row">Cancel</a>`);
            }
            request = false;
        })

        $(document).on('click', 'a.cancel-row', function (e) {
            e.preventDefault();
            if (!request) {
                request = true;
                var tr = $(this).parents('tr');
                var td = $(this).parents('td');
                var amount = tr.find('td.amount-service');
                var price = tr.find('td.price-service');
                amount.html(amount.attr('data-value'));
                price.html(price.attr('data-value'));
                td.html(`<a href="" class="btn btn-sm btn-primary edit_row"><i class="fa fa-pencil"></i> Sửa</a>`);
                request = false;
            }
        })
    </script> --}}
@endsection