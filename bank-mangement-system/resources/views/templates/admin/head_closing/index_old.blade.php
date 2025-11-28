@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12"> 
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">@if($type==1) Head Closing List @else  Add Head Closing  @endif </h6>
                    </div> 
                    <div class="card-body">
                        {{Form::open(['url'=>'#','method'=>'POST','enctype'=>'multipart/form-data','id'=>'getHeadList','name'=>'getHeadList'])}}
                            <div class="row"> 
                                <div class="col-md-6">
                                    <div class="form-group row">
                                      <label class="col-form-label col-lg-6">Financial Year </label>
                                      <div class="col-lg-6 error-msg">
                                        <select class="form-control" id="financial_year" name="financial_year">
                                          <option value="">Select Financial Year </option>
                                          @foreach( getFinancialYear() as $key => $value )
                                            <option value="{{ $value }}"  >{{ $value }} </option>
                                          @endforeach
                                        </select>
                                        {{Form::hidden('type_page',$type,['id'=>'type_page','class'=>''])}}
                                      </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">  
                                        <div class="col-lg-12 text-right" > 
                                            <button type="button" class=" btn bg-dark legitRipple" id="formgethead"  >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>


            <div class="col-md-12" id="head_closing_value_show"> 
                
            </div>

        </div>

    </div>

@stop

@section('script')
<script type="text/javascript">
    $(document).ready(function(){
        
    $(document).on('keyup','.aa',function(event){
        
        if( $.isNumeric($(this).val()) == true){
            $(this).next('.pl-2').html("");
        }else{
            $(this).next('.pl-2').html("Please Enter Only Number");
        }
        
        $('.aa').each(function() {

            if($(this).val()){
                   
                if($(this).val().split('-')[0] == ""  ){
                    $(this).css("border", "2px solid red");
                }else{
                    if($(this).val() != "0.00" && $.isNumeric($(this).val()) == true){
                        $(this).css("border", "2px solid  green");
                    }else{
                        $(this).removeAttr("style");
                    }
                    
                }
                if( $.isNumeric($(this).val()) == true){
                    $(this).next('.pl-2').html("");
                }
                
            }else{
                $(this).removeAttr("style");
                $(this).next('.pl-2').html("Please Enter Value");
            }
        });

    })
    $(document).on('click','#myformsubmit',function(e){
        
        e.preventDefault();
        var myarray = [];
        $('.aa').each(function() {
            if(!$(this).val()){
                $(this).next('.pl-2').html("Please Enter Value");
                 myarray = "error";
                 
            }else if( $.isNumeric($(this).val()) == false){
                $(this).next('.pl-2').html("");
                $(this).next('.pl-2').html("Please Enter Only Number");
                myarray = "error";
            }
        });
        
        if(myarray == '' && myarray != "error"){
            var dropdwn = $("#financial_year").val();
            //var myform = $("#myform").serialize();
            $.ajax({
                    type: "POST",  
                    url: "{!! route('admin.closing_head.save') !!}",
                    data: $("#myform").serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) { 
                        $("#financial_year").val('');                        
                        if(response.msg_type=="success")
                        {
                            
                            
                            $('#head_closing_value_show').html('<div class="alert alert-success alert-block"><strong>Amount successfully added </strong></div>');  
                            $('html, body').animate({
                            scrollTop: $("#head_closing_value_show").offset().top 
                            }, 2000);
                        
                        }
                        else
                        {                     
                        
                            $('#head_closing_value_show').html('<div class="alert alert-danger alert-block">  <strong>'+response.vew+' </strong></div>');
                        
                        }

                    }
            });
        }
            
           
        })

    
    })
    function resetForm(){
            $('#getHeadList')[0].reset();
            $("#head_closing_value_show").html('');
        }
    function myFormreset(){
        $('#myform')[0].reset();
        $('.aa').each(function() {
            $(this).val('0.00');
            $(this).removeAttr("style")
        });
        
        $('html, body').animate({
        scrollTop: $("#head_closing_value_show").offset().top }, 2000);

        
    }

    function resetFinanceHead() {
      var financial_year = $('#financial_year').val();
      if ( financial_year != '') {
        $.ajax({
         type: "POST",  
          url: "{!! route('admin.reset-closing_head') !!}",
          dataType: 'JSON',
          data: {'financial_year':financial_year},
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {      				
            
            if(response.msg_type=="success")
            {
              $('#head_closing_value_show').html('<div class="alert alert-success alert-block">  <strong>Head closing amount removed successfully.</strong> </div>'); 
            }
            else if(response.msg_type=="error")
            {
              $('#head_closing_value_show').html('<div class="alert alert-danger alert-block">  <strong>Something went worng!</strong> </div>');  
            }
          }, 
          error: function() {
            $('#head_closing_value_show').html('<div class="alert alert-danger alert-block">  <strong>Something went worng!</strong> </div>');
          }
        });
      } else {
        $('#head_closing_value_show').html('<div class="alert alert-danger alert-block">  <strong>Plese select financial year!</strong> </div>');
      }
      $('html, body').animate({
        scrollTop: $("#head_closing_value_show").offset().top }, 2000);
    }
</script>
    @include('templates.admin.head_closing.partials.script')

@stop