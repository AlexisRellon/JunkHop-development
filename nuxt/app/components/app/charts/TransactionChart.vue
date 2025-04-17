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
  interval: {
    type: String,
    default: 'monthly',
    validator: (value) => ['daily', 'weekly', 'monthly'].includes(value)
  }
});

// References
const chartContainer = ref(null);
let chart = null;

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
  const counts = props.data.map(item => item.count);
  const volumes = props.data.map(item => item.volume);
  
  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'cross',
        crossStyle: {
          color: '#999'
        }
      }
    },
    legend: {
      data: ['Transaction Count', 'Material Volume'],
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
        name: 'Count',
        position: 'left',
        axisLine: {
          show: true,
          lineStyle: {
            color: '#5470c6'
          }
        },
        axisLabel: {
          formatter: '{value}'
        }
      },
      {
        type: 'value',
        name: 'Volume (kg)',
        position: 'right',
        axisLine: {
          show: true,
          lineStyle: {
            color: '#10b981'
          }
        },
        axisLabel: {
          formatter: '{value} kg'
        }
      }
    ],
    series: [
      {
        name: 'Transaction Count',
        type: 'bar',
        yAxisIndex: 0,
        data: counts,
        itemStyle: {
          color: '#5470c6'
        }
      },
      {
        name: 'Material Volume',
        type: 'line',
        yAxisIndex: 1,
        data: volumes,
        symbol: 'circle',
        symbolSize: 8,
        lineStyle: {
          width: 3,
          color: '#10b981'
        },
        itemStyle: {
          color: '#10b981'
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

// Format interval labels based on selected interval
const formatIntervalLabels = () => {
  if (props.interval === 'daily') {
    return props.data.map(item => {
      const date = new Date(item.date);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
  } else if (props.interval === 'weekly') {
    return props.data.map(item => {
      const date = new Date(item.date);
      return `Week ${date.getWeek()} ${date.getFullYear()}`;
    });
  } else {
    return props.data.map(item => {
      const [year, month] = item.date.split('-');
      const date = new Date(year, month - 1);
      return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
  }
};

// Resize chart on window resize
const handleResize = () => {
  if (chart) {
    chart.resize();
  }
};

// Watch for data and interval changes
watch(() => props.data, () => {
  if (chart) {
    updateChart();
  }
}, { deep: true });

watch(() => props.interval, () => {
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
