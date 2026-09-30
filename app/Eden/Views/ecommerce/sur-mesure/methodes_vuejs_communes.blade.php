{{-- <script> --}}

renumerote_etapes: function() {
	
	// on renumérote les étapes

	setTimeout(function() {
		
		$('.js_numero_etape_devis:visible').each(function(index) {
			
			var index_plus_un = index + 1;

			
			$(this).text(index_plus_un);
		});
	}, 250);
},

// on met à jour les frais de livraison en fonction du nombre de portes de garage dans le panier
maj_frais_de_livraison: function() {
	
	var frais_de_livraison = 0;
	
	var nombre_portes_garage = 0;
	
	var panier = this.panier;
	var panier_classique = this.panier_classique;
	var telecommandes_supplementaires = this.telecommandes_supplementaires;
	
	// frais de livraison des portes de garage
	$.each(panier.portes_de_garage, function(index, porte_garage) {
		
		nombre_portes_garage += porte_garage.quantite;
	});
	
	// il y a des portes de garage, on ne calcule pas d'autres frais de livraison
	if(nombre_portes_garage >= 1) {
		
		this.frais_de_livraison = nombre_portes_garage * 0;
		
		this.maj_total_ttc_livre();
		
		return;
	}
	
	// il n'y a pas de portes de garage, on regarde le total du panier
	var total_panier = parseFloat(0);
	
	$.each(panier.tabliers, function(index, tablier) {
		
		total_panier += parseFloat(tablier.tarif_final) * parseFloat(tablier.quantite);
	});
	
	$.each(panier.volets, function(index, volet) {
		
		total_panier += parseFloat(volet.tarif_final) * parseFloat(volet.quantite);
	});
	
	var promo_commandes = 0;
	
	if(this.promo_volet_roulant.commandes != undefined) {
		
		var promo_commandes = this.promo_volet_roulant.commandes;
	}
	
	$.each(panier.telecommandes_supplementaires, function(type_telecommande, liste_quantites) {
		
		$.each(liste_quantites, function(code_article, quantite) {
			
			if(quantite == 0)
				return;
			
			// on ajoute au total
			$.each(telecommandes_supplementaires[type_telecommande].telecommandes, function(index, telecommande) {
				
				if(telecommande.code_article != code_article)
					return;
				
				
				
				total_panier += parseFloat(telecommande.tarif) * parseFloat(quantite) * (100 - promo_commandes) / 100;
			});
			
			if(type_telecommande == 'connectee_somfy') {
				
				$.each(telecommandes_supplementaires[type_telecommande].passerelles_ip, function(index, telecommande) {
					
					if(telecommande.code_article != code_article)
						return;
					
					total_panier += parseFloat(telecommande.tarif) * parseFloat(quantite) * (100 - promo_commandes) / 100;
				});
			}
		});
	});
	
	// le panier classique
	total_panier += parseFloat(panier_classique.total_ttc);
	
	// frais de livraison offerts
	if(total_panier >= 250) {
			
		this.frais_de_livraison = 0;
		
		this.maj_total_ttc_livre();
	}
	else {
		
		this.frais_de_livraison = 19;
		
		this.maj_total_ttc_livre();
	}
	
	return;
},

// on met à jour le total du panier avec les frais de livraison
maj_total_ttc_livre: function() {
	
	var total_ttc_livre = parseFloat(0);
	
	var panier = this.panier;
	var panier_classique = this.panier_classique;
	var telecommandes_supplementaires = this.telecommandes_supplementaires;
	
	$.each(panier.portes_de_garage, function(index, porte_garage) {
		
		total_ttc_livre += parseFloat(porte_garage.tarif_final * porte_garage.quantite);
	});
	
	$.each(panier.volets, function(index, volet) {
		
		total_ttc_livre += parseFloat(volet.tarif_final * volet.quantite);
	});
	
	var promo_commandes = 0;
	
	if(this.promo_volet_roulant.commandes != undefined) {
		
		var promo_commandes = this.promo_volet_roulant.commandes;
	}
	
	$.each(panier.telecommandes_supplementaires, function(type_telecommande, liste_quantites) {
		
		$.each(liste_quantites, function(code_article, quantite) {
			
			if(quantite == 0)
				return;
			
			// on ajoute au total
			$.each(telecommandes_supplementaires[type_telecommande].telecommandes, function(index, telecommande) {
				
				if(telecommande.code_article != code_article)
					return;
				
				total_ttc_livre += parseFloat(telecommande.tarif) * parseFloat(quantite) * (100 - promo_commandes) / 100;
			});
			
			if(type_telecommande == 'connectee_somfy') {
				
				$.each(telecommandes_supplementaires[type_telecommande].passerelles_ip, function(index, telecommande) {
					
					if(telecommande.code_article != code_article)
						return;
					
					total_ttc_livre += parseFloat(telecommande.tarif) * parseFloat(quantite) * (100 - promo_commandes) / 100;
				});
			}
		})
	});
	
	$.each(panier.tabliers, function(index, tablier) {
		
		total_ttc_livre += parseFloat(tablier.tarif_final * tablier.quantite);
	});
	
	// le panier classique
	total_ttc_livre += parseFloat(panier_classique.total_ttc);
	
	this.total_ttc_livre = total_ttc_livre + this.frais_de_livraison;
	
	// on sauvegarde le panier
	this.enregistre_panier();
	
	return;
},

enregistre_panier: function() {
	
	// on vient probablement juste de charger la page
	if(vue_instance == undefined)
		return;
	
	$.ajax({
		type: "POST",
		url: "{{ route('enregistre_panier') }}",
		data: {
			donnees_vue_js: {
				
				panier: vue_instance.$data.panier, 
				frais_de_livraison: vue_instance.$data.frais_de_livraison,
			}
		},
		dataType: 'json'
	});
},

afficher_details_article_sur_mesure: function(article_sur_mesure) {

	if(article_sur_mesure.afficher_details === true) {
		article_sur_mesure.afficher_details = false;
	}
	else {
		article_sur_mesure.afficher_details = true;
	}
},

maj_prix_porte: function(porte_garage) {
	
	var vue_instance = this;
	
	setTimeout(function() {
		
		vue_instance.prix_porte_de_garage(porte_garage);
	}, 200);
},

maj_prix_volet: function(volet, cle) {
	
	var vue_instance = this;

	setTimeout(function() {
		
		volet.tarif_final = vue_instance.prix_volet(volet, cle);
	}, 200);
},

