<template>
  <b-row>
    <b-col xl="3" md="6" v-for="(card, index) in cards" :key="index">
      <stats-card
        :title="card.title"
        :type="card.type"
        :sub-title="card.subtitle"
        :icon="card.icon"
        class="mb-4"
      >
        <template #footer>
          <span :class="card.footerClass + ' mr-2'">{{ card.footerValue }}</span>
          <span class="text-nowrap">{{ card.footerText }}</span>
        </template>
      </stats-card>
    </b-col>
  </b-row>
</template>

<script>
import {
  getCountEmployees,
  getCountClients,
  getCountProjects,
  getCountTasks
} from "../api/statsCardsApi";

export default {
  name: "CardStats",
  data() {
    return {
      cards: [
        {
          title: "Employees",
          type: "gradient-red",
          subtitle: "Loading...",
          icon: "ni ni-single-02",
          footerClass: "text-success",
        },
        {
          title: "Clients",
          type: "gradient-orange",
          subtitle: "Loading...",
          icon: "ni ni-circle-08",
          footerClass: "text-success",
        },
        {
          title: "Projects",
          type: "gradient-green",
          subtitle: "Loading...",
          icon: "ni ni-bullet-list-67",
          footerClass: "text-danger",
        },
        {
          title: "Tasks",
          type: "gradient-info",
          subtitle: "Loading...",
          icon: "ni ni-chart-bar-32",
          footerClass: "text-success",
        }
      ]
    };
  },
  async mounted() {
  const [employees, clients, projects, tasks] = await Promise.all([
    getCountEmployees(),
    getCountClients(),
    getCountProjects(),
    getCountTasks()
  ]);

  this.cards[0].subtitle = employees && employees.count !== undefined ? employees.count : "N/A";
  this.cards[1].subtitle = clients && clients.count !== undefined ? clients.count : "N/A";
  this.cards[2].subtitle = projects && projects.count !== undefined ? projects.count : "N/A";
  this.cards[3].subtitle = tasks && tasks.count !== undefined ? tasks.count : "N/A";
}

};
</script>
