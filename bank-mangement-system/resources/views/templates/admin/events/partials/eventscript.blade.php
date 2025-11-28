<script type="text/javascript">
'use strict';

//Public Globals
const days = ['Sunday', 'Monday', 'Tuesday', 'Wedensday', 'Thursday', 'Friday', 'Saturday'];
const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

let c_date = new Date();
let day = c_date.getDay();
let month = c_date.getMonth();
let year = c_date.getFullYear();

(function App() {

    const calendar = `<div class="container">
            <div class="row">
                <div class="col-sm-6 col-12 d-flex">
                    <div class="card border-0 mt-5 flex-fill">
                        <div class="card-header py-3 d-flex justify-content-between">
                            <span class="prevMonth">&#10096;</span>
                            <span><strong id="s_m"></strong></span>
                            <span class="nextMonth">&#10097;</span>
                        </div>
                        <div class="card-body px-1 py-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <thead class="days text-center">
                                        <tr>
                                            ${Object.keys(days).map(key => (
                                                `<th><span>${days[key].substring(0,3)}</span></th>`
                                            )).join('')}                                            
                                        </tr>
                                    </thead>
                                    <tbody id="dates" class="dates text-center"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-12 d-flex pa-sm">
                    <div class="card border-0 mt-5 flex-fill d-none" id="event">
                        <div class="card-header py-3 text-center">
                            Add Event
                            <button type="button" class="close hide">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="card-body px-1 py-3">
                            <div class="text-center">
                                <span class="event-date">06 June 2020</span><br>
                                <span class="event-day">Monday</span>
                            </div> 
                            <div class="events-today my-3 px-3">
                               
                            </div> 
                            <div class="input-group events-input mb-3 col-10 mx-auto mt-2">
                                <input type="text" class="form-control" placeholder="Add Event" id="eventTxt">
                                <div class="input-group-append">
                                    <button class="btn btn-danger" type="button" id="createEvent">+</button>
                                </div>
                            </div>                        
                        </div>
                    </div>                            
                </div>
            </div>
        </div>
        <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 200px;">
            <div class="toast" style="position: absolute; top: 0; right: 15px;" data-delay="3000">
                <div class="toast-body">
                    
                </div>
            </div>
        </div>`;
    document.getElementById('app').innerHTML = calendar;   
})()

function renderCalendar(m, y) {
    //Month's first weekday
    let firstDay = new Date(y, m, 1).getDay();  
    //Days in Month
    let d_m = new Date(y, m+1, 0).getDate();
    //Days in Previous Month
    let d_pm = new Date(y, m, 0).getDate();
    
    let table = document.getElementById('dates');
    table.innerHTML = '';
    let s_m = document.getElementById('s_m');
    s_m.innerHTML = months[m] + ' ' + y;
    let date = 1;

    let dt = new Date(Date.parse(months[m] +" 1, "+y+"")).getMonth()+1;
    let startDate = new Date(y + "-" + dt + "-" + date);




    var getTot = new Date(startDate.getMonth(), startDate.getFullYear(), 0).getDate();
    var sat = new Array();   //Declaring array for inserting Saturdays
    var sun = new Array();   //Declaring array for inserting Sundays

    for(var i=1;i<=getTot;i++){    //looping through days in month
        var newDate = new Date(startDate.getFullYear(),startDate.getMonth(),i)
        if(newDate.getDay()==0){   //if Sunday
            sun.push(i);
        }
        if(newDate.getDay()==6){   //if Saturday
            sat.push(i);
        }

    }
    delete sat["0"];
    delete sat["2"];
    delete sat["4"];
    console.log(sat);
    console.log(sun);

    //remaing dates of last month
    let r_pm = (d_pm-firstDay) +1;
    for (let i = 0; i < 6; i++) {
        let row = document.createElement('tr');
        for (let j = 0; j < 7; j++) {
            if (i === 0 && j < firstDay) {  
                let cell = document.createElement('td');
                let span = document.createElement('span');
                let cellText = document.createTextNode(r_pm);
                span.classList.add('ntMonth');
                span.classList.add('prevMonth');                  
                cell.appendChild(span).appendChild(cellText);
                row.appendChild(cell);
                r_pm++;
            }
            else if (date > d_m && j <7) {
                if (j!==0) {
                    let i = 0; 
                    for (let k = j; k < 7; k++) {
                         i++                                             
                        let cell = document.createElement('td');
                        let span = document.createElement('span');
                        let cellText = document.createTextNode(i);
                        span.classList.add('ntMonth');                    
                        span.classList.add('nextMonth');                    
                        cell.appendChild(span).appendChild(cellText);
                        row.appendChild(cell);          
                    };                  
                }                
               break;
            }
            else {
                let cell = document.createElement('td');
                let span = document.createElement('span');
                let cellText = document.createTextNode(date);
                span.classList.add('showEvent');
                var classMonth = m+1; 
                if(date < 10){
                    var classDay = '0'+date+'';
                }else{
                    var classDay = date;    
                }

                if(classMonth < 10){
                    var classMonth = '0'+classMonth+'';
                }else{
                    var classMonth = classMonth;    
                }
                span.classList.add('showEvent'+classDay+''+classMonth+''+y+'');
                if (date === c_date.getDate() && y === c_date.getFullYear() && m === c_date.getMonth()) {
                    //span.classList.add('bg-danger');
                    span.classList.add('bg-current');
                    span.classList.add(''+date+'');
                } 
                if(jQuery.inArray(date, sat) !== -1){
                    span.classList.add('sat-holiday');
                }
                if(jQuery.inArray(date, sun) !== -1){
                    span.classList.add('sun-holiday');
                }
                cell.appendChild(span).appendChild(cellText);
                row.appendChild(cell);
                date++;
            }
        }
        table.appendChild(row);
    }
}

