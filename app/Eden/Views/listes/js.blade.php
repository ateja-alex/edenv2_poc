@push('donnees_pour_vuejs_data')

	documents_gescom: {!! collect(\App\Eden\Variables::$documents_gescom) !!},

@endpush

@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Permet de faire un tri sur les colonnes de la liste
	 *
	 */
	change_tri: function(colonne, groupement_id = null) {

		if(colonne.tri_desactive === 1)
			return;

		var tri = groupement_id != null ? colonne.id + '_' + groupement_id : colonne.id;

		if(this.liste.options_liste.tri == tri)
			this.liste.options_liste.direction_tri = this.liste.options_liste.direction_tri == 1 ? 0 : 1;
		else {
			this.liste.options_liste.tri = tri;
			this.liste.options_liste.direction_tri = 0;
		}

		this.actualisation_filtres();
	},

	/**
	 *
	 * Permet de modifier la page en cours avec la pagination
	 *
	 */
	change_page: function(page) {
		this.liste.options_liste.page = page;
		this.actualisation_filtres();
	},

	/**
	 *
	 * Permet d'actualiser la liste avec les nouveaux filtres
	 *
	 */
	actualisation_filtres: async function(recuperer_les_ids_uniquement = false, callback = function(){return true;}) {

		var id_liste = this.id_liste;
		$('.css_actualisation_liste').width($('#liste_elements_'+id_liste).outerWidth());
		$('#liste_'+id_liste+' .css_actualisation_liste').hide();

        if(this.liste.page_colonnes){
            for (const [colonne_id, pages] of Object.entries(this.liste.page_colonnes)) {
                this.liste.page_colonnes[colonne_id] = 1;
            }
        }

		if(this.liste.kanban_colonnes) {
			this.parametres_liste.nombre_colonnes_garder = this.liste.kanban_colonnes.length;
		}

		// on actualise
		await this.actualiser(recuperer_les_ids_uniquement, callback);
	},

	/**
	 *
	 * /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\
	 * /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\
	 * WARNING : Attention ne pas appeller cette méthode directement !
	 * Il faut passer par actualisation_filtres()
	 * /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\
	 * /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\
	 *
	 */
	actualiser: async function(recuperer_les_ids_uniquement = false, callback) {

		var id_liste = this.id_liste;

		var vue_contexte = this;

		if(this.liste.requete_actualisation_en_cours !== undefined)
			this.liste.requete_actualisation_en_cours.abort();

		vue_contexte.$forceUpdate();
		$('#liste_elements_'+id_liste).addClass('css_actualisation_ajax_en_cours');

		$('.js_liste_ligne_selectionnee_'+id_liste).removeClass('js_liste_ligne_selectionnee_'+id_liste).removeClass('css_liste_ligne_selectionnee');

		var parametres = structuredClone(this.parametres_liste);

		if(recuperer_les_ids_uniquement)
			parametres.recuperer_les_ids_uniquement = recuperer_les_ids_uniquement;

		vue_contexte.liste['requete_actualisation_en_cours'] = $.post({

			url: "/eden/liste/"+id_liste,
			dataType: "json",
			method: 'POST',
			data: parametres
		})

		.fail(function(xhr, status, error) {

            if(xhr.responseJSON == undefined)
                return;

            vue_contexte.liste['requete_actualisation_en_cours'] = undefined;

			vue_contexte.liste.erreur_ajax = xhr.responseJSON.message;
			loading(false);

			toastr.error(vue_instance.$root.traduction('interface.listes.erreur_inattendue_survenue'));
		})

		.done(async (donnees) => {

            vue_contexte.liste['requete_actualisation_en_cours'] = undefined;

            await vue_contexte.traitement_donnees_liste(donnees, callback);
		});

		return new Promise(async (resolve, reject) => {
			await vue_contexte.liste['requete_actualisation_en_cours'];
			resolve(true);
		});
	},

	/**
	 *
	 * Applique les données d'une liste, qu'elles viennent d'une actualisation ou de l'initialisation
	 *
	 */
	traitement_donnees_liste: async function(donnees, callback) {

		var vue_contexte = this;

		var id_liste = this.id_liste;

            if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			if(vue_contexte.liste.chargement_initial_a_effectuer === true){

				this.$root.$emit('liste_'+this.id_liste+'_initialise');

				vue_contexte.liste.chargement_initial_a_effectuer = false;

			}

			$('#liste_elements_'+id_liste).removeClass('css_actualisation_ajax_en_cours');

			if(donnees.recuperer_les_ids_uniquement == true){

				vue_contexte.liste.ids = donnees.ids;

				if(callback != undefined && callback !== false)
					callback(id_liste);

				return;
			}

			vue_contexte.liste.type_element_options = donnees.type_element_options;

			if(donnees.type_element !== undefined) {
				vue_contexte.liste.type_element = donnees.type_element;
				vue_contexte.type_element = donnees.type_element;
			}

			vue_contexte.liste.lignes = donnees.lignes;
			vue_contexte.liste.options_liste = donnees.options_liste;

			if(donnees.droits_liste !== undefined)
				vue_contexte.liste.droits_liste = donnees.droits_liste;

			if(donnees.modele_liste_libre !== undefined)
				vue_contexte.liste.modele_liste_libre = donnees.modele_liste_libre;

			if(this.$root.moi_extranet != null){
				if(this.liste.options_liste.filtres && this.liste.options_liste.filtres.length > 0)
					localStorage['filtres_liste_'+this.id_liste] = JSON.stringify(this.liste.options_liste.filtres);
				else if(localStorage['filtres_liste_'+this.id_liste])
					delete localStorage['filtres_liste_'+this.id_liste];
			}

			if(donnees.fiche !== undefined)
				vue_contexte.liste.fiche = donnees.fiche;

			vue_contexte.liste.colonnes = donnees.colonnes;
			vue_contexte.liste.ids = donnees.ids;
			vue_contexte.liste.calculs = donnees.calculs;
			vue_contexte.liste.nombre_elements = donnees.nombre_elements;
			vue_contexte.liste.nombre_elements_nombres = donnees.nombre_elements_nombres;
			vue_contexte.liste.nombre_elements_nombres_sans_filtres = donnees.nombre_elements_nombres_sans_filtres;

			if(donnees.kanban_colonnes !== undefined) {
				vue_contexte.liste.kanban_nombre_elements_affiches = donnees.kanban_nombre_elements_affiches;
				vue_contexte.liste.kanban_colonnes = donnees.kanban_colonnes;
				vue_contexte.liste.kanban_elements_par_colonnes = donnees.kanban_elements_par_colonnes;
				vue_contexte.liste.kanban_nombre_par_colonnes = donnees.kanban_nombre_par_colonnes;
				vue_contexte.liste.kanban_somme_par_colonnes = donnees.kanban_somme_par_colonnes;
			}

			$('#checkbox_selection_global_liste_'+id_liste).prop('checked',false);

			this.$nextTick(() => {
				$(".draggable_element_liste").draggable({
					cursorAt:{
						top: 5,
						left: 5
					},
					revert: true,
					start : function(e){
                        var item = $(this);
                        item.css('max-width','50px');
                        item.css('max-height','50px');
                        item.css('overflow','hidden');
                    },
                    stop : function(e){
                        var item = $(this);
                        item.css('max-width', '');
                        item.css('max-height', '');
                        item.css('overflow', '');
                    }
				});
				$('input[data-toggle=toggle]').bootstrapToggle();
			});

			if(typeof specifique_liste_apres_filtres !== 'undefined') {
				specifique_liste_apres_filtres();
			}

			// on appelle la fonction callback
			if(callback != undefined && callback !== false)
				callback(id_liste);

			this.calculs_lignes_selectionnes();

			this.$emit('actualisation_liste');

			setTimeout(() => {
				@yield('action_a_executer_acualisation_liste')
				@stack('action_a_executer_acualisation_liste')
			},250);
	},

	verifie_si_ligne_cochee: function(id_element) {

		if(this.liste.lignes_selectionnees.includes(id_element)){

			$('body').find('tr[element_id=' + id_element + ']').addClass('css_liste_ligne_selectionnee');
			$('body').find('tr[element_id=' + id_element + ']').addClass('js_liste_ligne_selectionnee');

			return true;

		}

		else if(this.liste.lignes_selectionnees.includes(id_element.toString())){

			$('body').find('tr[element_id=' + id_element + ']').addClass('css_liste_ligne_selectionnee');
			$('body').find('tr[element_id=' + id_element + ']').addClass('js_liste_ligne_selectionnee');

			return true;

		}

		else{

			$('body').find('tr[element_id=' + id_element + ']').removeClass('css_liste_ligne_selectionnee');
			$('body').find('tr[element_id=' + id_element + ']').removeClass('js_liste_ligne_selectionnee');

			return false;

		}
	},

	verifie_si_ligne_cochee_class: function(id_element) {

		if(this.liste.lignes_selectionnees.includes(id_element)){

			return "js_liste_ligne_selectionnable js_liste_ligne_lien_vers_fiche js_ligne_element css_liste_ligne_selectionnee js_liste_ligne_selectionnee";

		}

		else if(this.liste.lignes_selectionnees.includes(id_element.toString())){

			return "js_liste_ligne_selectionnable js_liste_ligne_lien_vers_fiche js_ligne_element css_liste_ligne_selectionnee js_liste_ligne_selectionnee";

		}

		else{

			return "js_liste_ligne_selectionnable js_liste_ligne_lien_vers_fiche js_ligne_element"

		}

	},

	retour_a_la_liste: function() {

		var id_liste = this.id_liste;

		var vue_instance = this;

		var formulaire_modale = !!this.liste.modele_liste_libre.formulaire_modale;

		if(formulaire_modale)
			vue_instance.popover_ajout_element = false;
		else{

			$('#popover_ajout_element_'+id_liste).fadeOut(200, function() {

				vue_instance.popover_ajout_element = false;

				$('#affichage_liste_'+id_liste).fadeIn(200);
			});

		}
	},

	afficher_popover_creation_element: function() {

		var id_liste = this.id_liste;

		var vue_instance = this;

		var formulaire_modale = !!this.liste.modele_liste_libre.formulaire_modale;

		if(formulaire_modale)
			vue_instance.popover_ajout_element = true;
		else{
			$('#affichage_liste_'+id_liste).fadeOut(200, function() {

				vue_instance.popover_ajout_element = true;

				$('#popover_ajout_element_'+id_liste).fadeIn(200);

			});
		}
	},

	creer_dans_liste: function(valeur_groupement = null) {

		this.liste.duplication_en_cours = false;

		this.liste.element_id_modification = null;
		this.liste.ligne_modification = null;

		var type_element = this.liste.type_element;

		this.$once('formulaire_charger',() => {

			var modele_par_defaut_liste = structuredClone(this.liste.modele_par_defaut);

			var champs_type_element = this.$refs.formulaire_liste.champs_type_element;

			for(const champ of Object.values(champs_type_element)){

				if ((champ.type !== 4 && champ.type !== 5) || !champ.valeur_defaut) 
					continue;

				const date = new Date();

				if (champ.valeur_defaut === '#aujourdhui+7j#')
					date.setDate(date.getDate() + 7);
				else if (champ.valeur_defaut === '#aujourdhui+14j#')
					date.setDate(date.getDate() + 14);
				else if (champ.valeur_defaut === '#aujourdhui+1h#')
					date.setHours(date.getHours() + 1);

				const valeur = champ.type === 4 ? date.toLocaleDateString('sv-SE') : date.toLocaleString('sv-SE');
    			modele_par_defaut_liste[champ.nom_sql] = valeur;
            }

			for(donnee in modele_par_defaut_liste){

				if(modele_par_defaut_liste[donnee] !== null && modele_par_defaut_liste[donnee] != '' && modele_par_defaut_liste[donnee] != 0)
					this.$set(this.$refs.formulaire_liste.element, donnee, modele_par_defaut_liste[donnee]);
			}

			if(valeur_groupement !== null)
				this.$set(this.$refs.formulaire_liste.element, this.kanban_champ, valeur_groupement);

			this.$set(this, this.type_element, this.$refs.formulaire_liste.element);

			this.$refs.formulaire_liste.details_transformation_stock = {};
		});

		this.afficher_popover_creation_element();

	},

	modifier_dans_liste: function(id_element) {

		var vue_instance =this;

		vue_instance.liste.duplication_en_cours = false;

		var type_element = vue_instance.liste.type_element;

		for(var ligne in vue_instance.liste.lignes) {

			var element = structuredClone(vue_instance.liste.lignes[ligne].element);

			if(element.id == id_element) {

				vue_instance.$once('formulaire_charger',function(){
					vue_instance.$refs.formulaire_liste.element = element;

					vue_instance.$set(vue_instance, vue_instance.type_element, vue_instance.$refs.formulaire_liste.element);

					if(type_element == "modele_de_calculateur")
						vue_instance[type_element].calculateur = JSON.parse(vue_instance[type_element].calculateur);

				});

				vue_instance.liste.element_id_modification = element.id;
				vue_instance.ligne_modification = vue_instance.liste.lignes[ligne];

				vue_instance.$forceUpdate();

				var vue_tmp = this;

				@stack('js_a_inserer')

				break;
			}
		}

		this.afficher_popover_creation_element();
	},

    supprimer_dans_liste: async function(id_element) {

		var id_liste = this.id_liste;

		var vue_instance = this;

		if(!await confirm_eden())
			return false;

		$('#liste_elements_'+id_liste).addClass('css_actualisation_ajax_en_cours');

		var retour_post_suppression = true;

        var type_element = vue_instance.liste.type_element_options ? vue_instance.liste.type_element_options : vue_instance.liste.type_element;

		// on fait un appel ajax pour supprimer
		await $.get({

			url: "eden/element/"+type_element+"/"+id_element+"/supprimer",
			dataType: "json",
			method: 'GET'
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				$('#liste_elements_'+id_liste).removeClass('css_actualisation_ajax_en_cours');

				await erreur(donnees.retour);
				retour_post_suppression = false;
				return;
			}

            vue_instance.$root.$emit('enregistrement_liste_'+id_liste);

			// on actualise la liste
			vue_instance.actualisation_filtres();
		});

		return retour_post_suppression;

	},

	retablir_dans_liste: async function(id_element) {

		var id_liste = this.id_liste;

		var vue_instance = this;

		if(!await confirm_eden())
			return false;

		$('#liste_elements_'+id_liste).addClass('css_actualisation_ajax_en_cours');

		// on fait un appel ajax pour supprimer
		$.get({

			url: "eden/corbeille/"+vue_instance.liste.type_element+"/"+id_element+"/retablir",
			dataType: "json",
			method: 'GET'
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				$('#liste_elements_'+id_liste).removeClass('css_actualisation_ajax_en_cours');

				await erreur(donnees.retour);
				return;
			}

			// on actualise la liste
			vue_instance.actualisation_filtres();
		});
	},

	annuler_dans_liste: async function(id_element) {

		var id_liste = this.id_liste;

		var vue_instance = this;

		if(vue_instance.liste.type_element != 'commande_vente')
			return false;

        if(!await confirm_eden())
            return false;

		$('#liste_elements_'+id_liste).addClass('css_actualisation_ajax_en_cours');

		var retour_post_annulation = true;

		// on fait un appel ajax pour supprimer
		await $.get({

			url: "eden/document/"+vue_instance.liste.type_element+"/"+id_element+"/annuler",
			dataType: "json",
			method: 'GET'
		}).done(function(donnees) {

			if(donnees.retour !== true) {

				$('#liste_elements_'+id_liste).removeClass('css_actualisation_ajax_en_cours');

				erreur(donnees.retour);
				retour_post_annulation = false;
				return;
			}

			// on actualise la liste
			vue_instance.actualisation_filtres();
		});

		return retour_post_annulation;

	},

	dupliquer_dans_liste: function(element, enregistrer_et_dupliquer = false) {

		element = structuredClone(element);

		this.liste.duplication_en_cours = true;

		var type_element = this.liste.type_element;

		this.liste.element_id_modification = null;
		this['id_element'] = element.id;

		element.id = '';
		this.$set(this, type_element, element);

		if(!enregistrer_et_dupliquer){

			this.$once('formulaire_charger',() => {

				this.$refs.formulaire_liste.element = element;
			});

			this.afficher_popover_creation_element();
		}
		else
			this.$refs.formulaire_liste.element = element;
	},

	/*
	 *
	 * Enregistre un élément depuis le formulaire d'une liste
	 * type_enregistrement : définit si on est dans le cas d'un enregistrement classique (0), si on veut enregistrer l'élément et en créer un nouveau (1), ou enregistrer et dupliquer directement l'élément (2)
	 *
	 */
	enregistrer_dans_liste: async function(parametres = {}, type_enregistrement = 0) {

		var id_liste = this.id_liste;
		var type_element = this.liste.type_element;

		// On afficher le loader
		loading();

		var parametres = {};

		if(this.liste.duplication_en_cours == true){

			parametres = {...parametres, ...{

				liste_elements_a_dupliquer : this.liste_elements_a_dupliquer,
				id_element: this.id_element,
				mode : 'duplication',
			}};
		}
		else if(this.filtres_pour_fiche)
			parametres = this.filtres_pour_fiche;

		var donnees = await this.$refs.formulaire_liste.enregistrer(parametres, null, type_enregistrement);

		this.$root.$emit('enregistrement_liste_'+id_liste,donnees);

		if(donnees.retour !== true){
			loading(false);
			return;
		}

		if(type_enregistrement === 2)
			this.dupliquer_dans_liste(donnees.element, true);

		loading(false);

		if(type_enregistrement === 0)
			this.retour_a_la_liste();

		// c'est un message générique ERP, non lié à une liste
		if(this.liste.fiche == 1)
			this.liste.messsage_liste_succes = this.$root.traduction('interface.listes.element_enregistre_avec_succes')+", <a href='/eden/fiche/"+type_element+"/"+donnees.element.id+"'>"+this.$root.traduction('interface.listes.cliquez_ici_pour_afficher')+"</a>";
		else
			this.liste.messsage_liste_succes = this.$root.traduction('interface.listes.element_enregistre_avec_succes');

		this.actualisation_filtres();

        return donnees;
	},

	exporter(type_export, id_rapport = null) {

		var payload = this.parametres_liste;

		var url = "/eden/liste/"+ (id_rapport ?? this.id_liste) +"/exporter/"+type_export;

		$.post({
			url: url,
			data: payload,
		}).done((retour) => {

			if(retour.hasOwnProperty('url_fichier'))
				window.open(retour.url_fichier, '_blank');
			else
				toastr.success(this.$root.traduction('messages.php.liste.export_en_cours'));
		});
	},

	/**
	*
	* Gestion des exports sur mesure
	*
	*/
	exporter_modele: function(id) {

		var payload = this.parametres_liste;

		$.post({
			url: "/eden/liste/"+this.id_liste+"/exporter_modele/"+id,
			data: payload,
		}).done((retour) => {
			if(retour.hasOwnProperty('url_fichier'))
				window.open(retour.url_fichier, '_blank');
			else
				toastr.success(this.$root.traduction('messages.php.liste.export_en_cours'));
		});
	},

	zoom_ligne: function(id_element, parametre = false) {

		var vue_instance =this;
		var taille_array = vue_instance.liste.lignes.length;

		for(let i=0; i < taille_array; i++){

			if(vue_instance.liste.lignes[i].id == id_element){

				// On stock la ligne vue qui nous interesse
				var ligne = vue_instance.liste.lignes[i];

				if(ligne.details_ligne != null){

					ligne.details_ligne = null;
					vue_instance.$forceUpdate();
				}
				else{

					for(ligne_a_supprimer of vue_instance.liste.lignes){
						ligne_a_supprimer.details_ligne = null;
					}

					ligne.details_ligne = vue_instance.$root.traduction('interface.listes.chargement');
					vue_instance.$forceUpdate();

					// Requête AJAX pour récuperer le HTML voulu
					$.post({

						url: "/eden/liste/detail/ligne",
						dataType: "json",
						method: 'POST',
						data: {
							id_element: ligne.id,
							type_element: vue_instance.liste.type_element,
							id_liste_parent: vue_instance.id_liste,
							parametre: parametre,
						},
					}).done(function(donnees) {

						if(donnees.succes == true){

							ligne.details_ligne = donnees.retour;
							vue_instance.$forceUpdate();
						}
					})
				}

			}
		}
	},



	actualise_filtres_sur_liste:function() {

		var vue_instance = this;

		vue_instance.actualisation_filtres();

		vue_instance.deselectionner_toutes_les_lignes();
	},



	checkbox_selectionner_toutes_les_lignes:function(event) {

		var vue_instance = this;

        checkbox = event.target;

		if($(checkbox).prop('checked') === true) {

			vue_instance.selectionner_toutes_les_lignes();
		}
		else {

			vue_instance.deselectionner_toutes_les_lignes()
		}
	},

	selectionner_toutes_les_lignes:function() {

		var vue_instance = this;

		var id_liste = this.id_liste;

		$.get({
			url: "eden/liste/"+id_liste+"/recupere_ids",
			dataType: "json",
			data: this.parametres_liste,
		})
		.done((data) => {

			vue_instance.liste.lignes_selectionnees = data.ids;

			if(data.ids.length == 0){

				vue_instance.liste.nb_lignes_selectionnees = "";

			}

			else if(data.ids.length == 1){

				vue_instance.liste.nb_lignes_selectionnees = data.ids.length + " "+vue_instance.$root.traduction('interface.listes.ligne_selectionnee');

			}

			else if(data.ids.length > 1){

				vue_instance.liste.nb_lignes_selectionnees = data.ids.length + " "+vue_instance.$root.traduction('interface.listes.lignes_selectionnees');

			}

			this.calculs_lignes_selectionnes();

		});
	},


	deselectionner_toutes_les_lignes:function() {

		var vue_instance = this;

		vue_instance.liste.lignes_selectionnees = [];

		vue_instance.liste.nb_lignes_selectionnees = "";

		$('#liste_'+this.id_liste+' .js_liste_ligne_selectionnable').each(function() {

			if(!$(this).hasClass('js_liste_ligne_selectionnee'))
				return;

		});

	},

		serialize(valeur){

			let val, key, okey
			let ktype = ''
			let vals = ''
			let count = 0
			const _utf8Size = function (str) {
				return ~-encodeURI(str).split(/%..|./).length
			}
			const _getType = function (inp) {
				let match
				let key
				let cons
				let types
				let type = typeof inp
				if (type === 'object' && !inp) {
					return 'null'
				}
				if (type === 'object') {
					if (!inp.constructor) {
						return 'object'
					}
					cons = inp.constructor.toString()
					match = cons.match(/(\w+)\(/)
					if (match) {
						cons = match[1].toLowerCase()
					}
					types = ['boolean', 'number', 'string', 'array']
					for (key in types) {
						if (cons === types[key]) {
							type = types[key]
							break
						}
					}
				}
				return type
			}
			const type = _getType(valeur)
			switch (type) {
				case 'function':
					val = ''
					break
				case 'boolean':
					val = 'b:' + (valeur ? '1' : '0')
					break
				case 'number':
					val = (Math.round(valeur) === valeur ? 'i' : 'd') + ':' + valeur
					break
				case 'string':
					val = 's:' + _utf8Size(valeur) + ':"' + valeur + '"'
					break
				case 'array':
					case 'object':
						val = 'a'
						for (key in valeur) {
							if (valeur.hasOwnProperty(key)) {
								ktype = _getType(valeur[key])
								if (ktype === 'function') {
									continue
								}
								okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key)
								vals += this.serialize(okey) + this.serialize(valeur[key])
								count++
							}
						}
						val += ':' + count + ':{' + vals + '}'
						break
					case 'undefined':
						default:
							val = 'N'
							break
			}
			if (type !== 'object' && type !== 'array') {
				val += ';'
			}

			return val
		},

		filtres_pour_fiche_formatees(){

			return btoa(this.serialize(!!this.filtres_pour_fiche ? this.filtres_pour_fiche : []));

		},

		calculs_lignes_selectionnes: function () {

			if(this.liste.lignes_selectionnees.length == 0)
				return;

			var id_liste = this.id_liste;

			if(this.liste.requete_calcul_lignes !== undefined)
				this.liste.requete_calcul_lignes.abort();

			var parametres = structuredClone(this.parametres_liste);

			parametres.lignes_selectionnees = this.liste.lignes_selectionnees;

			this.liste.requete_calcul_lignes = $.post({

				url: "/eden/liste/"+id_liste+"/calculs_elements_selectionnes",
				dataType: "json",
				method: 'POST',
				data: parametres
			})
			.fail((xhr, status, error) => {
				if(xhr.responseJSON == undefined)
					return;

				this.liste.requete_calcul_lignes = undefined;
			})
			.done(async (donnees) => {

				for(index_calcul in this.liste.calculs){
					this.$set(this.liste.calculs[index_calcul], 'calcul_elements_selectionnes', donnees[index_calcul]);
				}
			});
		},

	initialiser_liste_libre: function(){

		var vue_instance = this;

		var donnees_requete = {
			id: vue_instance.id_liste,
			type_element: vue_instance.liste.type_element,
			options_liste : vue_instance.liste.options_liste,
			recuperer_lignes : true,
			filtres_pour_fiche : vue_instance.filtres_pour_fiche_formatees(),
		};

		return this.$root.charger_element_module('liste', vue_instance.id_liste, donnees_requete);
	},

	initialisation_complete_liste: function(){

		var vue_instance = this;

		this.$on('changement_filtres',(nouvelles_valeurs) => {

			this.$set(this.liste.options_liste,'filtres',nouvelles_valeurs);

			this.actualisation_filtres();
		});

		this.$on('changement_recherche_avancee',(parametres) => {

			this.$set(this.liste.options_liste,'recherche_avancee',parametres.recherche_avancee.structure);

			if(parametres.actualisation)
				this.actualisation_filtres();
		});

		if(vue_instance.informations_pour_fiche != undefined && vue_instance.informations_pour_fiche.indicateurs != undefined)
			vue_instance.liste.indicateurs = vue_instance.informations_pour_fiche.indicateurs;

		vue_instance.liste.options_liste.filtres_pour_fiche = vue_instance.filtres_pour_fiche;

		if(vue_instance.modele_par_defaut != undefined)
			vue_instance.liste.modele_par_defaut = vue_instance.modele_par_defaut;

		if(vue_instance.seulement_inactif != undefined)
			vue_instance.liste.options_liste.seulement_inactif = vue_instance.seulement_inactif;
		else
			vue_instance.liste.options_liste.seulement_inactif = false;

		if(vue_instance.indicateur_source != undefined)
			vue_instance.liste.options_liste.indicateur_source = vue_instance.indicateur_source;

		if(vue_instance.kanban_unite != undefined)
			vue_instance.liste.options_liste.kanban_unite = vue_instance.kanban_unite;

		if(vue_instance.kanban_colonne_somme != undefined)
			vue_instance.liste.options_liste.kanban_colonne_somme = vue_instance.kanban_colonne_somme;

		if(vue_instance.kanban_colonne_count != undefined)
			vue_instance.liste.options_liste.kanban_colonne_count = vue_instance.kanban_colonne_count;

		if(vue_instance.kanban)
			vue_instance.liste.options_liste.kanban = vue_instance.kanban;

		if(vue_instance.$root.moi_extranet != null && localStorage['filtres_liste_'+vue_instance.id_liste])
			vue_instance.liste.options_liste.filtres = JSON.parse(localStorage['filtres_liste_'+vue_instance.id_liste]);

		if(vue_instance.recherche_par_defaut !== undefined && vue_instance.recherche_par_defaut !== null)
			vue_instance.liste.options_liste.recherche = vue_instance.recherche_par_defaut;

		return this.initialiser_liste_libre().then(async (donnees) => {

			donnees = donnees[vue_instance.id_liste] ?? donnees;
			vue_instance.liste.options_liste = donnees.options_liste;

			if(vue_instance.$root.moi_extranet != null && localStorage['filtres_liste_'+vue_instance.id_liste])
				vue_instance.liste.options_liste.filtres = JSON.parse(localStorage['filtres_liste_'+vue_instance.id_liste]);

			await vue_instance.$nextTick();

			if(donnees.filtres !== undefined)
				vue_instance.liste.filtres = donnees.filtres;

			if(donnees.droits_liste !== undefined)
				vue_instance.liste.droits_liste = donnees.droits_liste;

			if(donnees.modele_liste_libre !== undefined)
				vue_instance.liste.modele_liste_libre = donnees.modele_liste_libre;

			if(donnees.colonnes !== undefined)
				vue_instance.liste.colonnes = donnees.colonnes;

			if(donnees.calculs !== undefined)
				vue_instance.liste.calculs = donnees.calculs;

			if(donnees.elements_a_copier !== undefined){
				vue_instance.liste.elements_a_copier = donnees.elements_a_copier;
				vue_instance.liste_elements_a_dupliquer = donnees.elements_a_copier.map(element => element.type_element);
			}

			if(donnees.composant_actions !== undefined)
				vue_instance.liste.actions = donnees.composant_actions;

			if(donnees.composant_options !== undefined)
				vue_instance.liste.options = donnees.composant_options;

			if(donnees.composant_options_mobile !== undefined)
				vue_instance.liste.options_mobile = donnees.composant_options_mobile;

			if(vue_instance.recherche_par_defaut !== undefined && vue_instance.recherche_par_defaut !== null)
				vue_instance.liste.options_liste.recherche = vue_instance.recherche_par_defaut;

			if(vue_instance.$refs.filtres && vue_instance.$refs.filtres.$refs.recherche_avancee)
				vue_instance.$refs.filtres.$refs.recherche_avancee.chargement_initial(donnees.recherche_avancee, true);

			if(donnees.lignes_liste !== undefined)
				await vue_instance.traitement_donnees_liste(donnees.lignes_liste);
			else
				vue_instance.actualisation_filtres();
		});
	},

	afficher_actions_masse: function(){

		if(this._composant_actions_masse !== undefined)
			return this._composant_actions_masse;

		var affichage_actions_masse = this.liste.actions;

		var vue_instance = this;

		eval(affichage_actions_masse);

		this._composant_actions_masse = composant;

		return composant;
	},

	afficher_options: function(ligne, mobile = false){

		if(this._cache_composants_options === undefined)
			this._cache_composants_options = {};

		var cle_cache = (mobile ? 'mobile_' : '') + ligne.id;

		if(this._cache_composants_options[cle_cache] !== undefined && this._cache_composants_options[cle_cache].ligne === ligne)
			return this._cache_composants_options[cle_cache].composant;

		var affichage_options = mobile ? this.liste.options_mobile : this.liste.options;

		var vue_instance = this;

		eval(affichage_options);

		this._cache_composants_options[cle_cache] = {ligne: ligne, composant: composant};

		return composant;
	},

	gestion_lien : function(event,lien,ligne){

		if(!lien || lien.type == 'redirection')
			return;

		event.stopPropagation();
		event.preventDefault();

		if(lien.type == 'detail'){

			if(this.$root.intranet)
				this.$root.charger_formulaire(this.liste.type_element, ligne.element.id, this.id_liste);
			else
				this.modifier_dans_liste(ligne.element.id);
		}
	},

