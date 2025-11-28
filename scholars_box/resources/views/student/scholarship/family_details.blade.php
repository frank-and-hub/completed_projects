<div class="row">
    @if(isset($scholarships[0]->apply_now->principal_guardian_name_h_s) && ($scholarships[0]->apply_now->principal_guardian_name_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Principal Guardian Name{{$scholarships[0]->apply_now->principal_guardian_name_r == '1' ? '*' : '' }}</label>
            <input  type="text"  name="guardian_name"  class="form-input-one"  placeholder=""  value="{{ isset($draft) && $draft->guardian_name != '' ? $draft->guardian_name : '' }}"  {{$scholarships[0]->apply_now->principal_guardian_name_r == '1' ? 'required="true"' : '' }} />
        </div> 
    </div>  
    @endif
    @if(isset($scholarships[0]->apply_now->relationship_with_the_principal_guardian_h_s) && ($scholarships[0]->apply_now->relationship_with_the_principal_guardian_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Relationship with the Principal Guardian{{$scholarships[0]->apply_now->relationship_with_the_principal_guardian_r == '1' ? '*' : '' }}</label>
            <select  name="guardian_relationship"  class="form-input-one"  {{$scholarships[0]->apply_now->relationship_with_the_principal_guardian_r == '1' ? 'required="true"' : '' }} >
                <option value="">Select Relationship with the Principal Guardian</option>
                <option value="Father" {{ (isset($draft) && $draft->guardian_relationship != '' ? $draft->guardian_relationship : '') === 'Father' ? 'selected' : '' }}>Father</option>
                <option value="Mother" {{ (isset($draft) && $draft->guardian_relationship != '' ? $draft->guardian_relationship : '') === 'Mother' ? 'selected' : '' }}>Mother</option>
                <option value="Others" {{ (isset($draft) && $draft->guardian_relationship != '' ? $draft->guardian_relationship : '') === 'Others' ? 'selected' : '' }}>Others</option>
            </select>
        </div>
    </div>
    @endif    
    
    @if(isset($scholarships[0]->apply_now->occupation_of_the_principal_guardian_h_s) && ($scholarships[0]->apply_now->occupation_of_the_principal_guardian_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Occupation of the Principal Guardian{{$scholarships[0]->apply_now->occupation_of_the_principal_guardian_r == '1' ? '*' : '' }}</label>
            <input  type="text" name="guardian_occupation"  class="form-input-one"  value="{{ isset($draft) && $draft->guardian_occupation != '' ? $draft->guardian_occupation : '' }}"  placeholder="" {{$scholarships[0]->apply_now->occupation_of_the_principal_guardian_r == '1' ? 'required="true"' : '' }} />
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->mobile_no_of_the_principal_guardian_h_s) && ($scholarships[0]->apply_now->mobile_no_of_the_principal_guardian_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Mobile No. of the Principal Guardian{{$scholarships[0]->apply_now->mobile_no_of_the_principal_guardian_r == '1' ? '*' : '' }}</label>
            <input  type="text"  name="guardian_phone_number"  class="form-input-one digitsOnly" placeholder="Phone No."  {{$scholarships[0]->apply_now->mobile_no_of_the_principal_guardian_r == '1' ? 'required="true"' : '' }} pattern="[0-9]{10}"  value="{{ isset($draft) && $draft->guardian_phone_number != '' ? $draft->guardian_phone_number : '' }}"  title="Please enter a valid 10-digit phone number" />
            <small>Format: 12345678901</small>
        </div>
    </div>
@endif

    @if(isset($scholarships[0]->apply_now->no_of_siblings_h_s) && ($scholarships[0]->apply_now->no_of_siblings_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>No of siblings {{$scholarships[0]->apply_now->no_of_siblings_r == '1' ? '*' : '' }}</label>
            <select name="number_of_siblings" class="form-input-one" {{$scholarships[0]->apply_now->no_of_siblings_r == '1' ? 'required="true"' : '' }}>
                <option value="">Select No of siblings</option>
                <option value="only_child" {{ (isset($draft) && $draft->number_of_siblings != '' ? $draft->number_of_siblings : '') === 'only_child' ? 'selected' : '' }}> Only Child</option>
                <option value="1" {{ (isset($draft) && $draft->number_of_siblings != '' ? $draft->number_of_siblings : '') === '1' ? 'selected' : '' }}> 1</option>
                <option value="2" {{ (isset($draft) && $draft->number_of_siblings != '' ? $draft->number_of_siblings : '') === '2' ? 'selected' : '' }}> 2</option>
                <option value="3" {{ (isset($draft) && $draft->number_of_siblings != '' ? $draft->number_of_siblings : '') === '3' ? 'selected' : '' }}> 3</option>
                <option value="4" {{ (isset($draft) && $draft->number_of_siblings != '' ? $draft->number_of_siblings : '') === '4' ? 'selected' : '' }}> 4</option>
                <option value="5" {{ (isset($draft) && $draft->number_of_siblings != '' ? $draft->number_of_siblings : '') === '5' ? 'selected' : '' }}> 5</option>
                <option value="6" {{ (isset($draft) && $draft->number_of_siblings != '' ? $draft->number_of_siblings : '') === '6' ? 'selected' : '' }}> 6</option>
            </select>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->familys_annual_income_h_s) && ($scholarships[0]->apply_now->familys_annual_income_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Family Annual Income (in INR) {{$scholarships[0]->apply_now->familys_annual_income_r == '1' ? '*' : '' }}</label>
            <input  type="text" name="annual_income"  value="{{ isset($draft) && $draft->annual_income != '' ? $draft->annual_income : '' }}"  class="form-input-one digitsOnly"  placeholder="Annual Income"  {{$scholarships[0]->apply_now->familys_annual_income_r == '1' ? 'required="true"' : '' }} />
        </div>
    </div>
    <div class="col-md-12">
        <h6 class="center-heading">Current Address</h6>
    </div>

@endif
@if(isset($scholarships[0]->apply_now->current_house_type_h_s) && ($scholarships[0]->apply_now->current_house_type_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>House Type {{$scholarships[0]->apply_now->current_house_type_r == '1' ? '*' : '' }}</label>
            <select name="current_house_type" class="form-input-one" {{$scholarships[0]->apply_now->current_house_type_r == '1' ? 'required="true"' : '' }}>
                <option value="">Select House Type</option>
                <option value="self_family_owned_katcha_house" {{ (isset($draft) && $draft->current_house_type != '' ? $draft->current_house_type : '') === 'self_family_owned_katcha_house' ? 'selected' : '' }}> Self/Family Owned Katcha House (Mud House, Tin Shed)</option>
                <option value="self_family_owned_pakka_house" {{ (isset($draft) && $draft->current_house_type != '' ? $draft->current_house_type : '') === 'self_family_owned_pakka_house' ? 'selected' : '' }}> Self/Family Owned Pakka House</option>
                <option value="rented_katcha_house" {{ (isset($draft) && $draft->current_house_type != '' ? $draft->current_house_type : '') === 'rented_katcha_house' ? 'selected' : '' }}> Rented Katcha (Mud House/Tin Shed)</option>
                <option value="rented_pakka_house" {{ (isset($draft) && $draft->current_house_type != '' ? $draft->current_house_type : '') === 'rented_pakka_house' ? 'selected' : '' }}> Rented Pakka House</option>
            </select>
        </div>
    </div>
@endif
@if(isset($scholarships[0]->apply_now->current_address_h_s) && ($scholarships[0]->apply_now->current_address_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Address {{$scholarships[0]->apply_now->current_address_r == '1' ? '*' : '' }}</label>
            <textarea name="current_address" class="form-input-one" placeholder="Address" {{$scholarships[0]->apply_now->current_address_r == '1' ? 'required="true"' : '' }} >{{ (isset($draft) && $draft->current_address != '') ? $draft->current_address : '' }}</textarea>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->current_state_h_s) && ($scholarships[0]->apply_now->current_state_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Current State {{$scholarships[0]->apply_now->current_state_r == '1' ? '*' : '' }}</label> 
            <select class="form-input-one current_state" name="current_state" id="current_state" {{$scholarships[0]->apply_now->current_state_r == '1' ? 'required="true"' : '' }}>
                <option value="" data-val='' >Select Current State</option>
                @foreach($state as $s)
                <option value="{{$s->name}}" class="" data-val="{{$s->id}}"{{ ((isset($draft) && ($draft->current_state != '') ? $draft->current_state : '') == $s->name) ? 'selected' : '' }} >{{$s->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->current_district_h_s) && ($scholarships[0]->apply_now->current_district_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Current District {{$scholarships[0]->apply_now->current_district_r == '1' ? '*' : '' }}</label>
            <select id="current_district" name="current_district" class="form-input-one" {{$scholarships[0]->apply_now->current_district_r == '1' ? 'required="true"' : '' }} >
                <option data-state="0" value="" >Select Current District</option>
                @foreach($district as $s)
                <option value="{{$s->name}}" data-state="{{$s->state_id}}" {{ ((isset($draft) && ($draft->current_district != '') ? ($draft->current_district) : $s->state_id) == $s->name) ? 'selected' : '' }}  >{{$s->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->current_pin_code_h_s) && ($scholarships[0]->apply_now->current_pin_code_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Pincode {{$scholarships[0]->apply_now->current_pin_code_r == '1' ? '*' : '' }}</label>
            <input  type="text" name="current_pincode"  id="current_pincode"  class="form-input-one digitsOnly"  placeholder="Pincode"  value="{{ (isset($draft) && $draft->current_pincode != '') ? ($draft->current_pincode) : '' }}"  {{$scholarships[0]->apply_now->current_pin_code_r == '1' ? 'required="true"' : '' }} />
            <div id="pincodeError" style="color: red;"></div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-box-one">
            <label>
            <input  type="checkbox"  name="is_pm_same_as_current" id="is_pm_same_as_current"  {{ ((isset($draft) && $draft->is_pm_same_as_current != '') ? ($draft->is_pm_same_as_current??0) : 0) == 1 ? 'checked="checked"' : '' }} />
                If same as Current address
            </label>
        </div>
    </div>
    <div class="col-md-12">
        <h6 class="center-heading">Permanent Address</h6>
    </div>
    <script>
    {{
    (isset($draft) && $draft->is_pm_same_as_current != '') ? 'chenageCurrentAndPermanentAddress()': '' 
    }}
    </script>
@endif
@if(isset($scholarships[0]->apply_now->permanent_home_type_h_s) && ($scholarships[0]->apply_now->permanent_home_type_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>House Type {{$scholarships[0]->apply_now->permanent_home_type_r == '1' ? '*' : '' }}</label>
            <select name="permanent_house_type" id="permanent_house_type" class="form-input-one" {{$scholarships[0]->apply_now->permanent_home_type_r == '1' ? 'required="true"' : '' }} >
                <option value="">Select House Type</option>
                <option value="self_family_owned_katcha_house" {{ ((isset($draft) && $draft->permanent_house_type != '') ? $draft->permanent_house_type : '') === 'self_family_owned_katcha_house' ? 'selected' : '' }} >Self/Family Owned Katcha House (Mud House, Tin Shed)</option>
                <option value="self_family_owned_pakka_house" {{ ((isset($draft) && $draft->permanent_house_type != '') ? $draft->permanent_house_type : '') === 'self_family_owned_pakka_house' ? 'selected' : '' }} >Self/Family Owned Pakka House </option>
                <option value="rented_katcha_house" {{ ((isset($draft) && $draft->permanent_house_type != '') ? $draft->permanent_house_type : '') === 'rented_katcha_house' ? 'selected' : '' }} >Rented Katcha (Mud House/Tin Shed)</option>
                <option value="rented_pakka_house"{{ ((isset($draft) && $draft->permanent_house_type != '') ? $draft->permanent_house_type : '') === 'rented_pakka_house' ? 'selected' : '' }} >Rented Pakka House</option>
            </select>
        </div>
    </div>
@endif
@if(isset($scholarships[0]->apply_now->permanent_address_h_s) && ($scholarships[0]->apply_now->permanent_address_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Address {{$scholarships[0]->apply_now->permanent_address_r == '1' ? '*' : '' }}</label>
            <textarea  name="permanent_address"  id="permanent_address"  class="form-input-one"  placeholder="Address"  {{$scholarships[0]->apply_now->permanent_address_r == '1' ? 'required="true"' : '' }} > {{ (isset($draft) && $draft->permanent_address != '') ? $draft->permanent_address : '' }} </textarea> 
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->permanent_state_h_s) && ($scholarships[0]->apply_now->permanent_state_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Permanent State {{$scholarships[0]->apply_now->permanent_state_r == '1' ? '*' : '' }}</label>
            <select class="form-input-one" name="permanent_state" id="permanent_state" {{$scholarships[0]->apply_now->permanent_state_r == '1' ? 'required="true"' : '' }}>
                <option value="" data-val=''>Select Permanent State</option>
                @foreach($state as $s)
                <option value="{{$s->name}}" class="" data-val="{{$s->id}}" {{ ((isset($draft) && ($draft->permanent_state != '') ? $draft->permanent_state : $s->id) == $s->name) ? 'selected' : '' }} >{{$s->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->permanent_district_h_s) && ($scholarships[0]->apply_now->permanent_district_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>District {{$scholarships[0]->apply_now->permanent_district_r == '1' ? '*' : '' }}</label>
             <select id="permanent_district" name="permanent_district" class="form-input-one" {{$scholarships[0]->apply_now->permanent_district_r == '1' ? 'required="true"' : '' }}>
                <option data-state="0" value="" >Select Permanent District</option>
                @foreach($district as $s)
                <option  value="{{$s->name}}"  data-state="{{$s->state_id}}"  {{ ((isset($draft) && ($draft->permanent_district != '') ? $draft->permanent_district : $s->state_id) == $s->name) ? 'selected' : '' }}  > {{$s->name}} </option>
                @endforeach
            </select>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->permanent_pin_code_h_s) && ($scholarships[0]->apply_now->permanent_pin_code_h_s == '1'))
    <div class="col-md-4">
    <div class="form-box-one">
        <label>Pincode {{$scholarships[0]->apply_now->permanent_pin_code_r == '1' ? '*' : '' }}</label>
        <input  type="text" name="permanent_pincode"  class="form-input-one digitsOnly"  id="permanent_pincode"  placeholder="Pincode"  value="{{ (isset($draft) && $draft->permanent_pincode != '') ? ($draft->permanent_pincode) : '' }}"  {{$scholarships[0]->apply_now->permanent_pin_code_r == '1' ? 'required="true"' : '' }} />
        <div id="pincodeErrornew" style="color: red;"></div>
    </div>
</div>
    @endif
    @if(isset($scholarships[0]->apply_now->permanent_current_citizenship_h_s) && ($scholarships[0]->apply_now->permanent_current_citizenship_h_s == '1'))
    <div class="col-md-4">
        <div class="form-box-one">
            <label>Current Citizenship {{$scholarships[0]->apply_now->permanent_current_citizenship_r == '1' ? '*' : '' }}</label>
            <select class="form-input-one" name="current_citizenship" id="current_citizenship" {{$scholarships[0]->apply_now->permanent_current_citizenship_r == '1' ? 'required="true"' : '' }}>
                <option value="">Select Current Citizenship</option>
                <option value="India" {{ (((isset($draft) && $draft->current_citizenship != '' ) ? $draft->current_citizenship : optional($user->student)->current_citizenship)==='India')?'selected':''}}>India</option>
            </select>
        </div>
    </div>
    @endif
</div>
<script>
       $(document).on('change', '#current_state', function () {
            const cstate = $('#current_state option:selected').data('val');
            var coptions = $('#current_district option');   
            resetSelect($('#current_district'));
            coptions.each(function () {
                if ($(this).data('state') === cstate || $(this).data('state') == 0) {
                    $(this).css('display', 'block');
                }else{
                   $(this).css('display', 'none');
                }
            });
        });
        $(document).on('change', '#permanent_state', function () {
            const pstate = $('#permanent_state option:selected').data('val');
            var poptions = $('#permanent_district option'); 
            resetSelect($('#permanent_district'));
            poptions.each(function () {
                if ($(this).data('state') === pstate || $(this).data('state') == 0) {
                    $(this).css('display', 'block');
                }else{
                   $(this).css('display', 'none');
                }
            });
        });
    function resetSelect($selectElement,i = 0) {
        $selectElement.prop('selectedIndex', i); 
    }
    document.getElementById('current_pincode').addEventListener('input', function(event) {
        var pincode = event.target.value.trim();
        var errorDiv = document.getElementById('pincodeError');
        var pattern = /^[1-9][0-9]{5}$/;
        if (!pattern.test(pincode)) {
            errorDiv.textContent = "Please enter a valid Indian PIN code (6 digits starting with a non-zero digit).";
        } else {
            errorDiv.textContent = "";
        }
    });
    document.getElementById('permanent_pincode').addEventListener('input', function(event) {
        var pincode = event.target.value.trim();
        var errorDiv = document.getElementById('pincodeErrornew');
        var pattern = /^[1-9][0-9]{5}$/; 
        if (!pattern.test(pincode)) {
            errorDiv.textContent = "Please enter a valid Indian PIN code (6 digits starting with a non-zero digit).";
        } else {
            errorDiv.textContent = "";
        }
    });
</script>