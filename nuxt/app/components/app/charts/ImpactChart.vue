<template>
  <div ref="chartContainer" class="w-full h-full"></div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import * as echarts from 'echarts/core';
import { BarChart, LineChart } from 'echarts/charts';
import {
  GridComponent,
  TooltipComponent,
  TitleComponent,
  ToolboxComponent,
  LegendComponent,
  DataZoomComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
  BarChart,
  LineChart,
  GridComponent,
  TooltipComponent,
  TitleComponent,
  ToolboxComponent,
  LegendComponent,
  DataZoomComponent,
  CanvasRenderer
]);

// Props
const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
  },
  metric: {
    type: String,
    default: 'co2',
    validator: (value) => ['co2', 'landfill', 'water', 'energy'].includes(value)
  }
});

// References
const chartContainer = ref(null);
let chart = null;

// Metric configurations
const metricConfig = {
  co2: {
    name: 'CO₂ Emissions Saved',
    unit: 'kg',
    color: '#10b981', // teal-500
    icon: '🌳'
  },
  landfill: {
    name: 'Landfill Space Saved',
    unit: 'm³',
    color: '#8b5cf6', // purple-500
    icon: '🗑️'
  },
  water: {
    name: 'Water Saved',
    unit: 'liters',
    color: '#3b82f6', // blue-500
    icon: '💧'
  },
  energy: {
    name: 'Energy Saved',
    unit: 'kWh',
    color: '#f97316', // orange-500
    icon: '⚡'
  }
};

// Initialize chart
const initChart = () => {
  if (chartContainer.value) {
    chart = echarts.init(chartContainer.value);
    updateChart();
  }
};

// Update chart with new data
const updateChart = () => {
  if (!chart || !props.data.length) return;
  
  const dates = props.data.map(item => item.date);
  const values = props.data.map(item => item[props.metric]);
  const config = metricConfig[props.metric];
  
  // Calculate cumulative impact
  const cumulativeValues = [];
  let cumulative = 0;
  values.forEach(val => {
    cumulative += val;
    cumulativeValues.push(cumulative);
  });
  
  const option = {
    title: {
      show: false,
      text: `${config.name}`,
      left: 'center'
    },
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'cross',
        crossStyle: {
          color: '#999'
        }
      },
      formatter: function(params) {
        const date = params[0].axisValue;
        let html = `<div style="margin-bottom:5px;">${date}</div>`;
        
        params.forEach(param => {
          const marker = param.marker;
          const name = param.seriesName;
          const value = param.value;
          const unit = config.unit;
          
          html += `${marker} ${name}: ${value} ${unit}<br/>`;
        });
        
        return html;
      }
    },
    legend: {
      data: ['Monthly Impact', 'Cumulative Impact'],
      bottom: 0
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '60px',
      containLabel: true
    },
    xAxis: [
      {
        type: 'category',
        data: dates,
        axisPointer: {
          type: 'shadow'
        }
      }
    ],
    yAxis: [
      {
        type: 'value',
        name: `Monthly ${config.name} (${config.unit})`,
        position: 'left',
        axisLine: {
          show: true,
          lineStyle: {
            color: config.color
          }
        },
        axisLabel: {
          formatter: `{value} ${config.unit}`
        }
      },
      {
        type: 'value',
        name: `Total ${config.name} (${config.unit})`,
        position: 'right',
        axisLine: {
          show: true,
          lineStyle: {
            color: '#94a3b8' // slate-400
          }
        },
        axisLabel: {
          formatter: `{value} ${config.unit}`
        }
      }
    ],
    series: [
      {
        name: 'Monthly Impact',
        type: 'bar',
        data: values,
        yAxisIndex: 0,
        itemStyle: {
          color: config.color
        }
      },
      {
        name: 'Cumulative Impact',
        type: 'line',
        data: cumulativeValues,
        yAxisIndex: 1,
        symbolSize: 8,
        lineStyle: {
          width: 3,
          color: '#94a3b8'
        },
        itemStyle: {
          color: '#94a3b8'
        }
      }
    ]
  };
  
  // Add data zoom for larger datasets
  if (props.data.length > 7) {
    option.dataZoom = [
      {
        type: 'inside',
        start: 0,
        end: 100
      },
      {
        show: true,
        type: 'slider',
        bottom: 25,
        start: 0,
        end: 100
      }
    ];
  }
  
  chart.setOption(option);
};

// Resize chart on window resize
const handleResize = () => {
  if (chart) {
    chart.resize();
  }
};

// Watch for data and metric changes
watch(() => props.data, () => {
  if (chart) {
    updateChart();
  }
}, { deep: true });

watch(() => props.metric, () => {
  if (chart) {
    updateChart();
  }
});

// Lifecycle hooks
onMounted(() => {
  initChart();
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  if (chart) {
    chart.dispose();
    chart = null;
  }
  window.removeEventListener('resize', handleResize);
});
</script>
