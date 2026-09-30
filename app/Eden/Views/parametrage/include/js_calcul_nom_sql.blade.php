@push('donnees_pour_vuejs_methods')

	recupere_valeur_par_chemin(chemin){

		return chemin.split('.').reduce((accumulateur, index) => accumulateur?.[index], this);
	},

	modifie_valeur_par_chemin(chemin, valeur){

		const cles = chemin.split('.');
		const derniere_cle = cles.pop();
		let cible = this;

		for (let i = 0; i < cles.length; i++) {

			cible = cible[cles[i]];
		}
		
  		cible[derniere_cle] = valeur;
	},

	calcul_nom_sql(v_model_champ_nom_sql, v_model_champ_nom =  '') {

		var nom_sql = this.recupere_valeur_par_chemin(v_model_champ_nom === '' ? v_model_champ_nom_sql : v_model_champ_nom);
		
		// on crée le nom_sql
		var accents = [

			/[\300-\306]/g, /[\340-\346]/g, // A, a
			/[\310-\313]/g, /[\350-\353]/g, // E, e
			/[\314-\317]/g, /[\354-\357]/g, // I, i
			/[\322-\330]/g, /[\362-\370]/g, // O, o
			/[\331-\334]/g, /[\371-\374]/g, // U, u
			/[\321]/g, /[\361]/g, // N, n
			/[\307]/g, /[\347]/g, // C, c
		];

		var sans_accents =['A','a','E','e','I','i','O','o','U','u','N','n','C','c'];

		for(var i = 0; i < accents.length; i++){

			nom_sql = nom_sql.replace(accents[i], sans_accents[i]);
		}

		nom_sql = nom_sql.toLowerCase();

		// autres caractères spéciaux
		var a_remplacer = [/[\41-\57]/g, /[\72-\100]/g, /[\133-\140]/g, /[\173-\176]/g,/–/g, /¤/g, /£/g, /§/g, /µ/g, /¨/g, /;/g, /°/g,/ /g,/’/g,/²/g,/€/g];

		for(var n = 0; n < a_remplacer.length; n++){

			nom_sql = nom_sql.replace(a_remplacer[n], "_");
		}

		for(index = nom_sql.length;index > 0;index--){

			var lettre = nom_sql[index];

			if(index > 0 && nom_sql[index-1] == '_' && lettre == '_')
				nom_sql = nom_sql.slice(0, index -1) + nom_sql.slice(index);
		}

		this.modifie_valeur_par_chemin(v_model_champ_nom_sql, nom_sql);
		this.$forceUpdate();
	},
@endpush