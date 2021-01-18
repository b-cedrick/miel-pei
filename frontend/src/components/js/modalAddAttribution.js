
import { post } from "lodash"
import axios from 'axios';
import authHeader from '../../services/authHeader';

export default {
  props: {
    idPcM: {},
    selectedDateM: {},
    selectedTime: {},
    dialog: {}
  },  
  data(){
    return {
      loading: false,
        items: [],
        search: null,
        select: null,
        idClient: "",     
           
    }
  },
  watch: {
    search (val) {
      console.log(val)
      val && val !== this.select && this.querySelections(val)
    },
  },
  methods: {
    close() {
      this.$emit('update:dialog', false)
    },
    addAttribution(){
      alert("Attribution added")
      this.close()
    },
    querySelections (v) {
      this.loading = true
      // Simulated ajax query
      if(v.length >= 3){
        let data = { word: v, headers: authHeader()}
        axios.post('http://localhost:8081/api/auth/clients', data)
        .then(response => {
          setTimeout(() => {
            this.items = response.data.data.map(data => {
              this.idClient = data.idClient
              return data.nom+ " "+ data.prenom
            })
            this.loading = false
          }, 500)
        })

      } 
    },
    formatClient(client){

    }
  }
}