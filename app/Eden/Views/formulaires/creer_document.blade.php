@php
    $traduction_element_pluriel = \Illuminate\Support\Facades\Blade::compileString('@traduction("'.table_libre($management->_type_element)->index_traduction.'","element_pluriel")');
@endphp

@extends('eden::templates.template')

@include('eden::formulaires.include.document.vues_a_surcharger.creer_document_spe')
@include('eden::fiches.include.filtres_et_modele_par_defaut_listes_sur_fiche')

@section('title') {!! ucfirst(traduction(table_libre($management->_type_element)->index_traduction . '.element')) !!} @if(!empty($management->affiche())) {{ $management->affiche() }} @endif @stop


@php
$style_ligne_document = modele('style_ligne_document')->get();

$articles_modifiables = false;

if($management->existe() === false) {

	$articles_modifiables = true;
}
else {


	$articles_modifiables = $management->articles_modifiables();
}


@endphp

@push('styles')

	@foreach($style_ligne_document as $style)

		.style_ligne_document_{{$style->id}} {
			color: {{$style->couleur}};
			font-size: {{$style->taille_police}}px;
			font-weight:{{ ($style->gras) ? 'bold' : ''  }};
			font-style:{{ ($style->italique) ? 'italic' : ''  }}
		}
	@endforeach

	.css_lien_droite_nomenclature{
	color: #707070;
	}

	.css_lien_droite_nomenclature:hover{
	color: var(--background_menus);
	}
@endpush

