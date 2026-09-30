@extends('eden::templates.template')

@section('title') Menus @stop

@section('content')
	
	
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('nom' => 'Menus')
			)])

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
							<h4>
								Menus
							</h4>
							<div class="nav-item dropdown dropdown_hover dropleft d-inline-flex ml-auto" aria-haspopup="true" aria-expanded="false">
								<div class="css_ajouter_element mr-3">
									<i class="css_action_icon fa fa-fw fa-plus-square" data-toggle="tooltip" data-placement="left" title="Ajouter"></i>
								</div>
								<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
									<span class="dropdown-item" @click="ajout_element('menus_categories')"><i class="fa fa-fw fa-plus-square"></i> Nouvelle catégorie</span>
									<span class="dropdown-item" @click="ajout_element('menus_liens')"><i class="fa fa-fw fa-plus-square"></i> Nouveau lien</span>
								</div>
							</div>
							<a style="margin-left: 10px;" target="_blank" href="eden/parametrage/traduction?categorie=7" data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element mr-3" data-original-title="Affichage traduction global">
								<i class="css_action_icon fas fa-flag" aria-hidden="true"></i>
							</a>
						</div>
						<draggable @change="changement_ordre_menus" ghost-class="menus_draggable_ghost" drag-class="menus_draggable_drag" v-model="menus" :group="{ name: 'menus', pull: true, put: true }"
							@start="debut_drag" @end="fin_drag" swap-threshold=1 animation=150 :scroll="true" :force-fallback="true" :force-auto-scroll-fallback="true" class="card-body css_parametrage_menu">
							<div :key="(menu.type_element === 'menus_categories' ? 'menus_categories_' : 'menu_liens_') + menu.id" :data-type="menu.type_element" v-for="(menu, index_menu) in menus" :class="'row menus_menu '+(menu.desactive ? 'css_inactif': '')">
								<div class="col-md-12">
									<template v-if="menu.type_element === 'menus_categories'">
										<div :class="{ 'row': true, 'css_inactif': menu.desactive }" style="padding: 9px; ">
											<div class="col-md-12"><b>Catégorie</b> :
												<span v-html="$root.traduction(menu.index_traduction + '.nom')"></span>
												<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" title="modifier" @click="ajout_element('menus_categories', menu)" v-show="!menu.desactive">
													<i class="css_action_icon secondaire far fa-edit"></i>
												</span>
												<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" title="Nouveau lien" @click="ajout_element('menus_liens', null, menu)" v-show="!menu.desactive">
													<i class="css_action_icon secondaire far fa fa-fw fa-plus-square"></i>
												</span>
												<profil-droits-divers type="menus_categories" :index="menu.id" :bouton="true" :type_profil="extranet == 1 ? 'extranet' : 'eden'">
													<template v-slot:bouton="{gestion_profil,profil_droits_divers}">
														<span class="css_ajouter_element ml-3" style="position:relative" data-toggle="tooltip" data-placement="left" title="Gestion des profils" @click="gestion_profil()" v-show="!menu.desactive">
															<i class="css_action_icon secondaire fas fa-users"></i>
															<span v-if="profil_droits_divers.profils.length > 0" class="icone_droits_profils">
																@{{ profil_droits_divers.profils.length }}
															</span>
														</span>
													</template>
												</profil-droits-divers>
												<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" title="Supprimer" @click="supprimer_element('menus_categories', menu)" v-show="!menu.desactive">
													<i class="css_action_icon secondaire fa fa-fw fa-trash"></i>
												</span>
												<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" :title="menu.desactive ? 'Activer' : 'Désactiver'" @click="activation_menu(menu, !menu.desactive)">
													<i :class="'css_action_icon secondaire far fa-eye' + (menu.desactive ? '-slash' : '')"></i>
												</span>
											</div>
										</div>
										<div class="row">
											<draggable v-model="menu.sous_menus" ghost-class="menus_draggable_ghost" drag-class="menus_draggable_drag" swap-threshold=1 
												animation=150 @start="debut_drag()" @end="fin_drag()" @change="changement_ordre_menus($event, index_menu, menu.id)" 
												:group="{ name: 'sous_menus', pull: true, put: droit_depot_sous_menu }" :scroll="true" :force-auto-scroll-fallback="true" 
												:force-fallback="true" class="col-md-12">
												<div :key="'menu_liens_' + sous_menu.id" :class="{ 'row': true, 'menus_sous_menu' : true, 'css_inactif': sous_menu.desactive }" v-for="(sous_menu, index) in menu.sous_menus">

													<div class="col-md-12">|<b style="padding-left: 50px;">Lien</b> :
														<span v-html="$root.traduction(sous_menu.index_traduction + '.nom')"></span>
														<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" title="modifier" @click="ajout_element('menus_liens', sous_menu, menu)" v-show="!sous_menu.desactive">
															<i class="css_action_icon mineur far fa-edit"></i>
														</span>
														<profil-droits-divers type="menus_liens" :index="sous_menu.id" :bouton="true" :type_profil="extranet == 1 ? 'extranet' : 'eden'">
															<template v-slot:bouton="{gestion_profil,profil_droits_divers}">
																<span class="css_ajouter_element ml-3" style="position:relative" data-toggle="tooltip" data-placement="left" title="Gestion des profils" @click="gestion_profil()" v-show="!menu.desactive">
																	<i class="css_action_icon mineur secondaire fas fa-users"></i>
																	<span v-if="profil_droits_divers.profils.length > 0" class="icone_droits_profils">
																		@{{ profil_droits_divers.profils.length }}
																	</span>
																</span>
															</template>
														</profil-droits-divers>
														<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" title="Supprimer" @click="supprimer_element('menus_liens', sous_menu, menu)" v-show="!sous_menu.desactive">
															<i class="css_action_icon mineur far fa-trash-alt"></i>
														</span>
														<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" :title="sous_menu.desactive ? 'Activer' : 'Désactiver'" @click="activation_menu(sous_menu, !sous_menu.desactive)">
															<i :class="'css_action_icon secondaire far fa-eye' + (sous_menu.desactive ? '-slash' : '')"></i>
														</span>
													</div>
												</div>
											</draggable>
										</div>
									</template>
									<template v-else>

										<b>Lien</b> :
										<span v-html="$root.traduction(menu.index_traduction + '.nom')"></span>
										<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" title="Modifier" @click="ajout_element('menus_liens', menu)" v-show="!menu.desactive">
											<i class="css_action_icon secondaire far fa-edit"></i>
										</span>
										<profil-droits-divers type="menus_liens" :index="menu.id" :bouton="true" :type_profil="extranet == 1 ? 'extranet' : 'eden'">
											<template v-slot:bouton="{gestion_profil,profil_droits_divers}">
												<span class="css_ajouter_element ml-3" style="position:relative" data-toggle="tooltip" data-placement="left" title="Gestion des profils" @click="gestion_profil()" v-show="!menu.desactive">
													<i class="css_action_icon secondaire fas fa-users"></i>
													<span v-if="profil_droits_divers.profils.length > 0" class="icone_droits_profils">
														@{{ profil_droits_divers.profils.length }}
													</span>
												</span>
											</template>
										</profil-droits-divers>
										<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" title="Supprimer" @click="supprimer_element('menus_liens', menu)" v-show="!menu.desactive">
											<i class="css_action_icon secondaire far fa-trash-alt"></i>
										</span>
										<span class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="left" :title="menu.desactive ? 'Activer' : 'Désactiver'" @click="activation_menu(menu, !menu.desactive)">
											<i :class="'css_action_icon secondaire far fa-eye' + (menu.desactive ? '-slash' : '')"></i>
										</span>
									</template>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal ajout élément -->
	<template v-if="modale_edition_menu">
		<transition name="modal">
			<div class="modal-mask">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Gestion de menu</h5>
							<button type="button" @click="modale_edition_menu = false">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body css_form">
							<formulaire ref="formulaire" :nom_formulaire="type_element"></formulaire>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="modale_edition_menu = false">Fermer</button>
							<button type="button" class="btn btn-primary" @click="enregistrer_element">Enregistrer</button>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</template>

