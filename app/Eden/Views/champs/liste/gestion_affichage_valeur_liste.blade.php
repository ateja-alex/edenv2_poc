@push('donnees_pour_vuejs_methods')
    affichage_valeur_liste_libre(modele,liste,valeurs_listes,type_element,nom_sql){

        if(valeurs_listes === undefined || valeurs_listes[type_element] === undefined ||
            valeurs_listes[type_element][nom_sql] === undefined || valeurs_listes[type_element][nom_sql].parent === undefined )
            return liste;

        var liaisons = valeurs_listes[type_element][nom_sql].parent;

        var valeur_parent = modele[liaisons.champ_liste_libre_parent];

        var liste_a_afficher = [];

        if(valeur_parent == null || valeur_parent === 0 || valeur_parent === undefined || valeur_parent === '')
            return liste;

        if(typeof valeur_parent == 'object') {

            if(!Array.isArray(valeur_parent)){
                var valeur_parent_formatee = [];

                for(index of Object.keys(valeur_parent)){
                    if(valeur_parent[index])
                        valeur_parent_formatee.push(parseInt(index));
                }

                valeur_parent = valeur_parent_formatee;
            }

            if(valeur_parent.length === 0)
                return liste;

            $.each(liste,function(index,valeur_liste){

                if(valeur_liste.id_valeur === 0)
                    liste_a_afficher.push(valeur_liste);

                if(liaisons.champ_liste_libre_liaisons[valeur_liste.id_valeur] !== undefined){
                    var liaisons_valeurs = Object.values(liaisons.champ_liste_libre_liaisons[valeur_liste.id_valeur]);

                    if(liaisons_valeurs.filter(x => valeur_parent.indexOf(x) !== -1).length > 0)
                        liste_a_afficher.push(valeur_liste);
                }
            });
        }
        else{
            valeur_parent = parseInt(valeur_parent);

            $.each(liste,function(index,valeur_liste){

                if(valeur_liste.id_valeur === 0)
                    liste_a_afficher.push(valeur_liste);

                if(liaisons.champ_liste_libre_liaisons[valeur_liste.id_valeur] !== undefined){

                    var liaisons_valeurs = Object.values(liaisons.champ_liste_libre_liaisons[valeur_liste.id_valeur]);

                    if(liaisons_valeurs.includes(valeur_parent))
                        liste_a_afficher.push(valeur_liste);
                }
            });
        }

        return liste_a_afficher;
    },

    changement_valeurs_liste : function(modele,valeurs_listes,type_element,nom_sql){

        if(valeurs_listes === undefined || valeurs_listes[type_element] === undefined ||
            valeurs_listes[type_element][nom_sql] === undefined)
            return;

        var liaisons = valeurs_listes[type_element][nom_sql];

        var valeur = structuredClone(modele[nom_sql]);

        if(valeur !== null && valeur !== 0 && typeof valeur !== 'object' && liaisons.parent !== undefined){

            if(liaisons.parent.champ_liste_libre_liaisons[valeur] !== undefined) {

                var liaisons_valeurs = Object.values(liaisons.parent.champ_liste_libre_liaisons[valeur]);

                if(liaisons_valeurs.length === 1) {

                    if(modele[liaisons.parent.champ_liste_libre_parent] !== null && typeof modele[liaisons.parent.champ_liste_libre_parent] === 'object')
                        modele[liaisons.parent.champ_liste_libre_parent].push(liaisons_valeurs[0]);
                    else if(modele[liaisons.parent.champ_liste_libre_parent] !== liaisons_valeurs[0]){
                        modele[liaisons.parent.champ_liste_libre_parent] = liaisons_valeurs[0];

                        this.changement_valeurs_liste(modele,this.valeurs_listes_libres[liaisons.parent.id_cl_parent].liaisons,type_element,liaisons.parent.champ_liste_libre_parent);
                    }
                }
            }
        }

        if(valeur !== null && valeur !== 0 && liaisons.enfants !== undefined) {

            $.each(liaisons.enfants, function(index,liaison_enfant){

                var valeur_enfant = structuredClone(modele[liaison_enfant.champ_liste_libre_enfant]);

                if(valeur_enfant !== null && valeur_enfant !== 0) {

                    if(typeof valeur_enfant == 'object'){

                        if(!Array.isArray(valeur_enfant)){
                            var valeur_enfant_formatee = [];

                            for(index of Object.keys(valeur_enfant)){
                                if(valeur_enfant[index])
                                    valeur_enfant_formatee.push(parseInt(index));
                            }

                            valeur_enfant = valeur_enfant_formatee;
                        }

                        for(id_valeur_liste of valeur_enfant){

                            if(typeof valeur == 'object' && (
                                    liaison_enfant.champ_liste_libre_liaisons[id_valeur_liste] === undefined ||
                                    valeur.filter(x => liaison_enfant.champ_liste_libre_liaisons[id_valeur_liste].indexOf(x) !== -1).length === 0
                                )
                            ){
                                modele[liaison_enfant.champ_liste_libre_enfant].splice(modele[liaison_enfant.champ_liste_libre_enfant].indexOf(id_valeur_liste),1);
                            }

                            else if(typeof valeur !== 'object' && (
                                    liaison_enfant.champ_liste_libre_liaisons[id_valeur_liste] === undefined
                                || !liaison_enfant.champ_liste_libre_liaisons[id_valeur_liste].includes(valeur))
                            ) {
                                modele[liaison_enfant.champ_liste_libre_enfant].splice(modele[liaison_enfant.champ_liste_libre_enfant].indexOf(id_valeur_liste),1);
                            }
                        }
                    }
                    else {
                        if (liaison_enfant.champ_liste_libre_liaisons[valeur_enfant] !== undefined) {

                            if(typeof valeur == 'object' && valeur.filter(x => liaison_enfant.champ_liste_libre_liaisons[valeur_enfant].indexOf(x) !== -1).length === 0)
                                modele[liaison_enfant.champ_liste_libre_enfant] = 0;
                            else if(typeof valeur !== 'object' && !liaison_enfant.champ_liste_libre_liaisons[valeur_enfant].includes(valeur))
                                modele[liaison_enfant.champ_liste_libre_enfant] = 0;
                        }
                    }

                }
            });
        }
    },
@endpush