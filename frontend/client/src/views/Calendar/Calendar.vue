<template>
  <div class="calendar-container">
    <vue-cal
      class="vuecal--blue-theme"
      :time="true"
      active-view="day"
      :events="formattedEvents"
      :disable-views="['years', 'year', 'month', 'week']"
      @event-click="onEventClick"
      :min-cell-height="100"
      :min-date="minDate"
      :max-date="maxDate"
      :time-from="8 * 60"
      :time-to="17 * 60"
      :time-step="30"
      :snap-to-time="true"
    />
  </div>
</template>

<script>
import VueCal from 'vue-cal'
import 'vue-cal/dist/vuecal.css'
import { getAllCalendarEvents } from '../../api/calendarApi'

export default {
  components: { VueCal },
  data() {
    const today = new Date()
    return {
      minDate: new Date(today.getFullYear(), today.getMonth(), 1),
      maxDate: new Date(today.getFullYear(), today.getMonth() + 1, 0),
      events: [],
      selectedEvent: null
    }
  },
  computed: {
    formattedEvents() {
      return this.events.map(event => {
        // Create proper datetime objects using appointment_date + time parts
        const startDate = this.combineDateAndTime(
          event.appointment_date, 
          event.start_time.split('T')[1].split('.')[0]
        )
        const endDate = this.combineDateAndTime(
          event.appointment_date,
          event.end_time.split('T')[1].split('.')[0]
        )
        
        return {
          start: startDate,
          end: endDate,
          title: event.client_email,
          content: `<i class="icon fa fa-calendar"></i> ${event.project_name} - ${event.client_company}`,
          class: 'appointment',
          originalEvent: event
        }
      })
    }
  },
  async created() {
    await this.fetchCalendarEvents()
  },
  methods: {
    async fetchCalendarEvents() {
      try {
        const events = await getAllCalendarEvents()
        if (events) {
          this.events = events
        }
      } catch (error) {
        console.error('Error fetching calendar events:', error)
      }
    },
    combineDateAndTime(dateString, timeString) {
      const date = new Date(dateString)
      const [hours, minutes, seconds] = timeString.split(':')
      date.setHours(hours, minutes, seconds || 0)
      return date
    },
    onEventClick(event) {
      this.selectedEvent = event.originalEvent
      // You might want to add your event click handling here
    },
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      return new Date(dateString).toLocaleDateString(undefined, options)
    },
    formatTime(datetimeString) {
      const timePart = datetimeString.split('T')[1].split('.')[0]
      const [hours, minutes] = timePart.split(':')
      const hour = parseInt(hours, 10)
      const ampm = hour >= 12 ? 'PM' : 'AM'
      const hour12 = hour % 12 || 12
      return `${hour12}:${minutes} ${ampm}`
    }
  }
}
</script>

<style scoped>
.calendar-container {
  max-width: 1300px;
  margin: 0 auto;
  height: 100vh;
  position: relative;
}

/* Calendar theme */
.vuecal--blue-theme .vuecal__title-bar {
  background-color: #42b983;
  color: white;
}

.vuecal--blue-theme .vuecal__arrow.vuecal__arrow--prev,
.vuecal--blue-theme .vuecal__arrow.vuecal__arrow--next {
  background-color: #42b983;
  color: white;
}

.vuecal--blue-theme .vuecal__header {
  background-color: #f5f5f5;
}

/* Cell styling */
.vuecal__time-cell,
.vuecal__cell-content {
  height: 100px !important;
  min-height: 100px !important;
  position: relative;
}

.vuecal__time-cell-line {
  height: 100px !important;
}

/* Event styling */
.vuecal__event.appointment {
  position: absolute;
  width: calc(100% - 8px);
  left: 4px;
  right: 4px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background-color: rgba(66, 185, 131, 0.9);
  border: 1px solid rgb(33, 150, 100);
  color: #fff;
  cursor: pointer;
  font-size: 14px;
  padding: 8px;
  min-height: 40px;
  max-height: 90px;
  overflow-y: auto;
  white-space: normal;
  transition: height 0.2s ease;
}

.vuecal__event.appointment:hover {
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  z-index: 1;
}

.vuecal__event-content {
  white-space: normal;
  overflow: visible;
  height: auto;
}


/* Responsive adjustments */
@media (max-width: 768px) {
  .calendar-container {
    height: 70vh;
  }
  
  .vuecal__time-cell,
  .vuecal__cell-content {
    height: 80px !important;
    min-height: 80px !important;
  }
  
  .vuecal__time-cell-line {
    height: 80px !important;
  }
}
</style>
