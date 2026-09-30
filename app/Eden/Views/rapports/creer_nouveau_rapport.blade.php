@extends('eden::templates.template')

@section('title') {{ traduction('rapport.divers.gestion_rapports_parametrables') }} @stop

@section('content')

	<div id="vue_app" >
		<div class="content-wrapper" >
			<div id="base-content" class="container-fluid">

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header">
								<h4>
									@if($duplication)
										@traduction('rapport.divers.dupliquer_rapport') : @{{traduction('rapport.'+rapport.id_rapport_dupliquer+'.titre')}}
									@else
										@traduction('rapport.divers.creer_nouveau_rapport')
									@endif
								</h4>
							</div>
							<div class="card-body">
								<form action="#" method="post" class="css_form" id="formulaire_rapport">
									<input type="hidden" name="id" v-model="rapport.id" />
									@if($duplication)
										<input type="hidden" name="id_rapport_dupliquer" v-model="rapport.id_rapport_dupliquer"/>
									@endif

									<div class="row" v-if="rapport.index_traduction == null">
										<div class="col-sm-2">@traduction('rapport.divers.titre')</div>
										<div class="col-sm-4">
											<input required type="text" name="titre" v-model="rapport.titre" @change="calcul_nom_sql('rapport.id_rapport', 'rapport.titre')"/>
										</div>
										<div class="col-sm-2">@traduction('rapport.divers.id_rapport')</div>
										<div class="col-sm-4">
											<input required type="text" name="id_rapport" v-model="rapport.id_rapport" @change="calcul_nom_sql('rapport.id_rapport')"/>
										</div>
									</div>
									<div class="row" v-else>
										<div class="col-sm-12">
											<traduction-table ref="traduction_table"  categorie="10" :filtrage_index="rapport.index_traduction+'.'"></traduction-table>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-2">@traduction('rapport.divers.type_de_rapport') </div>
										<div class="col-sm-4">
											<select name="type_rapport" v-model="rapport.type_rapport" @if($duplication) disabled="true" @endif>
												<option value="liste_libre">{{ traduction('rapport.divers.liste_libre') }}</option>
												<option value="histogramme">{{ traduction('rapport.divers.histogramme') }}</option>
												<option value="courbe">{{ traduction('rapport.divers.courbe') }}</option>
												<option value="diagramme_circulaire">{{ traduction('rapport.divers.diagramme_circulaire') }}</option>
												<option value="tableau">{{ traduction('rapport.divers.tableau') }}</option>
												<option value="pdf">{{ traduction('rapport.divers.pdf') }}</option>
												<option value="indicateur">{{ traduction('rapport.divers.indicateur') }}</option>
												<option value="graphique_funnel">{{ traduction('rapport.divers.graphique_funnel') }}</option>
												<option value="carte">{{ traduction('rapport.divers.carte') }}</option>
											</select>
										</div>
										<template v-if="rapport.type_rapport === 'liste_libre'">
											<div class="col-sm-2">@traduction('rapport.divers.type_de_liste') </div>
											<div class="col-sm-4">
												<select name="type" v-model="rapport.type">
													<option value="liste_libre">{{ traduction('rapport.divers.liste_libre.type.liste') }}</option>
													<option value="requete_sql">{{ traduction('rapport.divers.liste_libre.type.requete_sql') }}</option>
													<option value="kanban">{{ traduction('rapport.divers.liste_libre.type.kanban') }}</option>
												</select>
											</div>
										</template>
										
									</div>
                                    <div class="row">
										<div class="col-sm-2">@traduction('rapport.divers.categorie')</div>
										<div class="col-sm-4">
											<select name="categorie" v-model="rapport.categorie">
												@foreach($categories as $categorie => $nom)

													<option value="{{$categorie}}">{{ $nom['nom']}}</option>
												@endforeach
											</select>
										</div>
									</div>
                                    <div class="row">
										<div class="col-sm-2">@traduction('rapport.divers.description')</div>
										<div class="col-sm-10">
											<textarea v-model="rapport.description" name="description"></textarea>
										</div>
									</div>
                                    <div class="row">
										<div class="col-sm-2">@traduction('rapport.divers.icone')</div>
										<div class="col-sm-4">
											<select name="icone" v-model="rapport.icone">
												<option value="table">{{ traduction('rapport.divers.table') }}</option>
												<option value="chart-area">{{ traduction('rapport.divers.chart_area') }}</option>
												<option value="chart-pie">{{ traduction('rapport.divers.chart_pie') }}</option>
												<option value="info">{{ traduction('rapport.divers.info') }}</option>
											</select>
										</div>
										<div class="col-sm-2">@traduction('rapport.divers.ordre')</div>
										<div class="col-sm-4"><input required type="text"  v-model="rapport.ordre" name="ordre"  /></div>
									</div>
                                    <div class="row">
                                        <div class="col-sm-2">@traduction('rapport.divers.disponible_extranet')</div>
                                        <div class="col-sm-4">
                                            <select id="extranet" name="extranet" v-model="rapport.extranet">
                                                <option value="0">{{ traduction('rapport.divers.non') }}</option>
                                                <option value="1">{{ traduction('rapport.divers.oui') }}</option>
                                            </select>
                                        </div>
										<div class="col-sm-2">@traduction('rapport.divers.rapport_sur_fiche')</div>
										<div class="col-sm-4">
                                            <select id="extranet" name="extranet" v-model="rapport.rapport_sur_fiche">
                                                <option value="0">{{ traduction('rapport.divers.non') }}</option>
                                                <option value="1">{{ traduction('rapport.divers.oui') }}</option>
                                            </select>
										</div>
									</div>
                                    <div class="row">
										<div class="col-sm-2">@traduction('rapport.divers.type_element_principal')</div>
										<div class="col-sm-4">
											<select @if($duplication) disabled="true" @endif id="type_element" name="type_element" @change="rapport.id_rapport_cible = null" v-model="rapport.type_element">
												<template v-if="rapport.type_rapport == 'carte'">
													<option v-for="type in types_vues_carte" :value="type.type_element ? type.type_element : type.nom_sql" v-html="type.type_element ? type.type_element : type.nom_sql"></option>
												</template>
												<template v-else>
													<option v-for="element in elements" :value="element.type_element" v-html="element.type_element"></option>
												</template>
											</select>
										</div>
                                        <template v-if="rapport.rapport_sur_fiche != 0">
                                            <div class="col-sm-2">
                                                @traduction('rapport.divers.cle_etrangere')
                                            </div>
                                            <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                                <select v-model="rapport.cle_etrangere">
                                                    <option value="id" v-html="traduction('rapport.divers.id_element')"></option>
                                                    <option v-for="champ in champs_selection_element[rapport.type_element]" :value="champ.nom_sql">
                                                        @{{traduction(champ.index_traduction,'nom')}} (@{{champ.nom_sql}})
                                                    </option>
                                                </select>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="row" v-if="rapport.rapport_sur_fiche != 0">
                                        <template v-if="rapport.rapport_sur_fiche != 0">
                                            <div class="col-sm-2">@traduction('rapport.divers.type_element_fiche')</div>
                                            <div class="col-sm-4">
                                                <select id="type_element_fiche" name="type_element_fiche" v-model="rapport.type_element_fiche">
                                                    <option v-for="element in types_elements_fiche" :value="element" v-html="element"></option>
                                                </select>
                                            </div>
                                        </template>
                                        <div class="col-sm-2">
                                            @traduction('rapport.divers.cle_primaire')
                                        </div>
                                        <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                            <select v-model="rapport.cle_primaire">
                                                <option value="id" v-html="traduction('rapport.divers.id_element')"></option>
                                                <option v-for="champ in champs_selection_element[rapport.type_element_fiche]" :value="champ.nom_sql">
                                                    @{{traduction(champ.index_traduction,'nom')}} (@{{champ.nom_sql}})
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row" v-show="rapport.type_rapport == 'indicateur'">
										<div class="col-sm-2">@traduction('rapport.divers.icone_dans_rapport')</div>
                                        <div class="col-sm-4">
                                            <button type="button" class="btn btn-primary iconpicker-component ">
                                                <i class="fas" :class="rapport.icone_dans_rapport"></i>
                                            </button>
                                            <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle menu"
                                                    data-selected="fa-car" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">@traduction('rapport.divers.toggle_dropdown')</span>
                                            </button>
                                            <div class="dropdown-menu"></div>
                                        </div>
                                        <div class="col-sm-2">Détail rapport</div>
                                        <div class="col-sm-4">
                                            <select v-model="rapport.lien_rapport" name="lien_rapport">
                                                <option value="">Sans détail</option>
                                                <option value="liste">Liste principale</option>
                                                <option value="rapport">Rapport personnalisé</option>
                                            </select>
                                        </div>
									</div>
                                    <div class="row" v-show="rapport.type_rapport == 'indicateur' && rapport.lien_rapport == 'rapport'">
										<div class="col-sm-2">@traduction('rapport.divers.rapport_detaille')</div>
										<div class="col-sm-4">
                                            <select v-model="rapport.id_rapport_cible" name="id_rapport_cible">
                                                @foreach($categories as $categorie)

                                                    <optgroup label="{{ $categorie['nom'] }}">
                                                        @foreach($categorie['rapports'] as $rapport)
															@if(empty($rapport->type_rapport) || $rapport->type_rapport == 'liste_libre')
																<option v-if="'{{$rapport->type_element}}' == rapport.type_element" value="{{ $rapport['id_rapport'] }}">{{ traduction($rapport->index_traduction.'.titre') }}</option>
															@endif
														@endforeach
                                                    </optgroup>

                                                @endforeach
                                            </select>
                                        </div>
									</div>

								</form>
								<button type="button" class="btn btn-primary" @click="enregistrer">@traduction('rapport.divers.enregistrer')</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@include('eden::parametrage.include.js_calcul_nom_sql')

