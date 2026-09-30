@extends('eden::templates.template')

@section('title') Liste des parametres element @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('nom' => 'Liste des paramètres des éléments')
			)])

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.liste_elements_parametres.titre_categorie.divers')
							</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<div class="row">
									<div class="col-md-2"><b>@traduction('interface.liste_elements_parametres.titre_colonne.parametre')</b></div>
									<div class="col-md-10"><b>@traduction('interface.liste_elements_parametres.titre_colonne.explication')</b></div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/parametrage/champs_libres/modification_des_listes_libres') }}">@traduction('interface.liste_elements_parametres.titre_ligne.modification_des_listes_libres')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.modification_des_listes_libres')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/entite') }}">@traduction('interface.liste_elements_parametres.titre_ligne.entite')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.entite')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/pays') }}">@traduction('interface.liste_elements_parametres.titre_ligne.pays')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.pays')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/compte_bancaire') }}">@traduction('interface.liste_elements_parametres.titre_ligne.compte_bancaire')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.compte_bancaire')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/compte_email') }}">@traduction('interface.liste_elements_parametres.titre_ligne.compte_email')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.compte_email')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/configuration_email') }}">@traduction('interface.liste_elements_parametres.titre_ligne.configuration_email')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.configuration_email')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/modele_email') }}">@traduction('interface.liste_elements_parametres.titre_ligne.modele_email')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.modele_email')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/cout_rh') }}">@traduction('interface.liste_elements_parametres.titre_ligne.cout_rh')</a></div>
									<div class="col-md-10">
										@if(fiche('saisie_des_temps')->structure_fiche()['options']['unite'] == 'heure')
											@traduction('interface.liste_elements_parametres.explication_ligne.cout_rh')
										@else
											@traduction('interface.liste_elements_parametres.explication_ligne.cout_rh_en_jours')
										@endif
									</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/synchro_mail') }}">@traduction('interface.liste_elements_parametres.titre_ligne.synchro_mail')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.synchro_mail')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/entrepot') }}">@traduction('interface.liste_elements_parametres.titre_ligne.entrepot')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.entrepot')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/devise') }}">@traduction('interface.liste_elements_parametres.titre_ligne.devise')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.devise')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/categorie_activite') }}">@traduction('interface.liste_elements_parametres.titre_ligne.categorie_activite_projet')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.categorie_activite_projet')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/equipe') }}">@traduction('interface.liste_elements_parametres.titre_ligne.equipe')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.equipe')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/adresse_interne') }}">@traduction('interface.liste_elements_parametres.titre_ligne.adresse_interne')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.adresse_interne')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/approbation_workflow') }}">@traduction('interface.liste_elements_parametres.titre_ligne.approbation_workflow')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.approbation_workflow')</div>
								</div>
								@if(fonctionnalite('gescom_activer_style_sur_ligne_document') == true)
									<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
										<div class="col-md-2"><a href="{{ url('/eden/liste/equipe') }}"><a href="{{ route('base_eden.liste.index', ['type_element' => 'style_ligne_document']) }}">@traduction('interface.liste_elements_parametres.titre_ligne.style_ligne_document')</a></div>
										<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.style_ligne_document')</div>
									</div>
								@endif
								@if(fonctionnalite('utiliser_chronometre'))
									<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
										<div class="col-md-2"><a href="{{ route('base_eden.liste.index', ['type_element' => 'parametrage_chronometre']) }}">@traduction('interface.liste_elements_parametres.titre_ligne.parametrage_chronometre')</a></div>
										<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.parametrage_chronometre')</div>
									</div>
								@endif
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ route('base_eden.liste.index', ['type_element' => 'jour_indisponibilite']) }}">@traduction('interface.liste_elements_parametres.titre_ligne.jour_indisponibilite')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.jour_indisponibilite')</div>
								</div>
                                @yield('parametres_specifiques_client_divers')
							</div>
                        </div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.liste_elements_parametres.titre_categorie.vente')
							</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<div class="row">
									<div class="col-md-2"><b>@traduction('interface.liste_elements_parametres.titre_colonne.parametre')</b></div>
									<div class="col-md-10"><b>@traduction('interface.liste_elements_parametres.titre_colonne.explication')</b></div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/groupe_recouvrement') }}">@traduction('interface.liste_elements_parametres.titre_ligne.groupe_recouvrement')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.groupe_recouvrement')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/type_relance_recouvrement') }}">@traduction('interface.liste_elements_parametres.titre_ligne.type_relance_recouvrement')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.type_relance_recouvrement')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/relance_automatique_recouvrement') }}">@traduction('interface.liste_elements_parametres.titre_ligne.relance_automatique_recouvrement')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.relance_automatique_recouvrement')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/canal_de_vente') }}">@traduction('interface.liste_elements_parametres.titre_ligne.canal_de_vente')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.canal_de_vente')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/mode_paiement') }}">@traduction('interface.liste_elements_parametres.titre_ligne.mode_paiement')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.mode_paiement')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/compte_bancaire') }}">@traduction('interface.liste_elements_parametres.titre_ligne.compte_bancaire')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.compte_bancaire')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/modalite_paiement') }}">@traduction('interface.liste_elements_parametres.titre_ligne.modalite_paiement')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.modalite_paiement')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/article_unite') }}">@traduction('interface.liste_elements_parametres.titre_ligne.article_unite')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.article_unite')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/modele_de_document') }}">@traduction('interface.liste_elements_parametres.titre_ligne.modele_de_document')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.modele_de_document')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/modele_commentaire') }}">@traduction('interface.liste_elements_parametres.titre_ligne.modele_commentaire')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.modele_commentaire')</div>
								</div>
								@if(fonctionnalite('utiliser_reglage_marge_par_nature'))
									<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
										<div class="col-md-2"><a href="{{ url('/eden/liste/nature_article') }}">@traduction('interface.liste_elements_parametres.titre_ligne.nature_article')</a></div>
										<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.nature_article')</div>
									</div>
									<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
										<div class="col-md-2"><a href="{{ url('/eden/liste/nature_article_modele') }}">@traduction('interface.liste_elements_parametres.titre_ligne.nature_article_modele')</a></div>
										<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.nature_article_modele')</div>
									</div>
								@endif
								@if(fonctionnalite('calculateur_sur_document'))
									<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
										<div class="col-md-2"><a href="{{ url('/eden/liste/modele_de_calculateur') }}">@traduction('interface.liste_elements_parametres.titre_ligne.modele_de_calculateur')</a></div>
										<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.modele_de_calculateur')</div>
									</div>
								@endif
								@yield('parametres_specifiques_client_vente')
							</div>
                        </div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.liste_elements_parametres.titre_categorie.gestion_tresorerie')
							</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<div class="row">
									<div class="col-md-2"><b>@traduction('interface.liste_elements_parametres.titre_colonne.parametre')</b></div>
									<div class="col-md-10"><b>@traduction('interface.liste_elements_parametres.titre_colonne.explication')</b></div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ route('budget_insight.parametrage_synchro') }}">@traduction('interface.liste_elements_parametres.titre_ligne.parametrage_synchro')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.parametrage_synchro')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/automatisation_fournisseur') }}">@traduction('interface.liste_elements_parametres.titre_ligne.automatisation_fournisseur')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.automatisation_fournisseur')</div>
								</div>
								
								@yield('parametres_specifiques_client_treso')
							</div>
                        </div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.liste_elements_parametres.titre_categorie.comptabilite')
							</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<div class="row">
									<div class="col-md-2"><b>@traduction('interface.liste_elements_parametres.titre_colonne.parametre')</b></div>
									<div class="col-md-10"><b>@traduction('interface.liste_elements_parametres.titre_colonne.explication')</b></div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/journal_comptable') }}">@traduction('interface.liste_elements_parametres.titre_ligne.journeaux_comptable')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.journeaux_comptable')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/compte_comptable') }}">@traduction('interface.liste_elements_parametres.titre_ligne.plan_comptable')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.plan_comptable')</div>
								</div>
								<!--
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/taux_de_tva') }}">Taux de TVA</a></div>
									<div class="col-md-10">Liste des taux de TVA gérés en comptabilité</div>
								</div>
								-->
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/code_tva') }}">@traduction('interface.liste_elements_parametres.titre_ligne.taux_tva')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.taux_tva')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/exercice') }}">@traduction('interface.liste_elements_parametres.titre_ligne.exercices')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.exercices')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/categorie_comptable') }}">@traduction('interface.liste_elements_parametres.titre_ligne.categorie_comptable')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.categorie_comptable')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/correspondance_mode_paiement_budget_insight_eden') }}">@traduction('interface.liste_elements_parametres.titre_ligne.correspondance_mode_paiement_insigth_eden')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.correspondance_mode_paiement_insigth_eden')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/article_note_de_frais') }}">@traduction('interface.liste_elements_parametres.titre_ligne.article_note_de_frais')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.article_note_de_frais')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/export_compta_modele') }}">@traduction('interface.liste_elements_parametres.titre_ligne.export_compta_modele')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.export_compta_modele')</div>
								</div>
								<div class="row" style="margin-bottom: 10px; margin-top: 10px;">
									<div class="col-md-2"><a href="{{ url('/eden/liste/export_compta_colonne') }}">@traduction('interface.liste_elements_parametres.titre_ligne.export_compta_colonne')</a></div>
									<div class="col-md-10">@traduction('interface.liste_elements_parametres.explication_ligne.export_compta_colonne')</div>
								</div>
								@yield('parametres_specifiques_client_treso')
							</div>
                        </div>
					</div>
				</div>
				@yield('parametres_specifiques_client')
			</div>
		</div>
	</div>

@endsection




