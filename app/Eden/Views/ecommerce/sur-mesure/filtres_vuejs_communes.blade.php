oui_non: function(value) {

	return value ? 'Oui' : 'Non';
},
// filtre porte de garage
type_panneaux: function(value) {
	if(value == 'ligne_veine_bois') {
		return 'Ligné veiné bois';
	} else if (value == 'cassettes_veine_bois') {
		return 'Cassettes veiné bois';
	} else if (value == 'plat_veine_bois') {
		return 'Plat veiné bois';
	} else if (value == 'plat_lisse') {
		return 'Plat lisse';
	}
},
type_hublots: function(value) {
	if(value == 'rectangle_double_vitrage_verre') {
		return 'Rectangle double vitrage';
	} else if (value == 'rectangle_plexi') {
		return 'Rectangle plexi';
	} else if (value == 'carre_plexi') {
		return 'Carré plexi';
	} else if (value == 'rond_plexi') {
		return 'Rond plexi';
	} else if (value == 'carre_inox_double_vitrage') {
		return 'Carré inox double vitrage';
	} else if (value == 'pas_de_hublot') {
		return 'Pas de hublots';
	}
},
emplacement_hublots: function(value) {
	if(value == 'horizontaux') {
		return 'Horizontaux';
	} else if (value == 'verticaux_gauche') {
		return 'Verticaux à gauche';
	} else if (value == 'verticaux_droite') {
		return 'Verticaux à droite';
	} else {
		return '-';
	}
},
manoeuvre_porte_garage: function(value) {
	if(value == 'motorisee_amc') {
		return 'Motorisée amc';
	} else {
		return 'Manuelle';
	}
},
// Filtre volet roulant
matiere_volet_roulant: function(value) {
	if(value == 'alu') {
		return 'Aluminium';
	} else if (value == 'pvc') {
		return 'P.V.C'
	}
},
coffre_volet_roulant: function(value) {
	if(value == 'pas_de_coffre') {
		return 'Pas de coffre';
	} else if (value == '45') {
		return 'Forme pan coupe 45%';
	} else if (value == 'rond') {
		return 'Forme quart de rond';
	} else {
		return '-';
	}
},
manoeuvre_volet: function(value) {
	if(value == 'sangle') {
		return 'A sangle';
	} else if (value == 'tirage_direct') {
		return 'Tirage direct';
	} else if (value == 'manivelle') {
		return 'A manivelle';
	} else if (value == 'electrique_amc') {
		return 'Electrique AMC';
	} else if (value == 'electrique_somfy') {
		return 'Electrique Somfy';
	} else if (value == 'radio_amc') {
		return 'Radio AMC';
	} else if (value == 'radio_somfy') {
		return 'Radio Somfy';
	} else if (value == 'connectee_somfy') {
		return 'Connectée Somfy';
	} else if (value == 'solaire_somfy') {
		return 'Solaire Somfy';
	}
},
verrou_volet: function(value) {
	if(value == 'pas_de_verrou') {
		return 'Pas de verrou';
	} else if (value == 'automatique') {
		return 'Automatique anti-soulèvement';
	} else if (value == 'manuel_lame_finale') {
		return 'Manuel sur lame finale';
	} else if (value == 'cle_lame_intermediaire') {
		return 'Serrure à clef sur lame intermédiaire';
	} else {
		return '-';
	}
},
// Filtre tablier de volet roulant
epaisseur_lame_tablier: function(value) {
	if(value == 'AL39') {
		return 'ALU 39 mm';
	} else if (value == 'AL54') {
		return 'ALU 54 mm';
	} else if (value == 'P40') {
		return 'P.V.C 40 mm';
	} else if (value == 'P55') {
		return 'P.V.C 55 mm';
	}
},
verrouillage_tablier: function(value) {
	if(value == 'sans') {
		return 'Sans verrou';
	} else if (value == 'verrou_manuel') {
		return 'Verrou manuel sur lame finale';
	} else if (value == 'serrure_a_clef_sur_lame_intermediaire') {
		return 'Serrure à clef sur lame intermédiaire';
	} else if (value == 'serrure_a_clef_sur_lame_finale') {
		return 'Serrure à clef sur lame finale';
	}
},
attaches_tablier: function(value) {
	if(value == 'sans') {
		return 'Sans attache';
	} else if (value == 'standard') {
		return 'Attaches standards métalliques';
	} else if (value == 'sangles') {
		return 'Attaches sangles';
	}
},

affiche_tarif: function(tarif) {
	
	if(tarif == undefined)
		return '';
	
	tarif = parseFloat(tarif);
	
	if(tarif == '' || tarif == 0) {
		
		return 'Offert';
	}
	
	return tarif.toFixed(2)+' €';
},

affiche_tarif_sans_offert: function(tarif) {
	
	if(tarif == undefined)
		return '';
	
	tarif = parseFloat(tarif);
	
	if(tarif == '') {
		
		return '0.00 €';
	}
	
	return tarif.toFixed(2)+' €';
},