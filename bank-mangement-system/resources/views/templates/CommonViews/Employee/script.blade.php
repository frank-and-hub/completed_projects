<script type="text/javascript">
  var date = new Date();
  var today = new Date(date.getFullYear() - 18, date.getMonth(), date.getDate());
  var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  if (sessionStorage.getItem('refreshed')) {
    console.log('Page was refreshed!');
    $('#is_employee').prop('checked', false);
  }
  sessionStorage.setItem('refreshed', 'true');
  $(document).ready(function () {
    $('#customer_detail').validate({
      rules: {
        customer_id: { required: true, customerId: true },
      }, messages: {
        customer_id: {
          required: "Please enter Customer id.",
        },
      },
    });

    $('#branch_id').html();
    $('#email').val('');
    $('#account_no').val('');
    $('#is_employee').on('click', function () {
      if ($(this).prop('checked')) {
        $(this).val('1');
        $('#employee_register').hide();
        $('#customer_code').removeClass('d-none');
        $('#customer_code').show();
        $('#show_customer_detail').show('');
        $('#customer_id').on('keyup', function () {
          $('#employee_register').hide();
        });
      } else {
        $(this).val('');
        $('#employee_register').show();
        $('#customer_id').val('');
        $('#show_customer_detail').val('').hide();
        $('#customer_code').hide();
        var form = document.getElementById('employee_register');
        // Loop through all form elements
        for (var i = 0; i < form.elements.length; i++) {
          var element = form.elements[i];
          // Check if the element is an input field, textarea, or select
          if (element.type !== 'button' && element.type !== 'submit' && element.type !== 'reset') {
            // Clear the value of the element
            element.value = '';
            // Remove the disabled attribute
            element.disabled = false;
            element.readOnly = false;
            // For select elements, reset the selected option
            if (element.type === 'select-one' || element.type === 'select-multiple') {
              element.selectedIndex = -1;
            }
          }
        }
        $('#photo-preview').attr('src', "{{ url('/') }}/asset/images/user.png");
        $('#branch').prepend($('#branch_id').find('option[value=""]')).val('');
        $('#company_id').prepend($('#company_id').find('option[value=""]')).val('');
      }
      // $('#date_application').val($('#create_application_date').val());
      setDatePickerForStartDate();
    });
    var urlsetting = $('#comman').val();
    var targetRoute;
    if (urlsetting == 1) {
      targetRoute = "{!! route('admin.employeeDetail') !!}";
      exporturl = "{!!route('admin.designationByCategory')!!}";
      SSB = "{!!route('admin.check.ssb.account')!!}";
      DataGet = "{!!route('admin.designationDataGet')!!}";
    } else {
      targetRoute = "{!! route('branch.employeeDetail') !!}";
      exporturl = "{!!route('branch.designationByCategory')!!}";
      SSB = "{!!route('branch.check.ssb.account')!!}";
      DataGet = "{!!route('branch.designationDataGet')!!}";
    }
    $(document).on('click keyup', '#customer_data', function () {
      var code = $(customer_id).val();
      var maxLength = 12;
      var customerId = $(customer_id).val().trim();
      $('#category').prepend($('#category').find('option[value=""]')).val('');
      $('#designation').prepend($('#designation').find('option[value=""]')).val('');
      $('#salary').val('');

      if (customerId.length === maxLength) {
        $('#show_customer_detail').html('');
        if (code != '') {
          $.ajax({
            type: "POST",
            url: targetRoute,
            dataType: 'JSON',
            data: {
              'code': code,
            },
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
              
              if (response.value == 1) {
                swal('Warning', response.msg_veryCustomerId, 'error');
                $('#employee_register').hide();
                return false;
              }
              if (response.veryCustomer != 1) {
                $('#employee_register').hide();
                $('.title_hide').hide();
                $('#employee_register').show();
                $('.title_hide').show();
                var optionToDisable = document.getElementById('photo');
                if (response.data.image != 'noimage') {
                  $('#photo-preview').attr('src', response.data.image);
                } else {
                  $('#photo-preview').attr('src', "{{ url('/') }}/asset/images/user.png");
                }
                $('#hidden_photo').val(response.data.image);
                $('#hidden_image').val(response.data.member.photo);
                if (response.data.image != 'noimage') {
                  optionToDisable.disabled = true;
                } else {
                  optionToDisable.disabled = false;
                }
                $('#applicant_name').val((response.data.member.first_name !== null ? response.data.member.first_name : '') + (response.data.member.last_name !== null ? ' ' + response.data.member.last_name : '')).prop('readonly', response.data.member.first_name !== null);
                $('#email').val(response.data.member.email).prop('readonly', response.data.member.email !== null);
                $('#company_register_date').prop('readonly', true);
                $('#mobile_no').val(response.data.member.mobile_no).prop('readonly', response.data.member.mobile_no !== null);
                var datetimeDOB = response.data.member.dob;
                var formatdD = moment(datetimeDOB).format('DD/MM/YYYY');
                $('#dob').val(formatdD).prop('readonly', formatdD !== null);
                // $('#select_date').datepicker({'startDate': response.data.member.created_at});
                $('#date_application').val(response.data.member.created_at);
                if (formatdD) {
                  $('#dob').prop('readonly', true);
                  $('#dob').datepicker("destroy");
                } else {
                  $('#dob').prop('readonly', false);
                }
                if (response.data.member.gender == 1) {
                  $('#gender_male').prop('checked', true);
                  $('#gender_female').prop('disabled', true);
                  $('#gender_other').prop('disabled', true);
                }
                else if (response.data.member.gender == 0) {
                  $('#gender_female').prop('checked', true);
                  $('#gender_other').prop('disabled', true);
                  $('#gender_male').prop('disabled', true);
                }
                else if (response.data.member.gender == 2) {
                  $('#gender_other').prop('checked', true);
                  $('#gender_female').prop('disabled', true);
                  $('#gender_male').prop('disabled', true);
                }
                $('#guardian_name').val(response.data.member.father_husband).prop('readonly', response.data.member.father_husband !== null);;
                var CustomerID = $('#customer_id').val();
                $('#empID').val(CustomerID);
                $('#mother_name').val(response.data.member.mother_name).prop('readonly', response.data.member.mother_name !== null);
                $('#guardian_number').val(response.data.member.member_nominee_details.mobile_no);
                if (response.data.member.marital_status == 1) {
                  $('#married').prop('checked', true);
                  $('#un_married').prop('disabled', true);
                } else if (response.data.member.marital_status == 0) {
                  $('#un_married').prop('checked', true);
                  $('#married').prop('disabled', true);
                  $('#divorced').prop('disabled', true);
                }
                var datetimeDate = response.data.member.re_date;
                var formatdDate = moment(datetimeDate).format('DD/MM/YYYY');
                $('.register_date').val(formatdDate);
                $('#permanent_address').val(response.data.member.member_id_proof.first_address);
                $('#current_address').val(response.data.member.member_id_proof.second_address);
                
               // 5965CI000529
                var ssb_account = response.savingAccount;
                $.each(ssb_account, function(index, value) { 
                    $('#ssb_account').val(value.account_no).prop('readonly', value.account_no === null);
                    $('#company_id').val(value.company_id).change();
                    $('#branch').val(value.branch_id);
                    if (value.account_no) {
                        var datetimeDateSSB = value.register_date;
                        var formatdDateSB = moment(datetimeDateSSB).format('DD/MM/YYYY');
                        $('#ssb_account_date').val(formatdDateSB).prop('readonly', true);
                        $('#ssb_account_id').val(value.id).prop('readonly', true);
                    }
                });
                var bankDetails = response.data.member.member_bank_details[0];
                if (bankDetails != null) {
                  $('#bank_name').val(bankDetails.bank_name);
                  $('#bank_address').val(bankDetails.address);
                  $('#bank_address').val(bankDetails.address);
                  $('#account_no').val(bankDetails.account_no);
                  $('#cAccount_no').val(bankDetails.account_no);
                  $('#ifsc_code').val(bankDetails.ifsc_code);
                }
                if (response.data.member.member_id_proof.first_id_type_id == 5) {
                  $('#pen_number').val(response.data.member.member_id_proof.first_id_no).prop('readonly', response.data.member.member_id_proof.first_id_no !== null);
                }
                if (response.data.member.member_id_proof.second_id_type_id == 3) {
                  $('#aadhar_number').val(response.data.member.member_id_proof.second_id_no).prop('readonly', response.data.member.member_id_proof.second_id_no !== null);
                }
                if (response.data.member.member_nominee_details.relation == 3) {
                  $('#mother_name').val(response.data.member.member_nominee_details.name).prop('readonly', response.data.member.member_nominee_details.name !== null);
                }
              }
              else {
                swal('Warning', 'Employee already exist with selected customer ID!', 'error');


              }
            },
            error: function (error) {
              console.error('AJAX error:', error);
            }
          });
        }
      } else {
        $('#show_customer_detail').html(
          '<div style="color: red;">Please provide a valid customer ID!</div>'
        );

        $('#employee_register').hide();
        var form = document.getElementById('employee_register');
        // Loop through all form elements
        for (var i = 0; i < form.elements.length; i++) {
          var element = form.elements[i];
          // Check if the element is an input field, textarea, or select
          if (element.type !== 'button' && element.type !== 'submit' && element.type !== 'reset') {
            // Clear the value of the element
            element.value = '';
            // Remove the disabled attribute
            element.disabled = false;
            element.readOnly = false;
            // For select elements, reset the selected option
            if (element.type === 'select-one' || element.type === 'select-multiple') {
              element.selectedIndex = -1;
            }
          }
        }

        $('#photo-preview').attr('src', "{{ url('/') }}/asset/images/user.png");
        $('#branch').prepend($('#branch_id').find('option[value=""]')).val('');
        $('#company_id').prepend($('#company_id').find('option[value=""]')).val('');
      }
      // $('#date_application').val(response.date.created_at);
      setDatePickerForStartDate();
    });
    $('#company_id').on('change', function () {
      var company_id = $(this).val();
      var code = $('#customer_id').val();
      $.ajax({
        type: "POST",
        url: "{{ ($admin != true) ? route('branch.ssbDataGet') : route('admin.ssbDataGet') }}",
        dataType: 'JSON',
        data: {
            'company_id': company_id,
            'customer_id': code,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {

            $('#ssb_account').val(response.account_no).prop('readonly', response.account_no !== null);
            
            if (response.account_no) {
                var SSBdate = response.register_date;
                var formatdDateSSB = moment(SSBdate).format('DD/MM/YYYY');
                $('#ssb_account_date').val(formatdDateSSB).prop('readonly', true);
                $('#ssb_account_id').val(response.id).prop('readonly', true);
            }
        },
        error: function (error) { 
          $('#ssb_account_date').val('');
          $('#ssb_account').val('');
          swal('warning', error.responseJSON.error, 'warning'); // Adjust as needed
        }
    });

    });
  });
  if ($('#customer_id').val() != '') {
    $('#customer_id').trigger('keyup');
  }
  var create_application_date = $('#create_application_date').val();
  $(document).ready(function () {
    $("#select_date").hover(function () {
      
      $('#select_date').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        autoclose: true,
        endDate: create_application_date,
        orientation: 'bottom',
        // startDate: '01/04/2021',
      })
    });
    $('#company_id').on('change', function () {
      // $('#applicant_name').val('');
      $('#ssb_account').val('');
    });
    // $('#branch').on('change', function () {
    //   // $('#applicant_name').val('');
    //   $('#ssb_account').val('');
    // });
    $('#dob').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: "-18y",
      autoclose: true
    });
    $('.work_start').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: create_application_date,
      autoclose: true
    })
    $('.work_end').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: create_application_date,
      autoclose: true
    })
    $.validator.addMethod("decimal", function (value, element, p) {
      if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
        $.validator.messages.decimal = "";
        result = true;
      } else {
        $.validator.messages.decimal = "Please enter valid numeric number.";
        result = false;
      }
      return result;
    }, "");
    /****  validation with class Start  *****/
    $.validator.addClassRules({
      m_status: { mStatusRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("mStatusRequired", $.validator.methods.required, "Please select marital status.");
    $.validator.addClassRules({
      gender: { genderRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("genderRequired", $.validator.methods.required, "Please select gender.");
    $.validator.addClassRules({
      examination_more: { cRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("cRequired", $.validator.methods.required, "Please select examination.");
    $.validator.addClassRules({
      examination: { examinationRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("examinationRequired", $.validator.methods.required, "Please select examination.");
    $.validator.addClassRules({
      examination_passed: { exaPassRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("exaPassRequired", $.validator.methods.required, "Please select examination passed.");
    $.validator.addClassRules({
      examination_passed_more: { exaPassMoreRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("exaPassMoreRequired", $.validator.methods.required, "Please select examination passed.");
    $.validator.addClassRules({
      university_name_more: { uniRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("uniRequired", $.validator.methods.required, "Please enter university name.");
    $.validator.addClassRules({
      subject_more: { subjectRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("subjectRequired", $.validator.methods.required, "Please enter subjects name.");
    $.validator.addClassRules({
      division_more: { divisionRequired: true, decimal: true, perCheck: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("divisionRequired", $.validator.methods.required, "Please enter division.");
    $.validator.addClassRules({
      passing_year_more: { passingYearRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("passingYearRequired", $.validator.methods.required, "Please select passing year.");
    $.validator.addClassRules({
      diploma_course: { dipCourseRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("dipCourseRequired", $.validator.methods.required, "Please enter diploma course.");
    $.validator.addClassRules({
      academy: { academyRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("academyRequired", $.validator.methods.required, "Please enter academy name.");
    $.validator.addClassRules({
      diploma_university: { dipUniRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("dipUniRequired", $.validator.methods.required, "Please enter university name.");
    $.validator.addClassRules({
      diploma_subject: { dipSubjectRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("dipSubjectRequired", $.validator.methods.required, "Please enter subjects name.");
    $.validator.addClassRules({
      diploma_division: { dipDivisionRequired: true, decimal: true, perCheck: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("dipDivisionRequired", $.validator.methods.required, "Please enter division.");
    $.validator.addClassRules({
      diploma_passing_year: { diPassingYearRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("diPassingYearRequired", $.validator.methods.required, "Please select passing year.");
    $.validator.addClassRules({
      company_name: { companyNameRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("companyNameRequired", $.validator.methods.required, "Please enter company name.");
    $.validator.addClassRules({
      work_start: { workStart: true, dateDdMm: true, workdatevalidation: 'work_start', },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("workStart", $.validator.methods.required, "Please enter work from date.");
    $.validator.addClassRules({
      work_end: { workEnd: true, dateDdMm: true, workdatevalidation: 'work_end', },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("workEnd", $.validator.methods.required, "Please enter work to date.");
    $.validator.addClassRules({
      nature_work: { natureWorkRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("natureWorkRequired", $.validator.methods.required, "Please enter nature work.");
    $.validator.addClassRules({
      work_salary: { workSalaryRequired: true, decimal: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("workSalaryRequired", $.validator.methods.required, "Please enter salary.");
    $.validator.addClassRules({
      reference_name: { nameRequired: true, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("nameRequired", $.validator.methods.required, "Please enter name.");
    $.validator.addClassRules({
      reference_no: { noRequired: true, reNumber: true, reMinlength: 10, reMaxlength: 12, },
      submitHandler: function (form) { return false; }
    });
    $.validator.addMethod("noRequired", $.validator.methods.required, "Please enter number.");
    $.validator.addMethod("reNumber", $.validator.methods.number, "Please enter valid number.");
    $.validator.addMethod("reMinlength", $.validator.methods.minlength, "Please enter minimum  10 or maximum 12 digit.");
    $.validator.addMethod("reMaxlength", $.validator.methods.maxlength, "Please enter minimum  10 or maximum 12 digit.");
    $.validator.addMethod("perCheck", function (value, element, p) {
      if (value <= 100) {
        $.validator.messages.perCheck = "";
        result = true;
      } else {
        $.validator.messages.perCheck = "Division should not greater than 100%";
        result = false;
      }
      return result;
    }, "");
    /****  validation with Please enter minimum  10 or maximum 12 digit.class end  *****/
    $.validator.addMethod("dateDdMm", function (value, element, p) {

      if (this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
        $.validator.messages.dateDdMm = "";
        result = true;
      } else {
        $.validator.messages.dateDdMm = "Please enter valid date.";
        result = false;
      }
      return result;
    }, "");
    $.validator.addMethod("workdatevalidation", function (value, element, p) {
      id = $(element).attr('id');
      var res = id.substr(11);
      if (res) {
        //var from = new Date(Date.parse($('#work_start_'+res).val()));
        //  var to = new Date(Date.parse($('#work_end_'+res).val()));
        moment.defaultFormat = "DD/MM/YYYY HH:mm";
        var f1 = moment($('#work_start_' + res).val() + ' 00:00', moment.defaultFormat).toDate();
        var f2 = moment($('#work_end_' + res).val() + ' 00:00', moment.defaultFormat).toDate();
        var from = new Date(Date.parse(f1));
        var to = new Date(Date.parse(f2));
        if (to > from) {
          $.validator.messages.workdatevalidation = "";
          result = true;
        } else {
          $.validator.messages.workdatevalidation = "To date must be greater than from date.";
          result = false;
        }
      }
      else {
        var from = new Date($('#work_start').val());
        var to = new Date($('#work_end').val());
      }
      return result;
    }, "");
    $.validator.addMethod("workdatevalidation1", function (value, element, p) {
      moment.defaultFormat = "DD/MM/YYYY HH:mm";
      var f1 = moment($('#work_start').val() + ' 00:00', moment.defaultFormat).toDate();
      var f2 = moment($('#work_end').val() + ' 00:00', moment.defaultFormat).toDate();
      var from = new Date(Date.parse(f1));
      var to = new Date(Date.parse(f2));
      if (to > from) {
        $.validator.messages.workdatevalidation1 = "";
        result = true;
      } else {
        $.validator.messages.workdatevalidation1 = "To date must be greater than from date.";
        result = false;
      }

      return result;
    }, "")
    $.validator.addMethod("checkVoterId", function (value, element, p) {
      if (this.optional(element) || /^([a-zA-Z]){3}([0-9]){7}?$/g.test(value) == true) {
        result = true;
      } else {
        $.validator.messages.checkVoterId = "Please enter valid voter id number.";
        result = false;
      }
      return result;
    }, "");
    $.validator.addMethod("checkPenCard", function (value, element, p) {
      if (this.optional(element) || /([A-Z]){5}([0-9]){4}([A-Z]){1}$/.test(value) == true) {
        result = true;
      } else {
        $.validator.messages.checkPenCard = "Please enter valid pan card no.";
        result = false;
      }
      return result;
    }, "");
    $.validator.addMethod("checkAadhar", function (value, element, p) {
      /*
      if (value == "") {
          $.validator.messages.checkAadhar = "Please enter Aadhar number.";
          return false;
      }
      */
      if (value != "") {
        if (/^(\d{12}|\d{16})$/.test(value)) {
          return true;
        } else {
          $.validator.messages.checkAadhar = "Please enter a valid Aadhar card number.";
          return false;
        }
      } else {
        return true;
      }
    }, "");
    $.validator.addMethod("chk_created", function (value, element, p) {
      moment.defaultFormat = "DD/MM/YYYY HH:mm";
      var f1 = moment($('#select_date').val() + ' 00:00', moment.defaultFormat).toDate();
      var f2 = moment($('#ssb_account_date').val() + ' 00:00', moment.defaultFormat).toDate();
      var from = new Date(Date.parse(f2));
      var to = new Date(Date.parse(f1));
      if ($('#ssb_account_date').val() != '') {
        if (to >= from) {
          $.validator.messages.chk_created = "";
          result = true;
        } else {
          $.validator.messages.chk_created = "Register date  must be greater than or equal to SSB account date.";
          result = false;
        }
      }
      return result;
    }, "")
    $('#employee_register').validate({
      rules: {
        select_date: {
          required: true,
          dateDdMm: true,
        },
        category: "required",
        company_id: "required",
        branch_id: "required",
        designation: "required",

        photo: {
          // required:true,
          extension: "jpg|jpeg|png",
        },
        guardian_name: "required",
        salary: {
          required: true,
          decimal: true,
        },
        recommendation_employee_name: "required",
        applicant_name: "required",
        pen_number: {
          required: function (element) {
            return $("#aadhar_number").val().trim() === '' && $("#voter_id").val().trim() === '';
          },
          checkPenCard: true
        },
        aadhar_number: {
          required: function (element) {
            return $("#pen_number").val().trim() === '' && $("#voter_id").val().trim() === '';
          },
          checkAadhar: true
        },
        voter_id: {
          required: function (element) {
            return $("#aadhar_number").val().trim() === '' && $("#pen_number").val().trim() === '';
          },

        },
        dob: {
          required: true,
          dateDdMm: true,
        },
        guardian_number: {
          required: true,
          number: true,
          minlength: 10,
          maxlength: 12
        },
        mobile_no: {
          required: true,
          number: true,
          minlength: 10,
          maxlength: 12
        },
        email: {
          email: function (element) {
            if ($("#email").val() != '') {
              return true;
            } else {
              return false;
            }
          },
        },
        mother_name: "required",
        permanent_address: "required",
        current_address: "required",
        language_known_1: {
          required: true,
          number: false,
        },
        university_name: "required",
        subject: "required",
        division: {
          required: true,
          decimal: true,
          perCheck: true,
        },
        passing_year: "required",
        ssb_account: {
          number: true,
        },
        account_no: {
          required: true,
          number: true,
          minlength: 8,
          maxlength: 16
        },
        cAccount_no: {
          required: true,
          number: true,
          minlength: 8,
          maxlength: 16,
          equalTo: "#account_no"
        },
        bank_name: {
          required: true,
        },
        ifsc_code: {
          required: true,
          checkIfsc: true,
        },
        diploma_course: {
          required: function (element) {
            if (($("#academy").val() != '') || ($("#diploma_university_name").val() != '') || ($("#diploma_subject").val() != '') || ($("#diploma_passing_year").val() != '') || ($("#diploma_division").val() != '')) {
              return true;
            } else {
              return false;
            }
          }
        },
        academy: {
          required: function (element) {
            if (($("#diploma_course").val() != '') || ($("#diploma_university_name").val() != '') || ($("#diploma_subject").val() != '') || ($("#diploma_passing_year").val() != '') || ($("#diploma_division").val() != '')) {
              return true;
            } else {
              return false;
            }
          }
        },
        diploma_university_name: {
          required: function (element) {
            if (($("#diploma_course").val() != '') || ($("#academy").val() != '') || ($("#diploma_subject").val() != '') || ($("#diploma_passing_year").val() != '') || ($("#diploma_division").val() != '') || ($("#diploma_division").val() != '')) {
              return true;
            } else {
              return false;
            }
          }
        },
        diploma_subject: {
          required: function (element) {
            if (($("#diploma_course").val() != '') || ($("#academy").val() != '') || ($("#diploma_university_name").val() != '') || ($("#diploma_passing_year").val() != '') || ($("#diploma_division").val() != '')) {
              return true;
            } else {
              return false;
            }
          }
        },
        diploma_division: {
          required: function (element) {
            if (($("#diploma_course").val() != '') || ($("#academy").val() != '') || ($("#diploma_university_name").val() != '') || ($("#diploma_passing_year").val() != '') || ($("#diploma_subject").val() != '')) {
              return true;
            } else {
              return false;
            }
          },
          decimal: function (element) {
            if ($("#diploma_division").val() != '') {
              return true;
            } else {
              return false;
            }
          },
          perCheck: function (element) {
            if ($("#diploma_division").val() != '') {
              return true;
            } else {
              return false;
            }
          },
        },
        diploma_passing_year: {
          required: function (element) {
            if (($("#diploma_course").val() != '') || ($("#academy").val() != '') || ($("#diploma_university_name").val() != '') || ($("#diploma_subject").val() != '') || ($("#diploma_division").val() != '')) {
              return true;
            } else {
              return false;
            }
          }
        },
        company_name: {
          required: function (element) {
            if (($("#work_start").val() != '') || ($("#work_end").val() != '') || ($("#nature_work").val() != '') || ($("#work_salary").val() != '') || ($("#reference_name").val() != '') || ($("#reference_no").val() != '')) {
              return true;
            } else {
              return false;
            }
          }
        },
        work_start: {
          required: function (element) {
            if (($("#company_name").val() != '') || ($("#work_end").val() != '') || ($("#nature_work").val() != '') || ($("#work_salary").val() != '') || ($("#reference_name").val() != '') || ($("#reference_no").val() != '')) {
              return true;
            } else {
              return false;
            }
          },
          dateDdMm: function (element) {
            if ($("#work_start").val() != '') {
              return true;
            } else {
              return false;
            }
          },
          workdatevalidation1: function (element) {
            if ($("#work_start").val() != '') {
              return true;
            } else {
              return false;
            }
          },
        },
        work_end: {
          required: function (element) {
            if (($("#company_name").val() != '') || ($("#work_start").val() != '') || ($("#nature_work").val() != '') || ($("#work_salary").val() != '') || ($("#reference_name").val() != '') || ($("#reference_no").val() != '')) {
              return true;
            } else {
              return false;
            }
          },
          dateDdMm: function (element) {
            if ($("#work_end").val() != '') {
              return true;
            } else {
              return false;
            }
          },
          workdatevalidation1: function (element) {
            if ($("#work_end").val() != '') {
              return true;
            } else {
              return false;
            }
          },
        },
        nature_work: {
          required: function (element) {
            if (($("#company_name").val() != '') || ($("#work_start").val() != '') || ($("#work_end").val() != '') || ($("#work_salary").val() != '') || ($("#reference_name").val() != '') || ($("#reference_no").val() != '')) {
              return true;
            } else {
              return false;
            }
          },
        }, work_salary: {
          required: function (element) {
            if (($("#company_name").val() != '') || ($("#work_start").val() != '') || ($("#nature_work").val() != '') || ($("#work_end").val() != '') || ($("#reference_name").val() != '') || ($("#reference_no").val() != '')) {
              return true;
            } else {
              return false;
            }
          },
          decimal: function (element) {
            if ($("#work_salary").val() != '') {
              return true;
            } else {
              return false;
            }
          },
        },
        reference_name: {
          required: function (element) {
            if (($("#company_name").val() != '') || ($("#work_start").val() != '') || ($("#nature_work").val() != '') || ($("#work_end").val() != '') || ($("#work_salary").val() != '') || ($("#reference_no").val() != '')) {
              return true;
            } else {
              return false;
            }
          },
        },
        reference_no: {
          required: function (element) {
            if (($("#company_name").val() != '') || ($("#work_start").val() != '') || ($("#nature_work").val() != '') || ($("#work_end").val() != '') || ($("#work_salary").val() != '') || ($("#reference_name").val() != '')) {
              return true;
            } else {
              return false;
            }
          },
          number: function (element) {
            if ($("#reference_no").val() != '') {
              return true;
            } else {
              return false;
            }
          },
          minlength: 10,
          maxlength: 12
        },
        // ssb_account_date:{
        //   chk_created : true,
        // },
        esi_account_no: {
          number: function (element) {
            if ($("#esi_account_no").val() != '') {
              return true;
            } else {
              return false;
            }
          },
          minlength: 10,
          maxlength: 10,
        },
        pf_account_no: {
          number: function (element) {
            if ($("#pf_account_no").val() != '') {
              return true;
            } else {
              return false;
            }
          },
          minlength: 12,
          maxlength: 12,
        },
      },
      messages: {
        select_date: {
          required: "Please  select register date.",
        },
        category: "Please select category.",
        company_id: "Please select Company ",
        branch_id: "Please select branch.",
        designation: "Please select designation.",

        photo: {
          // required: 'Please select photo.',
          extension: "Accept only PNG,JPG."
        },
        salary: {
          required: "Please enter salary.",
        },
        recommendation_employee_name: "Please enter recommendation employee name.",
        applicant_name: "Please enter applicant name.",
        dob: {
          required: "Please enter date of brith.",
        },
        mobile_no: {
          required: "Please enter mobile number.",
          number: "Please enter valid number.",
          minlength: "Please enter minimum  10 or maximum 12 digit.",
          maxlength: "Please enter minimum  10 or maximum 12 digit."
        },
        email: {
          required: "Please enter email id.",
          email: "Please enter valid email id.",
        },
        guardian_name: "Please enter father/legal guardian name.",
        guardian_number: {
          required: "Please enter father/legal guardian number.",
          number: "Please enter valid number.",
          minlength: "Please enter minimum  10 or maximum 12 digit.",
          maxlength: "Please enter minimum  10 or maximum 12 digit."
        },
        mother_name: "Please enter mother name.",
        permanent_address: "Please enter permanent address.",
        current_address: "Please enter current address.",
        language_known_1: {
          required: "Please enter language known",
          number: "Please enter valid language known",
        },
        university_name: "Please enter university name.",
        subject: "Please enter subject name.",
        division: {
          required: "Please enter division.",
        },
        passing_year: "Please select passing year.",
        diploma_passing_year: "Please select passing year.",
        diploma_course: {
          required: "Please enter course.",
        },
        academy: {
          required: "Please enter academy.",
        },
        diploma_university_name: {
          required: "Please enter university name.",
        },
        diploma_subject: {
          required: "Please enter subject.",
        },
        diploma_division: {
          required: "Please enter division.",
        },
        company_name: {
          required: "Please enter reference name.",
        },
        company_name: {
          required: "Please enter company name.",
        },
        work_start: {
          required: "Please enter work start date.",
        },
        work_end: {
          required: "Please enter work end date.",
        },
        nature_work: {
          required: "Please enter nature work.",
        },
        work_salary: {
          required: "Please enter salary.",
        },
        reference_name: {
          required: "Please enter reference name.",
        },
        reference_no: {
          required: "Please enter reference number.",
          number: "Please enter valid number.",
          minlength: "Please enter minimum  10 or maximum 12 digit.",
          maxlength: "Please enter minimum  10 or maximum 12 digit."
        },
        account_no: {
          required: "Please enter account number.",
        },
        cAccount_no: {
          required: "Bank A/C and confirm bank A/C must be same.",
        },
        bank_name: {
          required: "Please enter bank name.",
        },
        ifsc_code: {
          required: "Please enter IFSC Code.",
        }
      },submitHandler:function(form){
        var formData = new FormData(form);
        var createdat = $('#created_at').val();
        var createdAtInput = document.createElement("input");
          createdAtInput.type = "hidden"; 
          createdAtInput.name = "created_at";
          createdAtInput.value = createdat;
          form.appendChild(createdAtInput);
          form.submit();
      },
      errorElement: 'label',
      errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
    });
    $('#company_id').on('change', function () {
      $('#ssb_account').val('');
      $('#ssb_account_id').val('');
      $('#ssb_account_date').val('');
    })
    $('#ssb_account').on('change', function () {
      var ssb_account = $(this).val();
      var company_id = $('#company_id').val();
      var name = $('#applicant_name').val().toLowerCase();
      $('#ssb_account').val('');
      $('#ssb_account_id').val('');
      $('#ssb_account_date').val('');
      $.ajax({
        type: "POST",
        url: SSB,
        data: { ssb_account: ssb_account },
        dataType: "JSON",
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          if (response.resCount == 1) {
            if (ssb_account == response.account_no.account_no && $.trim(name) == (response.name.toLowerCase()).replace(/\s+/g, ' ').trim()
            ) {
              if (response.account_no.company_id == company_id) {
                $('#ssb_account').val(response.account_no.account_no);
                $('#ssb_account_id').val(response.account_no.id);
                $('#ssb_account_date').val(response.ssbDate);
              }
              else {
                swal("Error!", "selected company and SSB account not matched!", "error");
              }
            }
            else {
              swal("Error!", "Applicant name or ssb account holder name(" + response.name.toLowerCase() + ") not match!", "error");

            }
          }
          else {
            swal("Error!", " SSB account not found!", "error");
          }
        }
      })
    })
    $('#applicant_name').on('keyup', function () {
      if ($("#ssb_account").val() != '') {
        $("#ssb_account").trigger("change");
      }
    })

  })

  $(document).ajaxStart(function () {
    $(".loader").show();
  });
  $(document).ajaxComplete(function () {
    $(".loader").hide();
  });


  var a = 0;
  $("#add_qualification").click(function () {
    a++;
    $("#qualification").append('<tr><td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"><div class="col-lg-12 error-msg"> <div class="row"> <div class="col-lg-6"> <div class="custom-control custom-radio mb-3 ">  <input type="radio" id="examination10_' + a + '" name="examination_more[' + a + ']" class="custom-control-input examination_more" value="10th"> <label class="custom-control-label" for="examination10_' + a + '">10th</label> </div> </div> <div class="col-lg-6"> <div class="custom-control custom-radio mb-3  "> <input type="radio" id="examination12_' + a + '" name="examination_more[' + a + ']" class="custom-control-input examination_more" value="12th"> <label class="custom-control-label" for="examination12_' + a + '">12th</label> </div>  </div> <div class="col-lg-12"> <div class="custom-control custom-radio mb-3  "> <input type="radio" id="examinationgra_' + a + '" name="examination_more[' + a + ']" class="custom-control-input examination_more" value="Graduation"> <label class="custom-control-label" for="examinationgra_' + a + '">Graduation </label> </div> </div> <div class="col-lg-12"> <div class="custom-control custom-radio mb-3  "> <input type="radio" id="examinationpostgra_' + a + '" name="examination_more[' + a + ']" class="custom-control-input examination_more" value="Post Graduation">  <label class="custom-control-label" for="examinationpostgra_' + a + '">Post Graduation </label> </div> </div>  </div> </div>  </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg"> <div class="row"> <div class="col-lg-12"> <div class="custom-control custom-radio mb-3 "> <input type="radio" id="examination_passed_school_' + a + '" name="examination_passed_more[' + a + ']" class="custom-control-input examination_passed_more" value="School" > <label class="custom-control-label" for="examination_passed_school_' + a + '">School</label> </div> </div> <div class="col-lg-12"> <div class="custom-control custom-radio mb-3  "> <input type="radio" id="examination_passed_college_' + a + '" name="examination_passed_more[' + a + ']" class="custom-control-input examination_passed_more" value="College"> <label class="custom-control-label" for="examination_passed_college_' + a + '">College</label> </div> </div> <div class="col-lg-12"> <div class="custom-control custom-radio mb-3  "> <input type="radio" id="examination_passed_university_' + a + '" name="examination_passed_more[' + a + ']" class="custom-control-input examination_passed_more" value="University"> <label class="custom-control-label" for="examination_passed_university_' + a + '">University</label> </div> </div>  </div> </div>  </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg"> <input type="text" name="university_name_more[' + a + ']" id="university_name_' + a + '" class="form-control university_name_more"  > </div> </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg">  <textarea name="subject_more[' + a + ']" id="subject_' + a + '" class="form-control subject_more"></textarea> </div> </td> <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg"> <input type="text" name="division_more[' + a + ']" id="division_' + a + '" class="form-control division_more"  > </div> </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg"> <select name="passing_year_more[' + a + ']" id="passing_year_' + a + '" class="form-control passing_year_more" > <option value="">Select Passing Year</option> {{ $last= date('Y')-100 }} {{ $now = date('Y') }} @for ($i = $now; $i >= $last; $i--) <option value="{{ $i }}">{{ $i }}</option> @endfor </select> </div> </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <button type="button" class="btn btn-primary remCF"><i class="fas fa-trash"></i></button> </td> </tr>');
  });
  $("#qualification").on('click', '.remCF', function () {
    $(this).parent().parent().remove();
  });
  var b = 0;
  $("#add_diploma").click(function () {
    $("#diploma_course").addClass("diploma_course");
    $("#academy").addClass("academy");
    $("#diploma_university_name").addClass("diploma_university");
    $("#diploma_subject").addClass("diploma_subject");
    $("#diploma_division").addClass("diploma_division");
    $("#diploma_passing_year").addClass("diploma_passing_year");
    b++;
    $("#diploma").append('  <tr> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg"> <input type="text" name="diploma_course_more[' + b + ']" id="diploma_course_' + b + '" class="form-control diploma_course" > </div> </td>  <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg">  <input type="text" name="academy_more[' + b + ']" id="academy_' + b + '" class="form-control academy" > </div>  </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg"> <input type="text" name="diploma_university_name_more[' + b + ']" id="diploma_university_name_' + b + '" class="form-control diploma_university"  > </div> </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg">  <textarea name="diploma_subject_more[' + b + ']" id="diploma_subject_' + b + '" class="form-control diploma_subject"></textarea> </div> </td> <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg"> <input type="text" name="diploma_division_more[' + b + ']" id="diploma_division_' + b + '" class="form-control diploma_division" > </div>  </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">   <div class="col-lg-12 error-msg">   <select name="diploma_passing_year_more[' + b + ']" id="diploma_passing_year_' + b + '" class="form-control diploma_passing_year " > <option value="">Select Passing Year</option> {{ $last= date('Y')-100 }} {{ $now = date('Y') }}  @for ($i = $now; $i >= $last; $i--) <option value="{{ $i }}">{{ $i }}</option>  @endfor </select> </div> </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <button type="button" class="btn btn-primary remDiploma"><i class="fas fa-trash"></i></button> </td> </tr> ');
  });
  $("#diploma").on('click', '.remDiploma', function () {
    $(this).parent().parent().remove();
    count = $(".diploma_course").length;
    if (count == 1) {
      $("#diploma_course").removeClass("diploma_course");
      $("#academy").removeClass("academy");
      $("#diploma_university_name").removeClass("diploma_university");
      $("#diploma_subject").removeClass("diploma_subject");
      $("#diploma_division").removeClass("diploma_division");
      $("#diploma_passing_year").removeClass("diploma_passing_year");
    }

  });
  var c = 0;
  $("#add_experience").click(function () {
    $("#company_name").addClass("company_name");
    $("#work_start").addClass("work_start");
    $("#work_end").addClass("work_end");
    $("#nature_work").addClass("nature_work");
    $("#work_salary").addClass("work_salary");
    $("#reference_name").addClass("reference_name");
    $("#reference_no").addClass("reference_no");
    c++;
    $("#experience").append('  <tr> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg"> <input type="text" name="company_name_more[' + c + ']" id="company_name_' + c + '" class="form-control company_name"    > </div> </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 "> <div class="form-group row"> <label class="col-form-label col-lg-3">From </label> <div class="col-lg-9 error-msg"> <div class="input-group"> <span class="input-group-prepend" style="margin-right: 0.5rem;"> <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span> </span> <input type="text" class="form-control work_start" name="work_start_more[' + c + ']" id="work_start_' + c + '" placeholder="DD/MM/YYYY"> </div> </div> </div> <div class="form-group row"> <label class="col-form-label col-lg-3">To </label>  <div class="col-lg-9 error-msg"> <div class="input-group"> <span class="input-group-prepend" style="margin-right: 0.5rem;"> <span class="input-group-text" ><i class="fa fa-calendar" aria-hidden="true"></i></span> </span> <input type="text" class="form-control work_end" name="work_end_more[' + c + ']" id="work_end_' + c + '" placeholder="DD/MM/YYYY"> </div> </div>  </div> </div>  </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg">  <input type="text" name="nature_work_more[' + c + ']" id="nature_work_' + c + '" class="form-control nature_work"   > </div>  </td> <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <div class="col-lg-12 error-msg"> <input type="text" name="work_salary_more[' + c + ']" id="work_salary_' + c + '" class="form-control work_salary" > </div> </td>  <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;"> <div class="col-lg-12 "> <div class="form-group row">  <label class="col-form-label col-lg-3">Name </label>  <div class="col-lg-9 error-msg">  <input type="text" name="reference_name_more[' + c + ']" id="reference_name_' + c + '" class="form-control reference_name" >  </div> </div> <div class="form-group row"> <label class="col-form-label col-lg-3">No. </label>  <div class="col-lg-9 error-msg"> <input type="text" name="reference_no_more[' + c + ']" id="reference_no_' + c + '" class="form-control reference_no"> </div> </div> </div> </td>  <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"> <button type="button" class="btn btn-primary remWork"><i class="fas fa-trash"></i></button> </td> </tr>');
    $('.work_start').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: date,
      autoclose: true
    })
    $('.work_end').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: date,
      autoclose: true
    })
  });
  $('#work_start').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,
    endDate: date,
    autoclose: true
  })
  $('#work_end').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,
    endDate: date,
    autoclose: true
  })
  $("#experience").on('click', '.remWork', function () {
    $(this).parent().parent().remove();
    count = $(".company_name").length;
    if (count == 1) {
      $("#company_name").removeClass("company_name");
      $("#work_start").removeClass("work_start");
      $("#work_end").removeClass("work_end");
      $("#nature_work").removeClass("nature_work");
      $("#work_salary").removeClass("work_salary");
      $("#reference_name").removeClass("reference_name");
      $("#reference_no").removeClass("reference_no");
    }
  });
  $(document).on('change', '#designation', function () {
    var designation = $(this).val();
    $.ajax({
      type: "POST",
      url: DataGet,
      dataType: 'JSON',
      data: { 'designation': designation, },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        //alert(response.data.basic_salary);
        if (response.msg == 1) {
          $('#salary').val(response.salary);
        }
        else {
          swal("Sorry!", "Record not found.Try Again!", "error");
        }
      }
    })
  });
  $(document).on('change', '#photo', function () {
    // $("#upload_form").submit();
    if (this.files && this.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#photo-preview').attr('src', e.target.result);
        // $('#photo-preview').attr('style', 'width:200px; height:200px;');
      }
      reader.readAsDataURL(this.files[0]);
    }
  });
  $(document).on('change', '#category', function () {
    var category = $('#category').val();
    $('#salary').val('');
    $.ajax({
      type: "POST",
      url: exporturl,
      dataType: 'JSON',
      data: { 'category': category },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        $('#designation').find('option').remove();
        $('#designation').append('<option value="">Select Designation</option>');
        $.each(response.data, function (index, value) {
          $("#designation").append("<option value='" + value.id + "'>" + value.designation_name + "</option>");
        });
      }
    });
    $('#account_no, #cAccount_no').on('cut copy paste', function (e) {
      e.preventDefault();
    });
  });
  $(document).ready(function () {
    $('#photo').on('change', function () {
      var fileName = $(this).val().split('\\').pop();
      $('#photo_label').text(fileName || 'Select photo');
    });
  });
  $('#company_id').on('change', function () {
    var company_id = $(this).val();
    $.ajax({
      type: "POST",
      url: "{{route('admin.employee.companydate')}}",
      dataType: 'JSON',
      data: {
        'company_id': company_id,
      },
      success: function (response) {
        $('#company_register_date').val(response);
        var isEmployeeCheckbox = $('#is_employee').val();
        if (isEmployeeCheckbox == 1) {
          $('#select_date').val();
        }
        $('#date_application_company_date').val(response);
        setDatePickerForStartDate(response);
      }
    });
  });
  /*
  $("#select_date").hover(function () {
    setDatePickerForStartDate();
  });
  */
  function setDatePickerForStartDate() {
    var edate = $('#date_application').val();
    var d = $('#date_application_company_date').val();
    // var dd = (d != null) && (d < edate ) ? d : edate;
    var edateObj = new Date(edate);
    var dObj = d ? new Date(d) : new Date();
    var dd = (dObj < edateObj) ? d : edate;
    $('#select_date').datepicker({
        format: "dd/mm/yyyy",
        autoclose: true,
        todayHighlight: true,
        startDate: dd,  // Set the start date directly using the startDate option
        endDate: edate    // Set the end date directly using the endDate option
    });
    console.log(dd,'dd');
    console.log(edate,'edate');
    $('#select_date').datepicker('setStartDate', dd);
}
</script>