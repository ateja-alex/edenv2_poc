@include('eden::fiches.include.note_de_frais.options_fil_ariane.js_valider_refuser')

<span v-if="changement_statut_possible()" @click="changement_statut_note_de_frais($event,1)"
	  :title="traduction('interface.note_de_frais.valider')" class="css_action_icon primaire fa fa-fw fa-check"
	  data-placement="left" data-toggle="tooltip">
</span>