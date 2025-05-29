<template>
  <div>
    <base-header class="pb-6 pb-8 pt-5 pt-md-8 bg-gradient-success">
      <card-stats :cards="adminStats" />
    </base-header>

    <!--Charts-->
    <b-container fluid class="mt--7">
      <b-row>
        <b-col xl="8" class="mb-5 mb-xl-0">
          <card type="default" header-classes="bg-transparent">
            <b-row align-v="center" slot="header">
              <b-col>
                <h6 class="text-light text-uppercase ls-1 mb-1">Projects</h6>
                <h5 class="h3 text-white mb-0">Project Overview</h5>
              </b-col>
            </b-row>
            <line-chart
              :height="350"
              ref="bigChart"
              :chart-data="bigLineChart.chartData"
              :extra-options="bigLineChart.extraOptions"
            >
            </line-chart>
          </card>
        </b-col>

        <b-col xl="4" class="mb-5 mb-xl-0">
          <card header-classes="bg-transparent">
            <b-row align-v="center" slot="header">
              <b-col>
                <h6 class="text-uppercase text-muted ls-1 mb-1">Tasks</h6>
                <h5 class="h3 mb-0">Tasks Overview</h5>
              </b-col>
            </b-row>

            <bar-chart
              :height="350"
              ref="barChart"
              :chart-data="redBarChart.chartData"
              :extra-options="redBarChart.extraOptions"
            >
            </bar-chart>
          </card>
        </b-col>
      </b-row>
      <!-- End charts-->

      <!--Tables-->
      <b-row class="mt-5">
        <b-col xl="8" class="mb-5 mb-xl-0">
          <page-visits-table></page-visits-table>
        </b-col>
        <b-col xl="4" class="mb-5 mb-xl-0">
          <social-traffic-table></social-traffic-table>
        </b-col>
      </b-row>
      <!--End tables-->
    </b-container>
  </div>
</template>

<script>
  // Charts
  import * as chartConfigs from '@/components/Charts/config';
  import LineChart from '@/components/Charts/LineChart';
  import BarChart from '@/components/Charts/BarChart';

  // Components
  import BaseProgress from '@/components/BaseProgress';
  import StatsCard from '@/components/Cards/StatsCard';
  import { adminStats } from '@/data/cardStats';
  import CardStats from './CardStats.vue';
  
  // API
  import { getProjectCountByMonth, getTaskCountByMonth } from '../api/statsCardsApi';

  export default {
    components: {
      LineChart,
      BarChart,
      CardStats,
      BaseProgress,
      StatsCard,
    },
    data() {
      return {
        adminStats,
        bigLineChart: {
          chartData: {
            datasets: [
              {
                label: 'Projects',
                data: Array(12).fill(0),
                fill: true,
                backgroundColor: 'rgba(29,140,248,0.1)',
                borderColor: '#1d8cf8',
                borderWidth: 2,
                pointBackgroundColor: '#1d8cf8',
                pointBorderColor: '#1d8cf8',
                pointHoverBackgroundColor: '#1d8cf8',
                pointHoverBorderColor: '#1d8cf8',
              }
            ],
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          },
          extraOptions: chartConfigs.blueChartOptions,
        },
        redBarChart: {
          chartData: {
            datasets: [
              {
                label: 'Tasks',
                data: Array(12).fill(0),
                backgroundColor: '#fb6340',
                borderColor: '#fb6340',
                borderWidth: 1
              }
            ],
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          },
          extraOptions: chartConfigs.blueChartOptions
        }
      };
    },
    methods: {
      async fetchProjectData() {
        try {
          const response = await getProjectCountByMonth();
          if (response && response.success) {
            const projectData = response.data;
            
            const monthlyCounts = Array(12).fill(0);
            
            projectData.forEach(item => {
              const month = parseInt(item.month.split('-')[1]) - 1;
              monthlyCounts[month] = item.count;
            });
            
            this.bigLineChart.chartData = {
              ...this.bigLineChart.chartData,
              datasets: [{
                ...this.bigLineChart.chartData.datasets[0],
                data: monthlyCounts
              }]
            };
          }
        } catch (error) {
          console.error('Error fetching project data:', error);
        }
      },
      async fetchTaskData() {
        try {
          const response = await getTaskCountByMonth();
          if (response && response.success) {
            const taskData = response.data;
            
            const monthlyCounts = Array(12).fill(0);
            
            taskData.forEach(item => {
              const month = parseInt(item.month.split('-')[1]) - 1;
              monthlyCounts[month] = item.count;
            });
            
            this.redBarChart.chartData = {
              ...this.redBarChart.chartData,
              datasets: [{
                ...this.redBarChart.chartData.datasets[0],
                data: monthlyCounts
              }]
            };
          }
        } catch (error) {
          console.error('Error fetching task data:', error);
        }
      }
    },
    async mounted() {
      await Promise.all([
        this.fetchProjectData(),
        this.fetchTaskData()
      ]);
    }
  };
</script>

<style>
.el-table .cell{
  padding-left: 0px;
  padding-right: 0px;
}
</style>