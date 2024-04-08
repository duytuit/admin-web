<div class="tab-pane" id="document_apartment" style="padding: 15px 0;">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="box box-primary">
                <h1>
                    <a href=""
                       class="btn btn-success"
                       data-toggle="modal"
                       data-target="#add-document-apartment"
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
                                <td>Căn hộ/Nhóm căn hộ</td>
                                <th>File</th>
                                <th>Tác vụ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($document_apartments))
                            @foreach($document_apartments as $key=>$document_apartment)
                                <tr class="document-row">
                                    <td class="document_id" data-id="{{$document_apartment->id}}" >{{$key+1}}</td>
                                    <td class="document_title">{{$document_apartment->title}}</td>
                                    <td class="document_description" >{{$document_apartment->description}}</td>
                                    <td class="document_apartment_list">
                                        @if(count($document_apartment->apartments)>0)
                                            Căn hộ:
                                            @foreach($document_apartment->apartments as $apartment)
                                                <a href="">{{$apartment->name}} </a> &sbquo;
                                            @endforeach

                                            <select class="document_ap_list" style="display: none" id="">
                                            @foreach($document_apartment->apartments as $apartment)
                                                <option value="{{$apartment->id}}" selected="selected">{{$apartment->name}}</option>
                                            @endforeach
                                            </select>
                                            <span class="document_type" data-id="2" style="display: none"></span>
                                        @endif
                                        @if(count($document_apartment->apartment_groups)>0)
                                            Nhóm căn hộ:
                                            @foreach($document_apartment->apartment_groups as $apartment_group)
                                                <a href="">{{$apartment_group->name}} </a> &sbquo;
                                            @endforeach
                                            <select class="document_ap_gr_list" style="display: none" id="">
                                                @foreach($document_apartment->apartment_groups as $apartment_group)
                                                    <option value="{{$apartment_group->id}}" selected="selected">{{$apartment_group->name}}</option>
                                                @endforeach
                                            </select>
                                            <span class="document_type" data-id="3" style="display: none"></span>
                                        @endif
                                    </td>
                                    <td class="document_attach_file">
                                        @foreach($document_apartment->attach_file as $k => $file)
                                            <a href="{{ "https://media.dxmb.vn" . $file->hash_file}}" target="_blank" >{{$file->file_name}}</a>
                                            <br>
                                        @endforeach
                                        <div class="attach_file" style="display: none">
                                        @foreach($document_apartment->attach_file as $file)
                                            <div class='file-upload-item'>
                                                <span class='image' data-name="{{$file->file_name}}" data-base='{{$file->hash_code}}'>{{$file->file_name}}</span>&ensp;
                                                <i class='fa fa-close btn-remove-image' onclick='removeThisFile(this)'></i>
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-info edit-document-apartment" >
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a class="btn btn-danger delete-asset"
                                           data-id="{{$document_apartment->id}}"
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
    @include('v3.documents.modals.add-document-apartment')
    @include('v3.documents.modals.edit-document-apartment')
</div>
