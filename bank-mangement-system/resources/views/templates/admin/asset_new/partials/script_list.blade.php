<script type="text/javascript">
    var assets_list;
     var depreciation_list;
$(document).ready(function () {

     assets_list = $('#assets_list').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#assets_list').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.asset.lists') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(), 
                d.branch=$('#branch').val(),
                d.category=$('#category').val(), 
                d.status=$('#status').val(), 
                d.is_search=$('#is_search').val(),
                d.export=$('#export').val()
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
             {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
           {data: 'company', name: 'company'},
           {data: 'branch', name: 'branch'},
           /*  {data: 'branch_code', name: 'branch_code'}, 
            {data: 'sector', name: 'sector'}, 
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'}, */
            {data: 'assets_category', name: 'assets_category'}, 
            {data: 'assets_subcategory', name: 'assets_subcategory'},
            {data: 'demand_date', name: 'demand_date'},
            {data: 'advice_date', name: 'advice_date'}, 

            {data: 'amount', name: 'amount', 
                "render":function(data, type, row){
                     if ( row.amount>=0 ) {
                         return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                         return "N/A";
                     }
                 }
             },

            {data: 'party_name', name: 'party_name'}, 
            {data: 'mobile_number', name: 'mobile_number'},
            {data: 'bill_number', name: 'bill_number'}, 
            {data: 'bill_file_id', name: 'bill_file_id'}, 
            {data: 'status', name: 'status'},
            //{data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(assets_list.table().container()).removeClass( 'form-inline' );

    //GET ITEMS

    $(document).on('change','#company_id',function(){
        var company = $(this).find('option:selected').val();
        
        $.ajax({
            url:"{{route('admin.asset.get.items')}}",
            type:"POST",
            data:
            {
                'company':company,
            },
            success: function(res){
                
                var categorySelect = $('#category');
                categorySelect.empty();
                categorySelect.append('<option value="">Select categories</option>');
                $.each(res.items, function (index, item){
                    categorySelect.append('<option value="' + item.head_id + '">' + item.sub_head + '</option>');
                });
            }
        });
        
    });

    depreciation_list = $('#depreciation_list').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#depreciation_list').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.depreciation.lists') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(), 
                d.branch=$('#branch').val(),
                d.category=$('#category').val(), 
                d.status=$('#status').val(), 
                d.is_search=$('#is_search').val(),
                d.export=$('#export').val()
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
             {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
           {data: 'branch', name: 'branch'},
           /*  {data: 'branch_code', name: 'branch_code'}, 
            {data: 'sector', name: 'sector'}, 
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'}, */
            {data: 'assets_category', name: 'assets_category'}, 
            {data: 'assets_subcategory', name: 'assets_subcategory'}, 
            {data: 'advice_date', name: 'advice_date'}, 

            {data: 'party_name', name: 'party_name'}, 
            {data: 'mobile_number', name: 'mobile_number'},
            {data: 'bill_number', name: 'bill_number'},


            {data: 'amount', name: 'amount', 
                "render":function(data, type, row){
                     if ( row.amount>=0 ) {
                         return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                         return "N/A";
                     }
                 }
             },
             {data: 'current_balance', name: 'current_balance', 
                "render":function(data, type, row){
                     if ( row.current_balance>=0 ) {
                         return row.current_balance+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                         return "N/A";
                     }
                 }
             },

            {data: 'depreciation_per', name: 'depreciation_per'}, 

            {data: 'bill_file_id', name: 'bill_file_id'},  
            //{data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(depreciation_list.table().container()).removeClass( 'form-inline' );


 /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.asset.exportLists') !!}");
        $('form#filter').submit();
        return true;
    });
	*/
	$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export').val(extension);
		
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.asset.exportLists') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExport(start,limit,formData,chunkSize);
					$(".loaders").text(response.percentage+"%");
                }else{
					var csv = response.fileName;
                    console.log('DOWNLOAD');
					$(".spiners").css("display","none");
					$("#cover").fadeOut(100); 
					window.open(csv, '_blank');
                }
            }
        });
    }
	

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
	/*
    $('.export_de').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export_de').val(extension);
        $('form#filter').attr('action',"{!! route('admin.depreciation.exportLists') !!}");
        $('form#filter').submit();
        return true;
    });  
     */
	 $('.export_de').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export_de').val(extension);
		
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExporte(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExporte(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.depreciation.exportLists') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExporte(start,limit,formData,chunkSize);
					$(".loaders").text(response.percentage+"%");
                }else{
					var csv = response.fileName;
                    console.log('DOWNLOAD');
					$(".spiners").css("display","none");
					$("#cover").fadeOut(100); 
					window.open(csv, '_blank');
                }
            }
        });
    }
	

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


    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });


    $('#filter').validate({
      rules: {
      //  status:"required",  

      },
       messages: {  
     //     status: "Please select status",
      },
        
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).addClass('is-invalid');
          });
        }
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).removeClass('is-invalid');
          });
        }
      }
  });

 


 
});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        $(".table-section").removeClass('hideTableData');
        assets_list.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error"); 
 
    $('#branch').val('');
    $('#company_id').val('');
    $('#category').val('');
    $('#status').val(''); 
    $('#is_search').val('no');    
    $(".table-section").addClass("hideTableData");
    assets_list.draw();
}
function searchForm1()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        $(".table-section").removeClass('hideTableData');
        depreciation_list.draw();
    }
}
function resetForm1()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error"); 
    $(".table-section").addClass("hideTableData");
    
    $('#company_id').val('');
    $('#branch').val('');
    $('#category').val('');
    $('#is_search').val('no');     

    depreciation_list.draw();
}

</script>