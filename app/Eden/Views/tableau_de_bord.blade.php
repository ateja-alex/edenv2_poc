@extends('eden::templates.template')

@push('styles')

	@media (max-width: 1023px) {

		ul.liste_onglets {
			flex-direction: column !important
		}
	}

	.css_filtres_listes{
		padding:unset!important;
	}

@endpush

@section('title') Accueil  @endsection


@section('options_fil_ariane')

	<div style="display: flex;gap: 5px;font-size:12px;align-items: center;">

		<span v-if="valeurs_filtres.filter(valeur => valeur.id == 1).length > 0" class="filtres_dates_actifs">
			@traduction('interface.tableau_de_bord.filtres.date')
			<filtre-date class="valeur_filtre_date"
				 :affichage="true"
				 :valeurs="valeurs_filtres.filter(valeur => valeur.id == 1)[0].valeurs"
				 :filtre="filtres.filter(filtre => filtre.id == 1)[0]"
			></filtre-date>
		</span>
		<filtres ref="filtres" :appliquer_recherche_avancee="false"  :valeurs_filtres="valeurs_filtres" :filtres="filtres"></filtres>

		<i class="css_action_icon primaire fa fa-fw fa-sync" @click="actualisation_rapports()" title="Actualisation" data-toggle="tooltip"></i>

		@if(admin() && empty($tableau_de_bord->type))

			<!-- Editer le tableau de bord -->
			<i class="css_action_icon primaire fa fa-fw fa-edit" @click="change_mode_edition()" title="Éditer" data-toggle="tooltip"></i>

			<!-- Ajouter un élément -->
			<i class="css_action_icon primaire fa fa-fw fa-plus" onclick="vue_instance.tableau_de_bord_contenu=JSON.parse(JSON.stringify(vue_instance.tableau_de_bord_contenu_vierge));$('#modal_saisie_tableau_de_bord_contenu').modal('show');" title="Nouvel élément" data-toggle="tooltip"></i>
		@endif
	</div>

@endsection

@section('content')


   	<div class="content-wrapper" >

		<div  id="base-content" class="container-fluid css_conteneur_fiche css_tableau_de_bord">

			{{-- Fil d'arianne --}}
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('nom' => $tableau_de_bord->nom)
			)])

			@php
				if(!empty(moi())){

                    $colonnes_non_acces = [];

                    if (session()->has('cache.droits_profils.divers_non_acces'))
            			$colonnes_non_acces = session()->get('cache.droits_profils.divers_non_acces')['tableau_de_bord'] ?? [];

					$tableaux_de_bord = modele('tableau_de_bord')
						->whereNotIn('tableau_de_bord.id',$colonnes_non_acces)
						->orderBy('ordre')->get();
				}
				elseif(!empty(moi_extranet()))
					$tableaux_de_bord = modele('tableau_de_bord')->where('disponible_extranet',1)->orderBy('ordre')->get();
			@endphp

			<ul class="nav nav-tabs liste_onglets" style="position: relative; top: -7px; margin-top: 70px; border-bottom: 0px solid #dedede;">

				@foreach($tableaux_de_bord as $tableau_de_bord_tmp)
					<li>
						<a  href="{{ route('base_eden.tableau_de_bord.index', [$tableau_de_bord_tmp->id]) }}" class="css_background_couleur_primaire_active @if($tableau_de_bord->id == $tableau_de_bord_tmp->id) active @endif" style="margin-left: 1px;">
							@traduction('{{$tableau_de_bord_tmp->index_traduction}}','nom')
						</a>
					</li>
				@endforeach

			</ul>

			@if(empty($tableau_de_bord->type))
				@include('eden::tableau_de_bord.full_parametrable')
			@else
				@include('eden::tableau_de_bord.liste_de_rapports')
			@endif


		</div>
	</div>

@endsection

