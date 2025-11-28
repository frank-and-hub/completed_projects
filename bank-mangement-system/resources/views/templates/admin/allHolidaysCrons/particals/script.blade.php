<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<script type="text/javascript">
    function addNewHolidayCron() {
        $('#myModal').modal('show');
    }

    $('#effective_to_model_update').datepicker({

format: "dd/mm/yyyy",
orientation: "top",
autoclose: true,
startDate: "today",



});

$('#effective_to_model').datepicker({

format: "dd/mm/yyyy",
orientation: "top",
autoclose: true,
startDate: "today",



});


function submitUpdateEffectiveToDate() {
    $(".error-message").text("");
    
    // Get the values of all input fields
    var corn_title = $('#corn_title').val();
    var cron_name = $('#cron_name').val();
    var templateId = $('#templateId').val();
    var effective_to_model = $('#effective_to_model').val();
    var message = $('#message').val();
    
    // Check if any of the fields are empty
    if (!corn_title || !cron_name || !templateId || !effective_to_model || !message) {
        // Show error messages for empty fields
        if (!corn_title) $("#corn_title_error").text("Title is required.");
        if (!cron_name) $("#cron_name_error").text("Cron Name is required.");
        if (!templateId) $("#templateId_error").text("Template Id is required.");
        if (!effective_to_model) $("#effective_to_model_error").text("Date is required.");
        if (!message) $("#message_error").text("Message is required.");
        return; // Exit the function early
    }

    // If all fields are filled, proceed with the AJAX request
    $.ajax({
        type: "POST",
        url: "{!! route('admin.allholiday.crons.save') !!}",
        dataType: 'JSON',
        data: {
            'corn_title': corn_title,
            'cron_name': cron_name,
            'templateId': templateId,
            'effective_to_model': effective_to_model,
            'message': message,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if(response.msg == 'true'){
                // Swal.fire({
                //     text: "Cron Added Successfully !!",
                //     icon: 'success',
                //     showCancelButton: false,
                //     confirmButtonColor: '#3085d6',
                // }).then((result) => {
                //     if (result.isConfirmed) {
                //         window.location.reload();
                //     }
                // });
                swal({
                      title: 'Success!',
                      text: 'Cron Added Successfully',
                      type: "success"
                    }, function(result) {
                      if (result) {
                        window.location.reload();
                        $('#myModal').find('input[type=text], input[type=number], input[type=email], input[type=password], textarea, select').val('');

                      }
                    });
            }
        },
        error: function (xhr, status, error) {
            // Handle AJAX errors
            console.error(xhr.responseText);
        }
    });
}



function formatDate(dateString) {
    // Convert dateString to a Date object
    var date = new Date(dateString);
    
    // Get day, month, and year components
    var day = date.getDate();
    var month = date.getMonth() + 1; // Months are zero-based
    var year = date.getFullYear();

    // Add leading zeros if necessary
    if (day < 10) {
        day = '0' + day;
    }
    if (month < 10) {
        month = '0' + month;
    }

    // Format as dd/mm/yy
    return day + '/' + month + '/' + year;
}


    function updateNewHolidayCron(id, title, date, templateId, cronname,message) {
        
        
       

        
        $('#corn_title_update').val(title);
        $('#cron_name_update').val(cronname);
        $('#templateId_update').val(templateId);
  
        $('#message_update').val(message);
        $('#modelid').val(id);

        console.log(date);
      
var formattedDate = formatDate(date); // Format date
$('#effective_to_model_update').val(formattedDate); // Set input value

        $('#myModalupdate').modal('show');
    }

    function updateCronUpdate() {
        $('#myModalupdate').modal('hide');
        var corn_title = $('#corn_title_update').val();
        var cron_name = $('#cron_name_update').val();
        var templateId = $('#templateId_update').val();
        var effective_to_model = $('#effective_to_model_update').val();
        var message_update = $('#message_update').val();
        var id = $('#modelid').val();

        $.ajax({
            type: "POST",
            url: "{!! route('admin.allholiday.crons.save') !!}", // Provide the URL for updating cron here
            dataType: 'JSON',
            data: {
                'corn_title': corn_title,
                'cron_name': cron_name,
                'templateId': templateId,
                'effective_to_model': effective_to_model,
                'message': message_update,
                'id': id,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if(response.msg == 'true'){
                    // Swal.fire({
                    //     text: "Cron Updated Successfully !!",
                    //     icon: 'success',
                    //     showCancelButton: false,
                    //     confirmButtonColor: '#3085d6',
                    // }).then((result) => {
                    //     if (result.isConfirmed) {
                    //         window.location.reload();
                    //     }
                    // });
                    swal({
                      title: 'Success!',
                      text: 'Cron Updated Successfully ',
                      type: "success"
                    }, function(result) {
                      if (result) {
                        window.location.reload();
                      }
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle AJAX errors
                console.error(xhr.responseText);
            }
        });
    }

    function updateNewHolidayCronStatus(cronid){
        $.ajax({
            type: "POST",
            url: "{!! route('admin.allholiday.crons.status') !!}", // Provide the URL for updating cron here
            dataType: 'JSON',
            data: {
               
                'id': cronid,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if(response.msg == 'true'){
                    swal({
                      title: 'Success!',
                      text: 'Status Updated Sucessfully !!',
                      type: "success"
                    });
                    // Swal.fire({
                    //     text: "Cron Status Updated Successfully !!",
                    //     icon: 'success',
                    //     showCancelButton: false,
                    //     confirmButtonColor: '#3085d6',
                    // })
                    window.location.reload();
                }
            },
            error: function (xhr, status, error) {
                // Handle AJAX errors
                console.error(xhr.responseText);
            }
        });
    }
</script>

<script>
    $(document).ready(function() {
        // Initialize datepicker
        $("#effective_to_model_update").datepicker({
            dateFormat: "dd/mm/yy"  // Set date format
        });
    });
</script>

<script>

var modal = document.getElementById("myModal");


var closeButton = modal.querySelector(".close");


var form = document.getElementById("HolidaysCron");


closeButton.addEventListener("click", function() {
  form.reset();
});
</script>


