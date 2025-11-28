<script type="text/javascript">
    var commissiontable;
    $('.table_hidden').hide();
    $(document).ready(function() {  
        $('input[name="start_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format(
                'MM/DD/YYYY'));
        });
        $('input[name="start_date"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        // Datatables
        commissiontable = $('#commission_listing').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.loan_commission_list') !!}",
                "type": "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                "data":function(d) {
                    d.searchform=$('form#filter').serializeArray(),
                    d.id=$('#id').val()
                }, 

            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },               
                {
                    data: 'month',
                    name: 'month'
                },
                {
                    data: 'associate_id',
                    name: 'associate_id'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
               
                {
                    data: 'carder_name',
                    name: 'carder_name'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'qualifying_amount',
                    name: 'qualifying_amount'
                },
                {
                    data: 'commission_amount',
                    name: 'commission_amount'
                },
                
                {
                    data: 'percentage',
                    name: 'percentage'
                },
                {
                    data: 'carder_from',
                    name: 'carder_from'
                },
                {
                    data: 'carder_to',
                    name: 'carder_to'
                },
                {
                    data: 'commission_type',
                    name: 'commission_type'
                },

                /*{data: 'action', name: 'action',orderable: false, searchable: false},*/
            ]
        });
        $(commissiontable.table().container()).removeClass('form-inline');
        // Show loading image
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        // Hide loading image
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $('.export').on('click',function(e){
		
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#commission_export').val(extension);
			var startdate = $("#start_date").val();
			var enddate = $("#end_date").val();
			
	     if(extension == 0)
		{
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			$('#filter').val(extension);

			$('form#filter').attr('action',"{!! route('branch.loan.loanCommissionExport') !!}");

			$('form#filter').submit();
		}
		
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('branch.loan.loanCommissionExport') !!}",
            data : formData,
            success: function(response) {
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExport(start,limit,formData,chunkSize);
					$(".loaders").text(response.percentage+"%");
                }else{
					var csv = response.fileName;
					$(".spiners").css("display","none");
					$("#cover").fadeOut(100); 
					window.open(csv, '_blank');
                }
            }
        });
    }
	
	// A function to turn all form data into a jquery object
    jQuery.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        jQuery.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };       
    });
    function printDiv(elem) {
        printJS({
            printable: elem,
            type: 'html',
            targetStyles: ['*'],
        })
    }

    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            commissiontable.draw();
            $('.table_hidden').show();
        } else {
            $('.table_hidden').hide();
        }
    }

    function resetForm() {
        $('.table_hidden').hide();
        $("#filter")[0].reset();        
        $('#is_search').val("yes");
        $('.table-section').addClass('hideTableData');
        commissiontable.draw();
      
    }
    $(document).ready(function() {
    $('#year').on('change', function() {
      $('#month').val("");
      var selectedYear = $(this).val(); 
      $('#month option.myopt').each(function() {
        var allowedYears = $(this).data('year'); 
        if (allowedYears && allowedYears.includes(Number(selectedYear))) {
          $(this).show(); 
        } else {
          $(this).hide();
        }
      });
    });
    $("#year").trigger("change");
});
</script>
