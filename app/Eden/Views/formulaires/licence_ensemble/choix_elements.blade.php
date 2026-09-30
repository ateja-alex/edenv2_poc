<div class="licence_ensemble">
    <div v-if="chargement" class="chargement_licence_ensemble">
        <img class="image_rotation" src="{{asset('eden/images/logo_eden.svg')}}" />
    </div>
    <div class="bloc_type_licence_ensemble" v-for="type in types" v-else>
        <div class="titre_bloc_type_licence_ensemble">
            <h5>@{{ $root.$options.filters.nom_valeur_liste_formatee(type.index,621) }}</h5>
            <div v-if="!modele_element[type.nom_elements].includes('tous')">
                <span @click="selection_total(informations[type.nom_elements],type.nom_elements)">@traduction('interface.licence.tout_selectionner')</span>
                <span>/</span>
                <span @click="selection_total(informations[type.nom_elements],type.nom_elements,true)">@traduction('interface.licence.tout_deselectionner')</span>
            </div>
            <input v-model="type.recherche" :placeholder="$root.traduction('interface.licence.rechercher')">
        </div>
        <div class="elements_bloc_type_licence_ensemble">

            <div class="element_titre_licence_ensemble">
                <input :id="type.nom_elements+'_tous'" type="checkbox" v-model="modele_element[type.nom_elements]" value="tous" :name="type.nom_elements+'[]'">
                <label :for="type.nom_elements+'_tous'">@traduction('interface.licence.tous')</label>
            </div>

            <routes v-if="type.index == 1" :recherche_route="type.recherche" :routes="informations.routes" :licence_ensemble="modele_element"></routes>

            <div class="bloc_elements_licence_ensemble" v-else-if="type.index == 2">
                <div class="element_titre_licence_ensemble" v-for="type_element in types_elements_tries" :key="type_element.valeur">
                    <input v-if="!modele_element.types_elements.includes('tous')" type="checkbox" :id="'type_element_'+type_element.valeur" name="types_elements[]" :value="type_element.valeur" v-model="modele_element.types_elements">
                    <label :for="'type_element_'+type_element.valeur">
                        <span v-html="type_element.nom"></span>
                        <span>( @{{ type_element.valeur }} )</span>
                    </label>
                </div>
            </div>

            <div class="bloc_elements_licence_ensemble" v-else>
                <div v-for="(modules,type) in modules_tries">
                    <div class="element_licence_ensemble">
                        <h6 v-html="nom_type_module(type)"></h6>
                        <div v-if="!modele_element.modules.includes('tous')">
                            <span @click="selection_total(modules,'modules')">@traduction('interface.licence.tout_selectionner')</span>
                            <span>/</span>
                            <span @click="selection_total(modules,'modules',true)">@traduction('interface.licence.tout_deselectionner')</span>
                        </div>
                        <i @click="affichage_modules.includes(type) ? affichage_modules.splice(affichage_modules.indexOf(type),1) : affichage_modules.push(type)"
                           :class="'fas fa-chevron-'+(affichage_modules.includes(type) ? 'up' : 'down')"></i>
                    </div>
                    <div class="bloc_module_licence_ensemble" v-show="affichage_modules.includes(type)">
                        <div class="element_licence_ensemble" v-for="module of modules" :key="module.valeur">
                            <div class="element_titre_licence_ensemble">
                                <input v-if="!modele_element.modules.includes('tous')" type="checkbox" :id="'module_'+module.valeur" name="modules[]" :value="module.valeur" v-model="modele_element.modules">
                                <label :for="'module_'+module.valeur">
                                    <span v-html="module.nom"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')

    types : {
        route : {
            index : 1,
            nom_elements : 'routes',
            recherche : ''
        },
        type_element : {
            index : 2,
            nom_elements : 'types_elements',
            recherche : '',
        },
        module : {
            index : 3,
            nom_elements : 'modules',
            recherche : '',
        },
    },
    affichage_modules : [],
    chargement : true,
@endpush

