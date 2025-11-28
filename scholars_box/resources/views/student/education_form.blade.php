<div class="student-personal-form">
    <div class="row">
        <?php
        $state = \App\Models\CountryData\State::whereStatus('active')->get(); 
        $district = \App\Models\CountryData\District::whereStatus('active')->get(); 
        ?>
        @foreach ($educationDetails as $index => $educationDetail)
            <form id="userEducationDetailUpdateForm_{{ $index }}" data-id="{{ $educationDetail->id }}">
                @csrf
                <div class="accordion-item one">
                    <h3 class="accordion-header h3-title" id="headingInsideOne">
                        <button class="accordion-button one collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseInsideOne{{ $index }}" aria-expanded="false"
                            aria-controls="collapseInsideOne{{ $index }}">
                            Education - {{ ucwords($educationDetail->level)  }} <span class="icon"><i class="fa fa-angle-left"
                                    aria-hidden="true"></i></span>
                        </button>
                    </h3>
                    <div id="collapseInsideOne{{ $index }}" class="accordion-collapse collapse"
                        aria-labelledby="headingInsideOne" data-bs-parent="#headingInsideOne">
                        <div class="accordion-body row">
                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Degree*</label>
                                            <select name="level" class="form-input-one educationLevel">
                                                <option value="">Select Degree</option>
                                                @foreach (\App\Models\EducationDetail::DEGREES as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ $educationDetail->level == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                                <option value="other"
                                                    {{ $educationDetail->level == 'other' ? 'selected' : '' }}>
                                                    Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-box-one">
                                        <label>
                                            <input name="is_education_pursuing" id="is_education_pursuing_{{$index}}"
                                                {{ $educationDetail->pursuing ? 'checked' : '' }} type="checkbox">
                                            If Currently pursuing
                                        </label>
                                    </div>

                                    <div class="col-md-6 otherLevelInput"
                                        style="{{ $educationDetail->level === 'other' ? 'display: block;' : 'display: none;' }}">
                                        <div class="form-box-one">
                                            <label>Custom Degree*</label>
                                            <input type="text" name="other_level" class="form-input-one"
                                                placeholder="{{ $educationDetail->other_level ?? '' }}"
                                                value="{{ $educationDetail->other_level ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Institute/University*</label>
                                            <?php
                                            $sortedInstitutes = \App\Models\EducationDetail::INSTITUTES;
                                            
                                            $otherInstitute = 'Other';
                                            $otherKey = array_search($otherInstitute, $sortedInstitutes);
                                    
                                            if ($otherKey !== false) {
                                                unset($sortedInstitutes[$otherKey]);
                                            }
                                    
                                            asort($sortedInstitutes);
                                    
                                            if ($otherKey !== false) {
                                                $sortedInstitutes = [$otherKey => $otherInstitute] + $sortedInstitutes;
                                            }
                                            ?>
                                            <select name="education_institute" class="form-input-one educationInstitute" onchange="acclinghideshowfunction(this)">
                                                <option value="">Select Institute</option>
                                                @foreach ($sortedInstitutes as $key => $value)
                                                    <option value="{{ $key }}" {{ $educationDetail->institute_name == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 {{($educationDetail->institute_name == 'Other') ? '' : 'd-none' }} other_university_dynamic">
                                        <div
                                            class="form-box-one">
                                            <label>Other Institute/University/School</label>
                                            <input required
                                                type="text"
                                                name="education_institute_other"
                                                class="form-input-one"
                                                placeholder="Institute/University"
                                                value="{{$educationDetail->education_institute_other ?? ''}}" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Type of Institute*</label>
                                            <select name="education_institute_type" class="form-input-one">
                                                <option value="">Select Type of Institute</option>
                                                <option value="government_aided"
                                                    {{ $educationDetail->institute_type == 'government_aided' ? 'selected' : '' }}>
                                                    Government Aided
                                                </option>
                                                <option value="private_institute"
                                                    {{ $educationDetail->institute_type == 'private_institute' ? 'selected' : '' }}>
                                                    Private Institute
                                                </option>
                                                <option value="public_aided"
                                                    {{ $educationDetail->institute_type == 'public_aided' ? 'selected' : '' }}>
                                                    Public Aided
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label for="education_state_{{$index}}">State*</label>
                                            <select name="state_id" id="education_state_{{$index}}" class="form-input-one" >
                                                <option value="">Select State</option>
                                                @foreach($state as $s)
                                                <option value="{{$s->name}}" data-val="{{$s->id}}" {{ ($educationDetail->state??'0') == $s->name ? 'selected' : '' }} >{{$s->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label for="education_district_{{$index}}">District*</label>
                                            <select name="education_institute_district" id="education_district_{{$index}}" class="form-input-one" {{$educationDetail->district??''}}>
                                                <option value="" data-state="0" >Select District</option>
                                                @foreach($district as $s)
                                                <option value="{{$s->name}}" {{ ($educationDetail->district??0) == $s->name ? 'selected' : ''}} data-state="{{$s->state_id}}" >{{$s->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <script>
                                        document.getElementById('education_state_{{$index}}').addEventListener('change', function() {
                                            const educationState{{$index}} = this.options[this.selectedIndex].getAttribute('data-val');
                                            // document.getElementById('education_district_{{$index}}').selectedIndex = 0;
                                            document.querySelectorAll('#education_district_{{$index}} option').forEach(function(option) {
                                                if((option.getAttribute('data-state') === educationState{{$index}}) || (option.getAttribute('data-state') == 0)){
                                                    option.style.visibility = 'visible';
                                                    option.style.display = 'block';
                                                } else {
                                                    option.style.visibility = 'hidden';
                                                    option.style.display = 'none';
                                                }
                                            });
                                        });
                                        document.getElementById('education_state_{{$index}}').dispatchEvent(new Event('change'));
                                    </script>
                                 <div class="col-md-6">
                                    <div class="form-box-one">
                                        <label>Course Name*</label>
                                        <?php
                                        $sortedCourses = \App\Models\EducationDetail::COURCE;
                                        
                                        $otherCourse = 'Others';
                                        $otherKey = array_search($otherCourse, $sortedCourses);
                                
                                        if ($otherKey !== false) {
                                            unset($sortedCourses[$otherKey]);
                                        }
                                
                                        asort($sortedCourses);
                                
                                        if ($otherKey !== false) {
                                            $sortedCourses = [$otherKey => $otherCourse] + $sortedCourses;
                                        }
                                        ?>
                                        <select name="education_course_name" class="form-input-one" onchange="acclinghideshowfunctioncourse(this)">
                                            <option value="">Select Course Name</option>
                                            @foreach ($sortedCourses as $key => $value)
                                                <option value="{{ $key }}" {{ $educationDetail->course_name == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                    <div class="col-md-6 {{($educationDetail->course_name == 'Others') ? '' : 'd-none' }} other_course_dynamic">
                                        <div
                                            class="form-box-one">
                                            <label>Other Education Course</label>
                                            <input required
                                                type="text"
                                                name="education_course_other"
                                                class="form-input-one"
                                                placeholder="Other Course"
                                                value="{{$educationDetail->education_course_other ?? ''}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Specialisation*</label>
                                            <input type="text" name="education_specialisation" class="form-input-one"
                                                placeholder="Specialisation"
                                                value="{{ $educationDetail->specialisation ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Grading System*</label>
                                            <select name="education_grade_type" class="form-input-one">
                                                <option value="">Select Grading System</option>
                                                <option value="10_point_grading_system_cgpa"
                                                    {{ $educationDetail->grade_type === '10_point_grading_system_cgpa' ? 'selected' : '' }}>
                                                    10 point grading
                                                    system CGPA</option>
                                                <option value="%_marks_out_of_100"
                                                    {{ $educationDetail->grade_type === '%_marks_out_of_100' ? 'selected' : '' }}>
                                                    % marks out of 100
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Percentage
                                                scored/CGPA *</label>
                                            <input type="text" name="education_grade" class="form-input-one"
                                                placeholder="Percentage scored/CGPA"
                                                value="{{ $educationDetail->grade ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label for="education_start_date{{$index}}" >From *</label>
                                            <input type="date" name="education_start_date" id="education_start_date{{$index}}"
                                                class="form-input-one date" placeholder="From"
                                                value="{{ $educationDetail->start_date ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label for="education_end_date{{$index}}">To *</label>
                                            <input type="date" name="education_end_date" id="education_end_date{{$index}}"
                                                class="date form-input-one" placeholder="To" value="{{ $educationDetail->end_date ?? '' }}"
                                                max="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <script>

                                        document.getElementById('education_start_date{{$index}}').addEventListener('change', function () {
                                            var education_start_date{{$index}} = new Date(this.value);
                                            document.getElementById('education_end_date{{$index}}').min = this.value;
                                        
                                            var education_end_date{{$index}} = new Date(document.getElementById('education_end_date{{$index}}').value);
                                            if (education_end_date{{$index}} < education_start_date{{$index}}) {
                                                document.getElementById('education_end_date{{$index}}').value = '';
                                            }
                                        });
                                        
                                        document.getElementById('education_end_date{{$index}}').addEventListener('change hover', function (ev) {
                                            var condication_check = document.getElementById('is_education_pursuing_{{$index}}').value;
                                            if(condication_check != "checked"){
                                                ev.preventDefault();
                                            }
                                            var education_start_date{{$index}} = new Date(document.getElementById('education_start_date{{$index}}').value);
                                            var education_end_date{{$index}} = new Date(this.value);
                                        
                                            if (education_end_date{{$index}} < education_start_date{{$index}}) {
                                                this.value = '';
                                            }
                                        });
                                        
                                        document.addEventListener('change hover', function (event) {
                                            if (event.target.id == 'is_education_pursuing_{{$index}}') {
                                                var education_end_date{{$index}} = document.getElementById('education_end_date{{$index}}');
                                                education_end_date{{$index}}.disabled = event.target.checked;
                                                if (event.target.checked) {
                                                    education_end_date{{$index}}.disabled = true;
                                                    education_end_date{{$index}}.value = '';
                                                    education_end_date{{$index}}.readOnly = true;
                                                } else {
                                                    education_end_date{{$index}}.disabled = false;
                                                    education_end_date{{$index}}.readOnly = false;
                                                }
                                            }
                                        });
                                          
                                    </script>
                                    <div class="col-md-12">
                                        <div class="form-box-one mb-0">
                                            <button
                                                data-form-id="userEducationDetailUpdateForm_{{ $index }}"
                                                data-id="{{ $educationDetail->id }}"
                                                onclick="updateEducationDetail(this,event)" type="button"
                                                class="sec-btn-one"><span>UPDATE</span></button>
                                                
                                            <button onclick="deleteeducation({{$educationDetail->id}})" type="button" class="sec-btn-one"  ><span>DELETE</span></button>                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </form>
        @endforeach
    </div>

</div>
<script src="{{ asset('frontend/js/jquery.min.js') }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js"
    integrity="sha512-lOrm9FgT1LKOJRUXF3tp6QaMorJftUjowOWiDcG5GFZ/q7ukof19V0HKx/GWzXCdt9zYju3/KhBNdCLzK8b90Q=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.css"
    integrity="sha512-0p3K0H3S6Q4bEWZ/WmC94Tgit2ular2/n0ESdfEX8l172YyQj8re1Wu9s/HT9T/T2osUw5Gx/6pAZNk3UKbESw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

<script>
    Noty.overrideDefaults({
        type: 'alert',
        layout: 'topRight',
        theme: 'mint',
        timeout: 2000
    });
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">

<script>
    $(document).ready(function() {
        $(".date").flatpickr({
            enableTime: false,
            altInput: true,
        });
    });
</script>
<style>
    input.form-input-one.date.form-control.input {
        background: transparent !important;
    }
</style>

<meta name="asd" content="{{ csrf_token() }}">
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name=asd]').attr('content')
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
<script>
    $('.educationLevel').each(function() {
        var $this = $(this); 
        var $otherLevelInput = $this.closest('.row').find(
        '.otherLevelInput'); 

        if ($this.val() === 'other') {
            $otherLevelInput.show();
        } else {
            $otherLevelInput.hide();
        }
    });
    
    $('.educationLevel').change(function() {
        var $this = $(this);
        var $otherLevelInput = $this.closest('.row').find('.otherLevelInput');
        if ($this.val() === 'other') {
            $otherLevelInput.show();
        } else {
            $otherLevelInput.hide();
        }
    });

    function fetchAndUpdateDynamicForm() {
        $.ajax({
            
            url: "{{ route('Student.getEducationDetail') }}",
            type: 'GET',
            success: function(htmlResponse) {
                $('#dynamic_forms').html(htmlResponse);
            },
            error: function(jqXHR) {
                new Noty({
                    type: 'error',

                    text: 'Failed to load the new form.'
                }).show();
            }
        });
    }
    function deleteeducation(id){
        $.ajax({
            url: '/student/delete-education-detail' + '/' + id,
            type: 'POST',
            dataType: 'json', 
            success: function(response) {
                fetchAndUpdateDynamicForm();
                new Noty({
                    text: 'Education details deleted successfully!',
                    timeout: 3000
                }).show();
            },
            error: function(jqXHR) {
                console.error(jqXHR.error);
            }
        });
    }
    function acclinghideshowfunction(selectElement) {
        var row = selectElement.closest('.row');
        var otherLevelInput = row.querySelector('.other_university_dynamic');
        if (selectElement.value == 'Other') {
            otherLevelInput.classList.remove('d-none');
        } else {
            otherLevelInput.classList.add('d-none');
        }
    }
    function acclinghideshowfunctioncourse(selectElement) {
        var row = selectElement.closest('.row');    
        var otherLevelInput = row.querySelector('.other_course_dynamic');
        if (selectElement.value == 'Others') {
            otherLevelInput.classList.remove('d-none');
        } else {
            otherLevelInput.classList.add('d-none');
        }
    }
    function updateEducationDetail(element, event) {
        
        event.preventDefault();
        var formID = $(element).data('form-id');
        var educationDetailId = $(element).data('id');
        var form = `#${formID}`;
        var formData = $(form).serialize();
        
        $.ajax({
            url: '/student/update-education-detail' + '/' + educationDetailId,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                fetchAndUpdateDynamicForm();
                new Noty({
                    text: 'Education details updated successfully!',
                    timeout: 3000
                }).show();
            },
            error: function(jqXHR) {
                console.error(jqXHR.error);
            }
        });
    }
</script>
