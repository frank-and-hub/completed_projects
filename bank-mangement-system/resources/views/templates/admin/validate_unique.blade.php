$.validator.addMethod("{{$value}}", function(value,e){
	let {{$value}} = $('#{{$value}}').val();
	$.ajaxSetup({
		headers:
		{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
	});
	$('input[name="{{$value}}"]').on('keyup',function(){
		$.post(
			"{{url('/')}}/admin/companies/{{$value}}_unique",
			{ '{{$value}}' : {{$value}} },
			function(e){
				var data = jQuery.parseJSON(e.data);
				if(data > 0){
					$.validator.messages.{{$value}} = "";
					result = true;
				}else{
					$.validator.messages.{{$value}} = "This {{str_replace('_',' ',$value)}} already exists";
					result = false;
				}
			}
		);
	});
	return result;
}, "");