maj_prix_tablier: function(tablier) {
	
	var vue_instance = this;
	
	setTimeout(function() {
		
		tablier.tarif_final = vue_instance.prix_tablier(tablier);
	}, 200);
},
maj_sens_verrou_tablier: function(tablier) {
	
	if(tablier.tablier_verrou == 'sans') {

		tablier.sens_verrouillage = '';
	} else {
		tablier.sens_verrouillage = 'interieur';
	}
	
},
// suppression d'une porte de garage du panier
supprime_porte_garage: function(index) {
	
	this.panier.portes_de_garage.splice(index, 1);
	
	this.maj_frais_de_livraison();
},

// suppression d'un tablier du panier
supprime_tablier: function(index) {
	
	this.panier.tabliers.splice(index, 1);
	
	this.maj_frais_de_livraison();
},

// suppression d'un volet roulant du panier
supprime_volet: function(index) {
	
	this.panier.volets.splice(index, 1);
	
	this.maj_frais_de_livraison();
},

// suppression d'un volet roulant du panier
supprime_accessoire: function(index, article_id) {
	
	$.ajax({
		type: "POST",
		url: "{{ route('ecommerce.ajax_modifie_produit_au_panier') }}",
		data: {
			article_id: article_id,
			quantite: 0,
			tarif: 0,
		},
		dataType: 'json'
	}).done(function() {
		
		document.location.reload();
	});
	
	// this.panier_classique.articles_dans_panier.splice(index, 1);
	
	// this.maj_frais_de_livraison();
},

