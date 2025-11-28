@extends('templates.admin.master')
@section('css')
<style>
	.table{
		table-layout: auto;
		width: 100%;
		display: block;
		overflow-x: auto;
		white-space: nowrap;
	}
thead th{
    
    vertical-align:top;
	width:100%;
}
th {
    background:#00B0F0;
}
tr + tr th{
    background:#DAEEF3;
	width:100%;

}
tr + tr, tbody {
    text-align:left
}
table, th, td {
    border:solid 1px;
    border-collapse:collapse;
    table-layout:fixed;
}
table.display {
  margin: 0 auto;
  width: 100%;
  clear: both;
  border-collapse: collapse;
  table-layout: fixed;        
  word-wrap:break-word;     
}

        .pagination{
            float: right;
            margin-top: 10px;
        }
</style>
@endsection
@section('content')

<div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
					<form action="{{route('admin.gst_outward_report.export')}}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" name="export_report_extension" id="export_report_extension">

                    </form>  
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">{{$title}}</h6>
						<div class="">
                        	<button type="button" class="btn bg-dark legitRipple export_report ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    	</div>
                    </div>
                    <div class="">
						<table id="" class="table datatable-show-all">
							<thead>
								<tr>
									<th>Name of the receipient</th>
                                    <th>GSTIN/ UIN</th>
                                    <th>State Name </th>
									<th>POS</th>
									<th colspan="6">Invoice Details</th>
                                    <th colspan="2">Quantity</th>
                                    <th colspan="2">IGST</th>
                                    <th colspan="2">CGST</th>
									<th colspan="2">SGST</th>
                                    <th colspan="2">Cess</th>
                                    <th>Indicate if supply attracts reverse charge $</th>
                                    <th>Name of ecommerce operator</th>
                                    <th>GSTIN of ecommerce operator</th>
                                    <th colspan="4">Shipping Bill</th>
                                    <th>Select Receipient Category if different from "Regular"</th>
                                    <th>Item Type</th>
								</tr>
								
								<tr>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th>No.</th>
									<th>Date</th>
									<th>Invoice Value</th>
									<th>HSN/SAC</th>
									<th>Goods/Service description</th>
									<th>Taxable value</th>
									<th>Qty</th>
									<th>Unit</th>
									<th>Rate</th>
									<th>Amt</th>
									<th>Rate</th>
									<th>Amt</th>
									<th>Rate</th>
									<th>Amt</th>
									<th>Rate</th>
									<th>Amt</th>
									<th></th>
									<th></th>
									<th></th>
									<th>Export Type</th>
									<th>No.</th>
									<th>Date</th>
									<th>Part Code</th>
									<th></th>
									<th></th>
									
								</tr>
							</thead>
							<tbody>

							@foreach($data as $value)
								<tr>
								<td>@if(isset($value['memberDetails']->first_name)){{$value['memberDetails']->first_name}}@endif</td>
								<td>@if(isset($value->customer_gst_no)){{$value->customer_gst_no}}@endif</td>
								<td>@if(isset($value['memberDetails']['branch']['branchStatesCustom']->name)){{$value['memberDetails']['branch']['branchStatesCustom']->name}}@endif</td>
								<td>-</td>
								<td>@if(isset($value->invoice_number)){{$value->invoice_number}}@endif</td>
								<td>@if(isset($value->created_at)){{date('d-m-Y',strtotime($value->created_at))}}@endif</td>
								<td>@if(isset($value->total_amount)){{$value->total_amount}}@endif</td>
								<td>-</td>
								<td>-</td>
								<td>{{$value->tax_value}}</td>
								<td>-</td>
								<td>-</td>
								<td>@if(isset($value->amount_of_tax_igst) && $value->amount_of_tax_igst > 0 && isset($value->gstHeadrate)){{$value->gstHeadrate->gst_percentage}}@endif</td>
								<td>@if(isset($value->amount_of_tax_igst)  && $value->amount_of_tax_igst > 0 && isset($value->gstHeadrate)){{$value->amount_of_tax_igst}}@endif</td>
								<td>@if(isset($value->amount_of_tax_cgst)  && $value->amount_of_tax_cgst > 0 && isset($value->gstHeadrate)){{($value->gstHeadrate->gst_percentage)/2}}@endif</td>
								<td>@if(isset($value->amount_of_tax_cgst)  && $value->amount_of_tax_cgst > 0 && isset($value->gstHeadrate)){{$value->amount_of_tax_cgst}}@endif</td>
								<td>@if(isset($value->amount_of_tax_sgst)  && $value->amount_of_tax_sgst > 0 && isset($value->gstHeadrate)){{($value->gstHeadrate->gst_percentage)/2}}@endif</td>
								<td>@if(isset($value->amount_of_tax_sgst)  && $value->amount_of_tax_sgst > 0 && isset($value->gstHeadrate)){{$value->amount_of_tax_sgst}}@endif</td>
								<td-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
						</tr>
							@endforeach
							</tbody>
							
						</table>
					
                       
					</div>
					{!! $data->links() !!}
                </div>
            </div>
        </div>
    </div>
	@include('templates.admin.gstReport.partials.outward_report_script')

@stop