<template>
  <div ref="chartContainer" class="w-full h-full"></div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import * as echarts from 'echarts/core';
import { LineChart } from 'echarts/charts';
import {
  GridComponent,
  TooltipComponent,
  TitleComponent,
  ToolboxComponent,
  LegendComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
  LineChart,
  GridComponent,
  TooltipComponent,
  TitleComponent,
  ToolboxComponent,
  LegendComponent,
  CanvasRenderer
]);

// Props
const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
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
  
  // Extract dates and prices from the data
  const dates = props.data.map(item => item.date);
  const prices = props.data.map(item => item.price);
  
  const option = {
    tooltip: {
      trigger: 'axis',
      formatter: function(params) {
        const data = params[0];
        return `${data.axisValue}<br/>${data.marker} Price: ₱${data.value.toFixed(2)}`;
      }
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: dates
    },
    yAxis: {
      type: 'value',
      axisLabel: {
        formatter: '₱{value}'
      }
    },
    series: [
      {
        name: 'Price',
        type: 'line',
        data: prices,
        smooth: true,
        symbol: 'circle',
        symbolSize: 6,
        itemStyle: {
          color: '#10b981' // teal-500 color
        },
        lineStyle: {
          width: 3,
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              {
                offset: 0,
                color: '#10b981' // teal-500
              },
              {
                offset: 1,
                color: '#14b8a6' // teal-400
              }
            ]
          }
        },
        areaStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              {
                offset: 0,
                color: 'rgba(16, 185, 129, 0.2)' // teal-500 with opacity
              },
              {
                offset: 1,
                color: 'rgba(16, 185, 129, 0.02)' // teal-500 with low opacity
              }
            ]
          }
        }
      }
    ]
  };
  
  chart.setOption(option);
};

// Resize chart on window resize
const handleResize = () => {
  if (chart) {
    chart.resize();
  }
};

// Watch for data changes
watch(() => props.data, () => {
  if (chart) {
    updateChart();
  }
}, { deep: true });

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
