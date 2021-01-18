<template>
    <v-dialog v-model="dialog" max-width="500">
      <template v-slot:activator="{ on, attrs }">
        <v-btn icon color="green" dark v-bind="attrs" v-on="on"></v-btn>
      </template>

      <v-card>
          <v-card-title class="headline font-weight-bold border-bottom border-dark d-flex justify-content-between">
            <h5> Attribuer </h5>
            <v-btn color="grey darken-1" text @click="close"> X </v-btn>
          </v-card-title>

        <v-card-text>
          <v-autocomplete
              v-model="select"
              :loading="loading"
              :items="items"
              :search-input.sync="search"
              cache-items
              class="mx-0"
              flat
              hide-no-data
              hide-details
              label="Client"
              solo-inverted
            ></v-autocomplete>          
        </v-card-text>

        <v-card-actions class="d-flex justify-content-end mt-0 pt-0">
          <v-btn class="text-white" color="grey darken-1" @click="close"> Annuler </v-btn>
          <v-btn class="text-white" color="blue darken-1" @click="addAttribution"> Attribuer </v-btn>

        </v-card-actions>
      </v-card>

    </v-dialog>
</template>
<script src="../js/modalAddAttribution.js"> </script>