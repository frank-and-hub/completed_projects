










					<table class="table  datatable-show-all">
					<thead>


									 <tr>

									 <th>{{$data['headDetail']->sub_head}}</th>
									<th>{{ number_format((float)headTotalNew($data['headDetail']->head_id,$data['date'],$data['to_date'],$data['branch_id']), 2, '.', '')}}</th>

									 </tr>
								 </thead>


						@if(count($data['childHead'])>0)

		                    @foreach ($data['childHead'] as $val1)

		                    <?php  $head4= getHead($val1->head_id,4);
		                            $headIDS = array($data['headDetail']->head_id);

		                    	$subHeadsIDS =App\Models\AccountHeads::where('head_id',$data['headDetail']->head_id)->where('status',0)->pluck('head_id')->toArray();

				                if( count($subHeadsIDS) > 0 ){
				                    $headIDS=  array_merge($headIDS,$subHeadsIDS);
				                   $record= get_account_head_ids($headIDS,$subHeadsIDS,true);

				                }

				                 foreach ($record as $key => $value) {
				                $ids[] = $value;
				               }
				               $ID = $ids;

		                    ?>
								<thead>


									<tr>

											{{$val1->sub_head}}
									<th>

                                         </th>
                                         <th>
                                        {{ number_format((float)headTotalNew($val1->head_id,$data['date'],$data['to_date'],$data['branch_id']), 2, '.', '')}}
                                         </th>






									</tr>
								</thead>
								<?php  $head4= getHead($val1->head_id,5);
									$head4Id = array();
					               if($val1->head_id == 92)
					               {
						               	$parentID = array($val1->head_id);
										$subHeadsParentIDS =App\Models\AccountHeads::where('head_id',$val1->head_id)->where('status',0)->pluck('head_id')->toArray();
						                if( count($subHeadsParentIDS) > 0 ){
						                    $parentID=  array_merge($parentID,$subHeadsParentIDS);
						                   $record= get_change_sub_account_head($parentID,$subHeadsParentIDS,true);
						                }
						                foreach ($record as $key => $value) {
						                $head4Id[] = $value;
						               }
						              // dd($head4Id);
					               }
				               		// $Dataid = $head4Id;


								?>

		                        @if(count($head4)>0)

		                          	@foreach ($head4 as $val4)
		                          	<?php  $count= getHead($val4->head_id,4);

		                          	?>
										<tbody>

					                        <tr>

					                          <td> </td>

					                         <th>{{$val4->sub_head}}  </th>



                                         <th>{{ number_format((float)headTotalNew($val4->head_id,$data['date'],$data['to_date'],$data['branch_id']), 2, '.', '')}}

                                         </th>


					                          <td> </td>

					                        </tr>
				                      </tbody>
		                      		@endforeach
		                      		@endif
							@endforeach



							@elseif(count($data['subchildHead'])  > 0)
							@foreach ($data['subchildHead'] as $val2)
							  <?php  $head5= getHead($val2->head_id,5);?>
							  <thead>
							<tr>

									<th>{{$val2->sub_head}}  </th>




                                    <th>{{ number_format((float)headTotalNew($val2->head_id,$data['date'],$data['to_date'],$data['branch_id']), 2, '.', '')}}
									</th>


									</tr>
								</thead>
								<?php  $headnew= getHead($val2->head_id,5);
								?>
		                        @if(count($headnew)>0)
		                          	@foreach ($headnew as $val4)
										<tbody>
					                        <tr>
					                          <td> </td>
					                           <td> {{$val4->sub_head}}</td>

                                         		<td>{{ number_format((float)headTotalNew($val4->head_id,$data['date'],$data['to_date'],$data['branch_id']), 2, '.', '')}}

                                         		</td>

					                          <td> </td>
					                        </tr>
				                      </tbody>
				                    @endforeach
				                @endif
							@endforeach

                		@endif
                		<!-- Bank Charge oTHER hEAD Start -->
						@if($data['headDetail']->head_id == 92)
						<tr>
							<th>Bank Charge Other</th>
							<th></th>

							<th></th>

							<th>{{number_format((float)headTotalNew($data['headDetail']->head_id,$data['date'],$data['to_date'],$data['branch_id']), 2, '.', '')}}</th>
						</tr>
						@endif
						<!-- Bank Charge oTHER hEAD End -->
					</table>
