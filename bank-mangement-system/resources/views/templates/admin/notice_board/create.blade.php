@extends('templates.admin.master')

@section('content')


    <div class="content">

        @if ($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif

        <form action="{!! route('admin.notice.store') !!}" method="post" enctype="multipart/form-data" id="notice_create" name="notice_create">
        @csrf
            <div class="card bg-white" >
              <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Date<sup class="required">*</sup></label>
                            <div class="col-lg-12 error-msg">
                                <div class="input-group">
                                    <span class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                    </span>
                                    <input type="text" class="form-control " name="application_date" id="application_date"  >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Title<sup class="required">*</sup></label>
                            <div class="col-lg-12 error-msg">
                                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}"  >
                            </div>
                        </div>
                  </div>

                  <div class="col-lg-12">
                      <table id="table" width="70%">
                          <thead>
                          <tr>
                              <td colspan="4"><h3 class="card-title mb-3"> Upload Files</h3><small>(Accept only png,jpg, jpeg or pdf files.)</small></td>
                          </tr>
                          </thead>
                          <tbody>
                          <tr class="add_row">
                              <td id="no" width="5%"></td>
                              <td width="75%"><input type="file" class="left" id="document0" name="document[]">
                                  <label id="document0-error" class="error" for="document0"></label>
                                  </td>
                              <td width="20%"></td>
                          </tr>
                          </tbody>
                          <tfoot>
                          <tr>
                              <td colspan="4">
                                  <button class="btn btn-success btn-sm" type="button" id="add" title='Add new file'/>Add new file</button>
                              </td>
                          </tr>
                          </tfoot>
                      </table>
                   {{-- <h3 class="card-title mb-3"> Upload Files</h3>
                    <div class="form-group row">
                      <div class="col-lg-6" id="file-upload-name">
                      </div>
                      <div class="col-lg-6 custom-file error-msg">
                        <input type="file" class="custom-file-input" id="document" name="document[]" multiple>
                        <label class="custom-file-label" for="photo">Select document</label>
                        <span class="form-text text-muted">Accepted formats:png, jpg,jpeg, pdf.</span>
                      </div>
                    </div>--}}
                  </div>
                </div>

                  <table class="table datatable-show-all">
                      <thead>
                      <tr>
                          {{--<th>#</th>--}}
                          <th>State Name</th>
                          <th>Check Permission</th>
                          <th></th>
                      </tr>
                      </thead>
                      <tbody>
                      @php
                          $i = 1;
                      @endphp
                      @foreach ( $states as $state )
                          <tr>
                              <td><strong> {{ $state['state']['name'] }}</strong></td>
                              <td style="width:120px;"><input class="all-per main-per-{{ $state['state']['id'] }}" data-id="per-{{ $state['state']['id'] }}" type="checkbox"> </td>
                              <td style="width:100px;"><span class="main-permission"><i class="fas fa-angle-right"></i></span></td>
                          </tr>
                          <tr style="display:none">
                              <td class="subTable" colspan="3">
                                  <table class="table sub-datatable-show-all">
                                      <tbody>
                                      @foreach( $state['branch'] as $key => $branchName )
                                          <tr>
                                                <td>{{ $branchName }}</td>
                                                <td style="width:100px;">
                                                    <input type="checkbox" class="per-{{ $state['state']['id'] }} main-per-{{ $state['state']['id'] }}-child" value="{{ $key }}"  name="branch[]"
                                                           style="margin-left: 40px;">
                                                </td>
                                                <td style="width:138px;"> </td>
                                              </tr>
                                      @endforeach
                                      </tbody>
                                  </table>
                              </td>
                          </tr>
                      @endforeach
                      </tbody>
                  </table>
                  <div class="form-group row" id="checkbox-error" style="color:red;float:right;display:none;">
                      Please select minimum one Branch
                  </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </div>
            </div>
        </form>

</div>

    <script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
      $('#application_date').datepicker({
            format:"dd/mm/yyyy",
            todayHighlight: true, 
            autoclose:true,
        })
      
        $('#notice_create').validate({
            rules: {
                document:{
                    required: true,
                    extension: "jpg|jpeg|png|pdf"
                },
                title: {
                    required: true,
                },
                branch: {
                    required: true,
                },
            },
            messages: {
                document:{
                    required: "Please upload image/pdf files.",
                    extension: "Accept only png,jpg, jpeg or pdf files."
                },
                title: {
                    required: "Please enter title for notice.",
                },
                branch: {
                    required: "Please select minimum one branch.",
                }
            },
            errorElement: 'label',
            errorPlacement: function (error, element) {
                error.addClass(' ');
                element.closest('.error-msg').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        // Append/Add new row
        var filenumber = 1;
        var fileNumberCount = 2;
        $('#table').on('click', "#add", function(e) {
            $('#table tbody').append('<tr class="add_row"><td></td><td><input id="document' + filenumber + '" name="document[]" class="left" type="file"/><label ' + 'id="document'+filenumber+'-error" class="error" for="document'+filenumber+'"></label></td><td class="text-center"><button type="button" class="btn btn-danger btn-sm delete" id="" data-id="' + filenumber + '" title="Delete file">Delete file</button> </td><tr>');

            var js = document.createElement("script");

            js.type = "text/javascript";
            js.src =  '{{ env('APP_URL') }}'+'/asset/js/sweetalert.min.js';

            document.body.appendChild(js);

            filenumber++;
            fileNumberCount++;
            return false;
            e.preventDefault();
        });

        // Delete row
        $(document).on('click', ".delete", function(e) {
            let Delete = $(this).closest('tr');
            swal({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                buttons: true,
                showCancelButton: true,
                dangerMode: true,
            },function(isConfirm){
                if (isConfirm) {
                    console.log( Delete.attr('data-id') );
                    Delete.remove();
                    // console.log( $(this).closest('tr').attr('data-id') );
                    e.preventDefault();
                }
            });
        });

        $('#notice_create').submit(function() {
            var count = 0;
            var status = true;
            var extention = ["pdf", "png", "jpg", "jpeg"];
            var inputFields = $('.left');
            console.log("DD", $('.left') );
            $("input[type='file']").each(function(){
                var input = inputFields[count].id;
               console.log( this.files,"Test", this.files.length, input )
                var lableId = '#'+input + '-error';
                console.log( lableId, "RRRR" );
                //if ( count > 0) {
                    if (this.files.length == 0) {
                        $(lableId).html('');
                        $(lableId).append('This field is required');
                        $(lableId).attr('style', 'display:block;')
                        status = false;
                    } else {
                        var fileName = this.files[0].name;
                        var fileDetail = fileName.split('.');
                        console.log(lableId, "RRRR", fileDetail, fileDetail[fileDetail.length - 1], typeof(fileDetail[fileDetail.length - 1]) );
                        if ( !extention.includes(fileDetail[fileDetail.length - 1]) ) {
                            console.log(this.files[0].name, "III", typeof(fileDetail[fileDetail.length - 1]));
                            $(lableId).html('');
                            $(lableId).append('Please enter a value with a valid extension.');
                            $(lableId).attr('style', 'display:block;')
                            status = false;
                        }
                        console.log(this.files[0].name);
                    }
               // }
                count++;
            });

            if ( $('input:checked', this).length > 0 ) {
                $('#checkbox-error').hide();
            } else {
                $('#checkbox-error').show();
                //swal("Warning!", "Please select minimum one Branch", "warning");
                return false;
            }
            return status;
        });

        $(document).on('change','#document',function(){
            console.log("TTT", this.files);
            if (this.files ) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#photo-preview').attr('src', e.target.result);
                    $('#photo-preview').attr('style', 'width:200px; height:200px;');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        $('.main-permission').on('click', function () {
            $(this).toggleClass('open');
            if ($(this).parent().parent().next('tr').attr('style')) {
                $(this).parent().parent().next('tr').removeAttr('style');
            } else if (typeof ($(this).parent().parent().next('tr').attr('style')) == 'undefined') {
                $(this).parent().parent().next('tr').attr('style', 'display:none');
            }
        });

        $('.all-per').on('click', function () {
            var className = $(this).attr('data-id');
            if ($(this).is(':checked')) {
                $("input:checkbox." + className).prop('checked', this.checked);
                $('#checkbox-error').hide();
            } else {
                $("input:checkbox." + className).prop('checked', false);
            }

        });
        $("input[type='checkbox']").on('click', function () {

            var className = $(this).attr("class");
            var classArray = className.split(" ");
            var classIndex = classArray[0].split('-');
            var upDateClass = 'main-per-' + classIndex[1];

            $("input:checkbox." + upDateClass).prop('checked', false);
            $('#checkbox-error').hide();
        });
    });

</script>
<style rel="stylesheet">
    #table td { padding:0.75rem 0 0.75rem 1.25rem;}
    .subTable .table th, .subTable .table td { padding:0.75rem 0 0.75rem 1.25rem;}

    .main-permission.open { transform: rotate(90deg); display: inline-block; }
    .main-permission{cursor: pointer;
        padding: 2px;
        display: inline-block;
        background: #3E3A82;
        border-radius: 100%;
        width: 25px;
        height: 25px;
        line-height: normal;
        text-align: center;
        font-size: 18px;
        color: #fff;}
</style>
@stop