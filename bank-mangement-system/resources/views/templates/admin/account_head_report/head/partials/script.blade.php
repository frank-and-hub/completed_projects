<script type="text/javascript">
    $(document).ready(function() {
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });

        $('#filter_headlist').validate({

            rules: {
                company_list: {
                    required: true,
                },
            },
            messages: {
                company_list: {
                    "required": "Please Select Company",
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('');
                element.closest('.error-msg').append(error);

            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');

            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');

            },

        });


        $("#searchheadlist").on("click", function(e) {
            if ($('#filter_headlist').valid()) {
                var companyList = $("form#filter_headlist #company_list").val();

                $('#account-head-sec').html('');

                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.head.getHeadlistbyCompany') !!}",
                    dataType: 'JSON',
                    data: {
                        'companyList': companyList
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // console.log(response)
                        if (response.msg_type == 'success') {
                            $('#account-head-sec').html(response.view);
                        }
                    }

                });
            }
        });


        $('#company').on('change', function() {

            var company_id = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get.parentheadbycompany') !!}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {

                    $('#head1').find('option').remove();
                    $('#head2').find('option').remove();
                    $('#head3').find('option').remove();
                    $('#head4').find('option').remove();
                    $('#headDetails').hide();
                    $('#new_head').val('');

                    $('#head1').append(
                        '<option value="">---Select Parent Head---</option>');
                    $.each(response.parent_headid, function(index, value) {

                        $("#head1").append("<option value='" + value.id + "'>" +
                            value.sub_head + "</option>");

                    });

                }
            });
        })

        $('#head1').on('change', function() {

            var head_id = $(this).val();
            var companySelect = document.getElementById("company");
            var selectedCompany = companySelect.value;
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get.child_head') !!}",
                dataType: 'JSON', 
                data: {
                    'child_asset_id': head_id,
                    'selectedCompany': selectedCompany
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {

                    $('#head2').find('option').remove();
                    $('#new_head').val('');

                    $('#head2').append(
                        '<option value="">---Select Child Subhead---</option>');
                    $.each(response.sub_child_assets, function(index, value) {
                        $("#head2").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });
                }
            });
        })

        $('#head2').on('change', function() {

            var head_id = $(this).val();
            var companySelect = document.getElementById("company");
            var selectedCompany = companySelect.value;
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get.child_head') !!}",
                dataType: 'JSON',
                data: {
                    'child_asset_id': head_id,
                    'selectedCompany': selectedCompany

                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    $('#head3').find('option').remove();
                    $('#new_head').val('');

                    $('#head3').append(
                        '<option value="">---Select Child Subhead---</option>');
                    $.each(response.sub_child_assets, function(index, value) {
                        $("#head3").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });
                }
            });
        })

        $('#head3').on('change', function() {

            var head_id = $(this).val();
            var companySelect = document.getElementById("company");
            var selectedCompany = companySelect.value;
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get.child_head') !!}",
                dataType: 'JSON',
                data: {
                    'child_asset_id': head_id,
                    'selectedCompany': selectedCompany

                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    $('#head4').find('option').remove();
                    $('#new_head').val('');

                    $('#head4').append(
                        '<option value="">---Select Child Subhead---</option>');
                    $.each(response.sub_child_assets, function(index, value) {
                        $("#head4").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });
                }
            });
        })

        $(document).on('click', '.subhead', function() {

            var val = $(this).attr('data-value');
            $('.' + val + '-icon').toggleClass('fas fa-angle-down fas fa-angle-up');
            $('.' + val + '-child_head').toggle();
            $('.head3').hide();
        });

        $(document).on('click', '.child_head', function() {
            var val = $(this).attr('data-value');
            $('.' + val + '-sub_child_head').toggle();
            $('.' + val + '-icon').toggleClass('fas fa-angle-up fas fa-angle-down ');
            var val2 = $('.' + val + '-sub_child_head').attr('data-value');
            $('.' + val2 + '-sub_child_head2').hide();
            var val3 = $('.' + val2 + '-sub_child_head2').attr('data-value');
            // alert(val3);
            $('.' + val3 + '-head5').hide();
        });

        $(document).on('click', '.sub_child_head', function() {
            var val = $(this).attr('data-value');

            $('.' + val + '-sub_child_head2').toggle();
            $('.' + val + '-icon').toggleClass('fas fa-angle-up fas fa-angle-down');
            var val2 = $('.' + val + '-sub_child_head2').attr('data-value');


            $('.' + val2 + '-head5').hide();


        });
        $(document).on('click', '.sub_child_head2', function() {
            var val = $(this).attr('data-value');
            $('.' + val + '-head5').toggle();
            $('.' + val + '-icon').toggleClass('fas fa-angle-up fas fa-angle-down');

        });
    })

    function statusUpdate(id) {

        swal({
                title: "Are you sure?",
                text: "Do want to Change Status?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-primary",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                cancelButtonClass: "btn-danger",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.update.status.indirect_expense') !!}",
                        dataType: 'JSON',
                        data: {
                            'id': id
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {

                            if (response) {

                                swal("Success", "Update Status successfully!", "success");
                                location.reload();
                            } else {
                                swal("Error", "Something went wrong.Try again!", "warning");
                            }
                        }
                    });
                }
            }
        );
    }

    function resetForm() {
        $('#account-head-sec').html(' ');
        var form = $("#filter_headlist"),
            validator = form.validate();
        validator.resetForm();

    }


    $(document).ready(function() {

        // $(".company").select2({
        //     multiple:true,
        //     placeholder : "Please Select Company",
        // });
        $('.createnewhead').validate({
            rules: {
                company: {
                    required: true,
                },
                head1: {
                    required: true,
                },
                // head2:{
                //     required:true,
                // },
                // head3 :{
                //     required:true,
                // },
                // head4 :{
                //     required:true,
                // },
                new_head: {
                    required: true,
                }
            },
            messages: {
                company: {
                    "required": "Please Select Company",
                },
                head1: {
                    "required": "Please Select Head1",
                },
                // head2:{
                //     "required":"Please Select Head2",
                // },
                // head3:{
                //     "required":"Please Select Head3",
                // },
                // head4:{
                //     "required":"Please Select Head4",
                // },
                new_head: {
                    "required": "Please Enter New Head",
                },
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('');
                element.closest('.error-msg').append(error);

            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');

            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');

            },
        });

        $(".createnewheads").on("click", function(e) {

            if ($('.createnewhead').valid()) {

                var company = $("form#head #company").val();
                var head1 = $("form#head #head1").val();
                var head2 = $("form#head #head2").val();
                var head3 = $("form#head #head3").val();
                var head4 = $("form#head #head4").val();
                var new_head = $("form#head #new_head").val();
                var systemdate = $('#create_application_date').val();

                $.ajax({

                    type: "POST",
                    url: "{!! route('admin.save.head') !!}",
                    dataType: 'JSON',
                    data: {
                        'company': company,
                        'head1': head1,
                        'head2': head2,
                        'head3': head3,
                        'head4': head4,
                        'new_head': new_head,
                        'systemdate':systemdate
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    success: function(response) {
                        console.log(response);
                        if (response.exists === '1') {
                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",

                                type: "warning",
                                showCancelButton: false,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",


                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                                    // Iterate over the dataArray
                                    dataArray.forEach(function(item, index) {

                                        const lastItem = dataArray[dataArray
                                            .length - 1];
                                        // var comapnyids = lastItem.company_id;
                                        var companyIds = lastItem.company_id
                                            .split(',').map(function(id) {
                                                return id.trim().replace(
                                                    '[', '').replace(
                                                    ']', ''
                                                ); // Remove any leading/trailing whitespaces and brackets
                                            });

                                        updateCompanies(companyIds);
                                        const id = lastItem.id;
                                        document.getElementById('headId')
                                            .value = id;
                                        const companyIdArray = JSON.parse(
                                            lastItem.company_id);

                                        // Get the last company_id from the array
                                        const lastCompanyId = companyIdArray[
                                            companyIdArray.length - 1];
                                        document.getElementById(
                                                'company_id_input').value =
                                            lastCompanyId;

                                        // Now, set the selected option in the dropdown
                                        var companyIdInputValue = document
                                            .getElementById('company_id_input')
                                            .value;
                                        var companiesSelect = document
                                            .getElementById('comapnies');
                                        // Check if it's an even index (0-based) and there is a second record available
                                
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i]
                                                    .labels; // Change 'Label' to 'Head'
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }
                                    });

                                    // Show headDetails
                                    $('#headDetails').show();
                                    $('#assign').show();
                                    $('#submit').hide();
                                    $('#createHead').hide();


                                }

                            });
                    } else if (response.exists == '2') {
                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",

                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                                    // Iterate over the dataArray
                                    dataArray.forEach(function(item, index) {
                                        
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i]
                                                    .labels; // Change 'Label' to 'Head'
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }
                                    });

                                    // Show headDetails
                                    $('#headDetails').show();
                                    $('#assign').show();
                                    $('#submit').show();
                                }


                            });
                    } else if (response.exists == '0') {

                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",

                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                               


                                    dataArray.forEach(function(item, index) {
                                        console.log(item);
                                        const lastItem = dataArray[dataArray
                                            .length - 1];
                                        // var comapnyids = lastItem.company_id;
                                        var companyIds = lastItem.company_id
                                            .split(',').map(function(id) {
                                                return id.trim().replace(
                                                    '[', '').replace(
                                                    ']', ''
                                                ); // Remove any leading/trailing whitespaces and brackets
                                            });

                                        console.log('companyIds', companyIds);
                                        updateCompanies(companyIds);
                                        const id = lastItem.id;
                                        document.getElementById('headId')
                                            .value = id;
                                        const companyIdArray = JSON.parse(
                                            lastItem.company_id);

                                        // Get the last company_id from the array
                                        const lastCompanyId = companyIdArray[
                                            companyIdArray.length - 1];
                                        document.getElementById(
                                                'company_id_input').value =
                                            lastCompanyId;

                                        // Now, set the selected option in the dropdown
                                        var companyIdInputValue = document
                                            .getElementById('company_id_input')
                                            .value;
                                        var companiesSelect = document
                                            .getElementById('comapnies');
                                      
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i]
                                                    .labels; // Change 'Label' to 'Head'
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }
                                    });





                                    // Show headDetails
                                    $('#headDetails').show();
                                    $('#assign').hide();
                                    $('#submit').hide();
                                    $('#createHead').hide();


                                }

                            });

                    } else if (response.exists == '4') {

                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",

                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                                    // Iterate over the dataArray
                             


                                    dataArray.forEach(function(item, index) {
                                        console.log(item);
                                        const lastItem = dataArray[dataArray
                                            .length - 1];
                                        // var comapnyids = lastItem.company_id;
                                        var companyIds = lastItem.company_id
                                            .split(',').map(function(id) {
                                                return id.trim().replace(
                                                    '[', '').replace(
                                                    ']', ''
                                                ); // Remove any leading/trailing whitespaces and brackets
                                            });

                                        console.log('companyIds', companyIds);
                                        updateCompanies(companyIds);
                                        const id = lastItem.id;
                                        document.getElementById('headId')
                                            .value = id;
                                        const companyIdArray = JSON.parse(
                                            lastItem.company_id);

                                        // Get the last company_id from the array
                                        const lastCompanyId = companyIdArray[
                                            companyIdArray.length - 1];
                                        document.getElementById(
                                                'company_id_input').value =
                                            lastCompanyId;

                                        // Now, set the selected option in the dropdown
                                        var companyIdInputValue = document
                                            .getElementById('company_id_input')
                                            .value;
                                        var companiesSelect = document
                                            .getElementById('comapnies');

                                  
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i]
                                                    .labels; // Change 'Label' to 'Head'
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }

                                    });








                                    

                                    // Show headDetails
                                    $('#headDetails').show();
                                    $('#assign').show();
                                    $('#submit').hide();
                                    $('#createHead').hide();

                                }


                            });

                    } else if (response.exists == '3') {

                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",

                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",

                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                                    // Iterate over the dataArray
                                   



                                    dataArray.forEach(function(item, index) {
                                        console.log(item);

                                        const lastItem = dataArray[dataArray
                                            .length - 1];
                                        // var comapnyids = lastItem.company_id;
                                        var companyIds = lastItem.company_id
                                            .split(',').map(function(id) {
                                                return id.trim().replace(
                                                    '[', '').replace(
                                                    ']', ''
                                                ); // Remove any leading/trailing whitespaces and brackets
                                            });

                                        console.log('companyIds', companyIds);
                                        updateCompanies(companyIds);
                                        const id = lastItem.id;
                                        document.getElementById('headId')
                                            .value = id;
                                        const companyIdArray = JSON.parse(
                                            lastItem.company_id);

                                        // Get the last company_id from the array
                                        const lastCompanyId = companyIdArray[
                                            companyIdArray.length - 1];
                                        document.getElementById(
                                                'company_id_input').value =
                                            lastCompanyId;

                                        // Now, set the selected option in the dropdown
                                        var companyIdInputValue = document
                                            .getElementById('company_id_input')
                                            .value;
                                        var companiesSelect = document
                                            .getElementById('comapnies');
                                        // Check if it's an even index (0-based) and there is a second record available
                                        // if (index % 2 === 0 && index + 1 <
                                        //     dataArray.length) {
                                        //     // Create a new div with classes row and form-group
                                        //     var rowDiv = document.createElement(
                                        //         'div');
                                        //     rowDiv.className = 'row form-group';

                                        //     // Loop through the two records at even and odd indices
                                        //     for (var i = 0; i < 2; i++) {
                                        //         // Create the column for the Label
                                        //         var labelColumn = document
                                        //             .createElement('div');
                                        //         labelColumn.className = 'col-2';

                                        //         // Create a new label element for the Label
                                        //         var subHeadLabel = document
                                        //             .createElement('label');
                                        //         subHeadLabel.innerHTML =
                                        //             'Label: ' + dataArray[
                                        //                 index + i].labels;
                                        //         labelColumn.appendChild(
                                        //             subHeadLabel);

                                        //         // Create the column for the Input
                                        //         var inputColumn = document
                                        //             .createElement('div');
                                        //         inputColumn.className = 'col-4';

                                        //         // Create a new input element for the Label
                                        //         var subHeadInput = document
                                        //             .createElement('input');
                                        //         subHeadInput.type = 'text';
                                        //         subHeadInput.classList.add(
                                        //             'form-control');
                                        //         subHeadInput.value = dataArray[
                                        //             index + i].sub_head;

                                        //         // Make the Label input read-only
                                        //         subHeadInput.readOnly = true;

                                        //         // Append the Label input to the inputColumn
                                        //         inputColumn.appendChild(
                                        //             subHeadInput);

                                        //         // Append the columns to the rowDiv
                                        //         rowDiv.appendChild(labelColumn);
                                        //         rowDiv.appendChild(inputColumn);
                                        //     }

                                        //     // Append the rowDiv to headDetails .card-body
                                        //     $('#headDetails .card-body').append(
                                        //         rowDiv);
                                        // }

                                        // if (index % 2 === 0 && index + 1 <
                                        //     dataArray.length) {
                                        //     // Create a new div with classes row and form-group
                                        //     var rowDiv = document.createElement(
                                        //         'div');
                                        //     rowDiv.className = 'row form-group';

                                        //     // Loop through the two records at even and odd indices
                                        //     for (var i = 0; i < 2; i++) {
                                        //         // Create the column for the Head
                                        //         var labelColumn = document
                                        //             .createElement('div');
                                        //         labelColumn.className = 'col-2';

                                        //         // Create a new label element for the Head
                                        //         var subHeadLabel = document
                                        //             .createElement('label');
                                        //         subHeadLabel.innerHTML =
                                        //             'Head: ' + dataArray[index +
                                        //                 i]
                                        //             .labels; // Change 'Label' to 'Head'
                                        //         labelColumn.appendChild(
                                        //             subHeadLabel);

                                        //         // Create the column for the Input
                                        //         var inputColumn = document
                                        //             .createElement('div');
                                        //         inputColumn.className = 'col-4';

                                        //         // Create a new input element for the Head
                                        //         var subHeadInput = document
                                        //             .createElement('input');
                                        //         subHeadInput.type = 'text';
                                        //         subHeadInput.classList.add(
                                        //             'form-control');
                                        //         subHeadInput.value = dataArray[
                                        //             index + i].sub_head;

                                        //         // Make the Head input read-only
                                        //         subHeadInput.readOnly = true;

                                        //         // Append the Head input to the inputColumn
                                        //         inputColumn.appendChild(
                                        //             subHeadInput);

                                        //         // Append the columns to the rowDiv
                                        //         rowDiv.appendChild(labelColumn);
                                        //         rowDiv.appendChild(inputColumn);
                                        //     }

                                        //     // Append the rowDiv to headDetails .card-body
                                        //     $('#headDetails .card-body').append(
                                        //         rowDiv);
                                        // }
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i].labels;
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }

                                    });







                                    // If checkHead is present, append it as well
                                    // if (responseData.checkHead) {
                                    //     var checkHeadDiv = document.createElement('div');
                                    //     checkHeadDiv.innerHTML =
                                    //         '<strong>Head ID:</strong> ' + responseData.checkHead.head_id +
                                    //         ', <strong>Sub Head:</strong> ' + responseData.checkHead.sub_head;

                                    //     $('#headDetails .card-body').append(checkHeadDiv);
                                    // }

                                    // Show headDetails
                                    $('#headDetails').show();
                                    // $('#assign').show();
                                    $('#submit').hide();
                                    $('#createHead').show();

                                }


                            });

                    } else {

                        $('#headDetails').hide();

                        $('#assign').hide();
                        $('#submit').show();
                        $('#createHead').hide();




                    }


                        if (response.msg_type == 'success') {
                            // alert('asdsa')
                            swal("Success", "Account Head Created Successfully!",
                                "success");


                            window.location.href = "{{ route('admin.create_head') }}";
                        }
                    }
                });

            }
        });


    });
    $(document).on('click', '#resetform', function() {

        $("#company_list").val('');
        $("#account-head-sec").html('');
    });
