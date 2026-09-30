@extends('eden::templates.template')

@section('title') Import sur mesure @stop

@php
	$id_liste_import_sur_mesure = 0;

	$liste_import_sur_mesure = \App\Eden\Models\Liste_libre::where('type_element','import_sur_mesure')
		->where(function($requete){
			$requete->whereNull('id_rapport');
			$requete->orWhere('id_rapport','');
		})->first();

	if($liste_import_sur_mesure != null)
		$id_liste_import_sur_mesure = $liste_import_sur_mesure->id;
@endphp

@section('content')

	<div class="content-wrapper">
		<div id="base-content" class="container-fluid">

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3" id="import_sur_mesure">
						<div class="card-header css_flex_header_liste">
							<h4>
								@{{ traduction('interface.import_sur_mesure.etapes.'+etapes_noms[etape_import]) }} : @traduction('interface.import_sur_mesure.titre')
							</h4>
							<div class="ml-md-3 css_form" v-if="import_sur_mesure.titre != ''" style="font-size: 20px;display: inline-flex;">
								<span style="margin-right: 10px;">-</span>
								<span v-if="modification_titre == false" style="cursor: pointer;" @click="modification_titre = true;">
									@{{ import_sur_mesure.titre }}
									<i class="fas fa-pen"></i>
								</span>
								<input v-else type="text" @change="enregistrer_import();modification_titre = false;" v-model="import_sur_mesure.titre" />
							</div>
							<div class="ml-auto" v-if="etape_import == 1">
								<i @click="enregistrer_import()" data-toggle="tooltip" :title="traduction('interface.import_sur_mesure.tooltip.enregistrer_mappage')" class="css_action_icon secondaire far fa-save css_font_16"></i>
							</div>
							<div class="ml-auto" v-else></div>
						</div>
						<div class="card-body css_form css_parametrage_formulaire">
							<div v-show="etape_import == 0">
								@include('eden::import_sur_mesure.initialisation')
							</div>
							<div v-if="etape_import == 1">
								@include('eden::import_sur_mesure.parametrage')
							</div>
							<div v-if="etape_import == 2">
								@include('eden::import_sur_mesure.recapitulatif')
							</div>
							<div v-if="etape_import == 3">
								@include('eden::import_sur_mesure.import_en_cours')
							</div>
							<div v-if="etape_import == 4">
								@include('eden::import_sur_mesure.resultat')
							</div>
						</div>
						<div class="card-footer" style="background-color: red;" v-if="condition_suivant !== true">
							<div class="row" >
								<div class="col-md-12" style="color:white" v-html="condition_suivant">
								</div>
							</div>
						</div>
						<div class="card-footer" v-if="etape_import == 2">
							<div class="row">
								<div class="col-sm-7"></div>
								<div class="col-sm-3">
									{!! management('import_en_cours')->champ('types_notifications')->nom_vue() !!}
								</div>
								<div class="col-sm-2">
									{!! management('import_en_cours')->champ('types_notifications')->cree() !!}
								</div>
							</div>
						</div>
						<div class="card-footer">
							<div class="row">
								<div class="col-md-6" v-if="etape_import < 3">
									<button v-if="etape_import > 0 && etape_import < 3" @click="condition_suivant = true;etape_import -= 1;" class="js_bouton_etape_suivante btn btn-primary" style="border-radius: 0px;padding-left: 6%;padding-right: 6%;padding-top: 1%;padding-bottom: 1%;width: 100%;">
										<i class="fas fa-chevron-left" style="margin-right: 8%;"></i>
										@traduction('interface.import_sur_mesure.precedent')
									</button>
								</div>
								<div class="col-md-6" v-if="etape_import < 2">
									<button class="js_bouton_etape_suivante btn btn-primary" @click="etape_suivante" style="border-radius: 0px;padding-left: 6%;padding-right: 6%;padding-top: 1%;padding-bottom: 1%;width: 100%;">
										@traduction('interface.import_sur_mesure.suivant')
										<i class="fas fa-chevron-right" style="margin-left: 8%;"></i>
									</button>
								</div>
								<div class="col-md-6" v-if="etape_import == 2">
									<button class="js_bouton_etape_suivante btn btn-primary" @click="lancement_import" style="border-radius: 0px;padding-left: 6%;padding-right: 6%;padding-top: 1%;padding-bottom: 1%;width: 100%;">
										@traduction('interface.import_sur_mesure.lancer_import')
									</button>
								</div>
							</div>
							<div class="row" v-if="etape_import == 3 && import_en_cours.nombres_importes > 500">
								<div class="col-md-12">
									<button class="js_bouton_etape_suivante btn btn-danger" @click="annuler_import" style="border-radius: 0px;padding-left: 6%;padding-right: 6%;padding-top: 1%;padding-bottom: 1%;width: 100%;">
										@traduction('interface.import_sur_mesure.annuler_import')
									</button>
								</div>
							</div>
							<div class="row" v-if="etape_import == 4">
								<div class="col-md-12">
									<button class="js_bouton_etape_suivante btn btn-primary" @click="nouvel_import" style="border-radius: 0px;padding-left: 6%;padding-right: 6%;padding-top: 1%;padding-bottom: 1%;width: 100%;">
										@traduction('interface.import_sur_mesure.nouvel_import')
									</button>
								</div>
							</div>
						</div>
					</div>
					<div v-if="etape_import == 0">
						@include('eden::listes.includes.liste', [
							'type_element' => 'import_en_cours',
							'id_liste' => $id_liste_import_sur_mesure,
							'modele_par_defaut' => modele_par_defaut('import_en_cours'),
						])
					</div>
                </div>
            </div>
        </div>
    </div>