async prix_porte_de_garage(porte_de_garage) {
	
	// limites de tailles
	if(porte_de_garage.garage_dimensions.largeur_tableau < 2000) {

		await alerte_eden("La largeur minimum pour une porte sectionnelle est de 2000 mm");
		porte_de_garage.garage_dimensions.largeur_tableau = 2000;
	}
	if(porte_de_garage.garage_dimensions.hauteur_tableau < 1800) {

		await alerte_eden("La hauteur minimum pour une porte sectionnelle est de 1800 mm");
		porte_de_garage.garage_dimensions.hauteur_tableau = 1800;
	}
	if(porte_de_garage.garage_dimensions.largeur_tableau > 3000) {

		await alerte_eden("La largeur maximum pour une porte sectionnelle est de 3000 mm");
		porte_de_garage.garage_dimensions.largeur_tableau = 3000;
	}
	if(porte_de_garage.garage_dimensions.hauteur_tableau > 2250) {

		await alerte_eden("La hauteur maximum pour une porte sectionnelle est de 2250 mm");
		porte_de_garage.garage_dimensions.hauteur_tableau = 2250;
	}
	
	// les hublots en fonction du type de porte
	if(porte_de_garage.garage_type_de_panneaux == 'ligne_veine_bois') {
		
		if(porte_de_garage.garage_hublots != 'rectangle_double_vitrage_verre' && porte_de_garage.garage_hublots != 'rectangle_plexi' && porte_de_garage.garage_hublots != 'carre_plexi' && porte_de_garage.garage_hublots != 'rond_plexi' && porte_de_garage.garage_hublots != 'pas_de_hublot') {
			
			porte_de_garage.garage_hublots = 'pas_de_hublot';
		}
	}
	if(porte_de_garage.garage_type_de_panneaux == 'cassettes_veine_bois') {
		
		if(porte_de_garage.garage_hublots != 'rectangle_double_vitrage_verre' && porte_de_garage.garage_hublots != 'rectangle_plexi' && porte_de_garage.garage_hublots != 'pas_de_hublot') {
			
			porte_de_garage.garage_hublots = 'pas_de_hublot';
		}
	}
	
	// on regarde les options impossibles (si on est en motorisation manuelle, certaines options ne sont pas possibles)
	if(porte_de_garage.garage_manoeuvre == 'manuelle') {
		
		// on force les options
		porte_de_garage.garage_options_motorisations.emetteurs_supplementaires = 0;
		porte_de_garage.garage_options_motorisations.bouton_mural_filaire = 0;
		porte_de_garage.garage_options_motorisations.bouton_mural_sans_fil = 0;
		porte_de_garage.garage_options_motorisations.clavier_a_code = 0;
		porte_de_garage.garage_securite_voie_publique = 0;
		porte_de_garage.garage_debrayage_depuis_exterieur = 0;
			
		if(porte_de_garage.garage_verouillage != 'pas_de_verrou' && porte_de_garage.garage_verouillage != 'verrou_interieur' && porte_de_garage.garage_verouillage != 'serrure_a_clef') {
			
			porte_de_garage.garage_verouillage = 'pas_de_verrou';
		}
	}
	else {
		
		porte_de_garage.garage_verouillage = 'pas_de_verrou';
	}
	
	// on corrige un petit bug
	if(porte_de_garage.garage_hublots == 'pas_de_hublot') {
		
		porte_de_garage.garage_emplacements_hublots = '';
	}
	else {
		
		if(porte_de_garage.garage_emplacements_hublots == '') {
			
			porte_de_garage.garage_emplacements_hublots = 'horizontaux';
		}
	}
	
	if(porte_de_garage.garage_hublots == 'carre_inox_double_vitrage' && porte_de_garage.garage_emplacements_hublots != 'verticaux_gauche' && porte_de_garage.garage_emplacements_hublots != 'verticaux_droite') {
		
		porte_de_garage.garage_emplacements_hublots = 'verticaux_gauche';
	}
	
	// on gère la photo
	porte_de_garage.img_apercu_porte_garage = '{{ asset('ecommerce-amc/images/devis/porte-garage/apercu') }}/'+porte_de_garage.garage_type_de_panneaux+'_'+porte_de_garage.garage_couleur+'_'+porte_de_garage.garage_hublots+'_'+porte_de_garage.garage_emplacements_hublots+'.png';
					
	
	
	// étape 1, on calcule le prix par défaut
	var liste_prix = this.tarifs_porte_de_garage[porte_de_garage.garage_type_de_panneaux];
	
	var vue_instance = this;
	
	var prix_actuel = 0;
	var prix_actuel_hors_options = 0;
	
	$.each(liste_prix, function(index, prix) {
		
		if(prix.hauteur < porte_de_garage.garage_dimensions.hauteur_tableau)
			return;
		
		if(prix.largeur < porte_de_garage.garage_dimensions.largeur_tableau)
			return;
		
		prix_actuel_hors_options = prix.prix;
		
		// on break
		return false;
	});
	
	// option couleur
	var surface = porte_de_garage.garage_dimensions.hauteur_tableau / 1000 * porte_de_garage.garage_dimensions.largeur_tableau / 1000;
	var supplement_couleur = 0;
	
	if(porte_de_garage.garage_couleur == 'brun') {
		
		supplement_couleur = surface * 6.45; 
	}
	
	if(porte_de_garage.garage_couleur == 'anthracite') {
		
		supplement_couleur = surface * 6.45; 
	}
	
	if(porte_de_garage.garage_couleur == 'noir') {
		
		supplement_couleur = surface * 6.45; 
	}
	
	if(porte_de_garage.garage_couleur == 'chene_dore') {
		
		supplement_couleur = surface * 43.86; 
	}

	
	if(porte_de_garage.garage_couleur == 'ral') {
		
		supplement_couleur = surface * 43.86; 
	}
	
	prix_actuel_hors_options += supplement_couleur;
	
	// option dimensions
	if(porte_de_garage.garage_dimensions.retombee_linteau < 120) {
		
		prix_actuel_hors_options += 46.35 * porte_de_garage.garage_dimensions.largeur_tableau / 1000;
	}
	if(porte_de_garage.garage_dimensions.ecoincon_gauche < 75) {
		
		prix_actuel_hors_options += 35.58;
	}
	if(porte_de_garage.garage_dimensions.ecoincon_droit < 75) {
		
		prix_actuel_hors_options += 35.58;
	}
	
	// option manuelle
	if(porte_de_garage.garage_manoeuvre == 'manuelle') {
		
		prix_actuel_hors_options -= 176.30;
	}
	
	// calcul du nombre de hublots & du tarif lié
	var prix_hublot = 0;
	
	if(porte_de_garage.garage_hublots == 'rectangle_plexi')
		prix_hublot = 99.99;
	if(porte_de_garage.garage_hublots == 'rectangle_double_vitrage_verre')
		prix_hublot = 149.99;
	if(porte_de_garage.garage_hublots == 'carre_plexi')
		prix_hublot = 109.99;
	if(porte_de_garage.garage_hublots == 'carre_inox_double_vitrage')
		prix_hublot = 184.99;
	if(porte_de_garage.garage_hublots == 'rond_plexi')
		prix_hublot = 109.99;

	var nombre_hublots = 0;
	var prix_total_hublots = 0;

	if(porte_de_garage.garage_type_de_panneaux != 'cassettes_veine_bois') {
		
		// position verticale ou horizontale ?
		if(porte_de_garage.garage_emplacements_hublots == 'horizontaux') {
			
			if(porte_de_garage.garage_dimensions.largeur_tableau <= 2600) {
				porte_de_garage.nombre_hublots = 3;
				prix_total_hublots = 3 * prix_hublot;
			}
			else {
				porte_de_garage.nombre_hublots = 4;
				prix_total_hublots = 4 * prix_hublot;
			}
		}
		else {
			
			if(porte_de_garage.garage_dimensions.hauteur_tableau <= 1900) {
				porte_de_garage.nombre_hublots = 3;
				prix_total_hublots = 3 * prix_hublot;
			}
			else {
				porte_de_garage.nombre_hublots = 4;
				prix_total_hublots = 4 * prix_hublot;
			}
		}
	}
	// cassettes / S200
	else {
		
		if(porte_de_garage.garage_dimensions.largeur_tableau <= 2600) {
			
			porte_de_garage.nombre_hublots = 3;
			prix_total_hublots = 3 * prix_hublot;
		}
		else {
			
			porte_de_garage.nombre_hublots = 4;
			prix_total_hublots = 4 * prix_hublot;
		}
	}
	
	porte_de_garage.prix_total_hublots = prix_total_hublots;
	
	prix_actuel_hors_options += prix_total_hublots;
	prix_actuel = prix_actuel_hors_options;
	
	
	
	// options verrou
	porte_de_garage.tarif_verrou = 0;
	
	if(porte_de_garage.garage_verouillage == 'verrou_interieur') {
		
		porte_de_garage.tarif_verrou = {{ modele('article', 105)->tarif }};
		prix_actuel += {{ modele('article', 105)->tarif }};
	}
	if(porte_de_garage.garage_verouillage == 'serrure_a_clef') {
		
		porte_de_garage.tarif_verrou = {{ modele('article', 106)->tarif }};
		prix_actuel += {{ modele('article', 106)->tarif }};
	}
	
	// option motorisée
	prix_actuel += porte_de_garage.garage_options_motorisations.bouton_mural_filaire * {{ modele('article', 95)->tarif }};
	prix_actuel += porte_de_garage.garage_options_motorisations.bouton_mural_sans_fil * {{ modele('article', 94)->tarif }};
	prix_actuel += porte_de_garage.garage_options_motorisations.clavier_a_code * {{ modele('article', 93)->tarif }};
	prix_actuel += porte_de_garage.garage_options_motorisations.emetteurs_supplementaires * {{ modele('article', 92)->tarif }};
	
	if(porte_de_garage.garage_securite_voie_publique == 1) {
		
		prix_actuel += {{ modele('article', 103)->tarif }};
	}
	
	// option débrayage depuis l'exterieur
	if(porte_de_garage.garage_debrayage_depuis_exterieur == 1) {
		
		prix_actuel += {{ modele('article', 104)->tarif }};
	}
	
	
	// on renseigne sur l'élément
	porte_de_garage.tarif_final_avant_promo = prix_actuel;
	porte_de_garage.tarif_final_hors_options_avant_promo = prix_actuel_hors_options;
	
	
	if(this.promo_porte_garage !== false) {
		
		porte_de_garage.tarif_final = prix_actuel * (100 - this.promo_porte_garage) / 100;
		porte_de_garage.tarif_final_hors_options = prix_actuel_hors_options * (100 - this.promo_porte_garage) / 100;
		
		porte_de_garage.promo = this.promo_porte_garage;
	}
	else {
		
		porte_de_garage.tarif_final = prix_actuel;
		porte_de_garage.tarif_final_hors_options = prix_actuel_hors_options;
		
		porte_de_garage.promo = 0;
	}
	
	// on met à jour le total
	this.maj_frais_de_livraison();
	
	return Math.round(porte_de_garage.tarif_final * 100) / 100;
},

