@foreach($subcategories->sortBy('carder')  as $subcategory)
   <tr @if($subcategory->member->is_block==0) @if($subcategory->member->associate_status==1) class='child_active' @else class='child_inactive'  @endif @else class='child_inactive'  @endif > 
                                    
                                    <td>{{ $subcategory->member->member_id  }}</td>
                                    <td>{{ $subcategory->member->associate_no  }}</td>
                                    <td>{{ $subcategory->member->first_name }} {{ $subcategory->member->last_name }}</td>
                                    <td>{{ getCarderName($subcategory->member->current_carder_id) }}</td> 
                                    <td>{{ $subcategory->member->associate_senior_code }}</td>
                                    <td>{{ getSeniorData($subcategory->member->associate_senior_id,'first_name') }}  {{ getSeniorData($subcategory->member->associate_senior_id,'last_name') }}</td>
                                    <td>{{ getCarderName(getSeniorData($subcategory->member->associate_senior_id,'current_carder_id')) }}</td>
                                    <td>
                                        @if($subcategory->member->is_block==0)
                                        @if($subcategory->member->associate_status==1)
                                            Active 
                                        @else Inactive 
                                    @endif  @else Blocked 
                                    @endif
                                    </td>
                                    <td> <a href="{!! route('admin.associate.treeview',$subcategory->member->id) !!}"  title="View Tree" target="_blanck"><i class="fas fa-tree"></i></a></td>

                                </tr>
                                @if(count($subcategory->subcategory)>0)
                                    @include('templates.admin.associate.partials.treeviewlisting',['subcategories' => $subcategory->subcategory])
                                @endif
  
@endforeach 