@push('modales')
	<template v-if="modale_modification_tableau_de_bord_contenu">
		<transition name="modal">
			<div class="modal-mask">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">@traduction('interface.tableau_de_bord.modal_modification_tableau_de_bord.titre')</h5>
							<button type="button" class="close" @click="modale_modification_tableau_de_bord_contenu = false">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body" style="overflow: unset;">
							<form action="#" method="post" class="css_form" >

								<div class="row" style="align-items: center">
									<div class="col-sm-3">{!! management('tableau_de_bord_contenu')->champ('width')->modele->nom !!}</div>
									<div class="col-sm-8">
										<input type="range" max="12" min="1" class="form-control" v-model="tableau_de_bord_contenu.width" @change="modifie_tableau_de_bord_contenu(true)" name="width" type="number" class=" " name="width" value="" placeholder="" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
									</div>
									<div class="col-sm-1" v-html="tableau_de_bord_contenu.width+' / 12'"></div>
								</div>

								<div class="row">
									<div class="col-sm-3">@traduction('interface.tableau_de_bord.modal_modification_tableau_de_bord.champs.ordre')</div>
									<div class="col-sm-9">
										<input type="number" v-model="tableau_de_bord_contenu.ordre" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
									</div>
								</div>

								<template v-if="tableau_de_bord_contenu.type == 1">
									<div class="row">
										<div class="col-sm-3">
											@traduction('interface.tableau_de_bord.modal_ajout_section.champs.rapport')
										</div>
										<div class="col-md-9">
											<select v-model="tableau_de_bord_contenu.element" disabled>
												@foreach($rapports_disponibles as $categorie)

													<optgroup label="{{$categorie['nom']}}">
														@foreach($categorie['rapports'] as $rapport)
															<option value="{{$rapport->id_rapport}}">{{$rapport->titre}} ({{$rapport->id_rapport}})</option>
														@endforeach
													</optgroup>

												@endforeach
											</select>
										</div>
									</div>

									@include('eden::tableau_de_bord.correspondance_filtres')

								</template>

								<div class="row" v-show="tableau_de_bord_contenu.type == 3 || tableau_de_bord_contenu.type == 2">
									<div class="col-sm-3" v-if="tableau_de_bord_contenu.type == 3">@traduction('interface.tableau_de_bord.modal_modification_tableau_de_bord.champs.titre_section')</div>
									<div class="col-sm-3" v-if="tableau_de_bord_contenu.type == 2">@traduction('interface.tableau_de_bord.modal_modification_tableau_de_bord.champs.titre_du_bloc')</div>
									<div class="col-sm-9">
										<input type="text" v-model="tableau_de_bord_contenu.element">
									</div>
								</div>

								<div class="row" v-show="tableau_de_bord_contenu.type == 3">
									<div class="col-sm-3">@traduction('interface.tableau_de_bord.modal_modification_tableau_de_bord.champs.element')</div>
									<div class="col-sm-9">
										<select v-model="tableau_de_bord_contenu.valeurs">
											@foreach(variable('liste_tables') as $clef => $table)
												<option value="{{$clef}}">{{$table}}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="row" v-show="tableau_de_bord_contenu.type == 4">
									<div class="col-sm-3">@traduction('interface.tableau_de_bord.modal_modification_tableau_de_bord.champs.contenu_html')</div>
									<div class="col-sm-9">
										<textarea v-model="tableau_de_bord_contenu.html"></textarea>
									</div>
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-danger" @click="supprime_tableau_de_bord_contenu(tableau_de_bord_contenu.id)">@traduction('interface.modales.supprimer')</button>
							<button type="button" class="btn btn-secondary" @click="modale_modification_tableau_de_bord_contenu = false" >@traduction('interface.modales.fermer')</button>
							<button type="button" class="btn btn-primary" @click="modifie_tableau_de_bord_contenu()">@traduction('interface.modales.enregistrer')</button>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</template>

	<div class="modal fade" id="modal_ajout_section" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@traduction('interface.tableau_de_bord.modal_ajout_section.titre')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="#" method="post" class="css_form" >

						<div class="row">
							<div class="col-sm-2">
								@traduction('interface.tableau_de_bord.modal_ajout_section.champs.type')
							</div>
							<div class="col-md-10">
								<select v-model="tableau_de_bord_contenu_section.type">
									<option value="3" selected="" v-html="traduction('interface.tableau_de_bord.modal_ajout_section.champs.type.valeurs_select.section')"></option>
									<option value="4" v-html="traduction('interface.tableau_de_bord.modal_ajout_section.champs.type.valeurs_select.indicateur')"></option>
									<option value="7" v-html="traduction('interface.tableau_de_bord.modal_ajout_section.champs.type.valeurs_select.rapport')"></option>
									<option value="5" v-html="traduction('interface.tableau_de_bord.modal_ajout_section.champs.type.valeurs_select.debut_boucle')"></option>
									<option value="6" v-html="traduction('interface.tableau_de_bord.modal_ajout_section.champs.type.valeurs_select.fin_boucle')"></option>
								</select>
							</div>
						</div>

						<div class="row" v-if="tableau_de_bord_contenu_section.type == 3">
							<div class="col-sm-2">
								@traduction('interface.tableau_de_bord.modal_ajout_section.champs.titre_section')
							</div>
							<div class="col-md-10">
								<input type="text" v-model="tableau_de_bord_contenu_section.element">
							</div>
						</div>

						<div class="row" v-if="tableau_de_bord_contenu_section.type == 4">
							<div class="col-sm-2">
								@traduction('interface.tableau_de_bord.modal_ajout_section.champs.indicateur')
							</div>
							<div class="col-md-10">
								<select v-model="tableau_de_bord_contenu_section.element">
									@foreach($indicateurs_disponibles as $indicateur)
										<option value="{!! $indicateur->id_rapport !!}">{!! $indicateur->titre !!} ({!! $indicateur->id_rapport !!})</option>
									@endforeach
								</select>
							</div>

						</div>

						<div class="row" v-if="tableau_de_bord_contenu_section.type == 7">
							<div class="col-sm-2">
								@traduction('interface.tableau_de_bord.modal_ajout_section.champs.rapport')
							</div>
							<div class="col-md-10">
								<select v-model="tableau_de_bord_contenu_section.element">
									@foreach($rapports_disponibles as $categorie)

										<optgroup label="{{$categorie['nom']}}">
											@foreach($categorie['rapports'] as $rapport)
												<option value="{{$rapport->id_rapport}}">{{$rapport->titre}} ({{$rapport->id_rapport}})</option>
											@endforeach
										</optgroup>

									@endforeach
								</select>
							</div>

						</div>
						<div class="row" v-if="tableau_de_bord_contenu_section.type == 4">
							<div class="col-sm-2">
								@traduction('interface.tableau_de_bord.modal_ajout_section.champs.theme')
							</div>
							<div class="col-sm-10">
								<select v-model="tableau_de_bord_contenu_section.theme">
									<option value="1" v-html="traduction('interface.tableau_de_bord.modal_ajout_section.champs.theme.valeurs_select.prioritaire')"></option>
									<option value="2" v-html="traduction('interface.tableau_de_bord.modal_ajout_section.champs.theme.valeurs_select.secondaire')"></option>
								</select>
							</div>
						</div>

						<div class="row" v-if="tableau_de_bord_contenu_section.type == 5">
							<div class="col-sm-2">
								@traduction('interface.tableau_de_bord.modal_ajout_section.champs.boucler_sur')
							</div>
							<div class="col-md-10">
								<select v-model="tableau_de_bord_contenu_section.element">
									<option value="1" v-html="traduction('interface.tableau_de_bord.modal_ajout_section.champs.boucler_sur.valeurs_select.utilisateurs')"></option>
								</select>
							</div>
						</div>
						<div class="row" v-if="tableau_de_bord_contenu_section.type == 5">
							<div class="col-sm-2">
								@traduction('interface.tableau_de_bord.modal_ajout_section.champs.valeur')
							</div>
							<div class="col-sm-10">
								<input type="text" v-model="tableau_de_bord_contenu_section.valeurs">
							</div>
						</div>

					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal" >@traduction('interface.modales.fermer')</button>
					<button type="button" class="btn btn-primary" @click="cree_tableau_de_bord_contenu_section()">@traduction('interface.modales.enregistrer')</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal_saisie_tableau_de_bord_contenu" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@traduction('interface.tableau_de_bord.modal_saisie_contenu_tableau_de_bord.titre')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="#" method="post" class="css_form" >

						<div class="row">
							<div class="col-sm-3">{!! management('tableau_de_bord_contenu')->champ('type')->nom_vue() !!}</div>
							<div class="col-sm-9">{!! management('tableau_de_bord_contenu')->champ('type')->cree() !!}</div>
						</div>

						<template v-if="tableau_de_bord_contenu.type == 1">
							<div class="row">
								<div class="col-sm-3">{!! management('tableau_de_bord_contenu')->champ('element')->nom_vue() !!}</div>
								<div class="col-sm-9">

									<select class="type_" v-model="tableau_de_bord_contenu.element" @change="chargement_champs_libres_rapport(true);">

										@foreach($rapports_disponibles as $categorie)

											<optgroup label="{{$categorie['nom']}}">
												@foreach($categorie['rapports'] as $rapport)
													<option value="{{$rapport->id_rapport}}">{{$rapport->titre}} ({{$rapport->id_rapport}})</option>
												@endforeach
											</optgroup>

										@endforeach

									</select>

								</div>
							</div>

							@include('eden::tableau_de_bord.correspondance_filtres')
						</template>

						<div class="row" v-if="tableau_de_bord_contenu.type == 2">
							<div class="col-sm-3">
								@traduction('interface.tableau_de_bord.modal_saisie_contenu_tableau_de_bord.champs.titre_du_bloc')
							</div>
							<div class="col-sm-9">
								<input type="text" v-model="tableau_de_bord_contenu.element">
							</div>
						</div>

						<div class="row" v-if="tableau_de_bord_contenu.type == 3">
							<div class="col-sm-3">{!! management('tableau_de_bord_contenu')->champ('element')->nom_vue() !!}</div>
							<div class="col-sm-9">

								<select class="type_" v-model="tableau_de_bord_contenu.element">

									<optgroup label="Standard">
										@foreach(glob('../app/Eden/Views/tableau_de_bord/composants/*.blade.php') as $fichier)
											@php
												$pathinfo = pathinfo($fichier);
												$pathinfo['filename'] = str_replace('.blade', '', $pathinfo['filename']);
											@endphp
											<option value="{{$pathinfo['filename']}}">{{ucfirst($pathinfo['filename'])}}</option>
										@endforeach
									</optgroup>

                                    <optgroup label="Spécifique">
										@foreach(glob('../resources/views/vendor/eden/tableau_de_bord/composants/*.blade.php') as $fichier)
											@php
												$pathinfo = pathinfo($fichier);
												$pathinfo['filename'] = str_replace('.blade', '', $pathinfo['filename']);
											@endphp
											<option value="{{$pathinfo['filename']}}">{{ucfirst($pathinfo['filename'])}}</option>
										@endforeach
									</optgroup>

								</select>

							</div>
						</div>

					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal" >@traduction('interface.modales.fermer')</button>
					<button type="button" class="btn btn-primary" @click="cree_tableau_de_bord_contenu()">@traduction('interface.modales.enregistrer')</button>
				</div>
			</div>
		</div>
	</div>

