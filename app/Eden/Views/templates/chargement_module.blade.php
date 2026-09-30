@push('donnees_pour_vuejs_data')
    cache: {
        champ_conditionnel: {},
        requete_champ_conditionnel: {},
        affichage_element: {},
        requete_affichage_element: {},
        valeurs_champs: {},
        requete_valeurs_champs: {},
    },
    delai_inactivite_module : 30,
    delai_plafond_module : 300,
    taille_maximum_lot_module : 200,
    modules_a_generer : [
        {
            id : 'liste',
            url : "{{ route('base_eden.liste.initialisation_multiple') }}",
            elements_a_charger : [],
            loaded:false,
            timer_inactivite : null,
            timer_plafond : null,
        },
        {
            id : 'affichage_element',
            url : "{{ route('base_eden.element.affichage') }}",
            elements_a_charger : [],
            loaded:false,
            timer_inactivite : null,
            timer_plafond : null,
        },
        {
            id : 'valeurs_champs',
            url : "{{ route('base_eden.champ.valeurs_multiples') }}",
            elements_a_charger : [],
            loaded:false,
            timer_inactivite : null,
            timer_plafond : null,
        }
    ],
@endpush

@push('donnees_pour_vuejs_mounted')

    this.requete_charger_module(this.modules_a_generer[0]);

@endpush

@push('donnees_pour_vuejs_methods')

    charger_element_module : function(id_module, id_element, donnees_requete){

        var module = this.modules_a_generer.filter(m => m.id == id_module)[0];

        if(module == undefined)
            return Promise.resolve(undefined);

        var resolveFn;

        var promise = new Promise((resolve, reject) => {
            resolveFn = resolve;
        });

        module.elements_a_charger.push({
            id : id_element,
            donnees_requete : donnees_requete,
            promise_resolve : resolveFn,
        });

        this.planifier_chargement_module(module);

        return promise;
    },

    libelle_element_lecture : function(type_element, element_id){

        if(!type_element || !element_id)
            return '';

        var element = this.cache.affichage_element[type_element + '_' + element_id];

        if(element === undefined){

            this.recuperer_affichage_element(type_element, element_id);

            return '';
        }

        return element === null ? '' : (element.affiche_lien_pour_select ?? element.chaine_affichage ?? '');
    },

    recuperer_affichage_element : function(type_element, element_id){

        if(!type_element || !element_id)
            return Promise.resolve(null);

        var index_cache = type_element+'_'+element_id;

        if(this.cache.affichage_element[index_cache] !== undefined)
            return Promise.resolve(this.cache.affichage_element[index_cache]);

        if(this.cache.requete_affichage_element[index_cache] !== undefined)
            return this.cache.requete_affichage_element[index_cache];

        this.cache.requete_affichage_element[index_cache] =
            this.charger_element_module('affichage_element', index_cache, {
                type_element: type_element,
                element_id: element_id,
            }).then((element) => {

                if(element === undefined)
                    element = null;

                this.$set(this.cache.affichage_element, index_cache, element);
                this.$delete(this.cache.requete_affichage_element, index_cache);

                return element;
            });

        return this.cache.requete_affichage_element[index_cache];
    },

    recuperer_champ_libre : function(type_element, nom_sql){

        if(!type_element || !nom_sql)
            return Promise.resolve(null);

        var index_cache = type_element+'_'+nom_sql;

        if(this.cache.valeurs_champs[index_cache] !== undefined)
            return Promise.resolve(this.cache.valeurs_champs[index_cache]);

        if(this.cache.requete_valeurs_champs[index_cache] !== undefined)
            return this.cache.requete_valeurs_champs[index_cache];

        this.cache.requete_valeurs_champs[index_cache] =
            this.charger_element_module('valeurs_champs', index_cache, {
                type_element : type_element,
                nom_sql : nom_sql,
            }).then((champ_libre) => {

                if(champ_libre === undefined)
                    champ_libre = null;

                this.$set(this.cache.valeurs_champs, index_cache, champ_libre);
                this.$delete(this.cache.requete_valeurs_champs, index_cache);

                return champ_libre;
            });

        return this.cache.requete_valeurs_champs[index_cache];
    },

    planifier_chargement_module : function(module){

        if(module.elements_a_charger.length >= this.taille_maximum_lot_module){
            this.requete_charger_module(module);
            return;
        }

        if(module.timer_inactivite)
            clearTimeout(module.timer_inactivite);

        module.timer_inactivite = setTimeout(() => {
            this.requete_charger_module(module);
        }, this.delai_inactivite_module);

        if(!module.timer_plafond){

            module.timer_plafond = setTimeout(() => {
                this.requete_charger_module(module);
            }, this.delai_plafond_module);
        }
    },

    annuler_timers_module : function(module){

        if(module.timer_inactivite){
            clearTimeout(module.timer_inactivite);
            module.timer_inactivite = null;
        }

        if(module.timer_plafond){
            clearTimeout(module.timer_plafond);
            module.timer_plafond = null;
        }
    },

    charger_modules : function(){

        for(module of this.modules_a_generer){
            this.requete_charger_module(module);
        }
    },

    requete_charger_module : function(module){

        module.loaded = true;

        this.annuler_timers_module(module);

        if(module.elements_a_charger.length == 0)
            return;

        var elements_a_charger = module.elements_a_charger;

        module.elements_a_charger = [];

        $.post({
            url: module.url,
            dataType: "json",
            method: 'post',
            data: {
                donnees : elements_a_charger.map(e => e.donnees_requete),
            }
        }).done((donnees) => {

            for(element of elements_a_charger) {
                element.promise_resolve(donnees[element.id]);
            }

        });
    },
@endpush
