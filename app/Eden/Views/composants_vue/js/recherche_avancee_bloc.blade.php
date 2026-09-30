<script>
const recherche_avancee_bloc = Vue.component('recherche-avancee-bloc', {
    template: `<div class="recherche_avancee_bloc_option">
                <select v-model="operateur" v-if="affichage_option">
                    <option v-for="option in $root.valeurs_listes_formatees[650]" :value="option.id_valeur" v-html="option.valeur"></option>
                </select>
                <div class="recherche_avancee_bloc" :id_random="id_random" ref="bloc">
                    <div class="actions">
                        <div class="suppression" @click="supprimer_bloc()" :title="$root.traduction('composant.blocs_recherche_avancee.supprimer_bloc')">
                            <span>
                                <i class="fas fa-trash-alt"></i>
                            </span>
                        </div>
                        <div class="ajout_filtre" @click="ajouter_condition()" :title="$root.traduction('composant.blocs_recherche_avancee.nouveau_filtre')">
                            a=x
                            <span class="icone_secondaire">
                                <i class="fas fa-plus"></i>
                            </span>
                        </div>
                        <div class="ajout_bloc" @click="ajouter_bloc()" :title="$root.traduction('composant.blocs_recherche_avancee.nouveau_groupe_de_conditions')">
                            ( )
                            <span class="icone_secondaire">
                                <i class="fas fa-plus"></i>
                            </span>
                        </div>
                        <div class="exclusion" :style="'background:'+(bloc.exclu == 1 ? '#669e24' : 'lightgrey')" @click="bloc.exclu = bloc.exclu == 1 ? 0 : 1;$parent.$emit('changement_bloc',{
                            index_bloc : index_bloc,
                            bloc : bloc
                        });">
                            @traduction('composant.recherche_avancee.exclusion')
                        </div>
                    </div>
                    <div class="filtres_blocs">
                        <template v-for="(filtre,index) in filtres_bloc">
                            <select v-model="filtre.operateur" @change="enregistrement_condition(filtre,index)" v-if="index > 0">
                                <option v-for="option in $root.valeurs_listes_formatees[650]" :value="option.id_valeur" v-html="option.valeur"></option>
                            </select>
                            <div class="filtre" ref="filtres">
                                <div class="condition" v-if="filtres[filtre.type_element +'_'+filtre.nom_sql]">
                                    <span class="text-lowercase">
                                        <span class="text-capitalize">@{{ filtre.index_traduction ? $root.traduction(filtre.index_traduction+'.nom') : filtre.nom }}</span>
                                        <span v-if="filtre.champ_liaison != null && !filtre.champ_questionnaire" v-html="$root.traduction('composant.recherche_avancee.du_champ')+' '+$root.traduction('champs_libres.'+type_element+'.'+filtre.champ_liaison+'.nom')"></span>
                                    </span>
                                    <component :is="filtres[filtre.type_element +'_'+filtre.nom_sql].type_filtre" :valeurs="filtre.valeurs" :affichage="true" :filtre="filtres[filtre.type_element +'_'+filtre.nom_sql]"></component>
                                </div>
                                <div>
                                    <i @click="modifier_condition(filtre,index)"
                                    class="css_pointer fa fa-fw fa-pencil-alt" aria-hidden="true"></i>
                                    <i @click="supprimer_condition(index)"
                                    class="css_pointer fa fa-trash " aria-hidden="true"></i>
                                </div>
                            </div>
                        </template>
                        <template v-for="(bloc_dans_bloc,index_bloc_dans_bloc) in bloc.blocs">
                            <recherche-avancee-bloc :type_element="type_element" :bloc="bloc_dans_bloc" :bloc_parent="bloc" :index_bloc="index_bloc_dans_bloc" :filtres="filtres"></recherche-avancee-bloc>
                        </template>
                    </div>
                </div>
        </div>`,
    props:{
        index_bloc : {},
        bloc : {},
        bloc_parent : {
            type : Object|Boolean,
            default : false
        },
        type_element : {
            type : String,
            default : function(){
                return '';
            }
        },
        filtres : {
            type : Object,
            default : function(){
                return {};
            }
        },
    },

	computed:{

        id_random: function() {

            length = 15;

            var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

            if (! length) {
                length = Math.floor(Math.random() * chars.length);
            }

            var str = '';
            for (var i = 0; i < length; i++) {
                str += chars[Math.floor(Math.random() * chars.length)];
            }

            return str;
        },

        operateur : {
            get(){
                return this.bloc.operateur;
            },
            set(valeur){
                var bloc = this.bloc;

                bloc.operateur = valeur;

                this.$parent.$emit('changement_bloc',{
                    index_bloc : this.index_bloc,
                    bloc : bloc
                });
            }
        },

        filtres_bloc : function(){

            var filtres_bloc = structuredClone(this.bloc.filtres);

            for(filtre_bloc of filtres_bloc){

                var filtre = this.filtres[filtre_bloc.type_element+'_'+filtre_bloc.nom_sql].modele;

                filtre_bloc.index_traduction = filtre.index_traduction;
                filtre_bloc.nom = filtre.nom;
                filtre_bloc.champ_questionnaire = filtre.champ_questionnaire;
            }

            return filtres_bloc;
        },

        affichage_option : function(){
            return this.index_bloc != 0 || (this.bloc_parent !== false && this.bloc_parent.filtres.length > 0);
        },
    },

    methods : {

        ajouter_condition : function(){
            this.$parent.$emit('ajouter_condition',{
                instance : this
            });
        },

        modifier_condition : function(filtre,index){
            this.$parent.$emit('modifier_condition',{
                filtre : filtre,
                index_filtre : index,
                instance : this
            });
        },

        enregistrement_condition : function(condition, index_filtre = null){

            var bloc = this.bloc;

            if(bloc.filtres == null)
                this.$set(bloc,'filtres',[]);

            if(index_filtre == null)
                bloc.filtres.push(condition);
            else
                this.$set(bloc.filtres,index_filtre,condition);

            this.$parent.$emit('changement_bloc',{
                index_bloc : this.index_bloc,
                bloc : bloc
            });
        },

        async supprimer_bloc(){

            if(!await confirm_eden())
                return false;

            this.$parent.$emit('supprimer_bloc',{
                index_bloc : this.index_bloc,
                non_actualise : !this.bloc.filtres || this.bloc.filtres.length == 0
            });
        },

        async supprimer_condition(index_filtre) {

            if(!await confirm_eden())
                return false;

            var bloc = this.bloc;

            bloc.filtres.splice(index_filtre,1);

            this.$parent.$emit('changement_bloc',{
                index_bloc : this.index_bloc,
                bloc : bloc
            });

        },

        ajouter_bloc() {

            var nouveau_bloc = {operateur: 0, filtres: [], blocs: [], exclu: 0};

            var bloc = this.bloc;

            if(bloc.blocs == undefined)
                this.$set(bloc,'blocs',[]);

            bloc.blocs.push(nouveau_bloc);

            this.$parent.$emit('changement_bloc',{
                index_bloc : this.index_bloc,
                bloc : bloc,
                non_actualise : true
            });
        },
    },

    mounted : function(){

        this.$on('ajouter_condition',(parametres) => {
            this.$parent.$emit('ajouter_condition',parametres);
        });

        this.$on('modifier_condition',(parametres) => {
            this.$parent.$emit('modifier_condition',parametres);
        });

        this.$on('supprimer_bloc',(parametres) => {
            var bloc = this.bloc;

            bloc.blocs.splice(parametres.index_bloc,1);

            this.$parent.$emit('changement_bloc',{
                index_bloc : this.index_bloc,
                bloc : bloc,
                non_actualise : parametres.non_actualise
            });
        });

        this.$on('changement_bloc',(parametres) => {

            var bloc = this.bloc;

            bloc.blocs[parametres.index_bloc] = parametres.bloc;

            this.$parent.$emit('changement_bloc',{
                index_bloc : this.index_bloc,
                bloc : bloc,
                non_actualise: parametres.non_actualise
            });
        });
    },

});
</script>