var start = moment().subtract(6, 'days');
var end = moment(now);
window.filter = [];
window.filter.startDate = start.format('YYYY-MM-DD');
window.filter.endDate = end.format('YYYY-MM-DD');
window.filter.status = null;
function daterange(selector, URL = null,getChartData=null) {
    $(document).ready(function () {

        function cb(start, end) {
            $(selector + ' span').html(start.format('D MMM') + ' - ' + end.format('D MMM'));
            var data = {
                "start_date": start.format('Y-M-D'),
                "end_date": end.format('Y-M-D')
            };

            getChartData(URL, data);
        }

        $(selector).daterangepicker({
            opens: 'left',
            startDate:start,
            endDate:end,
            maxDays: 7,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                    'month').endOf('month')],
                'From Start': [moment(), moment()]
            }
        }, cb).on('apply.daterangepicker', function (ev, picker) {
            window.filter.startDate = picker.startDate.format('YYYY-MM-DD 00:00');
            window.filter.endDate = picker.endDate.format('YYYY-MM-DD 00:00');

        });

        cb(start, end);

            // $(".daterangepicker, .ranges, ul").children(0).removeClass('active');
            //  $(".daterangepicker, .ranges, ul").children(2).AddClass('active');
    });




};
