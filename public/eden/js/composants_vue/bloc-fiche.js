Vue.component('bloc-fiche', {
    template: `
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					<slot name="titre"></slot>	
				</h4>
			</div>
			<div class="card-body">

				<slot name="contenu"></slot>
			</div>
		</div>
    `,
	
});



Vue.component('liste', {
    template: `
		<table class="table table-bordered table-hover" :id="id_table" width="100%" cellspacing="0">
			<thead>
				<tr>
					<th v-for="titre in titres">{{ titre }}</th>
				</tr>
			<slot></slot>
			</thead>
			
			<tbody>
				<tr v-for="ligne in lignes_liste_computed">
					<td><span class="css_lien" @click="editer_element(type_element, ligne.id)">editer</span></td>
					<td v-for="(titre, index) in titres" v-show="index != 'options_sur_ligne'">{{ ligne[index] }}</td>
				</tr>
			</tbody>
			
		</table>
    `,
	
	props: {
        titres: {},
        type_element: {},
        id_table: {},
        url: {},
    },
	
	data: function() {
		
		return {
			
			lignes_liste: {}
		};
		
	},
	
	computed: {
		
		lignes_liste_computed: function() {
			
			return this.lignes_liste;
		}
	},
	
	methods: {
		
		actualise: function() {
			
			var instance_vue = this;
			
			$('#'+instance_vue.id_table).addClass('css_actualisation_ajax_en_cours');
			
			var formulaire_filtres = $('#filtres_'+instance_vue.id_table);		

			
			$.post({

				url: this.url,
				dataType: "json",
				method: 'POST',
				data: formulaire_filtres.serialize(),
			}).done(function(donnees) {

				instance_vue.lignes_liste = donnees.lignes;
				
				$('#'+instance_vue.id_table).removeClass('css_actualisation_ajax_en_cours');
			});
		},
		
		editer_element: function(type_element, id_element) {
			
			vue_event.$emit('edition_element_dans_modale', {type_element:type_element, id_element:id_element, liste:this.lignes_liste});
		}
	},
	
	mounted: function() {
		
		var instance = this;
				
		vue_event.$on('actualiser', function(type_element) {
			
			if(instance.type_element != type_element)
				return;
			
			instance.actualise();
		});
			
		this.actualise();
	}
	
	
});

Vue.component('modale-edition-element', {
    template: `
		<div class="modal fade" :id="id_modale" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><slot name="header">{{ header }}</slot></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<slot name="contenu"></slot>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal" v-if="fermer === true">Fermer</button>
						<button type="button" class="btn btn-danger" @click="supprimer_element" v-if="supprimer === true">Supprimer</button>
						<button type="button" class="btn btn-primary" @click="enregistrer_element" v-if="enregistrer === true">Enregistrer</button>
					</div>
				</div>
			</div>
		</div>
    `,
	
	props: {
        type_element: {},
        id_modale: {},
        header: {},
        enregistrer: {default: true},
        fermer: {default: true},
    },
	
	data: function() {
		
		return {
			
			element_actif: false,
		}
	},
	
	computed: {
		
		supprimer: function() {
			
			return this.element_actif;
		}
	},
	
	created: function() {
		
		var instance = this;
		
		vue_event.$on('edition_element_dans_modale', function() {
			
			instance.element_actif = true;
		});
		
		vue_event.$on('creation_element_dans_modale', function() {
			
			instance.element_actif = false;
		});
	},

	methods: {
		
		enregistrer_element: function() {
			
			var formulaire = $(this.$el).find('form');
			var id_element = formulaire.find('input[name=id_element]').val();
			
			if(id_element != '' && id_element != '0')
				var url = "eden/element/"+this.type_element+"/"+id_element+"/enregistrer";
			else
				var url = "eden/element/"+this.type_element+"/creer";
			
			// On afficher le loader
			loading();
			
			var instance = this;
			
			var champ_id_element = formulaire.find('input[name=id_element]').clone();
			
			formulaire.find('input[name=id_element]').remove();
			
			var data = formulaire.serialize();
			
			formulaire.append(champ_id_element);

			// on enregistre les infos du champ libre
			$.post({

				url: url,
				dataType: "json",
				method: 'POST',
				data: data
			}).done(async function(donnees) {
				
				// On retire le loader
				loading(false);
				
				if(donnees.retour !== true) {
				
					await erreur(donnees.retour);
					return;
				}
				
				vue_event.$emit('actualiser', instance.type_element);
				
				$('#'+instance.id_modale).modal('hide');
			});
		},
		
		supprimer_element: function() {
			
			var formulaire = $(this.$el).find('form');
			var id_element = formulaire.find('input[name=id_element]').val();
			
			if(!confirm("Etes vous certain ?"))
				return false;

			$('#liste_elements_old').addClass('css_actualisation_ajax_en_cours');

			var instance = this;

			// on fait un appel ajax pour supprimer
			$.get({

				url: "eden/element/"+instance.type_element+"/"+id_element+"/supprimer",
				dataType: "json",
				method: 'GET'
			}).done(async function(donnees) {

				if(donnees.retour !== true) {

					// $('#liste_elements_old').removeClass('css_actualisation_ajax_en_cours');

					await erreur(donnees.retour);
					return;
				}

				// on actualise la liste
				vue_event.$emit('actualiser', instance.type_element);
				
				// on ferme la mocale
				$('#'+instance.id_modale).modal('hide');
			});
		}
	}
});


