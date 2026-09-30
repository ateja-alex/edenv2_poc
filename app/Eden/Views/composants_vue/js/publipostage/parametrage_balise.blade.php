<script>
    const parametrage_balise = Vue.component('parametrage-balise', {
        template: `
            <div class="parametrage_balise">
                <template v-for="(bloc,index_bloc) in blocs">
                    <champ-publipostage v-if="bloc.type == 'texte'" :exclusion_balises_parametrage="modele_parametrage.id != null ? [modele_parametrage] : false" type_champ="textarea" @change="gestion_changement" :type_element="type_element_lien" :modele="bloc" nom_sql="valeur"></champ-publipostage>
                    <parametrage-balise v-else ref="blocs_parametrage" @suppression_bloc="suppression_bloc($event)" :parent="bloc" @change="gestion_changement" style="margin: 5px 0;" :type_element="type_element_lien" :modele="bloc" nom_sql="valeur" :recherches_avancees="recherches_avancees"></parametrage-balise>
                </template>
                <div class="actions">
                    <div v-for="type of ['boucle','condition']" :title="$root.traduction('composant.parametrage_balise.ajout_element.'+type)" :class="'ajout_'+type" @click="modale_bloc(type)">
                        <i v-if="type == 'boucle'" class="fas fa-undo"></i>
                        <span v-else>a=x</span>
                    </div>
                    <template v-if="parent != null">
                        <div :title="$root.traduction('composant.parametrage_balise.parametrage')" class="parametrage" @click="$parent.modale_bloc(parent.type, parent)">
                            <span v-html="$root.traduction('composant.parametrage_balise.affichage_bloc.'+parent.type)+(parent.type == 'boucle' ? ' '+$root.traduction('tables_libres.'+type_element_lien+'.nom_table')+' ('+type_element_lien+')' : '')"></span>
                            <span><i class="fas fa-cog" aria-hidden="true"></i></span>
                        </div>
                        <div :title="$root.traduction('composant.parametrage_balise.suppression')" class="suppression" @click="suppression_bloc()">
                            <span><i class="fas fa-trash-alt" aria-hidden="true"></i></span>
                        </div>
                    </template>
                </div>
                <template v-if="modale_ajout_bloc">
                    <transition name="modal">
                        <div class="modal-mask">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" v-html="$root.traduction('composant.parametrage_balise.ajout_element.'+bloc.type)"></h5>
                                        <button type="button" class="close" @click="modale_ajout_bloc = false" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body css_form">
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <span v-html="$root.traduction('composant.parametrage_balise.modale.'+bloc.type+'.nom_champ')"></span>
                                            </div>
                                            <div class="col-sm-10">
                                                <template v-if="bloc.type == 'boucle'">
                                                    <select v-model="bloc.type_boucle" v-if="gestion_elements_publipostes">
                                                        <option value="elements_publipostes" v-html="$root.traduction('composant.parametrage_balise.modale.boucle.elements_publipostage')"></option>
                                                        <option value="lien_champ" v-html="$root.traduction('composant.parametrage_balise.modale.boucle.lien_champ')"></option>
                                                    </select>
                                                    <parametrage-lien-champ v-if="bloc.type_boucle == 'lien_champ'" ref="parametrage_lien_champ"
                                                        :lien_champ="bloc.lien_champ"
                                                        @changement_lien_champ="bloc.lien_champ = $event"
                                                        @changement_filtrages="bloc.filtrages = $event"
                                                        :recherches_avancees="bloc.filtrages"
                                                        :type_element="type_element_lien"
                                                        :filtres_valeur_final="{
                                                            tables_libres_final : 'tous',
                                                        }"></parametrage-lien-champ>
                                                </template>
                                                <champ-publipostage v-else :exclusion_balises_parametrage="true" :variables_par_type_contexte="variables_publipostage_condition" :type_element="type_element_lien" :modele="bloc" nom_sql="valeur_condition"></champ-publipostage>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" @click="modale_ajout_bloc = false" >@traduction('interface.modales.fermer')</button>
							            <button type="button" v-if="bloc.type_boucle == 'elements_publipostes' || bloc.lien_champ != null || bloc.valeur_condition != ''" class="btn btn-primary" @click="ajouter_bloc()">@traduction('interface.modales.enregistrer')</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>
                </template>
            </div>
        `,
        props: {
            type_element : {
                type : String,
                default: null
            },
            modele : {
                type : Object,
                default: function(){
                    return {};
                }
            },
            nom_sql : {
                type : String,
                default: null
            },
            parent : {
                type : Object,
                default: function(){
                    return null;
                }
            },
            recherches_avancees : {
                type : Array,
                default: function(){
                    return [];
                }
            },
        },
        data: function(){
            return {
                blocs : [],
                modale_ajout_bloc: false,
                bloc : {
                    type : 'boucle',
                    type_boucle: 'elements_publipostes',
                    lien_champ : null,
                    filtrages : [],
                },
                id_composant_filtrage: 0,
                variables_publipostage_condition : [{
                    titre : this.$root.traduction('composant.parametrage_balise.variables_publipostage_condition.titre'),
                    id: 'valeur_contexte',
                    valeur : 'type_element',
                    elements : [
                        {
                            nom_sql : '#elements_publipostes_multiple',
                            titre : this.$root.traduction('composant.parametrage_balise.variables_publipostage_condition.elements_publipostes_multiple'),
                            valeur : "\{\{#elements_publipostes_multiple\}\}",
                        },
                        {
                            nom_sql : '#elements_publipostes_unique',
                            titre : this.$root.traduction('composant.parametrage_balise.variables_publipostage_condition.elements_publipostes_unique'),
                            valeur : "\{\{#elements_publipostes_unique\}\}",
                        }
                    ],
                }],
            }
        },
        computed : {
            type_element_lien : function(){

                if(this.parent != null && this.parent.lien_champ != null){

                    var liens_champs = this.parent.lien_champ.split('/');

                    return liens_champs[liens_champs.length -1].replace('table_libre|','').split('.')[0];
                }
                else
                    return this.type_element;
            },
            parent_id : function(){

                if(this.parent == null)
                    return '';

                var index_parent = this.$parent.parent_id;

                if(index_parent != '')
                    return index_parent + '_'+this.parent.id;
                else
                    return this.parent.id;
            },

            filtrages : function(){

                this.id_composant_filtrage;

                var filtrages = [];

                var base_id = this.parent_id != '' ? this.parent_id + '_' : '';

                var index_bloc_parametrage = 0;

                for(var bloc of this.blocs){
                    if(bloc.type == 'boucle'){

                        if(bloc.filtrages.length > 0)
                            filtrages.push({
                                id_boucle : base_id+bloc.id,
                                filtrages : bloc.filtrages,
                            });

                        if(this.$refs.blocs_parametrage)
                            filtrages = filtrages.concat(this.$refs.blocs_parametrage[index_bloc_parametrage].filtrages);
                    }

                    if(bloc.type != 'texte')
                        index_bloc_parametrage++;
                }

                return filtrages;
            },

            gestion_elements_publipostes : function(){

                if(this.parent == null)
                    return false;

                return this.parent.type == 'condition' && this.parent.valeur_condition.includes('#elements_publipostes_multiple');
            },

            modele_parametrage : function(){

                if(this.parent == null)
                    return this.modele

                return this.$parent.modele_parametrage;
            }
        },
        methods : {
            transcription_parametrage : function(){

                var valeur = this.modele[this.nom_sql] ?? '';

                this.blocs = [];

                var base_id = this.parent_id != '' ? this.parent_id + '_' : '';

                var index_curseur = 0;
                var regex_ouverture = new RegExp('\\{\\{@(boucle|condition)_(' + base_id + '(\\d+))\\(([^)]*)\\)\\}\\}', 'g');
                var ouvertures = Array.from(valeur.matchAll(regex_ouverture));

                for (var ouverture of ouvertures) {

                    var type = ouverture[1];
                    var id_complet = ouverture[2];
                    var id = parseInt(ouverture[3]);
                    var parametre = ouverture[4];

                    var index_debut = ouverture.index;
                    var index_debut_interieur = index_debut + ouverture[0].length;
                    var balise_fin = "\{\{@fin_"+type+"_" + id_complet + "\}\}";
                    var index_debut_balise_fin = valeur.indexOf(balise_fin, index_debut_interieur);

                    if (index_debut_balise_fin === -1)
                        continue;

                    this.blocs.push({
                        type: 'texte',
                        valeur : index_debut == 0 ? '' : valeur.slice(index_curseur, index_debut - 1),
                    });

                    var bloc = {
                        type: type,
                        id: id,
                        valeur : valeur.slice(index_debut_interieur + 1, index_debut_balise_fin - 1),
                    };

                    if(type == 'condition')
                        bloc.valeur_condition = parametre;
                    else if(type == 'boucle'){
                
                        bloc.type_boucle = parametre == '#elements_publipostes' ? 'elements_publipostes' : 'lien_champ';
                        bloc.lien_champ = bloc.type_boucle == 'lien_champ' ? parametre : null;

                        bloc.filtrages = structuredClone(this.recherches_avancees).map((recherche) => {

                            if(!recherche.id_cible.startsWith(base_id+id))
                                return null;
                            
                            var id_cible = recherche.id_cible.replace(base_id+id+'_', '');

                            if(!id_cible.includes('_')){
                                recherche.id_cible = id_cible;
                                return recherche;
                            }

                            return null;
                        }).filter(r => r != null);

                    }

                    this.blocs.push(bloc);

                    index_curseur = index_debut_balise_fin + balise_fin.length + 1;
                }

                this.blocs.push({
                    type: 'texte',
                    valeur : valeur.slice(index_curseur)
                });
            },
            gestion_changement : function(){

                if(this.parent != null)
                    this.$emit('change');
                else
                    this.modele[this.nom_sql] = this.transcription_valeur();
            },
            transcription_valeur : function(){

                var valeur = '';

                var index_bloc_parametrage = 0;

                for(index_bloc in this.blocs){
                    var bloc = this.blocs[index_bloc];
                    if(bloc.type == 'texte')
                        valeur+= bloc.valeur;
                    else{

                        var type = bloc.type;

                        var id = this.parent_id != '' ? this.parent_id + '_' + bloc.id : bloc.id;

                        var balise_debut = "\{\{@"+type+"_"+id+"(";

                        if(type == 'condition')
                            balise_debut+= bloc.valeur_condition;
                        else{
                            if(bloc.type_boucle == 'elements_publipostes')
                                balise_debut+= "#elements_publipostes";
                            else if(bloc.type_boucle == 'lien_champ')
                                balise_debut+= bloc.lien_champ;
                        }

                        balise_debut+= ")\}\}";

                        valeur+= ' '+balise_debut+' '+this.$refs.blocs_parametrage[index_bloc_parametrage].transcription_valeur()+' \{\{@fin_'+type+'_'+id+'\}\} ';

                        index_bloc_parametrage++;
                    }
                }

                this.id_composant_filtrage++;

                return valeur;
            },
            modale_bloc: function(type,bloc = null){

                if(bloc == null){

                    var bloc = {
                        type: type,
                    };

                    if(type == 'boucle'){
                        bloc.type_boucle = this.gestion_elements_publipostes ? 'elements_publipostes' : 'lien_champ';
                        bloc.lien_champ = null;
                        bloc.filtrages = [];
                    }
                    else
                        bloc.valeur_condition = '';

                    this.bloc = bloc;
                }
                else
                    this.bloc = structuredClone(bloc);

                this.modale_ajout_bloc = true;
            },
            ajouter_bloc: function(){

                if(this.bloc.id != null){

                    var bloc = this.blocs.find(bloc => bloc.id == this.bloc.id);

                    bloc = Object.assign(bloc, this.bloc);
                }
                else{

                    var max_id_bloc = this.blocs.reduce((max, bloc) => Math.max(max, bloc.id ?? 0), 0);
                    
                    var bloc = {
                        id : max_id_bloc + 1,
                    };

                    bloc = Object.assign(bloc, this.bloc);

                    this.blocs.push(bloc);

                    this.blocs.push({
                        type: 'texte',
                        valeur : ''
                    });

                }

                this.modale_ajout_bloc = false;

                this.$nextTick(() => {
                    this.gestion_changement();
                });
            },

            suppression_bloc : async function(bloc = null){

                if(bloc == null){
                    if(!await confirm_eden())
                        return;

                    this.$emit('suppression_bloc', this.parent);
                }
                else{
                    var index = this.blocs.indexOf(bloc);

                    bloc_avant = this.blocs[index - 1];
                    bloc_apres = this.blocs[index + 1];

                    bloc_avant.valeur+= (bloc_avant.valeur != '' && bloc_apres.valeur != '' ? '\n' : '')+bloc_apres.valeur;

                    this.blocs.splice(index, 2);
                }
            },
        },
        mounted : async function(){
            this.transcription_parametrage();
        },
    });
</script>