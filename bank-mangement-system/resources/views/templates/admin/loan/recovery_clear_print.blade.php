@extends('templates.admin.master')

@section('content')
<div class="content">
  <div class="row">
    <div class="col-md-12" >
      <div class="card">
       <!-- <div class="card-header header-elements-inline">
          <h4 class="card-title font-weight-semibold  col-lg-12 text-center">Print No Dues</h4>
        </div>-->
        <div class="card-body" id="print_id">
          <div class="id-card" style="max-width:400px; margin:0 auto; padding:12px;"> </div>
          <table id="print_recipt" border = "0" width = "100%" style="border-collapse: collapse;font-size:20px;margin:0px; padding:0px;">
            <!--<tr><td align="center"><img src="{{$data['recovery_clear_logo']}}" style="width:100%"  /><br/><br/><br/></td></tr>-->
            <tr>
              <th align="right"   style="text-align: right; border-collapse: collapse;font-size:12px;">DATE:- {{$data['clear_date']}}<br/>
                <br/>
                <br/>
                <br/></th>
            </tr>
            <tr>
              <th align="center" style="text-align: center;"><u>No Due Certificate</u><br/>
                <br/>
                <br/>
                <br/></th>
            </tr>
            <tr>
              <td style="width:80%">As on dated {{$data['clear_date']}} the said customer has cleared all the dues from our company, Loan Number - <u>{{$data['account_number']}}</u> and customer name is <u>{{$data['name']}} S/O {{$data['father_husband']}}.</u> <br/>
                <br/>
                <br/></td>
            </tr>
            <tr>
              <td style="width:80%">So, please process further for any loan.<br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/></td>
            </tr>
            <tr>
              <td align="right">Authorised Signatory </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-12">
      <div class="card bg-white" >
        <div class="card-body">
          <div class="text-center">
            <button type="submit" class="btn btn-primary avoid-this" onclick="idPrint('print_id');">Print </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@include('templates.admin.associate.partials.print_js')
@stop