renderCalendar(month, year)


    $(function(){
        /*function showEvent(eventDate){
            let storedEvents = JSON.parse(localStorage.getItem('events'));
            console.log('events',storedEvents);
            if (storedEvents == null){
                $('.events-today').html('<h5 class="text-center">No events found</h5 class="text-center">');               
            }else{
                let eventsToday = storedEvents.filter(eventsToday => eventsToday.eventDate === eventDate);
                let eventsList = Object.keys(eventsToday).map(k => eventsToday[k]);
                if(eventsList.length>0){
                    let eventsLi ='';
                    eventsList.forEach(event =>  $('.events-today').html(eventsLi +=`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${event.eventText}
                    <button type="button" class="close remove-event" data-e-id="${event.eId}" data-event-id="${event.id}" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>`));
                }else{
                    $('.events-today').html('<h5 class="text-center">No events found</h5 class="text-center">');
                }               
            }
        }*/

        function showEvent(events){
            if (events == null){
                $('.events-today').html('<h5 class="text-center">No events found</h5 class="text-center">');            
            }else{
                let eventsLi ='';
                events.forEach(event =>  $('.events-today').html(eventsLi +=`<div class="alert alert-danger alert-dismissible fade show ${event.id}-e-box" role="alert">
                ${event.title}
                <button type="button" class="close remove-event" data-e-id="${event.id}" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>`));             
            }
        }

        $(document).on('click', '.prevMonth', function(){
            year = (month === 0) ? year - 1 : year;
            month = (month === 0) ? 11 : month - 1;
            renderCalendar(month, year);
            let state = $( "#stateid option:selected" ).val();
            $.ajax({ 
                type: "POST",    
                url: "{!! route('admin.getallevent') !!}",
                dataType: 'JSON',
                data: {'state':state},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                    if(response.msg_type == 'success')
                    {
                        $('.showEvent').removeClass('choliday');
                        $.each(response.events, function( index, value ) {
                            var hdate = value.start_date;
                            var result = hdate.split('-');
                            var d = result[2];
                            var m = result[1];
                            var y = result[0];
                            var date = m+'/'+d+'/'+y;
                            var a = new Date(date);
                            var dayName = days[a.getDay()];

                            if(dayName != 'Saturday' && dayName != 'Sunday'){
                                $('.showEvent'+d+''+m+''+y+'').addClass('choliday');    
                            }
                        });
                    }else{
                        //$('.events-today').html('<h5 class="text-center">No events found</h5 class="text-center">');
                    }
                }
            });
        })

        $(document).on('click', '.nextMonth', function(){
            year = (month === 11) ? year + 1 : year;
            month = (month + 1) % 12;
            renderCalendar(month, year);
            let state = $( "#stateid option:selected" ).val();
            $.ajax({ 
                type: "POST",   
                url: "{!! route('admin.getallevent') !!}",
                dataType: 'JSON',
                data: {'state':state},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                    if(response.msg_type == 'success')
                    {
                        $('.showEvent').removeClass('choliday');
                        $.each(response.events, function( index, value ) {
                            var hdate = value.start_date;
                            var result = hdate.split('-');
                            var d = result[2];
                            var m = result[1];
                            var y = result[0];
                            var date = m+'/'+d+'/'+y;
                            var a = new Date(date);
                            var dayName = days[a.getDay()];

                            if(dayName != 'Saturday' && dayName != 'Sunday'){
                                $('.showEvent'+d+''+m+''+y+'').addClass('choliday');    
                            }
                        });
                    }else{
                        //$('.events-today').html('<h5 class="text-center">No events found</h5 class="text-center">');
                    }
                }
            });
        })

        $(document).on('change', '#stateid', function(){
            $('.showEvent').removeClass('active');
            $('#event').removeClass('d-none');
            $(this).addClass('active');
            let sDate = $('.event-date').text();
            let todaysDate = sDate.slice(0, 2) +' '+ (months[month]) +' '+ year;
            let eventDay = days[new Date(year, month, $(this).text()).getDay()];
            let eventDate = $(this).text() + month + year;
            let state = $( "#stateid option:selected" ).val();
            //let eventDate = $(this).text() + month + year;
            $('.event-date').html(todaysDate).data('eventdate', eventDate);
            $('.event-day').html(eventDay);
            $.ajax({
                  type: "POST",  
                  url: "{!! route('admin.getevent') !!}",
                  dataType: 'JSON',
                  data: {'eventDate':todaysDate,'state':state},
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
                  success: function(response) { 
                    if(response.msg_type == 'success')
                    {
                        showEvent(response.events);
                    }else{
                        $('.events-today').html('<h5 class="text-center">No events found</h5 class="text-center">');
                    }
                }
            });

            $.ajax({ 
                type: "POST",   
                url: "{!! route('admin.getallevent') !!}",
                dataType: 'JSON',
                data: {'state':state},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                    if(response.msg_type == 'success')
                    {
                        $('.showEvent').removeClass('choliday');
                        $.each(response.events, function( index, value ) {
                            var hdate = value.start_date;
                            var result = hdate.split('-');
                            var d = result[2];
                            var m = result[1];
                            var y = result[0];

                            var date = m+'/'+d+'/'+y;
                            var a = new Date(date);
                            var dayName = days[a.getDay()];

                            if(dayName != 'Saturday' && dayName != 'Sunday'){
                                $('.showEvent'+d+''+m+''+y+'').addClass('choliday');    
                            }
                            
                        });
                    }else{
                        //$('.events-today').html('<h5 class="text-center">No events found</h5 class="text-center">');
                    }
                }
            });
        })

        $(document).on('change', '#monthstateid', function(){
            let state = $( "#monthstateid option:selected" ).val();

            $.ajax({ 
                type: "POST",   
                url: "{!! route('admin.getstatemonths') !!}",
                dataType: 'JSON',
                data: {'state':state},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                    if(response.msg_type == 'success')
                    {
                        $('input:checkbox').prop('checked',false);
                        $.each(response.holidays, function( index, value ) {
                           $("."+value.month_name).prop('checked', true); 
                        });
                    }else{
                        swal("Warning!", ""+response.view+"", "warning");
                    }
                }
            });
        })
    
        $(document).on('click', '.showEvent', function(){
            $('.showEvent').removeClass('active');
            $('#event').removeClass('d-none');
            $(this).addClass('active');
            let todaysDate = $(this).text() +' '+ (months[month]) +' '+ year;
            let eventDay = days[new Date(year, month, $(this).text()).getDay()];
            let eventDate = $(this).text() + month + year;
            let state = $( "#stateid option:selected" ).val();
            //let eventDate = $(this).text() + month + year;
            $('.event-date').html(todaysDate).data('eventdate', eventDate);
            $('.event-day').html(eventDay);
            $.ajax({
                  type: "POST",  
                  url: "{!! route('admin.getevent') !!}",
                  dataType: 'JSON',
                  data: {'eventDate':todaysDate,'state':state},
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
                  success: function(response) { 
                    if(response.msg_type == 'success')
                    {
                        showEvent(response.events);
                    }else{
                        $('.events-today').html('<h5 class="text-center">No events found</h5 class="text-center">');
                    }
                }
            });
        })

        $(document).on('click', '.hide', function(){
            $('#event').addClass('d-none');
        })

        $(document).on('click', '#createEvent', function(){
            let obj = [];

            let state = $( "#stateid option:selected" ).val();

            /*if(state > 0){*/
                let stateid = $( "#stateid option:selected" ).val();
                let eventDate = $('.event-date').data('eventdate');
                let eDate = $('.event-date').html();
                let eventText = $('#eventTxt').val();
                let valid = false;
                $('#eventTxt').removeClass('data-invalid');
                $('.error').remove();
                if (eventText == ''){
                    $('.events-input').append(`<span class="error">Please enter event</span>`);
                    $('#eventTxt').addClass('data-invalid');
                    $('#eventTxt').trigger('focus');
                }else if(eventText.length < 3){
                    $('#eventTxt').addClass('data-invalid');
                    $('#eventTxt').trigger('focus');
                    $('.events-input').append(`<span class="error">please enter at least three characters</span>`);
                }else{
                    valid = true;
                }
                if (valid){

                    $.ajax({
                          type: "POST",  
                          url: "{!! route('admin.addevent') !!}",
                          dataType: 'JSON',
                          data: {'eventDate':eDate,'eventText':eventText,'stateid':stateid},
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) { 
                            if(response.msg_type == 'success')
                            {
                                var hdate = response.date;
                                var result = hdate.split('-');
                                var d = result[2];
                                var m = result[1];
                                var y = result[0];
                                var date = m+'/'+d+'/'+y;
                                var a = new Date(date);
                                var dayName = days[a.getDay()];

                                if(dayName != 'Saturday' && dayName != 'Sunday'){
                                    $('.showEvent'+d+''+m+''+y+'').addClass('choliday');    
                                }

                                $('#eventTxt').val('');
                                $('.toast-body').html('Your event have been added');
                                $('.toast').toast('show');

                                $('.events-today').append('<div class="alert alert-danger alert-dismissible fade show '+response.eId+'-e-box" role="alert">'+eventText+'<button type="button" class="close remove-event" data-e-id="'+response.eId+'" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');

                            }else{
                                swal("Error!", "Something went wrong!", "error");
                            }
                        }
                    });  
                }    
            /*}else{
                swal("Warning!", "Please select a state first!", "warning");
            }*/   
        })

        $(document).on('click', '.remove-event', function(e){
           
            let eDate = $('.event-date').html();

            var date    = new Date(eDate)
            var twoDigitMonth = ((date.getMonth().length+1) === 1)? (date.getMonth()+1) : '0' + (date.getMonth()+1);
            var eventDate = date.getFullYear() + "-" + twoDigitMonth + "-" + date.getDate();

            var fullDate = new Date()
            var twoDigitMonth = ((fullDate.getMonth().length+1) === 1)? (fullDate.getMonth()+1) : '0' + (fullDate.getMonth()+1);
            var currentDate = fullDate.getFullYear() + "-" + twoDigitMonth + "-" + fullDate.getDate();
            //Sachin sir ne equal date ka lagaya h 2/2/2022
            if(new Date(currentDate) <= new Date(eventDate)){
                
                let eId = $(this).data('e-id');
                e.preventDefault();
                swal({
                  title: "Are you sure, you want to delete this event?",
                  text: "",
                  icon: "warning",
                  buttons: [
                    'No, cancel it!',
                    'Yes, I am sure!'
                  ],
                  dangerMode: true,
                }).then(function(isConfirm) {
                  if (isConfirm) {
                    removeEvent(eId);
                  } 
                });
            }else{
                swal("Error!", "You don't have any permission to delete this event!", "error");
            }
        })

        $(document).on('click', '.createMonth', function(e){
           if ($('input[name^=month]:checked').length <= 0) {
                swal("Warning!", "Please select atleast one month!", "warning");
                return false;
            }else{
                return true;
            }
        })

        function removeEvent(eId){
            $.ajax({
              type: "POST",  
              url: "{!! route('admin.removeevent') !!}",
              dataType: 'JSON',
              data: {'eId':eId},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                    if(response.msg_type == 'success')
                    {
                        $('.'+eId+'-e-box').remove();
                        $('.toast-body').html('Your event have been removed');
                        $('.toast').toast('show');  
                    }else{
                        swal("Error!", "Something went wrong!", "error");
                    }
                }
            });
        }

        $('.export').on('click',function(){
            var extension = $(this).attr('data-extension');
            $('#investments_export').val(extension);
            $('form#filter').attr('action',"{!! route('admin.holidays.export') !!}");
            $('form#filter').submit();
            return true;
        });


        $(window).bind("load", function() {
            $.ajax({   
                type: "POST", 
                url: "{!! route('admin.getallevent') !!}",
                dataType: 'JSON',
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                    if(response.msg_type == 'success')
                    {
                        $('.showEvent').removeClass('choliday');
                        $.each(response.events, function( index, value ) {
                            var hdate = value.start_date;
                            var result = hdate.split('-');
                            var d = result[2];
                            var m = result[1];
                            var y = result[0];              
                            var date = m+'/'+d+'/'+y;
                            var a = new Date(date);
                            var dayName = days[a.getDay()];

                            if(dayName != 'Saturday' && dayName != 'Sunday'){
                                $('.showEvent'+d+''+m+''+y+'').addClass('choliday');    
                            }
                            console.log('ttt','showEvent'+d+''+m+''+y+'');
                        });
                    }
                }
            });
        });

        // Show loading image
        $( document ).ajaxStart(function() {
            $( ".loader" ).show();
        });

        // Hide loading image
        $( document ).ajaxComplete(function() {
            $( ".loader" ).hide();
        });
    })
</script>
            
