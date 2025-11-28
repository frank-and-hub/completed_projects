

$(document).ready(function () {

    if (editUrl != '') {
        $.ajax({
            url: editUrl,
            method: 'get',
            beforeSend: function () {
                $("#loader").removeClass('d-none');
                $("#availabilityLoader").removeClass('d-none');

            },
            success: function (res) {
                $("#loader").addClass('d-none');
                $("#availabilityLoader").addClass('d-none');

                $(res.data).each(function (idx, value) {
                    var opening_time = value.opening_time;
                    var closing_time = value.closing_time;
                    var availability = value.availability;
                    var day = value.day;
                    var type = value.type;
                    if (type != 'custom') {
                        $("." + day).find(".day-selected-check-mark").remove();
                        $("." + day).append("<div class='day-selected-check-mark pl-3'> <svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='#48D33A' class='bi bi-check-circle-fill' viewBox='0 0 16 16'> <path d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'></path> </svg>  </div>");
                        $("#" + day).removeClass('d-none');
                        $("." + day).addClass('day-box-selected');
                        $("." + day).removeClass('day-box');
                        $("#" + day).find(".availability").children().remove();
                        $html = "<input type='hidden' name='day[]' value='" + day + "'><input type='hidden' name='type[]' class='type' value='" + type + "'><span>" + availability + "</span><input type='hidden' name='opening_time[]' class='opening_time' value='" + opening_time + "'> <input type='hidden' name='closing_time[]' class='closing_time' value='" + closing_time + "'> ";
                        $("#" + day).find(".availability").append($html);
                    } else {
                        var availability = tConvert(opening_time) + " To " + tConvert(closing_time);
                        $("." + day).find(".day-selected-check-mark").remove();
                        $("." + day).addClass('day-box-selected');
                        $("." + day).append("<div class='day-selected-check-mark pl-3'> <svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='#48D33A' class='bi bi-check-circle-fill' viewBox='0 0 16 16'> <path d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'></path> </svg>  </div>");
                        $("#" + day).removeClass('d-none');
                        $("." + day).removeClass('day-box');

                        $("#" + day).find(".availability").children().remove();
                        $html = "<input type='hidden' name='day[]' value='" + day + "'><input type='hidden' name='type[]' class='type' value='" + type + "'><span>" + availability + "</span><input type='hidden' name='opening_time[]' class='opening_time' value='" + opening_time + "'> <input type='hidden' name='closing_time[]' class='closing_time' value='" + closing_time + "'> ";

                        $("#" + day).find(".availability").append($html);

                    }

                });

            }
        })
    }

    function tConvert(time) {
        // Check correct time format and split into components
        time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

        if (time.length > 1) { // If time format correct
            time = time.slice(1);  // Remove full string match value
            time = time.slice(0, -1);
            time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM
            time[0] = +time[0] % 12 || 12; // Adjust hours
        }

        return time.join(''); // return adjusted time or original string
    }


    //when checked every day
    $("#everyday").click(function () {
        var checked = $(this).is(":checked");
        if ($(".parktiming-box").find(".day-selected-check-mark").length > 0) {
            if (checked) {
                $(".day").each(function (indx, value) {
                    if ($(this).hasClass('day-box-selected')) {
                        $(this).addClass('day-box-update');
                        WhiteCheckMark($(this));
                    } else {
                        $(this).removeClass('day-box');
                        $(this).addClass('day-box-selected day-box-update');
                    }
                });
                // $("#day_update_btn").removeClass('d-none');
                // $("#day_save_btn").addClass('d-none');
                $("#day_remove_btn").css('visibility', 'visible');

            } else {


                $(".day").each(function (indx, elemnet) {
                    if ($(this).find('.day-selected-check-mark').length > 0) {
                        $(this).removeClass('day-box-update');
                        GreenCheckMark($(this));
                    } else {
                        $(this).addClass('day-box');
                        $(this).removeClass('day-box-selected day-box-update');
                    }
                });
                $("#day_remove_btn").css('visibility', 'hidden');
                $("#day_update_btn").addClass('d-none');
                $("#day_save_btn").removeClass('d-none');
            }

        }

        if ($(".parktiming-box").find(".day-selected-check-mark").length == 0) {

            if (checked) {
                $(".days-box").find('.day-box').each(function () {
                    $(this).addClass('selected-days');
                    $(this).removeClass('day-box');
                });
            } else {
                $(".days-box").find('.selected-days').each(function () {
                    $(this).removeClass('selected-days');
                    $(this).addClass('day-box');
                });
            }
        }

        radionBtnEnableDisable();
        ShowAndHideSaveBtn();

    });

    //when checked custom
    $(".parktiming-box input[name=type]").click(function () {
        if ($("input:radio[name=type]:checked").val() == "custom") {
            $(".custom-time").removeClass('d-none');
        } else {
            $(".custom-time").addClass('d-none');
        }
    });

    //when click unselected days
    $(".parktiming-box").on('click', '.day-box', function () {
        $(this).addClass('selected-days');
        $(this).removeClass('day-box');

        $("#dy_save_btn").removeClass('d-none');

        radionBtnEnableDisable();
        EveryDayChecked();
        hideBtn();
        ShowAndHideSaveBtn();
    });

    //When click selected day
    $(".parktiming-box").on('click', '.selected-days', function () {
        $(this).removeClass('selected-days');
        $(this).addClass('day-box');
        if ($(".days-box").find(".selected-days").length < 7) {
            $("#everyday").prop('checked', false);

        }
        radionBtnEnableDisable();
        ShowAndHideSaveBtn();

    });

    //When click saved  box
    $(".parktiming-box").on('click', '.day-box-selected', function () {
        var check_mark = $(this).find(".day-selected-check-mark");
        if (check_mark.hasClass("day-selected-check-mark")) {
            $(this).addClass('day-box-update');
            // $(this).addClass('selected-days');

            WhiteCheckMark($(this));
            $("#day_remove_btn").css('visibility', 'visible');
            // $("#day_update_btn").removeClass('d-none');
            // $("#day_save_btn").addClass('d-none');
        } else {
            $(this).removeClass('day-box-selected');
            $(this).addClass('day-box');
        }
        radionBtnEnableDisable();

        EveryDayChecked();
        ShowAndHideSaveBtn();
        hideBtn();

    });


    //when click updated selecte btm
    $(".parktiming-box").on('click', '.day-box-update', function () {
        $(this).removeClass('day-box-update');
        GreenCheckMark($(this));
        $("#everyday").prop('checked', false);
        hideBtn();
        ShowAndHideSaveBtn();
        radionBtnEnableDisable();

    });

    //day save click
    $("#day_save_btn").click(function () {
        $("#everyday").prop('checked', false);
        var availability = $("input:radio[name=type]:checked").val();
        var opening_time = $("#opening_time").val();
        var closing_time = $("#closing_time").val();
        var selected_days = $(".days-box").find('.selected-days');
        var unselected_days = $(".days-box").find(".day-box");
        var updateDay = $(".days-box").find('.day-box-update');

        if (selected_days.length != 0 || updateDay.length != 0) {
            if (availability != 'custom') {

                updateDay.each(function () {

                    var selected_day = $(this);
                    var type = availability.replace(/ /g, "_").toLowerCase();
                    SetAvailability(selected_day, availability, type);
                    $(this).removeClass('day-box-update');
                });

                selected_days.each(function () {
                    if (!$(this).find('.day-selected-check-mark').hasClass('day-selected-check-mark')) {
                        var selected_day = $(this);
                        var type = availability.replace(/ /g, "_").toLowerCase();
                        SetAvailability(selected_day, availability, type);
                    }
                });


            } else {
                if (opening_time == '' || closing_time == '') {
                    $.alert({
                        title: 'Day!',
                        content: 'Please Fill The time',
                    });

                } else {
                    updateDay.each(function () {
                        var selected_day = $(this);
                        var time = tConvert(opening_time) + " To " + tConvert(closing_time);
                        var type = availability.replace(/ /g, "_").toLowerCase();
                        SetAvailability(selected_day, time, type, opening_time, closing_time);
                        $(this).removeClass('day-box-update');

                    });

                    selected_days.each(function () {
                        if (!$(this).find('.day-selected-check-mark').hasClass('day-selected-check-mark')) {
                            var selected_day = $(this);
                            var time = tConvert(opening_time) + " To " + tConvert(closing_time);
                            var type = availability.replace(/ /g, "_").toLowerCase();
                            SetAvailability(selected_day, time, type, opening_time, closing_time);
                        }
                    });
                    $(this).addClass("d-none");
                }
            }
        } else {
            $.alert({
                title: 'Day!',
                content: 'Please Select Day',
            });
        }

        if (unselected_days.length != 0) {
            unselected_days.each(function () {
                var unselected_day = $(this);
                UnsetAvailability(unselected_day);
            });
        }
        $("#day_remove_btn").css('visibility', 'hidden');
        radionBtnEnableDisable();
        ShowAndHideSaveBtn();
    });

    //day update click
    // $("#day_update_btn").click(function () {
    //     $("#everyday").prop('checked', false);
    //     var availability = $("input:radio[name=type]:checked").val();
    //     var opening_time = $("#opening_time").val();
    //     var closing_time = $("#closing_time").val();
    //     var updateDay = $(".days-box").find('.day-box-update');

    //     if (availability != 'custom') {
    //         updateDay.each(function () {

    //             var selected_day = $(this);
    //             var type = availability.replace(/ /g, "_").toLowerCase();
    //             SetAvailability(selected_day, availability, type);
    //             $(this).removeClass('day-box-update');
    //         });
    //     } else {
    //         if (opening_time == '' || closing_time == '') {
    //             $.alert({
    //                 title: 'Day!',
    //                 content: 'Please Fill The time',
    //             });

    //         }
    //         else {
    //             updateDay.each(function () {
    //                 var selected_day = $(this);
    //                 var time = tConvert(opening_time) + " To " + tConvert(closing_time);
    //                 var type = availability.replace(/ /g, "_").toLowerCase();
    //                 SetAvailability(selected_day, time, type, opening_time, closing_time);
    //                 $(this).removeClass('day-box-update');

    //             });
    //         }
    //     }
    //     ShowAndHideSaveBtn();
    //     hideBtn();
    // })

    //when click remove btn
    $("#day_remove_btn").click(function (e) {

        var checkedUpdatedDay = $(".parktiming-box").find(".day-box-update");
        var selected_day = $(".parktiming-box").find(".selected_days");
        var dflt = e.preventDefault();
        $.confirm({
            title: 'Delete',
            content: "Do you want to remove selected Day(s)",
            buttons: {
                confirm: {
                    btnClass: 'btn btn-success',
                    action: function (e) {
                        checkedUpdatedDay.each(function () {
                            UnsetAvailability($(this));
                            $(this).removeClass("day-box-update day-box-selected selected-days");
                            $(this).addClass("day-box");
                            $(this).find(".day-selected-check-mark").remove();
                            $("#everyday").prop('checked', false);

                        });


                        hideBtn();
                        radionBtnEnableDisable();

                        ShowAndHideSaveBtn();

                    }
                },
                cancel: function () {
                    null;
                },
            }
        });

    })

    function SetAvailability(selected_day, availability, type = null, opening_time = null, closing_time = null) {
        var day = selected_day.text().trim().toLowerCase();
        selected_day.find(".day-selected-check-mark").remove();
        $day = selected_day.text();
        selected_day.append("<div class='day-selected-check-mark pl-3'> <svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='#48D33A' class='bi bi-check-circle-fill' viewBox='0 0 16 16'> <path d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'></path> </svg>  </div>");
        $("#" + day).removeClass('d-none');
        $("#" + day).find(".availability").children().remove();

        $html = "<input type='hidden' name='day[]' value='" + day + "'><input type='hidden' name='type[]' class='type' value='" + type + "'><span>" + availability + "</span><input type='hidden' name='opening_time[]' class='opening_time' value='" + opening_time + "'> <input type='hidden' name='closing_time[]' class='closing_time' value='" + closing_time + "'> ";
        if (opening_time != null && closing_time != null) {

        }
        $("#" + day).find(".availability").append($html);

        selected_day.removeClass('selected-days');
        selected_day.addClass('day-box-selected');


    }

    function UnsetAvailability(unselected_day) {
        var day = unselected_day.text().trim().toLowerCase();
        $("#" + day).addClass('d-none');
        $("#" + day).find(".availability").text('');
    }
    function hideBtn() {
        var checkedUpdatedDay = $(".parktiming-box").find(".day-box-update").length;
        var selectedDay = $(".parktiming-box").find(".selected-days").length;

        if (checkedUpdatedDay == 0 || selectedDay != 0) {
            $("#day_remove_btn").css('visibility', 'hidden');

        }
    }

    function ShowAndHideSaveBtn() {

        var day_selected = $(".parktiming-box").find(".selected-days:not(:has(.day-selected-check-mark))").length;
        var selected_day = $(".parktiming-box").find(".day-box-update").length;
        if (day_selected != 0 || selected_day != 0) {
            $("#day_save_btn").removeClass('d-none')
        } else {
            $("#day_save_btn").addClass('d-none')
        }
    }

    function WhiteCheckMark(selector) {
        var check_mark = $(selector).find(".day-selected-check-mark");
        check_mark.find('svg').attr('fill', '#fff');
    }
    function GreenCheckMark(selector) {
        var check_mark = $(selector).find(".day-selected-check-mark");
        check_mark.find('svg').attr('fill', '#48D33A');
    }

    function EveryDayChecked() {
        var selected_day_length = $(".days-box").find(".selected-days").length;
        var saved_day_length = $(".parktiming-box").find(".day-box-update").length;

        if (selected_day_length == 7 || selected_day_length + saved_day_length == 7) {
            $("#everyday").prop('checked', true);

        } else {
            $("#everyday").prop('checked', false);

        }
    }

    function radionBtnEnableDisable() {

        var selected_day_length = $(".days-box").find(".selected-days").length;
        var saved_day_length = $(".parktiming-box").find(".day-box-update").length;
        if(selected_day_length > 0 || saved_day_length > 0){
            $("#down_to_dusk").prop('disabled', false);
            $("#24_hours").prop('disabled', false);
            $("#custom_time").prop('disabled', false);
        }else if(selected_day_length == 0 && saved_day_length == 0){
            $("#down_to_dusk").prop('disabled', true);
            $("#24_hours").prop('disabled', true);
            $("#custom_time").prop('disabled', true);
        }
    }


});
