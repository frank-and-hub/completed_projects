@extends('admin.layout.master')

@push('plugin-styles')
  <!-- Plugin css import here -->
@endpush

@section('content')

<form method="post" action="{{route('scholarship.apply_now.form')}}" id="" class="" >   
    <!-- Page content here -->
    <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h4>Personal Details</h4>
              <div class="card-body">
                <div class="table-responsive">
              <table id="dataTableExample" class="table">
                <thead>
                  <tr>
                    <th>Name of Field</th>
                    <th>Show / Hide Field</th>
                    <th>Required</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>First Name</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->first_name_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="first_name_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->first_name_r  === '1' ? 'checked' : '' ) : 'checked' }} name="first_name_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Last Name</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->last_name_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="last_name_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->last_name_r  === '1' ? 'checked' : '' ) : 'checked' }} name="last_name_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Email Id</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->email_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="email_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->email_r  === '1' ? 'checked' : '' ) : 'checked' }} name="email_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Phone Number</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->phone_number_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="phone_number_h_s" >
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->phone_number_r  === '1' ? 'checked' : '' ) : 'checked' }} name="phone_number_r" >
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Date of Birth</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->dob_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="dob_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->dob_r  === '1' ? 'checked' : '' ) : 'checked' }} name="dob_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Whatsapp Number</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->whatsapp_number_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="whatsapp_number_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->whatsapp_number_r  === '1' ? 'checked' : '' ) : 'checked' }} name="whatsapp_number_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Gender</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->gender_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="gender_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->gender_r  === '1' ? 'checked' : '' ) : 'checked' }} name="gender_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Aadhar Card Number</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->aadhar_card_number_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="aadhar_card_number_h_s" >
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->aadhar_card_number_r  === '1' ? 'checked' : '' ) : 'checked' }} name="aadhar_card_number_r" >
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>If you belong to minority</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->if_you_below_to_maturity_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="if_you_below_to_maturity_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->if_you_below_to_maturity_r  === '1' ? 'checked' : '' ) : 'checked' }} name="if_you_below_to_maturity_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Category</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->category_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="category_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->category_r  === '1' ? 'checked' : '' ) : 'checked' }} name="category_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>If you belong to PwD Category</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->if_you_belong_to_pwd_category_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="if_you_belong_to_pwd_category_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->if_you_belong_to_pwd_category_r  === '1' ? 'checked' : '' ) : 'checked' }} name="if_you_belong_to_pwd_category_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>If your/your family belong to army</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->if_your_your_family_belong_to_army_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="if_your_your_family_belong_to_army_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->if_your_your_family_belong_to_army_r  === '1' ? 'checked' : '' ) : 'checked' }} name="if_your_your_family_belong_to_army_r">
                    </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
              </div>
            </div>
        </div>
        </div>
    </div>
    
    <!-- second Step -->
    <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h4>Family Details</h4>
              <div class="card-body">
                <div class="table-responsive">
              <table id="dataTableExample" class="table">
                <thead>
                  <tr>
                    <th>Name of Field</th>
                    <th>Show / Hide Field</th>
                    <th>Required</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Principal Guardian Name</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->principal_guardian_name_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="principal_guardian_name_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->principal_guardian_name_r  === '1' ? 'checked' : '' ) : 'checked' }} name="principal_guardian_name_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Relationship with the Principal Guardian</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->relationship_with_the_principal_guardian_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="relationship_with_the_principal_guardian_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->relationship_with_the_principal_guardian_r  === '1' ? 'checked' : '' ) : 'checked' }} name="relationship_with_the_principal_guardian_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Occupation of the Principal Guardian</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->occupation_of_the_principal_guardian_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="occupation_of_the_principal_guardian_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->occupation_of_the_principal_guardian_r  === '1' ? 'checked' : '' ) : 'checked' }} name="occupation_of_the_principal_guardian_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Mobile No. of the Principal Guardian</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->mobile_no_of_the_principal_guardian_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="mobile_no_of_the_principal_guardian_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->mobile_no_of_the_principal_guardian_r  === '1' ? 'checked' : '' ) : 'checked' }} name="mobile_no_of_the_principal_guardian_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>No of siblings</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->no_of_siblings_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="no_of_siblings_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->no_of_siblings_r  === '1' ? 'checked' : '' ) : 'checked' }} name="no_of_siblings_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Family's Annual Income (in INR)</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->familys_annual_income_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="familys_annual_income_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->familys_annual_income_r  === '1' ? 'checked' : '' ) : 'checked' }} name="familys_annual_income_r">
                    </div>
                    </td>
                  </tr>
                </tbody>
                </table>
                <div class="onecontent">
                    <div class="mb-2 mt-4">
                    <div class="mb-2">
                      <h5>Current Address</h5>
                    </div>
                  </div>
                </div>
                <table id="dataTableExample" class="table">
                  <thead>
                    <tr>
                      <th>Name of Field</th>
                      <th>Show / Hide Field</th>
                      <th>Required</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>House Type</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_house_type_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="current_house_type_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_house_type_r  === '1' ? 'checked' : '' ) : 'checked' }} name="current_house_type_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Address</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_address_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="current_address_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_address_r  === '1' ? 'checked' : '' ) : 'checked' }} name="current_address_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>State</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_state_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="current_state_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_state_r  === '1' ? 'checked' : '' ) : 'checked' }} name="current_state_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>District</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_district_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="current_district_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_district_r  === '1' ? 'checked' : '' ) : 'checked' }} name="current_district_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Pin Code</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_pin_code_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="current_pin_code_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->current_pin_code_r  === '1' ? 'checked' : '' ) : 'checked' }} name="current_pin_code_r">
                      </div>
                      </td>
                    </tr>
                  </tbody>
              </table>
              <div class="onecontent">
                    <div class="mb-2 mt-4">
                    <div class="mb-2">
                      <h5>Permanent Address</h5>
                    </div>
                  </div>
                </div>
                <table id="dataTableExample" class="table">
                  <thead>
                    <tr>
                      <th>Name of Field</th>
                      <th>Show / Hide Field</th>
                      <th>Required</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>House Type</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_home_type_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_home_type_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_home_type_r  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_home_type_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Address</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_address_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_address_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_address_r  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_address_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>State</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_state_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_state_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_state_r  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_state_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>District</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_district_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_district_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_district_r  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_district_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Pin Code</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_pin_code_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_pin_code_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_pin_code_r  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_pin_code_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Current Citizenship</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_current_citizenship_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_current_citizenship_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->permanent_current_citizenship_r  === '1' ? 'checked' : '' ) : 'checked' }} name="permanent_current_citizenship_r">
                      </div>
                      </td>
                    </tr>
                  </tbody>
              </table>
            </div>
              </div>
            </div>
        </div>
        </div>
    </div>
    
    <!-- Third Step -->
    <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h4>Education Details</h4>
              <div class="card-body">
                <table id="dataTableExample" class="table">
                <thead>
                  <tr>
                    <th>Name of Field</th>
                    <th>Show / Hide Field</th>
                    <th>Required</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Occupation</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_occupation_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_occupation_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_occupation_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_occupation_r">
                    </div>
                    </td>
                  </tr>
                </tbody>
                </table>
                <div class="table-responsive">
                  
                <div class="onecontent">
                    <div class="mb-2 mt-4">
                    <div class="mb-2">
                      <h5>Graduation</h5>
                    </div>
                  </div>
                </div>
              <table id="dataTableExample" class="table">
                <thead>
                  <tr>
                    <th>Name of Field</th>
                    <th>Show / Hide Field</th>
                    <th>Required</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Institute/University*</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_institute_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_institute_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_institute_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_institute_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Type of Institute</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_type_of_institute_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_type_of_institute_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_type_of_institute_r  === '1' ? 'checked' : '' ) : 'checked' }}  name="education_graduation_type_of_institute_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>District</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_district_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_district_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_district_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_district_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>State</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_state_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_state_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_state_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_state_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Course Name *</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_course_name_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_course_name_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_course_name_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_course_name_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Specialisation</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_specialisation_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_specialisation_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_specialisation_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_specialisation_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Grading System</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_grading_system_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_grading_system_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_grading_system_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_grading_system_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Percentage scored/CGPA</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_percentage_scored_cgpa_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_percentage_scored_cgpa_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_percentage_scored_cgpa_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_percentage_scored_cgpa_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>From</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_form_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_form_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_form_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_form_r">
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>To</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_to_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_to_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->education_graduation_to_r  === '1' ? 'checked' : '' ) : 'checked' }} name="education_graduation_to_r">
                    </div>
                    </td>
                  </tr>
                </tbody>
                </table>
                <div class="onecontent">
                    <div class="mb-2 mt-4">
                    <div class="mb-2">
                      <h5>Work Expereince</h5>
                    </div>
                  </div>
                </div>
                <table id="dataTableExample" class="table">
                  <thead>
                    <tr>
                      <th>Name of Field</th>
                      <th>Show / Hide Field</th>
                      <th>Required</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Employment Type</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_employment_type_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="work_employment_type_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_employment_type_r  === '1' ? 'checked' : '' ) : 'checked' }} name="work_employment_type_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Company Name</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_company_name_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="work_company_name_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_company_name_r  === '1' ? 'checked' : '' ) : 'checked' }} name="work_company_name_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Designation</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_designation_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="work_designation_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_designation_r  === '1' ? 'checked' : '' ) : 'checked' }} name="work_designation_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Joining Date</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_joining_date_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="work_joining_date_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_joining_date_r  === '1' ? 'checked' : '' ) : 'checked' }} name="work_joining_date_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Worked Till</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_worked_title_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="work_worked_title_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_worked_title_r  === '1' ? 'checked' : '' ) : 'checked' }} name="work_worked_title_r">
                      </div>
                      </td>
                    </tr>
                    <tr>
                      <td>Job Profile</td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_job_profile_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="work_job_profile_h_s">
                      </div>
                      </td>
                      <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->work_job_profile_r  === '1' ? 'checked' : '' ) : 'checked' }} name="work_job_profile_r">
                      </div>
                      </td>
                    </tr>
                  </tbody>
              </table>
            </div>
              </div>
            </div>
        </div>
        </div>
    </div>
    {{--
    <!-- 4th Step here -->
    <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h4>Attach Your Document</h4>
              <div class="card-body">
                <div class="table-responsive">
              <table id="dataTableExample" class="table">
                <thead>
                  <tr>
                    <th>Name of Field</th>
                    <th>Show / Hide Field</th>
                    <th>Required</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Select a Document</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->select_a_document_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="select_a_document_h_s" >
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->select_a_document_r  === '1' ? 'checked' : '' ) : 'checked' }} name="select_a_document_r" >
                    </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Upload Your Document</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->upload_your_document_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="upload_your_document_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->upload_your_document_r  === '1' ? 'checked' : '' ) : 'checked' }} name="upload_your_document_r">
                    </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
              </div>
            </div>
        </div>
        </div>
    </div>--}}
    <!-- extra Step here -->

    <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h4>Required Documents</h4>
              @if(isset($form->docs))
              <p>{{ $form->docs }}</p>
              @endif
              <div class="card-body">
                <div class="d-flex">
                  <div class="dropdown col mx-2">
                    <label for="docs" aria-labelledby="">Documents</label>
                    <select name="docs[]" id="docs" multiple class="col mx-2 form-select select-box-global filtter w-100"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                <option class="dropdown-item" value='' >Please Select</option>
                @foreach(\App\Models\Document::$documentTypes as $k => $v)
                    @php
                        $selected = isset($form) && $form->docs ? in_array($k, json_decode($form->docs)) : false;
                    @endphp
                    <option class="dropdown-item" value="{{$k}}" {{ $selected ? 'selected' : '' }}>{{ ucwords($v) }}</option>
                @endforeach
            </select>
            
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
    
    <!-- 5th Step here -->
    <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h4>More Details</h4>
              <div class="card-body">
                <div class="table-responsive">
              <table id="dataTableExample" class="table">
                <thead>
                  <tr>
                    <th>Name of Field</th>
                    <th>Show / Hide Field</th>
                    <th>Required</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>More Details About Your Document</td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->more_details_about_your_document_h_s  === '1' ? 'checked' : '' ) : 'checked' }} name="more_details_about_your_document_h_s">
                    </div>
                    </td>
                    <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="" {{isset($form) ? ($form->more_details_about_your_document_r  === '1' ? 'checked' : '' ) : 'checked' }} name="more_details_about_your_document_r">
                    </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
              </div>
            </div>
        </div>
        </div>
    </div>
    <div class="col-sm-12">
      <div class="mb-3">
          <input type="hidden" name="scholarship_id" value="{{$scholarship->id}}"/>
          <input type="hidden" name="user_id" value="{{auth()->user()->id}}"/>
          @csrf
            <button type="submit" class="btn btn-primary submit">Submit</button>
      </div>
    </div><!-- Col -->
</form>
@endsection

@push('plugin-scripts')
  <!-- Plugin js import here -->
@endpush

@push('custom-scripts')
  <!-- <script src="{{ asset('assets/js/blackpage.js') }}"></script> -->
  <!-- Custom js here -->
@endpush