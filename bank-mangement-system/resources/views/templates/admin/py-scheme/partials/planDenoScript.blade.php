<script type="text/javascript">

$(document).ready(function() 
{
    var plantable;

    /*Client Side Jquery Validation for Plan Category Form*/

    jQuery.validator.addMethod("lettersonly", function(value, element) 
    {
        return this.optional(element) || /^[a-z\s]+$/i.test(value);

    }, "Only alphabetical characters");

    /*Set the Date picker on Effective From field and after than effective to filed start date from as per the effective from selected date*/

    $(document).on('mouseover',function()
    {
        $( ".effective_from" ).datepicker(
        {
            format: "dd/mm/yyyy",
            autoclose: true,
            todayHighlight: true,
            startDate:'+0d' 
        }).on('changeDate',function(e){
            $(".effective_to").datepicker(
                {
                    format: "dd/mm/yyyy",
                    autoclose: true,
                    todayHighlight: true,
                    startDate: e.date
                });
        });
    });

    /*Validation on the Plan Deno Form*/

    $('#plan_deno_form').validate({ // initialize the plugin
        rules: {
            'tenure' : 
            {
                'required' : true,
                'digits' : true,
                'min' : 1,
            },
            'denomination' :
            {
                'required' : true,
                'digits' : true,
                'min' : 1,
            },
            'effective_from' : 
            {
                'required' : true,
            },
            'effective_to' :
            {
                'required' : true,
            }
        },
        messages:
        {
            'tenure' : 
            {
                'required' : 'Tenure is required',
            },
            'denomination' : 
            {
                'required' : 'Denomination is required',
                'digits' : 'Use only digits',
                'min' : 'Enter denomination more than 0',
            },
            'effective_from' : 
            {
                'required' : 'Select the date',
            },
            'effective_to' : 
            {
                'required' : 'Select the date',
            }
        },
    });

    /**
    * listing the data in PLAN DENOS TABLE*/

    var plantable = $('#file_charge_d').DataTable(
    {
        processing: true,
        serverSide: true,
        pageLength: 20,
        responsive:true,
        lengthMenu: [10, 20, 40, 50, 100],
       
        ajax: {
            "url": "{!! route('admin.planDenoListing') !!}",
            "type": "POST",
            "data": function(d) 
            {
                d.start = d.start || 0;
                d.length = d.length || 10;
                d.plan_code = $('#plan_code').val();
            },
            "headers": 
            {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'tenure', name: 'tenure'},
            {data: 'denomination', name: 'denomination'},
            {data: 'effective_from', name: 'effective_from'},
            {data: 'effective_to', name: 'effective_to'},
            {data: 'action', name: 'action',orderable:false, searchable: false},
        ],
        columnDefs: [
            { targets: [0,1,2,3,4], visible: true},
            { targets: '5', visible: false },
        ],
    });

    $(plantable.table().container()).removeClass( 'form-inline' );

    $( document ).ajaxStart(function() {

        $( ".loader" ).show();

    });

    $( document ).ajaxComplete(function() 
    {
        $( ".loader" ).hide();
    });

    /**
     * Status Change*/

    $(document).on('click','.change_status',function()
        {
            var statusId = $(this).attr('statusId');
            var status = $(this).attr('data-status');
            swal({
                title : "Are you sure want to change status?",
                showDenyButton : true,
                showCancelButton : true,
            },function(isConfirm)
            {
                if(isConfirm)
                {
                    $.ajax({
                        "url" : "{{route('admin.planDenoStatus')}}",
                        "type" : "POST",
                        "data" : {
                            'id' : statusId,
                            'status' : status,
                            },
                        "headers": 
                        {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success : function(e)
                        {
                            if(e.save>0)
                            {   
                                plantable.draw();
                                swal("Success","Status updated successfully","success");
                                return false;
                            }
                            else
                            {
                                plantable.draw();
                                swal("Wrong","Status not updated","error");
                                return false;
                            }
                        },
                    });
                }
            })
        });

    /**
    * Insert the data with ajax
    **/

     $("#plan_deno_form").on('submit',function(e)
     {
        e.preventDefault();
        $.ajax(
        {
            url : '{{ route("planDenoInsert") }}',
            type : 'POST',
            data : $('#plan_deno_form').serialize(),
            success : function(e)
            {
                if(e.data>0 && e.save==0)
                {
                    swal('Wrong','Effective From Date already exists','error');
                    return false;
                }
                else if(e.data==0 && e.save==1)
                {
                    plantable.draw();   
                    swal('Success','Plan denomination inserted successfully','success');
                    $(".close").click();
                    $("#denomination").val(null);
                    $("#effective_from").val(null);
                    $("#effective_to").val(null);
                }
                else if(e.data==0 && e.save==0)
                {
                    swal('Wrong','Enter values correct','error');
                    return false;
                }
            },
            error:function()
            {
                alert("error");
                return false;
            },

        });
    });

    /*Close the Model on Click Close Button*/
     
    $('.close').click(function()
    {
        $("#denomination").val(null);
        $("#effective_from").val(null);
        $("#effective_to").val(null);
    });

    /**
    * click event on the delete button
    **/

    $(document).on('click',".delete_data",function(e)
    {
        e.preventDefault();
        var id = $(this).attr('del_id');
        swal({
            title : "Are you sure want to delete the data?",
            showDenyButton : true,
            showCancelButton : true,
        },function(isConfirm)
        {
            if(isConfirm)
            {
                $.ajax({
                    url: "{!! route('admin.planDenoDelete') !!}",
                    type : "POST",
                    data: {'id':id},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success:function(e)
                    {
                        if(e.success>0)
                        {
                            plantable.draw();
                            swal("success","Data deleted successfully","success");  
                        }
                        else
                        {
                            console.log('error on deliting data');
                        }
                    },
                    error:function(){
                        alert("error");
                    }
                });
            }
        })
    });
});

</script>