@push('donnees_pour_vuejs_mounted')
    await $.post({
        url : '{{route('maintenance.licence.informations')}}',
        dataType:'json',
        data : {
            licence_ensemble_id : this.licence_ensemble.id,
        }
    }).done((donnees) => {

        for(cle in donnees.valeurs_elements){

            this.$set(this.licence_ensemble,cle,donnees.valeurs_elements[cle]);

        }

        delete donnees.valeurs_elements;

        this.$set(this.$root.cache_formulaires,'informations_licences',donnees);

        this.chargement = false;
    });
@endpush

@push('donnees_pour_vuejs_computed')

    modele_element : function(){

        var modele_element = this.licence_ensemble;

        for(type of Object.values(this.types)){

            if(modele_element[type.nom_elements] == undefined)
                this.$set(modele_element,type.nom_elements,[]);
        }

        return modele_element;
    },

    informations : function(){

        if(this.$root.cache_formulaires.informations_licences == undefined){

            return {
                types_elements : [],
                routes : [],
                modules_generaux : [],
                modules_type_element : [],
                modules : [],
            };
        }

        var informations = this.$root.cache_formulaires.informations_licences;

        var types_elements = this.modele_element.types_elements;

        if(types_elements.includes('tous')){

            var modules_par_type = informations.modules_generaux;

            if(informations.modules_type_element != undefined){

                for(type_element of Object.keys(informations.modules_type_element)){

                    if(Object.values(informations.modules_type_element[type_element]).length > 0)
                        modules_par_type[type_element] = structuredClone(informations.modules_type_element[type_element]);
                }

            }

            informations.modules = modules_par_type;

            return informations;
        }

        var modules_par_type = {
            'general' : informations.modules_generaux.general
        };

        var document_de_vente = {!! collect(\App\Eden\Variables::$documents_vente_gescom) !!};

        var document_d_achat = {!! collect(\App\Eden\Variables::$documents_achat_gescom) !!};

        if(informations.modules_type_element != undefined){

            for(type_element of types_elements){

                if(document_de_vente.includes(type_element) && modules_par_type.document_vente == undefined)
                    modules_par_type.document_vente = informations.modules_generaux.document_vente;

                if(document_d_achat.includes(type_element) && modules_par_type.document_achat == undefined)
                    modules_par_type.document_achat = informations.modules_generaux.document_achat;

                if(informations.modules_type_element[type_element] != undefined && Object.values(informations.modules_type_element[type_element]).length > 0)
                    modules_par_type[type_element] = structuredClone(informations.modules_type_element[type_element]);
            }

        }

        informations.modules = modules_par_type;

        return informations;
    },

    types_elements_tries : function(){

        var recherche = this.types.type_element.recherche;

        var types_elements_ordonnes = [];

        for(type_element of this.informations.types_elements){

            var nom = this.$root.traduction('tables_libres.'+type_element+'.nom_table');

            types_elements_ordonnes.push({
             'nom' : nom.charAt(0).toUpperCase() + nom.slice(1),
             'valeur' : type_element
            });
        }

        types_elements_ordonnes.sort((a, b) => {

            if(this.licence_ensemble.types_elements.includes(a.valeur) && !this.licence_ensemble.types_elements.includes(b.valeur))
                return -1;

            if(!this.licence_ensemble.types_elements.includes(a.valeur) && this.licence_ensemble.types_elements.includes(b.valeur))
                return 1;

            if(this.licence_ensemble.types_elements.includes(a.valeur) && this.licence_ensemble.types_elements.includes(b.valeur))
                return (this.licence_ensemble.types_elements.indexOf(a.valeur) < this.licence_ensemble.types_elements.indexOf(b.valeur)) ? -1 : 1;

            if (a.nom === b.nom)
                return 0;

            return (a.nom < b.nom) ? -1 : 1;
        });

        var types_elements = [];

        if(recherche == null || recherche == '')
            return types_elements_ordonnes;

        for(type_element of types_elements_ordonnes){

            if(type_element.valeur.toLowerCase().includes(recherche) || type_element.nom.toLowerCase().includes(recherche) ||
                this.licence_ensemble.types_elements.includes(type_element.valeur))
                types_elements.push(type_element);
        }

        return types_elements;
    },

    modules_tries : function(){

        var recherche = this.types.module.recherche;

        var modules_par_type = structuredClone(this.informations.modules);

        for(modules of Object.values(modules_par_type)){

            modules.sort((a, b) => {

                if(this.licence_ensemble.modules.includes(a.valeur) && !this.licence_ensemble.modules.includes(b.valeur))
                    return -1;

                if(!this.licence_ensemble.modules.includes(a.valeur) && this.licence_ensemble.modules.includes(b.valeur))
                    return 1;

                if(this.licence_ensemble.modules.includes(a.valeur) && this.licence_ensemble.modules.includes(b.valeur))
                    return (this.licence_ensemble.modules.indexOf(a.valeur) < this.licence_ensemble.modules.indexOf(b.valeur)) ? -1 : 1;

                if (a.nom === b.nom)
                    return 0;

                return (a.nom < b.nom) ? -1 : 1;
            });

        }

        if(recherche == null || recherche == '')
            return modules_par_type;

        var modules_tries = {};

        for(type in modules_par_type){

            for(module of modules_par_type[type]){

                if(module.valeur.toLowerCase().includes(recherche) || module.nom.toLowerCase().includes(recherche) || this.licence_ensemble.modules.includes(module.valeur)){

                    if(modules_tries[type] == undefined)
                        modules_tries[type] = [];

                    modules_tries[type].push(module);
                }
            }
        }

        return modules_tries;
    },

