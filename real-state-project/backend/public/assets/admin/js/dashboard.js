function initializeChart(canvasId, legendId, nodataId, data, labels, colors) {
  var ctx = $("#" + canvasId).get(0).getContext("2d");
  // Show or hide the no data image based on the data
  if (data.every(val => val === 0)) {
      $('#' + nodataId).show();
      $('#' + canvasId).hide();
      $('#' + legendId).hide();
  } else {
      $('#' + nodataId).hide();
      $('#' + canvasId).show();
      $('#' + legendId).show();

      var chartData = {
          datasets: [{
              data: data
              , backgroundColor: colors
              , borderWidth: 0
          }]
          , labels: labels
      };

      var chartOptions = {
          responsive: true
          , maintainAspectRatio: true
          , animation: {
              animateScale: true
              , animateRotate: true
          }
          , legend: {
              display: false
          }
          , legendCallback: function(chart) {
              var text = [];
              text.push('<ul class="legend' + chart.id + '">');
              for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                  text.push('<li><span class="legend-label" style="background-color:' + chart.data.datasets[0].backgroundColor[i] + '"></span>');
                  if (chart.data.labels[i]) {
                      text.push(chart.data.labels[i]);
                  }
                  text.push('</li>');
              }
              text.push('</ul>');
              return text.join("");
          }
          , cutoutPercentage: 70
      };

      window[canvasId] = new Chart(ctx, {
          type: 'doughnut'
          , data: chartData
          , options: chartOptions
      });

      document.getElementById(legendId).innerHTML = window[canvasId].generateLegend();
  }
}
