<!-- A priori plus utilisé -->

<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header d-flex align-items-center">
				<h4>
					@traduction('module_sur_fiche.fiche.article_multi_familles.titre')
				</h4>
				<span class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="left" title="{{traduction('module_sur_fiche.fiche.article_multi_familles.nouveau')}}" @click="article_famille_creer">
					<i class="css_action_icon secondaire fa fa-fw fa-plus-square"></i>
				</span>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th>@traduction('module_sur_fiche.fiche.article_multi_familles.ref')</th>
								<th>@traduction('module_sur_fiche.fiche.article_multi_familles.famille')</th>
							</tr>
						</thead>
						<tbody>
							<tr v-show="articles_par_familles.length == 0">
								<td colspan="5">@traduction('module_sur_fiche.fiche.article_multi_familles.aucun_1') <span class="css__lien" @click="article_famille_creer">@traduction('module_sur_fiche.fiche.article_multi_familles.ref')</span> @traduction('module_sur_fiche.fiche.article_multi_familles.ref')</td>
							</tr>
							<tr v-for="article_par_famille in articles_par_familles">
								<td>
									<span class="btn btn-mini btn-xs btn-default" style="padding: 5px; border: 1px solid white; background: white; color: #6f6f6f !important;">
										<span class="fa fa-search" @click="article_par_famille_modifier(article_par_famille)" data-toggle="tooltip" title="{{traduction('module_sur_fiche.fiche.article_multi_familles.afficher')}}"></span>
									</span>
								</td>
								<td>@{{ article_par_famille.famille.nom }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_ajout_article_par_famille" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('module_sur_fiche.fiche.article_multi_familles.titre_modal')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form action="#" id="formulaire_ajout_article_famille" method="post" class="css_form">
					<input type="hidden" name="id" v-model="article_famille.id" />
					<input type="hidden" name="article_id" v-model="article_famille.article_id" />
					<div class="row">
						<div class="col-sm-2">@traduction('module_sur_fiche.fiche.article_multi_familles.famille')</div>
						<div class="col-sm-4">
                        {!! management('article_famille')->champ('famille_id')->cree() !!}
                        </div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" @click="article_famille_supprimer" v-show="article_famille.id != ''">@traduction('module_sur_fiche.fiche.article_multi_familles.supprimer')</button>
				<button type="button" class="btn btn-primary" @click="article_famille_enregistrer">@traduction('module_sur_fiche.fiche.article_multi_familles.enregistrer')</button>
			</div>
		</div>
	</div>
</div>	



@push('donnees_pour_vuejs_data')
	articles_par_familles: {!! $articles_par_familles !!},
    article_famille : {id: '', article_id : '{{ $management_element->modele->id }}' },
@endpush

@push('donnees_pour_vuejs_methods')
	
	article_famille_creer: function() {
		
		$('#modal_ajout_article_par_famille').modal('show');

        this.article_famille = {id: '', famille_id : '', article_id : '{{ $management_element->modele->id }}'}
		
	},
		
	article_par_famille_modifier: function(article_famille) {
		
		vue_instance.article_famille = article_famille;
		
		$('#modal_ajout_article_par_famille').modal('show');
	},
		
	article_famille_enregistrer: function() {
		
		// On affiche le loader
		loading();
		
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.fiche.index_post', ['article', $management_element->modele->id, 'enregistrer_article_par_famille'], false) }}",
			dataType: "json",
			method: 'POST',
			data: $('#formulaire_ajout_article_famille').serialize()
		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}

			$('#modal_ajout_article_par_famille').modal('hide');
			
			// on actualise la liste
			vue_instance.article_famille_acutaliser();
		});
		
	},
	
	article_famille_supprimer: async function() {

		if(!await confirm_eden("{{ traduction('module_sur_fiche.fiche.article_multi_familles.confirmation_suppression') }}"))
			return false;

		loading(true);


		// on fait un appel ajax pour supprimer
		$.post({
			
			url: "{{ route('base_eden.fiche.index_post', ['article', $management_element->modele->id, 'supprimer_article_par_famille'], false) }}",
			dataType: "json",
			data: {
				
				id: vue_instance.$data.article_famille.id
			}
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				loading(false);

				await erreur(donnees.retour);
				return;
			}
			
			$('#modal_ajout_article_par_famille').modal('hide');

			// on actualise la liste
			vue_instance.article_famille_acutaliser();
		});
	},
	
	article_famille_acutaliser: function() {
		
		loading(true);
		
		$.get({

			url: 'eden/fiche/article/{{ $management_element->modele->id }}/articles_par_familles',
			dataType: "json"
		}).done(function(articles_par_familles) {

			//console.log(articles_par_familles)
			
			// On retire le loader
			loading(false);
			
			vue_instance.articles_par_familles = articles_par_familles;
		});
	},	
	
@endpush