@endpush

@push('donnees_pour_vuejs_mounted')

	this.$on('actualisation',()=>{
		this.actualisation_filtres();
	});

    var vue_liste = this;

    $(document).on('click', function(e) {
        var target = $(e.target);
        var datepicker_class = ".day, .dow, .prev, .next, .month, .datepicker-months, .today, .datepicker-years, .year, .new, .clear, .datepicker-switch, .datepicker-days";
        if(!target.is($('#liste_'+vue_liste.id_liste+' .css_conteneur_popover_filtre,#liste_'+vue_liste.id_liste+' .css_conteneur_popover_filtre').find('*').addBack()) && !target.is(datepicker_class)) {
            vue_liste.filtre_actif = null;
        }
    });
@endpush


@push('donnees_pour_vuejs_mounted')

	var vue_instance = this;

	csrf_token = "{{ csrf_token() }}";

	$('body').on('change', '#liste_'+vue_instance.id_liste+' select.js_sous_totaux_sur_liste', function() { vue_instance.actualise_filtres_sur_liste();});

	$('body').on('click', '#liste_'+vue_instance.id_liste+' .js_deplier_contenu_sous_element', function(event) {

		var element_a_deplier = $(this).find('#liste_'+vue_instance.id_liste+' .js_deplier_contenu_sous_element_a_deplier');

		if(element_a_deplier.is(':visible')) {

			element_a_deplier.slideUp(100);
		}
		else {

			element_a_deplier.slideDown(100);
		}

	});

	$('body').on('click', '#liste_'+vue_instance.id_liste+' .js_liste_ligne_selectionnable', function(event) {

		var colonne_champ = $(event.target).closest('.colonne_champ').length > 0;

		if(colonne_champ)
			return;

		var id_element = parseInt($(this).find('td').eq(0).find('input[type=checkbox]').val())

		if(vue_instance.liste == undefined || vue_instance.liste.lignes_selectionnees == undefined)
			return;

		var lignes = vue_instance.liste.lignes_selectionnees;

		if(lignes.includes(id_element) == false) {

			lignes.push(id_element);

		}
		else {

			var index = lignes.indexOf(id_element);

			if (index > -1) {

				lignes.splice(index, 1);

			}
			else{

				index = lignes.indexOf(parseInt(id_element));

				if (index > -1) {

					lignes.splice(index, 1);

				}

			}

		}

		vue_instance.liste.lignes_selectionnees = lignes;

		if(vue_instance.liste.lignes_selectionnees.length == 0){

			vue_instance.liste.nb_lignes_selectionnees = "";

		}

		else if(vue_instance.liste.lignes_selectionnees.length == 1){

			vue_instance.liste.nb_lignes_selectionnees = vue_instance.liste.lignes_selectionnees.length + " "+vue_instance.$root.traduction('interface.listes.ligne_selectionnee');

		}

		else if(vue_instance.liste.lignes_selectionnees.length > 1){

			vue_instance.liste.nb_lignes_selectionnees = vue_instance.liste.lignes_selectionnees.length + " "+vue_instance.$root.traduction('interface.listes.lignes_selectionnees');

		}

		vue_instance.calculs_lignes_selectionnes();

	});

	$('body').on('click', '#liste_'+vue_instance.id_liste+' .js_liste_ajouter_relance', function() {

		var id_element = $(this).attr('id_element');
		var type_element = $(this).attr('type_element');
		var type = $(this).attr('type');

		vue_instance.ajouter_une_relance(id_element, type, type_element);
	});

	$('body').on('click', '#liste_'+vue_instance.id_liste+' .js_liste_supprimer_relance', function() {

		var id_relance = $(this).attr('id_relance');

        vue_instance.supprimer_une_relance(id_relance);
	});

	$('body').on('click', '#liste_'+vue_instance.id_liste+' .js_liste_apercu_element', function() {

		var id_element = $(this).attr('id_element');

		vue_instance.modifier_dans_liste(id_element);
	});

	$('body').on('click', '#liste_'+vue_instance.id_liste+' .js_fermer_detail_ligne', function(event) {

		var id_element = $(this).parent().parent().parent().prev().attr('element_id');

		var taille_array = vue_instance.liste.lignes.length;

		event.stopPropagation();

		for(let i=0; i < taille_array; i++){

			if(vue_instance.liste.lignes[i].id == id_element){

				// On stock la ligne vue qui nous interesse
				var ligne = vue_instance.liste.lignes[i];

				ligne.details_ligne = null;
				vue_instance.$forceUpdate();
			}
		}
	});

