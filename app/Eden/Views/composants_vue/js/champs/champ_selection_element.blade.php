<script>
const champ_selection_element = Vue.component('champ-selection-element', {
    template: `<div class="champ_selection_element" :id="id_random+'_ajout_modal'">
					<div>
						<input type="hidden" :name="name" v-model="modele[nom_sql]" />
						<template v-if="lecture_seule">
							<div class="affichage">
								<span class="css_enfant_cacher_texte css_lien_selection_element" :id="id_random+'_affichage_resultat'" v-html="affichage" v-show="modele[nom_sql] != '' && modele[nom_sql] !== undefined && modele[nom_sql] != null && modele[nom_sql] != 0"></span>
							</div>
						</template>
						<template v-else>
							<div class="champ" v-show="modele[nom_sql] == '' || modele[nom_sql] == undefined || modele[nom_sql] == null || modele[nom_sql] == 0">
								<span :id="'bouton_toggle_select_selection_element_'+id_random" class="css_input_recherche_selection_element css_background_couleur_primaire" @click="afficher_donnees">
									<i class="fas fa-search"></i>
									<i v-if="affichage_select" class="fas fa-chevron-up"></i>
									<i v-else class="fas fa-chevron-down"></i>
								</span>
								<dropdown v-if="affichage_select || affichage_recherche" ref="dropdown" :id="'select_selection_element_'+id_random" class="css_select_multiselection_element" @scroll.native="gestion_scroll_pagination">
									<div class="affichage_valeur" @click="affichage_element(donnee.id)" v-for="donnee in donnees_select" v-html="donnee.affichage_pour_recherche"></div>
									<div class="chargement" v-if="chargement_select">
										<img src="/eden/images/ajax_loader.gif" />
									</div>
									<div class="aucun_resultat" v-else-if="donnees_select.length == 0">
										@traduction('composant.champ_selection_element_multiple.aucun_resultat')
									</div>
								</dropdown>
								<input type="text" :id="'input_recherche_'+id_random" @input="chargement_valeur_recherche" placeholder="Rechercher..." v-model="recherche_en_cours" />
								<bouton-creation-pour-champ v-if="desactiver_creation_a_la_volee !== true && desactiver_creation_a_la_volee != '1'" 
									@rechargement_options="affichage_element($event)"
									:type_element="type_element_origine" :type_element_ajax="type_element" :nom_sql="nom_sql" ></bouton-creation-pour-champ>
							</div>
							<div class="affichage" v-show="modele[nom_sql] != '' && modele[nom_sql] !== undefined && modele[nom_sql] != null && modele[nom_sql] != 0">
								<span>
									<i class="fas fa-search css_input_recherche_selection_element"></i>
								</span>
								<span class="css_enfant_cacher_texte css_lien_selection_element" :id="id_random+'_affichage_resultat'" v-html="affichage"></span>
								<span class="css_enfant_cacher_texte css_lien_selection_element_delete" @click="supprimer_selection()"><span class="fa fa-times"></span></span>
							</div>
						</template>
					</div>
				</div>`,
	props: {
		modele: {},
		type_element: "",
		nom_sql: '',
		name: '',
		filtrage: [],
		lecture_seule: false,
		desactiver_creation_a_la_volee: false,
		type_element_origine:'',
	},
	data: function () {
		return {
			recherche_en_cours: '',
			affichage: '',
			derniere_info_recuperee: false,
			donnees_select: [],
			elements_a_charger: true,
			affichage_select: false,
			affichage_recherche: false,
			chargement_select : false,
			modele_element_selectionne: null,
			requete_recherche:false,
		}
	},
	computed: {
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
		affichage_element: async function(element_id) {
			var type_element = this.type_element;
			var nom_champ = this.nom_sql;

			if(element_id == '' || element_id == null || element_id == 0) {
				this.$set(this.modele,nom_champ,0);
				this.affichage = '';
				this.recherche_en_cours = '';
				this.modele_element_selectionne = null;
				return;
			}

			await this.recuperer_affichage_element(type_element,element_id).then((element) => {
				this.derniere_info_recuperee = element.id;
				this.modele_element_selectionne = element;
                this.$set(this.modele, nom_champ, element.id);
				this.affichage = element.affiche_lien_pour_select;
				this.recherche_en_cours = '';

				this.$root.$emit('selection-element',{
					modele : this.modele,
					element : element,
					type_element : this.type_element,
					nom_champ : this.nom_sql,
				});
                this.$emit('selection-element',{
                    id: element.id,
                    modele : this.modele,
                    element : element,
                    type_element : this.type_element,
                    nom_champ : this.nom_sql,
                });

				this.$parent.$forceUpdate();
				this.$root.$forceUpdate();
			});
		},
		recuperer_affichage_element: async function(type_element,element_id) {

			if(typeof this.$root.recuperer_affichage_element != 'function')
				return $.ajax({
					url: "/eden/element/"+type_element+"/"+element_id,
					dataType: "json"
				});

			return this.$root.recuperer_affichage_element(type_element, element_id);
		},
		supprimer_selection: function() {
			this.derniere_info_recuperee = '';

			if(this.modele[this.nom_sql] != '' && this.modele[this.nom_sql] != null && this.modele[this.nom_sql] != 0)
				this.$set(this.modele,this.nom_sql,null);

			this.$root.$emit('suppression-selection-element',{
				modele : this.modele,
				type_element : this.type_element,
				nom_champ : this.nom_sql,
			});

			this.affichage = '';
			this.modele_element_selectionne = null;
			var nom_champ = this.nom_sql;
			var id_random = this.id_random;
		},
		chargement_valeur_recherche: async function() {

			this.donnees_select = [];

			if(this.recherche_en_cours == ''){
				this.affichage_recherche = false;
				return;
			}
			
			this.affichage_recherche = true;

			if(this.requete_recherche !== false)
				this.requete_recherche.abort();

			this.chargement_select = true;

			var ids_a_eviter = this.$parent.$options.name == 'champ-multiple' ? structuredClone(this.$parent.modele[this.nom_sql]) : [];

			var filtrage = this.filtrage;

			this.requete_recherche = $.post({
				url: '/eden/element/recherche/'+this.type_element+'/'+this.recherche_en_cours,
				dataType: "json",
				data: {
					ids_a_eviter: ids_a_eviter,
					filtrage : filtrage,
					nombre_elements : 70,
					source: {
						type_element : this.type_element_origine,
						nom_sql : this.nom_sql,
						modele : this.modele,
					}
				},
			}).done(async (donnees) => {
				this.requete_recherche = false;
				this.elements_a_charger = false;
				this.donnees_select = await this.donnees_select.concat(donnees);
				this.chargement_select = false;
			});
		},
		afficher_donnees: function() {
			this.affichage_select = !this.affichage_select;
			if(this.affichage_select === true) {
				this.donnees_select = [];
				this.elements_a_charger = true;
				this.charger_donnees_select();
			}
		},
		charger_donnees_select : async function(){
			this.chargement_select = true;
			if(this.affichage_select === false)
				return;

			var ids_a_eviter = this.$parent.$options.name == 'champ-multiple' ? structuredClone(this.$parent.modele[this.nom_sql]) : [];
			
			this.donnees_select.forEach(function(element){
				ids_a_eviter.push(element.id);
			});

			var filtrage = this.filtrage;

			$.post({
				url: '/eden/element/recherche/'+this.type_element+'/%25',
				dataType: "json",
				data: {
					ids_a_eviter: ids_a_eviter,
					filtrage : filtrage,
					nombre_elements : 15,
					source: {
						type_element : this.type_element_origine,
						nom_sql : this.nom_sql,
						modele : this.modele,
					}
				},
			}).done(async (donnees) => {
				if(donnees.length === 0 || donnees.length < 15)
					this.elements_a_charger = false;
				this.donnees_select = await this.donnees_select.concat(donnees);
				this.chargement_select = false;
			});
		},
        vider_modele_ajax_multi : function(){
            this.modele[this.nom_sql] = null;
        },
		gestion_scroll_pagination : function(e){
			if(this.affichage_select && this.elements_a_charger && this.chargement_select === false && e.target.scrollTop + e.target.clientHeight >= e.target.scrollHeight) {
				this.charger_donnees_select();
			}
		},
	},
	mounted: function() {
		var instance = this;

		// on charge l'élément
		if(this.modele[this.nom_sql] != undefined && this.modele[this.nom_sql] != 0) {
			this.affichage_element(this.modele[this.nom_sql]);
		}
		$(document).on('click', function(e) {
			var target = $(e.target);
			
			if(instance.affichage_select && 
				!target.is($('#bouton_toggle_select_selection_element_'+instance.id_random).find('*').addBack())){
				instance.affichage_select = false;
			}
			else if(instance.affichage_recherche && 
				!target.is($('#input_recherche_'+instance.id_random).find('*').addBack())){
				instance.affichage_recherche = false;
				instance.recherche_en_cours = '';
			}
		});
	},
	created: function() {

		this.$watch(function(){

			return this.modele ? this.modele[this.nom_sql] : undefined;

		}, function(valeur) {

			if(this.derniere_info_recuperee != valeur) {

				if(valeur == '' || valeur == null || valeur == undefined || valeur == 0)
					this.supprimer_selection();
				else
					this.affichage_element(valeur);
			}

			this.derniere_info_recuperee = valeur;
		});
	},
	watch: {
		type_element: {
			handler : function(){
				this.supprimer_selection();
			}
		},
	}
});
</script>
