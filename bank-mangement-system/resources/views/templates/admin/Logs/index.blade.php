@extends('templates.admin.master')
@section('content')
<!-- Modal -->
<style>
   .custom-modal {
   max-width: 600px;
   margin: 0 auto;
   }
   .modal-content {
   border: 2px solid #000;
   border-radius: 10px;
   }
   .modal-body {
   display: flex;
   justify-content: space-between;
   align-items: center;
   }
   .border-value{
   /* border: 2px solid #000; */
   display: flex;
   justify-content: space-between;
   align-items: center;
   }
   .border{
    width:121%;
   }
   .custom-box {
   /* border: 2px solid #000; */
   width: 250px;
   padding: 15px;
   border-radius: 8px;
   margin-right: 20px;
   box-sizing: border-box;
   }
   .custom-input-group {
   margin-top: 7px;
   display: flex;
   align-items: center;
   flex-wrap: nowrap;
   white-space: nowrap;
   width: 50%; /* Adjust the width as needed */
   margin-left: -28px;
   margin-bottom: 5px;
   }
   .custom-input-group span {
   min-width: 80px;
   margin-right: 7px;
   text-align: right;
   }
   .form-control {
   width: calc(100% - 90px);
   font-size: 14px;
   }
   .custom-box h6 {
   position: relative;
   }
   .custom-box h6::after {
   content: "";
   position: absolute;
   bottom: 0;
   left: 0;
   /* width: 100%; */
   height: 2px;
   background-color: #000;
   margin-bottom: -5px ;
   }
</style>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog custom-modal" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">EDIT LOG</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <!-- First Div -->
            <div class="border-value">
               <div class="custom-box">
                  <h6 class="font-weight-bold">Old Values</h6>
                  <div class="old-values">

                  </div>
                  <!-- Content for the first div -->
                  
               </div>
               <!-- Second Div -->
               <div class="custom-box">
                  <h6 class="font-weight-bold" >New Values</h6>
                  <!-- Content for the second div -->
                  <div class="new-values">
                     
                  </div>
                  
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>
<!-- model end -->
<?php
$category = [
   1=>'Personal Loan',
   2=>'Staff Loan',
   3=>'Group Loan',
   4=>'Loan Against Investment',
];
$status = [
   1=>'Approved',
   0=>'Pending',
   6=>'Hold',
   5=>'Rejected',
   8=>'Cancle',
   7=>'Approved hold',
   4=>'Loan Transfer',
   98=>'Loan Create',
   99=>'Edit Loan Application',
   3=>'Loan Closed',
   9=>'Ecs status change',
   10=>'transfer entry delete',
   11=>'emi entry delete',   
   12=>'ECS registration',
   13=>'Transfer Edit',
   14=>'Transfer Entry Delete',
   15=>'Transfer Entry Date Change',

];
?>
<div class="content">
   <div class="row">
      <div class="col-md-12">
         <div class="card">
            <div class="card-header header-elements-inline">
               <h6 class="card-title font-weight-semibold">{{$headTitle}}</h6>
            </div>
            <div class="">
               <table id="loan_logs" class="table datatable-show-all">
                  <thead>
                     <tr>
                        @foreach($columnName as $value)
                        <th>{{$value}}</th>
                        @endforeach
                     </tr>
                  </thead>  
                  <tbody>
                     @forelse($record as $key =>  $value)
                     <?php
                     $t = getLoanData($value->loan_type)->loan_category;
                     
                     ?>
                     <tr>
                        <td>{{$key + 1 }}</td>
                        <td>{{$category[$t]??''}}</td>
                        <td>{{$value->loan_name}}</td>
                        <td>{{$status[$value->status]??''}}</td>
                        <td>{{$value->description}}</td>
                        <td>{{$value->created_by_name ?? 'Admin'}}</td>
                        <td>{{$value->user_name}}</td>
                        <td>{{date('d/m/Y H:i:s',strtotime($value->created_at))}}</td>
                        <td>
                           @if($value->status=='99')
                           <button class="fas fa-eye text-default mr-2 logs" id="editLog"  data-id={{$value->id}}></button>
                           @else
                           N/A
                           @endif
                        </td>                        
                     </tr>
                     @empty
                     <tr>
                        <td>No Record Found</td>
                     </tr>
                     @endforelse
                  </tbody>                                    
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
    $(document).ready(function(){
    var loan_logs = $('#loan_logs').dataTable();
        $(document).on('click', '.logs', function () {
         $('.new-values').html('');
         $('.old-values').html('');
            var id = $(this).data('id');
            console.log(id);
            
            $.ajax({
                type: "GET",
                url: '{{ route("getLoanlogs") }}',
                data: {'id': id},
                success: function (response) {
                    $('#exampleModal').modal('toggle');

                    var record = response[0].new_val;
                    var newR=JSON.parse(record);
                    var keys = Object.keys(newR);
                    var values = Object.values(newR);
                    for (var i = 0; i < keys.length; i++) {
                        var html = '<div class="border">' +
                            '<div class="custom-input-group">' +
                            '<span class="font-weight-bold">Name:</span>' +
                            '<span>'+ keys[i] +'</span>' +
                            '</div>' +
                            '<div class="custom-input-group">' +
                            '<span class="font-weight-bold">Value:</span>' +
                            '<span>' + values[i] + '</span>' +
                            '</div>' +
                            '</div>';
                        
                        // Append the generated HTML to the modal
                        $('.new-values').append(html);
                    }

                    var oldrecord = response[0].old_val;
                    var oldR=JSON.parse(oldrecord);
                    var oldkeys = Object.keys(oldR);
                    var oldvalues = Object.values(oldR);
                    for (var i = 0; i < oldkeys.length; i++) {
                        var html = '<div class="border">' +
                            '<div class="custom-input-group">' +
                            '<span class="font-weight-bold">Name:</span>' +
                            '<span>'+ oldkeys[i] +'</span>' +
                            '</div>' +
                            '<div class="custom-input-group">' +
                            '<span class="font-weight-bold">Value:</span>' +
                            '<span>' + oldvalues[i] + '</span>' +
                            '</div>' +
                            '</div>';
                        
                        // Append the generated HTML to the modal
                        $('.old-values').append(html);
                    }
                }
            });
        });

    });
</script>
@stop