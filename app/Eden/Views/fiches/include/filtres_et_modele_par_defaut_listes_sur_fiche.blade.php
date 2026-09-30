@push('donnees_pour_vuejs_methods')
    construit_filtres_et_modele_par_defaut : function(mode) { 
		const retour = {};

		for (const bloc of Object.values(this.listes_sur_fiche)) {
			for (const fiche of Object.values(bloc)) {
				if (!retour[fiche.liste_libre.id])
					this.$set(retour, fiche.liste_libre.id, {});

				const dynamique = fiche[mode].dynamique;
				const cle = fiche[mode].cle;

				let valeur;

				if (dynamique == 0)
					valeur = fiche[mode].valeur;
				else {
					if (mode === "filtres_pour_fiche"){
						if (typeof fiche[mode].valeur !== 'object')
							valeur = this[fiche.v_model][fiche[mode].valeur];
						else {
							fiche[mode].valeur.elements_ids[0].id = this[fiche.v_model][fiche[mode].valeur.elements_ids[0].id];
							valeur = fiche[mode].valeur;
						}
					} else
						valeur = this[fiche.v_model][fiche[mode].valeur];
				}

				this.$set(retour[fiche.liste_libre.id], cle, valeur);
			}
		}

		return retour;
    },
@endpush

@push('donnees_pour_vuejs_data')
    @if(!empty($listes_sur_fiche))
        listes_sur_fiche : {!! collect($listes_sur_fiche) !!},
    @endif
@endpush

@push('donnees_pour_vuejs_computed')
    @if(!empty($listes_sur_fiche))

        filtres_pour_fiche: function(){
            return this.construit_filtres_et_modele_par_defaut("filtres_pour_fiche");
        },

        modele_par_defaut_fiche: function(){
            return this.construit_filtres_et_modele_par_defaut("modele_par_defaut");
        },
    @endif
@endpush