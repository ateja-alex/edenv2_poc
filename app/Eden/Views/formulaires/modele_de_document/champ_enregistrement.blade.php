<div class="row" v-if="modele_de_document.condition_enregistrement_dans_champ != '' && modele_de_document.condition_enregistrement_dans_champ != null">
	<div class="col-sm-2">
		{!! management('modele_de_document')->champ('champ_enregistrement')->nom_vue() !!}
	</div>
	<div class="col-sm-10">
		<select-champs-libres                      
			:champs_libres="champs_piece_jointe"
			:type_element_origine="modele_de_document.type_element_autres"
			:type_element="modele_de_document.type_element_autres"
			:nom_sql="modele_de_document.champ_enregistrement"
			@changement_select_champs_libres="modele_de_document.champ_enregistrement = $event.nom_sql;"

		></select-champs-libres>
		<input type="hidden" v-model="modele_de_document.champ_enregistrement" name="champ_enregistrement">
	</div>
</div>