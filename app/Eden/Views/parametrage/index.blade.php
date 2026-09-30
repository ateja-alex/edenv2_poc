@extends('eden::templates.template')

@section('title') Paramétrage @stop

@section('styles')
<style type="text/css">
	/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
@endsection

@include('eden::modales_alertes.rappels_versions')

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('nom' => 'Paramétrage')
			)])

			@if(editeur())
				<div class="row">
					<div class="col-md-12">

						<div class="card mb-3">
							<div class="card-header">
								<h4>
									Administration
								</h4>
							</div>

							<div class="card-body">
								<div class="row">

									<div class="col-md-3 mb-3">
										<a href="{{ route('maintenance.migrations') }}">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/maintenance.png') }}" alt="">
												<span>Migrations</span>
											</div>
										</a>
									</div>

									@if(editeur())
										<div class="col-md-3 mb-3">
											<a href="{{ route('maintenance.debug') }}">
												<div class="css_block_acces_module_parametrage">
													<i class="fas fa-clipboard-list"></i>
													<span>Mode développeur</span>
												</div>
											</a>
										</div>
									@endif

									@if(editeur())
										<div class="col-md-3 mb-3">
											<a href="{{ route('maintenance.cache') }}">
												<div class="css_block_acces_module_parametrage">
													<i class="fas fa-database"></i>
													<span>Contrôle du cache</span>
												</div>
											</a>
										</div>
									@endif
									<div class="col-md-3 mb-3">
										<a href="{{ route('base_eden.liste.index',['type_element' => 'version_eden']) }}">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/list.png') }}" alt="">
												<span>Versioning</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a href="{{route('parametrage.logs.index')}}">
											<div class="css_block_acces_module_parametrage">
												<img src="{{asset('/eden/images/pictos/status.png')}}" alt="">
												<span>Logs</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a href="{{ route('base_eden.liste.index', 'statistique_bdd') }}">
											<div class="css_block_acces_module_parametrage">
												<i class="fas fa-database"></i>
												<span>Taille des tables de BDD</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a @click="appel_route('{{ route('maintenance.colonnes_null') }}')">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/maintenance.png') }}" alt="">
												<span>Passer toutes les colonnes en NULL</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<span @click="appel_route('{{ route('maintenance.suppresion_filtres_utilisateurs_rapports') }}')">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/icone_crm.png') }}" alt="">
												<span>Supprimer les filtres des utilisateurs sur les rapports</span>
											</div>
										</span>
									</div>

								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header">
								<h4>
									Génération des composants
								</h4>
							</div>
							<div class="card-body">

								<div class="row">
									<div class="col-md-3 mb-3">
										<a @click="appel_route('{{ route('maintenance.generation_fichier.composants.index') }}')">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
												<span>Mettre à jour tous les composants</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a @click="appel_route('{{ route('maintenance.generation_fichier.menus') }}')" >
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
												<span>Mettre à jour les menus</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a @click="appel_route('{{ route('maintenance.generation_fichier.css') }}')">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
												<span>Mettre à jour le css</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a @click="appel_route('{{ route('maintenance.generation_fichier.composants.module') }}')" >
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
												<span>Mettre à jour les modules</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a href="{{ route('maintenance.generation_fichier.module') }}">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
												<span>Mettre à jour un module</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a @click="appel_route('{{ route('maintenance.generation_fichier.composants.liste') }}')">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
												<span>Mettre à jour les listes</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a @click="appel_route('{{ route('maintenance.generation_fichier.valeurs_champs_listes') }}')">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
												<span>Mettre à jour les valeurs des listes libres et des listes formatées</span>
											</div>
										</a>
									</div>

									<div class="col-md-3 mb-3">
										<a @click="appel_route('{{ route('maintenance.generation_fichier.traductions') }}')">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
												<span>Mettre à jour le fichier de traductions</span>
											</div>
										</a>
									</div>

								</div>

							</div>

						</div>
					</div>
				</div>
			@endif

			<div class="row">
				<div class="col-md-12">

					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Structure de l'ERP
							</h4>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.menu.index',['extranet' => null]) }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/menu.png') }}" alt="">
											<span>
												Menus
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.menu.index',['extranet' => 'extranet']) }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/menu.png') }}" alt="">
											<span>
												Menus Extranet
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.table_libre.principales') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/database.png') }}" alt="">
											<span>
												Tables principales
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.table_libre.liste') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/database.png') }}" alt="">
											<span>
												Autres tables
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('base_eden.liste.index', 'vue_sql') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/database.png') }}" alt="">
											<span>
												Vues libres
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.rapport.liste') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/list.png') }}" alt="">
											<span>
												Rapports
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('base_eden.liste.index', ['type_element' => 'mappage_table_conversion']) }}">
										<div class="css_block_acces_module_parametrage">
											<i class="fa fa-exchange-alt"></i>
											<span>
												Conversions
											</span>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">

					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Paramétrage général de l'ERP
							</h4>
						</div>
						<div class="card-body">
							<div class="row">

								<div class="col-md-3 mb-3">
									<a href="{{ url('/eden/liste/maquette') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/palette.png') }}" alt="">
											<span>
												Maquette
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.css.index') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/palette.png') }}" alt="">
											<span>
												CSS personnalisé
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.css.index', ['extranet' => 'true']) }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/palette.png') }}" alt="">
											<span>
												CSS personnalisé - Extranet
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.fonctionnalites.index') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/fonction_on_off.png') }}" alt="">
											<span>
												Fonctionnalités
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.traduction.index') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/traduction.png') }}" alt="">
											<span>
												Traduction
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('base_eden.liste.index', ['type_element' => 'tableau_de_bord']) }}">
										<div class="css_block_acces_module_parametrage">
											<i class="fab fa-windows"></i>
											<span>
												Tableaux de bord
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('base_eden.liste.index', ['type_element' => 'requete_sql_cron']) }}">
										<div class="css_block_acces_module_parametrage">
											<i class="far fa-bell"></i>
											<span>
												Requêtes planifiées
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('base_eden.liste.index', ['type_element' => 'notification_manuelle']) }}">
										<div class="css_block_acces_module_parametrage">
											<i class="far fa-clock"></i>
											<span>
												Notifications
											</span>
										</div>
									</a>
								</div>

								@if(fonctionnalite('intranet'))
									<div class="col-md-3 mb-3">
										<a href="{{ route('parametrage.intranet.index') }}">
											<div class="css_block_acces_module_parametrage">
												<img src="{{ asset('eden/images/pictos/sheet.png') }}" alt="">
												<span>
													Intranet
												</span>
											</div>
										</a>
									</div>
								@endif
								<div class="col-md-3 mb-3">
									<a href="{{ url('/eden/liste/cron') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/cron.png') }}" alt="">
											<span>
												Crons
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.licence.index') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/licence.png') }}" alt="">
											<span>
												Licences
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.email.index') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/email.png') }}" alt="">
											<span>
												E-Mail
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('base_eden.liste.index',['synchronisation_service']) }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/connection.png') }}" alt="">
											<span>
												Synchronisation service externe
											</span>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">

					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Paramétrage des pdfs
							</h4>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-3 mb-3">
									<a href="{{ route('parametrage.pdf_par_defaut.index') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/pdf.png') }}" alt="">
											<span>
												PDF par défaut
											</span>
										</div>
									</a>
								</div>
								<div class="col-md-3 mb-3">
									<a href="{{ route('base_eden.liste.index', ['type_element' => 'modele_de_document']) }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/pdf_files.png') }}" alt="">
											<span>
												Modèles de PDF
											</span>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Divers
							</h4>
						</div>
						<div class="card-body">
							@if(editeur())
								<a href="{{ route('maintenance.sharepoint.creation_dossiers_elements') }}">Création des dossiers Eden dans Sharepoint</a> : permet de créer les dossiers de tous les éléments dans Sharepoint.<br/>
							@endif
							<a href="{{ route('import_sur_mesure.index') }}">Imports sur mesure</a> : permet d'importer un fichier CSV<br/>
							<a href="{{ route('parametrage.indicateur.index') }}">Indicateurs</a> : permet de paramétrer les indicateurs de l'ERP<br/>
							<a href="{{ route('base_eden.liste.index', ['type_element' => 'calendrier_synchro']) }}">Synchro du calendrier</a> : permet de gérer la synchro calendrier des différents éléments.<br/>
							<a href="{{ route('parametrage.gdrive.index') }}">Synchro GDrive de la bibliothèque</a> : permet de connecter Google Drive à la bibliothèque.<br/>
							<a href="{{ route('base_eden.liste.index', ['type_element' => 'approbation_workflow']) }}">Approbations Workflow</a> : permet d'approuver ou réfuter des actions en attente d'approbation.<br/>
							<a href="{{ route('docusign.droit_docusign_api') }}">Droits Docusign API</a> : permet de donner les droits à EDEN de gérer les informations de Docusign <br/>
                            <a href="{{ route('base_eden.liste.index', ['type_element' => 'mappage_insee']) }}">Mappage INSEE</a> : Configurer les liaisons avec l'API pour récupérer les informations des entreprises.<br/>
							<a href="javascript:void(0)"  @click="appel_route('{{ route('eden_cron.generer_pdfs_documents') }}')">Générer les pdfs des documents</a> : permet de générer les pdfs des documents qui n'ont pas de pdf <br/>
							<a href="javascript:void(0)" onClick="copie_lien()">Plugin outlook</a> : Copie le lien de l'extension<br/>
							<input id="lien_outlook" style="display: none;" value="https://addon-outlook.easydev.run/manifest.prod.xml"/>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_methods')
	appel_route:function(route){

		loading(true);

		$.ajax({
			url: route,
			dataType: 'json',
		}).done(async function(retour){

			loading(false);

			if(retour !== true) {

				await erreur(retour);
				return;
			}

			info("Action effectuée avec succès !");
		});

	},
@endpush

<script type="text/javascript">

	function copie_lien() {

		var copyText = document.getElementById("lien_outlook");

		copyText.select();
		copyText.setSelectionRange(0, 99999);

		navigator.clipboard.writeText(copyText.value);

		toastr.success("URL d'extension copié")
	};
</script>
