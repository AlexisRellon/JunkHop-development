<template>
  <div ref="chartContainer" class="w-full h-full"></div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import * as echarts from 'echarts/core';
import { PieChart, BarChart } from 'echarts/charts';
import {
  GridComponent,
  TooltipComponent,
  TitleComponent,
  ToolboxComponent,
  LegendComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
  PieChart,
  BarChart,
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
  },
  type: {
    type: String,
    default: 'pie',
    validator: (value) => ['pie', 'bar'].includes(value)
  }
});

// References
const chartContainer = ref(null);
let chart = null;

// Color palette
const colors = {
  'Paper': '#3b82f6',     // blue-500
  'Plastics': '#10b981',  // green-500
  'Metals': '#ef4444',    // red-500
  'Glass': '#8b5cf6',     // purple-500
  'Electronics': '#f97316', // orange-500
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
  
  if (props.type === 'pie') {
    renderPieChart();
  } else {
    renderBarChart();
  }
};

// Render pie chart
const renderPieChart = () => {
  const option = {
    tooltip: {
      trigger: 'item',
      formatter: '{b}: {c} kg ({d}%)'
    },
    legend: {
      orient: 'vertical',
      right: 10,
      top: 'center',
      data: props.data.map(item => item.name)
    },
    series: [
      {
        name: 'Material Type',
        type: 'pie',
        radius: ['40%', '70%'],
        avoidLabelOverlap: false,
        itemStyle: {
          borderRadius: 10,
          borderColor: '#fff',
          borderWidth: 2
        },
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
        data: props.data.map(item => {
          return {
            name: item.name,
            value: item.value,
            itemStyle: {
              color: colors[item.name] || item.color
            }
          };
        })
      }
    ]
  };
  
  chart.setOption(option);
};

// Render bar chart
const renderBarChart = () => {
  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'shadow'
      },
      formatter: '{b}: {c} kg'
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    xAxis: {
      type: 'value',
      axisLabel: {
        formatter: '{value} kg'
      }
    },
    yAxis: {
      type: 'category',
      data: props.data.map(item => item.name),
      axisTick: {
        alignWithLabel: true
      }
    },
    series: [
      {
        name: 'Material Amount',
        type: 'bar',
        barWidth: '60%',
        data: props.data.map(item => {
          return {
            value: item.value,
            itemStyle: {
              color: colors[item.name] || item.color
            }
          };
        })
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

// Watch for data and type changes
watch(() => props.data, () => {
  if (chart) {
    updateChart();
  }
}, { deep: true });

watch(() => props.type, () => {
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