@push('donnees_pour_vuejs_data')
	rapport: {!! $rapport_libre !!},
    types_elements_fiche: {!! collect($types_elements_fiche) !!},
    champs_selection_element: {!! collect($champs_selection_element) !!},
	types_vues_carte: {!! collect($types_vues_carte) !!},
	elements: {!! collect($elements) !!},
@endpush

@push('donnees_pour_vuejs_methods')
	
	ajouter_serie: function() {

		var new_key = Object.keys(this.rapport.parametrage_rapport_libre.series).length;
		this.rapport.parametrage_rapport_libre.series[new_key] = ({nom: traduction('rapport.divers.nouvelle_serie')});
		vue_instance.$forceUpdate();
	},

	supprimer_serie: function(index_serie) {
		delete this.rapport.parametrage_rapport_libre.series[index_serie];
		vue_instance.$forceUpdate();
	},

	inverse_valeur_filtre_creation_rapport(modele, id) {

		if(modele.indexOf(id) >= 0) {

			modele.splice(modele.indexOf(id), 1);
		}
		else {

			modele.push(id);
		}

	},

	enregistrer: function() {

		loading(true);
		var vue_contexte = this;

		var rapport = this.rapport;

		rapport.type_formulaire = 'creation';

		// on enregistre les infos du champ libre
		$.post({

			url: 'eden/parametrage/rapport/enregistrer_parametrage',
			dataType: "json",
			data: rapport,
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				loading(false);
				return;
			}

			if(donnees.redirect) {

				document.location = donnees.redirect;
				return;
			}

			if(vue_contexte.rapport.type_rapport == 'liste_libre' && donnees.hasOwnProperty('id')) {

				window.location.href = "{{URL::to('/eden/parametrage/liste_libre')}}"+'/'+donnees.id;
				return;
			}
			if(vue_contexte.rapport.type_rapport == 'carte' && donnees.hasOwnProperty('id_rapport')) {
				window.location.href = "{{URL::to('/eden/parametrage/rapport/parametrer')}}"+'/'+donnees.id_rapport;
				return;
			}
			else {

				window.location.href = "{{URL::to('/eden/rapport')}}"+'/'+donnees.id_rapport;
				return;
			}
		});

	},

@endpush
@push('donnees_pour_vuejs_watch')
		rapport: function () {
			this.$forceUpdate();
		},
@endpush
@push('scripts')
    <script>

		$('.icp-dd').iconpicker({
			//title: 'Dropdown with picker',
			//component:'.btn > i'
		});

		$('.icp').on('iconpickerSelected', function (e) {
			vue_instance.rapport.icone_dans_rapport = e.iconpickerValue;
		});

	</script>
@endpush

