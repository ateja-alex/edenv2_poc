<div class="card mb-3 css_bloc_formulaire_fiche">	
	<div class="card-header">	
		<h4 class="css_titre_formulaire_fiche_element" style="width: 100%;">@traduction('module_sur_fiche.suivi_recette_easydev.messages.titre') :</h4>	
	</div>	
	<div class="card-body css_form">	

		<div v-for="echange in suivi_recette_easydev_echanges">	

			<div v-if="echange.message.indexOf('fas fa-flag') > -1" v-html="echange.message"></div>

			<div v-else :style="{ textAlign : echange.super_admin == '{{ moi()->super_admin }}' ? 'right' : 'left' }">	
				<div>	
					<img alt="image" class="rounded-circle" style="max-width: 45px; max-height: 45px;" :style="{ float: echange.super_admin == '{{ moi()->super_admin }}' ? 'right' : 'left', marginRight: echange.super_admin == '{{ moi()->super_admin }}' ? '0px' : '10px', marginLeft: echange.super_admin == '{{ moi()->super_admin }}' ? '10px' : '0px' }" :src="echange.cree_par | affiche_utilisateur_avatar" />	
					<strong v-html="echange.auteur" style="font-size: 16px;"></strong><br/>	
					<span v-html="echange.cree_le" style="font-size: 18px;"></span>	
				</div>	
				<div style="padding:5px;border-radius:3px; max-width: 70%;"	
					 v-bind:class="[ echange.super_admin == '1' ? 'alert-warning' : 'alert-info' ]"	
					 v-bind:style="{ float : echange.super_admin == '{{ moi()->super_admin }}' ? 'right' : 'left' }"	
					>	
					<div v-html="nl2br(echange.message)"></div>	
					<div v-if="echange.piece_jointe && echange.piece_jointe.includes('https://')">
						<br>@traduction('module_sur_fiche.suivi_recette_easydev.messages.piece_jointe') : <a :href="echange.piece_jointe" target="_blank" v-text="echange.piece_jointe"></a>
						<br/>
						<img :src="echange.piece_jointe" style="max-width: 500px;" v-if="(echange.piece_jointe.indexOf('.png') >= 0 || echange.piece_jointe.indexOf('.jpg') >= 0 || echange.piece_jointe.indexOf('.jpeg') >= 0 || echange.piece_jointe.indexOf('.PNG') >= 0 || echange.piece_jointe.indexOf('.JPG') >= 0 || echange.piece_jointe.indexOf('.JPEG') >= 0)" />
					</div>
					<div v-if="echange.piece_jointe && echange.piece_jointe.includes('https://') == false">
						<br>@traduction('module_sur_fiche.suivi_recette_easydev.messages.piece_jointe') : <a :href="'storage/'+echange.piece_jointe" target="_blank" v-text="echange.piece_jointe"></a>
						<br/>
						<img :src="'storage/'+echange.piece_jointe" style="max-width: 500px;" v-if="(echange.piece_jointe.indexOf('.png') >= 0 || echange.piece_jointe.indexOf('.jpg') >= 0 || echange.piece_jointe.indexOf('.jpeg') >= 0 || echange.piece_jointe.indexOf('.PNG') >= 0 || echange.piece_jointe.indexOf('.JPG') >= 0 || echange.piece_jointe.indexOf('.JPEG') >= 0)" />
					</div>
				</div><br style="clear:both">	

			</div>	
			<hr>	
		</div>	

		@traduction('module_sur_fiche.suivi_recette_easydev.messages.repondre') :	

		{!! management('suivi_recette_easydev_echange')->champ('message')->attr('rows', 10)->cree() !!}
		{!! management('suivi_recette_easydev_echange')->champ('url')->cree() !!}
		{!! management('suivi_recette_easydev_echange')->champ('piece_jointe')->cree() !!}

		@if(super_admin())	
		<div class="row">	
			<div class="col-md-2">@traduction('module_sur_fiche.suivi_recette_easydev.messages.ajouter_feuille_de_temps') :</div>	
			<div class="col-md-6"><input type="text" v-model="suivi_recette_easydev_echange.temps_passe" placeholder="{{traduction('module_sur_fiche.suivi_recette_easydev.messages.temps_passe_heures')}}" /></div>	
		</div>		
		@endif	

		<button class="btn btn-primary" @click="repondre">@traduction('module_sur_fiche.suivi_recette_easydev.messages.repondre')</button>
		<p style="color: red" id="erreur_echange"></p>	
		<div id="echange_enregistre" class="alert" style="display: none;"></div>
	</div>	
</div>	

@push('donnees_pour_vuejs_data')	
	suivi_recette_easydev_echanges:{!! $echanges !!},	
	suivi_recette_easydev_echange:{suivi_recette:{{$id_element}} },	
@endpush	

@push('donnees_pour_vuejs_methods')	

 	repondre : function() {	
 		
 		// On démarre le loader
 		loading(true);

 		// On vérifie la pertinence des données
 		// Il n'y a ni message ni PJ, on retourne donc une erreur
		if(typeof vue_instance.suivi_recette_easydev_echange.message == 'undefined' && typeof vue_instance.suivi_recette_easydev_echange.piece_jointe == 'undefined' ){

			// On enlève le loader et affiche le message d'erreur
			loading(false);
			var element = document.getElementById("erreur_echange");
			element.innerHTML = "{{traduction('module_sur_fiche.suivi_recette_easydev.messages.vide')}}";
		}

		// On a au moins une des donnée, on enregistre l'échange
		else{

	 		// On enregistre l'échange
			$.ajax({	

				method: 'POST',	
				dataType: 'json',	
				data: vue_instance.suivi_recette_easydev_echange,	
				url: '{!! route('base_eden.element.creer', ['suivi_recette_easydev_echange']) !!}'
			}).done(function(donnees) {	

				if(donnees.retour) {	

					donnees.element.auteur = "{{ moi()->prenom }} {{ moi()->nom }}";	
					donnees.element.cree_le = vue_instance.formate_date(donnees.element.cree_le) ;	
					donnees.element.super_admin = "{{ moi()->super_admin }}" ;	
					vue_instance.suivi_recette_easydev_echanges.push(donnees.element);	
					vue_instance.suivi_recette_easydev_echange={suivi_recette:{{$id_element}} };	
				}	
			});	

			loading(false);
			var element = document.getElementById("erreur_echange");
			element.innerHTML = "";

			$('#echange_enregistre').html('{{traduction('module_sur_fiche.suivi_recette_easydev.messages.message_ok')}}').addClass('alert-success').show('fast').delay(2500).hide('fast');
		}
	},	

	formate_date : function (date) {	

		var elements_de_la_date = date.split(' ');	
		var elements_de_la_date2 = elements_de_la_date[0].split('-');	

		var date_au_format_francais = ' {{traduction('module_sur_fiche.suivi_recette_easydev.messages.date_le')}} ' + elements_de_la_date2[2]+'/'+elements_de_la_date2[1]+'/'+elements_de_la_date2[0]+' {{traduction('module_sur_fiche.suivi_recette_easydev.messages.date_a')}} '+elements_de_la_date[1];	

		return date_au_format_francais;	
	},	

@endpush 