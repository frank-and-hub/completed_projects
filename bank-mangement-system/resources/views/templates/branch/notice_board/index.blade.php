@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Notice Board</h3>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-6">
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark"></h3>
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12"> 
                            <div class="notice-list panel-body">
                                @if( count($noticeLists) > 0 )
                                    <marquee direction="up" behavior=scroll scrollamount="4" onmouseleave="this.start();" onmouseover="this.stop();">
                                        <dl>
                                        @foreach( $noticeLists as $key => $noticeList)
                                                <dt @if($key%2 == 0 )style="background-color:#e0f7fa;padding:5px;"@else style="padding:5px;"@endif >
                                                    <span class="notice" data-id="{{ $noticeList->id }}" style="color:blue;">
                                                        <strong style="color: red;">
                                                        {{ \Carbon\Carbon::parse($noticeList->start_date)->format('d M Y')}}
                                                        </strong> 
                                                        | {{$noticeList->title}}
                                                    </span> 
                                                </dt>
                                        @endforeach
                                        </dl>
                                    </marquee>
                                @else
                                    Not Available!
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        @if( $noticeDocuments )
                        <h3 class="mb-0 text-dark" id="document-title"><strong>{{ \Carbon\Carbon::parse($noticeDocuments->start_date)->format('d M Y')}}</strong>  {{ $noticeDocuments->title }}</h3>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12">
                            <div class="notice-loader" style="display:none"></div>
                            <div class="panel-body" id="notice-document">
                                <dl>
                                    @if( $noticeDocuments && count($noticeDocuments->files) > 0 )
                                        @foreach($noticeDocuments->files as $document)
                                            @php $extension = explode('.',$document->file_name); @endphp
                                            @if ($extension[1] == 'pdf')
                                                <dt>
                                                    <a href="{{ImageUpload::generatePreSignedUrl($document->file_path . $document->file_name)}}" target="_blank">
                                                        <img alt="'{{$document->file_name}}'" src="{{config('app.url')}}{{$document->file_path.'icon-pdf.png' }}" style="margin-left:auto; margin-right:auto;width:200px;display:block;"/>
                                                    </a>
                                                </dt>
                                            @else
                                                <dt>
                                                    <a href="{{ImageUpload::generatePreSignedUrl($document->file_path . $document->file_name)}}" target="_blank">
                                                        <img alt="'{{$document->file_name}}'" src="{{config('app.url')}}{{$document->file_path.$document->file_name }}" style="margin-left:auto; margin-right:auto;width:100%;display:block;"/>
                                                    </a>
                                                </dt>
                                            @endif
                                        @endforeach
                                    @else
                                        Not Available!
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <style>
        .notice {cursor: pointer;}
        #notice-document img {width: -moz-available;}
        #notice-document dl dt {padding-bottom: 10px; padding-top: 10px;}
        .notice-loader {
            position: absolute;
            right: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            /* background: url('{{url('/')}}/asset/images/loader.gif') 50% 50% no-repeat rgb(249,249,249); */
        }
        /*.notice-list{height:537px;min-height:537px;}*/
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            var app_url = "{!! url('/') !!}"
            // var app_url = "{!! config('app.url') !!}"
            $(document).on('click','.notice',function(){
                $(".notice-loader" ).show();
                var title = $(this).html();
                var id = $(this).data('id');
                var url = app_url+"/branch/get-noticeboard";
                console.log(url);
                $.post(url,{'id':id},function( e ) {
                    let data = e.data;
                    let imageUrl = e.image;
                    if (data) {
                        $(".notice-loader" ).hide();
                        var docData = '<dl>';
                        $.each(data, function( index, value ) {
                            var name = value.file_name.split(".");
                            let type = name[name.length - 1];
                            if( type == 'pdf'){
                                // docData = docData + '<dt><a href="'+app_url+'/'+value.file_path+value.file_name+'" target="_blank"><img ' + 'src="'+app_url+'/asset/notice-board/icon-pdf.png'+'" style="margin-left:auto;margin-right:auto;width:200px;display:block;"/></a></dt>';
                                docData = docData + '<dt><a href="'+imageUrl[index]+'" target="_blank"><img ' + 'src="'+app_url+'/asset/notice-board/icon-pdf.png'+'" alt="'+value.file_name+'" style="margin-left:auto;margin-right:auto;width:200px;display:block;"/></a></dt>';
                            } else {
                                // docData = docData + '<dt><a href="'+app_url+'/'+value.file_path+value.file_name+'" target="_blank"><img ' + 'src="'+app_url+'/'+value.file_path+value.file_name+'" style="margin-left:auto;margin-right:auto;width:100%;display:block;' + '"/></a></dt>';
                                docData = docData + '<dt><a href="'+imageUrl[index]+'" target="_blank"><img ' + 'src="'+imageUrl[index]+'" alt="'+value.file_name+'" style="margin-left:auto;margin-right:auto;width:100%;display:block;' + '"/></a></dt>';
                            }
                        });
                        docData = docData +"</dl>"
                    }
                    console.log(docData);
                    $("#notice-document").html(docData);
                    $("#document-title").html(title);

                },'JSON');


            });

        });
    </script>
@stop