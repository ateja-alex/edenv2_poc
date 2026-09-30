@extends('eden::templates.template')

@section('title') Paramètres {!! table_libre($type_element)->element_pluriel !!} @stop

@section('styles')

<style type="text/css">
	/*
	.zoom:hover .fa{
		font-size: 25px !important;
	}
	.zoom:hover p{
		font-size: 15px;
	}
	.zoom:hover {

		cursor: pointer;
		padding-top: 18px;
		padding-bottom: 17px;
	}
	.zoom {

		border: 1px solid #7F7D7B;
		width: 80%;
		padding: 20px;
	}
	*/
	.zoom:hover {

		cursor: pointer;
		background: var(--background_menus);
		color: white;
	}

	.zoom {

		padding: 5px;
		margin: 5px !important;
		border: 1px solid #7F7D7B;

	}

	.zoom p {

		color: #495057;
	}

	.zoom i {

		color: #7F7D7B;
	}

	.zoom:hover p {

		color: white;
	}
	.zoom:hover i {

		color: white;
	}
</style>
@stop

@section('content')

<div class="content-wrapper" >

	<div id="base-content" class="container-fluid">

		@include('eden::includes.fil_ariane', ['fil_ariane' => array(
			array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
			array('route' => 'parametrage.table_libre.principales', 'nom' => 'Gestion des Éléments'),
			array('nom' => table_libre($type_element)->element)
		)])

		<div class="row">
			<div class="col-md-12">
				<div class="card mb-3" v-show="contenu_principale">
					<div class="card-header card_header_parametrage">
						<h4>
							{!! table_libre($type_element)->element_pluriel !!}
						</h4>
						<div class="card_header_parametrage_options">
							@if($table_libre->vue_sql != 1)
								<span class="css_ajouter_element css__lien" data-toggle="tooltip" data-placement="left" title="Synchroniser les pièces jointes dans Sharepoint" @click="envoyer_pj_sharepoint()">
									<i class="css_action_icon fas fa-file-import"></i>
								</span>
								<span class="css_ajouter_element css__lien" data-toggle="tooltip" data-placement="left" title="Rafraîchir les index de recherche" @click="rafraichir_index_recherche()">
									<i class="css_action_icon fas fa-search-plus"></i>
								</span>
								<span class="css_ajouter_element css__lien" data-toggle="tooltip" data-placement="left" title="Vider chaînes d'affichage" @click="vider_chaine_affichage()">
									<i class="css_action_icon fas fa-search-minus"></i>
								</span>
							@else
								<div class="ml-auto badge badge-primary">Vue SQL</div>
							@endif
							<span class="css_ajouter_element css__lien" data-toggle="tooltip" data-placement="left" title="Rafraîchir les composants listes" @click="genere_fichier_composants()">
								<i class="css_action_icon fa fa-recycle"></i>
							</span>
						</div>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-12">
								<h4 class="mb-3">Structure</h4>
							</div>
						</div>

						<div class="row">

							<div class="col-md-4 mb-3">
								<div class="css_block_acces_module_parametrage" @click="modification_table_libre(table_libre.id)">
									<img src="{{ asset('eden/images/pictos/connection.png') }}" alt="">
									<span>
										Table
									</span>
								</div>
							</div>

							<div class="col-md-4 mb-3">
								<a href="{{ route('parametrage.champ_libre.liste', $type_element) }}">
									<div class="css_block_acces_module_parametrage">
										<img src="{{ asset('eden/images/pictos/fields.png') }}" alt="">
										<span>
											Champs
										</span>
									</div>
								</a>
							</div>

                            @if($table_libre->vue_sql != 1)

                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('parametrage.fiche.index', $type_element) }}">
                                        <div class="css_block_acces_module_parametrage">
                                            <img src="{{ asset('eden/images/pictos/sheet.png') }}" alt="">
                                            <span>
                                                Fiche
                                            </span>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('parametrage.extranet.fiche', $type_element) }}">
                                        <div class="css_block_acces_module_parametrage">
                                            <img src="{{ asset('eden/images/pictos/sheet.png') }}" alt="">
                                            <span>
                                                Fiche extranet
                                            </span>
                                        </div>
                                    </a>
                                </div>

							@endif

							<div class="col-md-4 mb-3" v-for="bloc in blocs.filter(b => !b.desactive)" @click="afficher_bloc(bloc)">
								<div class="css_block_acces_module_parametrage">
									<img :src="bloc.image" alt="">
									<span v-html="bloc.nom"></span>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<h4 class="mb-3">Listes</h4>
								<span class="mb-3 css_form" style="float: right;">
									<select v-model="filtres_listes" style="width: 105px;">
										<option value="0">Tous</option>
										<option value="1">Activée</option>
										<option value="2">Désactivée</option>
									</select>
								</span>
							</div>
						</div>

						<div class="row">

							{{-- On boucle sur les listes libres  --}}
							<div class="col-md-4 mb-3" v-for="liste_libre in informations_listes.listes" v-if="filtres_listes == 0 || (filtres_listes == 1 && liste_libre.inactif != 1) | (filtres_listes == 2 && liste_libre.inactif == 1)">
								<a :href="'{{URL::to('eden/parametrage/liste_libre/')}}/'+liste_libre.id">
									<div :class="'css_block_acces_module_parametrage '+(liste_libre.inactif == 1 ? 'inactif' : '')">
										<span data-toggle="tooltip" title="Standard" v-if="liste_libre.standard == 1" style="position: absolute;top: 5px;right: 7px;font-size: 10px;">
											<img class="image_relative" style="width: 20px;" src="{{ asset('eden/images/pictos/pastille_standard.png')}}" alt="">
										</span>
										<span style="position: absolute;bottom: 5px;right: 7px;" data-toggle="tooltip" data-placement="left" title="Rafraîchir ce composant liste">
											<i @click="regenere_fichier_composant($event,liste_libre.id)" class="fa fa-refresh image_relative"></i>
										</span>
										<img :src="'{{ asset('eden/images/pictos')}}/icone_liste_libre_'+liste_libre.type_liste+'.png'" alt="">
										<span v-if="liste_libre.type_liste == 'principale'" v-html="traduction(liste_libre.table_index_traduction,'element_pluriel')"></span>
										<span v-else v-html="traduction(liste_libre.rapport_index_traduction,'titre')"></span>
										<span style="font-size: 10px;" v-if="liste_libre.type_liste !== 'principale'">(@{{ liste_libre.id_rapport }})</span>
										<span style="font-size: 10px;" v-else>(Liste principale)</span>
									</div>
								</a>
							</div>

							<div class="col-md-4 mb-3">
								<div class="css_ajout_nouvelle_liste_param_zoom " @click="creer_nouvelle_liste">
									<img src="{{ asset('eden/images/pictos/add.svg') }}" alt="">
									<span>NOUVELLE LISTE</span>
								</div>
							</div>
						</div>

                        @if($table_libre->fiche == 1 || in_array($type_element, \App\Eden\Variables::$documents_gescom))
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="mb-3">Rapports sur fiche</h4>
                                </div>
                            </div>

                            <div class="row">

                                {{-- On boucle sur les rapports  --}}
                                <div class="col-md-4 mb-3" v-for="rapport in rapports_fiche">
                                    <a :href="'{{URL::to('eden/parametrage/rapport/parametrer')}}/'+rapport.id_rapport">
                                        <div :class="'css_block_acces_module_parametrage '+(rapport.inactif == 1 ? 'inactif' : '')">
                                            <span data-toggle="tooltip" title="Standard" v-if="rapport.standard == 1" style="position: absolute;top: 5px;right: 7px;font-size: 10px;">
                                                <img class="image_relative" style="width: 20px;" src="{{ asset('eden/images/pictos/pastille_standard.png')}}" alt="">
                                            </span>
                                            <i :class="'fa fa-' + rapport.icone" :id="rapport.id"></i>
                                            <span v-html="traduction(rapport.index_traduction, 'titre')"></span>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="css_ajout_nouvelle_liste_param_zoom " @click="creer_nouveau_rapport">
                                        <img src="{{ asset('eden/images/pictos/add.svg') }}" alt="">
                                        <span>NOUVEAU RAPPORT</span>
                                    </div>
                                </div>
                            </div>
                        @endif

						@if($table_libre->vue_sql != 1)

							<div class="row">
								<div class="col-md-12">
									<h4 class="mb-3">Formulaires</h4>
								</div>
							</div>

							<div class="row">

                                @if(!$formulaire_generique_vide)
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('parametrage.formulaire.index', $type_element) }}">
                                            <div class="css_block_acces_module_parametrage">
                                                <img src="{{ asset('eden/images/pictos/form.png') }}" alt="">
                                                <span>
                                                    Formulaire Générique
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                @else
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('parametrage.formulaire.ajouter_volee', $type_element) }}">
                                            <div class="css_ajout_nouvelle_liste_param_zoom">
                                                <img src="{{ asset('eden/images/pictos/add.svg') }}" alt="">
                                                <span>
                                                    Créer formulaire générique
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                @endif


                                @if(!$formulaire_fiche_vide)
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('parametrage.formulaire.index', 'fiche_'.$type_element) }}">
                                            <div class="css_block_acces_module_parametrage">
                                                <img src="{{ asset('eden/images/pictos/form.png') }}" alt="">
                                                <span>
                                                    Formulaire Fiche
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                @else
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('parametrage.formulaire.ajouter_volee', ['fiche_'.$type_element,'fiche']) }}">
                                            <div class="css_ajout_nouvelle_liste_param_zoom">
                                                <img src="{{ asset('eden/images/pictos/add.svg') }}" alt="">
                                                <span>
                                                    Créer formulaire fiche
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                @endif

                                @if(!$formulaire_creation_volee_vide)
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('parametrage.formulaire.index', 'creation_volee_'.$type_element) }}">
                                            <div class="css_block_acces_module_parametrage">
                                                <img src="{{ asset('eden/images/pictos/form.png') }}" alt="">
                                                <span>
                                                Formulaire Création à la volée
                                            </span>
                                            </div>
                                        </a>
                                    </div>
                                @else
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('parametrage.formulaire.ajouter_volee_parametrable',
                                        ['creation_volee_'.$type_element, $type_element,'creation_volee']) }}">
                                            <div class="css_ajout_nouvelle_liste_param_zoom">
                                                <img src="{{ asset('eden/images/pictos/add.svg') }}" alt="">
                                                <span>
                                                Créer formulaire création à la volée
                                            </span>
                                            </div>
                                        </a>
                                    </div>
                                @endif

                                @foreach($formulaires_libres as $formulaire_libre)
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('parametrage.formulaire.index', $formulaire_libre->nom_formulaire) }}">
                                            <div class="css_block_acces_module_parametrage">
                                                <img src="{{ asset('eden/images/pictos/form.png') }}" alt="">
                                                <span v-html="traduction('{{$formulaire_libre->index_traduction}}','titre')">
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach

								<div class="col-md-4 mb-3">
									<a href="{{ route('parametrage.formulaire.ajouter_volee_parametrable',
									['formulaire_'.(count($formulaires_libres) + 1).'_'.$type_element, $type_element]) }}">
										<div class="css_ajout_nouvelle_liste_param_zoom">
											<img src="{{ asset('eden/images/pictos/add.svg') }}" alt="">
											<span>Créer un formulaire pour les fiches</span>
										</div>
									</a>
								</div>

								<div class="col-md-4 mb-3">
									<a href="{{ route('parametrage.formulaire.ajouter_volee_parametrable',
									['extranet_'.$type_element, $type_element,'extranet']) }}">
										<div class="css_ajout_nouvelle_liste_param_zoom">
											<img src="{{ asset('eden/images/pictos/add.svg') }}" alt="">
											<span>
												Créer un formulaire extranet
											</span>
										</div>
									</a>
								</div>

								<div class="col-md-4 mb-3">
									<a href="{{ route('parametrage.formulaire.ajouter_volee_parametrable',
									['web_'.(count($formulaires_libres->where('type_formulaire','web')) + 1).
									'_'.$type_element, $type_element,'web']) }}">
										<div class="css_ajout_nouvelle_liste_param_zoom">
											<img src="{{ asset('eden/images/pictos/add.svg') }}" alt="">
											<span>
												Créer un formulaire web
											</span>
										</div>
									</a>
								</div>
						    </div>
                        @endif
					</div>
				</div>
				<div v-for="bloc in blocs" v-show="bloc_afficher == bloc.id">
					<div style="position:relative">
						<div style="position: absolute;top: -10px;left: -10px;z-index: 500" @click="contenu_principale = true;bloc_afficher = null">
							<div class="bulle_option css_pointer">
								<i class="fas fa-chevron-left"></i>
							</div>
						</div>
						<component :is="bloc.composant.name" v-bind="bloc.composant.props"></component>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

	<!-- Modal modification table libre -->
	@include('eden::parametrage.include.modal_modification_table_libre')
	@include('eden::parametrage.include.modale_ajout_liste_libre')
	@include('eden::parametrage.include.modale_ajout_rapport')