@endpush

@push('donnees_pour_vuejs_data')

	tableau_de_bord_contenu: {!! management('tableau_de_bord_contenu')->modele_par_defaut() !!},
	tableau_de_bord_contenu_vierge: {!! management('tableau_de_bord_contenu')->modele_par_defaut() !!},
	tableau_de_bord_contenu_section: {!! management('tableau_de_bord_contenu')->modele_par_defaut() !!},
	tableau_de_bord_tous_contenus: {!! $contenu !!},
	mode_edition:0,
	requete_mise_a_jour : false,
	filtres: {!! collect($filtres) !!},
	valeurs_filtres: {!! collect($valeurs_filtres) !!},
	modale_modification_tableau_de_bord_contenu : false,
	champs_libres_correspondances: [],

@endpush

@push('donnees_pour_vuejs_mounted')

	this.$on('changement_filtres',(nouvelles_valeurs) => {
		this.$set(this,'valeurs_filtres',nouvelles_valeurs);
		this.actualisation_rapports();
	});
@endpush

@push('donnees_pour_vuejs_methods')

	changement_select_champs_libres : function(valeur,id_filtre){
		if(!this.tableau_de_bord_contenu.correspondances_filtres)
			this.$set(this.tableau_de_bord_contenu,'correspondances_filtres',[]);

		correspondance = this.tableau_de_bord_contenu.correspondances_filtres ?
			this.tableau_de_bord_contenu.correspondances_filtres.
				filter(filtre => filtre.id_filtre_tableau_de_bord == id_filtre) : null;

		if(correspondance && correspondance.length > 0)
			this.tableau_de_bord_contenu.correspondances_filtres.splice(this.tableau_de_bord_contenu.correspondances_filtres.indexOf(correspondance[0]),1);
		else
			this.tableau_de_bord_contenu.correspondances_filtres.push({
				nom_sql_compatible : valeur.nom_sql,
				id_filtre_tableau_de_bord : id_filtre,
			});
	},

	change_mode_edition() {

		this.mode_edition=(vue_instance.mode_edition+1)%2;

		if(this.mode_edition == 1) {

			$("#sortable").sortable({
				helper: "clone",
		        stop:  function (event, ui) {
		            vue_instance.enregistre_ordre_tableau_de_bord();
		        }
			});

			$( "#sortable" ).disableSelection();

		} else {

			$("#sortable").sortable('destroy');

		}
	},

	async cree_tableau_de_bord_contenu() {

		vue_instance.tableau_de_bord_contenu.tableau_de_bord = {{ $tableau_de_bord->id }};
    
        if([1, 2, 3].includes(parseInt(this.tableau_de_bord_contenu.type)) && !this.tableau_de_bord_contenu.element){
            await alerte_eden(this.traduction('messages.js.tableau_de_bord.erreur_enregistrement_element_manquant'));
            return;
        }

		$.ajax({
			method: 'POST',
			url: "{{ route('base_eden.element.creer', ['tableau_de_bord_contenu']) }}",
			dataType: "json",
			data: vue_instance.tableau_de_bord_contenu,
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			document.location=document.location;
		});

	},

	cree_tableau_de_bord_contenu_section() {

		vue_instance.tableau_de_bord_contenu_section.tableau_de_bord = {{ $tableau_de_bord->id }};
		vue_instance.tableau_de_bord_contenu_section.bloc_parent = vue_instance.tableau_de_bord_contenu.id;

		$.ajax({
			method: 'POST',
			url: "{{ route('base_eden.element.creer', ['tableau_de_bord_contenu']) }}",
			dataType: "json",
			data: vue_instance.tableau_de_bord_contenu_section,
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			document.location=document.location;
		});

	},

	modifie_tableau_de_bord_contenu(modification_range = false) {

		var vue_composant = this;

		if(vue_composant.requete_mise_a_jour !== false)
			vue_composant.requete_mise_a_jour.abort();

		element = $('#tdb_element_'+vue_composant.tableau_de_bord_contenu.id);

		element
			.removeClass('col-lg-1 col-lg-2 col-lg-3 col-lg-4 col-lg-5 col-lg-6 col-lg-7 col-lg-8 col-lg-9 col-lg-10 col-lg-11 col-lg-12')
			.addClass('col-lg-'+vue_composant.tableau_de_bord_contenu.width);

		modifications = {width:vue_composant.tableau_de_bord_contenu.width, height:vue_composant.tableau_de_bord_contenu.height,
		element:vue_composant.tableau_de_bord_contenu.element,ordre:vue_composant.tableau_de_bord_contenu.ordre,
		correspondances_filtres : vue_composant.tableau_de_bord_contenu.correspondances_filtres.length == 0 ? 'false' : vue_composant.tableau_de_bord_contenu.correspondances_filtres};

		if(typeof vue_composant.tableau_de_bord_contenu.valeurs != 'undefined') {
			modifications['valeurs'] = vue_composant.tableau_de_bord_contenu.valeurs;
			modifications['html'] = vue_composant.tableau_de_bord_contenu.html;
		}

		vue_composant.requete_mise_a_jour = $.ajax({
			method: 'POST',
			url: "{!! URL::to('eden/element/tableau_de_bord_contenu') !!}" + "/"+vue_composant.tableau_de_bord_contenu.id +"/enregistrer",
			dataType: "json",
			data: modifications,
		}).done(async (donnees) => {

			vue_composant.requete_mise_a_jour = false;

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			{{-- document.location=document.location; --}}

			if(modification_range !== true)
				this.modale_modification_tableau_de_bord_contenu = false;

			if(vue_composant.tableau_de_bord_contenu.type == 1){
				if(vue_composant.tableau_de_bord_contenu.type_rapport == 'liste_libre')
					this.$refs['liste_libre_' + vue_composant.tableau_de_bord_contenu.donnees_liste.id_liste].actualisation_filtres();
				else if(vue_composant.tableau_de_bord_contenu.type_rapport == 'kanban')
					this.actualisation_filtres()
				else
					this.$refs['rapport_' + vue_composant.tableau_de_bord_contenu.element].actualise_rapport();
			}
		});

	},

	enregistre_ordre_tableau_de_bord() {

		var itemOrder = $('#sortable').sortable("toArray");

		modifications = {}

		for (var i = 0; i < itemOrder.length; i++) {
			modifications[i]=itemOrder[i].replace('tdb_element_','');
		}

		$.ajax({
			method: 'POST',
			url: "{!! URL::to('eden/parametrage/tableau_de_bord') !!}" + "/"+this.tableau_de_bord_contenu.id +"/change_ordre",
			dataType: "json",
			data: modifications,
		}).done(async (donnees) => {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			this.modale_modification_tableau_de_bord_contenu = false;
		});

	},

	supprime_tableau_de_bord_contenu(element_id) {

		$.ajax({
			url: "{!! URL::to('eden/element/tableau_de_bord_contenu') !!}" + "/"+this.tableau_de_bord_contenu.id +"/supprimer",
			dataType: "json",
		}).done(async (donnees) => {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			this.modale_modification_tableau_de_bord_contenu = false;
			$('#tdb_element_'+element_id).hide('fast');
		});

	},

	actualisation_rapports(){

		// on enregistre les paramétres du tableau de bord
		$.post({
			url: "/eden/tableau_de_bord/{{$tableau_de_bord->id}}/enregistrer_parametres",
			dataType: "json",
			data: {
				parametres : this.valeurs_filtres
			}
		}).done(() => {
			this.$nextTick(() => {
				for(contenu of Object.values(this.tableau_de_bord_tous_contenus)){
					if(contenu.type == 1){
						if(contenu.type_rapport == 'liste_libre')
							this.$refs['liste_libre_' + contenu.donnees_liste.id_liste].actualisation_filtres();
						else if(contenu.type_rapport == 'kanban')
							this.actualisation_filtres()
						else
							this.$refs['rapport_' + contenu.element].actualise_rapport();
					}
				}
			});

		});

	},

	modification_contenu : function(contenu_id){
		this.modale_modification_tableau_de_bord_contenu=true;
		this.tableau_de_bord_contenu=this.tableau_de_bord_tous_contenus[contenu_id];

		if(this.tableau_de_bord_contenu.type == 1)
			this.chargement_champs_libres_rapport();
	},

	chargement_champs_libres_rapport : function(reinitialise_filtres){

		if(!this.tableau_de_bord_contenu.correspondances_filtres && reinitialise_filtres)
			this.$set(this.tableau_de_bord_contenu,'correspondances_filtres',[]);

		$.post({
			url : 'eden/rapport/'+this.tableau_de_bord_contenu.element,
			dataType : 'json'
		}).done((rapport) => {

			this.$set(this.tableau_de_bord_contenu,'rapport',rapport);

			$.ajax({
				url : 'eden/champs/valeurs/'+rapport.type_element,
				dataType : 'json'
			}).done((champs_libres) => {
				this.champs_libres_correspondances = champs_libres;
			});
		});
	},

	verification_compatibilite : function(compatibilites,champ_libre){

		var compatible = true;

		for(champ in compatibilites){

			if(compatible == false)
				continue;

			if(Array.isArray(compatibilites[champ]))
				compatible = compatibilites[champ].includes(champ_libre[champ]);
			else
				compatible = compatibilites[champ] == champ_libre[champ];
		}

		return compatible;
	},

