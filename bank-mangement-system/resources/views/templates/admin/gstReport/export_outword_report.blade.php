<table >
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
							
							@foreach($data as  $value)
							<tr>
								<td>{{$value['name_of_recipient']}}</td>
								<td>-</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>-</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>-</td>
								<td>-</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>-</td>
								<td>-</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>{{$value['name_of_recipient']}}</td>
								<td>{{$value['name_of_recipient']}}</td>
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
								<td>-</td>
							</tr>	
							@endforeach
							</tbody>
							
						</table>