</script>


<script>
    $(document).ready(function() {
        $('#new_head').blur(function() {
            var title = $(this).val();
            var formData = $('#head').serialize(); // Serialize the form data

            $.ajax({
                url: '{{ route('admin.checkhead.title') }}',
                type: 'POST',
                data: formData + '&title=' +
                    title, // Append the title to the serialized form data
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // alert(response.exists);
                    if (response.exists === '1') {
                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",

                                type: "warning",
                                showCancelButton: false,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",


                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                                    // Iterate over the dataArray
                                    dataArray.forEach(function(item, index) {

                                        const lastItem = dataArray[dataArray
                                            .length - 1];
                                        // var comapnyids = lastItem.company_id;
                                        var companyIds = lastItem.company_id
                                            .split(',').map(function(id) {
                                                return id.trim().replace(
                                                    '[', '').replace(
                                                    ']', ''
                                                ); // Remove any leading/trailing whitespaces and brackets
                                            });

                                        updateCompanies(companyIds);
                                        const id = lastItem.id;
                                        document.getElementById('headId')
                                            .value = id;
                                        const companyIdArray = JSON.parse(
                                            lastItem.company_id);

                                        // Get the last company_id from the array
                                        const lastCompanyId = companyIdArray[
                                            companyIdArray.length - 1];
                                        document.getElementById(
                                                'company_id_input').value =
                                            lastCompanyId;

                                        // Now, set the selected option in the dropdown
                                        var companyIdInputValue = document
                                            .getElementById('company_id_input')
                                            .value;
                                        var companiesSelect = document
                                            .getElementById('comapnies');
                                        // Check if it's an even index (0-based) and there is a second record available
                                        // if (index % 2 === 0 && index + 1 <
                                        //     dataArray.length) {
                                        //     // Create a new div with classes row and form-group
                                        //     var rowDiv = document.createElement(
                                        //         'div');
                                        //     rowDiv.className = 'row form-group';

                                        //     // Create the first column for the first Label
                                        //     var labelColumn1 = document
                                        //         .createElement('div');
                                        //     labelColumn1.className = 'col-2';

                                        //     // Create a new label element for the first Sub Head
                                        //     var subHeadLabel1 = document
                                        //         .createElement('label');
                                        //     subHeadLabel1.innerHTML =
                                        //         'Sub Head:';
                                        //     labelColumn1.appendChild(
                                        //         subHeadLabel1);

                                        //     // Create the second column for the first Input
                                        //     var inputColumn1 = document
                                        //         .createElement('div');
                                        //     inputColumn1.className = 'col-4';

                                        //     // Create a new input element for the first Sub Head
                                        //     var subHeadInput1 = document
                                        //         .createElement('input');
                                        //     subHeadInput1.type = 'text';
                                        //     subHeadInput1.classList.add(
                                        //         'form-control');
                                        //     subHeadInput1.value = item.sub_head;

                                        //     // Make the first Sub Head input read-only
                                        //     subHeadInput1.readOnly = true;

                                        //     // Append the first Sub Head input to the first inputColumn
                                        //     inputColumn1.appendChild(
                                        //         subHeadInput1);

                                        //     // Append the first columns to the rowDiv
                                        //     rowDiv.appendChild(labelColumn1);
                                        //     rowDiv.appendChild(inputColumn1);

                                        //     // Create the third column for the second Label
                                        //     var labelColumn2 = document
                                        //         .createElement('div');
                                        //     labelColumn2.className = 'col-2';

                                        //     // Create a new label element for the second Sub Head
                                        //     var subHeadLabel2 = document
                                        //         .createElement('label');
                                        //     subHeadLabel2.innerHTML =
                                        //         'Sub Head:';
                                        //     labelColumn2.appendChild(
                                        //         subHeadLabel2);

                                        //     // Create the fourth column for the second Input
                                        //     var inputColumn2 = document
                                        //         .createElement('div');
                                        //     inputColumn2.className = 'col-4';

                                        //     // Create a new input element for the second Sub Head
                                        //     var subHeadInput2 = document
                                        //         .createElement('input');
                                        //     subHeadInput2.type = 'text';
                                        //     subHeadInput2.classList.add(
                                        //         'form-control');
                                        //     subHeadInput2.value = dataArray[
                                        //             index + 1]
                                        //         .sub_head; // Use the correct property or value for the second input

                                        //     // Make the second Sub Head input read-only
                                        //     subHeadInput2.readOnly = true;

                                        //     // Append the second Sub Head input to the second inputColumn
                                        //     inputColumn2.appendChild(
                                        //         subHeadInput2);

                                        //     // Append the second columns to the rowDiv
                                        //     rowDiv.appendChild(labelColumn2);
                                        //     rowDiv.appendChild(inputColumn2);

                                        //     // Append the rowDiv to headDetails .card-body
                                        //     $('#headDetails .card-body').append(
                                        //         rowDiv);
                                        // }
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i]
                                                    .labels; // Change 'Label' to 'Head'
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }
                                    });

                                    // Show headDetails
                                    $('#headDetails').show();
                                    $('#assign').show();
                                    $('#submit').hide();
                                    $('#createHead').hide();


                                }

                            });
                    } else if (response.exists == '2') {
                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",

                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                                    // Iterate over the dataArray
                                    dataArray.forEach(function(item, index) {
                                        // Check if it's an even index (0-based) and there is a second record available
                                        // if (index % 2 === 0 && index + 1 <
                                        //     dataArray.length) {
                                        //     // Create a new div with classes row and form-group
                                        //     var rowDiv = document.createElement(
                                        //         'div');
                                        //     rowDiv.className = 'row form-group';

                                        //     // Create the first column for the first Label
                                        //     var labelColumn1 = document
                                        //         .createElement('div');
                                        //     labelColumn1.className = 'col-2';

                                        //     // Create a new label element for the first Sub Head
                                        //     var subHeadLabel1 = document
                                        //         .createElement('label');
                                        //     subHeadLabel1.innerHTML =
                                        //         'Sub Head:';
                                        //     labelColumn1.appendChild(
                                        //         subHeadLabel1);

                                        //     // Create the second column for the first Input
                                        //     var inputColumn1 = document
                                        //         .createElement('div');
                                        //     inputColumn1.className = 'col-4';

                                        //     // Create a new input element for the first Sub Head
                                        //     var subHeadInput1 = document
                                        //         .createElement('input');
                                        //     subHeadInput1.type = 'text';
                                        //     subHeadInput1.classList.add(
                                        //         'form-control');
                                        //     subHeadInput1.value = item.sub_head;

                                        //     // Make the first Sub Head input read-only
                                        //     subHeadInput1.readOnly = true;

                                        //     // Append the first Sub Head input to the first inputColumn
                                        //     inputColumn1.appendChild(
                                        //         subHeadInput1);

                                        //     // Append the first columns to the rowDiv
                                        //     rowDiv.appendChild(labelColumn1);
                                        //     rowDiv.appendChild(inputColumn1);

                                        //     // Create the third column for the second Label
                                        //     var labelColumn2 = document
                                        //         .createElement('div');
                                        //     labelColumn2.className = 'col-2';

                                        //     // Create a new label element for the second Sub Head
                                        //     var subHeadLabel2 = document
                                        //         .createElement('label');
                                        //     subHeadLabel2.innerHTML =
                                        //         'Sub Head:';
                                        //     labelColumn2.appendChild(
                                        //         subHeadLabel2);

                                        //     // Create the fourth column for the second Input
                                        //     var inputColumn2 = document
                                        //         .createElement('div');
                                        //     inputColumn2.className = 'col-4';

                                        //     // Create a new input element for the second Sub Head
                                        //     var subHeadInput2 = document
                                        //         .createElement('input');
                                        //     subHeadInput2.type = 'text';
                                        //     subHeadInput2.classList.add(
                                        //         'form-control');
                                        //     subHeadInput2.value = dataArray[
                                        //             index + 1]
                                        //         .sub_head; // Use the correct property or value for the second input

                                        //     // Make the second Sub Head input read-only
                                        //     subHeadInput2.readOnly = true;

                                        //     // Append the second Sub Head input to the second inputColumn
                                        //     inputColumn2.appendChild(
                                        //         subHeadInput2);

                                        //     // Append the second columns to the rowDiv
                                        //     rowDiv.appendChild(labelColumn2);
                                        //     rowDiv.appendChild(inputColumn2);

                                        //     // Append the rowDiv to headDetails .card-body
                                        //     $('#headDetails .card-body').append(
                                        //         rowDiv);
                                        // }
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i]
                                                    .labels; // Change 'Label' to 'Head'
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }
                                    });

                                    // Show headDetails
                                    $('#headDetails').show();
                                    $('#assign').show();
                                    $('#submit').show();
                                }


                            });
                    } else if (response.exists == '0') {

                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",

                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                                    // Iterate over the dataArray
                                    // dataArray.forEach(function(item, index) {
                                    //     console.log(item);
                                    //     // Check if it's an even index (0-based) and there is a second record available
                                    //     if (index % 2 === 0 && index + 1 <
                                    //         dataArray.length) {
                                    //         // Create a new div with classes row and form-group
                                    //         var rowDiv = document.createElement(
                                    //             'div');
                                    //         rowDiv.className = 'row form-group';

                                    //         // Create the first column for the first Label
                                    //         var labelColumn1 = document
                                    //             .createElement('div');
                                    //         labelColumn1.className = 'col-2';

                                    //         // Create a new label element for the first Sub Head
                                    //         var subHeadLabel1 = document
                                    //             .createElement('label');
                                    //         subHeadLabel1.innerHTML =
                                    //             'Label: ' + item.labels;
                                    //         labelColumn1.appendChild(
                                    //             subHeadLabel1);

                                    //         // Create the second column for the first Input
                                    //         var inputColumn1 = document
                                    //             .createElement('div');
                                    //         inputColumn1.className = 'col-4';

                                    //         // Create a new input element for the first Sub Head
                                    //         var subHeadInput1 = document
                                    //             .createElement('input');
                                    //         subHeadInput1.type = 'text';
                                    //         subHeadInput1.classList.add(
                                    //             'form-control');
                                    //         subHeadInput1.value = item.sub_head;

                                    //         // Make the first Sub Head input read-only
                                    //         subHeadInput1.readOnly = true;

                                    //         // Append the first Sub Head input to the first inputColumn
                                    //         inputColumn1.appendChild(
                                    //             subHeadInput1);

                                    //         // Append the first columns to the rowDiv
                                    //         rowDiv.appendChild(labelColumn1);
                                    //         rowDiv.appendChild(inputColumn1);

                                    //         // Create the third column for the second Label
                                    //         var labelColumn2 = document
                                    //             .createElement('div');
                                    //         labelColumn2.className = 'col-2';

                                    //         // Create a new label element for the second Sub Head
                                    //         var subHeadLabel2 = document
                                    //             .createElement('label');
                                    //         subHeadLabel2.innerHTML =
                                    //             'Sub Head:';
                                    //         labelColumn2.appendChild(
                                    //             subHeadLabel2);

                                    //         // Create the fourth column for the second Input
                                    //         var inputColumn2 = document
                                    //             .createElement('div');
                                    //         inputColumn2.className = 'col-4';

                                    //         // Create a new input element for the second Sub Head
                                    //         var subHeadInput2 = document
                                    //             .createElement('input');
                                    //         subHeadInput2.type = 'text';
                                    //         subHeadInput2.classList.add(
                                    //             'form-control');
                                    //         subHeadInput2.value = dataArray[
                                    //                 index + 1]
                                    //             .sub_head; // Use the correct property or value for the second input

                                    //         // Make the second Sub Head input read-only
                                    //         subHeadInput2.readOnly = true;

                                    //         // Append the second Sub Head input to the second inputColumn
                                    //         inputColumn2.appendChild(
                                    //             subHeadInput2);

                                    //         // Append the second columns to the rowDiv
                                    //         rowDiv.appendChild(labelColumn2);
                                    //         rowDiv.appendChild(inputColumn2);

                                    //         // Append the rowDiv to headDetails .card-body
                                    //         $('#headDetails .card-body').append(
                                    //             rowDiv);
                                    //     }
                                    // });


                                    dataArray.forEach(function(item, index) {
                                        console.log(item);
                                        const lastItem = dataArray[dataArray
                                            .length - 1];
                                        // var comapnyids = lastItem.company_id;
                                        var companyIds = lastItem.company_id
                                            .split(',').map(function(id) {
                                                return id.trim().replace(
                                                    '[', '').replace(
                                                    ']', ''
                                                ); // Remove any leading/trailing whitespaces and brackets
                                            });

                                        console.log('companyIds', companyIds);
                                        updateCompanies(companyIds);
                                        const id = lastItem.id;
                                        document.getElementById('headId')
                                            .value = id;
                                        const companyIdArray = JSON.parse(
                                            lastItem.company_id);

                                        // Get the last company_id from the array
                                        const lastCompanyId = companyIdArray[
                                            companyIdArray.length - 1];
                                        document.getElementById(
                                                'company_id_input').value =
                                            lastCompanyId;

                                        // Now, set the selected option in the dropdown
                                        var companyIdInputValue = document
                                            .getElementById('company_id_input')
                                            .value;
                                        var companiesSelect = document
                                            .getElementById('comapnies');
                                        // Check if it's an even index (0-based) and there is a second record available
                                        // if (index % 2 === 0 && index + 1 <
                                        //     dataArray.length) {
                                        //     // Create a new div with classes row and form-group
                                        //     var rowDiv = document.createElement(
                                        //         'div');
                                        //     rowDiv.className = 'row form-group';

                                        //     // Loop through the two records at even and odd indices
                                        //     for (var i = 0; i < 2; i++) {
                                        //         // Create the column for the Label
                                        //         var labelColumn = document
                                        //             .createElement('div');
                                        //         labelColumn.className = 'col-2';

                                        //         // Create a new label element for the Label
                                        //         var subHeadLabel = document
                                        //             .createElement('label');
                                        //         subHeadLabel.innerHTML =
                                        //             'Label: ' + dataArray[
                                        //                 index + i].labels;
                                        //         labelColumn.appendChild(
                                        //             subHeadLabel);

                                        //         // Create the column for the Input
                                        //         var inputColumn = document
                                        //             .createElement('div');
                                        //         inputColumn.className = 'col-4';

                                        //         // Create a new input element for the Label
                                        //         var subHeadInput = document
                                        //             .createElement('input');
                                        //         subHeadInput.type = 'text';
                                        //         subHeadInput.classList.add(
                                        //             'form-control');
                                        //         subHeadInput.value = dataArray[
                                        //             index + i].sub_head;

                                        //         // Make the Label input read-only
                                        //         subHeadInput.readOnly = true;

                                        //         // Append the Label input to the inputColumn
                                        //         inputColumn.appendChild(
                                        //             subHeadInput);

                                        //         // Append the columns to the rowDiv
                                        //         rowDiv.appendChild(labelColumn);
                                        //         rowDiv.appendChild(inputColumn);
                                        //     }

                                        //     // Append the rowDiv to headDetails .card-body
                                        //     $('#headDetails .card-body').append(
                                        //         rowDiv);
                                        // }
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i]
                                                    .labels; // Change 'Label' to 'Head'
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }
                                    });





                                    // Show headDetails
                                    $('#headDetails').show();
                                    $('#assign').hide();
                                    $('#submit').hide();
                                    $('#createHead').hide();


                                }

                            });

                    } else if (response.exists == '4') {

                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",

                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                                    // Iterate over the dataArray
                                    // dataArray.forEach(function(item, index) {


                                    //     const lastItem = dataArray[dataArray
                                    //         .length - 1];
                                    //     // var comapnyids = lastItem.company_id;
                                    //     var companyIds = lastItem.company_id
                                    //         .split(',').map(function(id) {
                                    //             return id.trim().replace(
                                    //                 '[', '').replace(
                                    //                 ']', ''
                                    //             ); // Remove any leading/trailing whitespaces and brackets
                                    //         });

                                    //     updateCompanies(companyIds);

                                    //     const id = lastItem.id;
                                    //     document.getElementById('headId')
                                    //         .value = id;
                                    //     const companyIdArray = JSON.parse(
                                    //         lastItem.company_id);

                                    //     // Get the last company_id from the array
                                    //     const lastCompanyId = companyIdArray[
                                    //         companyIdArray.length - 1];
                                    //     document.getElementById(
                                    //             'company_id_input').value =
                                    //         lastCompanyId;

                                    //     // Now, set the selected option in the dropdown
                                    //     var companyIdInputValue = document
                                    //         .getElementById('company_id_input')
                                    //         .value;
                                    //     var companiesSelect = document
                                    //         .getElementById('comapnies');


                                    //     // Check if it's an even index (0-based) and there is a second record available
                                    //     if (index % 2 === 0 && index + 1 <
                                    //         dataArray.length) {
                                    //         // Create a new div with classes row and form-group
                                    //         var rowDiv = document.createElement(
                                    //             'div');
                                    //         rowDiv.className = 'row form-group';

                                    //         // Create the first column for the first Label
                                    //         var labelColumn1 = document
                                    //             .createElement('div');
                                    //         labelColumn1.className = 'col-2';

                                    //         // Create a new label element for the first Sub Head
                                    //         var subHeadLabel1 = document
                                    //             .createElement('label');
                                    //         subHeadLabel1.innerHTML =
                                    //             'Sub Head:';
                                    //         labelColumn1.appendChild(
                                    //             subHeadLabel1);

                                    //         // Create the second column for the first Input
                                    //         var inputColumn1 = document
                                    //             .createElement('div');
                                    //         inputColumn1.className = 'col-4';

                                    //         // Create a new input element for the first Sub Head
                                    //         var subHeadInput1 = document
                                    //             .createElement('input');
                                    //         subHeadInput1.type = 'text';
                                    //         subHeadInput1.classList.add(
                                    //             'form-control');
                                    //         subHeadInput1.value = item.sub_head;

                                    //         // Make the first Sub Head input read-only
                                    //         subHeadInput1.readOnly = true;

                                    //         // Append the first Sub Head input to the first inputColumn
                                    //         inputColumn1.appendChild(
                                    //             subHeadInput1);

                                    //         // Append the first columns to the rowDiv
                                    //         rowDiv.appendChild(labelColumn1);
                                    //         rowDiv.appendChild(inputColumn1);

                                    //         // Create the third column for the second Label
                                    //         var labelColumn2 = document
                                    //             .createElement('div');
                                    //         labelColumn2.className = 'col-2';

                                    //         // Create a new label element for the second Sub Head
                                    //         var subHeadLabel2 = document
                                    //             .createElement('label');
                                    //         subHeadLabel2.innerHTML =
                                    //             'Sub Head:';
                                    //         labelColumn2.appendChild(
                                    //             subHeadLabel2);

                                    //         // Create the fourth column for the second Input
                                    //         var inputColumn2 = document
                                    //             .createElement('div');
                                    //         inputColumn2.className = 'col-4';

                                    //         // Create a new input element for the second Sub Head
                                    //         var subHeadInput2 = document
                                    //             .createElement('input');
                                    //         subHeadInput2.type = 'text';
                                    //         subHeadInput2.classList.add(
                                    //             'form-control');
                                    //         subHeadInput2.value = dataArray[
                                    //                 index + 1]
                                    //             .sub_head; // Use the correct property or value for the second input

                                    //         // Make the second Sub Head input read-only
                                    //         subHeadInput2.readOnly = true;

                                    //         // Append the second Sub Head input to the second inputColumn
                                    //         inputColumn2.appendChild(
                                    //             subHeadInput2);

                                    //         // Append the second columns to the rowDiv
                                    //         rowDiv.appendChild(labelColumn2);
                                    //         rowDiv.appendChild(inputColumn2);

                                    //         // Append the rowDiv to headDetails .card-body
                                    //         $('#headDetails .card-body').append(
                                    //             rowDiv);
                                    //     }
                                    // });


                                    dataArray.forEach(function(item, index) {
                                        console.log(item);
                                        const lastItem = dataArray[dataArray
                                            .length - 1];
                                        // var comapnyids = lastItem.company_id;
                                        var companyIds = lastItem.company_id
                                            .split(',').map(function(id) {
                                                return id.trim().replace(
                                                    '[', '').replace(
                                                    ']', ''
                                                ); // Remove any leading/trailing whitespaces and brackets
                                            });

                                        console.log('companyIds', companyIds);
                                        updateCompanies(companyIds);
                                        const id = lastItem.id;
                                        document.getElementById('headId')
                                            .value = id;
                                        const companyIdArray = JSON.parse(
                                            lastItem.company_id);

                                        // Get the last company_id from the array
                                        const lastCompanyId = companyIdArray[
                                            companyIdArray.length - 1];
                                        document.getElementById(
                                                'company_id_input').value =
                                            lastCompanyId;

                                        // Now, set the selected option in the dropdown
                                        var companyIdInputValue = document
                                            .getElementById('company_id_input')
                                            .value;
                                        var companiesSelect = document
                                            .getElementById('comapnies');

                                        // Check if it's an even index (0-based) and there is a second record available
                                        // if (index % 2 === 0 && index + 1 <
                                        //     dataArray.length) {
                                        //     // Create a new div with classes row and form-group
                                        //     var rowDiv = document.createElement(
                                        //         'div');
                                        //     rowDiv.className = 'row form-group';


                                        //     // Loop through the two records at even and odd indices
                                        //     for (var i = 0; i < 2; i++) {
                                        //         // Create the column for the Label
                                        //         var labelColumn = document
                                        //             .createElement('div');
                                        //         labelColumn.className = 'col-2';

                                        //         // Create a new label element for the Label
                                        //         var subHeadLabel = document
                                        //             .createElement('label');
                                        //         subHeadLabel.innerHTML =
                                        //             'Label: ' + dataArray[
                                        //                 index + i].labels;
                                        //         labelColumn.appendChild(
                                        //             subHeadLabel);

                                        //         // Create the column for the Input
                                        //         var inputColumn = document
                                        //             .createElement('div');
                                        //         inputColumn.className = 'col-4';

                                        //         // Create a new input element for the Label
                                        //         var subHeadInput = document
                                        //             .createElement('input');
                                        //         subHeadInput.type = 'text';
                                        //         subHeadInput.classList.add(
                                        //             'form-control');
                                        //         subHeadInput.value = dataArray[
                                        //             index + i].sub_head;

                                        //         // Make the Label input read-only
                                        //         subHeadInput.readOnly = true;

                                        //         // Append the Label input to the inputColumn
                                        //         inputColumn.appendChild(
                                        //             subHeadInput);

                                        //         // Append the columns to the rowDiv
                                        //         rowDiv.appendChild(labelColumn);
                                        //         rowDiv.appendChild(inputColumn);
                                        //     }

                                        //     // Append the rowDiv to headDetails .card-body
                                        //     $('#headDetails .card-body').append(
                                        //         rowDiv);
                                        // }
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i]
                                                    .labels; // Change 'Label' to 'Head'
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }

                                    });








                                    // If checkHead is present, append it as well
                                    // if (responseData.checkHead) {
                                    //     var checkHeadDiv = document.createElement('div');
                                    //     checkHeadDiv.innerHTML =
                                    //         '<strong>Head ID:</strong> ' + responseData.checkHead.head_id +
                                    //         ', <strong>Sub Head:</strong> ' + responseData.checkHead.sub_head;

                                    //     $('#headDetails .card-body').append(checkHeadDiv);
                                    // }

                                    // Show headDetails
                                    $('#headDetails').show();
                                    $('#assign').show();
                                    $('#submit').hide();
                                    $('#createHead').hide();

                                }


                            });

                    } else if (response.exists == '3') {

                        swal({
                                title: "Are you sure?",
                                text: "The head has already been created. Please click on OK button to view the details!",

                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-primary delete_cheque",
                                confirmButtonText: "OK",

                            },
                            function(result) {
                                if (result) {
                                    var responseData = response.data;

                                    // Convert the object into an array
                                    var dataArray = Object.values(responseData);

                                    // Clear existing content in headDetails
                                    $('#headDetails .card-body').empty();

                                    // Iterate over the dataArray
                                    // dataArray.forEach(function(item, index) {


                                    //     const lastItem = dataArray[dataArray
                                    //         .length - 1];
                                    //     // var comapnyids = lastItem.company_id;
                                    //     var companyIds = lastItem.company_id
                                    //         .split(',').map(function(id) {
                                    //             return id.trim().replace(
                                    //                 '[', '').replace(
                                    //                 ']', ''
                                    //             ); // Remove any leading/trailing whitespaces and brackets
                                    //         });

                                    //     updateCompanies(companyIds);

                                    //     const id = lastItem.id;
                                    //     document.getElementById('headId')
                                    //         .value = id;
                                    //     const companyIdArray = JSON.parse(
                                    //         lastItem.company_id);

                                    //     // Get the last company_id from the array
                                    //     const lastCompanyId = companyIdArray[
                                    //         companyIdArray.length - 1];
                                    //     document.getElementById(
                                    //             'company_id_input').value =
                                    //         lastCompanyId;

                                    //     // Now, set the selected option in the dropdown
                                    //     var companyIdInputValue = document
                                    //         .getElementById('company_id_input')
                                    //         .value;
                                    //     var companiesSelect = document
                                    //         .getElementById('comapnies');
                                    //     // console.log('adsa',companiesSelect)
                                    //     // for (var i = 0; i < companiesSelect
                                    //     //     .options.length; i++) {

                                    //     //     if (companiesSelect.options[i]
                                    //     //         .value == companyIdInputValue) {
                                    //     //         companiesSelect.remove(i);
                                    //     //         break;
                                    //     //     }
                                    //     // }

                                    //     // Check if it's an even index (0-based) and there is a second record available
                                    //     if (index % 2 === 0 && index + 1 <
                                    //         dataArray.length) {
                                    //         // Create a new div with classes row and form-group
                                    //         var rowDiv = document.createElement(
                                    //             'div');
                                    //         rowDiv.className = 'row form-group';

                                    //         // Create the first column for the first Label
                                    //         var labelColumn1 = document
                                    //             .createElement('div');
                                    //         labelColumn1.className = 'col-2';

                                    //         // Create a new label element for the first Sub Head
                                    //         var subHeadLabel1 = document
                                    //             .createElement('label');
                                    //         subHeadLabel1.innerHTML =
                                    //             'Sub Head:';
                                    //         labelColumn1.appendChild(
                                    //             subHeadLabel1);

                                    //         // Create the second column for the first Input
                                    //         var inputColumn1 = document
                                    //             .createElement('div');
                                    //         inputColumn1.className = 'col-4';

                                    //         // Create a new input element for the first Sub Head
                                    //         var subHeadInput1 = document
                                    //             .createElement('input');
                                    //         subHeadInput1.type = 'text';
                                    //         subHeadInput1.classList.add(
                                    //             'form-control');
                                    //         subHeadInput1.value = item.sub_head;

                                    //         // Make the first Sub Head input read-only
                                    //         subHeadInput1.readOnly = true;

                                    //         // Append the first Sub Head input to the first inputColumn
                                    //         inputColumn1.appendChild(
                                    //             subHeadInput1);

                                    //         // Append the first columns to the rowDiv
                                    //         rowDiv.appendChild(labelColumn1);
                                    //         rowDiv.appendChild(inputColumn1);

                                    //         // Create the third column for the second Label
                                    //         var labelColumn2 = document
                                    //             .createElement('div');
                                    //         labelColumn2.className = 'col-2';

                                    //         // Create a new label element for the second Sub Head
                                    //         var subHeadLabel2 = document
                                    //             .createElement('label');
                                    //         subHeadLabel2.innerHTML =
                                    //             'Sub Head:';
                                    //         labelColumn2.appendChild(
                                    //             subHeadLabel2);

                                    //         // Create the fourth column for the second Input
                                    //         var inputColumn2 = document
                                    //             .createElement('div');
                                    //         inputColumn2.className = 'col-4';

                                    //         // Create a new input element for the second Sub Head
                                    //         var subHeadInput2 = document
                                    //             .createElement('input');
                                    //         subHeadInput2.type = 'text';
                                    //         subHeadInput2.classList.add(
                                    //             'form-control');
                                    //         subHeadInput2.value = dataArray[
                                    //                 index + 1]
                                    //             .sub_head; // Use the correct property or value for the second input

                                    //         // Make the second Sub Head input read-only
                                    //         subHeadInput2.readOnly = true;

                                    //         // Append the second Sub Head input to the second inputColumn
                                    //         inputColumn2.appendChild(
                                    //             subHeadInput2);

                                    //         // Append the second columns to the rowDiv
                                    //         rowDiv.appendChild(labelColumn2);
                                    //         rowDiv.appendChild(inputColumn2);

                                    //         // Append the rowDiv to headDetails .card-body
                                    //         $('#headDetails .card-body').append(
                                    //             rowDiv);
                                    //     }
                                    // });



                                    dataArray.forEach(function(item, index) {
                                        console.log(item);

                                        const lastItem = dataArray[dataArray
                                            .length - 1];
                                        // var comapnyids = lastItem.company_id;
                                        var companyIds = lastItem.company_id
                                            .split(',').map(function(id) {
                                                return id.trim().replace(
                                                    '[', '').replace(
                                                    ']', ''
                                                ); // Remove any leading/trailing whitespaces and brackets
                                            });

                                        console.log('companyIds', companyIds);
                                        updateCompanies(companyIds);
                                        const id = lastItem.id;
                                        document.getElementById('headId')
                                            .value = id;
                                        const companyIdArray = JSON.parse(
                                            lastItem.company_id);

                                        // Get the last company_id from the array
                                        const lastCompanyId = companyIdArray[
                                            companyIdArray.length - 1];
                                        document.getElementById(
                                                'company_id_input').value =
                                            lastCompanyId;

                                        // Now, set the selected option in the dropdown
                                        var companyIdInputValue = document
                                            .getElementById('company_id_input')
                                            .value;
                                        var companiesSelect = document
                                            .getElementById('comapnies');
                                        // Check if it's an even index (0-based) and there is a second record available
                                        // if (index % 2 === 0 && index + 1 <
                                        //     dataArray.length) {
                                        //     // Create a new div with classes row and form-group
                                        //     var rowDiv = document.createElement(
                                        //         'div');
                                        //     rowDiv.className = 'row form-group';

                                        //     // Loop through the two records at even and odd indices
                                        //     for (var i = 0; i < 2; i++) {
                                        //         // Create the column for the Label
                                        //         var labelColumn = document
                                        //             .createElement('div');
                                        //         labelColumn.className = 'col-2';

                                        //         // Create a new label element for the Label
                                        //         var subHeadLabel = document
                                        //             .createElement('label');
                                        //         subHeadLabel.innerHTML =
                                        //             'Label: ' + dataArray[
                                        //                 index + i].labels;
                                        //         labelColumn.appendChild(
                                        //             subHeadLabel);

                                        //         // Create the column for the Input
                                        //         var inputColumn = document
                                        //             .createElement('div');
                                        //         inputColumn.className = 'col-4';

                                        //         // Create a new input element for the Label
                                        //         var subHeadInput = document
                                        //             .createElement('input');
                                        //         subHeadInput.type = 'text';
                                        //         subHeadInput.classList.add(
                                        //             'form-control');
                                        //         subHeadInput.value = dataArray[
                                        //             index + i].sub_head;

                                        //         // Make the Label input read-only
                                        //         subHeadInput.readOnly = true;

                                        //         // Append the Label input to the inputColumn
                                        //         inputColumn.appendChild(
                                        //             subHeadInput);

                                        //         // Append the columns to the rowDiv
                                        //         rowDiv.appendChild(labelColumn);
                                        //         rowDiv.appendChild(inputColumn);
                                        //     }

                                        //     // Append the rowDiv to headDetails .card-body
                                        //     $('#headDetails .card-body').append(
                                        //         rowDiv);
                                        // }

                                        // if (index % 2 === 0 && index + 1 <
                                        //     dataArray.length) {
                                        //     // Create a new div with classes row and form-group
                                        //     var rowDiv = document.createElement(
                                        //         'div');
                                        //     rowDiv.className = 'row form-group';

                                        //     // Loop through the two records at even and odd indices
                                        //     for (var i = 0; i < 2; i++) {
                                        //         // Create the column for the Head
                                        //         var labelColumn = document
                                        //             .createElement('div');
                                        //         labelColumn.className = 'col-2';

                                        //         // Create a new label element for the Head
                                        //         var subHeadLabel = document
                                        //             .createElement('label');
                                        //         subHeadLabel.innerHTML =
                                        //             'Head: ' + dataArray[index +
                                        //                 i]
                                        //             .labels; // Change 'Label' to 'Head'
                                        //         labelColumn.appendChild(
                                        //             subHeadLabel);

                                        //         // Create the column for the Input
                                        //         var inputColumn = document
                                        //             .createElement('div');
                                        //         inputColumn.className = 'col-4';

                                        //         // Create a new input element for the Head
                                        //         var subHeadInput = document
                                        //             .createElement('input');
                                        //         subHeadInput.type = 'text';
                                        //         subHeadInput.classList.add(
                                        //             'form-control');
                                        //         subHeadInput.value = dataArray[
                                        //             index + i].sub_head;

                                        //         // Make the Head input read-only
                                        //         subHeadInput.readOnly = true;

                                        //         // Append the Head input to the inputColumn
                                        //         inputColumn.appendChild(
                                        //             subHeadInput);

                                        //         // Append the columns to the rowDiv
                                        //         rowDiv.appendChild(labelColumn);
                                        //         rowDiv.appendChild(inputColumn);
                                        //     }

                                        //     // Append the rowDiv to headDetails .card-body
                                        //     $('#headDetails .card-body').append(
                                        //         rowDiv);
                                        // }
                                        if (index % 2 === 0 && index + 1 <
                                            dataArray.length) {
                                            // Create a new div with classes row and form-group
                                            var rowDiv = document.createElement(
                                                'div');
                                            rowDiv.className = 'row form-group';

                                            // Loop through the two records at even and odd indices
                                            for (var i = 0; i < 2; i++) {
                                                // Create the column for the Head
                                                var labelColumn = document
                                                    .createElement('div');
                                                labelColumn.className = 'col-2';

                                                // Create a new label element for the Head
                                                var subHeadLabel = document
                                                    .createElement('label');
                                                subHeadLabel.innerHTML =
                                                    'Head: ' + dataArray[index +
                                                        i].labels;
                                                labelColumn.appendChild(
                                                    subHeadLabel);

                                                // Create the column for the Input
                                                var inputColumn = document
                                                    .createElement('div');
                                                inputColumn.className = 'col-4';

                                                // Create a new input element for the Head
                                                var subHeadInput = document
                                                    .createElement('input');
                                                subHeadInput.type = 'text';
                                                subHeadInput.classList.add(
                                                    'form-control');
                                                subHeadInput.value = dataArray[
                                                    index + i].sub_head;

                                                // Make the Head input read-only
                                                subHeadInput.readOnly = true;

                                                // Append the Head input to the inputColumn
                                                inputColumn.appendChild(
                                                    subHeadInput);

                                                // Append the columns to the rowDiv
                                                rowDiv.appendChild(labelColumn);
                                                rowDiv.appendChild(inputColumn);
                                            }

                                            // Append the rowDiv to headDetails .card-body
                                            $('#headDetails .card-body').append(
                                                rowDiv);
                                        }

                                    });







                                    // If checkHead is present, append it as well
                                    // if (responseData.checkHead) {
                                    //     var checkHeadDiv = document.createElement('div');
                                    //     checkHeadDiv.innerHTML =
                                    //         '<strong>Head ID:</strong> ' + responseData.checkHead.head_id +
                                    //         ', <strong>Sub Head:</strong> ' + responseData.checkHead.sub_head;

                                    //     $('#headDetails .card-body').append(checkHeadDiv);
                                    // }

                                    // Show headDetails
                                    $('#headDetails').show();
                                    // $('#assign').show();
                                    $('#submit').hide();
                                    $('#createHead').show();

                                }


                            });

                    } else {

                        $('#headDetails').hide();

                        $('#assign').hide();
                        $('#submit').show();
                        $('#createHead').hide();




                    }
                },
                error: function() {
                    console.log('Error in Ajax request');
                }
            });
        });
    });


    $(document).ready(function() {
        $("#assign").click(function() {
            // Show the select box container when the button is clicked
            $("#selectBoxContainer").show();
        });
    });
    $(document).ready(function() {
        console.log("Document ready!");

        $('#createHeads').on('change', function() {
            console.log("Checkbox changed!");

            if ($(this).is(':checked')) {
                console.log("Checkbox is checked!");
                $('#submit').show();
                $('#companies').hide();
                $('#assign').hide();
                $('#error-message').hide();


            } else {
                console.log("Checkbox is unchecked!");
                $('#submit').hide();
                $('#companies').show();
                $('#assign').show();
            }
        });
    });
