<div class="row">
    @if(isset($scholarships[0]->apply_now->education_occupation_h_s) && ($scholarships[0]->apply_now->education_occupation_h_s =='1'))
    <div class="col-md-3">
        <div class="form-box-one">
            <label>Occupation
                {{$scholarships[0]->apply_now->education_occupation_r == '1'?'*':''}}</label>
            <select name="occupation" class="form-input-one" {{$scholarships[0]->apply_now->education_occupation_r == '1'?'required':''}}>
                <option value="">Select Occupation</option>
                <option value="a_school_student"
                    {{ optional($user->student)->occupation === 'a_school_student' ? 'selected' : '' }}>
                    A School student</option>
                <option value="pursuing_bachelors"
                    {{ optional($user->student)->occupation === 'pursuing_bachelors' ? 'selected' : '' }}>
                    Pursuing Bachelors</option>
                <option value="pursuing_masters"
                    {{ optional($user->student)->occupation === 'pursuing_masters' ? 'selected' : '' }}>
                    Pursuing masters</option>
                <option value="pursuing_phd"
                    {{ optional($user->student)->occupation === 'pursuing_phd' ? 'selected' : '' }}>
                    Pursuing PhD.</option>
                <option value="pursuing_itis_diploma_polytechnic"
                    {{ optional($user->student)->occupation === 'pursuing_itis_diploma_polytechnic' ? 'selected' : '' }}>
                    Pursuing
                    ITIs/Diploma/Polytechnic
                </option>
                <option value="preparing_for_competitive_exams"
                    {{ optional($user->student)->occupation === 'preparing_for_competitive_exams' ? 'selected' : '' }}>
                    Preparing for competitive exams
                </option>
                <option value="working_professional"
                    {{ optional($user->student)->occupation === 'working_professional' ? 'selected' : '' }}>
                    Working Professional</option>
                <option value="others"
                    {{ optional($user->student)->occupation === 'others' ? 'selected' : '' }}>
                    Others</option>
            </select>
        </div>
    </div>
    <div class="col-md-12">
        <h6 class="center-heading">Graduation
        </h6>
    </div>

    <div class="col-md-12">
        <div class="form-box-one">
            <label>
                <input name="is_graduation_pursuing"
                   {{-- checked="{{ optional(optional($user->student)->educationDetails->where('level', 'graduation')->first())->pursuing? 'checked': '' }}"--}}
                    type="checkbox"  id="is_graduation_pursuing"
                    {{-- {{ optional(optional($user->student)->educationDetails->where('level', 'graduation')->first())->pursuing? 'checked': '' }} --}}>
                If Currently pursuing
            </label>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $('#is_graduation_pursuing').on('change', function(){
                var is_graduation_pursuing = $(this).is(':checked');
                if(is_graduation_pursuing){
                    $('#graduation_end_date').val('');
                    $('#graduation_end_date').prop('required',false);
                }
            });
        });
    </script>
    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_institute_h_s) && ($scholarships[0]->apply_now->education_graduation_institute_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label>Institute/University{{$scholarships[0]->apply_now->education_graduation_institute_r == '1'?'*':''}}</label>
            <input type="text" name="graduation_institute" class="form-input-one"
                placeholder="Institute/University"
                value="{{ optional($user->student)->educationDetails->firstWhere('level', 'graduation')->institute_name ?? '' }}"  {{$scholarships[0]->apply_now->education_graduation_institute_r == '1'?'required':''}}>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_type_of_institute_h_s) && ($scholarships[0]->apply_now->education_graduation_type_of_institute_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label>Type of
                Institute{{$scholarships[0]->apply_now->education_graduation_type_of_institute_r == '1'?'*':''}}</label>
            <select name="graduation_institute_type" class="form-input-one" {{$scholarships[0]->apply_now->education_graduation_type_of_institute_r == '1'?'required':''}}>
                <option value="">Select Type of Institute</option>
                <option value="government_aided"
                    {{ optional($user->student)->educationDetails->firstWhere('level', 'graduation') === 'government_aided' ? 'selected' : '' }}>
                    Government Aided
                </option>
                <option value="private_institute"
                    {{ optional($user->student)->educationDetails->firstWhere('level', 'graduation') === 'private_institute' ? 'selected' : '' }}>
                    Private Institute
                </option>
                <option value="public_aided"
                    {{ optional($user->student)->educationDetails->firstWhere('level', 'graduation') === 'public_aided' ? 'selected' : '' }}>
                    Public Aided
                </option>
            </select>
        </div>
    </div>

    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_state_h_s) && ($scholarships[0]->apply_now->education_graduation_state_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label>State{{$scholarships[0]->apply_now->education_graduation_state_r == '1'?'*':''}}</label>
            <select name="graduation_institute_state" id="graduation_institute_state" class="form-input-one" {{$scholarships[0]->apply_now->education_graduation_state_r == '1'?'required':''}}>
                <option value="">Select State</option>
                @foreach($state as $s)
                <option value="{{$s->name}}" data-val="{{$s->id}}" {{ (optional($user->student)->educationDetails->firstWhere('level', 'graduation') === $s->name) ? 'selected' : '' }} >{{$s->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_district_h_s) && ($scholarships[0]->apply_now->education_graduation_district_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label for="graduation_institute_district" >District{{$scholarships[0]->apply_now->education_graduation_district_r == '1'?'*':''}}</label>
            <select id="graduation_institute_district" name="graduation_institute_district" class="form-input-one" 
            {{$scholarships[0]->apply_now->education_graduation_district_r == '1' ? 'required=true' : '' }}>
                <option value="0" >Select District</option>
                @foreach($district as $s)
                <option value="{{$s->name}}" data-state="{{$s->state_id}}" >{{$s->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_course_name_h_s) && ($scholarships[0]->apply_now->education_graduation_course_name_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label>Course Name{{$scholarships[0]->apply_now->education_graduation_course_name_r == '1'?'*':''}}</label>
            <input type="text" name="graduation_course_name" class="form-input-one"
                placeholder="Course Name"
                value="{{ optional($user->student)->educationDetails->firstWhere('level', 'graduation')->course_name ?? '' }}" {{$scholarships[0]->apply_now->education_graduation_course_name_r == '1'?'required':''}}>
        </div>
    </div>

    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_specialisation_h_s) && ($scholarships[0]->apply_now->education_graduation_specialisation_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label>Specialisation {{$scholarships[0]->apply_now->education_graduation_specialisation_r == '1'?'*':''}}</label>
            <input type="text" name="graduation_specialisation" class="form-input-one"
                placeholder="Specialisation"
                value="{{ optional($user->student)->educationDetails->firstWhere('level', 'graduation')->specialisation ?? '' }}" {{$scholarships[0]->apply_now->education_graduation_specialisation_r == '1'?'required':''}}>
        </div>
    </div>

    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_grading_system_h_s) && ($scholarships[0]->apply_now->education_graduation_grading_system_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label>Grading System{{$scholarships[0]->apply_now->education_graduation_grading_system_r == '1'?'*':''}}</label>
            <select name="graduation_grade_type" class="form-input-one" {{$scholarships[0]->apply_now->education_graduation_grading_system_r == '1'?'required':''}}>
                <option value="">Select Grading System</option>
                <option value="10_point_grading_system_cgpa"
                    {{ optional($user->student)->educationDetails->firstWhere('level', 'graduation') === '10_point_grading_system_cgpa' ? 'selected' : '' }}>
                    10 point grading
                    system CGPA</option>
                <option value="%_marks_out_of_100"
                    {{ optional($user->student)->educationDetails->firstWhere('level', 'graduation') === '%_marks_out_of_100' ? 'selected' : '' }}>
                    % marks out of 100
                </option>
            </select>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_percentage_scored_cgpa_h_s) && ($scholarships[0]->apply_now->education_graduation_percentage_scored_cgpa_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label>Percentage scored/CGPA {{$scholarships[0]->apply_now->education_graduation_percentage_scored_cgpa_r == '1'?'*':''}}</label>
            <input type="text" name="graduation_grade" class="form-input-one"
                placeholder="Percentage scored/CGPA"
                value="{{ optional($user->student)->educationDetails->firstWhere('level', 'graduation')->grade ?? '' }}" {{$scholarships[0]->apply_now->education_graduation_percentage_scored_cgpa_r == '1'?'required':''}}>
        </div>
    </div>

    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_form_h_s) && ($scholarships[0]->apply_now->education_graduation_form_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label>From {{$scholarships[0]->apply_now->education_graduation_form_r == '1'?'*':''}}</label>
            <input type="date" name="date graduation_start_date" id="graduation_start_date"
                class="form-input-one" placeholder="From"
                value="{{ optional($user->student)->educationDetails->firstWhere('level', 'graduation')->start_date ?? '' }}" {{$scholarships[0]->apply_now->education_graduation_form_r == '1'?'required':''}}>
        </div>
    </div>

    @endif
    @if(isset($scholarships[0]->apply_now->education_graduation_to_h_s) && ($scholarships[0]->apply_now->education_graduation_to_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">

            <label>To {{-- {{$scholarships[0]->apply_now->education_graduation_to_r == '1'?'*':''}} --}}</label>
            <input type="date" name="date graduation_end_date" class="form-input-one" id="graduation_end_date"
                placeholder="To"
                value="{{ optional($user->student)->educationDetails->firstWhere('level', 'graduation')->end_date ?? '' }}" {{$scholarships[0]->apply_now->education_graduation_to_r == '1'?'required':''}}>
        </div>
    </div>
    <div class="col-md-12">
        <h6 class="center-heading">Work Experience
        </h6>
    </div>

    <div class="col-md-12 mb-3">
        <h6>Total Experience (includes paid/unpaid internships, immersion programs,
            NCC/NSS etc.)</h6>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->work_employment_type_h_s) && ($scholarships[0]->apply_now->work_employment_type_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">
            <label>Employment Type
                {{$scholarships[0]->apply_now->work_employment_type_r == '1'?'*':''}}</label>
            <select name="employment_type" class="form-input-one" {{$scholarships[0]->apply_now->work_employment_type_r == '1'?'required':''}}>
                <option value="">Select Employment Type</option>
                <option value="full_time"
                    @if (optional($user->student->employmentDetails) === 'full_time') selected @endif>
                    Full time</option>
                <option value="part_time"
                    @if (optional($user->student->employmentDetails) === 'part_time') selected @endif>
                    Part Time</option>
                <option value="internship"
                    @if (optional($user->student->employmentDetails) === 'internship') selected @endif>
                    Internship</option>
            </select>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->work_company_name_h_s) && ($scholarships[0]->apply_now->work_company_name_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">
            <label>Company Name{{$scholarships[0]->apply_now->work_company_name_r == '1'?'*':''}}</label>
            <input type="text"
                value="{{ optional($user->student->employmentDetails)->company_name }}"
                name="company_name" class="form-input-one" placeholder="" {{$scholarships[0]->apply_now->work_company_name_r == '1'?'required':''}}>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->work_designation_h_s) && ($scholarships[0]->apply_now->work_designation_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">
            <label>Designation{{$scholarships[0]->apply_now->work_designation_r == '1'?'*':''}}</label>
            <input type="text"
                value="{{ optional($user->student->employmentDetails)->designation }}"
                name="designation" class="form-input-one" placeholder="" {{$scholarships[0]->apply_now->work_designation_r == '1'?'required':''}}>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->work_joining_date_h_s) && ($scholarships[0]->apply_now->work_joining_date_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">
            <label>Joining Date{{($scholarships[0]->apply_now->work_joining_date_r == '1')?'*':''}}</label>
            <input type="date" id="joining_date"
                value="{{ optional($user->student->employmentDetails)->joining_date }}" 
                name="joining_date" class="date form-input-one" placeholder="" {{$scholarships[0]->apply_now->work_joining_date_r == '1'?'required':''}}>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->work_worked_title_h_s) && ($scholarships[0]->apply_now->work_worked_title_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">
            <label>Worked Till{{$scholarships[0]->apply_now->work_worked_title_r == '1'?'*':''}}</label>
            <input type="date" name="end_date" id="end_date"
                value="{{ optional($user->student->employmentDetails)->end_date }}"
                class="date form-input-one" placeholder="" {{$scholarships[0]->apply_now->work_worked_title_r == '1'?'required':''}}>
        </div>
    </div>
    @endif
    @if(isset($scholarships[0]->apply_now->work_job_profile_h_s) && ($scholarships[0]->apply_now->work_job_profile_h_s=='1'))
    <div class="col-md-3">
        <div class="form-box-one">
            <label>Job Profile{{$scholarships[0]->apply_now->work_job_profile_r == '1'?'*':''}}</label>
            <input type="text" name="job_role"
                value="{{ optional($user->student->employmentDetails)->job_role }}"
                class="form-input-one" placeholder="" {{$scholarships[0]->apply_now->work_job_profile_r == '1'?'required':''}}>
        </div>
    </div>
    @endif
</div>
<script>
    $(document).ready(function(){
        $('#graduation_start_date').on('change', function() {
            var graduation_start_date = new Date($(this).val());
            $('#graduation_end_date').prop('min', $(this).val()); // Set min attribute of toDate input
    
            var graduation_end_date = new Date($('#graduation_end_date').val());
            if (graduation_end_date < graduation_start_date) {
                $('#graduation_end_date').val(''); // Clear the incorrect value
            }
        });
    
        $('#graduation_end_date').on('change', function() {
            var graduation_start_date = new Date($('#graduation_start_date').val());
            var graduation_end_date = new Date($(this).val());
    
            if (graduation_end_date < graduation_start_date) {
                $(this).val(''); // Clear the incorrect value
            }
        });
        $(document).on('change', '#graduation_institute_state', function () {
            const gstate = $('#graduation_institute_state option:selected').data('val'); 
        
            var goptions = $('#graduation_institute_district option');
        
            goptions.each(function () {
                if ($(this).data('state') === gstate) {
                    $(this).css('display', 'block');
                } else {
                   $(this).css('display', 'none');
                }
            });
        });
        $('#joining_date').on('change', function() {
            var joining_date = new Date($(this).val());
            $('#end_date').prop('min', $(this).val());
    
            var end_date = new Date($('#toDate').val());
            if (end_date < joining_date) {
                $('#end_date').val('');
            }
        });
    
        $('#end_date').on('change', function() {
            var joining_date = new Date($('#joining_date').val());
            var end_date = new Date($(this).val());
    
            if (end_date < joining_date) {
                $(this).val(''); // Clear the incorrect value
            }
        });
    });
</script>