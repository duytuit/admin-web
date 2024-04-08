<div class="tab-pane active" id="document_building" style="padding: 15px 0;">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="box box-primary">
                <h1>
                    <a href=""
                       class="btn btn-success"
                       data-toggle="modal"
                       data-target="#add-document-building"
                    >
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới
                    </a>
                </h1>
                <div class="box-body">
                    <form action="" id="">
                        {{ csrf_field() }}
{{--                        <div class="row">--}}
{{--                            <div class="col-sm-2">--}}
{{--                                <input type="text" class="form-control" name="name" placeholder="Nhập keyword" value="">--}}
{{--                            </div>--}}
{{--                            <div class="input-group-btn">--}}
{{--                                <button type="submit" title="Tìm kiếm" class="btn btn-info"><i class="fa fa-search"></i></button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </form>
                    <div class="clearfix"></div>
                    <br>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th>STT</th>
                                    <th>Tiêu đề</th>
                                    <th>Ghi chú</th>
                                    <th>File</th>
                                    <th>Tác vụ</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(isset($document_buildings))
                            @foreach($document_buildings as $key=>$document_building)
                                <tr class="document-row">
                                    <td class="document_id" data-id="{{$document_building->id}}">{{$key+1}}</td>
                                    <td class="document_title">{{$document_building->title}}</td>
                                    <td class="document_description" >{{$document_building->description}}</td>
                                    <td class="document_attach_file">
                                        @foreach($document_building->attach_file as $file)
                                            <a href="{{ "https://media.dxmb.vn" . $file->hash_file}}" data-base64="{{$file->hash_code}}" target="_blank" >{{$file->file_name}}</a>
                                            <br>
                                        @endforeach
                                        <div class="attach_file" style="display: none">
                                            @foreach($document_building->attach_file as $file)
                                                <div class='file-upload-item'>
                                                    <span class='image' data-name="{{$file->file_name}}" data-base='{{$file->hash_code}}'>{{$file->file_name}}</span>&ensp;
                                                    <i class='fa fa-close btn-remove-image' onclick='removeThisFile(this)'></i>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-info edit-document-building" >
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a class="btn btn-danger delete-asset"
                                           data-id="{{$document_building->id}}"
                                           title="Xóa tài liệu">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('v3.documents.modals.add-document-building')
    @include('v3.documents.modals.edit-document-building')
</div>
