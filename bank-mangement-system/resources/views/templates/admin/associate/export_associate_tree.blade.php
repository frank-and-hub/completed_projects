<h3 class="mb-0 text-dark">Associate Tree - {{ $associate->associate_no }}({{ $associate->first_name }} {{ $associate->last_name }}) </h3>

<table  border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;" class="table ">
                            <thead>
                                <tr>     
                                    <th>Customer ID</th>                               
                                    <th>Associate Code</th>
                                    <th>Associate Name</th> 
                                    <th>Associate Carder </th>
                                    <th>Senior Code</th>
                                    <th>Senior Name</th> 
                                    <th>Senior Carder </th>
                                    <th>Status </th> 
                                </tr>
                            </thead> 
                            <tbody>
                                <tr class="parent-associate">
                                    
                                    <td>{{ $associate->member_id }}</td>
                                    <td>{{ $associate->associate_no }}</td>
                                    <td>{{ $associate->first_name }} {{ $associate->last_name }}</td>
                                    <td>{{ getCarderName($associate->current_carder_id) }}</td> 
                                    <td>{{ $associate->associate_senior_code }}</td>
                                    <td>{{ getSeniorData($associate->associate_senior_id,'first_name') }}  {{ getSeniorData($associate->associate_senior_id,'last_name') }}</td>
                                    <td>{{ getCarderName(getSeniorData($associate->associate_senior_id,'current_carder_id')) }}</td>
                                    <td>@if($associate->is_block==0)@if($associate->associate_status==1) Active @else Inactive @endif @else Blocked @endif</td> 

                                </tr>
                        @for ($i = 1; $i <= $associate->current_carder_id; $i++)
                            <?php  $arrayCategories = array();
                                $data=associateTree($associate->id,$i) 
                            ?>
                            @if(count($data)>0)
                            @foreach($data as $taxonomy)

                                    <tr @if($taxonomy->member->associate_status==1) class='child_active' @else class='child_inactive'  @endif > 
                                    <td>{{ $taxonomy->member->member_id  }}</td>
                                    <td>{{ $taxonomy->member->associate_no  }}</td>
                                    <td>{{ $taxonomy->member->first_name }} {{ $taxonomy->last_name }}</td>
                                    <td>{{ getCarderName($taxonomy->member->current_carder_id) }}</td> 
                                    <td>{{ $taxonomy->member->associate_senior_code }}</td>
                                    <td>{{ getSeniorData($taxonomy->member->associate_senior_id,'first_name') }}  {{ getSeniorData($taxonomy->member->associate_senior_id,'last_name') }}</td>
                                    <td>{{ getCarderName(getSeniorData($taxonomy->member->associate_senior_id,'current_carder_id')) }}</td>
                                    <td>

                                    @if($taxonomy->member->is_block==0)
                                        @if($taxonomy->member->associate_status==1)
                                            Active 
                                        @else Inactive 
                                    @endif  @else Blocked 
                                    @endif

                                    </td> 

                                </tr>
                                @if(count($taxonomy->subcategory)>0)
                                    @include('templates.admin.associate.partials.treeviewexport',['subcategories' => $taxonomy->subcategory])
                                @endif
                                
                            @endforeach
                            @endif
                        @endfor
                            </tbody>                   
                        </table>
