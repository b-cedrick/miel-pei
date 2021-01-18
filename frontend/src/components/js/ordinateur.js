
import modalAddAttribution from "../views/ModalAddAttribution.vue"

export default {
    components:{
        modalAddAttribution,
    },
    props: {
        ordiname:{},
        dataAttribution:{},
        selectedDate: {}
        // ordiname: {
        //     default: [function(){
        //         return {}
        //     }]
        // }
    },
    created() {
        this.initialize()
        this.displayHoraire()
    },
    data() {
        return {
            attributions:[],
            horaire:[],
            dialog:false,
            time: "",
            date: "",
            idPc: ""
        }
    },
    methods:{
        initialize(){   
            this.attributions = []
            if(this.dataAttribution){
                this.dataAttribution.attributions.map(dataAttib => {
                    const horaire = dataAttib.horaire
                    this.attributions.push({
                        [horaire]:{
                            "idPc":this.dataAttribution.id,
                            "nom":dataAttib.client.nom,
                            "prenom":dataAttib.client.prenom
                        }     
                    })
                }) 
            }            
        },
        displayHoraire(){

            const arrHoraire = ['8','9','10','11','12','13','14','15','16','17','18']
            if(this.dataAttribution){
                arrHoraire.map((heure, index) => {    
                    let attribution = {
                        "idPc":this.dataAttribution.id,
                        "heure": heure,
                        "nom":"",
                        "prenom":""
                    }          

                    let attribFound = false

                    this.attributions.map( attrib =>{  
                        if(Object.keys(attrib) == heure && !attribFound){
                            attribution = {
                                "idPc":attrib[heure].idPc,
                                "heure": heure,
                                "nom": attrib[heure].nom,
                                "prenom": attrib[heure].prenom
                            }     
                            attribFound = true                   
                        }   
                    })              

                    this.horaire.push(attribution)
                })  
            }        

        },
        passData(dialog,time,idPc){
            this.dialog = dialog
            this.time = time
            this.date = this.selectedDate
            this.idPc = idPc
        }
    }
}

