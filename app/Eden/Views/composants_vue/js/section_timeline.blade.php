<script>
const section_timeline = Vue.component('section-timeline', {
    template: `<div class="section_timeline">
		<div class="ibox">
		<div class="ibox-title">
			<h5 class="titre d-flex align-items-center">
				@traduction('composant.timeline.titre')
				<select class="filtre_elements" v-model="filtre_selectionne" @change="charge_donnees(1)">
					<option value="0">@{{ $root.traduction('composant.timeline.filtre_aucun') }}</option>
                    <template v-for="(modeles,type_element) in elements">
                      <option v-for="modele in modeles" :value="{type_element : type_element,element_id : modele.id}" >@{{ $root.traduction('composant.timeline.filtre_'+type_element) }} @{{ modele.nom }}</option>
                    </template>
				</select>

                <filtres ref="filtres" class="filtres_timeline" :appliquer_recherche_avancee="false" :valeurs_filtres="valeurs_filtres" :filtres="filtres"></filtres>

				<span class="ml-auto">
					<template v-for="timeline in timelines_possibles">
						<span v-if="timeline.desactivee != 1 && timeline.id_valeur != 0" @click="creer(timeline.id_valeur);" class="css_ajouter_element ml-3" :title="timeline.valeur"  data-toggle="tooltip" data-placement="top">
							<i :class="'css_action_icon mineur '+ (timeline.icone == null || timeline.icone == \'\' ? 'fas fa-stream' : timeline.icone)" :style="(timeline.couleur_police != null && timeline.couleur_police != \'\' ? 'color: '+timeline.couleur_police+'!important;' : \'\' )+' '+(timeline.couleur_fond != null && timeline.couleur_fond != \'\' ? 'background-color: '+timeline.couleur_fond+'!important;border-color: '+timeline.couleur_fond+'!important;' : \'\')"></i>
						</span>
					</template>
					<span @click="filtres_timeline = !filtres_timeline" class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="top" :title="$root.traduction('composant.timeline.filtres')">
						<i class="css_action_icon secondaire fa fa-fw fa-filter"></i>
					</span>
					<span @click="parametrage_timeline = !parametrage_timeline" class="css_ajouter_element ml-3" data-toggle="tooltip" data-placement="top" :title="$root.traduction('composant.timeline.parametrages')">
						<i class="css_action_icon fas fa-cog"></i>
					</span>
				</span>
			</h5>
		</div>
		<div class="card-header" v-show="filtres_timeline">
			<h5 class="mb-2">@traduction('composant.timeline.filtrer_par')</h5>
			<div class="container">
				<div class="row">
					<div class="col-sm-3">
						@traduction('composant.timeline.type_echanges')
					</div>

					<div class="col-sm-9">
						<template v-for="timeline in timelines_possibles">
							<span class="css_pointer badge mb-1 mr-1" v-if="timeline.desactivee == null || timeline.desactivee == 0 || timeline.desactivee == '' || timeline.desactivee == '0'" :class="{ 'badge-success' : filtres_par_types_echanges.includes(timeline.id_valeur) }" @click="ajoute_filtre(timeline.id_valeur)">
								<span>
									@{{ timeline.valeur }}
								</span>
							</span>
						</template>
					</div>
				</div>
			</div>
		</div>
		<div class="card-header" v-show="parametrage_timeline">
			<div class="row">
				<div class="col-sm-4">@traduction('composant.timeline.nombre_echanges_par_page')</div>
				<div class="col-sm-4"><input type="number" @change="charge_donnees(1)" v-model="nb_elements_par_page" @wheel.prevent @keydown.up.prevent @keydown.down.prevent></div>
			</div>
		</div>

		<div class="ibox-content inspinia-timeline">

			<div class="timeline-item" :key="echange.id" v-for="(echange, index) in echanges">
				<div class="row">
					<div class="col-1 css_timeline_date">
						<i class="css_timeline_icone" :style="(recuperer_timeline_possible(echange.type).couleur_police != null && recuperer_timeline_possible(echange.type).couleur_police != '' ? 'color: '+recuperer_timeline_possible(echange.type).couleur_police+'!important;' : '' )+(recuperer_timeline_possible(echange.type).couleur_fond != null && recuperer_timeline_possible(echange.type).couleur_fond != '' ? 'background: '+recuperer_timeline_possible(echange.type).couleur_fond+'!important;' : '')" :class="recuperer_timeline_possible(echange.type).icone ? recuperer_timeline_possible(echange.type).icone : 'fas fa-stream'" ></i>
					</div>
					<div class="col-8 css_timeline_details no-top-border css_timeline_item_pos_relative">
						<p class="m-b-xs">
							<strong v-html="echange.chaine_affichage"></strong>
							<span class="css__lien" @click="modifier(echange)">@traduction('composant.timeline.modifier')</span>
						</p>
						<p v-for="(affichage,type_element) in echange.informations_table">
                            <b class="text-capitalize">@{{ $root.traduction('tables_libres.'+type_element+'.element') }}</b> : <span v-html="affichage"></span>
                        </p>
						
						<p v-show="echange.objet !== null"><b>@{{ echange.objet }}</b></p>
						<template v-if="echange.description != undefined">
							<p :class="{ 'css_commentaire_timeline' : compter_ligne_timeline(echange) > 3, 'deploy' : echange.deplier === true}" v-html="$options.filters.nl2br(echange.description_affichage)"></p>
							<p v-if="echange.deplier === true && compter_ligne_timeline(echange) > 3">
								<span class="css__lien" @click="deplier_echange(echange)">@traduction('composant.timeline.afficher_moins')</span>
							</p>

							<p v-if="compter_ligne_timeline(echange) > 3 && echange.deplier === false">
								<span class="css__lien css_commentaire_bouton_lire_plus" @click="deplier_echange(echange)">@traduction('composant.timeline.afficher_plus')</span>
							</p>
						</template>


						<span class="retour_traite" v-if="echange.retour_traite == 1">
							<i class="fa fa-fw fa-check"></i> @traduction('composant.timeline.retour_traite') [<a href="javascript:;" @click="enregistrer_retour(echange.id, 0)">@traduction('composant.timeline.marquer_comme_non_traite')</a>]
						</span>
						@if(fonctionnalite('timeline_info_traitement') === true)
                            <span class="retour_non_traite" v-else>
                                <i class="fas fa-hourglass-half"></i> @traduction('composant.timeline.retour_en_attente_de_traitement') [<a href="javascript:;" @click="enregistrer_retour(echange.id, 1)">@traduction('composant.timeline.marquer_comme_traite')</a>]
                            </span>
						@endif

					</div>
				</div>
			</div>

			<nav class='css_nav_pagination_listes' >
				<ul class="pagination css_pagination_perso">
					<li class="page-item" v-show="page_active > 1">
						<a class="page-link" @click="charge_donnees(1)"><span>@traduction('composant.timeline.premiere_page')</span></a>
					</li>
					<li :class="{'page-item':true, 'active':(page === page_active)}" v-for="page in total_echanges" v-show="page >= page_active-4 && page <= page_active+4">
						<a class="page-link" @click="charge_donnees(page)">@{{ page }}</a>
					</li>
					<li class="page-item" v-show="page_active < total_echanges">
						<a class="page-link" @click="charge_donnees(total_echanges)"><span>@traduction('composant.timeline.derniere_page')</span></a>
					</li>
				</ul>
			</nav>

			<div class="row">
				<div class="col-md-4 offset-md-4 footer_timeline" v-html="total_echanges + ' pages'" ></div>
			</div>
			<br>

		</div>

		<!-- modale edition échange -->

		<template v-if="modal_type_echange">
            <transition name="modal">
                <div class="modal-mask">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">@traduction('composant.timeline.titre_modal')</h5>
                                <button type="button" class="close" @click="modal_type_echange = false" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body css_form">
                                <formulaire ref="formulaire" nom_formulaire="echange"></formulaire>
                            </div>
                            <div class="modal-footer" v-if="$refs.formulaire != null">
                                <button type="button" class="btn btn-secondary" @click="modal_type_echange = false">@traduction('composant.timeline.fermer')</button>
                                <button type="button" class="btn btn-danger" @click="supprimer()" v-show="$refs.formulaire.element.id != undefined">@traduction('composant.timeline.supprimer')</button>
                                <div class="btn btn-primary" @click="enregistrer()">@traduction('composant.timeline.enregistrer')</div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </template>

	</div>

	</div>`,
   props: {
		type_element_parent: {},
		parent_id: 0,
		valeurs_par_defaut: {},
        filtres_appliques: {},
	},
	data: function () {
		return {
			@yield('donnees_pour_vuejs_data')
        	@stack('donnees_pour_vuejs_data')
			echanges: {},
            elements: {},

            total_echanges: 0,
            page_active: 1,
            nb_elements_par_page: 5,

			filtre_selectionne: 0,
			filtres_par_types_echanges : [],
            valeurs_filtres : [],
            filtres : [],

            filtres_timeline : false,
            parametrage_timeline : false,

			timelines_possibles: {},

			modele_par_defaut_echange : {},
            modal_type_echange : false,

            requete_mise_a_jour : false,
            initialisation : true,
		}
	},
	mounted: function() {

		this.timelines_possibles = this.$root.valeurs_listes_formatees[33];

        @yield('donnees_pour_vuejs_mounted')
        @stack('donnees_pour_vuejs_mounted')
	},
	computed: {

        @yield('donnees_pour_vuejs_computed')
        @stack('donnees_pour_vuejs_computed')

		id_random: function() {

			length = 15;

			var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

			if (! length) {
				length = Math.floor(Math.random() * chars.length);
			}

			var str = '';
			for (var i = 0; i < length; i++) {
				str += chars[Math.floor(Math.random() * chars.length)];
			}

			return str;
		},
	},
	methods: {

        @yield('donnees_pour_vuejs_methods')
        @stack('donnees_pour_vuejs_methods')

		recuperer_timeline_possible: function(type) {

			// On a pas de type, dans le cas présent, on retour sans valeur par défaut
			if (type == null || type == 0 || type == undefined || type == 'undefined')
				return this.retourne_timeline_par_defaut();

			var timeline_possible_selectionne = this.retourne_timeline_par_defaut();

			// On boucle sur les timelines possibles et on retourne le bon
			$.each(this.timelines_possibles,function(index,timeline_possible){
				if(timeline_possible.id_valeur == type)
					timeline_possible_selectionne = timeline_possible;
			});

			// Si on arrive ici, on à pas trouvé d'occurence, on retourne alors la timeline par défaut ( sans valeur )
			return timeline_possible_selectionne;
		},

		retourne_timeline_par_defaut: function(){

			$.each(this.timelines_possibles,function(index,timeline_possible){
				if(timeline_possible.id_valeur == 0)
					timeline_possible_selectionne = timeline_possible;
			});

			return {
				valeur: '',
				couleur_police: '',
				couleur_fond: '',
				couleur: '',
				icone: '',
				desactivee: 0,
				id_liste_choix: 33,
				id_valeur: '',
			};
		},

		compter_ligne_timeline: function(echange) {

			if(!echange.description)
				return 0;

			if(echange.description == undefined)
				return 0;
			
			// Le nombre de 100 caractères par ligne est arbitraire
			// mais le but étant de ne pas afficher tout le texte si il est trop long, on n'a pas besoin d'une grande précision.
			return echange.description.length ? echange.description.length / 100 : 0;
	    },

		deplier_echange: function(echange) {

			if(echange.description != undefined) {

				if(echange.deplier === true) {

					echange.deplier = false;
				} else if (echange.deplier === false) {

					echange.deplier = true;
				} else {

					echange.deplier = false;
				}
			}
		},
		creer: function(type) {

            this.$once('formulaire_charger',() => {
                for(var cle in this.modele_par_defaut_echange) {
                    this.$refs.formulaire.element[cle] = this.modele_par_defaut_echange[cle];
                }

                for(var cle in this.valeurs_par_defaut) {
                    this.$refs.formulaire.element[cle] = this.valeurs_par_defaut[cle];
                }

                this.$refs.formulaire.element.type_element = this.type_element_parent;
                this.$refs.formulaire.element.element_id = this.parent_id;
                this.$refs.formulaire.element.type = type;
                this.$forceUpdate();
            });

            this.modal_type_echange = true;
		},

		modifier: function(echange) {

            this.$once('formulaire_charger',() => {
                this.$refs.formulaire.element = echange;
                this.$forceUpdate();
            });

            this.modal_type_echange = true;
		},

		enregistrer: async function() {

            var component = this;

            // On afficher le loader
            loading(true);

            var donnees = await component.$refs.formulaire.enregistrer();

            if(donnees.retour === true){
                component.charge_donnees();
				component.modal_type_echange = false;
            }

            loading(false);
		},

		supprimer: async function() {

            var composant = this;

			if(!await confirm_eden(composant.$root.traduction('composant.timeline.confirmation_suppression')))
				return;

			loading(true);

			$.get({

				url: "/eden/element/echange/"+this.$refs.formulaire.element.id+"/supprimer",
				dataType: "json",
				method: "get",
			}).done(async function(donnees) {

				loading(false);

				if(donnees.retour !== true) {

					await erreur(donnees.retour);
					return;
				}

				composant.charge_donnees();

				composant.modal_type_echange = false;

			});
		},

		enregistrer_retour: function(echange_id, retour_traite) {

			loading(true);

			var composant = this;
			var id_random = this.id_random;

			$.post({

				url: "/eden/element/echange/"+echange_id+"/enregistrer",
				dataType: "json",
				method: "post",
				data: {retour_traite:retour_traite},
			}).done(async function(donnees) {

				loading(false);

				if(donnees.retour !== true) {

					await erreur(donnees.retour);
					return;
				}

				composant.charge_donnees();
			});
		},

		charge_donnees: function(page = null) {

			var type_element_parent = this.type_element_parent;
			var element_parent_id = this.parent_id;

            if(this.requete_mise_a_jour !== false)
                this.requete_mise_a_jour.abort();

            if(page == null)
                page = this.page_active;
            else
                this.page_active = page;

			var data = {
                type_element: type_element_parent,
                id_element: element_parent_id,
                page: page,
                filtres : this.filtres_par_types_echanges,
                nb_elements_par_page: this.nb_elements_par_page,
                filtres_appliques: this.filtres_appliques,
                initialisation: this.initialisation,
                filtres_erp: this.valeurs_filtres,
                filtre_selectionne: this.filtre_selectionne
            };

			var url = "{{route('timeline.chargement_donnees', [], false)}}";

			$('.inspinia-timeline').css('opacity', '0.3');

			// on va chercher la liste des échanges
            this.requete_mise_a_jour = $.post({
				url: url,
				dataType: "json",
				data: data,
			}).done((retour) => {

                this.total_echanges = retour.total_echanges;
                this.echanges = retour.donnees;
                this.elements = retour.elements;

                this.requete_mise_a_jour = false;

				if(this.initialisation) {
                    this.filtres = retour.filtres_a_envoyer;
                    this.modele_par_defaut_echange = retour.modele_par_defaut;
                    this.initialisation = false;
                }

				$('.inspinia-timeline').css('opacity', '1');
			});

		},

		ajoute_filtre: function(key) {

			var index = this.filtres_par_types_echanges.findIndex(filtre => filtre == key);

			if(index == -1)
				this.filtres_par_types_echanges.push(key);
			else
				this.filtres_par_types_echanges.splice(index, 1);

            this.filtre_selectionne = 0;

			this.charge_donnees(1);
		},

	},
	created: function() {

		this.charge_donnees(1);

        this.$on('changement_filtres',(nouvelles_valeurs) => {

            this.$set(this,'valeurs_filtres',nouvelles_valeurs);
            this.filtre_selectionne = 0;

            this.charge_donnees();
        });
	},

	watch:{

        @yield('donnees_pour_vuejs_watch')
        @stack('donnees_pour_vuejs_watch')

	},
});
</script>
