@include('eden::fiches.include.employe_demande_conge.options_fil_ariane.js_valider_refuser')

<span v-if="changement_statut_possible()"
      @click="changement_statut_demande($event,2)" :title="traduction('interface.employe_demande_conge.refuser')"
      class="css_action_icon primaire fa fa-fw fa-times" data-placement="left" data-toggle="tooltip">
</span>