</script>

<script>
    // $(document).ready(function() {
    //     // Function to handle the click event on the "Assign" button
    //     $('#assign').on('click', function() {
    //         // Get selected values from the multi-select dropdown
    //         var selectedCompanies = $('#companies').val();
    //         var headId = $('#headId').val();

    //         // Make an AJAX request to save the data
    //         $.ajax({
    //             url: "{{ route('admin.updateCompanyHead') }}", // Replace with your Laravel route
    //             method: 'POST',
    //             data: {
    //                 selected_companies: selectedCompanies,
    //                 headId: headId
    //             },
    //             success: function(response) {
    //                 if (response.msg_type == 'success') {
    //                     swal('Success!', "Company assigned successfully !!.", 'success');

    //                         location
    //                             .reload(); // Corrected method for reloading the page

    //                 } else {


    //                     swal('Error!', "No company selected!!", 'error');
    //                 }

    //             },
    //             error: function(error) {
    //                 // Handle the error response
    //                 console.log(error);
    //             }
    //         });
    //     });
    // });

    $(document).ready(function() {
        // Function to handle the click event on the "Assign" button
        $('#assign').on('click', function() {
            // Get selected values from the multi-select dropdown
            var selectedCompanies = $('#companies').val();
            var headId = $('#headId').val();
            var systemdate = $('#create_application_date').val();
            // Check if at least one option is selected
            if (selectedCompanies && selectedCompanies.length > 0) {
                // Make an AJAX request to save the data
                $.ajax({
                    url: "{{ route('admin.updateCompanyHead') }}", // Replace with your Laravel route
                    method: 'POST',
                    data: {
                        selected_companies: selectedCompanies,
                        headId: headId,
                        systemdate:systemdate
                        
                    },
                    success: function(response) {
                        if (response.msg_type == 'success') {
                            swal('Success!', "Company assigned successfully !!.",
                                'success');
                            location.reload(); // Corrected method for reloading the page
                        } else {
                            swal('Error!', "No company selected!!", 'error');
                        }
                    },
                    error: function(error) {
                        // Handle the error response
                        console.log(error);
                    }
                });
            } else {
                // Display an error message if no option is selected
                $('#error-message').text('Please select at least one company.');
            }
        });
    });





    function updateCompanies(ids) {
        //    alert('adsa');
        console.log(ids)

        var count = ids.length;
        if (count != 3) {
            getCompanies(ids);
            if (Array.isArray(ids)) {
                $('#assign').show();
                ids.forEach(function(id) {

                    var optionToRemove = $('#companies option[value="' + id + '"]');

                    console.log('dsad', optionToRemove);
                    if (optionToRemove.length > 0) {

                        optionToRemove.remove();
                    } else {
                        console.warn('Option with value ' + id + ' not found.');
                    }
                });
            } else {
                console.error('ids must be an array.');
            }
        } else {
            getCompanies(ids);
            if (Array.isArray(ids)) {
                $('#assign').show();
                ids.forEach(function(id) {

                    var optionToRemove = $('#companies option[value="' + id + '"]');

                    console.log('dsad', optionToRemove);
                    if (optionToRemove.length > 0) {

                        optionToRemove.remove();
                    } else {
                        console.warn('Option with value ' + id + ' not found.');
                    }
                });
            } else {
                console.error('ids must be an array.');
            }
            $('#companies').hide();
            $('#assign').hide();
            $('#text_companies').show().css('color', 'red');


        }

    }



    function getCompanies(ids) {
        console.log(ids);

        $.ajax({
            url: "{{ route('admin.getCompanies') }}",
            method: 'POST',
            data: {
                companyIds: ids,
            },
            success: function(response) {
                if (response.message_type === 'success') {
                    console.log(response);
                    console.log('dasdsad', Array.isArray(response.comapnies));

                    // Check if response.companies is defined and is an array
                    if (Array.isArray(response.comapnies)) {
                        var companiesArray = response.comapnies;
                        var companiesHead = '';

                        companiesArray.forEach(function(item, index) {

                            if (item && item.name) {
                                companiesHead += item.name + ', '; // Append with comma
                            }
                        });

                        console.log(companiesHead.trim());

                        // Append company names to the specified div
                        $("#compies_head .card-body").text('Companies: ' + companiesHead.trim().slice(0, -
                            1));

                    } else {
                        console.error("Invalid or missing response.companies.");
                    }
                } else {
                    swal('Error!', "No company selected!!", 'error');
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }
</script>
