    $.validator.addMethod("{{$value}}", function(value, element, p){
        let {{$value}} = $('#{{$value}}').val();
        var value = {
            '{{$value}}':{{$value}},
        };
        $.ajax({
            url: "{{url('/')}}/admin/companies/{{$value}}_unique",
            type: "POST",
            data: value,
            dataType: "JSON",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },error:function(){
                console.log('error !');
                return false;
            },success:function(e){
                var data = jQuery.parseJSON(e.data);
                console.log('success !');
                return false;
                if(data > 0){
                    $.validator.messages.{{$value}} = "";
                    result = true;
                }else{
                    $.validator.messages.{{$value}} = "This {{$value}} already exists";
                    result = false;
                }
            }
        });
        return result;
    }, "");