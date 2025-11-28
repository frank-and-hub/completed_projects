<div class="row">
    @if(isset($scholarships[0]->apply_now->first_name_h_s) && $scholarships[0]->apply_now->first_name_h_s == '1')
      <div class="col-md-3">
          <div class="form-box-one">
              <label>First Name{{$scholarships[0]->apply_now->first_name_r =='1'?'*':''}}</label>
              <input 
                  type="text" 
                  name="first_name"
                  class="form-input-one {{ $user->first_name ? 'readonly' : '' }}" 
                  placeholder="First Name" 
                  value="{{ $user->first_name }}" 
                  id="first_name"
                  {{ $user->first_name ? 'disabled' : '' }} {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->first_name_r == '1' ? 'required=true' : '') : ''}}
                  />
          </div>
      </div>
      @endif
      @if(isset($scholarships[0]->apply_now->last_name_h_s) && $scholarships[0]->apply_now->last_name_h_s == '1')
      <div class="col-md-3">
          <div class="form-box-one">
              <label>Last Name{{$scholarships[0]->apply_now->last_name_r =='1'?'*':''}}</label>
              <input 
                  type="text" 
                  name="last_name"
                  class="form-input-one {{ $user->last_name ? 'readonly' : '' }}"
                  placeholder="Last Name" 
                  value="{{ $user->last_name }}" 
                  id="last_name"
                  {{ $user->last_name ? 'disabled' : '' }} {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->last_name_r == '1' ? 'required=true' : '') : ''}}
                  />
          </div>
      </div>
      @endif
      @if(isset($scholarships[0]->apply_now->email_h_s) && $scholarships[0]->apply_now->email_h_s == '1')
      <div class="col-md-3">
          <div class="form-box-one">
              <label>Email Id{{$scholarships[0]->apply_now->email_r =='1'?'*':''}}</label>
              <input 
                  type="email" 
                  name="email"
                  class="form-input-one {{ $user->email ? 'readonly' : '' }}"
                  placeholder="Email Address" 
                  value="{{ $user->email }}" 
                  id="email"
                  {{ $user->email ? 'disabled' : '' }} {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->email_r == '1' ? 'required=true' : '') : ''}}
                  />
          </div>
      </div>
      @endif
      @if(isset($scholarships[0]->apply_now->phone_number_h_s) && $scholarships[0]->apply_now->phone_number_h_s == '1')
    <div class="col-md-3">
        <div class="form-box-one">
            <label>Mobile Number{{$scholarships[0]->apply_now->phone_number_r =='1'?'*':''}}</label>
            <input 
                type="text" 
                name="phone_number" 
                class="form-input-one digitsOnly" 
                id="phone_number"
                placeholder="Phone No." 
                value="{{ isset($draft) && $draft->phone_number != '' ? $draft->phone_number : $user->phone_number }}" 
                {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->phone_number_r == '1' ? 'required=true' : '') : ''}}
                pattern="[0-9]{10}" 
                title="Please enter a valid 10-digit phone number"
                />
            <small>Format: 1234567890</small>
        </div>
    </div>
@endif

      @if(isset($scholarships[0]->apply_now->dob_h_s) && $scholarships[0]->apply_now->dob_h_s == '1')
      <div class="col-md-3">
          <div class="form-box-one">
  
              <label>Date of Birth{{$scholarships[0]->apply_now->dob_r =='1'?'*':''}}</label>
              <input 
                type="date" 
                name="dob" 
                class="date form-input-one" 
                id="dob" 
                onmouseenter="setEndDateOnHover()" 
                placeholder="Date of Birth" 
                value="{{ isset($draft) && $draft->dob != '' ? $draft->dob : $user->date_of_birth }}" 
                {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->dob_r == '1' ? 'required=true' : '') : ''}} 
                />
          </div>
      </div>
      @endif
      @if(isset($scholarships[0]->apply_now->whatsapp_number_h_s) && $scholarships[0]->apply_now->whatsapp_number_h_s == '1')
    <div class="col-md-3">
        <div class="form-box-one">
            <label>Whatsapp Number{{$scholarships[0]->apply_now->whatsapp_number_r =='1'?'*':''}}</label>
            <input 
                type="text" 
                name="whatsapp_number" 
                class="form-input-one digitsOnly" 
                id="whatsapp_number"
                placeholder="Whatsapp Number" 
                value="{{ isset($draft) && $draft->whatsapp_number != '' ? $draft->whatsapp_number : $user->whatsapp_number }}"
                {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->whatsapp_number_r == '1' ? 'required=true' : '') : ''}}
                pattern="[0-9]{10}" 
                title="Please enter a valid 10-digit WhatsApp number" 
                />
            <small>Format: 12345678901</small>
        </div>
    </div>
