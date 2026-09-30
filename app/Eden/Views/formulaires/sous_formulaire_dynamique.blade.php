@push('donnees_pour_vuejs_data')

    sous_formulaires_par_type_element : {},
    sous_formulaires_a_include : {},
    sous_formulaires_retraite : {},
    sous_formulaire_obligatoire : {},
    
@endpush

@push('donnees_pour_vuejs_methods')

    ajout_sous_formulaire_optionnel : async function(sous_formulaire, type_element_parent){

        var vue_instance = this;
    
        sous_formulaire = structuredClone(sous_formulaire);
    
        sous_formulaire.type_element_parent = type_element_parent;
        var type_element_remplacement = sous_formulaire.type_element_enfant;
    
        if(sous_formulaire.type_element_remplacement != undefined && sous_formulaire.type_element_remplacement != '')
            type_element_remplacement = sous_formulaire.type_element_remplacement;
    
        var type_element_remplacement_increment = type_element_remplacement;
    
        if(vue_instance.sous_formulaires_par_type_element[type_element_remplacement] != undefined && vue_instance.sous_formulaires_par_type_element[type_element_remplacement].length > 0){
            var increment = vue_instance.sous_formulaires_par_type_element[type_element_remplacement].length;
        
            while(vue_instance[type_element_parent][type_element_remplacement + '_' + increment] != undefined){
                increment++;
            }
        
            sous_formulaire.type_element_remplacement = type_element_remplacement + '_' + increment;
            type_element_remplacement_increment = type_element_remplacement + '_' + increment;
    
        }

        if(vue_instance.$root.cache_sous_formulaires[type_element_remplacement] != undefined){
            vue_instance.retour_affichage_sous_formulaire(vue_instance.$root.cache_sous_formulaires[type_element_remplacement], sous_formulaire, type_element_parent, type_element_remplacement_increment, type_element_remplacement);
            return;
        }

        await $.post({
            url: '{{ URL::to('/eden/formulaire/sous_formulaire/affichage') }}',
            datatype: 'Json',
            data: sous_formulaire,
        }).done(async function(donnees){
    
            if(donnees.erreur !== false){
        
                await alerte_eden(donnees.erreur);
                return;
        
            }

            vue_instance.retour_affichage_sous_formulaire(donnees, sous_formulaire, type_element_parent, type_element_remplacement_increment, type_element_remplacement);
            vue_instance.$root.cache_sous_formulaires[type_element_remplacement] = donnees;
    
        });
    
    },

    retour_affichage_sous_formulaire: async function(donnees, sous_formulaire, type_element_parent, type_element_remplacement_increment, type_element_remplacement){
    
        var vue_instance = this;

        if(donnees.modele_par_defaut != undefined){
            for (const [cle, valeur] of Object.entries(donnees.modele_par_defaut)) {
                if(typeof valeur === 'string' && /#([\w.]+)#/g.test(valeur)) {
                    donnees.modele_par_defaut[cle] = valeur.replace(/#([\w.]+)#/g, (_, valeur_a_remplacer) => {
                        return valeur_a_remplacer.split('.').reduce((obj, key) => (obj && key in obj) ? obj[key] : undefined, this);
                    });
                }
            }
        }
    
        if(type_element_remplacement_increment != undefined)
            vue_instance.$set(vue_instance[type_element_parent], type_element_remplacement_increment, structuredClone(donnees.modele_par_defaut));
        else
            vue_instance.$set(vue_instance[type_element_parent], type_element_remplacement, structuredClone(donnees.modele_par_defaut));
    
        if(vue_instance.sous_formulaires_par_type_element[type_element_remplacement] == undefined)
            vue_instance.sous_formulaires_par_type_element[type_element_remplacement] = new Array();
    
        if(sous_formulaire.nom_sous_formulaire != undefined)
            var nom_sous_formulaire = sous_formulaire.nom_sous_formulaire;
        else
            var nom_sous_formulaire = sous_formulaire.type_element_enfant;
    
        if(sous_formulaire.nom_formulaire_parent != undefined)
            var nom_formulaire_parent = sous_formulaire.nom_formulaire_parent;
        else
            var nom_formulaire_parent = sous_formulaire.type_element_parent;
    
        eval(donnees.formulaire);

        var name_remplacement_js = donnees.name_remplacement_js;

        if(type_element_remplacement_increment != undefined){
    
            var tableau_a_remplacer = [
                new RegExp('([^.|_|\[])' + type_element_remplacement, "g"),
                new RegExp(type_element_parent + '.' + type_element_remplacement, "g"),
                new RegExp(type_element_parent + '_creation_' + type_element_remplacement, "g"),
                'type_element_origine="'+type_element_remplacement_increment+'"'
            ];
            
            var tableau_remplacement = [
                '$1' + type_element_remplacement_increment,
                type_element_parent + '.' + type_element_remplacement_increment,
                type_element_parent + '_creation_' + type_element_remplacement_increment,
                'type_element_origine="'+type_element_remplacement+'"'
            ];
    
            for(var i = 0; i < tableau_a_remplacer.length; i++){
            
                formulaire.template = formulaire.template.replaceAll(tableau_a_remplacer[i], tableau_remplacement[i]);
                name_remplacement_js = name_remplacement_js.replaceAll(tableau_a_remplacer[i], tableau_remplacement[i]);

            }
    
        }
    
        formulaire.nom_sous_formulaire = nom_sous_formulaire;
        formulaire.nom_formulaire_parent = nom_formulaire_parent;
        formulaire.modele_par_defaut = structuredClone(donnees.modele_par_defaut);
        formulaire.name = "sous_formulaire_" + type_element_remplacement_increment;
        formulaire.type_element_remplacement = (type_element_remplacement_increment != undefined ? type_element_remplacement_increment : type_element_remplacement);
        formulaire.name_remplacement_js = name_remplacement_js;

        vue_instance.sous_formulaires_par_type_element[type_element_remplacement].push(formulaire);
        vue_instance.$forceUpdate();
        vue_instance.$parent.$parent.$emit('sous_formulaire_charge',formulaire.type_element_remplacement);
    },
    
    supprime_sous_formulaire: function(index_sous_formulaire, type_element_remplacement){

        var vue_instance = this;
    
        var sous_formulaire = vue_instance.sous_formulaires_par_type_element[type_element_remplacement].splice(index_sous_formulaire, 1);

        this.$delete(this.{{ $type_element }},sous_formulaire[0].type_element_remplacement);
    
        vue_instance.$forceUpdate();

    },
    
    afficher_bouton_sous_formulaire: function(sous_formulaire){

        var vue_instance = this;
    
        var nom_data = sous_formulaire.type_element_enfant;
    
        if(sous_formulaire.type_element_remplacement != undefined && sous_formulaire.type_element_remplacement != "")
            nom_data = sous_formulaire.type_element_remplacement;
    
        if(sous_formulaire.unique !== 1)
            return true;
    
        if(vue_instance.sous_formulaires_par_type_element[nom_data] == undefined || vue_instance.sous_formulaires_par_type_element[nom_data].length < (sous_formulaire.nombre ?? 1))
            return true;
    
        return false;
    
    },

    affichage_fermeture_sous_formulaire: function(sous_formulaire){

        var vue_instance = this;
    
        var nom_data = sous_formulaire.type_element_enfant;
    
        if(sous_formulaire.type_element_remplacement != undefined && sous_formulaire.type_element_remplacement != "")
            nom_data = sous_formulaire.type_element_remplacement;
    
        if(sous_formulaire.optionnel === 1)
            return true;
    
        if(vue_instance.sous_formulaires_par_type_element[nom_data] !== undefined && vue_instance.sous_formulaires_par_type_element[nom_data].length > 1)
            return true;
    
        return false;
    
    },
    
@endpush