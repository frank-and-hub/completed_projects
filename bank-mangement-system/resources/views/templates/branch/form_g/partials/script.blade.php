<script type="text/javascript">
    $(document).ready(function(){

     $('#update_15_g').validate({
        rules:{
            year:{
				required:true,
				},
            file:{
                required: true,
                accept: "application/pdf",

            },
            company_id:{
				required:true,
			}
        },
        messages: {
            file: {
                required: "Please select a file.",
                accept: "Please select a PDF file.",
            },
			year: {
                required: "Please select a year.",
            },
			company_id: {
                required: "Please select a Company.",
            }
        },
     })

    $("#update_15_g").on('submit', function(e){
        e.preventDefault();
        if($('#update_15_g').valid()){
            let formData = new FormData(this);
            const maxYear = $('option:selected','#year').attr('max-year')
            formData.append('max_year', maxYear);
            var customerId = $('#customer_id').val();
            var selectedcustomerId = $('#customerId').val();
            var memberId = $('#member_id').val();
            var year = $('#year').val();
            var file = $('#file').val();
            $.post("{{route('branch.form15g.datacheck')}}",{'year':year,'customerId':customerId,'memberId':memberId,'selectedcustomerId':selectedcustomerId,'year':year},function(e){
                if (e.data > 0) {
                    swal('Warning','15G form already uploaded in current financial year!','warning');
                    return false;
                }else{
                    $('#status').val('1');
                    if (year && file) {
                        $.ajax({
                            type: 'POST',
                            url: "{!! route('branch.update_15g.save') !!}",
                            data: formData,
                            dataType: 'json',
                            contentType: false,
                            cache: false,
                            processData: false,
                            success: function(e) {
                                let imageUrl = e.image;
                                if (e) {
                                    swal("Success!",
                                        'Record Created Successfully!',
                                        'success');
                                    location.reload();
                                    $('#year').val('');
                                    $('#file').val('');
                                    $.each(e.form, function(index, val) {
                                        var s_no = parseInt(index) + 1;
                                        var year = val.year ? val.year : '';
                                        var file = val.file ? val.file : '';
                                        var expendHtml = `<tr>
                                                        <td>${s_no}</td>
                                                        <td>${val.company.name}</td>
                                                        <td>${val.member.first_name??''} ${val.member.last_name??''}</td>
                                                        <td>${year}</td>
                                                        <td><a href="${imageUrl[index]}" target="_blank">${file}</a></td>
                                                        <td>${val.status==1?'Active':'Inactive'}</td>
                                                        <!--<td id="icon" style="width:4%;" onclick="myFunction('${val.id}')">
                                                            <i class="far fa-times-circle"></i>
                                                        </td>-->
                                                    </tr>`;
                                        $("#update_g").append(expendHtml);
                                    });
                                }
                            }
                        });
                    } else {
                        return false;
                    }
                }
            },'JSON');
        }
    });

   var url = window.location.pathname;

	$(document).ajaxComplete(function(){
		var created_at = $('#created_at').val();
        var dateParts = created_at.split(' ')[0].split('-');
        var month = parseInt(dateParts[1]);
        var year = parseInt(dateParts[0]);
        var nextyear = year;
        var extrayear = year + 1;
        var html = "";
        if (month > 3) {
            html += "<option value=" + nextyear + " max-year=" + extrayear + " >" + nextyear + ' - ' +extrayear + "</option>";
        }
        if (month < 3) {
            html += "<option value=" + year + " max-year=" + nextyear + " >" + year + ' - ' + nextyear +"</option>";
        }
        if (month == 3) {
            html += "<option value=" + (year - 1) + " max-year=" + nextyear + " >" + (year - 1) + ' - ' + nextyear + "</option>";
            html += "<option value=" + nextyear + " max-year=" + extrayear + " >" + nextyear + ' - ' + extrayear + "</option>";
        }
        $('#year').empty().append(html);
	});

    var m_id = url.substring(url.lastIndexOf('/') + 1);
	var companyId = $('#company_id').val();
     $.ajax({
            type: 'POST',
            url: "{!! route('branch.form_g.getData') !!}",
            data:{'memberId':m_id,'companyId':companyId},
            dataType: 'json',
            headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            success: function(e){
                let imageUrl = e.image;
                 if(e.form.length > 0)
                {
                $.each(e.form , function(index, val) {
                    var year = val.year ? val.year : '';
                    var file = val.file ? val.file : '';
                    var s_no = parseInt(index) + 1;
                    var expendHtml=`<tr>
									  <td>${s_no}</td>
									  <td>${val.company.name}</td>
									  <td>${val.member.first_name??''} ${val.member.last_name??''}</td>
									  <td>${year}</td>
									  <td><a href="${imageUrl[index]}" target="_blank">${file}</a></td>
									  <td>${val.status==1?'Active':'Inactive'}</td>
									</tr>`;
                $("#update_g").append(expendHtml);
            });
            }
            else{
                  $("#update_g").append( '<tr><td><td><td >No Record Found</td></td></td>');
            }
            }
        });

     $('.export').on('click',function(){
  var extension = $(this).attr('data-extension');
  $('#export').val(extension);
  $('form#filter').attr('action',"{!! route('branch.update_15g.report.export') !!}");
  $('form#filter').submit();
});



});

     function myFunction(value)
 {
    swal({
                    title: "Are you sure?",
                    text: "You want to delete record",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary experience_confirm_delete",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger experience_cancel_delete",
                    closeOnConfirm: false,
                    closeOnCancel: true
                  },
                  function(isConfirm) {
                    if (isConfirm)
                    {

                      $.ajax({
                        type: "POST",
                        url: "{!! route('branch.update_15g.record.delete') !!}",
                        dataType: 'JSON',
                        data: {'id':value,},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(e) {
                          if(e)
                          {

                              swal("Success!", "Record Deleted Successfully.", "success");
                          }
                          else
                          {
                            swal("Sorry!", "Record Not Deleted.Try Again!", "error");
                          }
                          location.reload();
                        }
                    })
                      }
                  });
 }

</script>
