$(document).ready(function(){

    const day_wise_users_chart = document.getElementById('day-wise-parks-bar-chart');
    const data = {
        labels: [],
        datasets: [
            { label: 'Parks', data: [], backgroundColor: '#48D33A', borderColor: '#4877D2', borderWidth: 1, fill: false, barPercentage: 0.4 },
            { label: 'Users', data: [], fill: false, backgroundColor: '#f58802', borderColor: '#EA5024', borderWidth: 1, barPercentage: 0.4, },
        ]
    };


    const cn = {
        type: 'bar', data: data,
        options: {
            responsive: true,
            scales: {
                x: { barPercentage: 0.9, display: true, title: { display: true, text: 'Days' } },
                y: { barPercentage: 0.4, display: true, title: { display: true, text: 'Count' } }
            }
        },
    };

    var barChart = new Chart(day_wise_users_chart, cn);


    function getChartData(URL,data){
        $.ajax({
            url:URL,
            data:data,
            method:'get',
            beforeSend:function(){
                $('#day_wise_users_parks_bar_chart_loader').removeClass('d-none');
            },
            success:function(res){
                $('#day_wise_users_parks_bar_chart_loader').addClass('d-none');
                barChart.data.labels = res.labels, barChart.data.datasets[0].data = res.parks, barChart.data.datasets[1].data = res.users;
                barChart.update();
            }

        })
    }

    daterange("#date-range-filter-day-wise-users",day_wise_parks_users_bar_chart_url,getChartData);
});



