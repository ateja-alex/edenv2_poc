<!-- recap ouverture du ticket -->
<div class="css_header_ticket_recap" v-if="suivi_recette_easydev.id != undefined && suivi_recette_easydev.id != '' && suivi_recette_easydev.id != 0">
	<strong style="font-size: 16px;">@{{ suivi_recette_easydev.cree_par_affichage }}</strong><br/>
	<span style="font-size: 13px;">@{{ suivi_recette_easydev.cree_le | datetime_relatif }}</span>
</div>

<div class="row">
	@champ('suivi_recette_easydev', 'titre', '4', '8')	
</div>	
<div class="row">	
	@champ('suivi_recette_easydev', 'description', '12', '12')	
</div>
<div class="row">
	@champ('suivi_recette_easydev', 'url', '12', '12')
</div>
<div class="row" v-if="suivi_recette_easydev.id != undefined && suivi_recette_easydev.id != '' && suivi_recette_easydev.id != 0">
	<div class="col-md-4">@traduction('champs_libres.suivi_recette_easydev.statut.nom')</div>
	<div class="col-md-8">{!! management('suivi_recette_easydev')->champ('statut')->attr('disabled', true)->cree() !!}</div>
</div>	
<div class="row">	
	@champ('suivi_recette_easydev', 'priorite', '4', '8')	
</div>	
<div class="row">	
	@champ('suivi_recette_easydev', 'images', '2', '10')	
</div>	

<input type="hidden" name="statut" v-model="suivi_recette_easydev.statut">

<div v-if="suivi_recette_easydev.statut == 2 || suivi_recette_easydev.statut == 4">
	<div v-if="suivi_recette_easydev.statut == 2" class="alert alert-warning">
		@traduction('formulaire.suivi_recette_easydev.ticket_en_attente_de_validation') 
		<span onclick="vue_instance.suivi_recette_easydev.statut = 3;setTimeout(function(){vue_instance.$refs.formulaire_edition_element.enregistrer();},100);" class="btn btn-default" style="padding: 2px; font-size: 12px;">@traduction('formulaire.suivi_recette_easydev.cloturer_le_ticket')</span> 
		@traduction('formulaire.suivi_recette_easydev.ou') 
		<span  class="btn btn-default" onclick="vue_instance.suivi_recette_easydev.statut = 7;setTimeout(function(){vue_instance.$refs.formulaire_edition_element.enregistrer();},100);" style="padding: 2px; font-size: 12px;">@traduction('formulaire.suivi_recette_easydev.refuser_la_resolution')</span>
	</div>
	<div v-else-if="suivi_recette_easydev.statut == 4" class="alert alert-warning">
		@traduction('formulaire.suivi_recette_easydev.ticket_bloque')
	</div>
</div>
<div v-else-if="suivi_recette_easydev.statut != ''" class="alert alert-success">
	<div v-if="suivi_recette_easydev.statut == 3">
		@traduction('formulaire.suivi_recette_easydev.ticket_cloture')
	</div>
	<div v-else>
		@traduction('formulaire.suivi_recette_easydev.ticket_encours')
	</div>
</div>

@push('donnees_pour_vuejs_watch')

	'suivi_recette_easydev.id': function(){

		var vue_contexte = this;

		if(vue_contexte.suivi_recette_easydev.cree_par != undefined && vue_contexte.suivi_recette_easydev.cree_par != ''){
			$.ajax({

				url: "{{ URL::to('/eden/element/utilisateur/') }}/"+vue_contexte.suivi_recette_easydev.cree_par,
				dataType: "json"
				}).done(function(donnees) {

				vue_contexte.suivi_recette_easydev.cree_par_affichage= donnees.nom+ ' ' +donnees.prenom;

				vue_contexte.$forceUpdate();

			});
		}
	},
@endpush

@push('donnees_pour_vuejs_mounted')

	var vue_contexte = this;

	if(vue_contexte.suivi_recette_easydev.cree_par != undefined && vue_contexte.suivi_recette_easydev.cree_par != ''){
		$.ajax({

			url: "{{ URL::to('/eden/element/utilisateur/') }}/"+vue_contexte.suivi_recette_easydev.cree_par,
			dataType: "json"
			}).done(function(donnees) {

			vue_contexte.suivi_recette_easydev.cree_par_affichage= donnees.nom+ ' ' +donnees.prenom;

			vue_contexte.$forceUpdate();

		});
	}

@endpush