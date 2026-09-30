<!-- Emmanuel, 27/10/21 Fichier plus utilisé, je pense -->

<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					<span v-html="traduction('eden_fiche_client_projets', 'Projets')"></span>
					<span class="css__lien" @click="projet_creer"><i class="fa fa-fw fa-plus-square"></i>
						<span v-html="traduction('eden_fiche_client_nouveau_projet', 'Nouveau projet')"></span>
					</span>
					
					<!--
					<span class="js_badge_selectionnables" style="margin-left: 50px;">
						<span class="badge badge-success">Actifs</span>
						<span class="badge badge-success">Clôturés</span>
						<span class="badge badge-success">Archivés</span>
					</span>
					-->
				</h4>
			</div>
			<div class="card-body">

				<div class="row">
					<div class="col-sm-12" v-show="projets.length == 0">
						<span v-html="traduction('eden_fiche_client_text_aucun_projet', 'Aucun projet pour ce client ! Cliquez sur &laquo; nouveau projet &raquo; ci dessus pour en ajouter un nouveau')"></span>
					</div>
					<div class="col-sm-3" v-for="projet in projets">
						<div class="css_fiche_document">
							<i class="fa fa-fw fa-folder-open"></i><br/>
							<span class="css__lien" @click="projet_modifier(projet.id)">@{{ projet.nom }}</span><br/>
							<span>Projet #@{{ projet.id }}<br/></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Modal ajout projet -->
<div class="modal fade" id="modal_ajout_projet" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><span v-html="traduction('eden_fiche_client_liste_projets_titre_modale', 'Gestion des projets')"></span></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<input type="hidden" name="id" v-model="projet.id" />
			
			<div class="modal-body">
				<form action="#" id="formulaire_ajout_projet" method="post" class="css_form">

					<input type="hidden" name="client_id" value="{{ $management_element->modele->id }}" />

					{!! formulaire('projet', '', '', ['forcer_valeur' => ['client_id' => $management_element->modele->id]]) !!}
				
				</form>
			</div>
			<div class="modal-footer">
				<a :href="'{{ URL::to('eden/fiche/projet') }}/'+projet.id+'/afficher'" class="btn btn-default" v-show="projet.id != ''"><span v-html="traduction('eden_fiche_client_liste_projets_afficher', 'Afficher')"></span></a>
				<button type="button" class="btn btn-danger" @click="projet_supprimer" v-show="projet.id != ''"><span v-html="traduction('eden_supprimer', 'Supprimer')"></span></button>
				<button type="button" class="btn btn-primary" @click="projet_enregistrer"><span v-html="traduction('eden_enregistrer', 'Enregistrer')"></span></button>
			</div>
		</div>
	</div>
</div>


@push('donnees_pour_vuejs_data')
	projets: {!! $projets !!},
	projet:  {!! management('projet')->modele_par_defaut() !!},
@endpush

@push('donnees_pour_vuejs_created')

	this.projet.client_id = {!! $management_element->modele->id !!};
	
@endpush

@push('donnees_pour_vuejs_methods')
	<!-- projet creer-->
	
	projet_creer: function() {
		
		$('#modal_ajout_projet').modal('show');
		
		// on réinitialise le contact
		this.projet = {id: ''};
	},
	

	<!-- projet modif-->
		
	projet_modifier: function(id) {
		
		$.each(vue_instance.projets, function(osef, projet) {
			
			if(projet.id == id) {
				
				vue_instance.projet = projet;
			}
		});
		
		$('#modal_ajout_projet').modal('show');
	},

	<!-- projet enregistrer-->	
	
	projet_enregistrer: function() {
		
		// on fait un appel ajax
		
		if($('#modal_ajout_projet input[name=id]').val() != '' && $('#modal_ajout_projet input[name=id]').val() != '0')
			var url = "eden/element/projet/"+$('#modal_ajout_projet input[name=id]').val()+"/enregistrer";
		else
			var url = "eden/element/projet/creer";

		// On affiche le loader
		loading();
		
		// on enregistre les infos du champ libre
		$.post({

			url: url,
			dataType: "json",
			method: 'POST',
			data: $('#formulaire_ajout_projet').serialize()
		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}

			$('#modal_ajout_projet').modal('hide');
			
			vue_instance.projet_actualiser();
		});
		
	},
	
	<!-- projet supprimer-->	
	projet_supprimer: async function() {

		if(!await confirm_eden())
			return false;

		loading(true);


		// on fait un appel ajax pour supprimer
		$.get({

			url: "eden/element/projet/"+vue_instance.$data.projet.id+"/supprimer",
			dataType: "json",
			method: 'GET'
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				loading(false);

				await erreur(donnees.retour);
				return;
			}
			
			$('#modal_ajout_projet').modal('hide');

			// on actualise la liste
			vue_instance.projet_actualiser();
		});
	},
	
	<!-- projet actualiser-->	
	
	projet_actualiser: function() {
		
		loading(true);
		
		$.get({

			url: 'eden/fiche/client/{{ $management_element->modele->id }}/projets',
			dataType: "json"
		}).done(function(projets) {
			
			// On retire le loader
			loading(false);
			
			vue_instance.projets = projets;
		});
	},
	
@endpush

