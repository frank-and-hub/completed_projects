@extends('templates.admin.master')

@section('content')
<style type="text/css">
    @media print {
            .head {
       background: #333333 !important;
    }
            }
    

/* common */
.ribbon {
  width: 150px;
  height: 150px;
  overflow: hidden;
  position: absolute;
}
.ribbon::before,
.ribbon::after {
  position: absolute;
  z-index: -1;
  content: '';
  display: block;
  border: 5px solid #2980b9;
}
.ribbon span {
  position: absolute;
  display: block;
  width: 225px;
  padding: 15px 0;
  background-color: #3498db;
  box-shadow: 0 5px 10px rgba(0,0,0,.1);
  color: #fff;
  font: 700 18px/1 'Lato', sans-serif;
  text-shadow: 0 1px 1px rgba(0,0,0,.2);
  text-transform: uppercase;
  text-align: center;
}

/* top left*/
.ribbon-top-left {
  top: -10px;
  left: -10px;
}
.ribbon-top-left::before{
  border-top-color: transparent;
  border-left-color: transparent;
}
.ribbon-top-left::before {
  top: 0;
  right: 0;
}
.ribbon-top-left::after {
  bottom: 0;
  left: 0;
}
.ribbon-top-left span {
  right: -25px;
  top: 30px;
  transform: rotate(-45deg);
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
             
              </div>
            </div>
          </div>
        </div>
       <div class="col-lg-12" >
          <div class="card bg-white" > 
            <div class="card-body ">
               <!--  <div class="box"> 
                    <div class="ribbon ribbon-top-left"><span>ribbon</span></div>       
                </div> -->
              <div class="row"  id="detail">
                <div class="d-flex justify-content-between" style="width:100%;">
                    <label class="card-title col-md-6 "><span class="font-weight-bold">Farista Export</span><br>Rajasthan 
                       <br/> India</label>
                    <h1></h1>
                    <div style="padding:5px 10px 5px 0px;font-size:10pt;" class="text-right">
                        <h1 class="card-title display-4" >BILL</h1>
                        <label class="font-weight-bold">Bill# 5456</label><br>
                         <label class="font-weight-normal mt-2">Balance Due</label><br>
                          <label class="font-weight-bold" style="font-size: 25px;">Rs.30000</label>
                    </div>

                </div>
                 
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
                  <table style="width:100%;margin-top:80px;table-layout:fixed;"  cellspacing="0" cellpadding="0" class="table detailtable">
                      <thead class="thead-dark head" id="">
                          <tr >
                              <th style="word-wrap: break-word;width:10%;" class="">
                                   #
                              </th>
                              <th style="word-wrap: break-word;width:40%;" class="">
                                   Item & Description
                              </th>
                              <th style="word-wrap: break-word;width:10%;" class="">
                                   Qty
                              </th>
                              <th style="word-wrap: break-word;width:10%;" class="">
                                   Rate
                              </th>
                              <th style="word-wrap: break-word;width:10%;" class="">
                                   Amount
                              </th>
                             

                              
                          </tr>
                      </thead>
                      <tbody>
                              <tr style="border-bottom:1px solid #ededed" class="">
                                      <td   class="">
                                        1
                                          <p style="color: grey;"> </p>
                                          <p style="margin-bottom: 0px;color: grey;"></p>
                                      </td>
                                      <td   class="">
                                      </td>
                                      <td   class="">
                                      </td>
                                      <td   class="">
                                      </td>
                                      <td  class="">
                                      </td>
                                    
                              </tr>
                              <tr style="border-bottom:1px solid #ededed" class="breakrow-inside breakrow-after">

                                      <td  class="">
                                        2
                                          <p style="color: grey;"> </p>
                                          <p style="margin-bottom: 0px;color: grey;"></p>
                                      </td>
                                      <td  class="">
                                          Kavita Baberwal
                                      </td>
                                      <td  class=" ">
                                          50,000.00
                                      </td>
                                      <td  class=" text-right">
                                          50,000.00
                                      </td>
                                       <td  class=" text-right">
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
                                    <td style="width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="width: 120px;" class=" text-right">
                                        
                                      </td>
                                      <td style="width: 120px;" class=" text-right">
                                        
                                      </td>
                                    <td class=" text-right"style="width: 120px;">Sub Total</td>
                                    
                                    <td class=" text-right"style="">50,000.00</td>
                                </tr>
                                    <tr style="height:40px;">
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                        <td class="text-right" style="" ><b>Total</b></td>
                                       
                                        <td class=" text-right"style=""><b>Rs.50,000.00</b></td>
                                    </tr>
                                     </tr>
                                    <tr style="height:40px;">
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                      <td style="" class=" text-right">
                                        
                                      </td>
                                     
                                        <th class="shadow-lg p-3 mb-5 bg-white rounded" colspan='2' style="" ><b class="d-flex justify-content-between">Balance Due <span class="text-right"> Rs.50,000.00</span></b></th>
                                       
                                     
                                      
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="clear: both;">Authorized Signature _______________________</div>
                </div>
                </div>
              </div> 
            </div>
          </div>
         
        </div>
    </div>
    @include('templates.admin.jv_management.partials.create_script')
@stop