@endpush

@push('donnees_pour_vuejs_computed')

	parametres_liste : function(){

		return {
			page : this.liste.options_liste.page,
			tri : this.liste.options_liste.tri,
			recherche : this.liste.options_liste.recherche,
			direction_tri : this.liste.options_liste.direction_tri,
			nombre_par_page : this.liste.options_liste.nombre_par_page,
			filtre_a_appliquer : this.liste.options_liste.filtre_a_appliquer,
			kanban_colonne_somme : this.liste.options_liste.kanban_colonne_somme,
			kanban : this.liste.options_liste.kanban,
			kanban_unite : this.liste.options_liste.kanban_unite,
			kanban_colonne_count : this.liste.options_liste.kanban_colonne_count,
            colonnes_kanban_total : this.liste.options_liste.colonnes_kanban_total,
			seulement_inactif : this.liste.options_liste.seulement_inactif,
			filtres_pour_fiche : this.filtres_pour_fiche_formatees(),
			indicateur_source : this.liste.options_liste.indicateur_source,
			filtres : this.liste.options_liste.filtres,
			recherche_avancee : this.liste.options_liste.recherche_avancee,
			filtres_appliques : this.liste.options_liste.filtres_appliques,
			filtres_affichage: this.liste.filtres.map((filtre) => {
				return {
					id : filtre.id,
					nom_sql : filtre.nom_sql,
					type_element : filtre.type_element,
					champ_de_liaison : filtre.champ_de_liaison
				};
			}),
		};
	},

@endpush
