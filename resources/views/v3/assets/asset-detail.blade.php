@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.master')

@section('content')

@section('content')
<section class="content-header">
    <h1>
        {{$meta_title}} - {{$asset->name}}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Thông tin tài sản</li>
    </ol>
</section>

<section class="content container">
    <div class="row">
        <div class="col-md-12">
            <h3>Thông tin tài sản</h3>
            <div class="row">
                <div class="col-md-4">
                    <p>Tài sản: <b></b></p>
                    <p>Danh mục: <b></b></p>
                </div>
                <div class="col-md-4">
                    <p>Khu vực:<b></b> </p>
                    <p>Bộ phận:<b></b> </p>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <h3>Danh sách lịch bảo trì</h3>
            <div class="container">
                @include('v3.assets.components.maintain-list')
            </div>
        </div>
    </div>
    @include('v3.assets.modals.update-status-maintain')
</section>


@endsection

@section('javascript')
    <script>
        $(function (){
            $('.update_status_maintain').on('click',function (){
                let row = $(this).closest('.maintain-time-row');
                let id = row.find('.id-maintain-time').attr('data-id');
                $('#id-asset-maitain').val(id);
                $('#update_status_maintain').modal('show');
            })
            $("#fileupload").change(function () {
                if (typeof (FileReader) != "undefined") {
                    var dvPreview = $("#dvPreview");
                    // dvPreview.html("");
                    var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
                    $($(this)[0].files).each(function () {
                        var file = $(this);
                        if (regex.test(file[0].name.toLowerCase())) {
                            var reader = new FileReader();
                            reader.onload = function (e) {
                                let boxChild = $("<div class='image-upload-item'><i class='fa fa-close btn-remove-image' onclick='removeThis(this)'></i><img class='image' /></div>");
                                boxChild.children('.image').attr("src", e.target.result);
                                boxChild.children('.image').attr("title",file[0].name.toLowerCase());
                                // img.attr("src", e.target.result);
                                dvPreview.append(boxChild);
                            }
                            reader.readAsDataURL(file[0]);
                        } else {
                            alert(file[0].name + " is not a valid image file.");
                            dvPreview.html("");
                            return false;
                        }
                    });
                } else {
                    alert("This browser does not support HTML5 FileReader.");
                }
            });

            $('.btn-js-action-update-maintain').on('click', function (e){
                e.preventDefault();
                let alert_pop_update_maintain = $('.alert_pop_update_maintain');

                alert_pop_update_maintain.hide();

                let row = $(this)

                let description = $('#description-asset-maintain').val();

                let price = $('#price-asset-maintain').val();

                let id = $('#id-asset-maitain').val();

                let images = [];

                $('#dvPreview img').each((index,value)=>{
                    images.push({
                        file_name: $(value).attr('title'),
                        hash_file: $(value).attr('src')
                    });
                })

                images = JSON.stringify(images);

                if (description == "") {
                    alert_pop_update_maintain.show();
                    alert_pop_update_maintain.html('<li>Kết quả thực hiện không được để  trông</li>')
                }
                else if (price == "") {
                    alert_pop_update_maintain.show();
                    alert_pop_update_maintain.html('<li>Giá không được để  trông</li>')
                }
                else {
                    showLoading();
                    $.ajax({
                        url: '/admin/v3/assets/update-maintain',
                        type: 'POST',
                        data: {
                            id: id,
                            description: description,
                            price: price,
                            attach_file: images
                        },
                        success: function (response) {
                            if (response.code === 0) {
                                alert("Cập nhật thành công");
                                location.reload();
                            } else {
                                alert("Cập nhật không thành công");
                                location.reload();
                            }
                            hideLoading();
                        },
                        error: function (e) {
                            console.log(e);
                            hideLoading();
                        }
                    })
                }

            })
        })
    </script>
@endsection