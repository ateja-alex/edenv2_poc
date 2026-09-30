<script>
    const filtre_famille = Vue.component('filtre-famille', {
        template: `
            <div>
                <div v-if="affichage">
                    <template v-if="options.includes(valeurs)">
                        <span class="variable" v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.'+this.valeurs)"></span>
                    </template>
                    <template v-else-if="!Array.isArray(valeurs) && valeurs.startsWith('lien_champ|')">
                        <span>@traduction('filtres.champ_recherche_element.contenu')</span>
                        <span class="variable" v-html="affichage_lien_champ"></span>
                    </template>
                    <template v-else>
                        @traduction('filtres.champ_famille.contenu')
                        <span class="valeur" v-html="valeurs_selectionnes.join(', ')"></span>
                    </template>
                </div>
                <div v-else class="css_liste_checkbox_popover filtre_famille">
                    <div v-if="!gestion_lien_champ" class="d-flex" style="padding:10px;gap: 10px;">
                        <label v-for="option in options" style="display: flex;gap: 5px;">
                          <input
                              type="radio"
                              :value="option"
                              v-model="valeur_checkbox"
                          />
                          <span>@{{ $root.traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.'+option) }}</span>
                        </label>
                    </div>
                    <div class="d-flex align-items-center justify-content-between col-12 my-2">
                        <template v-if="gestion_lien_champ">
                            <parametrage-lien-champ
                                :lien_champ="valeur_checkbox != null ? valeur_checkbox.replace('lien_champ|','') : ''"
                                @changement_lien_champ="valeur_checkbox = 'lien_champ|'+$event"
                                :type_element="type_element_source"
                                :filtres_valeur_final="{
                                    champs : [
                                        {type_element_ajax : filtre.modele.type_element_ajax},
                                        {type_element : filtre.modele.type_element_ajax, nom_sql : 'id'},
                                    ]
                                }"
                                :valeur_unique="true"></parametrage-lien-champ>
                        </template>
                        <template v-else>
                            <div style="width: 40%">
                                <span>@traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.recherche')</span>
                            </div>
                            <div style="width: 60%">
                                <input type="text" v-model="recherche" style="height: 24px; border: 1px solid rgb(228, 224, 224);width: 100%;"/>
                            </div>
                        </template>
                        <i v-if="type_element_source != null" @click="gestion_lien_champ = !gestion_lien_champ;valeur_checkbox = null" class="css_action_icon fas fa-random"></i>
                    </div>
                    <div style="display: flex;align-items: center;justify-content: center;min-width:200px;" v-if="chargement_elements">
                        <img style="width: 35px;height: 35px;" src="/eden/images/ajax_loader.gif" />
                    </div>
                    <template v-else-if="!gestion_lien_champ">
                        <affichage-famille v-for="famille in familles" :key="famille.id" :famille="famille" :valeurs="valeurs"></affichage-famille>
                    </template>
                </div>
            </div>
        `,
        props: {
            filtre : {
                type : Object,
                default: function(){
                    return {};
                }
            },
            valeurs : {
                type : [String, Array],
                default: function(){
                    return [];
                }
            },
            affichage: {
                type : Boolean,
                default: false
            },
            type_element_source: {
                type : String,
                default : null,
            },
        },
        data:function(){
            return {
                liste_familles: [],
                chargement_elements : true,
                options:['vide','non_vide'],
                recherche : '',
                gestion_lien_champ :false,
            }
        },
        methods:{
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
            tri_correspondance_recherche(familles){

                for(famille of familles){

                    if(famille.enfants && famille.enfants.length > 0)
                        famille.enfants = this.tri_correspondance_recherche(famille.enfants);
                }

                return familles.filter(famille => (famille.chaine_tags_recherche && famille.chaine_tags_recherche.toLowerCase().includes(this.recherche.toLowerCase()))
                    || (famille.enfants && famille.enfants.length > 0));
            },
        },
        computed: {
            valeurs_selectionnes : function(){

                var valeurs_selectionnes = [];

                return valeurs_selectionnes.concat(this.liste_familles.filter(x => this.valeurs.includes(x.id)).map((x) => {return x.chaine_affichage}));
            },
            valeur_checkbox : {
                get(){
                    if(Array.isArray(this.valeurs))
                        return null;

                    return this.valeurs;
                },
                set(valeur){
                    this.changement_filtre(valeur);
                },
            },
            familles : function(){

                var familles = structuredClone(this.liste_familles).filter(famille => !(famille.parent_id > 0) || !this.liste_familles.map(f => f.id).includes(famille.parent_id) )

                if(this.recherche == null || this.recherche == '')
                    return familles;

                return this.tri_correspondance_recherche(familles);
            },
            affichage_lien_champ : function(){

                if(Array.isArray(this.valeurs) || !this.valeurs.startsWith('lien_champ|'))
                    return '';

                var affichage_lien_champ = '';

                for(lien_champ of this.valeurs.replace('lien_champ|','').split('/')){

                    if(affichage_lien_champ != '')
                        affichage_lien_champ += ' => ';
                    
                    var partie_lien = lien_champ.split('.');
                    var type_element = partie_lien[0];
                    var nom_sql = partie_lien[1];

                    affichage_lien_champ += (nom_sql == 'id' ? 'ID' : this.$root.traduction('champs_libres.'+type_element+'.'+nom_sql+'.nom'))+' ('+lien_champ+')';
                }

                return affichage_lien_champ;
            },
        },
        mounted : function(){

            this.$on('changement_filtre',(valeurs) => {
                this.changement_filtre(valeurs);
            });

            $.post({
                url: '/eden/element/recherche/famille/%25',
                dataType: "json",
                data: {
                    source: {
                        type_element : this.filtre.modele.type_element,
                        nom_sql : this.filtre.modele.nom_sql,
                    },
                    sans_limite : true,
                },
            }).done(async (donnees) => {
                this.liste_familles = donnees.map(f => f.modele);
                this.chargement_elements = false;
            });
        },
        created : function(){
           @include('eden::composants_vue.js.include.filtres.affichage_famille')
        },
    });
</script>