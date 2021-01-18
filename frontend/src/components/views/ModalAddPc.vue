<template>
  <v-row class="mx-4">
    <v-dialog
      v-model="dialog"
      persistent
      max-width="300px"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-btn
          color="primary"
          dark
          v-bind="attrs"
          v-on="on"
          class="d-flex justify-start"
        >
          + PC
        </v-btn>
      </template>     

      <v-card>
        <v-card-title class="justify-center">
          <span class="headline text-center">Ajout PC</span>
        </v-card-title> 
        <div  class="p-4">
          <v-form
            ref="form"
            v-model="valid"
            lazy-validation
            class="text-center m-8"
            @submit="validate"
          >
            <v-text-field
              v-model="name"
              :counter="40"
              :rules="nameRules"
              label="Nom"
              required
            ></v-text-field>          
  
            <v-btn
              :disabled="!valid"
              color="secondary"
              class="mr-4"
              type="submit"
              v-if="name.length > 0"
            >
              Ajouter
            </v-btn>
              <v-btn
              color="secondary"
              v-else disabled
            >
              Ajouter
            </v-btn>
          </v-form>      
        </div>      
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script src="../js/modalAddPc.js"> </script>