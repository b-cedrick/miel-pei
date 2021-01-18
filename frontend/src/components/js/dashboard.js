    
import ordinateur from "../views/Ordinateur.vue"
import modalAddPc from "../views/ModalAddPc.vue"
import datePicker from "../views/DatePicker.vue"
import { get } from "lodash"
import axios from 'axios';
import authHeader from '../../services/authHeader';

export default {
    name: 'Profile',
    components:{
        ordinateur,
        modalAddPc,
        datePicker
    },
    data() {
        return {
            listOrdi: [],
            date: new Date().toISOString().substr(0, 10),
            menuDate: false,
        }
    },
    computed: {
      currentUser() {
        return this.$store.state.auth.user;
      }
    },
    created () {
      if (!this.currentUser) {
        this.$router.push('/login');
      }
      this.getListOrdis()
    },
    methods:{
        recupData(event){
            this.listOrdi.push(event)
        },
        getListOrdis(){
            this.listOrdi = []
            axios({
                methods: get,
                url: 'http://localhost:8081/api/auth/ordis',
                params: { date: this.date } , 
                headers: authHeader()
            })
            //   .then(response => (this.info = response))      
            .then(response => {
                this.listOrdi = response.data.data.map(dat => {
                        // return {"id":dat.id, "nom":dat.nom}  
                        console.log("DATAAAAA : ",dat)
                        return dat 
                    })
                    console.log(this.listOrdi.length)
            })
        },
        recupDate(date){
            this.date = date
            this.getListOrdis()
        },
    },
}