@endpush

@push('donnees_pour_vuejs_watch')

    'modele_element.types_elements' : function(nouvelle_valeur,ancienne_valeur){

        var nouveaux_elements = nouvelle_valeur.filter(x => !ancienne_valeur.includes(x));

        if(nouvelle_valeur.includes('tous'))
            nouveaux_elements = this.informations.types_elements;

        var types_elements_a_charger = [];

        for(type_element of nouveaux_elements){

            if(this.informations.modules_type_element == undefined || this.informations.modules_type_element[type_element] == undefined)
                types_elements_a_charger.push(type_element);
        }

        if(types_elements_a_charger.length > 0)
            this.modules_type_element(types_elements_a_charger);
    },

@endpush

@push('donnees_pour_vuejs_methods')

    modules_type_element : function(types_elements_a_charger){

        $.post({
            url : '{{route('maintenance.licence.modules_type_element')}}',
            dataType:'json',
            data : {
                types_elements : types_elements_a_charger,
            }
        }).done((donnees) => {

            for(type_element of types_elements_a_charger){

                this.$set(this.$root.cache_formulaires.informations_licences.modules_type_element,type_element,donnees.modules[type_element]);

            }
        });
    },

    selection_total : function(elements,nom_elements,deselectionne = false){

        var nouveau_modele = structuredClone(this.licence_ensemble[nom_elements]);

        var elements_modules = [];

        if(!Array.isArray(elements)){

            for(elements_par_type of Object.values(elements)){

                for(element of elements_par_type){
                    elements_modules.push(element);
                }
            }

            elements = elements_modules;
        }

        for(element of elements){

            var index = element.index == undefined ? (element.valeur == undefined ? element : element.valeur) : element.index;

            if(!deselectionne && !nouveau_modele.includes(index))
                nouveau_modele.push(index);
            else if(deselectionne && nouveau_modele.includes(index))
                nouveau_modele.splice(nouveau_modele.indexOf(index),1);
        }

        this.licence_ensemble[nom_elements] = nouveau_modele;
    },

    nom_type_module : function(nom){

        if(['general','document_achat','document_vente'].includes(nom))
            return this.$root.traduction('interface.licence_ensemble.type_module.'+nom);

        return this.$root.traduction('tables_libres.'+nom+'.nom_table') + ' ('+nom+')';
    },
@endpush

@push('donnees_pour_vuejs_created')
    @include('eden::formulaires.licence_ensemble.composant_route')
@endpush