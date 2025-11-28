@extends('templates.admin.master')

@section('content')
<style type="text/css">
    @media print {
            .head {
       background: #333333 !important;
    }
            }
</style>
<div class="content"> 
      <div class="row">
       
        <div class="col-lg-12">
          <div class="card bg-white" >
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div class="border rounded d-flex shadow  bg-light" style="height:30px;">
                  <div class="border-right px-2 py-1"><i class="fas fa-pen-nib"></i></div>
                 <div class="border-right px-2 py-1"><i class="fas fa-print"onclick="printDiv('detail');" ></i></div>
                 <div class="border-right px-2 py-1"><a href="{{url('admin/export/jv/detail')}}"> <i class="fas fa-file-pdf"></i></a></div>
                </div>
              </div>
             <div id="comments" class=""> 
                <div class="">
                  <div class=""> 
                    <ul class="list-unstyled"> 
                      <li id="" style="width: 50%;">
                        <div class="d-flex justify-content-between mt-3"> <div class="date-section float-left">
                          <div class="font-xxs text-draft font-weight-light"> 13/08/2021 05:26 PM <br> 
                          </div>
                        </div> 
                        <div class="d-flex">
                            <div class="border rounded-circle txn-comment-icon"> 
                              <i class="far fa-edit m-1" style="color:#f0b11a;"></i>  
                          </div> 
                          <div class="media-body mx-3">
                              <div class="comment">
                                <span class="description">Journal created for Rs.50,000.00</span>
                                <label class="font-xs text-muted">by <strong>Kavita Baberwal</strong></label>
                              </div>
                            </div>
                      </div>

                      </li>
                  <li id="" style="width: 50%;">
                        <div class="d-flex justify-content-between mt-3"> <div class="date-section float-left">
                          <div class="font-xxs text-draft font-weight-light"> 13/08/2021 05:26 PM <br> 
                          </div>
                        </div> 
                        <div class="d-flex">
                            <div class="border rounded-circle txn-comment-icon"> 
                              <i class="far fa-edit m-1" style="color:#f0b11a;"></i>  
                          </div> 
                          <div class="media-body mx-3">
                              <div class="comment">
                                <span class="description">Journal created for Rs.50,000.00</span>
                                <label class="font-xs text-muted">by <strong>Kavita Baberwal</strong></label>
                              </div>
                            </div>
                      </div>
                      </li>
                      <li id="" style="width: 50%;">
                            <div class="d-flex justify-content-between mt-3"> <div class="date-section float-left">
                              <div class="font-xxs text-draft font-weight-light"> 13/08/2021 05:26 PM <br> 
                              </div>
                            </div> 
                            <div class="d-flex">
                                <div class="border rounded-circle txn-comment-icon"> 
                                  <i class="far fa-edit m-1" style="color:#f0b11a;"></i>  
                              </div> 
                              <div class="media-body mx-3">
                                  <div class="comment">
                                    <span class="description">Journal created for Rs.50,000.00</span>
                                    <label class="font-xs text-muted">by <strong>Kavita Baberwal</strong></label>
                                  </div>
                                </div>
                          </div>
                      </li>
                  </ul>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
       <div class="col-lg-12" >
          <div class="card bg-white" > 
            <div class="card-body ">
              
              <div class="row"  id="detail">
                 <h1 class="card-title mx-3" style="text-align: right;width:100%;">JOURNAL</h1>
                <div class="col-lg-12">
                 <table style="width:100%;margin-top:30px;margin-bottom:80px;">
                    <tbody>
                      <tr>
                        <td style="width:60%;vertical-align:bottom;word-wrap: break-word;">
                                <div style="padding-bottom: 5px;">
                                    <label style="font-size: 10pt;" id="notes_label" class="">Notes</label>
                                    <br>
                                    <span style="white-space: pre-wrap;" id="notes">hbvhbvnv</span>
                                </div>
                        </td>

                        <td align="right" style="vertical-align:bottom;width: 40%;">
                            <table style="float:right;width: 100%;table-layout: fixed;word-wrap: break-word;" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                        <tr>
                                            <td style="padding:5px 10px 5px 0px;font-size:10pt;" class="text-right">
                                                <span class="">Date:</span>
                                            </td>
                                            <td class="text-right">
                                                <span id="">13/08/2021</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:5px 10px 5px 0px;font-size:10pt;" class="text-right">
                                                <span class="">Amount:</span>
                                            </td>
                                            <td class="text-right">
                                                <span id="">Rs.50,000.00</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:5px 10px 5px 0px;font-size: 10pt;" class="text-right">
                                                <span class="">Reference Number:</span>
                                            </td>
                                            <td class="text-right">
                                                <span id=""></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:5px 10px 5px 0px;font-size: 10pt;" class="text-right">
                                                <span class="">Branch:</span>
                                            </td>
                                            <td class="text-right">
                                                <span id="">Jaipur</span>
                                            </td>
                                        </tr>
                                </tbody>
                            </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <table style="width:100%;margin-top:80px;table-layout:fixed;" border="1" cellspacing="0" cellpadding="0" class="table detailtable">
                      <thead class="thead-dark head" id="">
                          <tr style="height:40px;">
                              <th style="word-wrap: break-word;" class="">
                                   Account Head 1
                              </th>
                              <th style="word-wrap: break-word;" class="">
                                   Account Head 2
                              </th>
                              <th style="word-wrap: break-word;" class="">
                                   Account Head 3
                              </th>
                              <th style="word-wrap: break-word;" class="">
                                   Account Head 4
                              </th>
                              <th style="word-wrap: break-word;" class="">
                                   Account Head 5
                              </th>
                              <th style="word-wrap: break-word;" class="">
                                  Contact
                              </th>
                              <th style="word-wrap: break-word;" class="">
                                  Description
                              </th>

                              <th align="right" style="word-wrap: break-word;" class=" ">
                                  Debits
                              </th>
                              <th align="right" style="word-wrap: break-word;" class="">
                                  Credits
                              </th>
                          </tr>
                      </thead>
                      <tbody>
                              <tr style="border-bottom:1px solid #ededed" class="breakrow-inside breakrow-after">
                                      <td  style="padding: 10px 0px 10px 10px;" class="">
                                         Petty Cash
                                          <p style="color: grey;"> </p>
                                          <p style="margin-bottom: 0px;color: grey;"></p>
                                      </td>
                                      <td  style="padding: 10px 10px 5px 5px;word-wrap: break-word;" class="">
                                      </td>
                                      <td  style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class="">
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                              </tr>
                              <tr style="border-bottom:1px solid #ededed" class="breakrow-inside breakrow-after">

                                      <td  style="padding: 10px 0px 10px 10px;" class="">
                                         Employee Advance
                                          <p style="color: grey;"> </p>
                                          <p style="margin-bottom: 0px;color: grey;"></p>
                                      </td>
                                      <td  style="padding: 10px 10px 5px 5px;word-wrap: break-word;" class="">
                                          Kavita Baberwal
                                      </td>
                                      <td  style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" ">
                                          50,000.00
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                                       <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                          50,000.00
                                      </td>
                              </tr>
                      </tbody>
                  </table>
                  <div style="width: 100%;margin-top: 1px;">
                    <div class="">
                        <table class="" cellspacing="0" border="0" width="100%">
                            <tbody>
                                <tr>
                                    <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;width: 120px;" class=" text-right">
                                        
                                      </td>
                                    <td class=" text-right"style="padding: 10px 10px 10px 5px;word-wrap: break-word;width: 120px;">Sub Total</td>
                                    <td class=" text-right"style="padding: 10px 10px 10px 5px;word-wrap: break-word;width: 120px;">50,000.00</td>
                                    <td class=" text-right"style="padding: 10px 10px 10px 5px;word-wrap: break-word;">50,000.00</td>
                                </tr>
                                    <tr style="height:40px;">
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                        
                                      </td>
                                      <td style="padding: 10px 10px 10px 5px;word-wrap: break-word;" class=" text-right">
                                        
                                      </td>
                                        <td class="text-right" style="padding: 10px 10px 10px 5px;word-wrap: break-word;" ><b>Total</b></td>
                                        <td class=" text-right"style="padding: 10px 10px 10px 5px;word-wrap: break-word;"><b>Rs.5s0,000.00</b></td>
                                        <td class=" text-right"style="padding: 10px 10px 10px 5px;word-wrap: break-word;"><b>Rs.50,000.00</b></td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="clear: both;"></div>
                </div>
                </div>
              </div> 
            </div>
          </div>
         
        </div>
    </div>
    @include('templates.admin.jv_management.partials.create_script')
@stop