prix_tablier: async function(tablier) {
	
	// étape 1, on calcule le prix par défaut
	var liste_prix = this.tarifs_tabliers[tablier.tablier_type_de_tablier];
	
	var vue_instance = this;
	
	var prix_actuel = 0;
	var prix_actuel_hors_options = 0;
	
	tablier.tarif_verrou = 0;
	tablier.tarif_attaches = 0;
	
	// limites de tailles
	if(tablier.tablier_largeur < 200) {

		await alerte_eden("La largeur minimum pour un tablier est de 200 mm");
		tablier.tablier_largeur = 200;
	}
	if(tablier.tablier_hauteur < 200) {

		await alerte_eden("La hauteur minimum pour un tablier est de 200 mm");
		tablier.tablier_hauteur = 200;
	}
	if(tablier.tablier_largeur > 3000) {

		await alerte_eden("La largeur maximum pour un tablier est de 3000 mm");
		tablier.tablier_largeur = 3000;
	}
	if(tablier.tablier_hauteur > 3000) {

		await alerte_eden("La hauteur maximum pour un tablier est de 3000 mm");
		tablier.tablier_hauteur = 3000;
	}
	
	// choix par défaut des épaisseurs
	if(tablier.tablier_matiere_tablier == 'ALU') {
		
		if(tablier.tablier_type_de_tablier != 'AL39' && tablier.tablier_type_de_tablier != 'AL54') {
			
			tablier.tablier_type_de_tablier = 'AL39';
		}
	}
	else {
		
		if(tablier.tablier_type_de_tablier != 'P40' && tablier.tablier_type_de_tablier != 'P55') {
			
			tablier.tablier_type_de_tablier = 'P40';
		}
		
		tablier.tablier_couleur = 'blanc';
	}
	
	// Aperçu du tablier
	if(tablier.tablier_coulisses == 0) {
		
		tablier.image_apercu = '{{ asset('ecommerce-amc/images/devis/tablier/apercu') }}/tablier_'+tablier.tablier_couleur+'.jpg';
	}
	else {
		
		tablier.image_apercu = '{{ asset('ecommerce-amc/images/devis/tablier/apercu') }}/tablier_'+tablier.tablier_couleur+'_coulisses.jpg';
	}

	$.each(liste_prix, function(index, prix) {
		
		if(prix.hauteur < tablier.tablier_hauteur)
			return;
		
		if(prix.largeur < tablier.tablier_largeur)
			return;
		
		prix_actuel_hors_options = prix.prix;
		
		// on break
		return false;
	});
	
	if(tablier.tablier_type_de_tablier == 'AL54' && tablier.tablier_couleur != 'blanc')
		prix_actuel_hors_options = prix_actuel_hors_options * 1.39;
	
	// coulisses
	if(tablier.tablier_coulisses == 1) {
		
		prix_actuel_hors_options += Math.round(tablier.tablier_hauteur * 2 * 11.15 / 10) / 100;
	}
	
	
	prix_actuel = prix_actuel_hors_options;
	
	// attaches
	if(tablier.tablier_attaches == 'standard') {
		
		prix_actuel += {{ modele('article', 101)->tarif }};
		tablier.tarif_attaches = {{ modele('article', 101)->tarif }};
	}
	if(tablier.tablier_attaches == 'sangles') {
		
		prix_actuel += {{ modele('article', 102)->tarif }};
		tablier.tarif_attaches = {{ modele('article', 102)->tarif }};
	}
	
	// butées d'arret
	if(tablier.tablier_butees_darret == 1) {
		
		prix_actuel += {{ modele('article', 100)->tarif }};
	}
	
	// verrou
	if(tablier.tablier_verrou == 'verrou_manuel') {
		
		prix_actuel += {{ modele('article', 97)->tarif }};
		tablier.tarif_verrou = {{ modele('article', 97)->tarif }};
	}
	if(tablier.tablier_verrou == 'serrure_a_clef_sur_lame_finale') {
		
		prix_actuel += {{ modele('article', 98)->tarif }};
		tablier.tarif_verrou = {{ modele('article', 98)->tarif }};
	}
	if(tablier.tablier_verrou == 'serrure_a_clef_sur_lame_intermediaire') {
		
		prix_actuel += {{ modele('article', 99)->tarif }};
		tablier.tarif_verrou = {{ modele('article', 99)->tarif }};
	}
	

	// on renseigne sur l'élément
	tablier.tarif_final_avant_promo = prix_actuel;
	tablier.tarif_final_hors_options_avant_promo = prix_actuel_hors_options;
	
	
	if(this.promo_tablier !== false) {
		
		tablier.tarif_final = prix_actuel * (100 - this.promo_tablier) / 100;
		tablier.tarif_final_hors_options = prix_actuel_hors_options * (100 - this.promo_tablier) / 100;
		
		tablier.promo = this.promo_tablier;
	}
	else {
		
		tablier.tarif_final = prix_actuel;
		tablier.tarif_final_hors_options = prix_actuel_hors_options;
		
		tablier.promo = 0;
	}

	this.$forceUpdate();

	this.maj_frais_de_livraison();
	
	return prix_actuel.toFixed(2);
},

