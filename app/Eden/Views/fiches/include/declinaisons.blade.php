<!-- Emmanuel, 27/10/2021 A priori plus utilisé -->

<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					Déclinaisons
					<span class="css__lien" @click="article_declinaison_creer"><i class="fa fa-fw fa-plus-square"></i> Nouvelle</span>
				</h4>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th>#</th>
								<th>Désignation</th>
								<th>Tarif</th>
							</tr>
						</thead>
						<tbody>
							<tr v-show="articles_declinaisons.length == 0">
								<td colspan="5">Aucune déclinaison pour cet article ! Cliquez sur &laquo; <span class="css__lien" @click="article_declinaison_creer">nouvelle</span> &raquo; ci dessus pour en ajouter une nouvelle</td>
							</tr>
							<tr v-for="article_declinaison in articles_declinaisons">
								<td>
									<span class="btn btn-mini btn-xs btn-default css_btn_action_theme" style="padding: 5px; border: 1px solid white; background: white; color: #6f6f6f !important;">
										<span class="fa fa-search"  @click="article_declinaison_modifier(article_declinaison)" data-toggle="tooltip" title="Afficher"></span>
									</span>
								</td>
								<td>
									@{{ article_declinaison.designation }}<br/>
									<span v-if="article_declinaison.famille_declinaison_1 != null"><br/>@{{ article_declinaison.famille_declinaison_1 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_1 }}</span>
									<span v-if="article_declinaison.famille_declinaison_2 != null"><br/>@{{ article_declinaison.famille_declinaison_2 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_2 }}</span>
									<span v-if="article_declinaison.famille_declinaison_3 != null"><br/>@{{ article_declinaison.famille_declinaison_3 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_3 }}</span>
									<span v-if="article_declinaison.famille_declinaison_4 != null"><br/>@{{ article_declinaison.famille_declinaison_4 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_4 }}</span>
									<span v-if="article_declinaison.famille_declinaison_5 != null"><br/>@{{ article_declinaison.famille_declinaison_5 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_5 }}</span>
								</td>
								<td>@{{ article_declinaison.tarif | montant }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>					

<!-- Modal ajout article_declinaison -->
<div class="modal fade" id="modal_ajout_article_declinaison" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Gestion des déclinaisons de l'article</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				@include('eden::formulaires.include.creation_declinaison')
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" @click="article_declinaison_supprimer" v-show="article_declinaison.id != ''">Supprimer</button>
				<button type="button" class="btn btn-primary" @click="article_declinaison_enregistrer">Enregistrer</button>
			</div>
		</div>
	</div>
</div>	


@push('donnees_pour_vuejs_data')
	articles_declinaisons: {!! $articles_declinaisons !!},
	article_declinaison: {id: '', designation:'', tarif:'', image:''},
@endpush

@push('donnees_pour_vuejs_methods')
	<!-- article_declinaison creer -->
	
	article_declinaison_creer: function() {
		
		$('#modal_ajout_article_declinaison').modal('show');
		
		// on réinitialise le contact
		this.article_declinaison = {id: '', designation:'', tarif:'', image:''};
	},
		
	
	<!-- article_declinaison modif -->	
	
	article_declinaison_modifier: function(article_declinaison) {
		
		vue_instance.article_declinaison = article_declinaison;
		
		$('#modal_ajout_article_declinaison').modal('show');
	},
		
	<!-- article_declinaison enregistrer -->		
	article_declinaison_enregistrer: function() {
		
		// On affiche le loader
		loading();
		
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.fiche.index_post', ['article', $management_element->modele->id, 'enregistrer_article_declinaison'], false) }}",
			dataType: "json",
			method: 'POST',
			data: $('#formulaire_ajout_article_declinaison').serialize()
		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}

			$('#modal_ajout_article_declinaison').modal('hide');
			
			// on actualise la liste
			vue_instance.article_declinaison_actualiser();
		});
		
	},
	
	<!-- article_declinaison supprimer-->	
	
	article_declinaison_supprimer: async function() {

		if(!await confirm_eden())
			return false;

		loading(true);


		// on fait un appel ajax pour supprimer
		$.post({
			
			url: "{{ route('base_eden.fiche.index_post', ['article', $management_element->modele->id, 'supprimer_article_declinaison'], false) }}",
			dataType: "json",
			data: {
				
				id: vue_instance.$data.article_declinaison.id
			}
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				loading(false);

				await erreur(donnees.retour);
				return;
			}
			
			$('#modal_ajout_article_declinaison').modal('hide');

			// on actualise la liste
			vue_instance.article_declinaison_actualiser();
		});
	},
	
	<!--article_declinaison actualiser-->	
	
	article_declinaison_actualiser: function() {
		
		loading(true);
		
		$.get({

			url: 'eden/fiche/article/{{ $management_element->modele->id }}/articles_declinaisons',
			dataType: "json"
		}).done(function(articles_declinaisons) {
			
			// On retire le loader
			loading(false);
			
			vue_instance.articles_declinaisons = articles_declinaisons;
		});
	},	
	
@endpush
