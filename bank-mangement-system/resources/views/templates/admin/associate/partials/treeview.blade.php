<ul>@foreach($subcategories->sortBy('carder')  as $subcategory)
   
    <li @if($subcategory->member->is_block==0) @if($subcategory->member->associate_status==1) class='acitve_child_code' @else class='inacitve_child_code' @endif @else class='inacitve_child_code' @endif >
        	<code @if($subcategory->member->is_block==0) @if($subcategory->member->associate_status==1) class='acitve_child_code' @else class='inacitve_child_code' @endif @else class='inacitve_child_code' @endif >
        		{{ $subcategory->member->member_id }}
        		<br>
				{{ $subcategory->member->associate_no }}
				<br>
        		{{ $subcategory->member->first_name }} {{ $subcategory->member->last_name }}
        		<br> 
        		{{ getCarderName($subcategory->member->current_carder_id) }} 
            <br>@if($subcategory->member->is_block==0) @if($subcategory->member->associate_status==1)  Active @else Inactive @endif @else Blocked @endif
        	</code>
        @if(count($subcategory->subcategory))
            @include('templates.admin.associate.partials.treeview',['subcategories' => $subcategory->subcategory])
        @endif
    </li>
   
@endforeach 
</ul>