@endsection

@push('donnees_pour_vuejs_data')
	menus: {!! collect($menus) !!},
	type_element: '',
	menu_principal : null,
	index_nouveau_menu: null,
	categorie_nouveau_menu: null,
	modele: {},
	extranet: 0,
	modale_edition_menu: false,
	modele_par_defaut_menus_liens: {!! modele_par_defaut('menus_liens') !!},
	modele_par_defaut_menus_categories: {!! modele_par_defaut('menus_categories') !!},
@endpush

<script>

@push('donnees_pour_vuejs_methods')

	debut_drag : function() {

		document.body.classList.add('menus_drag_en_cours');
	},

	fin_drag : function() {
		
		document.body.classList.remove('menus_drag_en_cours');
	},

	droit_depot_sous_menu : function(to, from, element_deplace) {

		if(element_deplace.dataset.type === 'menus_categories')
			return false;

		return true;
	},

	activation_menu: function(menu, valeur) {
		
		loading(true);
		menu.desactive = valeur;
		
		if(menu.type_element === 'menus_categories') {
			
			menu.sous_menus.map(function(sous_menu) {
				
				sous_menu.desactive = valeur;
			});
		}

		$.ajax({

			url: "{{ route('parametrage.menu.desactivation_menu') }}",
			method: 'post',
			dataType: 'json',
			data: {
				'type_element': menu.type_element,
				'id_menu' : menu.id,
				'valeur' : valeur
			}
		}).done((donnees) => {
			
			toastr.success(this.traduction('messages.js.parametrage_menus.changement_etat'))
			loading(false);
		});
	},
	
	ajout_element: function(type_element, modele = null, categorie_parent = null) {

		this.type_element = type_element;

		this.categorie_nouveau_menu = categorie_parent ?? null;
		
		if(!modele)
			this.index_nouveau_menu = categorie_parent !== null ? categorie_parent.sous_menus?.length ?? 1 : this.menus.length;

		this.modele = modele ?? structuredClone(this['modele_par_defaut_'+type_element]);

		this.$once('formulaire_charger',()=>{

            this.$set(this.$refs.formulaire, 'element', structuredClone(this.modele));
        });

		this.modale_edition_menu = true;
	},

	enregistrer_element: async function(){

		loading(true);

		var informations_supp = {ordre : this.index_nouveau_menu, id_categorie_parent : this.categorie_nouveau_menu?.id ?? null, id_menu_parent : this.menu_principal};

		var donnees = await this.$refs.formulaire.enregistrer(informations_supp);

		loading(false);

		if(donnees.retour !== true) {

			await erreur(donnees.retour);
			return;
		}

		if(this.modele.id){

			this.type_element = this.categorie_nouveau_menu = this.index_nouveau_menu = this.modele = null;
			this.modale_edition_menu = false;
			return;
		}

		var element = structuredClone(donnees.element);
		element.type_element = this.type_element;
		
		this.$root.traductions_valeurs[element.index_traduction + '.nom'] = element.nom;

		if(this.type_element === 'menus_categories')
			element.sous_menus = [];

		if(this.categorie_nouveau_menu !== null)
			this.categorie_nouveau_menu.sous_menus.push(element);
		else
			this.menus.push(element);

		this.type_element = this.categorie_nouveau_menu = this.index_nouveau_menu = this.modele = null;
		this.modale_edition_menu = false;
	},
	
	supprimer_element: function(type_element, menu, categorie = null) {
		
		loading(true);

		$.ajax({

			url: "/eden/element/" + type_element + '/' + menu.id + '/supprimer',
		}).done((donnees) => {
			
			if(categorie == null)
				this.menus.splice(this.menus.indexOf(menu), 1);
			else
				categorie.sous_menus.splice(categorie.sous_menus.indexOf(menu), 1);

			loading(false);
			toastr.success(this.traduction('messages.js.parametrage_menus.element_supprime'));
		});
	},

	changement_ordre_menus: function(evt, index_categorie_parent = null, id_categorie_parent = null) {

		loading(true);
		const type_evt = evt.moved ? 'moved' : (evt.added ? 'added' : 'removed');
		const ordre_menus = {'menus_categories' : {}, 'menus_liens' : {}};
		const menus_modifies = index_categorie_parent !== null ? this.menus[index_categorie_parent].sous_menus : this.menus;

		const ancien_index = evt[type_evt].oldIndex;
		const nouvel_index = evt[type_evt].newIndex;
		let index_min = null;
		let index_max = null;

		if(type_evt === 'moved'){

			index_min = ancien_index < nouvel_index ? ancien_index : nouvel_index;
			index_max = ancien_index > nouvel_index ? ancien_index : nouvel_index;
		}
		
		menus_modifies.forEach((menu, index) => {

			if(
				(type_evt === 'removed' && index < ancien_index) ||
				(type_evt === 'added' && index < nouvel_index) ||
				(type_evt === 'moved' && (index < index_min || index > index_max))
			)
				return;

			let ordre_menu = {ordre : index};

			if(id_categorie_parent === null && menu.id_menu_parent != this.menu_principal)
				ordre_menu = {...ordre_menu, id_menu_parent : this.menu_principal};

			if(menu.type_element === 'menus_liens' && id_categorie_parent !== null && menu.id_categorie_parent != id_categorie_parent)
				ordre_menu = {...ordre_menu, id_categorie_parent : id_categorie_parent, id_menu_parent : null};
			else if(menu.type_element === 'menus_liens' && id_categorie_parent === null && menu.id_categorie_parent != id_categorie_parent)
				ordre_menu = {...ordre_menu, id_categorie_parent : null};

			ordre_menus[menu.type_element][menu.id] = ordre_menu;
		});

		if(!ordre_menus)
			return;

		$.ajax({

			url: "{{ route('parametrage.menu.changement_ordre_menus') }}",
			method: 'post',
			dataType: 'json',
			data: {
				'ordre_menus': ordre_menus,
			}
		}).done((donnees) => {
			
			toastr.success(this.traduction('messages.js.parametrage_menus.ordre_enregistre'))
			loading(false);
		});
	},
	
@endpush

@push('donnees_pour_vuejs_mounted')

	this.menus.forEach((menu) => {

		if(menu.id_menu_parent && this.menu_principal === null)
			this.menu_principal = menu.id_menu_parent;
	});

	$.ajax({

		url: "/eden/element/menus/" + this.menu_principal,
		method: 'get',
		dataType: 'json',
	}).done((donnees) => {
		
		this.extranet = donnees.extranet;
	});
@endpush

</script>