prix_volet: async function(volet, cle) {
	
	// par défaut
	volet.promo = 0;
	volet.promo_commande = 0;
	
	this.afficher_commandes_groupees_volets_roulants();
	
	// on initialise les options
	volet.tarif_verrou = 0;
	volet.tarif_commande = 0;
	volet.tarif_coffre = 0;
	
	// on initialise les tarifs
	volet.tarif_final = 0;
	volet.tarif_final_hors_options = 0;
	
	volet.tarif_final_avant_promo = 0;
	volet.tarif_final_hors_options_avant_promo = 0;
	
	// on vérifie les variables dont on a besoin
	if(volet.volet_couleur == '') {
		
		this.renumerote_etapes();
		return 0;
	}
	
	if(volet.volet_pose == '') {
		
		this.renumerote_etapes();
		return 0;
	}
	
	if(volet.volet_manoeuvre == '') {
		
		this.renumerote_etapes();
		return 0;
	}
	
	// largeur et hauteur
	if(volet.volet_hauteur == '') {
		
		this.renumerote_etapes();
		return 0;
	}
	
	if(volet.volet_largeur == '') {
		
		this.renumerote_etapes();
		return 0;
	}
	
	// on vérifie les tailles mini et maxi des volets
	if(volet.volet_manoeuvre == 'sangle') {

		var largeur_mini = 300;
		var largeur_maxi = 2000;

		var hauteur_mini = 300;
		var hauteur_maxi = 2800;
	}
	if(volet.volet_manoeuvre == 'tirage_direct') {

		var largeur_mini = 600;
		var largeur_maxi = 2000;

		var hauteur_mini = 300;
		var hauteur_maxi = 2800;
	}
	if(volet.volet_manoeuvre == 'manivelle') {

		var largeur_mini = 600;
		var largeur_maxi = 3000;

		var hauteur_mini = 300;
		var hauteur_maxi = 2800;
	}
	if(volet.volet_manoeuvre == 'electrique_amc') {

		var largeur_mini = 550;
		var largeur_maxi = 3000;

		var hauteur_mini = 300;
		var hauteur_maxi = 2800;
	}
	if(volet.volet_manoeuvre == 'electrique_somfy') {

		var largeur_mini = 600;
		var largeur_maxi = 3000;

		var hauteur_mini = 300;
		var hauteur_maxi = 2800;
	}
	if(volet.volet_manoeuvre == 'radio_amc') {

		var largeur_mini = 700;
		var largeur_maxi = 3000;

		var hauteur_mini = 300;
		var hauteur_maxi = 2800;
	}
	if(volet.volet_manoeuvre == 'radio_somfy') {

		var largeur_mini = 700;
		var largeur_maxi = 3000;

		var hauteur_mini = 300;
		var hauteur_maxi = 2800;
	}
	if(volet.volet_manoeuvre == 'solaire_somfy') {

		var largeur_mini = 600;
		var largeur_maxi = 2900;

		var hauteur_mini = 300;
		var hauteur_maxi = 2800;
	}


	if(volet.volet_hauteur < hauteur_mini) {

		volet.volet_hauteur = hauteur_mini;
		await alerte_eden('La hauteur minimum pour cette manoeuvre est de '+hauteur_mini+' mm. Pour toute autre demande, notre service commercial est disponible au 03 66 72 91 88 ou à l\'adresse contact@store-volet.com');

	}
	if(volet.volet_hauteur > hauteur_maxi) {

		volet.volet_hauteur = hauteur_maxi;
		await alerte_eden('La hauteur maximum pour cette manoeuvre est de '+hauteur_maxi+' mm. Pour toute autre demande, notre service commercial est disponible au 03 66 72 91 88 ou à l\'adresse contact@store-volet.com');

	}

	if(volet.volet_largeur < largeur_mini) {

		volet.volet_largeur = largeur_mini;
		await alerte_eden('La largeur minimum pour cette manoeuvre est de '+largeur_mini+' mm. Pour toute autre demande, notre service commercial est disponible au 03 66 72 91 88 ou à l\'adresse contact@store-volet.com');

	}
	if(volet.volet_largeur > largeur_maxi) {

		volet.volet_largeur = largeur_maxi;
		await alerte_eden('La largeur maximum pour cette manoeuvre est de '+largeur_maxi+' mm. Pour toute autre demande, notre service commercial est disponible au 03 66 72 91 88 ou à l\'adresse contact@store-volet.com');

	}
	
	
	
	if(volet.volet_pose == 'a' || volet.volet_pose == 'e') {
		
		volet.image_apercu = '{{ asset('ecommerce-amc/images/devis/volet-roulant/apercu') }}/lames_volet_roulant_'+volet.volet_couleur+'.png';
	}
	else {
		
		volet.image_apercu = '{{ asset('ecommerce-amc/images/devis/volet-roulant/apercu') }}/volet_roulant_'+volet.volet_couleur+'.png';
	}
	
	// les valeurs par défaut
	
	// le verrou est obligatoire pour un volet avec manoeuvre solaire et radio somfy
	if(volet.volet_manoeuvre == 'solaire_somfy' || volet.volet_manoeuvre == 'radio_somfy') {
		
		volet.volet_fermeture = 'automatique';
	}
	
	// on vérifie la fermeture
	if(volet.volet_manoeuvre == 'sangle' && (volet.volet_fermeture != 'pas_de_verrou' && volet.volet_fermeture != 'manuel_lame_finale')) {
		
		volet.volet_fermeture = 'pas_de_verrou';
	}
	if(volet.volet_manoeuvre == 'tirage_direct' && (volet.volet_fermeture != 'pas_de_verrou' && volet.volet_fermeture != 'manuel_lame_finale' && volet.volet_fermeture != 'cle_lame_intermediaire')) {
		
		volet.volet_fermeture = 'pas_de_verrou';
	}
	if((volet.volet_manoeuvre == 'manivelle' || volet.volet_manoeuvre == 'electrique_amc' || volet.volet_manoeuvre == 'electrique_somfy' || volet.volet_manoeuvre == 'radio_amc' || volet.volet_manoeuvre == 'connectee_somfy' || volet.volet_manoeuvre == 'solaire_aok' || volet.volet_manoeuvre == 'electrique_aok' || volet.volet_manoeuvre == 'radio_aok') && (volet.volet_fermeture != 'pas_de_verrou' && volet.volet_fermeture != 'automatique')) {
		
		volet.volet_fermeture = 'pas_de_verrou';
	}
	
	// on vérifie les commandes
	if(volet.volet_manoeuvre == 'electrique_amc') {
		
		if(volet.volet_commande != 'commande_amc_en_applique' && volet.volet_commande != 'commande_amc_encastree')
			volet.volet_commande = 'commande_amc_en_applique';
	}
	if(volet.volet_manoeuvre == 'electrique_somfy') {
		
		if(volet.volet_commande != 'commande_somfy_en_applique' && volet.volet_commande != 'commande_somfy_encastree')
			volet.volet_commande = 'commande_somfy_en_applique';
	}
	if(volet.volet_manoeuvre == 'radio_amc') {
		
		if(volet.volet_commande != 'murale_individuelle_amc_radio' && volet.volet_commande != 'portative_individuelle_amc_radio')
			volet.volet_commande = 'murale_individuelle_amc_radio';
	}
	if(volet.volet_manoeuvre == 'radio_somfy') {
		
		if(volet.volet_commande != 'murale_individuelle_somfy_radio' && volet.volet_commande != 'portative_individuelle_somfy_radio')
			volet.volet_commande = 'murale_individuelle_somfy_radio';
	}
	if(volet.volet_manoeuvre == 'connectee_somfy') {
		
		if(volet.volet_commande != 'murale_individuelle_somfy_connectee' && volet.volet_commande != 'portative_individuelle_somfy_connectee')
			volet.volet_commande = 'murale_individuelle_somfy_connectee';
	}
	if(volet.volet_manoeuvre == 'solaire_somfy') {
		
		if(volet.volet_commande != 'murale_individuelle_somfy_solaire' && volet.volet_commande != 'portative_individuelle_somfy_solaire')
			volet.volet_commande = 'murale_individuelle_somfy_solaire';
	}
	
	// solaire AOK
	if(volet.volet_manoeuvre == 'solaire_aok') {
		
		if(volet.volet_commande != 'murale_individuelle_aok_solaire' && volet.volet_commande != 'portative_individuelle_aok_solaire')
			volet.volet_commande = 'murale_individuelle_aok_solaire';
	}
	
	// electrique AOK
	if(volet.volet_manoeuvre == 'electrique_aok') {
	
		if(volet.volet_commande != 'commande_aok_en_applique' && volet.volet_commande != 'commande_aok_encastree')
			volet.volet_commande = 'commande_aok_en_applique';
	}
	
	// radio AOK
	if(volet.volet_manoeuvre == 'radio_aok') {
		
		if(volet.volet_commande != 'murale_individuelle_aok_radio' && volet.volet_commande != 'portative_individuelle_aok_radio')
			volet.volet_commande = 'murale_individuelle_aok_radio';
	}
	
	
	if(volet.volet_manoeuvre == 'sangle' || volet.volet_manoeuvre == 'tirage_direct' || volet.volet_manoeuvre == 'manivelle') {
		
		volet.volet_commande = '';
	}
	
	// on vérifie le coffre
	if(volet.volet_pose == 'b' || volet.volet_pose == 'c' || volet.volet_pose == 'd') {
		
		if(volet.volet_coffre != 'rond' && volet.volet_coffre != '45') {
			
			volet.volet_coffre = '45';
		}
	}
	else {
		
		if(volet.volet_coffre != 'pas_de_coffre') {
			
			volet.volet_coffre = 'pas_de_coffre';
		}
	}
	
	// le coté
	if(volet.volet_matiere == 'pvc' && volet.volet_couleur != 'pvc_blanc') {
		
		volet.volet_couleur = 'pvc_blanc';
	}
	
	// le coté
	if(volet.volet_manoeuvre != 'tirage_direct') {
		
		if(volet.volet_cote_manoeuvre != 'gauche' && volet.volet_cote_manoeuvre != 'droite')
			volet.volet_cote_manoeuvre = 'gauche';
	}

	var tarifs = this.tarifs_volets.alu;
	
	if(volet.volet_couleur == 'pvc_blanc')
		var tarifs = this.tarifs_volets.pvc;
	
	if(volet.volet_pose == 'a' || volet.volet_pose == 'e')
		var tarifs = tarifs.a;
	else
		var tarifs = tarifs.b;
	
	if(volet.volet_manoeuvre == 'sangle')
		var man = 's';
	else if(volet.volet_manoeuvre == 'tirage_direct')
		var man = 't';
	else if(volet.volet_manoeuvre == 'manivelle')
		var man = 'm';
	else if(volet.volet_manoeuvre == 'electrique_amc')
		var man = 'e_amc';
	else if(volet.volet_manoeuvre == 'electrique_somfy')
		var man = 'e';
	else if(volet.volet_manoeuvre == 'radio_amc')
		var man = 'r_amc';
	else if(volet.volet_manoeuvre == 'radio_somfy')
		var man = 'r';
	else if(volet.volet_manoeuvre == 'solaire_somfy')
		var man = 'sol';
	else if(volet.volet_manoeuvre == 'connectee_somfy')
		var man = 'io';
	else if(volet.volet_manoeuvre == 'electrique_aok')
		var man = 'e_aok';
	else if(volet.volet_manoeuvre == 'radio_aok')
		var man = 'r_aok';
	else if(volet.volet_manoeuvre == 'solaire_aok')
		var man = 'sol_aok';
	
	tarifs = tarifs[man];
	
	var prix_actuel = 0;
	var prix_actuel_hors_options = 0;
	
	// Ajout tarifaires en cas de pose D
	var hauteur = parseInt(volet.volet_hauteur);
	var largeur = parseInt(volet.volet_largeur);
	
	if(volet.volet_pose == 'd') {

		hauteur += 180;
		largeur += 106;
	}
	
	$.each(tarifs, function(index, tarif) {

		if(tarif.tarif_hauteur >= hauteur && tarif.tarif_largeur >= largeur) {
			
			prix_actuel_hors_options = parseFloat(tarif.tarif_prix);
			
			if(vue_instance !== undefined)
				vue_instance.renumerote_etapes();
			
			return false;
		}
	});

	
	// on regarde les options
	prix_actuel = prix_actuel_hors_options;
	
	// le coffre
	if(volet.volet_coffre == 'rond') {
		
		prix_actuel += {{ modele('article', 55)->tarif }};
		volet.tarif_coffre = {{ modele('article', 55)->tarif }};
	}
	
	// le verrou
	if(volet.volet_fermeture == 'manuel_lame_finale') {
		
		volet.tarif_verrou = {{ modele('article', 58)->tarif }};
		prix_actuel += {{ modele('article', 58)->tarif }};
	}
	if(volet.volet_fermeture == 'cle_lame_intermediaire') {
		
		volet.tarif_verrou = {{ modele('article', 59)->tarif }};
		prix_actuel += {{ modele('article', 59)->tarif }};
	}
	if(volet.volet_fermeture == 'automatique') {
		
		volet.tarif_verrou = {{ modele('article', 57)->tarif }};
		prix_actuel += {{ modele('article', 57)->tarif }};
	}
	
	// la commande
	
	// les électriques
	if(volet.volet_commande == 'commande_amc_en_applique') {
		
		// c'est offert
		volet.tarif_commande = 0;
	}
	if(volet.volet_commande == 'commande_amc_encastree') {
		
		// c'est offert
		volet.tarif_commande = 0;
	}
	if(volet.volet_commande == 'commande_somfy_en_applique') {
		
		// c'est offert
		volet.tarif_commande = 0;
	}
	if(volet.volet_commande == 'commande_somfy_encastree') {
		
		// c'est offert
		volet.tarif_commande = 0;
	}
	
	// les radios
	if(volet.volet_commande == 'murale_individuelle_amc_radio') {
		
		volet.tarif_commande = {{ modele('article', 64)->tarif }};
	}
	if(volet.volet_commande == 'portative_individuelle_amc_radio') {
		
		volet.tarif_commande = {{ modele('article', 65)->tarif }};
	}
	if(volet.volet_commande == 'murale_individuelle_somfy_radio') {
		
		volet.tarif_commande = {{ modele('article', 66)->tarif }};
	}
	if(volet.volet_commande == 'portative_individuelle_somfy_radio') {
		
		volet.tarif_commande = {{ modele('article', 67)->tarif }};
	}
	if(volet.volet_commande == 'murale_individuelle_aok_radio') {
		
		volet.tarif_commande = {{ modele('article', 1007)->tarif }};
	}
	if(volet.volet_commande == 'portative_individuelle_aok_radio') {
		
		volet.tarif_commande = {{ modele('article', 1008)->tarif }};
	}
	
	
	// le solaire
	if(volet.volet_commande == 'murale_individuelle_somfy_solaire') {
		
		volet.tarif_commande = {{ modele('article', 89)->tarif }};
	}
	if(volet.volet_commande == 'portative_individuelle_somfy_solaire') {
		
		volet.tarif_commande = {{ modele('article', 90)->tarif }};
	}
	
	// le solaire AOK
	if(volet.volet_commande == 'murale_individuelle_aok_solaire') {
		
		volet.tarif_commande = {{ modele('article', 1010)->tarif }};
	}
	if(volet.volet_commande == 'portative_individuelle_aok_solaire') {
		
		volet.tarif_commande = {{ modele('article', 1009)->tarif }};
	}
	
	// connecté
	if(volet.volet_commande == 'murale_individuelle_somfy_connectee') {
		
		volet.tarif_commande = {{ modele('article', 82)->tarif }};
	}
	if(volet.volet_commande == 'portative_individuelle_somfy_connectee') {
		
		volet.tarif_commande = {{ modele('article', 83)->tarif }};
	}
	
	
	// on renseigne sur l'élément
	volet.tarif_final_avant_promo = prix_actuel;
	volet.tarif_final_hors_options_avant_promo = prix_actuel_hors_options;
	
	
	
	if(this.promo_volet_roulant !== false) {
		
		volet.tarif_final = prix_actuel * (100 - this.promo_volet_roulant[volet.volet_manoeuvre]) / 100;
		
		volet.tarif_final_hors_options = prix_actuel_hors_options * (100 - this.promo_volet_roulant[volet.volet_manoeuvre]) / 100;
		
		volet.promo = this.promo_volet_roulant[volet.volet_manoeuvre];
	}
	else {
		
		volet.tarif_final = prix_actuel;
		volet.tarif_final_hors_options = prix_actuel_hors_options;
		
		volet.promo = 0;
	}
	
	
	
	// on vient ajouter la commande
	volet.tarif_final_avant_promo += volet.tarif_commande;
	
	var tarif_commande_avec_promo = volet.tarif_commande;
	
	if(this.promo_volet_roulant !== false && this.promo_volet_roulant.commandes != '' && this.promo_volet_roulant.commandes != 0 && this.promo_volet_roulant.commandes != undefined) {
		
		volet.promo_commande = parseFloat(this.promo_volet_roulant.commandes);
		
		tarif_commande_avec_promo = Math.round(volet.tarif_commande * (100 - volet.promo_commande)) / 100;
	}
	else {
		
		tarif_commande_avec_promo = volet.tarif_commande;
	}
	
	volet.tarif_final += tarif_commande_avec_promo;
	
	
	this.renumerote_etapes();
	
	//console.log("OK on va appeler la maj des frais de livraison");
	
	this.maj_frais_de_livraison();
	
	return parseFloat(volet.tarif_final).toFixed(2);
},