@endsection

@php 

	$id_listes_libres = App\Eden\Models\Liste_libre::whereIn('type_element', [
			'trigger_eden',
			'notification_manuelle',
			'modele_email',
			'parametrage_destinataire_email',
			'parametrage_piece_jointe_email',
			'parametrage_balise_publipostage'
		])
		->where(function($requete){
			$requete->whereNull('id_rapport')->orWhere('id_rapport','');
		})
		->get()->pluck('id','type_element');

	$blocs = [
		[
			'id' => 'triggers',
			'nom' => 'Triggers',
			'image' => asset('eden/images/pictos/trigger.png'),
			'id_liste' => $id_listes_libres['trigger_eden'],
			'composant' => [
				'name' => 'liste-libre-' . $id_listes_libres['trigger_eden'],
				'props' => [
					'filtres_pour_fiche' => ['type_element_id' => $table_libre->id],
					'modele_par_defaut' => array_merge((array) modele_par_defaut('trigger_eden'), ['type_element_id' => $table_libre->id]),
					'mode_parametrage' => 1,
				]
			]
		],
		[
			'id' => 'notification_manuelle',
			'nom' => 'Notification manuelle',
			'image' => asset('eden/images/pictos/time.png'),
			'id_liste' => $id_listes_libres['notification_manuelle'],
			'composant' => [
				'name' => 'liste-libre-' . $id_listes_libres['notification_manuelle'],
				'props' => [
					'filtres_pour_fiche' => ['type_element_id' => $table_libre->id],
					'modele_par_defaut' => array_merge((array) modele_par_defaut('notification_manuelle'), ['type_element_id' => $table_libre->id]),
					'mode_parametrage' => 1,
				]
			]
		],
		[
			'id' => 'parametrage_envoi_email',
			'desactive' => $table_libre->envoyer_email != 1,
			'nom' => "Email",
			'image' => asset('eden/images/pictos/email.png'),
			'id_liste' => [
				$id_listes_libres['modele_email'], 
				$id_listes_libres['parametrage_destinataire_email'], 
				$id_listes_libres['parametrage_piece_jointe_email'],
				$id_listes_libres['parametrage_balise_publipostage']
			],
			'composant' => [
				'name' => 'parametrage-email',
				'props' => [
					'type_element' => $type_element,
					'ids_listes' => [
						'modele_email' => $id_listes_libres['modele_email'],
						'parametrage_destinataire_email' => $id_listes_libres['parametrage_destinataire_email'],
						'parametrage_piece_jointe_email' => $id_listes_libres['parametrage_piece_jointe_email'],
						'parametrage_balise_publipostage' => $id_listes_libres['parametrage_balise_publipostage']
					]
				]
			]
		],
	];

