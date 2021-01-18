<template>
  <v-container>
    <v-row>
      <v-col
        cols="4"
        lg="6"
         style="max-width: 290px"
      >
        <v-menu
          v-model="menu1"
          :close-on-content-click="false"
          max-width="290"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-text-field
              :value="computedDateFormattedMomentjs"
              readonly
              v-bind="attrs"
              v-on="on"
              @click:clear="date = null"
            ></v-text-field>
          </template>
          <v-date-picker
            v-model="date"
            @change="menu1 = false"
          ></v-date-picker>
        </v-menu>
      </v-col>
      <v-col
        cols="12"
        lg="6"
      >

      </v-col>
    </v-row>
  </v-container>
</template>

<script src="../js/datePicker.js"> </script>