<div class="student-personal-form" data-wow-delay=".4s">

    <div class="row">
        <!-- Loop through each educationDetail record -->
        @foreach ($employmentDetails as $index => $employmentDetail)
            <form id="userWorkDetailUpdateFormOne_{{ $index }}" data-id="{{ $employmentDetail->id }}">

                <div class="accordion-item one">
                    <h3 class="accordion-header h3-title" id="headingInsideOne">
                        <button class="accordion-button one collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseInsideOne{{ $index }}" aria-expanded="false"
                            aria-controls="collapseInsideOne{{ $index }}">
                            Work Experience - {{ ucwords($employmentDetail->designation) }} <span class="icon"><i class="fa fa-angle-left"
                                    aria-hidden="true"></i></span>
                        </button>
                    </h3>
                    <div id="collapseInsideOne{{ $index }}" class="accordion-collapse collapse"
                        aria-labelledby="headingInsideOne" data-bs-parent="#headingInsideOne">
                        <div class="accordion-body row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <h6>Total Experience (includes paid/unpaid
                                            internships, immersion programs, NCC/NSS
                                            etc.)</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Employment Type
                                                *</label>
                                            <select name="employment_type" class="form-input-one">
                                                <option value="">Select Employment Type</option>
                                                <option value="full_time"
                                                    @if (optional($employmentDetail)->employment_type == 'full_time') selected @endif>
                                                    Full time</option>
                                                <option value="internship"
                                                    @if (optional($employmentDetail)->employment_type == 'internship') selected @endif>
                                                    Internship</option>
                                                <option value="part_time"
                                                    @if (optional($employmentDetail)->employment_type == 'part_time') selected @endif>
                                                    Part time</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Company Name*</label>
                                            <input type="text"
                                                value="{{ optional($employmentDetail)->company_name }}"
                                                name="company_name" class="form-input-one" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Designation*</label>
                                            <input type="text" value="{{ optional($employmentDetail)->designation }}"
                                                name="designation" class="form-input-one" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Joining Date*</label>
                                            <input type="date"
                                                value="{{ optional($employmentDetail)->joining_date }}"
                                                name="joining_date" id="joining_date{{$index}}" class="date form-input-one" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Working Currently
                                                </label>
                                            <select name="working_currently" class="form-input-one" onchange="toggleWorkedTill(this)">
                                                <option value="">Select Working Currently</option>
                                                <option value="1"
                                                    @if (optional($employmentDetail)->working_currently == '1') selected @endif>
                                                    Yes</option>
                                                <option value="0"
                                                    @if (optional($employmentDetail)->working_currently == '0') selected @endif>
                                                    No</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 " id="workedTillContainer" @if (optional($employmentDetail)->working_currently == '1') style="display: none;" @endif >
                                        <div class="form-box-one">
                                            <label>Worked Till*</label>
                                            <input type="date" name="end_date"
                                                value="{{ optional($employmentDetail)->end_date }}"
                                                id="end_date{{$index}}"
                                                class="date form-input-one" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-box-one">
                                            <label>Job Profile*</label>
                                            <input type="text" name="job_role"
                                                value="{{ optional($employmentDetail)->job_role }}"
                                                class="form-input-one" placeholder="">
                                        </div>
                                    </div>

                                    <script>

                                        document.getElementById('joining_date{{$index}}').addEventListener('change', function () {
                                            var joining_date{{$index}} = new Date(this.value);
                                            document.getElementById('end_date{{$index}}').min = this.value;
                                        
                                            var end_date{{$index}} = new Date(document.getElementById('end_date{{$index}}').value);
                                            if (end_date{{$index}} < joining_date{{$index}}) {
                                                document.getElementById('end_date{{$index}}').value = '';
                                            }
                                        });
                                        
                                        document.getElementById('end_date{{$index}}').addEventListener('change hover', function (ev) {
                                            
                                            var joining_date{{$index}} = new Date(document.getElementById('joining_date{{$index}}').value);
                                            var end_date{{$index}} = new Date(this.value);
                                        
                                            if (end_date{{$index}} < joining_date{{$index}}) {
                                                this.value = '';
                                            }
                                        });

                                    </script>

                                    <div class="col-12">
                                        <div class="form-box-one mb-0">
                                            <button data-form-id="userWorkDetailUpdateFormOne_{{ $index }}"
                                                data-id="{{ $employmentDetail->id }}"
                                                onclick="updateWorkExperience(this,event)" type="button"
                                                class="sec-btn-one"><span>UPDATE</span></button>
                                            <button onclick="deletework({{$employmentDetail->id}})" type="button" class="sec-btn-one"  ><span>DELETE</span></button>                                          
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
   function fetchAndUpdateDynamicFormExperience() {
        $.ajax({
            
            url: "{{ route('Student.getEmployementDetails') }}",
            type: 'GET',
            success: function(htmlResponse) {
                $('#dynamic_forms_work_experience').html(htmlResponse);
            },
            error: function(jqXHR) {
                new Noty({
                    type: 'error',
                    text: 'Failed to load the new form.'
                }).show();
            }
        });
    }
    function deletework(id){
        $.ajax({
            url: '/student/delete-work-detail' + '/' + id,
            type: 'POST',
            dataType: 'json', 
            success: function(response) {
                fetchAndUpdateDynamicFormExperience();
                new Noty({
                    text: 'Work details deleted successfully!',
                    timeout: 3000
                }).show();
            },
            error: function(jqXHR) {
                console.error(jqXHR.error);
            }
        });
    }
    function updateWorkExperience(element, event) {
        event.preventDefault(); // Prevent default submit
        var formID = $(element).data('form-id');
        var educationDetailId = $(element).data('id'); // Fetching the data-id attribute
        var formData = $(`#${formID}`).serialize(); // Serialize form data
        $.ajax({
            url: '/student/update-work-detail' + '/' + educationDetailId,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
               new Noty({
                    text: 'Work details updated successfully!',
                    timeout: 3000
                }).show();
                fetchAndUpdateDynamicFormExperience();
            },
            error: function(jqXHR) {
                console.error(jqXHR.error);
            }
        });
    }
</script>