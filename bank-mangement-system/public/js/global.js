$.validator.addMethod("checkAmount", function(value, element,p) {
    if(parseFloat(value).toFixed(2) > 0 )
    {
    $.validator.messages.checkAmount = "";
    result = true;
    }else{
    $.validator.messages.checkAmount = "Amount Should be greater than zero.";
    result = false;
    }  
    return result;
}, "");