@endphp

@push('composants_vue')
	@foreach($blocs as $bloc)
		@if(isset($bloc['id_liste']))
			@foreach((array)$bloc['id_liste'] as $id_liste)
				<script type="text/javascript" src="{{ asset('storage/composants/liste_libre_'.$id_liste.'.js') }}"></script>
			@endforeach
		@endif
	@endforeach
@endpush

@section('donnees_pour_vuejs_data')
    types_elements: {!! $types_elements !!},
	informations_listes: {!! collect($informations_listes) !!},
	table_libre: {!! $table_libre !!},
    rapports_fiche: {!! json_encode($rapports_fiche) !!},
	table_libre_modification: '',
	filtres_listes: 1,
	contenu_principale: true,
	bloc_afficher: null,
	blocs : {!! collect($blocs) !!},
@endsection

@section('donnees_pour_vuejs_methods')

	rafraichir_index_recherche() {

		loading(true)

		// on enregistre les infos de la table libre
		$.get({

			url: "{{ URL::to("eden/maintenance/maj_chaine_tags_ajax/".$type_element) }}",
			dataType: "json",
			method: 'GET'
		}).done(async function(donnees) {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

            info('Action effectuée avec succès !');

		});
	},

	envoyer_pj_sharepoint() {

		loading(true)

		// on enregistre les infos de la table libre
		$.get({

			url: "{{ URL::to("/eden/maintenance/sharepoint/creation_dossiers_elements/".$type_element) }}",
			dataType: "json",
			method: 'GET'
		}).done(async function(donnees) {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

		});
	},

	vider_chaine_affichage() {

		loading(true)

		// on enregistre les infos de la table libre
		$.get({

			url: "{{ URL::to("eden/maintenance/vider_chaine_affichage/".$type_element) }}",
			dataType: "json",
			method: 'GET'
		}).done(async function(donnees) {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

            info('Action effectuée avec succès !');

		});

	},

	genere_fichier_composants() {

		loading(true)

		// on enregistre les infos de la table libre
		$.get({

			url: "{{ URL::to("eden/maintenance/generation_fichier/composants/liste/element/".$type_element) }}",
			dataType: "json",
			method: 'GET'

		}).done(async function(retour) {
			loading(false);

			if(retour !== true) {

				await erreur(retour);
				return;
			}

			info('Action effectuée avec succès !');

		});

	},

	regenere_fichier_composant: function(event,liste_libre_id){

		event.stopPropagation();
		event.preventDefault();

		loading(true);

		$.ajax({
			url : '{{ URL::to("eden/maintenance/generation_fichier/composants/liste/id/") }}/'+liste_libre_id,
		}).done(function(){
			loading(false);
			info('Action effectuée avec succès !');
		});
	},

	afficher_bloc(bloc){

		if(bloc.desactive)
			return;

		this.contenu_principale = false;
		this.bloc_afficher = bloc.id;
	},

@endsection