@section('content')


	<div
		class="
			content-wrapper
			@if(moi_extranet()) document_extranet @endif
		"
	>
	    <div id="base-content" class="container-fluid">

			@section('options_fil_ariane')

				@yield('options_specifiques_client')

			@endsection

			<?php $option_parametrer_element = ''; ?>
			@if(!empty(moi()) && moi()->super_admin == 1 && mode_parametrage() === true)
				<?php $option_parametrer_element = '<a href="'.route('parametrage.table_libre.zoom', ['type_element' => $management->_type_element]).'" class="css_bouton_modifier_liste_primaire">
					<i class="fas fa-cog"></i> Paramétrer "'. table_libre($management->_type_element)->element_pluriel .'"
				</a>'; ?>
			@endif

	    	{{-- Fil d'ariane --}}

			<?php

			$aide_contextuelle = false;

			if(!empty(moi()) && moi()->aide_contextuelle == 1) {

				$aide_contextuelle = $management->aide_contextuelle();
			}
			?>

			@if($management->existe())

				@if($management->est_une_vente())
					@include('eden::includes.fil_ariane', [
						'fil_ariane' => array(
							array('route' => 'base_eden.liste.index', 'nom' => $traduction_element_pluriel, 'arguments' => [$management->_type_element]),
							array('route' => 'base_eden.fiche.index', 'nom' => management('client', $management->modele->client_id)->affiche(), 'arguments' => ['client', $management->modele->client_id]),
							array('nom_vue' => "titre_fil_ariane")
						),
						'aide_contextuelle' => $aide_contextuelle
					])
				@else
					@include('eden::includes.fil_ariane', [
						'fil_ariane' => array(
							array('route' => 'base_eden.liste.index', 'nom' => $traduction_element_pluriel, 'arguments' => [$management->_type_element]),
							array('route' => 'base_eden.fiche.index', 'nom' => management('fournisseur', $management->modele->fournisseur_id)->affiche(), 'arguments' => ['fournisseur', $management->modele->fournisseur_id]),
							array('nom_vue' => "titre_fil_ariane")
						),
						'aide_contextuelle' => $aide_contextuelle
					])

				@endif
			@else

				@include('eden::includes.fil_ariane', [
					'fil_ariane' => array(
						array('route' => 'base_eden.liste.index', 'nom' => $traduction_element_pluriel, 'arguments' => [$management->_type_element]),
						array('nom' => 'Nouveau document'.' '.$option_parametrer_element)
					),
					'aide_contextuelle' => $aide_contextuelle
				])
			@endif

			<!-- le client a été supprimé -->
			@if($management->existe())
				@if($management->modele->inactif == 1)
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger text-center" role="alert">@traduction('messages.php.document_supprime')</div>
					</div>
				</div>
				@endif
			@endif

			<div class="row">
				<?php
				?>
				<div class="col-md-12">
					<div>
						{!! $management->actions_supplementaires_sur_saisie_document() !!}

					</div>
				</div>
			</div>

			@include('eden::formulaires.include.document_entete')


			<form class="css_form" id="formulaire_saisie_document">

				<input type="hidden" name="id" value="{{ optional($management->modele)->id }}" />
				<input type="hidden" name="marge_par_nature" id="marge_par_nature" :value="JSON.stringify(document.marge_par_nature)" />

				@if(!empty($variante))
					<input type="hidden" name="variante_devis_vente_id" value="{{ $variante }}">
				@endif

				@if(!empty($type_element_source))
					<input type="hidden" name="type_element_source" value="{{$type_element_source}}" />
					<input type="hidden" name="id_element_source" value="{{$id_element_source}}" />
				@endif


				<input type="hidden" name="afficher_pdf_apres_enregistrement" id="afficher_pdf_apres_enregistrement" value="0" />

				<input type="hidden" v-model="document.annule" name="annule" id="annule"/>

				@if($management->existe() && $management->_type_element == "commande_vente" && !empty(fonctionnalite('gescom_commande_vente_annulable_non_supprimable')) && $management->modele->annule == 0 && $management->modele->valide == 1)

					<textarea style="display: none;" name="motif_annulation" v-model="document.motif_annulation" id="motif_annulation"></textarea>

				@endif

				@yield('formulaire_entete')

				@include('eden::formulaires.include.document_alerte')

				<div class="row">
					@if(isset($errors) && !empty($errors->all()))
						<br/>
						<br/>
						<div class="col-md-12">
							@foreach($errors->all() as $message)
								<div class="alert alert-danger">{{ $message }}</div>
							@endforeach
						</div>
						<br/>
						<br/>
						<br/>
					@endif

					@if (\Session::has('warning'))
						<br/>
						<br/>
						<div class="col-md-12">

						<div class="alert alert-success">@traduction('messages.php.document_enregistre')
							@if($management->modele->valide != 1)

								: <b><a href="{{ route('document.valider', [$management->_type_element, $management->modele->id]) }}" style="color:white">@traduction('document.actions.valider.valider')</a></b>
							@endif
						</div>

							@foreach(\Session::get('warning') as $message)
								<div class="alert alert-warning">{{ $message }}</div>
							@endforeach
						</div>
						<br/>
						<br/>
						<br/>
					@endif
					@if(session()->get('confirmations') !== null && !empty(session()->get('confirmations')))
						<br/>
						<br/>
						<div class="col-md-12">
							@foreach(session()->get('confirmations') as $message)
								<div class="alert alert-success">{!! $message !!}</div>
							@endforeach
						</div>
						<br/>
						<br/>
						<br/>
					@endif
				</div>

				<!-- nouvelle présentation document, avec onglets -->
				<!-- la structure classique -->

				@foreach($structure['modules'] as $id_module_1 => $module)
					<div class="row">
						@if(!isset($module['taille']))
							@foreach($module as  $id_module_2 => $colonne_sous_module)
								<div class="col-md-{{ $colonne_sous_module['taille'] }}">
									<div class="row css_module_onglets">
										@if(!empty($colonne_sous_module['onglets']))
											<?php temps_execution('debut onglets ', 2); ?>
												<?php
													$onglet_selectionne = false;
													$onglet_selectionne_taille = false;
												?>
												@foreach($colonne_sous_module['modules'] as $sous_module_taille)
													@if($onglet_selectionne_taille === false)
														<?php
															$onglet_selectionne_taille = true;
														?>
														<div class="col-md-{{ $sous_module_taille['taille'] }} js_changement_taille_module">
													@endif
												@endforeach
													<ul class="nav nav-tabs css_pointer liste_onglets" id="onglet_{{$id_module_1}}_{{$id_module_2}}" style="position: relative;padding: 8px 5px 5px 0px; padding-left: 0;border-top-left-radius: 10px;border-top-right-radius: 10px;gap: 21px 2px">
														@foreach($colonne_sous_module['modules'] as $cle_sous_module => $sous_module)
															<?php
															if(!isset($sous_module['cacher_bloc_v_if'])){
																$sous_module['cacher_bloc_v_if'] = true;
															}
															?>
															<template v-if="{{(!$management->existe() && !empty($colonne_sous_module['bouton_suivant']) ? 'avancement_onglet.onglet_'.$id_module_1.'_'.$id_module_2.'.maximum >= '.$cle_sous_module : $sous_module['cacher_bloc_v_if'])}}">
																<li @click="{{'avancement_onglet.onglet_'.$id_module_1.'_'.$id_module_2.'.avancement'}} = {{$cle_sous_module}}">
																	<?php $nom_module=$sous_module['module']; ?>
																	<a :class="'js_onglet_sur_fiche css_background_couleur_primaire_active '+({{'avancement_onglet.onglet_'.$id_module_1.'_'.$id_module_2.'.avancement'}} == {{$cle_sous_module}} ? 'active' : '')">
																		@if(!empty($sous_module['liste']))
																			@traduction('rapport.{{$nom_module}}.titre')
																		@else
																			@traduction('document.blocs.{{$nom_module}}.titre')
																		@endif
																	</a>
																</li>
															</template>
														@endforeach
													</ul>
												</div>
										@endif
										<?php
										$onglet_selectionne = false;
										?>
										@foreach($colonne_sous_module['modules'] as $cle_sous_module => $sous_module)
												<?php
												if(!isset($sous_module['cacher_bloc_v_if'])){
													$sous_module['cacher_bloc_v_if'] = true;
												}
												?>
											<template v-if="{{(!$management->existe() && !empty($colonne_sous_module['bouton_suivant']) ? 'avancement_onglet.onglet_'.$id_module_1.'_'.$id_module_2.'.maximum >= '.$cle_sous_module : $sous_module['cacher_bloc_v_if'])}}">
												<div @if(!empty($colonne_sous_module['onglets'])) v-show="{{'avancement_onglet.onglet_'.$id_module_1.'_'.$id_module_2.'.avancement'}} == {{$cle_sous_module}}" @endif onglet="{{$sous_module['module']}}" class="tab-pane col-md-{{ $sous_module['taille'] }}" liste_onglet="liste_onglets_{{$id_module_1}}_{{$id_module_2}}">
													<div class="row">
														<div class="col-md-12 js_onglet_fiche">
															@if(!empty($sous_module['liste']))
																@include('eden::fiches.include.liste_libre_sur_fiche', $sous_module)
															@elseif(!empty(moi_extranet()) && view()->exists('eden::formulaires.include.document.'.$sous_module['chemin'].'_extranet'))
																@include('eden::formulaires.include.document.'.$sous_module['chemin'].'_extranet',array('onglet' => true))
															@else
																@include('eden::formulaires.include.document.'.$sous_module['chemin'],array('onglet' => true))
															@endif
															@if(!empty($colonne_sous_module['bouton_suivant']))
																<div class="card mb-3" style="border-top:unset;margin-top: -1rem!important;padding: 15px;">
																	<div style="margin-left: auto;">
																		@if($cle_sous_module > 0)
																			<div class="btn btn-primary" @click="onglet_suivant('{{$id_module_1}}_{{$id_module_2}}',{{$cle_sous_module}},true);">@traduction('interface.modales.precedent')</div>
																		@endif
																		@if($cle_sous_module < sizeof($colonne_sous_module['modules']) -1)
																			<div class="btn btn-primary" @click="onglet_suivant('{{$id_module_1}}_{{$id_module_2}}',{{$cle_sous_module}});">@traduction('interface.modales.suivant')</div>
																		@endif
																	</div>
																</div>
															@endif
														</div>
													</div>
												</div>
											</template>
										@endforeach
									</div>
								</div>
							@endforeach
						@else
							<?php
								if(!isset($module['cacher_bloc_v_if'])){
									$module['cacher_bloc_v_if'] = true;
								}
								?>
								<div id="{{$module['module']}}" class="col-md-{{ $module['taille'] }}" v-if="{{ $module['cacher_bloc_v_if'] }}">
									@if(!empty($module['liste']))
										@include('eden::fiches.include.liste_libre_sur_fiche', $module)
									@elseif(!empty(moi_extranet()) && view()->exists('eden::formulaires.include.document.'.$module['chemin'].'_extranet'))
										@include('eden::formulaires.include.document.'.$module['chemin'].'_extranet')
									@else
										@include('eden::formulaires.include.document.'.$module['chemin'])
									@endif
								</div>
						@endif
					</div>
				@endforeach



				@yield('gescom_creation_document_bloc_avant_articles')


				<div id="form_liste_articles" style="display: none;"></div>

			</form>

			<!-- Sticky footer enregistrement -->
			@includeWhen(fonctionnalite('gescom_afficher_bandeau_du_bas'),'eden::formulaires.include.document.bandeau_bas')

		</div>
		<div class="js_footer_limit_sticky"></div>
	</div>

	<template v-if="modale_annulation_partielle">
		<transition name="modal" >
			<div class="modal-mask">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">

						<div class="modal-header">
							<h5 class="modal-title">@traduction('document.blocs.articles.prompt_annulation_partielle')</h5>
						</div>

						<div class="modal-body">
							<p>@traduction('document.blocs.articles.prompt_annulation_partielle.explication')</p>
							{!! management('commande_vente')->champ('motif_annulation')->cree() !!}
						</div>

						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="continuer_enregistrement_document()" data-dismiss="modal">@traduction('interface.modales.enregistrer')</button>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</template>

	@if($management->existe() && $management->_type_element == "commande_vente" && fonctionnalite('gescom_commande_vente_annulable_non_supprimable') && $management->modele->valide == 1)
		<template v-if="modale_verification_annulation_totale">
			<transition name="modal" >
				<div class="modal-mask">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">

							<div class="modal-header">
								<h5 class="modal-title">@traduction('document.modale_verification_annulation_totale.titre')</h5>
							</div>

							<div class="modal-body">
								<h6>@traduction('document.modale_verification_annulation_totale.etes_vous_sur_de_vouloir_annuler')</h6>
							</div>

							<div class="modal-footer">
								<button type="button" style="cursor: pointer;" class="btn btn-secondary" @click="modale_verification_annulation_totale = false" data-dismiss="modal">@traduction('interface.modales.non')</button>
								<a class="btn btn-danger" href="{{ route('document.vente.commande.annulation_totale', [$management->modele->id]) }}" @click="modale_verification_annulation_totale = false" onclick="loading(true);"  data-dismiss="modal">@traduction('interface.modales.oui')</a>
							</div>
						</div>
					</div>
				</div>
			</transition>
		</template>
	@endif

	@if($management->existe())
		<template v-if="modale_verification_suppression_document">
			<transition name="modal" >
				<div class="modal-mask">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">

							<div class="modal-header">
								<h5 class="modal-title">@traduction('document.modale_verification_suppression_document.titre')</h5>
							</div>

							<div class="modal-body">
								<h6>@traduction('document.modale_verification_suppression_document.etes_vous_sur_de_vouloir_supprimer')</h6>
							</div>

							<div class="modal-footer">
								<button type="button" style="cursor: pointer;" class="btn btn-secondary" @click="modale_verification_suppression_document = false" data-dismiss="modal">@traduction('interface.modales.non')</button>
								<a class="btn btn-danger" href="{{ route('document.supprimer', [$management->_type_element, $management->modele->id]) }}" @click="modale_verification_suppression_document = false" onclick="loading(true);"  data-dismiss="modal">@traduction('interface.modales.oui')</a>
							</div>
						</div>
					</div>
				</div>
			</transition>
		</template>
	@endif

	@if(fonctionnalite('utiliser_reglage_marge_par_nature'))
		<template v-if="modale_marge_par_nature">
			<transition name="modal">
				<div class="modal-mask" id="modale_marge_par_nature" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="exampleModalLabel">@traduction('interface.document.marges_par_natures')</h5>
								<button type="button" class="close" @click="modale_marge_par_nature = false" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">

								<div v-if="natures_article_modele.length > 0" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">

									@traduction('interface.document.appliquer_modele_marge') :

									<span :class="'badge ' + (document.nature_article_modele === nature_article_modele.id ? 'badge-success' : 'badge-default')" 
										v-for="nature_article_modele in natures_article_modele" @click="appliquer_modele_de_marge(nature_article_modele.id)" 
										v-text="nature_article_modele.nom">
									</span>
								</div>
								<table class="table table-bordered table-hover css_form">
									<thead>
										<tr>
											<td>@traduction('interface.document.nature')</td>
											<td>@traduction('interface.document.marge_appliquee')</td>
										</tr>
									</thead>
									<tbody>
										<tr v-for="nature in natures_article" :key="nature.id">
											<td v-text="nature.nom"></td>

											<td>
												<input type="number" :name="'marge_par_nature' + nature.id" v-model="document.marge_par_nature[nature.id]"/>
											</td>
										</tr>
									</tbody>

								</table>
							</div>
							<div class="modal-footer">

								<button type="button" class="btn btn-secondary" @click="modale_marge_par_nature = false">@traduction('interface.modales.fermer')</button>
								<button type="submit" class="btn btn-primary" @click="appliquer_marge_par_nature()">@traduction('interface.modales.appliquer')</button>
							</div>

						</div>
					</div>
				</div>
			</transition>
		</template>
	@endif

	<template v-if="modale_articles_supprimes">
		<transition name="modal">
			<div class="modal-mask">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">@traduction('document.modale.articles_supprimes.titre')</h5>
							<button type="button" class="close" @click="modale_articles_supprimes = false" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body" style="height: 100px;" v-html="$root.traduction('document.modale.articles_supprimes.detail', null, [articles_supprimes])">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="modale_articles_supprimes = false">@traduction('interface.listes.fermer')</button>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</template>

@endsection

@if(fonctionnalite('saisie_documents_devise_etrangere'))
	@include('eden::formulaires.include.document.includes.devise_etrangere')
@endif

@if($management->est_une_vente())
	@include('eden::formulaires.include.document.base_client')
@else
	@include('eden::formulaires.include.document.base_fournisseur')
@endif

@push('donnees_pour_vuejs_data')

	nom_type_element: '{!! ucfirst(table_libre($management->_type_element)->element) !!}',
	type_element: '{!! $management->_type_element !!}',
	avancement_onglet : {
		@foreach($structure['modules'] as $id_module_1 => $module)
			@if(!isset($module['taille']))
				@foreach($module as  $id_module_2 => $colonne_sous_module)
					@if(!empty($colonne_sous_module['onglets']))
						{{'onglet_'.$id_module_1.'_'.$id_module_2}} : {
							avancement : 0,
							maximum : 0,
						},
					@endif
				@endforeach
			@endif
		@endforeach
	},
	@if($management->existe())
	id_element: '{!! $management->modele->id !!}',
	element_id: '{!! $management->modele->id !!}',
	@endif
	articles_preselection: [],

	@if(!$management->management_fiche()->presence_module('projet'))
		projet: {},
	@endif

	@if(fonctionnalite('gescom_preselection_articles_sur_document') === true)
		catalogue: {},
		sous_famille_selectionnee: 0,
	@endif

	@if ( session()->has('inputs') )
		document : {!! collect(session('inputs')) !!},
	@else

		@if(!empty($management->modele))
			document: {!! $management->modele !!},
		@else
			document: {!! $management->modele_par_defaut() !!},

		@endif
	@endif

	// dernière date connue du document, pour les calculs de délais (expiration, de règlement)
	@if(!empty($management->modele))
		derniere_date_document: '{{ formate_date('d/m/Y', $management->modele->date) }}',
	@else
		derniere_date_document: '{{ date('d/m/Y') }}',
	@endif

	// dernière modalité de paiement, pour les calculs de date de règlement
	@if(in_array($management->_type_element, array('bl_achat')))

		derniere_modalite_paiement_id: null,
	@else

		@if(!empty($management->modele) && !empty($management->modele->modalite_paiement_id))
			derniere_modalite_paiement_id: {{ $management->modele->modalite_paiement_id }},
		@else
			derniere_modalite_paiement_id: null,
		@endif
	@endif

	@if(isset($management->modele->projet_id) && $management->modele->projet_id != "")
       projet_id_initial: {!! $management->modele->projet_id !!},
   	@else
       projet_id_initial: 0,
   	@endif
	@if(!empty(moi_extranet()))
		disable_champs_extranet : true,
	@endif
	modale_annulation_partielle: false,
	modale_verification_suppression_document: false,
	@if($management->existe() && $management->_type_element == "commande_vente" && fonctionnalite('gescom_commande_vente_annulable_non_supprimable') && $management->modele->valide == 1)
		modale_verification_annulation_totale: false,
	@endif
	eco_contribution_active : false,
	modale_articles_supprimes : {{ !empty($articles_supprimes) ? 'true' : 'false' }},
	articles_supprimes : "{{ isset($articles_supprimes) ? implode(',', $articles_supprimes) : '' }}",
	duplication : {{ empty($duplication) ? 'false' : 'true' }},
	tags_pour_liste : {!! collect(!empty($management->modele->id) ? explode('<br/>',$management->tags_pour_liste($management->modele)) : []) !!},
	option_parametrer_element : `{!! $option_parametrer_element !!}`,
	natures_article : {!! modele('nature_article')->orderBy('nom')->get() !!},
	natures_article_modele : {!! modele('nature_article_modele')->orderBy('nom')->get() !!},
	natures_article_modele_marge : {!! modele('nature_article_modele_marge')->get()->groupBy('nature_article_modele_id') !!},
	modale_marge_par_nature : false,
@endpush

@push('donnees_pour_vuejs_watch')

{{--	On utilise un clone de la data document pour pouvoir récupérer la nouvelle et l'ancienne valeur dans le handler, sans cela les deux paramètres sont les mêmes--}}
	clone_document: {
		handler: async function(nouvelles_valeurs_document, anciennes_valeurs_document) {

			// Mise à jour des informations concernant le client ou le fournisseur
			@if(strpos($management->_type_element, 'vente') !== false)

				@if($management->management_fiche()->presence_module('projet'))
					{{-- @if(View::exists('eden::formulaires.include.document.actions.projet')) --}}

						if(this.derniere_info_recuperee_projet != this.document.projet_id) {

							await this.recupere_info_projet();

							this.derniere_info_recuperee_projet = this.document.projet_id;
						}

					{{-- @endif --}}
				@endif

				if(this.document.type_facture != undefined && this.document.type_facture == 2) {

					this.articles_du_document.forEach(function(article, osef){

						if(article.type_ligne == undefined){

							article.avancement_actuel = 100;

						}

					});

					this.mise_a_jour_total_document_vue();

				}

				if(anciennes_valeurs_document.type_facture != 1 && nouvelles_valeurs_document.type_facture == 1) {

					this.articles_du_document.forEach(function(article, osef){

						if(article.type_ligne == undefined){

							article.avancement_actuel = article.avancement_precedent;

						}

					});

					this.mise_a_jour_total_document_vue();

				}

				if(this.derniere_info_recuperee_client != this.document.client_id) {

					this.document.adresse_de_facturation = null;
					this.document.adresse_de_livraison = null;

					this.recupere_info_client(true);

					this.derniere_info_recuperee_client = this.document.client_id;

				}

			@else
				if(this.derniere_info_recuperee_fournisseur != this.document.fournisseur_id) {
					this.recupere_info_fournisseur(this.document.fournisseur_id);

					this.derniere_info_recuperee_fournisseur = this.document.fournisseur_id;
				}

				if(this.derniere_info_recuperee_projet != this.document.projet_id) {

					await this.recupere_info_projet();

					this.derniere_info_recuperee_projet = this.document.projet_id;
				}
			@endif




			// mise à jour des informations concernant le projet
			/*
			if(this.document.projet_id != undefined) {

				if(this.document.projet_id != this.projet_id_initial) {

					this.document.client_id = ""

					$.ajax({
						url: "{{ URL::to('eden/element') }}/projet/"+this.document.projet_id,
						dataType: "json"
					})
					.done(function(donnee) {

						vue_instance.document.client_id = donnee.client_id
						vue_instance.projet_id_initial = donnee.id;
					});
				}
			}
			*/

			@if(in_array($management->_type_element, array('facture_vente', 'acompte_vente', 'avoir_vente', 'facture_achat', 'acompte_achat', 'avoir_achat')))

				this.recalcule_date_de_reglement();
			@endif

			// pour les surcharges
			@stack('watch_document_specifique')


		},
		deep: true
	},


    // mise à jour de la date de règlement (facture_vente) & expiration (devis_vente)
    @if($management->_type_element == 'devis_vente')
        "clone_document.date" : function() {
            if(this.derniere_date_document != this.document.date) {
                $.post({
                    url: "{{ route('document.vente.devis.calcule_date_expiration') }}",
                    data: {
                        date: vue_instance.document.date,
                    },
                    dataType: "json"
                }).done(function(dates) {
                    vue_instance.document.date_expiration = dates.fr;
                    vue_instance.derniere_date_document = vue_instance.document.date;
                });
            }
        },
    @endif
	@if(fonctionnalite('utiliser_reglage_marge_par_nature'))
		"document.nature_article_modele": function(nouvelle_valeur) {

			if(this.modale_marge_par_nature)
				return;
			
			this.appliquer_modele_de_marge(nouvelle_valeur);
			this.appliquer_marge_par_nature();
		},
	@endif
@endpush

@push('donnees_pour_vuejs_mounted')

	this.articles_du_document.forEach((article, osef) => {

		if(article.type_ligne == undefined)
			this.arrondi_prix_article_depuis_fonctionnalite(article);
	});
	
	this.$nextTick(() => {
		if(this.duplication){
			@if(fonctionnalite('mise_a_jour_tarif_duplication') === 'article_tarif')
				this.document_mise_a_jour();
			@elseif(fonctionnalite('mise_a_jour_tarif_duplication') === 'tarif')
				this.document_mise_a_jour(true);
			@endif
		}
	});

	if(this.document.marge_par_nature)
		this.document.marge_par_nature = JSON.parse(this.document.marge_par_nature);
	else
		this.document.marge_par_nature = [];
@endpush

@push('donnees_pour_vuejs_created')

	var instance_vue = this;

	@if(fonctionnalite('gescom_preselection_articles_sur_document') === true)

		$.get('{{ route('document.recupere_catalogue_articles') }}')
			.done(function(catalogue) {

			instance_vue.catalogue = catalogue
		});
	@endif

	@if($management->est_une_vente())
		if(instance_vue.client && instance_vue.client.adresses_facturation.length == 1){

			instance_vue.document.adresse_de_facturation = instance_vue.client.adresses_facturation[0].id;
		};
	@endif

	@if(!$management->existe())
		this.recalcule_date_de_reglement();
	@endif

@endpush


@push('donnees_pour_vuejs_methods')

	arrondi_prix_article_depuis_fonctionnalite : function(article){

		var vue_contexte = this;

		article.tarif = vue_contexte.arrondi_nombre_depuis_fonctionnalite(article.tarif)
		article.prix_achat = vue_contexte.arrondi_nombre_depuis_fonctionnalite(article.prix_achat)

		if(article.modele != undefined && (article.modele.type_article == 1 || article.modele.type_article == 3) && article.nomenclature != undefined){

			article.nomenclature.forEach(function(article_nomenclature, osef){

				vue_contexte.arrondi_prix_article_depuis_fonctionnalite(article_nomenclature);

			});

		}

	},

	arrondi_nombre_depuis_fonctionnalite: function(nombre){

		return nombre == null ? 0 :parseFloat(nombre).toFixed({{ fonctionnalite('arrondi_sur_les_documents') }});

	},

	appliquer_modele_de_marge: function(id_modele) {

		if(this.natures_article_modele_marge[id_modele] == undefined)
			return;

		this.$set(this.document, 'nature_article_modele', id_modele);

		this.natures_article_modele_marge[id_modele].forEach((marge_par_nature_pour_modele) => {
			this.$set(this.document.marge_par_nature, marge_par_nature_pour_modele.nature_article_id, marge_par_nature_pour_modele.marge);
		});

	},

	appliquer_marge_par_nature: function(articles = []) {

		if(articles.length == 0)
			articles = this.articles_du_document;

		articles.forEach((article) => {

			if(!article.modele)
				return;

			var marge_demandee = 0;

			if(article.modele.nature_id){

				marge_demandee = this.document.marge_par_nature[article.modele.nature_id];

				article.marge_brute_pourcentage = marge_demandee;
				this.mise_a_jour_marge_brute_pourcentage(article);
			}

			if(article.nomenclature && article.nomenclature.length > 0)
				this.appliquer_marge_par_nature_pour_nomenclature(article.nomenclature, article);
		});

		this.modale_marge_par_nature = false;
		info("Marges appliquées avec succès");
	},

	appliquer_marge_par_nature_pour_nomenclature: function(nomenclature, article_originel) {

		nomenclature.forEach((article) => {

			if(!article.modele)
				return;

			var marge_demandee = 0;

			if(article.modele.type_article == 1 || article.modele.type_article == 3) {

				this.appliquer_marge_par_nature_pour_nomenclature(article.nomenclature, article_originel);
				return;
			}

			if(article.modele.nature_id) {
				
				marge_demandee = this.document.marge_par_nature[article.modele.nature_id];

				article.marge_brute_pourcentage = marge_demandee;
				this.mise_a_jour_marge_brute_pourcentage(article);
			}

		});
	},

	recalcule_date_de_reglement: function() {

		var fonctionnalite = "{{fonctionnalite('gescom_calcul_date_de_reglement_automatique')}}" == 1 ? true : false;

		if(fonctionnalite === false)
			return;

		if(this.derniere_date_document != this.document.date || this.derniere_modalite_paiement_id != this.document.modalite_paiement_id) {

			var tmp = JSON.parse(JSON.stringify(this.document));

			this.derniere_date_document = tmp.date;
			this.derniere_modalite_paiement_id = tmp.modalite_paiement_id;

			var date = this.document.date;
			var modalite_paiement_id = this.document.modalite_paiement_id;

			var vue_instance = this;

			$.post({

				url: "{{ route('document.calcule_date_reglement') }}",
				data: {

					date_facturation: date,
					modalite_paiement_id: modalite_paiement_id,
				},
				dataType: "json"
			}).done(function(dates) {

				// vue_instance.derniere_date_document = vue_instance.document.date;
				// vue_instance.derniere_modalite_paiement_id = vue_instance.document.modalite_paiement_id;

				// on renseigne la nouvelle date
				vue_instance.document.date_de_reglement = dates.fr;
				// $('input[name=date_de_reglement]').val(dates.fr).change();
				// $('input[name=date_de_reglement]').datepicker('setDate', dates.fr);
			});
		}
	},

	utilisation_devise_etrangere(uniquement_fonctionnalite = false,champ = false){

		var fonctionnalite = "{{fonctionnalite('saisie_documents_devise_etrangere')}}" == 1 ? true : false;

		if(uniquement_fonctionnalite)
			return fonctionnalite === true;

		var griser_prix = "{{fonctionnalite('documents_devise_etrangere_griser_prix_converti')}}" == 1 ? true : false;

        if(champ !== false && (champ != '{{fonctionnalite('documents_devise_etrangere_champ_conversion')}}' || griser_prix === false))
            return false;

        if(this.devise_euro_id === undefined)
            return false;

		if(this.document.devise == 0 || this.document.devise == null || this.document.devise == undefined)
			this.document.devise = this.devise_euro_id;

        return fonctionnalite === true && parseInt(this.document.devise) !== parseInt(this.devise_euro_id);
    },

	affiche_pays: function(pays_id){

		var vue_instance = this;

		// On récupère la liste formatée des pays
		var liste_pays = vue_instance.valeurs_listes_formatees[28];

		for(var index_pays in liste_pays){

			if(liste_pays[index_pays].id_valeur == pays_id)
				return liste_pays[index_pays].valeur;

		}

		return "";
	},

	verification_suppression_document: function(){

		this.modale_verification_suppression_document = true;

	},


	affiche_pays: function(pays_id){

		var vue_instance = this;

		// On récupère la liste formatée des pays
		var liste_pays = vue_instance.valeurs_listes_formatees[28];

		for(var index_pays in liste_pays){

			if(liste_pays[index_pays].id_valeur == pays_id)
				return liste_pays[index_pays].valeur;

		}

		return "";
	},

	document_mise_a_jour : function(tarif_uniquement = false) {

		loading(true);

		this.chargement_tableau_article = true;

		var donnees = {

			articles : this.articles_du_document_informations_necessaires(),
			type_element : '{{ $management->_type_element }}',
			parametres : {
				date_document: this.document.date,
				catalogue_groupement_id: this.document.catalogue_groupement_id,
				@if($management->est_une_vente())
					client_id: this.document.client_id,
				@else
					fournisseur_id: this.document.fournisseur_id,
				@endif
			}
		}

		$.ajax({

			url: "{{ route('document.mise_a_jour_tarif') }}/"+tarif_uniquement,
			dataType: 'json',
			method: "post",
			contentType: "application/json",
			data: JSON.stringify(donnees)
		}).done((retour) => {

			for(index_article in retour){

				var article_du_document = this.articles_du_document[index_article];

				for(attribut in retour[index_article]){

					if(attribut == 'composition') {

					    if(tarif_uniquement)
					        this.gestion_composition_tarif_uniquement(article_du_document,retour[index_article].composition);
                        else{
                            var valeurs = this.gestion_composition(retour[index_article][attribut]);
                            article_du_document.nomenclature = valeurs[0];
                            article_du_document.tarif = valeurs[1];
                            article_du_document.prix_achat = valeurs[2];
					    }
					}
					else
						article_du_document[attribut] = retour[index_article][attribut];
				}

				this.maj_infos_bloc_articles_fournisseur(article_du_document);
			}

			if(this.eco_contribution_active)
				this.recalcule_eco_contribution(true);

			this.chargement_tableau_article = false;
			this.mise_a_jour_total_document_vue();
			loading(false);
		});
	},

	gestion_composition : function(compositions){

		var nomenclatures = [];
		var tarif_total = 0;
		var prix_achat_total = 0;

		for(composition of compositions){

			if(composition.nomenclature != undefined && composition.nomenclature != "undefined" && composition.nomenclature != null && composition.nomenclature.length != 0)
				var nomenclature = composition.nomenclature;
			else
				var nomenclature = [];

			if(composition.conditionnement == null)
				composition.conditionnement = 0;

			var article_dans_nomenclature = {

				code_article: composition.code_article,
				designation: composition.designation,
				quantite: composition.quantite,
				tarif: vue_instance.arrondi_nombre_depuis_fonctionnalite(composition.tarif),
				prix_achat: vue_instance.arrondi_nombre_depuis_fonctionnalite(composition.prix_achat),
				article_id: composition.article_enfant_id,
				stock: composition.stock,
				unite: composition.unite,
				conditionnement_possible: composition.conditionnement_possible,
				conditionnement: composition.conditionnement,
				type_article: composition.type_article,
				afficher_nomenclature: false,
				nomenclature: nomenclature,
				calculateur: composition.calculateur,
				modele_de_calculateur_id: composition.modele_de_calculateur_id,
				modele: composition.modele
			};

			if(vue_instance.eco_contribution_active){
				article_dans_nomenclature.categorie_eco_contribution_id = composition.categorie_eco_contribution_id;
				article_dans_nomenclature.tarif_eco_contribution = composition.tarif_eco_contribution;
				article_dans_nomenclature.application_eco_contribution = composition.application_eco_contribution;
				article_dans_nomenclature.quantite_unite_eco_contribution = composition.quantite_unite_eco_contribution;
			}

			@if($management->est_une_vente())
				if(composition.tarif_force != null && composition.tarif_force != 0) {
					article_dans_nomenclature.tarif = vue_instance.arrondi_nombre_depuis_fonctionnalite(composition.tarif_force);
					article_dans_nomenclature.tarif_force = vue_instance.arrondi_nombre_depuis_fonctionnalite(composition.tarif_force);
					article_dans_nomenclature.tarif_initial = vue_instance.arrondi_nombre_depuis_fonctionnalite(composition.tarif_force);
					article_dans_nomenclature.affichage_tarif_force = true;
				}

				if(composition.prix_achat_force != null && composition.prix_achat_force != 0) {
					article_dans_nomenclature.prix_achat = vue_instance.arrondi_nombre_depuis_fonctionnalite(composition.prix_achat_force);
					article_dans_nomenclature.prix_achat_force = vue_instance.arrondi_nombre_depuis_fonctionnalite(composition.prix_achat_force);
				}
			@endif

			nomenclatures.push(article_dans_nomenclature);

			tarif_total += composition.quantite * composition.tarif;
			prix_achat_total += composition.quantite * composition.prix_achat;
		}

		var tarif = Math.round(tarif_total * 100) / 100;
		var prix_achat = Math.round(prix_achat_total * 100) / 100;

		return [nomenclatures,tarif,prix_achat];
	},

	gestion_composition_tarif_uniquement : function(article,compositions_nouveaux_tarifs){

		var tarif_total = 0;
		var prix_achat_total = 0;

		for(index_nomenclature in article.nomenclature){

			var nomenclature = article.nomenclature[index_nomenclature];

			if(Array.isArray(nomenclature.nomenclature) && nomenclature.nomenclature.length > 0)
				this.gestion_composition_tarif_uniquement(nomenclature,compositions_nouveaux_tarifs[index_nomenclature].composition);
			else{
				nomenclature.tarif = compositions_nouveaux_tarifs[index_nomenclature].tarif;
				nomenclature.prix_achat = compositions_nouveaux_tarifs[index_nomenclature].prix_achat;
			}

			tarif_total += nomenclature.quantite * nomenclature.tarif;
			prix_achat_total += nomenclature.quantite * nomenclature.prix_achat;
		}

		article.tarif = Math.round(tarif_total * 100) / 100;
		article.prix_achat = Math.round(prix_achat_total * 100) / 100;
	},

	mise_a_jour_tags : function(){
		$.ajax({
			url:'eden/document/'+this.type_element+'/'+this.document.id+'/mise_a_jour_tags',
		}).done((donnees) => {
			this.tags_pour_liste = donnees.tags_pour_liste;
			this.document.solde_document_ttc = donnees.solde_document_ttc;
			this.document.regle = donnees.regle;
			this.transformations_possibles = donnees.transformations_possibles;
			this.modeles_de_relances = donnees.modeles_de_relances;
		});
	},

	operations_document_post_modification : function(donnees){
		
		if(donnees.modele.marge_par_nature)
			donnees.modele.marge_par_nature = JSON.parse(donnees.modele.marge_par_nature);

		_.extend(this.document, donnees.modele);

		if(donnees.recurrence != null)
			_.extend(this.recurrence, donnees.recurrence);

		this.articles_du_document = donnees.articles;
		this.documents_lies = donnees.documents_lies;

		if(donnees.documents_par_recurrence != undefined)
			this.documents_par_recurrence = donnees.documents_par_recurrence;
		this.mise_a_jour_total_document_vue();
		this.mise_a_jour_tags();

		if(this.$refs.historique)
			this.$refs.historique.charge_donnees();

		var refs_liste = Object.keys(this.$refs).filter((ref) => ref.includes('liste_libre_'));

		for(ref of refs_liste){

			this.$refs[ref].actualisation_filtres();
		}
	},

	/**
	 *
	 * Changement d'onglet sur le flux de création du document
	 *
	 */
	onglet_suivant : function(indicateur,onglet_actuel,precedent = false) {

		if(precedent === true)
			this.avancement_onglet['onglet_'+indicateur].avancement = onglet_actuel - 1;
		else {
			if(this.avancement_onglet['onglet_'+indicateur].maximum <  onglet_actuel + 1)
				this.avancement_onglet['onglet_'+indicateur].maximum = onglet_actuel + 1;

			this.avancement_onglet['onglet_' + indicateur].avancement = onglet_actuel + 1;

		}
	},

	// @todo recoder avec la nouvelle version de la saisie des articles
	ajoute_articles_au_document : function() {

		@if(fonctionnalite('gescom_mode_preselection_articles_sur_document') == 'affichage_total')

			//@todo redévelopper cette partie la
		@else

			// on recupère les ids des articles de la préselection, et on les ajoute
			var articles_preselection = this.articles_preselection;

			articles_preselection.forEach((article_id) => {

				this.ajoute_article_au_document_vue(article_id, 1, true);
			})

		@endif

		// on switch vers la page article standard
		$('#preselection_des_articles').fadeOut(200, function() {

			$('.js_choisissez_vos_articles').hide();
			$('.js_options_sur_document').show();

			$('#articles_selectionnes').fadeIn(200);
		})
	},

	/**
	 *
	 * On vérifie si le document contient un article avec une TVA à zéro, et on prévient l'utilisateur (si config activé)
	 *
	 */
	enregistre_document_avec_verification : async function() {

		@if(fonctionnalite('alerte_tva_0_sur_document') === true && !in_array($management->_type_element, array('bl_vente', 'bl_achat')))

			var tva_a_zero = false;

			this.articles_du_document.forEach(function(article) {

				if(article.type_ligne == undefined && article.tva == 0)
					tva_a_zero = true;
			})

			if(tva_a_zero == true) {

				if(!await confirm_eden(this.traduction('document.alerte.tva_a_0'))) {
					return;
				}
			}

		@endif

		@if(fonctionnalite('alerte_document_prix_achat_nul') && (fonctionnalite($management->_type_element.'_prix_d_achat_et_marge') === true || fonctionnalite($management->_type_element.'_prix_d_achat_et_marge_pourcentage') === true))

			var prix_achat_0 = [];

			this.articles_du_document.forEach(function(article) {

				if(article.type_ligne)
					return;

				if (article.prix_achat == undefined || article.prix_achat == 0)
					prix_achat_0.push(article.designation);

			})

			if(prix_achat_0.length > 0) {

				if(!await confirm_eden(this.traduction('document.alerte.pa_a_0') + prix_achat_0 + '<br>' + this.traduction('document.alerte.voulez_vous_enregistrer'))) {
					return;
				}
			}


		@endif

		@if(fonctionnalite('gescom_autoriser_document_negatif') == "avertissement")
			if(this.totaux.ht < 0){

				if(!await confirm_eden(this.traduction('document.alerte.total_negatif'))){

					return;

				}

			}
		@endif

		@if(!empty(fonctionnalite('gescom_verification_prix_vente_inferieur_prix_achat')[$management->_type_element]))

				var articles_inferieur = [];

				this.articles_du_document.forEach(function(article) {

					if(article.type_ligne == undefined && article.total < article.prix_achat)
						articles_inferieur.push(article);

				})

				if(articles_inferieur.length > 0){

					var chaine_erreur = articles_inferieur.map((article) => { return article.designation}).join(', ');

					if(!await confirm_eden(this.traduction('document.alerte.indication_articles_prix_vente_inferieur_prix_achat', null, [chaine_erreur])))
						return;
				}

		@endif

		if(this.colonnes_articles.numero_de_serie){

			var numero_de_serie_invalide = false;

			var stock_indisponible = '';

			var numeros_de_serie_deja_utilises = this.numeros_de_serie_deja_utilises;

			var numeros_de_serie = [];

			var duplication_numero_de_serie = false;

			this.articles_du_document.forEach(function(article) {

				if(article.modele && article.stock - article.quantite < 0 && article.modele.type_numero_de_serie > 0){

					stock_indisponible += "Stock indisponible pour l'article : "+article.designation +" \n";
				}

				if(article.modele && article.modele.type_numero_de_serie > 0 ){

					if(article.numero_de_serie == null || article.numero_de_serie == "") {

						numero_de_serie_invalide = true;

					}

					if(article.modele && article.modele.type_numero_de_serie == 1 && numeros_de_serie_deja_utilises.includes(article.numero_de_serie)){

						duplication_numero_de_serie = "Le numéro de série "+article.numero_de_serie+" est déjà utilisé sur un document de ce type";

					}

					numeros_de_serie.push(article.numero_de_serie);
				}

			});

			this.articles_du_document.forEach(function(article) {

				if(article.modele && article.modele.type_numero_de_serie == 1){

					var count = 0;
					numeros_de_serie.forEach((v) => (v === article.numero_de_serie && count++));

					if(count >= 2) {
						duplication_numero_de_serie = "Le numéro de série " + article.numero_de_serie + " est utilisé plusieurs fois sur ce document";
					}

				}

			});

			if(stock_indisponible !== '') {

				await alerte_eden(stock_indisponible, '{{ traduction('interface.alerte.attention') }}');
				return;
			}
			if(numero_de_serie_invalide){

				toastr.error(this.traduction('messages.js.documents.numeros_serie_non_remplis'));
				return;
			}
			if(duplication_numero_de_serie){

				await alerte_eden(duplication_numero_de_serie, '{{ traduction('interface.alerte.attention') }}');
				return;
			}
		}

		//Si on est sur une commande ou un bl achat, qu'on ne livre pas chez le client et qu'un entrepôt est défini,
		// on vérifie si l'adresse de livraison correspond à l'adresse interne sélectionnée sur l'entrepôt
		if((this.type_element == 'commande_achat' || this.type_element == 'bl_achat') && this.document.a_livrer_chez_client != 1 &&
			this.document.entrepot_id != null && this.document.entrepot_id != 0 &&
			this.document.adresse_de_livraison != null && this.document.adresse_de_livraison != 0){

			var entrepot = null;

			await $.ajax({

				url: "/eden/element/entrepot/" + this.document.entrepot_id,
			}).done((donnees) => {

				entrepot = donnees;
			});

			if(entrepot.adresse_interne != null && entrepot.adresse_interne != this.document.adresse_de_livraison){

				if(!await confirm_eden(this.traduction('document.alerte.adresse_divergente')))
					return;
			}
		}

		//On vérifie s'il n'y a pas de quantité à 0 dans une composition ou une nomenclature pour prévenir l'utilisateur
		@if(fonctionnalite('gescom_validation_quantite_article_nulle'))
			var articles_nomenclature_a_0 = {};

			this.articles_du_document.forEach((article) => {

				if(article.type_ligne != undefined)
					return;

				if(parseFloat(article.quantite) <= 0)
					articles_nomenclature_a_0[article.designation] = article.designation;

				if(article.modele !== undefined && (article.modele.type_article === 1 || article.modele.type_article === 3)){

					var articles_a_ajouter = this.verification_quantites_nomenclature(article);

					if(Object.values(articles_a_ajouter).length > 0)
						articles_nomenclature_a_0[article.designation] = articles_a_ajouter;
				}
			});

			if(Object.values(articles_nomenclature_a_0).length > 0){

				var affichage_articles_a_0 = this.affiche_articles_quantite_a_0(articles_nomenclature_a_0);

				if(!await confirm_eden(this.traduction('messages.js.documents.alerte_quantite_nulle_nomenclature', null, ['<br>' + affichage_articles_a_0]))){

					loading(false);
					return false;
				}
			}
		@endif
		//Cette fonction est située dans un autre fichier pour pouvoir la surcharger, voir l'include ligne 1342
		var confirm_pre_enregistrement = await this.confirm_pre_enregistrement();
		var retour_verifications_spe = this.verifications_specifiques_pre_enregistrement();

		if(confirm_pre_enregistrement !== true)
			return;

		if(retour_verifications_spe !== true) {

			await alerte_eden(retour_verifications_spe, '{{ traduction('interface.alerte.attention') }}');
			loading(false);
			return false;
		}


		await this.enregistre_document();
	},

	/*
	*
	* Vérifie s'il n'y a pas des articles contenus dans des nomenclatures qui ont une quantité nulle et retourne ces articles
	* dans un tableau.
	*
	*/
	verification_quantites_nomenclature : function(article){

		var articles_a_0 = {};

		if(article.nomenclature) {

			article.nomenclature.forEach((article_nomenclature, index) => {

				if (article_nomenclature.type_article === 1 || article_nomenclature.type_article === 3) {

					var articles_a_ajouter = this.verification_quantites_nomenclature(article_nomenclature);

					if (Object.values(articles_a_ajouter).length > 0)
						articles_a_0[article_nomenclature.designation] = articles_a_ajouter;
				}

				if (parseFloat(article_nomenclature.quantite) <= 0)
					articles_a_0[index] = article_nomenclature.designation;
			});
		}

		return articles_a_0;
	},

	/*
	*
	* Génère la chaîne de caractères pour affiche les articles qui ont une quantité à 0 contenus dans une nomenclature
	*
	*/
	affiche_articles_quantite_a_0 : function(articles_a_0, niveau = -1){

		var affichage_articles_a_0 = '';
		niveau++;

		for(let [nom_article, articles] of Object.entries(articles_a_0)){



			if(typeof articles === 'object'){

				affichage_articles_a_0 += '<span style="margin-left:' + niveau * 30 +'px">' + nom_article + '</span><br>';
				affichage_articles_a_0 += this.affiche_articles_quantite_a_0(articles, niveau)
			}
			else
				affichage_articles_a_0 += '<span style="margin-left:' + niveau * 30 +'px">' + articles + '</span><br>';
		}

		return affichage_articles_a_0;
	},

@endpush

@push('donnees_pour_vuejs_computed')

	client_id: function() {

		return this.document.client_id;
	},

	clone_document: function(){
		return JSON.parse(JSON.stringify(this.document))
	},

	titre_fil_ariane(){

		var titre_fil_ariane = this.document.reference_document+' '+this.tags_pour_liste.join(' ');

		if(['facture_achat', 'facture_vente', 'avoir_achat', 'avoir_vente', 'acompte_vente', 'acompte_achat'].includes(this.type_element)
			&& this.document.valide == 1 && this.document.regle != 1
		)
			titre_fil_ariane += ' <span class="badge badge-danger">'+this.traduction('interface.document.badge_titre_saisie_document.solde_a_regler')+' '+this.$options.filters.montant(this.document.solde_document_ttc)+'</span>';

		if(this.option_parametrer_element != '')
			titre_fil_ariane += ' '+this.option_parametrer_element;

		return titre_fil_ariane;
	},

@endpush

@push('donnees_pour_vuejs_mounted')

	@if(isset($listes_sur_fiche['unitaire']['fiche_facture_vente_paiement']))
		this.$on('enregistrement_liste_{{$listes_sur_fiche['unitaire']['fiche_facture_vente_paiement']['liste_libre']->id}}',() => {
			this.mise_a_jour_tags();
		});
	@endif

	if(this.document.id)
		this.intervalle_mise_a_jour_tags = setInterval(() => this.mise_a_jour_tags(), 15000);
@endpush

@push('donnees_pour_vuejs_data')
	intervalle_mise_a_jour_tags : null,
@endpush

@include('eden::formulaires.include.document_js_enregistrement')
@include('eden::formulaires.include.document.vues_a_surcharger.verifications_specifiques_pre_enregistrement_js')