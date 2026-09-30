<div class="card mb-3 css_bloc_formulaire_fiche">
	<div class="card-header">	
		<h4 class="css_titre_formulaire_fiche_element">@traduction('module_sur_fiche.ticket_client.messages.titre') :</h4>
	</div>	
	<div class="card-body css_form bloc_echanges">

		<div class="bloc_formulaire_echange">
			<div class="formulaire_echange">

				@traduction('module_sur_fiche.ticket_client.messages.repondre') :

				<form id="formulaire_message_ticket_client">

					<input type="hidden" name="auteur_extranet" v-model="ticket_client_echange.auteur_extranet" />
					<input type="hidden" name="suivi_recette" v-model="ticket_client_echange.suivi_recette" />
					<input type="hidden" name="type_message" v-model="ticket_client_echange.type_message" />

					{!! management('ticket_client_echange')->champ('message')->attr('rows', 10)->cree() !!}
					{!! management('ticket_client_echange')->champ('pieces_jointes')->cree() !!}
				</form>
			</div>

			<div class="boutons_echanges_ticket">
				<div class="boutons_repondre_echanges_ticket">
					<button class="btn btn-primary" @click="repondre">
						<span v-if="ticket_client_echange.type_message == 0">
							@if(!empty(moi()))
								@traduction('module_sur_fiche.ticket_client.messages.reponse_externe')
							@else
								@traduction('module_sur_fiche.ticket_client.messages.repondre')
							@endif
						</span>
						@if(!empty(moi()))
							<span v-else-if="ticket_client_echange.type_message == 1"> @traduction('module_sur_fiche.ticket_client.messages.reponse_interne') </span>
						@endif
						<span v-else> @traduction('module_sur_fiche.ticket_client.messages.cloturer') </span>
					</button>
					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						</button>
						<div class="dropdown-menu">
							<a class="dropdown-item" @click="ticket_client_echange.type_message = 0;" v-if="ticket_client_echange.type_message != 0">
								@if(!empty(moi()))
									@traduction('module_sur_fiche.ticket_client.messages.reponse_externe')
								@else
									@traduction('module_sur_fiche.ticket_client.messages.repondre')
								@endif
							</a>
							@if(!empty(moi()))
								<a class="dropdown-item" @click="ticket_client_echange.type_message = 1;" v-if="ticket_client_echange.type_message != 1">@traduction('module_sur_fiche.ticket_client.messages.reponse_interne')</a>
							@endif
							<a class="dropdown-item" @click="ticket_client_echange.type_message = 2;" v-if="ticket_client_echange.type_message != 2">@traduction('module_sur_fiche.ticket_client.messages.cloturer')</a>
						</div>
					</div>
				</div>
				@if(!empty(moi()))
					<div>
						<button class="btn btn-primary btn_marquer_comme_lu" @click="marquer_comme_lu(ticket_client.nouvel_echange == true ? 0 : 1)">
							<span v-if="ticket_client.nouvel_echange == 1">@traduction('module_sur_fiche.ticket_client.messages.marquer_comme_lu')</span>
							<span v-else>@traduction('module_sur_fiche.ticket_client.messages.marquer_comme_non_lu')</span>
						</button>
					</div>
				@endif
			</div>
		</div>


		<p id="erreur_echange"></p>

		<div v-if="loading_echanges" class="loader_echanges_ticket">
			 <img class="img_loader_echanges_ticket" src="<?php echo e('eden/images/ajax_loader.gif'); ?>">
		</div>
		<div v-for="(echange,index_echange) in ticket_client_echanges" :key="echange.id" @if(empty(moi())) v-if="echange.type_message == 0 || echange.type_message == null || echange.type_message == 2" @endif>
			<div :class="'bloc_echange_ticket '+(echange_associe(echange) ? '' : 'echange_autre_utilisateur')">
				<div class="bloc_utilisateur">
					<div class="informations_utilisateur">
						<strong v-html="echange.auteur"></strong>
						<div class="informations_supplementaires_utilisateur">
							<span v-html="echange.cree_le"></span>
							<span v-if="echange.type_message == 1" class="badge badge-warning">
								@traduction('module_sur_fiche.ticket_client.messages.message_interne')
							</span>
						</div>
					</div>
					<img alt="image" class="rounded-circle" :src="(echange.cree_par != 0 ? $options.filters.affiche_utilisateur_avatar(echange.cree_par)  : 'eden/images/no_avatar.jpg')" />
				</div>
				<div class="bloc_texte">
					<div class="ticket_client_message" :style="echanges_deplies.includes(echange.id) ? '' : 'max-height:200px;overflow-y:hidden'" v-if="modification_echange.id != echange.id" ref="echanges" v-html="echange.message"></div>
					<div class="ticket_client_message" v-else>
						{!! management('ticket_client_echange')->champ('message')->vmodel(true,'modification_echange')->cree() !!}
					</div>
					<div class="options_message" v-if="acces_modification(echange) && modification_echange == false">
						<i class="fas fa-pencil-alt" @click="mise_en_place_modification(echange)"></i>
						<i class="fas fa-trash" @click="supprimer_echange(echange)"></i>
					</div>
					<div class="bouton_validation_modification" v-if="modification_echange.id == echange.id">
						<i class="fas fa-check" @click="modifier_echange"></i>
						<i class="fas fa-times" @click="modification_echange = false;"></i>
					</div>
                    <div class="ticket_client_bouton_deplier_message" v-if="$refs.echanges && $refs.echanges[index_echange] && $refs.echanges[index_echange].scrollHeight > 200">
                        <span class="css_pointer bulle_option" @click="echanges_deplies.includes(echange.id) ?
                        	echanges_deplies.splice(echanges_deplies.indexOf(echange.id),1) : echanges_deplies.push(echange.id)">
							<i :class="'fa fa-chevron-'+ (echanges_deplies.includes(echange.id) ? 'up' : 'down')"></i>
						</span>
                    </div>
					<div v-if="echange.pieces_jointes">
						<br/>@traduction('module_sur_fiche.ticket_client.messages.pieces_jointes') :<br/>
						<template v-for="piece_jointe in echange.pieces_jointes">
							<a :href="'storage/'+piece_jointe.chemin" target="_blank" v-text="piece_jointe.nom"></a><br/>
						</template>
					</div>
				</div>
			</div>
		</div>
	</div>	
