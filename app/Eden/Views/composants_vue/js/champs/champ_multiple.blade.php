<script>
    const champ_multiple = Vue.component('champ-multiple', {
        name: 'champ-multiple',
        template: `<div class="champ_multiple" :id="this.id_random">
                        <div v-for="(item, index) in composants" :key="item.id" @mouseenter="affichage_options = item.id;" @mouseleave="affichage_options = null;" class="enfant">
                            <input v-if="composant_enfant == 'input'" :disabled="lecture_seule"  :type='composant_enfant_props.type' :name="item.props['name']" :modele="item.props['modele']" :nom_sql="item.props['nom_sql']" v-model="item.modele[composant_enfant_props.nom_sql]">
                            <textarea v-else-if="composant_enfant == 'textarea'" :disabled="lecture_seule" :name="item.props['name']" :modele="item.props['modele']" :nom_sql="item.props['nom_sql']" v-model="item.modele[composant_enfant_props.nom_sql]"></textarea>
                            <component v-else :lecture_seule="lecture_seule" :is='composant_enfant' v-bind="item.props" @selection-element="ajout_valeur_ajax($event)"/>
                            <span v-if="composant_enfant!=='champ-selection-element' && affichage_options == item.id && !lecture_seule" class="bouton_suppresion" @click="supprimer_saisie(index)">
                                <i class="fas fa-times css_pointer"></i>
                            </span>
                        </div>
                        <span v-if="composant_enfant!=='champ-selection-element' && !lecture_seule"  @click="ajouter_saisie()">
                            <i class="fas fa-plus css_pointer css_input_ajout_selection_element css_background_couleur_primaire"></i>
                        </span>
                        <div class="affichage_valeurs" v-else>
                            <div v-for="(affichage,index) in affichages" :key="affichage.id" class="bloc">
                                <input type="hidden" :name="composant_enfant_props.name+'[]'" :value="affichage.id" />
                                <span @click="supprimer_saisie_ajax(index)" v-if="!lecture_seule" class="fas fa-times suppresion"></span>
                                <span :id="id_random+'_affichage_resultat_'+affichage.id" class="affichage" v-html="affichage.element.affiche_lien_pour_select"></span>
                            </div>
                        </div>
                        <input type="hidden" :name="composant_enfant_props.name" v-if="valeurs.length == 0" />
                    </div>`,

        props: {
            composant_enfant: '',
            composant_enfant_props: {
                type: Object,
                default: null,
            },
            modele: {
                type: Object,
                default: function(){
                    return null;
                }
            },
            lecture_seule :{
                type: Boolean | Number,
                default: false,
            },
        },

        data: function() {
            return {
                composants: [],
                id_a_generer: 0,
                watchers: [],
                affichage_options : null,
                donnees_elements: {},
            };
        },

        mounted: function() {

            this.composant_enfant_props.modele = this.modele;

            this.$watch('modele.' + this.composant_enfant_props.nom_sql,() => {

                if(this.composant_enfant === 'champ-selection-element'){
                    this.affichages_elements();
                }
                else{
                    
                    this.valeurs.forEach((valeur, index) => {
                        
                        if(this.composants[index] != undefined && this.composants[index].modele[this.composant_enfant_props.nom_sql] != valeur)
                            this.composants[index].modele[this.composant_enfant_props.nom_sql] = valeur;
                        else if(this.composants[index] == undefined)
                            this.ajouter_saisie(valeur);
                    });

                    for(i = this.composants.length - 1; i > this.valeurs.length - 1; i--){

                        this.supprimer_saisie(i);
                    }
                }
            });

            if(this.composant_enfant === 'champ-selection-element')
                this.ajouter_saisie();
            
            if(this.valeurs.length !== 0){
                if(this.composant_enfant === 'champ-selection-element'){
                    this.affichages_elements();
                }
                else{
                    for(valeur of this.valeurs){
                        this.ajouter_saisie(valeur);
                    }
                }
            }
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

            affichages : function(){

                var affichages = [];

                for(id of this.valeurs){

                    if(this.donnees_elements[id])
                        affichages.push(this.donnees_elements[id]);
                }

                return affichages;
            },

            valeurs : function(){

                return this.modele[this.composant_enfant_props.nom_sql] ?? [];
            }
        },

        methods: {
            generate_id: function() {
                this.id_a_generer++;
                const id_incrementiel = this.id_a_generer;
                return id_incrementiel;
            },

            ajouter_saisie(valeur_recuperee = null){

                if (this.composant_enfant === 'champ-selection-element' && this.composants.length > 0)
                    return;

                const props_copiee = structuredClone(this.composant_enfant_props);

                var modele = structuredClone(props_copiee.modele);
                if(modele == null || modele == undefined)
                    modele = structuredClone(this.modele);

                modele[this.composant_enfant_props.nom_sql] = null;

                if (this.composant_enfant === 'champ-selection-element' && props_copiee.name)
                    props_copiee.name = null;
                else if (props_copiee.name)
                    props_copiee.name = props_copiee.name + '[]';

                props_copiee.modele = modele;

                var id = this.generate_id();

                this.composants.push({
                    id: id,
                    props: props_copiee,
                    modele: modele
                });

                if (this.composant_enfant !== 'champ-selection-element') {

                    if(valeur_recuperee != null){
                        modele[this.composant_enfant_props.nom_sql] = valeur_recuperee;
                    }

                    const index = this.composants.length-1;

                    const unwatch = this.$watch(() => {
                        return this.composants[index]?.modele?.[this.composant_enfant_props.nom_sql];
                    }, () => {
                        this.changement_valeurs();
                    });

                    this.watchers.push(unwatch);
                }
            },
            changement_valeurs : function() {

                var tableau = [];
                for(item of this.composants){
                    tableau.push(item.modele[this.composant_enfant_props.nom_sql]);
                }
                this.$set(this.composant_enfant_props.modele,this.composant_enfant_props.nom_sql,tableau);
            },
            supprimer_saisie(index) {

                if(this.watchers[index]){
                    this.watchers[index]();
                    this.watchers.splice(index, 1);
                }
                this.composants.splice(index, 1);
                this.changement_valeurs();
            },
            ajout_valeur_ajax: function(data){

                if(this.composant_enfant_props.modele[this.composant_enfant_props.nom_sql] == undefined)
                    this.$set(this.composant_enfant_props.modele,this.composant_enfant_props.nom_sql,[]);

                this.composant_enfant_props.modele[this.composant_enfant_props.nom_sql].push(data.id);
                this.$refs[this.composant_enfant_props.nom_sql].vider_modele_ajax_multi();
            },

            supprimer_saisie_ajax(index){
                this.composant_enfant_props.modele[this.composant_enfant_props.nom_sql].splice(index,1);
            },

            affichages_elements : async function(){

                await this.$nextTick();

                for(id of this.modele[this.composant_enfant_props.nom_sql]){
                    this.$refs[this.composant_enfant_props.nom_sql].recuperer_affichage_element(this.composant_enfant_props.type_element,id).then((element) => {
                        this.donnees_elements[element.id] = {
                            id : element.id,
                            element : element,
                            chaine_affichage : element.chaine_affichage,
                        };

                        this.$parent.$forceUpdate();
				        this.$root.$forceUpdate();
                    });
                }
            },
        },
        watch : {

            modele : {

                handler : function() {

                    for(composant of this.composants){
                        
                        var modele = structuredClone(this.modele);
                        modele[this.composant_enfant_props.nom_sql] = composant.modele[this.composant_enfant_props.nom_sql];

                        composant.props.modele = modele;
                        composant.modele = modele;
                    }
                },
                deep:true,
            }
        }
    })
</script>
