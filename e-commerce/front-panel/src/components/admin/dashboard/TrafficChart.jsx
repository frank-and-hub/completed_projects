import React from 'react'
import ReactECharts from 'echarts-for-react'

export const TrafficChart = ({ trafficDataChart }) => {
  const getOption = () => ({
    tooltip: {
      trigger: 'item'
    },
    legend: {
      top: '5%',
      left: 'center'
    },
    series: [{
      name: 'Access From',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      label: {
        show: false,
        position: 'center'
      },
      emphasis: {
        label: {
          show: true,
          fontSize: '18',
          fontWeight: 'bold'
        }
      },
      labelLine: {
        show: false
      },
      data: trafficDataChart
    }]
  });

  return (
    <ReactECharts
      option={getOption()}
      style={{ minHeight: "400px" }}
    />
  );
};
