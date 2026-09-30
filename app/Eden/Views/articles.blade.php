@extends('eden::templates.template')

@section('title') Articles @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('nom' => traduction('interface.articles.titre'))
				)])
			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.articles.titre')
							</h4>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-3">
									<div class="css_conteneur_gauche_ticket">
										<h4 style="background: #b7b7b7;display: block;padding: 8px;text-align: center;">@traduction('interface.articles.titre_categorie_filtre.type_articles')</h4>
										<h5></h5>
										@if($listes['articles_classique'] != false)
											<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre" v-if="$root.recuperer_valeur_liste_formatee(62,0) != null">
												<span @click="change_liste_active_articles('articles_classique',{{$listes['articles_classique']['id_liste']}})" :style="{fontWeight: style_liste_active_articles('articles_classique'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
													<span class="js_filtre_sur_liste">
														<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
													</span>
													<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
														@{{ $root.recuperer_valeur_liste_formatee(62,0).valeur }}
														<span class="badge badge-default">{{ '{{ affiche_nombre_elements_articles('.$listes['articles_classique']['id_liste'].') }'.'}' }}</span>
													</span>
												</span>
											</div>
										@endif
										@if($listes['articles_nomenclature'] != false)
											<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre" v-if="$root.recuperer_valeur_liste_formatee(62,1) != null">
												<span @click="change_liste_active_articles('articles_nomenclature',{{$listes['articles_nomenclature']['id_liste']}})" :style="{fontWeight: style_liste_active_articles('articles_nomenclature'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
													<span class="js_filtre_sur_liste">
														<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
													</span>
													<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
														@{{ $root.recuperer_valeur_liste_formatee(62,1).valeur }}
														<span class="badge badge-default">{{ '{{ affiche_nombre_elements_articles('.$listes['articles_nomenclature']['id_liste'].') }'.'}' }}</span>
													</span>
												</span>
											</div>
										@endif
										@if($listes['articles_fdp'] != false)
											<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre" v-if="$root.recuperer_valeur_liste_formatee(62,2) != null">
												<span @click="change_liste_active_articles('articles_fdp',{{$listes['articles_fdp']['id_liste']}})" :style="{fontWeight: style_liste_active_articles('articles_fdp'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
													<span class="js_filtre_sur_liste">
														<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
													</span>
													<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
														@{{ $root.recuperer_valeur_liste_formatee(62,2).valeur }}
														<span class="badge badge-default">{{ '{{ affiche_nombre_elements_articles('.$listes['articles_fdp']['id_liste'].') }'.'}' }}</span>
													</span>
												</span>
											</div>
										@endif
										@if($listes['articles_assemble'] != false)
											<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre" v-if="$root.recuperer_valeur_liste_formatee(62,3) != null">
												<span @click="change_liste_active_articles('articles_assemble',{{$listes['articles_assemble']['id_liste']}})" :style="{fontWeight: style_liste_active_articles('articles_assemble'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
													<span class="js_filtre_sur_liste">
														<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
													</span>
													<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
														@{{ $root.recuperer_valeur_liste_formatee(62,3).valeur }}
														<span class="badge badge-default">{{ '{{ affiche_nombre_elements_articles('.$listes['articles_assemble']['id_liste'].') }'.'}' }}</span>
													</span>
												</span>
											</div>
										@endif
									</div>
								</div>
								<div class="col-md-9">
									@foreach($listes as $nom_liste => $infos_liste)
										@if($infos_liste !== false)
											<span v-show="liste_active_articles == '{{$nom_liste}}'">
												@include('eden::listes.includes.liste', [

													'type_element' => $infos_liste['type_element'],
													'id_liste' => $infos_liste['id_liste'],
                                                    'modele_par_defaut' => modele_par_defaut($infos_liste['type_element']),
													'options_liste' => $infos_liste['options_liste'],
												])
											</span>
										@endif
									@endforeach
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_methods')

	change_liste_active_articles: function(nom_liste,id_liste) {

		this.liste_active_articles = nom_liste;
		this.$refs['liste_libre_'+id_liste].actualisation_filtres();
	},
	style_liste_active_articles: function(nom_liste) {

		if(this.liste_active_articles == nom_liste)
			return 'bold';

		return '300';
	},
	affiche_nombre_elements_articles: function(id_liste) {

		if(!this.$refs['liste_libre_'+id_liste])
			return '';

		return this.$refs['liste_libre_'+id_liste].liste.nombre_elements.split(' ')[0];
	},
@endpush

@push('donnees_pour_vuejs_data')

	liste_active_articles: 'articles_classique',
@endpush


