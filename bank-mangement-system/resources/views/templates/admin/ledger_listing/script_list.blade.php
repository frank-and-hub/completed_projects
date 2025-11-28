<script type="text/javascript">
	var ledgerListingTable;
	$("#employee_id").select2({ dropdownAutoWidth: true });
	$("#member_id").select2({ dropdownAutoWidth: true, tags: "true", allowClear: true });
	$("#associate_id").select2({ dropdownAutoWidth: true });
	$(document).ready(function () {
		var date = new Date();
		$('#start_date').datepicker({
			format: "dd/mm/yyyy",
			todayHighlight: true,
			endDate: date,
			autoclose: true,
			orientation: 'bottom',
		});
		$('#end_date').datepicker({
			format: "dd/mm/yyyy",
			todayHighlight: true,
			endDate: date,
			autoclose: true,
			orientation: 'bottom',
		});
		$(document).on('change', '#head_id', function () {
			var hId = $('option:selected', this).val();
			$('.sub-heads').hide();
			$('.' + hId + '-sub-head').show();
			$("#ledger_type").attr("disabled", "true");
		});
		$(document).on('change', '#ledger_type', function () {
			$("#head_id").attr("disabled", "true");
		})
		$.validator.addMethod("decimal", function(value, element,p) {     

			if(this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {

				$.validator.messages.decimal = "";

				result = true;

			} else {

				$.validator.messages.decimal = "Please Enter valid numeric number.";

				result = false;

			}



			return result;

		}, "");
		ledgerListingTable = $('#ledger_listing').DataTable({
			processing: true,
			serverSide: true,
			pageLength: 100,
			ordering: false,
			lengthMenu: [10, 20, 40, 50, 100],
			"fnRowCallback": function (nRow, aData, iDisplayIndex) {
				var oSettings = this.fnSettings();
				$('html, body').stop().animate({
					scrollTop: ($('#ledger_listing').offset().top)
				}, 1000);
				$("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
				return nRow;
			},
			ajax: {
				"url": "{!! route('admin.ledger-listing') !!}",
				"type": "POST",
				"data": function (d, oSettings) {
					if (oSettings.json != null) {
						var total_amount = oSettings.json.total;
					} else {
						var total_amount = 0;
					}
					var page = ($('#ledger_listing').DataTable().page.info());
					var currentPage = page.page + 1;
						d.pages = currentPage,
						d.searchform = $('form#filter').serializeArray(),
						d.total = total_amount
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
			},
			columns: [
				{ data: 'DT_RowIndex', name: 'DT_RowIndex' },
				{ data: 'company', name: 'company' },
				{ data: 'created_date', name: 'created_date' },
				{ data: 'branch_name', name: 'branch_name' },
				{ data: 'head_name', name: 'head_name' },
				{ data: 'payment_mode', name: 'payment_mode' },
				{ data: 'description', name: 'description' },
				{ data: 'debit', name: 'debit' },
				{ data: 'credit', name: 'credit' },
				{ data: 'balance', name: 'balance' },
			]
		});
		$('#company_id').on('change',function(e){
			var company_id = $(this).val();
			$('.head_id').change();
		});
		$(document).on('change', '.head_id', function () {
			var hId = $(this).attr("data-row-id");
			var head_id = $(this).val();
			let pId = $('option:selected', this).attr('data-parent-id');
			let company_id = $('#company_id').val();
			let hideAccountNumberHeadId = [27, 28, 11, 96, 19, 18, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 92, 98, 99, 103, 104, 105, 106, 112, 113, 114, 120, 121, 173, 174, 178, 102, 117, 118, 119, 115, 116, 146, 100, 101, 108, 122, 144, 9, 23, 24, 107, 109, 110, 111, 148];
			let hideAccountNumberPids = [96,27,18];
			if (head_id > 0) {
				// if (head_id == 27 || head_id == 28 || head_id == 11 || head_id == 96 || head_id == 19 || head_id == 18 || head_id == 38 || head_id == 39 || head_id == 40 || head_id == 41 || head_id == 42 || head_id == 43 || head_id == 44 || head_id == 45 || head_id == 46 || head_id == 47 || head_id == 48 || head_id == 49 || head_id == 50 || head_id == 51 || head_id == 52 || head_id == 92 || head_id == 98 || head_id == 99 || head_id == 103 || head_id == 104 || head_id == 105 || head_id == 106 || head_id == 112 || head_id == 113 || head_id == 114 || head_id == 120 || head_id == 121 || head_id == 173 || head_id == 174 || head_id == 178 || head_id == 102 || head_id == 117 || head_id == 118 || head_id == 119 || head_id == 115 || head_id == 116 || head_id == 146 || head_id == 100 || head_id == 101 || head_id == 108 || head_id == 122 || head_id == 144 || head_id == 9 || head_id == 23 || head_id == 24 || head_id == 107 || head_id == 109 || head_id == 110 || head_id == 111 || head_id == 148 || pId == 96 || pId == 27 || pId == 18) {
				if (hideAccountNumberHeadId.includes(head_id) || hideAccountNumberPids.includes(pId)) {
					$(".accountNumberClass").css("display", "none");
				} else {
					$(".accountNumberClass").css("display", "block");
				}
				$.ajax({
					type: "POST",
					url: "{!! route('admin.getHeadLedgerData') !!}",
					dataType: 'JSON',
					data: { 'hId': hId, 'head_id': head_id,'company_id':company_id },
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					success: function (response) {
						console.log(response);
						$("#sub_head_id" + hId).empty();
						// $("#sub_head_id"+newId).empty();
						// $("#sub_head_id"+newId).append("<option value=''>Choose Sub Head</option>");
						$("#sub_head_id" + hId).append("<option value=''>Choose Sub Head</option>");
						if (response.length > 0) {
							if (head_id == 87 || head_id == 88) {
								$("#sub_head_id" + hId).removeAttr("required", "false");
							} else {
								$("#sub_head_id" + hId).attr("required", "true");
							}
							for (var k = 0; k < response.length; k++) {
								$("#sub_head_id" + hId).append("<option data-parent-id =" + response[k].parent_id + "  value=" + response[k].head_id + " >" + response[k].sub_head + "</option>");
							}
						} else {
							$("#sub_head_id" + hId).removeAttr("required", "true");
						}
					}
				});
			}
		});
		$(document).on('change', '#ledger_type', function () {
			var ledger_type = $(this).val();
			$("#employee_id, #employeeID, #member_id, #memberID, #associate_id, #rent_owner_id, #director_id, #share_holder_id, #vendor_id, #customer_id").val("");
			$("#memberNameDiv, #memberIDDiv, #associateDiv, #rentOwnerDiv, #directorDiv, #shareHolderDiv, #employeeIDDiv, #employeeNameDiv, #vendorDiv, #customerDiv").css("display", "none");
			if (ledger_type == "1") {
				$("#memberNameDiv, #memberIDDiv").css("display", "block");
			}
			if (ledger_type == "2") {
				$("#employeeIDDiv, #employeeNameDiv").css("display", "block");
			}
			if (ledger_type == "3") {
				$("#associateDiv").css("display", "block");
			}
			if (ledger_type == "4") {
				$("#rentOwnerDiv").css("display", "block");
			}
			if (ledger_type == "5") {
				$("#vendorDiv").css("display", "block");
			}
			if (ledger_type == "6") {
				$("#directorDiv").css("display", "block");
			}
			if (ledger_type == "7") {
				$("#shareHolderDiv").css("display", "block");
			}
			if (ledger_type == "8") {
				$("#customerDiv").css("display", "block");
			}
		});
		$('.export').on('click', function (e) {
			e.preventDefault();
			var extension = $(this).attr('data-extension');
			$('#_export').val(extension);
			if (extension == 0) {
				var formData = jQuery('#filter').serializeObject();
				var chunkAndLimit = 10000;
				$(".spiners").css("display", "block");
				$(".loaders").text("0%");
				doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
				$("#cover").fadeIn(100);
			}
			else {
				$('#_export').val(extension);
				$('form#filter').attr('action', "{!! route('admin.head_ledger_listing_export.export') !!}");
				$('form#filter').submit();
			}
		});
		// function to trigger the ajax bit
		function doChunkedExport(start, limit, formData, chunkSize) {
			formData['start'] = start;
			formData['limit'] = limit;
			jQuery.ajax({
				type: "post",
				dataType: "json",
				url: "{!! route('admin.head_ledger_listing_export.export') !!}",
				data: formData,
				success: function (response) {
					const tokenName = response.tokenName;
					if (response.result == 'next') {
						start = start + chunkSize;
						formData['fileName'] = tokenName;
						doChunkedExport(start, limit, formData, chunkSize);
						$(".loaders").text(response.percentage + "%");
					} else {
						var csv = response.fileName;
						console.log('DOWNLOAD');
						$(".spiners").css("display", "none");
						$("#cover").fadeOut(100);
						window.open(csv, '_blank');
					}
				}
			});
		}

		$('.export-2').on('click', function (e) {
			e.preventDefault();
			var extension = $(this).attr('data-extension');
			$('#_export').val(extension);
			if (extension == 0) {
				var formData = jQuery('#filter').serializeObject();
				var chunkAndLimit = 10000;
				$(".spiners").css("display", "block");
				$(".loaders").text("0%");
				doChunkedExport2(0, chunkAndLimit, formData, chunkAndLimit);
				$("#cover").fadeIn(100);
			}
			else {
				$('#_export').val(extension);
				$('form#filter').attr('action', "{!! route('admin.ledger_listing_export.export') !!}");
				$('form#filter').submit();
			}
		});
		// function to trigger the ajax bit
		function doChunkedExport2(start, limit, formData, chunkSize) {
			formData['start'] = start;
			formData['limit'] = limit;
			jQuery.ajax({
				type: "post",
				dataType: "json",
				url: "{!! route('admin.ledger_listing_export.export') !!}",
				data: formData,
				success: function (response) {
					const tokenName = response.tokenName;
					if (response.result == 'next') {
						start = start + chunkSize;
						formData['fileName'] = tokenName;
						doChunkedExport2(start, limit, formData, chunkSize);
						$(".loaders").text(response.percentage + "%");
					} else {
						var csv = response.fileName;
						console.log('DOWNLOAD');
						$(".spiners").css("display", "none");
						$("#cover").fadeOut(100);
						window.open(csv, '_blank');
					}
				}
			});
		}
		jQuery.fn.serializeObject = function () {
			var o = {};
			var a = this.serializeArray();
			jQuery.each(a, function () {
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
		$(ledgerListingTable.table().container()).removeClass('form-inline');
		$(document).ajaxStart(function () {
			$(".loader").show();
		});
		$(document).ajaxComplete(function () {
			$(".loader").hide();
		});
		$('#filter').validate({
			rules: {
				start_date: {
					required: true,
				},
				end_date: {
					required: true,
				},
				company_id: {
					required: true,
				},
				branch: {
					required: true,
				},
				ledger_type: {
					required: true,
				},
				account_number: {
					decimal: true,
				},
			},
			messages: {
				    account_number: "Please enter valid number",
			},
			errorElement: 'span',
			errorPlacement: function (error, element) {
				error.addClass(' ');
				element.closest('.error-msg').append(error);
			},
			highlight: function (element, errorClass, validClass) {
				$(element).addClass('is-invalid');
				if ($(element).attr('type') == 'radio') {
					$(element.form).find("input[type=radio]").each(function (which) {
						$(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
						$(this).addClass('is-invalid');
					});
				}
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).removeClass('is-invalid');
				if ($(element).attr('type') == 'radio') {
					$(element.form).find("input[type=radio]").each(function (which) {
						$(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
						$(this).removeClass('is-invalid');
					});
				}
			}
		});
		ledgerListingTableRecords = $('#ledger_listing_records').DataTable({
			processing: true,
			serverSide: true,
			pageLength: 20,
			ordering: false,
			lengthMenu: [10, 20, 40, 50, 100],
			"fnRowCallback": function (nRow, aData, iDisplayIndex) {
				var oSettings = this.fnSettings();
				$('html, body').stop().animate({
					scrollTop: ($('#ledger_listing_records').offset().top)
				}, 1000);
				$("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
				return nRow;
			},
			ajax: {
				"url": "{!! route('admin.ledger-records-listing') !!}",
				"type": "POST",
				"data": function (d, oSettings) {
					if (oSettings.json != null) {
						var total_amount = oSettings.json.total;
					}
					else {
						var total_amount = 0;
					}
					var page = ($('#ledger_listing_records').DataTable().page.info());
					var currentPage = page.page + 1;
					d.pages = currentPage,
						d.searchform = $('form#filter').serializeArray(),
						d.total = total_amount
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
			},
			columns: [
				{ data: 'DT_RowIndex', name: 'DT_RowIndex' },
				{ data: 'created_date', name: 'created_date' },
				{ data: 'company_id', name: 'company_id' },
				{ data: 'branch_name', name: 'branch_name' },
				{ data: 'head_name', name: 'head_name' },
				{ data: 'payment_mode', name: 'payment_mode' },
				{ data: 'description', name: 'description' },
				{ data: 'debit', name: 'debit' },
				{ data: 'credit', name: 'credit' },
				{ data: 'balance', name: 'balance' },
			]
		});
		$(ledgerListingTableRecords.table().container()).removeClass('form-inline');
		ledgerMemberListingTableRecords = $('#ledger_member_listing_records').DataTable({
			processing: true,
			serverSide: true,
			pageLength: 20,
			ordering: false,
			lengthMenu: [10, 20, 40, 50, 100],
			"fnRowCallback": function (nRow, aData, iDisplayIndex) {
				var oSettings = this.fnSettings();
				$('html, body').stop().animate({
					scrollTop: ($('#ledger_member_listing_records').offset().top)
				}, 1000);
				$("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
				return nRow;
			},
			ajax: {
				"url": "{!! route('admin.ledger-records-listing') !!}",
				"type": "POST",
				"data": function (d, oSettings) {
					if (oSettings.json != null) {
						var total_amount = oSettings.json.total;
					}
					else {
						var total_amount = 0;
					}
					var page = ($('#ledger_member_listing_records').DataTable().page.info());
					var currentPage = page.page + 1;
					d.pages = currentPage,
						d.searchform = $('form#filter').serializeArray(),
						d.total = total_amount
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
			},
			columns: [
				{ data: 'DT_RowIndex', name: 'DT_RowIndex' },
				{ data: 'created_date', name: 'created_date' },
				{ data: 'branches', name: 'branches' },
				{ data: 'head_name', name: 'head_name' },
				{ data: 'payment_mode', name: 'payment_mode' },
				{ data: 'description', name: 'description' },
				{ data: 'payment_type', name: 'payment_type' },
				{ data: 'balance', name: 'balance' },
			]
		});
		$(ledgerMemberListingTableRecords.table().container()).removeClass('form-inline');
	});
	
	
	function searchForm() {
		if ($('#filter').valid()) {
			$('#account_number').select2();
			var d = $('#account_number').select2('data');
			// $('#l_type').val(d[0].attr1);
			var head_id = $("#head_id").val();
			var ledger_type = $("#ledger_type").val();
			var member_id = $("#member_id").val();
			var employee_id = $("#employee_id").val();
			var associate_id = $("#associate_id").val();
			var rent_owner_id = $("#rent_owner_id").val();
			var director_id = $("#director_id").val();
			var share_holder_id = $("#share_holder_id").val();
			var account_number = $("#account_number").val();
			var vendor_id = $("#vendor_id").val();
			var customer_id = $("#customer_id").val();
			if (ledger_type == "1" && member_id == "") {
				swal("Warning!", "Please select member name", "warning");
				return false;
			}
			if (ledger_type == "2" && employee_id == "") {
				swal("Warning!", "Please select employee name", "warning");
				return false;
			}
			if (ledger_type == "3" && associate_id == "") {
				swal("Warning!", "Please select associate name", "warning");
				return false;
			}
			if (ledger_type == "4" && rent_owner_id == "") {
				swal("Warning!", "Please select rent owner name", "warning");
				return false;
			}
			if (ledger_type == "5" && vendor_id == "") {
				swal("Warning!", "Please select vendor name", "warning");
				return false;
			}
			if (ledger_type == "6" && director_id == "") {
				swal("Warning!", "Please select director name", "warning");
				return false;
			}
			if (ledger_type == "7" && share_holder_id == "") {
				swal("Warning!", "Please select share holder name", "warning");
				return false;
			}
			if (ledger_type == "8" && customer_id == "") {
				swal("Warning!", "Please select customer name", "warning");
				return false;
			}
			if (head_id == "" && ledger_type == "") {
				swal("Warning!", "Please select at least one head or ledger type!", "warning");
				return false;
			} else {
				$('#is_search').val("yes");
				$('#_export').val("1");
				ledgerListingTable.draw();
				$("#ledgerListingTableDiv").css("display", "block");
			}
		}
	}
	function searchForm1() {
		if ($('#filter').valid()) {
			var head_id = $("#head_id").val();
			var ledger_type = $("#ledger_type").val();
			var member_id = $("#member_id").val();
			var employee_id = $("#employee_id").val();
			var associate_id = $("#associate_id").val();
			var rent_owner_id = $("#rent_owner_id").val();
			var account_number = $("#account_number").val();
			var director_id = $("#director_id").val();
			var share_holder_id = $("#share_holder_id").val();
			var vendor_id = $("#vendor_id").val();
			var customer_id = $("#customer_id").val();
			if (ledger_type == "1" && member_id == "") {
				swal("Warning!", "Please select member name", "warning");
				return false;
			}
			if (ledger_type == "2" && employee_id == "") {
				swal("Warning!", "Please select employee name", "warning");
				return false;
			}
			if (ledger_type == "3" && associate_id == "") {
				swal("Warning!", "Please select associate name", "warning");
				return false;
			}
			if (ledger_type == "4" && rent_owner_id == "") {
				swal("Warning!", "Please select rent owner name", "warning");
				return false;
			}
			if (ledger_type == "5" && vendor_id == "") {
				swal("Warning!", "Please select vendor name", "warning");
				return false;
			}
			if (ledger_type == "6" && director_id == "") {
				swal("Warning!", "Please select director name", "warning");
				return false;
			}
			if (ledger_type == "7" && share_holder_id == "") {
				swal("Warning!", "Please select share holder name", "warning");
				return false;
			}
			if (ledger_type == "8" && customer_id == "") {
				swal("Warning!", "Please select customer name", "warning");
				return false;
			}
			if (head_id == "" && ledger_type == "") {
				swal("Warning!", "Please select at least one head or ledger type!", "warning");
				return false;
			} else {
				if (ledger_type == "1") {
					$('#is_search').val("yes");
					// alert('first');
					ledgerMemberListingTableRecords.draw();
					$("#ledgerMembersListingTableDiv").css("display", "block");
					$("#ledgerListingTableDiv").css("display", "none");
				} else {
					// alert('second');
					$('#is_search').val("yes");
					ledgerListingTableRecords.draw();
					$("#ledgerListingTableDiv").css("display", "block");
					$("#ledgerMembersListingTableDiv").css("display", "none");
				}
			}
		}
	}
	function resetForm() {
		var form = $("#filter"),
			validator = form.validate();
		validator.resetForm();
		form.find(".error").removeClass("error");
		$("#ledgerListingTableDiv").css("display", "none");
		$('#is_search').val("yes");
		$(".select2-selection__rendered").html("");
		$('#start_date').val(null);
		$('#end_date').val(null);
		$('#branch').val('');
		$('.head_id').val('');
		$('#subhead').val('');
		$('#ledger_type').val('');
		$('#employee_name').val('');
		$('#member_id').val('');
		$('#memberID').val('');
		$('#associate_id').val('');
		$('#rent_owner_id').val('');
		$('#director_id').val('');
		$('#share_holder_id').val('');
		$('#vendor_id').val('');
		$("#account_number").val('');
		$("#branch_id").val('');
		$('#customer_id').val('');
		$('#ledger_type').prop("disabled", false);
		$('#head_id').prop("disabled", false);
		$('.head_id ').prop("required", false);
		$("#memberNameDiv, #memberIDDiv, #associateDiv, #rentOwnerDiv, #directorDiv, #shareHolderDiv, #vendorDiv, #customerDiv").css("display", "none");
		$("#start_date").datepicker('setDate', null);
		$("#end_date").datepicker('setDate', null);
		ledgerListingTable.draw();
	}
	function resetForm1() {
		var form = $("#filter"),
			validator = form.validate();
		validator.resetForm();
		form.find(".error").removeClass("error");
		$("#ledgerListingTableDiv").css("display", "none");
		$("#ledgerMembersListingTableDiv").css("display", "none");
		$('#is_search').val("yes");
		$(".select2-selection__rendered").html("Select");
		$('#start_date').val(null);
		$('#end_date').val(null);

		$('#branch').val('');
		$('#company_id').val(0);
		$('.head_id').val('');
		$("#memberName").val('');
		$('#subhead').val('');
		$('#ledger_type').val('');
		$('#employee_name').val('');
		$('#member_id').val('');
		$('#memberID').val('');
		$('#associate_id').val('');
		$('#rent_owner_id').val('');
		$('#director_id').val('');
		$('#share_holder_id').val('');
		$('#vendor_id').val('');
		$("#account_number").val('');
		$("#branch_id").val('');
		$('#customer_id').val('');
		$('#ledger_type').prop("disabled", false);
		$('#head_id').prop("disabled", false);
		$('.head_id ').prop("required", false);
		$("#memberNameDiv, #memberIDDiv, #associateDiv, #rentOwnerDiv, #directorDiv, #shareHolderDiv, #vendorDiv, #customerDiv, #employeeNameDiv").css("display", "none");
		$("#start_date").datepicker('setDate', null);
		$("#end_date").datepicker('setDate', null);
		ledgerListingTable.draw();
	}
	// $('#account_number').on("select2-selecting", function(e) {
	//   console.log('Selecting');
	// });
	$(document).on('change', '.head_id', function () {
		var id = $(this).val();
		var row = $(this).attr('data-row-id');
		var pId = $('option:selected', this).attr('data-parent-id');
		var branchId = $("#branch_id").val();
		var company_id = $("#company_id").val();
		var account_no = $("#account_number").val();
		console.log(account_no);
		if (row == "1") {
			var id = $("#head_id").val()
		} else {
			var currents = parseInt(row) - 1;
			var id = $("#sub_head_id" + currents).val();
		}
		if (id > 0) {
			contactList(id, row, pId, branchId,account_no);
		}
		for (var i = row; i < 6; i++) {
			$("#sub_head_id" + i).val("");
		}
	});
	function contactList(headId, row, pId, branchId,account_no=null) {
		if (pId != '') {
			var pId = pId;
		}
		else {
			pId = "";
		}
		// console.log("fgf",pId);
		// $head_ids = array($headId);
		// 	$subHeadsIDS = \App\Mpdels\AccountHeads::where('head_id',$headId)->where('status',0)->pluck('parent_id')->toArray();
		// if( count($subHeadsIDS) > 0 ){
		//            $head_ids=  array_merge($head_ids,$subHeadsIDS);
		//           $record=get_account_head_ids($head_ids,$subHeadsIDS,true);
		//       	}
		//    console.log(record);  
		// $(".account_number").html('');
		// $(".account_number").append("<option value=''>Select Account Number</option>");
		var userType = '';
		if (headId == 56) {
			var url = "{!! route('admin.jv.getsavingaccounts') !!}"
			$("#type").val("4");
		} else if (headId == 34 || headId == 55) {
			var url = "{!! route('admin.jv.getmembers') !!}"
			$("#type").val("1");
		} else if (headId == 32 || headId == 61 || headId == 72 || headId == 73 || headId == 76 || headId == 143 || headId == 37) {
			var url = "{!! route('admin.jv.getemployees') !!}"
			$("#type").val("12");
		} else if (headId == 87 || headId == 88 || headId == 63 || headId == 141) {
			var url = "{!! route('admin.jv.getassociates') !!}"
			$("#type").val("2");
		} else if (headId == 15 || headId == 19 || pId == 19) {
			var url = "{!! route('admin.jv.getshareholders') !!}"
			$("#type").val("16");
		} else if (headId == 31 || headId == 33 || headId == 64 || headId == 65 || headId == 66 || headId == 67 || headId == 90) {
			var url = "{!! route('admin.jv.getloanaccounts') !!}"
			$("#type").val("5");
		} else if (headId == 36 || headId == 57 || headId == 58 || headId == 59 || headId == 80 || headId == 81 || headId == 82 || headId == 83 || headId == 84 || headId == 85 || headId == 122 || headId == 77 || headId == 78 || headId == 79 || headId == 139 || headId == 62 || headId == 35) {
			var url = "{!! route('admin.jv.getinvestmentsaccounts') !!}"
			$("#type").val("3");
		} else if (headId == 89) {
			var url = "{!! route('admin.jv.getreinvestmentsaccounts') !!}"
			$("#type").val("3");
		} else if (headId == 68 || headId == 69 || headId == 70 || headId == 91 || headId == 27) {
			var url = "{!! route('admin.jv.getbank') !!}"
			$("#type").val("8");
		} else if (headId == 18 || headId == 97) {
			var url = "{!! route('admin.jv.getloanfrombank') !!}"
			$("#type").val("17");
		} else if (headId == 28 || headId == 71) {
			var url = "{!! route('admin.jv.getbranch') !!}"
			$("#type").val("0");
		} else if (headId == 60 || headId == 74 || headId == 75 || headId == 93 || headId == 94 || headId == 95) {
			var url = "{!! route('admin.jv.getrentliability') !!}"
			$("#type").val("10");
		}
		else if (pId == 26) {
			var url = "{!! route('admin.jv.getcustomer') !!}"
			$("#type").val("26");
			var userType = 0;
		}
		else if (headId == 140 || headId == 176 || headId == 185) {
			var url = "{!! route('admin.jv.getcustomer') !!}"
			var userType = 0;
			$("#type").val("26");
		}
		console.log(url,account_no); return false;
		$(".account_number").select2({
			minimumInputLength: 3,
			ajax: {
				type: "POST",
				delay: 50,
				url: url,
				dataType: 'JSON',
				data: function (params) {
					return {
						query: params.term, // search term
						headId: headId,
						pId: pId,
						branchId: branchId,
						company_id: company_id,
						userType: userType,
						account_no: account_no,
						"_token": "{{ csrf_token() }}",
					};
				},
				processResults: function (response) {
					//console.log(response);
					return {
						results: response
					};
				},
				cache: true
			}
		});
	}
	/*
	$(document).on('keyup','.member_id',function(){
		var ledger_type = $("#ledger_type").val();
		var name = $(this).val();
		alert();
		if(ledger_type == "1"){
			var mainID = "member_id";
		} else if(ledger_type == "2"){
			var mainID = "employee_id";
		} else if(ledger_type == "3"){
			var mainID = "associate_id";
		}
		if( ledger_type == "1" || ledger_type == "3"){
			if(name.length > 3){
				$.ajax({
					type: "POST",  
					url: "{!! route('admin.getHeadLedgerUsersData') !!}",
					dataType: 'JSON',
					data: {'name':name,'ledger_type':ledger_type},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					success: function(response) {
						$("#"+mainID).empty();
						$("#"+mainID).append("<option value=''>Select...</option>");
						if(response.length > 0){
							if(ledger_type == "1" || ledger_type == "3"){
								for(var k=0; k<response.length; k++){
									if(response[k].last_name == "" || response[k].last_name == null){
										$("#"+mainID).append("<option value="+response[k].id+" data-row-name="+response[k].first_name+">"+response[k].member_id+"</option>");
									} else {
										$("#"+mainID).append("<option value="+response[k].id+" data-row-name="+response[k].first_name+">"+response[k].member_id+"</option>");
									}
								}
							}
							if(ledger_type == "2"){
								for(var k=0; k<response.length; k++){
									$("#"+mainID).append("<option value="+response[k].id+">"+response[k].employee_name+"</option>");
								}
							}
							//$("#"+mainID).select2({dropdownAutoWidth : true});
						}
					}
				}); 
			}
		}
	})
	*/
	var company = $("#company_id option:selected").val();
	console.log('company',company);
	$("#member_id").select2({
		minimumInputLength: 3,
		ajax: {
			type: "POST",
			delay: 250,
			url: "{!! route('admin.ledger_data.get-members-ledger') !!}",
			dataType: 'JSON',
			data: function (params) {
				return {
					query: params.term, // search term
					"_token": "{{ csrf_token() }}",
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});
	$(document).on('change', '#member_id', function () {
		var name = $(this).select2('data')[0].attr1;
		$("#memberName").val(name);
	});
	$("#employee_id").select2({
		minimumInputLength: 2,
		ajax: {
			type: "POST",
			delay: 250,
			url: "{!! route('admin.ledger_data.get-employee-ledger') !!}",
			dataType: 'JSON',
			data: function (params) {
				return {
					query: params.term, // search term
					"_token": "{{ csrf_token() }}",
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});
	$("#associate_id").select2({
		minimumInputLength: 2,
		ajax: {
			type: "POST",
			delay: 250,
			url: "{!! route('admin.ledger_data.get-associate-ledger') !!}",
			dataType: 'JSON',
			data: function (params) {
				return {
					query: params.term, // search term
					"_token": "{{ csrf_token() }}",
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});
	$("#rent_owner_id").select2({
		minimumInputLength: 2,
		ajax: {
			type: "POST",
			delay: 250,
			url: "{!! route('admin.ledger_data.get-rent-owner-ledger') !!}",
			dataType: 'JSON',
			data: function (params) {
				return {
					query: params.term, // search term
					"_token": "{{ csrf_token() }}",
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});
	$("#vendor_id").select2({
		minimumInputLength: 2,
		ajax: {
			type: "POST",
			delay: 250,
			url: "{!! route('admin.ledger_data.get-vendor-ledger') !!}",
			dataType: 'JSON',
			data: function (params) {
				return {
					query: params.term, // search term
					"_token": "{{ csrf_token() }}",
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});
	$("#director_id").select2({
		minimumInputLength: 2,
		ajax: {
			type: "POST",
			delay: 250,
			url: "{!! route('admin.ledger_data.get-director-ledger') !!}",
			dataType: 'JSON',
			data: function (params) {
				return {
					query: params.term, // search term
					"_token": "{{ csrf_token() }}",
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});
	$("#share_holder_id").select2({
		
		minimumInputLength: 2,
		ajax: {
			type: "POST",
			delay: 250,
			url: "{!! route('admin.ledger_data.get-share-holder-ledger') !!}",
			dataType: 'JSON',
			data: function (params) {
				return {
					company:company,
					query: params.term, // search term
					"_token": "{{ csrf_token() }}",
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});
	$("#customer_id").select2({
		minimumInputLength: 2,
		ajax: {
			type: "POST",
			delay: 250,
			url: "{!! route('admin.ledger_data.get-customer-ledger') !!}",
			dataType: 'JSON',
			data: function (params) {
				return {
					query: params.term, // search term
					"_token": "{{ csrf_token() }}",
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});
</script>