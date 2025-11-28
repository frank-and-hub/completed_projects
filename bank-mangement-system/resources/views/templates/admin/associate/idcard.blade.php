@extends('templates.admin.master')

@section('content')
<div class="content"> 
    <div class="row">  
    <div class="col-lg-12" > 
          @if (session('success')) 
          <div class="alert alert-success alert-dismissible fade show" role="alert">
              
              <span class="alert-text"><strong>Success!</strong> {{ session('success') }} </span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          @endif
        </div> 
        <div class="col-md-12" >
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h4 class="card-title font-weight-semibold  col-lg-12 text-center">Id Card</h4>
                </div>
                <div class="card-body" id="print_id"> 
                
                
                
                
              
               <div class="id-card" style="max-width:400px; margin:0 auto; padding:12px;">

                 <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-family:Arial, Helvetica, sans-serif;">
                 
                  <tr>
                   <td valign="top" width="90"><img src="{{url('/')}}/asset/images/{{$logo->small_logo}}" width="85" alt=""></td>
                   <td valign="top" style="padding:0 0 15px 0;">
                    <h3 style="font-weight:bold; font-size:17px; text-align:center; margin:0px; padding:0px 0 4px 0; text-align:left; border-bottom:#4a6399 solid 2px; color:#4a6399;">SAMRADDH Bestwin</h3>   
                    <p style="margin:0px; font-size:12px; color:#4a6399; ">{{$setting->address}}</p>
                    <p style="margin:0px; font-size:12px; color:#4a6399; ">Call Us:- {{$setting->mobile}}</p>
                    <p style="margin:0px; font-size:12px; color:#4a6399;">Mail Us:- {{$setting->email}}</p>
                    <p style="margin:0px; font-size:12px; color:#4a6399; ">Visit Us:- {{url('/')}} </p>
                   </td>
                  </tr>
                  <tr>
                    <td valign="top" class="card-photo" style="width:95px;">
                      @if($memberData->photo=='')
                        <img style="border:#000 solid 1px; border-radius:10px; width:82px;" src="{{url('/')}}/asset/images/user.png" alt="">
                      @else
                         <img style="border:#000 solid 1px; border-radius:10px; width:82px;"src="{{ImageUpload::generatePreSignedUrl('profile/member_avatar/'.$memberData->photo)}}">
                      @endif
                      </td>
                    <td valign="top">
                     <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:14px; line-height:12px;">
                      <tr>
                        <td style="padding:0 0 6px 0;">Employee code</td>
                        <td><span style="padding:0 6px 0 0;">:</span> {{$memberData->associate_no}}</td>
                      </tr>
                      <tr>
                        <td style="padding:0 0 6px 0;">Name</td>
                        <td><span style="padding:0 6px 0 0;">:</span> {{$memberData->first_name}} {{$memberData->last_name}}</td>
                      </tr>
                      <tr>
                        <td style="padding:0 0 6px 0;">Date of Joining</td>

                        <td><span style="padding:0 6px 0 0;">:</span>@if ($memberData->associate_join_date) {{ date("d/m/Y", strtotime
                        ($memberData->associate_join_date)) }} @else NA @endif</td>
                      </tr>
                      <tr>
                        <td style="padding:0 0 6px 0;">Blood Group</td>
                        <td><span style="padding:0 6px 0 0;">:</span> NA</td>
                      </tr>
                      <tr>
                        <td style="padding:0 0 6px 0;">Department</td>
                        <td><span style="padding:0 6px 0 0;">:</span> {{ getCarderNameFull($memberData->current_carder_id) }} </td>
                      </tr>
                      <tr>
                        <td style="padding:0 0 6px 0;">Contact No.</td>
                        <td><span style="padding:0 6px 0 0;">:</span> {{ $memberData->mobile_no }} </td>
                      </tr>
                      <tr>
                        <td style="padding:0 0 6px 0;">Designation</td>
                        <td><span style="padding:0 6px 0 0;">:</span> NA</td>
                      </tr>
                      <tr>
                        <td style="padding:0 0 6px 0;">Instructions</td>
                        <td><span style="padding:0 6px 0 0;">:</span> NA</td>
                      </tr>
                    </table>
                   </td>
                  </tr>
                  <tr>
                   <td valign="top"><h4 style="font-weight:normal; font-size:14px; text-align:center; margin:0px; padding:10px 0 0 0;">Signature</h4></td>
                   <td><h4 style="font-weight:normal; font-size:14px; text-align:center; margin:0px; padding:10px 0 0 0; text-align:right;">Authorized Person</h4></td>
                  </tr>  
                  
                </table>
                
                
                </div>
                
                
                
                
                

                </div>
            </div>
             

             

        </div>
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
              <div class="text-center">
              <button type="submit" class="btn btn-primary avoid-this" onclick="idPrint('print_id');">Print </button></div>
            </div>
          </div>
        </div>
         
    </div>
</div>
@include('templates.admin.associate.partials.print_js')
@stop