@endpush

@push('donnees_pour_vuejs_computed')

	correspondances_filtres : function(){

		if(this.tableau_de_bord_contenu.type != 1)
			return [];

		var correspondances_filtres = [];

		for(filtre of this.filtres){

			var correspondance_filtre =
				this.tableau_de_bord_contenu.correspondances_filtres ?
				this.tableau_de_bord_contenu.correspondances_filtres.filter(correspondance_filtre =>
					correspondance_filtre.id_filtre_tableau_de_bord == filtre.id) : null;

			if(correspondance_filtre && correspondance_filtre.length > 0){

				correspondance_filtre = structuredClone(correspondance_filtre[0]);

				correspondance_filtre.filtre = filtre;

				correspondances_filtres.push(correspondance_filtre);
			}
			else{

				correspondances_filtres.push({
					filtre : filtre,
					nom_sql_compatible:null,
					id_filtre_tableau_de_bord: filtre.id,
				});
			}
		}

		return correspondances_filtres;
	},

	filtres_rapports : function(){

		var filtres_rapports = [];

		for(contenu of Object.values(this.tableau_de_bord_tous_contenus).filter(contenu => contenu.type == 1)){

			var filtres = {};

			for(correspondance of contenu.correspondances_filtres){

				var valeurs_filtre = this.valeurs_filtres.filter(filtre => filtre.id == correspondance.id_filtre_tableau_de_bord);

				if(valeurs_filtre.length > 0)
					filtres[correspondance.nom_sql_compatible] = valeurs_filtre[0].valeurs;
			}

			filtres_rapports[contenu.element] = filtres;
		}

		return filtres_rapports;
	},
@endpush
