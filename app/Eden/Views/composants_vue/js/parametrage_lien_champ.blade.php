<script>
    const parametrage_lien_champ = Vue.component('parametrage-lien-champ', {
        template: `
            <div class="parametrage_lien_champ">
                <div class="affichages_valeurs">
                    <div class="valeur" v-for="(lien_champ, index_lien) in liens_champs">
                        <template v-if="lien_champ.possibilites.length > 0 || lien_champ.affichage_table_libre == 1">
                            <span v-if="index_lien > 0">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                            <select v-model="lien_champ.selection" @change="gestion_champ_selectionne(lien_champ,index_lien)">
                                <option v-if="!valeur_unique && lien_champ.affichage_table_libre != 2" value="table_libre" 
                                v-html="$root.traduction('composant.parametrage_lien_champ.table_libre')"></option>
                                <option v-for="table_libre in Object.keys(lien_champ.element_table_libre_final ?? {})" :value="'element_table_libre_final|'+table_libre" 
                                    v-html="$root.traduction('tables_libres.'+table_libre+'.nom_table')"></option>
                                <optgroup v-if="lien_champ.affichage_table_libre != 1" v-for="groupe in lien_champ.possibilites" :label="groupe.nom">
                                    <option v-for="champ in groupe.champs" :value="groupe.id + '|'+ champ.type_element + '.' + champ.nom_sql" 
                                    v-html="champ.index_traduction != null ? ($root.traduction(champ.index_traduction+'.nom') + ' ('+champ.nom_sql + ')') : champ.nom"></option>
                                </optgroup>
                            </select>
                            <div v-if="lien_champ.selection == 'table_libre'" class="select_table_libre">
                                <select-table-libre
                                    :tables_libres="lien_champ.tables_libres" 
                                    :type_element="lien_champ.valeur" 
                                    @changement_select_table_libre="changement_select_table_libre($event, 'type_element_' + index_lien)">
                                </select-table-libre>
                                <span class="filtre" v-if="lien_champ.valeur != null" :style="lien_champ.filtrage != undefined && lien_champ.filtrage.length > 0 ? 'color: white;background: green' : 'background: lightgray;color: grey'">
                                    <i class="fas fa-filter"
                                        @click="gestion_filtrage(lien_champ, index_lien)"></i>
                                </span>
                            </div>
                            <div v-else-if="lien_champ.type == 'element_table_libre_final'">
                                <select v-model="lien_champ.valeur">
                                    <option :value="lien_champ.selection.split('|')[1]">Tous</option>
                                    <option v-for="possibilite in lien_champ.element_table_libre_final[lien_champ.selection.split('|')[1]]" 
                                        :value="lien_champ.selection.split('|')[1]+'.'+possibilite.id" 
                                        v-html="possibilite.affichage_pour_recherche"></option>
                                </select>
                            </div>
                        </template>
                        <template v-else>
                            <img class="loader" src="eden/images/ajax_loader.gif">
                        </template>
                    </div>
                    <template v-if="liens_champs.length == 0">
                        <img class="loader" src="eden/images/ajax_loader.gif">
                    </template>
                </div>
                <template v-if="modale_filtrage">
                    <transition name="modal">
                        <div class="modal-mask">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">@traduction('composant.parametrage_lien_champ.filtrage_table_libre')</h5>
                                        <button type="button" class="close" @click="modale_filtrage = false" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <recherche-avancee ref="recherche_avancee" 
                                            :chargement_externe="true"
                                            :enregistrement_desactive="true"
                                            :bloc_unitaire="true"
                                            :parametres_recherche_avancee="{
                                                type_element : lien_champ_filtre.valeur.split('.')[0] ?? lien_champ_filtre.valeur,
                                            }"
                                            :informations_complementaires="{index_lien_champ : lien_champ_filtre.index}"></recherche-avancee>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>
                </template>
            </div>
        `,
        props: {
            lien_champ : {
                type : String,
                default : null
            },
            type_element: {
                type : String,
                default : null
            },
            filtres_valeur_final : {
                type : Object,
                default : function(){
                    return {};
                }
            },
            recherches_avancees : {
                type : Array,
                default : function(){
                    return [];
                }
            },
            valeur_unique : {
                type : Boolean,
                default : false
            },
        },
        data:function(){
            return {
                tables_libres : {!! \App\Eden\Models\Table_libre::get() !!},

                liens_champs : [],
                champs_libres_par_type_element : {},

                modale_filtrage : false,

                lien_champ_filtre: null,
                cle_filtrages : 0,
                initialisation_terminee : false,
            }
        },
        methods : {

            gestion_champ_selectionne : async function(lien_champ, index_lien){

                this.liens_champs.splice(index_lien+1, this.liens_champs.length - (index_lien+1));

                if(lien_champ.selection.startsWith('element_table_libre_final')){                    
                    lien_champ.type = 'element_table_libre_final';

                    var type_element = lien_champ.selection.split('|')[1];
                }
                else if(lien_champ.selection == 'table_libre'){
                    lien_champ.type = 'table_libre';
                }
                else{
                    var parties = lien_champ.selection.split('|');
                    lien_champ.type = parties[0];
                    lien_champ.valeur = parties[1];
                }

                if(lien_champ.type == 'champ'){

                    var type_element = lien_champ.valeur.split('.')[0];

                    var nom_sql = lien_champ.valeur.split('.')[1];

                    var type_element_ajax = this.champs_libres_par_type_element[type_element].filter(c => c.nom_sql == nom_sql)[0]?.type_element_ajax ?? type_element;

                    this.ajout_lien_champ('champ', type_element_ajax, nom_sql);
                }
            },

            ajout_lien_champ : async function(type_origine, type_element_ajax, nom_sql = null){

                var tables_libres = this.tables_libres;
                var element_table_libre_final = {};

                if(nom_sql !== null){

                    var tables_jointes = await $.ajax({
                        url: 'eden/champs/tables_jointes/'+type_element_ajax,
                        dataType:'json'
                    });

                    tables_libres = tables_jointes.map(tj => {
                        tj.type_element = tj.type_element +'.'+ tj.nom_sql_liaison;
                        return tj;
                    });

                    for(element of this.filtres_valeur_final.element_table_libre_final ?? []){

                        var valeurs_element_table_libre = await $.post({
                            url: 'eden/elements/'+element.type_element,
                            dataType:'json',
                            data : {
                                filtrage:[
                                    {
                                        champ : element.champ_lien_type_element,
                                        condition : 'where',
                                        valeur : type_element_ajax,
                                    },
                                ]
                            }
                        });

                        if(valeurs_element_table_libre.length > 0)
                            element_table_libre_final[element.type_element] = valeurs_element_table_libre;
                    }
                }

                var nouveau_lien_champ = {
                    type : null,
                    valeur : null,
                    selection : null,
                    tables_libres : tables_libres,
                    possibilites : [],
                    affichage_table_libre: 0,
                    element_table_libre_final : element_table_libre_final,
                };

                this.liens_champs.push(nouveau_lien_champ);

                if(nom_sql == 'id'){
                    if(Object.values(element_table_libre_final).length == 0){
                        nouveau_lien_champ.selection = 'table_libre',
                        nouveau_lien_champ.type = 'table_libre';
                    }
                    nouveau_lien_champ.affichage_table_libre = 1;
                    return;
                }

                if(type_origine == 'table_libre')
                    nouveau_lien_champ.affichage_table_libre = 2;

                nouveau_lien_champ.possibilites = await this.chargement_possibilites(type_element_ajax);
            },

            chargement_possibilites : async function(type_element){

                var champs_libres = await this.chargement_champs_libres(type_element);

                var possibilites = [];

                var champs_finals = champs_libres.filter(c => this.type_champ_final(c));
                
                if(champs_finals.length > 0)
                    possibilites.push({
                        id : 'champ_final',
                        nom : this.$root.traduction('composant.parametrage_lien_champ.champ_final'),
                        champs : champs_finals
                    });

                
                var champs_ajax = [];
                
                if(!this.valeur_unique)
                    champs_ajax.push({
                        'nom_sql' : 'id',
                        'type_element' : type_element,
                        'nom' : 'ID'
                    });
                
                champs_ajax = champs_ajax.concat(champs_libres.filter(c => c.type_reference == 42 || c.type == 42));

                possibilites.push({
                    id : 'champ',
                    nom : this.$root.traduction('composant.parametrage_lien_champ.lien_champ'),
                    champs : champs_ajax
                });

                return possibilites;
            },

            chargement_champs_libres : async function(type_element){

                if(this.champs_libres_par_type_element[type_element] !== undefined)
                    var champs_libres = this.champs_libres_par_type_element[type_element];
                else{
                    var champs_libres = await $.ajax({
                        url: 'eden/champs/valeurs/'+type_element,
                        dataType:'json'
                    });

                    champs_libres = [{
                        nom_sql : 'id',
                        type_element : type_element,
                        nom : 'ID'
                    }].concat(champs_libres);

                    this.champs_libres_par_type_element[type_element] = champs_libres;
                }

                return champs_libres;
            },

            gestion_filtrage : async function(lien_champ, index_lien){

                this.lien_champ_filtre = structuredClone(lien_champ);
                this.lien_champ_filtre.index = index_lien;

                this.modale_filtrage = true;

                await this.$nextTick();

                await this.$refs.recherche_avancee.charger_champs_libres();

                this.$refs.recherche_avancee.recherche_avancee = {
                    nom : '',
                    structure : this.lien_champ_filtre.filtrage ?? [],
                };
            },

            gestion_valeur_existante : async function(){

                var lien_champ = this.lien_champ;

                if(lien_champ == null || lien_champ == ''){

                    if(!this.type_element){
                        this.liens_champs.push({
                            type : 'table_libre',
                            valeur : null,
                            selection : 'table_libre',
                            tables_libres : this.tables_libres,
                            possibilites : [],
                            affichage_table_libre : 1,
                        });

                        return;
                    }

                    var lien_champ = {
                        type : null,
                        valeur : null,
                        selection : null,
                        tables_libres : this.tables_libres,
                        possibilites : [],
                    };

                    this.liens_champs.push(lien_champ);

                    lien_champ.possibilites = await this.chargement_possibilites(this.type_element);

                    return;
                }

                var liens_champs = lien_champ.split('/');

                var lien_precedent = null;

                var liens_champs_formates = [];

                for(index_lien in liens_champs){

                    var lien_champ = liens_champs[index_lien];

                    if(lien_champ.startsWith('table_libre|')){

                        var valeur = lien_champ.replace('table_libre|','');
                        
                        var type_element = valeur.split('.')[0];
                        var nom_sql = valeur.split('.')[1] ?? null;
                        var type = 'table_libre';
                        var selection = 'table_libre';
                    }
                    else if(lien_champ.startsWith('element_table_libre_final|')){ 
                        var valeur = lien_champ.replace('element_table_libre_final|',''); 
                        var type_element = valeur.split('.')[0]; 
                        var type = 'element_table_libre_final'; 
                        var selection = 'element_table_libre_final|'+type_element; 
                    }
                    else{
                        var valeur = lien_champ;

                        var type_element = valeur.split('.')[0];
                        var nom_sql = valeur.split('.')[1];

                        var champs_libres = await this.chargement_champs_libres(type_element);
                        var champ_libre = champs_libres.filter(c => c.nom_sql == nom_sql)[0];

                        if(this.type_champ_final(champ_libre))
                            var type = 'champ_final';
                        else
                            var type = 'champ';

                        var selection = type+'|'+valeur;
                    }

                    var affichage_table_libre = 0;
                    var element_table_libre_final  = {};

                    if(lien_precedent == null){
                        var tables_libres = this.tables_libres;
                        var type_element_ajax = this.type_element;
                    }
                    else if(lien_precedent.type == 'table_libre'){
                        affichage_table_libre = 2;
                        var type_element_ajax = type_element;
                    }
                    else{

                        var champs_libres = await this.chargement_champs_libres(lien_precedent.type_element);
                        var type_element_ajax = champs_libres.filter(c => c.nom_sql == lien_precedent.nom_sql)[0]?.type_element_ajax ?? lien_precedent.type_element;
                
                        var tables_jointes = await $.ajax({
                            url: 'eden/champs/tables_jointes/'+type_element_ajax,
                            dataType:'json'
                        });

                        tables_libres = tables_jointes.map(tj => {
                            tj.type_element = tj.type_element +'.'+ tj.nom_sql_liaison;
                            return tj;
                        });

                        for(element of this.filtres_valeur_final.element_table_libre_final ?? []){

                            var valeurs_element_table_libre = await $.post({
                                url: 'eden/elements/'+element.type_element,
                                dataType:'json',
                                data : {
                                    filtrage:[
                                        {
                                            champ : element.champ_lien_type_element,
                                            condition : 'where',
                                            valeur : type_element_ajax,
                                        },
                                    ]
                                }
                            });

                            if(valeurs_element_table_libre.length > 0)
                                element_table_libre_final[element.type_element] = valeurs_element_table_libre;
                        }
                    }

                    if(lien_precedent != null && lien_precedent.nom_sql == 'id')
                        affichage_table_libre = 1;
                    else
                        possibilites = await this.chargement_possibilites(type_element_ajax);

                    var filtrage = this.recherches_avancees.filter(ra => ra.id_cible == index_lien)[0]?.structure ?? null;

                    liens_champs_formates.push({
                        type : type,
                        valeur : valeur,
                        selection : selection,
                        tables_libres : tables_libres ?? [],
                        possibilites : possibilites ?? [],
                        affichage_table_libre: affichage_table_libre,
                        filtrage: filtrage,
                        element_table_libre_final: element_table_libre_final,
                    });

                    lien_precedent = {
                        type : type,
                        type_element : type_element,
                        nom_sql : nom_sql,
                    };
                }

                this.liens_champs = liens_champs_formates;
            },

            type_champ_final : function(champ){

                var filtres = this.filtres_valeur_final.champs;

                for(condition in filtres){
                    
                    var condition_vrai = true;

                    for(champ_lien in filtres[condition]){
                        var valeur_lien = filtres[condition][champ_lien];

                        if(Array.isArray(valeur_lien)){
                            if(!valeur_lien.includes(champ[champ_lien]))
                                condition_vrai = false;
                        }
                        else if(valeur_lien != champ[champ_lien])
                            condition_vrai = false;

                    }

                    if(condition_vrai)
                        return true;
                }

                return false;
            },

            changement_select_table_libre : function(table_libre, nom){

                var index_lien = parseInt(nom.replace('type_element_',''));

                this.liens_champs.splice(index_lien + 1, this.liens_champs.length - (index_lien + 1));

                lien_champ = this.liens_champs[index_lien];

                lien_champ.valeur = table_libre == null ? null : table_libre.type_element;

                lien_champ.filtrage = [];

                if(lien_champ.valeur != null && this.filtres_valeur_final.tables_libres_final != 'tous' && !(this.filtres_valeur_final.tables_libres_final ?? []).includes(table_libre.type_element.split('.')[0]))
                    this.ajout_lien_champ('table_libre', lien_champ.valeur.split('.')[0]);
            },
        },
        mounted : async function(){

            await this.gestion_valeur_existante();

            this.initialisation_terminee = true;
            this.$emit('initialisation_terminee');

            this.$on('changement_recherche_avancee', (parametres) => {
                if(parametres.actualisation){
                    this.$set(this.liens_champs[parametres.informations_complementaires.index_lien_champ], 'filtrage', parametres.recherche_avancee.structure);
                    this.cle_filtrages++;
                }
            });
        },
        watch : {
            'liens_champs' : {

                handler : function() {
                    if(!this.initialisation_terminee)
                        return;

                    this.$emit('changement_lien_champ', this.lien_champ_valeur);
                },
                deep:true
            },
            'filtrages' : {

                handler : function() {
                    this.$emit('changement_filtrages', this.filtrages);
                },
                deep:true
            },
        },
        computed : {

            lien_champ_valeur(){

                if(this.liens_champs.length == 0 || this.liens_champs.filter(lc => lc.valeur == null).length > 0)
                    return;
                
                var dernier_champ = this.liens_champs[this.liens_champs.length -1];

                if(!['element_table_libre_final','champ_final'].includes(dernier_champ.type)
                    && (dernier_champ.type != 'table_libre' || (this.filtres_valeur_final.tables_libres_final != 'tous' && !(this.filtres_valeur_final.tables_libres_final ?? []).includes(dernier_champ.valeur.split('.')[0]))
                ))
                    return null;

                var lien_champ_valeur = '';

                for(index_lien_champ in this.liens_champs){

                    if(index_lien_champ > 0)
                        lien_champ_valeur += '/';

                    lien_champ = this.liens_champs[index_lien_champ];

                    if(lien_champ.type == 'table_libre')
                        lien_champ_valeur += 'table_libre|';
                    else if(lien_champ.type == 'element_table_libre_final')
                        lien_champ_valeur += 'element_table_libre_final|';

                    lien_champ_valeur += lien_champ.valeur;
                }

                return lien_champ_valeur;
            },

            filtrages : function(){

                this.cle_filtrages;

                var filtrages = [];

                for(index_lien_champ in this.liens_champs){

                    lien_champ = this.liens_champs[index_lien_champ];

                    if(lien_champ.filtrage != null && lien_champ.filtrage.length > 0 && lien_champ.valeur != null)
                        filtrages.push({
                            id_cible : index_lien_champ,
                            type_element : lien_champ.valeur.split('.')[0],
                            structure : lien_champ.filtrage
                        });
                }

                return filtrages;
            },

            champ_selectionne : function(){

                if(this.liens_champs.length == 0)
                    return null;

                var dernier_lien_champ = this.liens_champs[this.liens_champs.length - 1];

                var nom_sql = null;

                if(dernier_lien_champ.type == 'champ_final' && dernier_lien_champ.valeur != null)
                    nom_sql =  dernier_lien_champ.valeur.split('.')[1] ?? null;

                if(nom_sql == null)
                    return null;

                return dernier_lien_champ.possibilites.find(p => p.id == 'champ_final').champs.find(c => c.nom_sql == nom_sql) ?? null;
        
            }    
        }

    });
</script>