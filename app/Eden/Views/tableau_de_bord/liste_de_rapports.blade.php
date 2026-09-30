<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-body">
				<div class="row">
					<div class="col-md-3">
						<div class="css_conteneur_gauche_ticket">
							@foreach($categories as $categorie)
								<h4 style="background: #b7b7b7;display: block;padding: 8px;text-align: center;">{{ $categorie->nom }}
									<br><br>
									@if(super_admin())
										<span class="css_bouton_modifier_liste_primaire" @click="modifier_tableau_de_bord_categorie({{$categorie}})">
											<i class="fas fa-cog"></i> @traduction('interface.tableau_de_bord.liste_de_rapports.modifier_categorie')
										</span>
									@endif
								</h4>

								@foreach($categorie->rapports as $rapport)
									<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
										<span @click="change_liste_active_tableau_de_bord('{{ $rapport['rapport']->id_rapport }}',{{ $rapport['rapport']->id_liste }})" :style="{fontWeight: style_liste_active_tableau_de_bord('{{ $rapport['rapport']->id_rapport }}')}">
											<span class="js_filtre_sur_liste">
												<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket">
											</span>
											<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
												{{ $rapport['rapport']->titre }}
												<span class="badge badge-default">{{ '{{ affiche_nombre_elements_tableau_de_bord('.$rapport['rapport']->id_liste.') }'.'}' }}</span>
											</span>
										</span>
										@if(super_admin())
											<span class="css_bouton_modifier_liste_primaire" @click="supprimer_tableau_de_bord_rapport('{{$rapport['rapport']->id_rapport}}',{{ $categorie->id }})">
												<i class="fa fa-times"></i>
											</span>
										@endif
									</div>
								@endforeach
								@if(super_admin())
									<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
										<span class="css_bouton_modifier_liste_primaire" @click="cree_tableau_de_bord_rapport({{$categorie->id}})">
											<i class="fas fa-cog"></i> @traduction('interface.tableau_de_bord.liste_de_rapports.ajouter_rapport')
										</span>
									</div>
								@endif


							@endforeach

							@if(super_admin())
								<span class="css_bouton_modifier_liste_primaire" @click="cree_tableau_de_bord_categorie">
									<i class="fas fa-cog"></i> @traduction('interface.tableau_de_bord.liste_de_rapports.creer_categorie')
								</span>
							@endif

						</div>
					</div>
					<div class="col-md-9">

						@foreach($categories as $categorie)
							@foreach($categorie->rapports as $rapport)

								<span v-show="liste_active_tableau_de_bord == '{{$rapport['rapport']->id_rapport}}'">
									@include('eden::listes.includes.liste', [

										'type_element' => $rapport['liste']['type_element'],
										'id_liste' => $rapport['rapport']->id_liste,
										'options_liste' => $rapport['liste']['options_liste'],
									])
								</span>
							@endforeach
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_modification_tableau_de_bord_categorie" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('interface.tableau_de_bord.liste_de_rapports.modifier_tableau_de_bord')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="#" method="post" class="css_form">

					<div class="row">
						@champ('tableau_de_bord_liste_categorie', 'nom', 6, 6)
					</div>
					<div class="row">
						@champ('tableau_de_bord_liste_categorie', 'ordre', 6, 6)
					</div>

				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" @click="supprime_tableau_de_bord_categorie(tableau_de_bord_liste_categorie.id)">@traduction('interface.modales.supprimer')</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal" >@traduction('interface.modales.fermer')</button>
				<button type="button" class="btn btn-primary" @click="enregistre_tableau_de_bord_categorie()">@traduction('interface.modales.enregistrer')</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_modification_tableau_de_bord_rapport" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('interface.tableau_de_bord.liste_de_rapports.modifier_tableau_de_bord')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="#" method="post" class="css_form">

					<div class="row">
						<div class="col-md-6">@traduction('interface.tableau_de_bord.liste_de_rapports.rapport')</div>
						<div class="col-md-6">
							<select v-model="tableau_de_bord_liste_rapport.id_rapport">
								@foreach($rapports_disponibles as $info_categorie)
									<optgroup label="{{ $info_categorie['nom'] }}">

										@foreach($info_categorie['rapports'] as $rapport)
											@if($rapport->liste_libre === true && empty($rapport->kanban))
												<option value="{{ $rapport->id_rapport }}">{{ $rapport->titre }}</option>
											@endif
										@endforeach
									</optgroup>
								@endforeach
							</select>
						</div>
					</div>

				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" @click="supprime_tableau_de_bord_categorie(tableau_de_bord_liste_categorie.id)">@traduction('interface.modales.supprimer')</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal" >@traduction('interface.modales.fermer')</button>
				<button type="button" class="btn btn-primary" @click="enregistre_tableau_de_bord_rapport()">@traduction('interface.modales.enregistrer')</button>
			</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_methods')

	cree_tableau_de_bord_categorie: function() {

		$('#modal_modification_tableau_de_bord_categorie').modal('show');

		this.tableau_de_bord_liste_categorie = {

			tableau_de_bord_id: {{$tableau_de_bord->id}},
			nom: '',
			ordre: '',
		};
	},

	modifier_tableau_de_bord_categorie: function(categorie) {

		$('#modal_modification_tableau_de_bord_categorie').modal('show');

		this.tableau_de_bord_liste_categorie = {

			tableau_de_bord_id: {{$tableau_de_bord->id}},
			nom: categorie.nom,
			ordre: categorie.ordre,
			id: categorie.id,
		};
	},

	modifie_tableau_de_bord_categorie: function() {


	},

	supprime_tableau_de_bord_categorie: function() {

		loading(true);

		$.ajax({
			method: 'POST',
			url: "{{ route('parametrage.tableau_de_bord.categorie.supprimer') }}",
			dataType: "json",
			data: vue_instance.tableau_de_bord_liste_categorie,
		}).done(async function(donnees) {


			if(donnees.retour !== true) {

				loading(false);
				await erreur(donnees.retour);
				return;
			}

			document.location=document.location;
		});
	},

	supprimer_tableau_de_bord_rapport: function(id_rapport,categorie_id) {

		loading(true);

		this.id_rapport = id_rapport;
		this.categorie_id = categorie_id;

		$.ajax({
			method: 'POST',
			url: "{{ route('parametrage.tableau_de_bord.rapport.supprimer') }}",
			dataType: "json",
			data: {
					'id_rapport': this.id_rapport,
					'id_categorie': this.categorie_id,
					'tableau_id': {{$tableau_de_bord->id}},
				},
		}).done(async function(donnees) {


			if(donnees.retour !== true) {

				loading(false);
				await erreur(donnees.retour);
				return;
			}

			document.location=document.location;
		});
	},

	enregistre_tableau_de_bord_categorie: function() {

		loading(true);

		$.ajax({
			method: 'POST',
			url: "{{ route('parametrage.tableau_de_bord.categorie.enregistrer') }}",
			dataType: "json",
			data: vue_instance.tableau_de_bord_liste_categorie,
		}).done(async function(donnees) {


			if(donnees.retour !== true) {

				loading(false);
				await erreur(donnees.retour);
				return;
			}

			document.location=document.location;
		});

	},

	cree_tableau_de_bord_rapport: function(id_categorie) {

		$('#modal_modification_tableau_de_bord_rapport').modal('show');

		this.tableau_de_bord_liste_rapport = {

			tableau_de_bord_id: {{$tableau_de_bord->id}},
			tableau_de_bord_liste_categorie_id: id_categorie,
			id_rapport: '',
		};
	},

	modifie_tableau_de_bord_rapport: function() {


	},

	supprime_tableau_de_bord_rapport: function() {


	},

	enregistre_tableau_de_bord_rapport: function() {

		loading(true);

		$.ajax({
			method: 'POST',
			url: "{{ route('parametrage.tableau_de_bord.rapport.enregistrer') }}",
			dataType: "json",
			data: vue_instance.tableau_de_bord_liste_rapport,
		}).done(async function(donnees) {


			if(donnees.retour !== true) {

				loading(false);
				await erreur(donnees.retour);
				return;
			}

			document.location=document.location;
		});

	},

	change_liste_active_tableau_de_bord: function(nom_liste,id_liste) {

		this.liste_active_tableau_de_bord = nom_liste;
		this.$refs['liste_libre_'+id_liste].actualisation_filtres();
	},
	style_liste_active_tableau_de_bord: function(nom_liste) {

		if(this.liste_active_tableau_de_bord == nom_liste)
			return 'bold';

		return '300';
	},
	affiche_nombre_elements_tableau_de_bord: function(id_liste) {

		if(!this.$refs['liste_libre_'+id_liste])
			return '';

		return this.$refs['liste_libre_'+id_liste].liste.nombre_elements.split(' ')[0];
	},
@endpush

@push('donnees_pour_vuejs_data')

	liste_active_tableau_de_bord: '{{$liste_rapports_rapport_par_defaut}}',
	tableau_de_bord_liste_categorie: {},
	tableau_de_bord_liste_rapport: {},
	id_rapport: 0,
	categorie_id: 0,
@endpush