@endsection

@include('eden::import_sur_mesure.modale_correspondance_valeurs_listes')

@push('donnees_pour_vuejs_data')
	etape_import : 0,
	import_en_cours: {!! $import_en_cours !!},
	import_sur_mesure: {!! $import_sur_mesure !!},
	condition_suivant: true,
	modification_titre: false,
	etapes_noms:{
		0 : 'initialisation',
		1 : 'parametrage',
		2 : 'recapitulatif',
		3 : 'import_en_cours',
		4 : "fin_import",
	},
@endpush

@push('donnees_pour_vuejs_methods')

	etape_suivante: function(){

		var vue_instance = this;

		loading(true);

		var retour = vue_instance.verification_etape_suivante();

		setTimeout(function () {
			vue_instance.condition_suivant = retour;

			loading(false);

			if(vue_instance.condition_suivant !== true)
				return;

			if(vue_instance.etape_import == 0)
				vue_instance.suite_initialisation();

			if(vue_instance.etape_import == 1)
				vue_instance.suite_parametrage();
		},400);

	},

	verification_etape_suivante: function(){

		var vue_instance = this;

		var retour = true;

		vue_instance.condition_suivant = true;

		if(this.etape_import == 0)
			retour = (this.import_en_cours.nom_fichier != '' && this.import_sur_mesure.type_element != '') ? true : "Le fichier et la table d'import doivent être renseigné";;

		if(this.etape_import == 1){

			var retour = true;

			var dependances_creation_element = [];

			$.each(vue_instance.import_sur_mesure.champs,function(index,informations){

				if(informations.correspondance == null){
					retour = vue_instance.traduction('interface.import_sur_mesure.correspondance_non_renseignee');
					return;
				}

				if(informations.cle_cree === true){

					var table_id = informations.table_cle;

					if(informations.table_id !== undefined)
						table_id = informations.table_id;

					var champ_libre = vue_instance.recuperation_champ_libre(informations.correspondance);

					if(champ_libre != null)
						dependances_creation_element.push([champ_libre.type_element,table_id]);
				}
				else{

					var champ_libre = vue_instance.recuperation_champ_libre(informations.correspondance);

					var correspondances = informations.correspondance.split('.');

					var element = correspondances[0];

					if(champ_libre != null && champ_libre.obligatoire == 1 && informations.cle_mise_a_jour == false && (
						vue_instance.import_sur_mesure.valeurs_par_defaut[element][champ_libre.nom_sql] == undefined
						|| vue_instance.import_sur_mesure.valeurs_par_defaut[element][champ_libre.nom_sql] == 0
						|| vue_instance.import_sur_mesure.valeurs_par_defaut[element][champ_libre.nom_sql] == null
						|| vue_instance.import_sur_mesure.valeurs_par_defaut[element][champ_libre.nom_sql] == '')){
						retour = vue_instance.traduction('interface.import_sur_mesure.valeur_par_defaut_champs_obligatoires');
						return;
					}

				}

			});

			if(retour === true){

				var verification_dependances = vue_instance.verification_dependances(dependances_creation_element);

				if(verification_dependances !== true){
					retour = verification_dependances;
					return retour;
				}

				$.each(vue_instance.champs_libres_valeurs_par_defaut.table_import,function(index_valeur_par_defaut,champ_libre){
					if(champ_libre.obligatoire == 1 && (vue_instance.import_sur_mesure.valeurs_par_defaut[champ_libre.type_element][champ_libre.nom_sql] == undefined
					|| vue_instance.import_sur_mesure.valeurs_par_defaut[champ_libre.type_element][champ_libre.nom_sql] == 0
					|| vue_instance.import_sur_mesure.valeurs_par_defaut[champ_libre.type_element][champ_libre.nom_sql] == null
					|| vue_instance.import_sur_mesure.valeurs_par_defaut[champ_libre.type_element][champ_libre.nom_sql] == '')){
						retour = vue_instance.traduction('interface.import_sur_mesure.valeur_par_defaut_champs_obligatoires')
						return;
					}
				});

				$.each(vue_instance.import_sur_mesure.tables_jointes,function(index_table_jointe,table_jointe){

					$.each(vue_instance.champs_libres_valeurs_par_defaut.tables_jointes[table_jointe.id],function(index,champ_libre){

						if(champ_libre.obligatoire == 1 && (vue_instance.import_sur_mesure.valeurs_par_defaut[table_jointe.id][champ_libre.nom_sql] == undefined
							|| vue_instance.import_sur_mesure.valeurs_par_defaut[table_jointe.id][champ_libre.nom_sql] == 0
							|| vue_instance.import_sur_mesure.valeurs_par_defaut[table_jointe.id][champ_libre.nom_sql] == null
							|| vue_instance.import_sur_mesure.valeurs_par_defaut[table_jointe.id][champ_libre.nom_sql] == '')){
							retour = vue_instance.traduction('interface.import_sur_mesure.valeur_par_defaut_champs_obligatoires')
							return;
						}

					});
				});
			}
		}

		return retour;

	},

	nouvel_import : function(){
		window.location.href = '{{URL::to('eden/import_sur_mesure')}}';
	},

	charger_import_sur_mesure: function(import_sur_mesure_id){

		$.ajax({
			url: '{{"eden/element/import_sur_mesure"}}/'+import_sur_mesure_id,
			dataType: 'json'
		}).done(function(import_sur_mesure){

			if(import_sur_mesure.complet == 0){
                toastr.error(vue_instance.traduction('interface.import_sur_mesure.modele_import_inutilisable'));
				return;
			}

			vue_instance.import_sur_mesure = import_sur_mesure;
			vue_instance.import_sur_mesure.tables_jointes = JSON.parse(vue_instance.import_sur_mesure.tables_jointes);
		});

	},

	// Fonction permettant de vérifier la linéarité de création des élements en fonction des dépendances de création
	verification_dependances : function(dependances){

		var vue_instance = this;

		var nombre_elements = vue_instance.import_sur_mesure.tables_jointes.length + 1;

		var degres = {};

		degres[vue_instance.import_sur_mesure.type_element] = 0;

		vue_instance.import_sur_mesure.tables_jointes.forEach(function(table_jointe){
			degres[table_jointe.id] = 0;
		});

		for (const [type_element] of dependances) {
			degres[type_element]++;
		}

		const verification_linearite_dependance = [];

		$.each(degres,function(nom_element,degre){
			if (degre === 0) verification_linearite_dependance.push(nom_element);
		});

		var ordre_lancement_import = [];

		while (verification_linearite_dependance.length) {

			const derniere_dependance = verification_linearite_dependance.shift();
			nombre_elements--;
			ordre_lancement_import.push(derniere_dependance);

			for (const [enfant, parent] of dependances) {
				if (parent === derniere_dependance) {
					degres[enfant]--;
					if (degres[enfant] === 0) verification_linearite_dependance.push(enfant);
				}
			}
		}

		if(nombre_elements === 0){
			vue_instance.import_sur_mesure.ordre_lancement_import = ordre_lancement_import;
			return true;
		}

		return vue_instance.traduction('interface.import_sur_mesure.erreur_ordre_dependances');
	},

@endpush

@push('donnees_pour_vuejs_mounted')

	if(this.import_sur_mesure != null){
		this.valeur_par_defaut_affichage = this.import_sur_mesure.type_element;
		if(this.import_en_cours != null){

			if(this.import_en_cours.statut == 1 || this.import_en_cours.statut == 2){
				this.etape_import = 3;
				this.import_statut_en_cours = true;
			}

			else if(this.import_en_cours.statut == 3 || this.import_en_cours.statut == 4)
				this.etape_import = 4;

			else if(this.import_sur_mesure.champs.length > 0){
				this.etape_import = 1;
				if(this.import_sur_mesure.champs.filter(champ => champ.correspondance != 'aucune_correspondance').length > 0 &&
					this.verification_etape_suivante() == true)
					this.etape_import = 2;
			}
		}
	}
@endpush

@push('scripts')

	<script>
		$('body').on('dblclick', '#affichage_liste_{{$id_liste_import_sur_mesure}} .js_liste_ligne_selectionnable', function() {

			var import_sur_mesure_id = $(this).attr('element_id');

			vue_instance.charger_import_sur_mesure(import_sur_mesure_id);
		});
	</script>
@endpush