</div>	

@push('donnees_pour_vuejs_data')	
	ticket_client_echanges: [],
	ticket_client_echange:	{},
	modele_ticket_client_echange: {!! modele_par_defaut('ticket_client_echange') !!},
	loading_echanges : false,
	modification_echange: false,
	echanges_deplies: [],
@endpush

@push('donnees_pour_vuejs_created')

	this.modele_ticket_client_echange.suivi_recette = {{$id_element}};

	@if(!empty(moi_extranet()))
		this.modele_ticket_client_echange.auteur_extranet= "{{ moi_extranet()->prenom }} {{ moi_extranet()->nom }}";
	@endif

	this.ticket_client_echange = structuredClone(this.modele_ticket_client_echange);
@endpush

@push('donnees_pour_vuejs_mounted')

	this.echanges_ticket();
@endpush

@push('donnees_pour_vuejs_methods')

 	repondre : async function() {
 		
 		// On démarre le loader
 		loading(true);

 		// On vérifie la pertinence des données
 		// Il n'y a ni message ni PJ, on retourne donc une erreur
		if(typeof vue_instance.ticket_client_echange.message == 'undefined'){

			// On enlève le loader et affiche le message d'erreur
			loading(false);
			var element = document.getElementById("erreur_echange");
			element.innerHTML = "{{traduction('module_sur_fiche.ticket_client.messages.vide')}}";
			return false;
		}

		// On a au moins une des donnée, on enregistre l'échange

		this.$root.$emit('trigger_enregistre_formulaire_fiche');

		// On enregistre l'échange
		await $.ajax({

				method: 'POST',
				dataType: 'json',
			    data: $("#formulaire_message_ticket_client").serialize(),
				url: '{!! route('base_eden.element.creer', ['ticket_client_echange']) !!}'
			}).done((donnees) => {

			if(donnees.retour) {

				this.ticket_client_echange = structuredClone(this.modele_ticket_client_echange);

				//On rafraîchit les échanges
				this.echanges_ticket();

				if(this.$refs.gestion_pieces_jointes != null)
					this.$refs.gestion_pieces_jointes.actualiser();
			}
		});

		loading(false);
		var element = document.getElementById("erreur_echange");
		element.innerHTML = "";
	},	

	formate_date : function (date) {	

		var elements_de_la_date = date.split(' ');	
		var elements_de_la_date2 = elements_de_la_date[0].split('-');	

		var date_au_format_francais = ' {{traduction('module_sur_fiche.suivi_recette_easydev.messages.date_le')}} ' + elements_de_la_date2[2]+'/'+elements_de_la_date2[1]+'/'+elements_de_la_date2[0]+' {{traduction('module_sur_fiche.suivi_recette_easydev.messages.date_a')}} '+elements_de_la_date[1];	

		return date_au_format_francais;	
	},

	echanges_ticket : function () {

		this.loading_echanges = true;

		//On récupère les échanges
		$.ajax({

			url: "{{ route('ticket_client.echanges', [$id_element]) }}",
		}).done((donnees) => {

			this.ticket_client_echanges = donnees;

			this.loading_echanges = false;

			this.$nextTick(() => this.$forceUpdate());
		});
	},

	echange_associe : function(echange){

		@if(empty(moi()))
			return echange.cree_par == 0 || echange.mail_id > 0;
		@else
			return echange.cree_par == {{ moi()->id }};
		@endif
	},

	acces_modification : function(echange){

		if(this.$root.moi.id > 0)
			return this.$root.moi.type_utilisateur == 2 || ( echange.type_element_createur == 'utilisateur' && echange.element_id_createur == this.$root.moi.id);
		else if(this.$root.moi_extranet.id > 0)
			return (echange.type_element_createur == 'contact' && echange.element_id_createur == this.$root.moi_extranet.contact_selectionne.id);

		return false;
	},

	mise_en_place_modification : function(echange){
		this.modification_echange = structuredClone(echange);
	},

	modifier_echange : function(){

		loading(true);

		$.ajax({
			method: 'POST',
			dataType: 'json',
			data: {
				message : this.modification_echange.message
			},
			url: 'eden/element/ticket_client_echange/'+this.modification_echange.id+'/enregistrer'
		}).done((donnees) => {

			if(donnees.retour) {
				//On rafraîchit les échanges
				this.modification_echange = false;
				this.echanges_ticket();
				loading(false);
			}
		});
	},

	supprimer_echange : async function(echange){

		if(!await confirm_eden("{{ traduction('interface.modales.confirmation_suppression') }}"))
			return false;

		loading(true);

		$.ajax({
			dataType: 'json',
			url: 'eden/element/ticket_client_echange/'+echange.id+'/supprimer'
		}).done((donnees) => {
			if(donnees.retour) {
				this.echanges_ticket();

				if(this.$refs.gestion_pieces_jointes != null)
					this.$refs.gestion_pieces_jointes.actualiser();

				loading(false);
			}
		});
	},

	marquer_comme_lu : function(valeur){

		loading(true);

		$.post({

			url: "{{ URL::to('eden/element/ticket_client') }}/"+this.element_id+"/enregistrer",
			dataType: "json",
			method: "post",
			data: {

				nouvel_echange: valeur,
			}
		}).done(async (donnees) => {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			this.ticket_client = donnees.element;
			toastr.success(this.traduction('messages.js.ticket_client.messages.marquer_comme_lu'));
		});
	},
@endpush