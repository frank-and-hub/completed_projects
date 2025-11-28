@extends('templates.admin.master')

@section('content')

<div class="content">   
       
      <div class="row">
        
         <div class="col-md-12" id="print_recipt">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h4 class="card-title font-weight-semibold  col-lg-12 text-center">Resign Letter</h4>
                </div>
                <div class="card-body"> 

                  <div class="  row"> 
                    <div class="col-lg-12   ">
                      <strong>Dear {{$employee->employee_name}},</strong>
                    </div>
                    <div class="col-lg-12   ">
                      <p>It is with regret that I acknowledge the receipt of your letter dated [date] resigning your position as [title]. Your resignation has been approved, and, per your request, your final day of work will be [date].</p>
                      <p>It has been a pleasure to work with you, and on behalf of our entire team, I would like to wish you the best in your future endeavors. [You may want to include other information here about the resignation process for your company.]</p>
                      <p>If you have any questions, please do not hesitate to contact me or human resources. Thank you again for your hard work.</p>



                    </div>
                    <div class="col-lg-12   ">
                      Sincerely, 
                      <br>
                      <strong>Samraddh Bestwin Micro Finance</strong>
                    </div>
                  </div>
                    
                </div>
              </div>
            </div>

            <div class="col-lg-12">
              <div class="card bg-white" >            
                <div class="card-body">
                  <div class="text-center">
                  <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print </button></div>
                </div>
              </div>
            </div>


      </div>  
</div>
@include('templates.admin.hr_management.employee.script_letter')
@stop