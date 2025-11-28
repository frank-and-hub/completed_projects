<script type="text/javascript">
$(document).ready(function() {

    var months = {'January': 1, 'February': 2, 'March': 3, 'April': 4, 'May': 5, 'June': 6, 'July': 7, 'August': 8, 'September': 9, 'October': 10, "November": 11, 'December': 12};
       

    var string = $('.fc-center h2').html()
    var monthName = string.replace(/\d+/g, '').split(" ").join("");
    var monthNumber = months[''+monthName+''];

    // Get registered member by id
    $.ajax({
        type: "POST",  
        url: "{!! route('branch.events.nextmonth') !!}",
        dataType: 'JSON',
        data: {'monthNumber':monthNumber},
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if(response.msg_type == 'error'){
                $('.fc-next-button').css('display','none');
            }
        }
    });

    $(document).on('click','.fc-next-button,.fc-prev-button',function(){
        var string = $('.fc-center h2').html()
        var monthName = string.replace(/\d+/g, '').split(" ").join("");
        var monthNumber = months[''+monthName+''];
        $.ajax({
            type: "POST",  
            url: "{!! route('branch.events.nextmonth') !!}",
            dataType: 'JSON',
            data: {'monthNumber':monthNumber},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.msg_type == 'error'){
                    $('.fc-next-button').css('display','none');
                }else{
                    $('.fc-next-button').css('display','block');
                }
            }
        });
    });

    /*var myArray = {id1: 100, id2: 200, "tag with spaces": 300};
    myArray.id3 = 400;
    myArray["id4"] = 500;

    alert(myArray['tag with spaces']);*/

    /*$('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#investments_export').val(extension);
        $('form#filter').attr('action',"{!! route('branch.investment.export') !!}");
        $('form#filter').submit();
        return true;
    });*/
});
</script>

