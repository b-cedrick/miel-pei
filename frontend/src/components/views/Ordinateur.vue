

<template>
    <div class="text-center">
       
        <modalAddAttribution :dialog.sync="dialog" :selectedDateM="date" :idPcM="idPc" :selectedTime="time" />

        <v-container>
            <v-card >
            <v-card-title>
                <div class="text-center">
                    {{ordiname}}
                </div>
            </v-card-title>
            <v-card-text v-for="h in horaire" :key="h.heure" >
                <div class="row">
                    <div class="col-2">
                        {{h.heure}}  
                    </div>
                    <div class="col-7">
                        {{h.nom}}
                        {{h.prenom}}
                    </div>
                    <div class="col-3">
                        <v-btn icon color="white" class="primary"
                        @click="passData(true,h.heure,h.idPc)"
                        >
                            <v-icon>
                                mdi-plus
                            </v-icon>
                        </v-btn>
                    </div>
                </div>
            </v-card-text>
        </v-card>
        </v-container>
    </div>  
</template>

<script src="../js/ordinateur.js"> </script>