/**
 * 
 * Doit on afficher les commandes groupées ?
 * 
 */
afficher_commandes_groupees_volets_roulants: function() {
	
	// on regarde si on a du multi manoeuvres poru les commandes groupées
	this.panier_multi_manoeuvres_commandes_groupees = false;
	this.panier_afficher_les_commandes_groupees = false;
	
	// on boucle sur le panier pour voir les différentes manoeuvres
	this.panier_differentes_manoeuvres = [];
	
	var manoeuvre_radio_somfy = false;
	var manoeuvre_radio_amc = false;
	var manoeuvre_connectee_somfy = false;
	var manoeuvre_radio_aok = false;
	var manoeuvre_solaire_aok = false;
	
	manoeuvre = false;
	
	if(this.panier.volets != undefined && this.panier.volets.length > 0){
		
		for(i in this.panier.volets) {
			
			var manoeuvre_tmp = this.panier.volets[i].volet_manoeuvre;
			
			if(manoeuvre_tmp != 'solaire_somfy' && manoeuvre_tmp != 'radio_amc' && manoeuvre_tmp != 'radio_somfy' && manoeuvre_tmp != 'connectee_somfy' && manoeuvre_tmp != 'radio_aok' && manoeuvre_tmp != 'solaire_aok')
				continue;
			
			manoeuvre = manoeuvre_tmp;
			
			if(manoeuvre == 'solaire_somfy')
				manoeuvre = 'radio_somfy';
			
			if(manoeuvre == 'radio_somfy')
				manoeuvre_radio_somfy = true;
			if(manoeuvre == 'radio_amc')
				manoeuvre_radio_amc = true;
			if(manoeuvre == 'connectee_somfy')
				manoeuvre_connectee_somfy = true;
			if(manoeuvre == 'radio_aok')
				manoeuvre_radio_aok = true;
			if(manoeuvre == 'solaire_aok')
				manoeuvre_solaire_aok = true;
		}
	}
	
	
	var nombre_manoeuvres_differentes = 0;
	
	if(manoeuvre_radio_somfy === true)
		nombre_manoeuvres_differentes++;
	
	if(manoeuvre_radio_amc === true)
		nombre_manoeuvres_differentes++;
	
	if(manoeuvre_connectee_somfy === true)
		nombre_manoeuvres_differentes++;

	if(manoeuvre_radio_aok === true)
		nombre_manoeuvres_differentes++;

	if(manoeuvre_solaire_aok === true)
		nombre_manoeuvres_differentes++;
	
	if(nombre_manoeuvres_differentes == 1) {
		
		// on affiche les commandes groupées
		this.panier_afficher_les_commandes_groupees = manoeuvre;
		
		// on passe les autres à 0
		if(manoeuvre == 'radio_amc') {
			
			this.panier.telecommandes_supplementaires.radio_aok['COMGROUPAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_aok['BOXAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_aok['CSPAOKGROUPEE-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_aok['CSMAOKGROUPEE-amc'] = 0;
			
			this.panier.telecommandes_supplementaires.solaire_aok['COMGROUPAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.solaire_aok['BOXAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.solaire_aok['CSPAOKGROUPEE-amc'] = 0;
			this.panier.telecommandes_supplementaires.solaire_aok['CSMAOKGROUPEE-amc'] = 0;
		
			this.panier.telecommandes_supplementaires.radio_somfy['CRG6CAH-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_somfy['CRGMS2-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_somfy['CRGPS3-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_somfy['CRGE6CAH-amc'] = 0;
			
			this.panier.telecommandes_supplementaires.connectee_somfy['CMIG-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['CPIG-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['S5IO-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['HCRONISIO-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['NINATIMERIO-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['TAHOMA-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['CONNEXOON-amc'] = 0;		
		}
		else if(manoeuvre == 'radio_somfy') {
			
			this.panier.telecommandes_supplementaires.radio_aok['COMGROUPAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_aok['BOXAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_aok['CSPAOKGROUPEE-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_aok['CSMAOKGROUPEE-amc'] = 0;
			
			this.panier.telecommandes_supplementaires.solaire_aok['COMGROUPAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.solaire_aok['BOXAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.solaire_aok['CSPAOKGROUPEE-amc'] = 0;
			this.panier.telecommandes_supplementaires.solaire_aok['CSMAOKGROUPEE-amc'] = 0;

			this.panier.telecommandes_supplementaires.radio_amc['CGMPAH-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_amc['CRGMS-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_amc['CRGPS-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_amc['CRP4C-amc'] = 0;
			
			this.panier.telecommandes_supplementaires.connectee_somfy['CMIG-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['CPIG-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['S5IO-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['HCRONISIO-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['NINATIMERIO-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['TAHOMA-amc'] = 0;
			this.panier.telecommandes_supplementaires.connectee_somfy['CONNEXOON-amc'] = 0;		
		}
		else if(manoeuvre == 'connectee_somfy') {

			this.panier.telecommandes_supplementaires.radio_aok['COMGROUPAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_aok['BOXAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_aok['CSPAOKGROUPEE-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_aok['CSMAOKGROUPEE-amc'] = 0;
			
			this.panier.telecommandes_supplementaires.solaire_aok['COMGROUPAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.solaire_aok['BOXAOK-amc'] = 0;
			this.panier.telecommandes_supplementaires.solaire_aok['CSPAOKGROUPEE-amc'] = 0;
			this.panier.telecommandes_supplementaires.solaire_aok['CSMAOKGROUPEE-amc'] = 0;

			this.panier.telecommandes_supplementaires.radio_amc['CGMPAH-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_amc['CRGMS-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_amc['CRGPS-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_amc['CRP4C-amc'] = 0;
		
			this.panier.telecommandes_supplementaires.radio_somfy['CRG6CAH-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_somfy['CRGMS2-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_somfy['CRGPS3-amc'] = 0;
			this.panier.telecommandes_supplementaires.radio_somfy['CRGE6CAH-amc'] = 0;	
		}
	}
	else {
		
		// on les passe tous à 0

		this.panier.telecommandes_supplementaires.radio_aok['COMGROUPAOK-amc'] = 0;
		this.panier.telecommandes_supplementaires.radio_aok['BOXAOK-amc'] = 0;
		this.panier.telecommandes_supplementaires.radio_aok['CSPAOKGROUPEE-amc'] = 0;
		this.panier.telecommandes_supplementaires.radio_aok['CSMAOKGROUPEE-amc'] = 0;
		
		this.panier.telecommandes_supplementaires.solaire_aok['COMGROUPAOK-amc'] = 0;
		this.panier.telecommandes_supplementaires.solaire_aok['BOXAOK-amc'] = 0;
		this.panier.telecommandes_supplementaires.solaire_aok['CSPAOKGROUPEE-amc'] = 0;
		this.panier.telecommandes_supplementaires.solaire_aok['CSMAOKGROUPEE-amc'] = 0;

		this.panier.telecommandes_supplementaires.radio_amc['CGMPAH-amc'] = 0;
		this.panier.telecommandes_supplementaires.radio_amc['CRGMS-amc'] = 0;
		this.panier.telecommandes_supplementaires.radio_amc['CRGPS-amc'] = 0;
		this.panier.telecommandes_supplementaires.radio_amc['CRP4C-amc'] = 0;
		
		this.panier.telecommandes_supplementaires.radio_somfy['CRG6CAH-amc'] = 0;
		this.panier.telecommandes_supplementaires.radio_somfy['CRGMS2-amc'] = 0;
		this.panier.telecommandes_supplementaires.radio_somfy['CRGPS3-amc'] = 0;
		this.panier.telecommandes_supplementaires.radio_somfy['CRGE6CAH-amc'] = 0;
		
		this.panier.telecommandes_supplementaires.connectee_somfy['CMIG-amc'] = 0;
		this.panier.telecommandes_supplementaires.connectee_somfy['CPIG-amc'] = 0;
		this.panier.telecommandes_supplementaires.connectee_somfy['S5IO-amc'] = 0;
		this.panier.telecommandes_supplementaires.connectee_somfy['HCRONISIO-amc'] = 0;
		this.panier.telecommandes_supplementaires.connectee_somfy['NINATIMERIO-amc'] = 0;
		this.panier.telecommandes_supplementaires.connectee_somfy['TAHOMA-amc'] = 0;
		this.panier.telecommandes_supplementaires.connectee_somfy['CONNEXOON-amc'] = 0;		
	}
	
	// on vérifie les trucs impossibles
	
	
	this.maj_frais_de_livraison();
},

retire_commande_supplementaire_du_panier: function(code_article) {
	
	var manoeuvre = this.panier_afficher_les_commandes_groupees;
	
	if(this.panier.telecommandes_supplementaires[manoeuvre][code_article] > 0)
		this.panier.telecommandes_supplementaires[manoeuvre][code_article]--;
	
	this.maj_frais_de_livraison();
},

ajoute_commande_supplementaire_au_panier: function(code_article) {
	
	var manoeuvre = this.panier_afficher_les_commandes_groupees;
	
	if(code_article == 'TAHOMA-amc') {
		
		if(this.panier.telecommandes_supplementaires[manoeuvre][code_article] == 0) {
			
			this.panier.telecommandes_supplementaires[manoeuvre][code_article]++;
		}
		
		// on force connexoon à 0
		this.panier.telecommandes_supplementaires[manoeuvre]['CONNEXOON-amc'] = 0;
		
	}
	else if(code_article == 'CONNEXOON-amc') {
		
		if(this.panier.telecommandes_supplementaires[manoeuvre][code_article] == 0) {
			
			this.panier.telecommandes_supplementaires[manoeuvre][code_article]++;
		}
		
		// on force tahoma à 0
		this.panier.telecommandes_supplementaires[manoeuvre]['TAHOMA-amc'] = 0;
	}
	else {
		
		this.panier.telecommandes_supplementaires[manoeuvre][code_article]++;
	}
	
	
	
	this.maj_frais_de_livraison();
},

change_quantite_dans_panier: function(objet, ajoute) {
	
	if(ajoute === false) {
		
		if(objet.quantite > 1) { 
		
			objet.quantite--; 
		}
		
		this.maj_frais_de_livraison();
		this.maj_total_ttc_livre();
		
		return;
	}
	
	objet.quantite++;
	
	this.maj_frais_de_livraison();
	this.maj_total_ttc_livre();
},

change_quantite_dans_panier_emetteurs_supplementaires_porte_garage: function(porte_garage, ajoute) {
	
	// on retire
	if(ajoute === false) {
		
		if(porte_garage.garage_options_motorisations.emetteurs_supplementaires > 1) { 
		
			porte_garage.garage_options_motorisations.emetteurs_supplementaires--; 
		}
		
		this.maj_prix_porte(porte_garage);
		
		// on sauvegarde le panier
		// this.enregistre_panier();
		
		return;
	}
	
	// on ajoute
	if(porte_garage.garage_options_motorisations.emetteurs_supplementaires === false) {
		
		porte_garage.garage_options_motorisations.emetteurs_supplementaires = 1;
	}
	else {
		
		porte_garage.garage_options_motorisations.emetteurs_supplementaires++;
	}
	
	this.maj_prix_porte(porte_garage);
	
	// on sauvegarde le panier
	// this.enregistre_panier();
},

{{-- </script> --}}