@endif

      @if(isset($scholarships[0]->apply_now->gender_h_s) && $scholarships[0]->apply_now->gender_h_s == '1')
      <div class="col-md-3">
          <div class="form-box-one">
  
              <label>Gender{{$scholarships[0]->apply_now->gender_r =='1'?'*':''}}</label>
              <select 
                name="gender" 
                id="gender" 
                class="form-input-one" 
                {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->gender_r == '1' ? 'required=true' : '') : ''}}
                >
                  <option value="">Select Gender</option>
                  <option value="male" {{ ((isset($draft) && $draft->gender != '' ? $draft->gender : $user->gender) === 'male' ? 'selected' : '') }}>
                      Male</option>
                  <option value="female" {{ ((isset($draft) && $draft->gender != '' ? $draft->gender : $user->gender) === 'female' ? 'selected' : '') }}>
                      Female</option>
                  <option value="other" {{ ((isset($draft) && $draft->gender != '' ? $draft->gender : $user->gender) === 'other' ? 'selected' : '') }}>
                      Other</option>
              </select>
          </div>
      </div>
      @endif
      @if(isset($scholarships[0]->apply_now->aadhar_card_number_h_s) && $scholarships[0]->apply_now->aadhar_card_number_h_s == '1')
<div class="col-md-3">
    <div class="form-box-one">
        <label>Aadhar Card Number{{$scholarships[0]->apply_now->aadhar_card_number_r =='1'?'*':''}}</label>
        <input 
            type="text" 
            name="aadhar_card_number" 
            id="aadhar_card_number"
            value="{{ isset($draft) && $draft->aadhar_card_number != '' ? $draft->aadhar_card_number : $user->aadhar_card_number }}" 
            class="form-input-one digitsOnly"
            placeholder="Aadhar Card Number" 
            {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->aadhar_card_number_r == '1' ? 'required=true' : '') : ''}}
            pattern="[0-9]{12}" 
            title="Aadhar Card Number must be exactly 12 digits" 
            />
    </div>
