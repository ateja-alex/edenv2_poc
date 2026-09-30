<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					@traduction('module_sur_fiche.fiche.centres_interet_client.titre')
					{{-- <span class="css__lien" @click="centre_d_interet_creer"><i class="fa fa-fw fa-plus-square"></i> Nouveau contact</span> --}}
				</h4>
			</div>
			<div class="card-body">
				<form id="formulaire_centres_d_interet" action="">
					<div v-for="(centres_d_interet_enfants, nom_centre_d_interet_parent) in centres_d_interet">
						{{-- <div class="col-sm-12" v-show="contacts.length == 0">Aucun contact ! Cliquez sur &laquo; <span class="css__lien" @click="contact_creer">nouveau contact</span> &raquo; ci dessus pour en ajouter un nouveau</div>
						<div class="col-sm-3" v-for="contact in contacts">
							<div class="css_fiche_document">
								<i class="fa fa-fw fa-address-book-o"></i><br/>
								<span class="css__lien" @click="contact_modifier(contact.id)">@{{ contact.prenom }} @{{ contact.nom }}</span><br/>
								<span v-show="contact.telephone != ''">@{{ contact.telephone }}<br/></span>
								<span v-show="contact.adresse_email != ''">@{{ contact.adresse_email }}<br/></span>
								<span class="badge badge-default" v-show="contact.poste != ''">@{{ contact.poste }}</span>
								<span class="badge badge-success" v-show="contact.contact_prioritaire == 1">Contact prioritaire</span>
								<span class="badge badge-danger" v-show="contact.npai == 1">NPAI</span>
							</div>
						</div> --}}
						<div class="row">
							<div class="col-sm-12 mb-2 css_form_ligne_titre">@{{ nom_centre_d_interet_parent }}</div>
						</div>
						<div class="row">
							<div v-for="centre_d_interet in centres_d_interet_enfants" class="col-sm-2">
								{{-- <input v-if="centre_d_interet_checked" type="checkbox" :value="centre_d_interet.id" :checked="centre_d_interet.checked" @click="mettre_a_jour_centres_d_interet">@{{ centre_d_interet.nom }}
								<input v-else type="checkbox" :value="centre_d_interet.id" :checked="centre_d_interet.checked" @click="mettre_a_jour_centres_d_interet"/><span class="ml-1">@{{ centre_d_interet.nom }}</span> --}}
								<input type="checkbox" :name="centre_d_interet.id" v-model="centre_d_interet.checked" @change="mettre_a_jour_centres_d_interet">@{{ centre_d_interet.nom }}
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>



@push('donnees_pour_vuejs_data')
	centres_d_interet: {!! $centres_d_interet !!},
	centre_d_interet: {},
@endpush

@push('donnees_pour_vuejs_methods')
	contact_creer: function() {
		
		$('#modal_ajout_contact').modal('show');
		
		// on réinitialise le contact
		this.contact = {id: ''};
	},
	
	contact_modifier: function(id) {
		
		$.each(vue_instance.contacts, function(osef, contact) {
			
			if(contact.id == id) {
				
				vue_instance.contact = contact;
			}
		});
		
		$('#modal_ajout_contact').modal('show');
	},
	
	mettre_a_jour_centres_d_interet: function() {
		
		// on fait un appel ajax
		
		var url = "eden/fiche/client/"+this.client.id+"/post/mettre_a_jour_centres_d_interet";

		// On affiche le loader
		// loading();
		
		var $this = this;

		// on enregistre les infos du champ libre
		$.post({

			url: url,
			dataType: "json",
			method: 'POST',
			data: $('#formulaire_centres_d_interet').serialize(),
			// data: $this.$data.centres_d_interet,
		}).done(async function(donnees) {
			
			// On retire le loader
			// loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}
			
			// $this.contact_actualiser();
		});
		
	},
	
	contact_supprimer: async function() {

		if(!await confirm_eden("{{ traduction('module_sur_fiche.fiche.centres_interet_client.confirmation_suppression') }}"))
			return false;

		loading(true);


		// on fait un appel ajax pour supprimer
		$.get({

			url: "eden/element/contact/"+vue_instance.$data.contact.id+"/supprimer",
			dataType: "json",
			method: 'GET'
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				loading(false);

				await erreur(donnees.retour);
				return;
			}
			
			$('#modal_ajout_contact').modal('hide');

			// on actualise la liste
			vue_instance.contact_actualiser();
		});
	},
	
	contact_definir_comme_prioritaire: function(valeur) {
		
		loading(true);
		
		var url = "eden/element/contact/"+$('#modal_ajout_contact input[name=id]').val()+"/enregistrer";

		// on enregistre la modification
		$.post({

			url: url,
			dataType: "json",
			method: 'POST',
			data: {
				
				contact_prioritaire: valeur
			}
		}).done(function(donnees) {
			
			// On retire le loader
			loading(false);
			
			$('#modal_ajout_contact').modal('hide');
			
			vue_instance.contact_actualiser();
		});
		
	},
	
	contact_npai: function(valeur) {
		
		loading(true);
		
		var url = "eden/element/contact/"+$('#modal_ajout_contact input[name=id]').val()+"/enregistrer";

		// on enregistre la modification
		$.post({

			url: url,
			dataType: "json",
			method: 'POST',
			data: {
				
				npai: valeur
			}
		}).done(function(donnees) {
			
			// On retire le loader
			loading(false);
			
			$('#modal_ajout_contact').modal('hide');
			
			vue_instance.contact_actualiser();
		});
	},
	
	contact_actualiser: function() {
		
		loading(true);
		
		$.get({

			url: 'eden/fiche/{{ $management_element->_type_element }}/{{ $management_element->modele->id }}/contacts',
			dataType: "json"
		}).done(function(contacts) {
			
			// On retire le loader
			loading(false);
			
			vue_instance.contacts = contacts;
		});
	},
	
@endpush
