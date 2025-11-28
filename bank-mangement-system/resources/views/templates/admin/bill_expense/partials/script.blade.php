<script type="text/javascript">
  var memberTable;
	$(document).ready(function () {

		$('.item_id').select2({

			width: '100%',

			placeholder: 'Select or Add',

			language: {

			  noResults: function() {

				return '<button id="no-results-btn" data-toggle="modal" data-target="#modal-form">Add Item</a>';

			  },

			},

			escapeMarkup: function(markup) {

			  return markup;

			},
		});

		$(document).on('change','.item_id',function(){
			var currentItemID = $(this).val();
			var currentItemRow = $(this).attr("data-row-id");
			$.ajax({

				  type: "POST",  

				  url: "{!! route('admin.get_item_details') !!}",

				  dataType: 'JSON',

				  data: {'item_id':currentItemID, 'currentItemRow':currentItemRow},

				  headers: {

					  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

				  },

				  success: function(response) { 

					if(response.msg_type =='success'){

						$("#tdRow"+currentItemRow).after(response.view);

						var newRowItem = parseInt(currentItemRow) + 1;

						$("#itemCount").val(newRowItem);

					} else{

					   swal("Error!", ""+response.view+"", "error");

					}

				  }
			});
		});

		$(document).on('click','.addNewRow',function(){

			var newRowItem = $("#itemCount").val();

			var currentItemRow = parseInt(newRowItem) - 1;

			$.ajax({

				  type: "POST",  

				  url: "{!! route('admin.get_items') !!}",

				  dataType: 'JSON',

				  data: {'newRowItem':newRowItem},

				  headers: {

					  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

				  },

				  success: function(response) { 

					if(response.msg_type =='success'){

						$("#trRow"+currentItemRow).after(response.view);

						$('.item_id').select2({

							width: '100%',

							placeholder: 'Select or Add',

							language: {

							  noResults: function() {

								return '<button id="no-results-btn" data-toggle="modal" data-target="#modal-form">Add Item</a>';

							  },

							},

							escapeMarkup: function(markup) {

							  return markup;

							},

						});

						var newRowItem1 = parseInt(newRowItem) + 1;

						$("#itemCount").val(newRowItem1);

					} else{

					   swal("Error!", ""+response.view+"", "error");

					}

				  }

			});
		})

	  var date = new Date();

	  $('#start_date').datepicker({

	    format: "dd/mm/yyyy",

	    todayHighlight: true,  

	    endDate: date, 

	    autoclose: true
	  });

	  $('#end_date').datepicker({

	    format: "dd/mm/yyyy",

	    todayHighlight: true, 

	    endDate: date,  

	    autoclose: true
	  });

	  $('#bill_date').datepicker({

	    format: "dd/mm/yyyy",

	    todayHighlight: true, 

	    endDate: date,  

	    autoclose: true
	  });

	  $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

	});
</script>