</div>
@endif

      @if(isset($scholarships[0]->apply_now->if_you_below_to_maturity_h_s) && $scholarships[0]->apply_now->if_you_below_to_maturity_h_s == '1')
      <div class="col-md-3">
          <div class="form-box-one">
              <label>
                  <input 
                     name="is_minority" 
                     id="is_minority"
                     {{--  checked="{{ optional($user->student)->is_minority ? 'checked' : '' }}" --}}
                     {{ (isset($draft) && $draft->is_minority != '' ? $draft->is_minority : 0) == 1 ? 'checked="checked"' : '' }}
                     type="checkbox"
                     {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->if_you_below_to_maturity_r == '1' ? 'required=true' : '') : ''}}
                     />
                  If you belong to minority
              </label>
              <div id="minority_group_div" style="display:none;">
                  <select 
                    name="minority_group" 
                    class="form-input-one" 
                    >
                      <option value="">Select minority group</option>
                      <option value="muslim"
                          {{  (isset($draft) && $draft->minority_group != '' ? $draft->minority_group : strtolower(optional($user->student)->minority_group)) === 'muslim' ? 'selected' : '' }} 
                          >
                          Muslim</option>
                      <option value="sikh"
                          {{  (isset($draft) && $draft->minority_group != '' ? $draft->minority_group : strtolower(optional($user->student)->minority_group)) === 'sikh' ? 'selected' : '' }} 
                          >
                          Sikh</option>
                      <option value="christian"
                          {{  (isset($draft) && $draft->minority_group != '' ? $draft->minority_group : strtolower(optional($user->student)->minority_group)) === 'christian' ? 'selected' : '' }} 
                          >
                          Christian</option>
                      <option value="buddhist"
                          {{  (isset($draft) && $draft->minority_group != '' ? $draft->minority_group : strtolower(optional($user->student)->minority_group)) === 'buddhist' ? 'selected' : '' }} 
                          >
                          Buddhist</option>
                      <option value="jain"
                          {{  (isset($draft) && $draft->minority_group != '' ? $draft->minority_group : strtolower(optional($user->student)->minority_group)) === 'jain' ? 'selected' : '' }} 
                          >
                          Jain</option>
                      <option value="zoroastrians"
                          {{  (isset($draft) && $draft->minority_group != '' ? $draft->minority_group : strtolower(optional($user->student)->minority_group)) === 'zoroastrians' ? 'selected' : '' }} 
                          >
                          Zoroastrians</option>
                  </select>
              </div>
          </div>
      </div>
      <script>
          
          $(document).ready(function () {
              var isMinorityCheckbox = $("#is_minority");
              var minorityGroupDiv = $("#minority_group_div");
      
              // Set the initial state based on checkbox
              if (isMinorityCheckbox.is(':checked')) {
                  minorityGroupDiv.show();
              } else {
                  minorityGroupDiv.hide();
              }
      
              // Add event listener for future changes
              $('body').on('change', '#is_minority', function () {
                  if ($(this).is(':checked')) {
                      $("#minority_group_div").show();
                  } else {
                      $("#minority_group_div").hide();
                  }
              });
          });
      </script>
  @endif
  @if(isset($scholarships[0]->apply_now->category_h_s) && $scholarships[0]->apply_now->category_h_s == '1')
      <div class="col-md-3">
          <div class="form-box-one">
  
              <label>Category{{$scholarships[0]->apply_now->category_r =='1'?'*':''}}</label>
              <select 
                name="category" 
                id="personal_details_category" 
                class="form-input-one" 
                {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->category_r == '1' ? 'required=true' : '') : ''}} 
                />
                  <option value="">Select Category</option>
                  <option value="general"
                      {{  (isset($draft) && $draft->category != '' ? $draft->category : strtolower(optional($user->student)->category)) === 'general' ? 'selected' : '' }} 
                      > 
                      General</option>
                  <option value="obc c"
                      {{  (isset($draft) && $draft->category != '' ? $draft->category : strtolower(optional($user->student)->category)) === 'obc c' ? 'selected' : '' }} 
                      > 
                      OBC C</option>
                  <option value="obc nc"
                      {{  (isset($draft) && $draft->category != '' ? $draft->category : strtolower(optional($user->student)->category)) === 'obc nc' ? 'selected' : '' }} 
                      > 
                      OBC NC</option>
                  <option value="sc"
                      {{  (isset($draft) && $draft->category != '' ? $draft->category : strtolower(optional($user->student)->category)) === 'sc' ? 'selected' : '' }} 
                      > 
                      SC</option>
                  <option value="st"
                      {{  (isset($draft) && $draft->category != '' ? $draft->category : strtolower(optional($user->student)->category)) === 'st' ? 'selected' : '' }} 
                      > 
                      ST</option>
                  <option value="other reservation"
                      {{  (isset($draft) && $draft->category != '' ? $draft->category : strtolower(optional($user->student)->category)) === 'other reservation' ? 'selected' : '' }} 
                      > 
                      Other Reservation</option>
              </select>
          </div>
      </div>
      @endif
      <div class="col-md-3" id="please_specify_other_reservation" style="display:none">
          <div class="form-box-one">
              <label>Please Specify (Other Reservation)</label>
              <input 
                type="text" 
                name="other_reservation" 
                class="form-input-one" 
                value="{{ (isset($draft) && $draft->other_reservation != '' ? $draft->other_reservation : optional($user->student)->other_reservation) }}" 
                placeholder="" 
                required
                />
          </div>
      </div>  
      @if(isset($scholarships[0]->apply_now->if_you_belong_to_pwd_category_h_s) && $scholarships[0]->apply_now->if_you_belong_to_pwd_category_h_s == '1')
      <div class="col-md-3">
          <div class="form-box-one">
              <input 
                id="is_pwd_category" 
                name="is_pwd_category" 
                type="checkbox"
                {{ (isset($draft) && $draft->is_pwd_category != '' ? $draft->is_pwd_category : 0) == 1 ? 'checked="checked"' : '' }}
                {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->if_you_belong_to_pwd_category_r == '1' ? 'required=true' : '') : ''}}
                />
              If you belong to PwD Category{{($scholarships[0]->apply_now->if_you_belong_to_pwd_category_r == '1' ? '*' : '')}}
  
              <div id="pwd_percentage_div" style="display:none;">
                  <input 
                    name="pwd_percentage" 
                    type="range" 
                    min="1"
                    class="form-control-range w-100" 
                    id="formControlRange"
                    onInput="$('#rangeval').html($(this).val())"
                    value="{{ (isset($draft) && $draft->pwd_percentage != '' ? $draft->pwd_percentage : optional($user->student)->pwd_percentage) ?? 50 }}">
                  <span
                      id="rangeval">{{ (isset($draft) && $draft->pwd_percentage != '' ? $draft->pwd_percentage : optional($user->student)->pwd_percentage) ?? 50 }}</span><span>%</span>
              </div>
          </div>
      </div>
      
  @endif
  @if(isset($scholarships[0]->apply_now->if_your_your_family_belong_to_army_h_s) && $scholarships[0]->apply_now->if_your_your_family_belong_to_army_h_s == '1')
      <div class="col-md-3">
          <div class="form-box-one">
              <label>
                  <input 
                    id="is_army_veteran_category" 
                    name="is_army_veteran_category"
                    type="checkbox"
                    {{ (isset($draft) && $draft->is_army_veteran_category != '' ? $draft->is_army_veteran_category : 0) == 1 ? 'checked="checked"' : '' }}
                    {{$scholarships[0]->apply_now ? ($scholarships[0]->apply_now->if_your_your_family_belong_to_army_r == '1' ? 'required=true' : '') : ''}}
                    />
                  If you/your family belong to army veteran category.{{($scholarships[0]->apply_now->if_your_your_family_belong_to_army_r == '1' ? '*' : '')}}
              </label>
          </div>
      </div>
      
      @endif
  </div>
  
  <script>
      $(document).ready(function () {
          const isArmyVeteranCheckbox = $("#is_army_veteran_category");
          const armyVeteranDataDiv = $("#army_veteran_data_div");
  
          // Set the initial state based on checkbox
          if (isArmyVeteranCheckbox.is(':checked')) {
              armyVeteranDataDiv.show();
          } else {
              armyVeteranDataDiv.hide();
          }
  
          $('body').on('change', '[name="is_army_veteran_category"]', function () {
              console.log("isArmyVeteranCheckbox Checkbox state changed");
              if ($(this).is(':checked')) {
                  armyVeteranDataDiv.show();
              } else {
                  armyVeteranDataDiv.hide();
              }
          });
  
     
          const isPWDCheckbox = $("#is_pwd_category");
          const pwdPercentageDiv = $("#pwd_percentage_div");
  
          // Set the initial state based on checkbox
          if (isPWDCheckbox.is(':checked')) {
              pwdPercentageDiv.show();
          } else {
              pwdPercentageDiv.hide();
          }
  
          $('body').on('change', '[name="is_pwd_category"]', function () {
              if ($(this).is(':checked')) {
                  pwdPercentageDiv.show();
              } else {
                  pwdPercentageDiv.hide();
              }
          });
          // $('#please_specify_other_reservation').hide();
          $('#personal_details_category').on('change', function () {
              let value = $(this).val();
              $('#please_specify_other_reservation').hide();
              if (value === 'other reservation') {
                  $('#please_specify_other_reservation').show();
                  $("form[name='other_reservation']").prop('required', true);
              } else {
                  $("form[name='other_reservation']").prop('required', false);
                  $('#please_specify_other_reservation').hide();
              }
          });
          
          @if($scholarships[0]->apply_now)
            @if($scholarships[0]->apply_now->first_name_r == '1')
            $('#first_name').prop('required', true);
            @else
            $('#first_name').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->last_name_r == '1')
            $('#last_name').prop('required', true);
            @else
            $('#last_name').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->email_r == '1')
            $('#email').prop('required', true);
            @else
            $('#email').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->phone_number_r == '1')
            $('#phone_number').prop('required', true);
            @else
            $('#phone_number').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->dob_r == '1')
            $('#dob').prop('required', true);
            @else
            $('#dob').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->whatsapp_number_r == '1')
            $('#whatsapp_number').prop('required', true);
            @else
            $('#whatsapp_number').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->gender_r == '1')
            $('#gender').prop('required', true);
            @else
            $('#gender').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->aadhar_card_number_r == '1')
            $('#aadhar_card_number').prop('required', true);
            @else
            $('#aadhar_card_number').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->if_you_below_to_maturity_r == '1')
            $('#is_minority').prop('required', true);
            @else
            $('#is_minority').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->category_r == '1')
            $('#personal_details_category').prop('required', true);
            @else
            $('#personal_details_category').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->if_you_belong_to_pwd_category_r == '1')
            $('#is_pwd_category').prop('required', true);
            @else
            $('#is_pwd_category').prop('required', false);
            @endif
            @if($scholarships[0]->apply_now->if_your_your_family_belong_to_army_r == '1')
            $('#is_army_veteran_category').prop('required', true);
            @else
            $('#is_army_veteran_category').prop('required', false);
            @endif
          @endif
      });
  </script>