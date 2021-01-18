import moment from 'moment'

export default {
  props: {
    dateR:{},
  },
  data: () => ({
    // https://github.com/date-fns/date-fns/blob/master/docs/upgradeGuide.md#string-arguments
    date: moment(new Date()).format("YYYY-MM-DD"),
    menu1: false,
    menu2: false,
  }),

  computed: {
    computedDateFormattedMomentjs () {
      this.$emit('dateR',this.date)
      moment.locale('fr')
      const dateToShow = moment(this.date).format('dddd, Do MMMM YYYY')
      return this.date ?  dateToShow.charAt(0).toUpperCase() + dateToShow.slice(1) : ''
    },
  },
}