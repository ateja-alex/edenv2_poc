<script>
    const filtre_recherche_element = Vue.component('filtre-recherche-element', {
        template: `<div>
          <div v-if="affichage">
            <template v-if="options.includes(valeurs)">
                <span class="variable" v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.'+valeurs)"></span>
            </template>
            <template v-else-if="!Array.isArray(valeurs) && valeurs.startsWith('lien_champ|')">
                <span>@traduction('filtres.champ_recherche_element.contenu')</span>
                <span class="variable" v-html="affichage_lien_champ"></span>
            </template>
            <template v-else>
                <span>@traduction('filtres.champ_recherche_element.contenu')</span>
                <span class="valeur" v-html="valeurs_selectionnes.join(', ')"></span>
            </template>
          </div>
          <div class="filtre_champ_recherche_element" v-else>
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
            <div v-if="Array.isArray(valeurs) && valeurs.length > 0" class="champ_selection_element_multiple" style="display: inline-flex;align-items: center;flex-wrap: wrap;gap: 10px 5px;padding: 10px;width: 422px;">
                <div class="block_selection_multiple_elements" style="max-width: 130px;" :key="id" v-for="id in valeurs">
                  <span class="css_selection_element_multiple" style="padding: 5px 10px;">
                        <span class="css_selection_element_multiple_icone" @click="retirer_element($event,id)">
                            <span class="fa fa-times"></span>
                        </span>
                        <span style="margin-left: 5px;" v-html="affichage_valeurs[id]"></span>
                    </span>
                </div>
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
                    <span style="width: 100px;">@traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.recherche')</span>
                    <input @keyup="donnees_select = [];elements_a_charger=true;charger_donnees()" v-model="recherche" class="mx-1" type="text" style="height: 24px; border: 1px solid #e4e0e0;">
                </template>
                <i v-if="type_element_source != null" @click="gestion_lien_champ = !gestion_lien_champ;valeur_checkbox = null" class="css_action_icon fas fa-random"></i>
            </div>
            <div v-if="!gestion_lien_champ" @scroll="affichage_elements_supplementaires($event)" style="position: unset;max-height:200px;overflow: auto;" class="css_select_multiselection_element">
              <div @click="ajout_element($event,donnee,index)" style="min-height:36px" v-for="(donnee,index) in donnees_select" v-html="donnee.affichage_pour_recherche"></div>
              <div style="display: flex;align-items: center;justify-content: center;" v-if="chargement_elements">
                <img style="width: 35px;height: 35px;" src="/eden/images/ajax_loader.gif" />
              </div>
              <div style="display: flex;align-items: center;justify-content: center;" v-else-if="donnees_select.length == 0">
                @traduction('composant.champ_selection_element_multiple.aucun_resultat')
              </div>
            </div>
          </div>
        </div>`,
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
        data : function(){
            return {
                donnees_select: [],
                elements_a_charger: true,
                chargement_elements: false,
                recherche:'',
                requete: false,
                affichage_valeurs : {},
                options:['vide','non_vide'],
                gestion_lien_champ :false,
            };
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
            affichage_elements_supplementaires : function(event){

                if (this.elements_a_charger && this.chargement_elements === false && $(event.target).scrollTop() + $(event.target).innerHeight() + 5 >= $(event.target)[0].scrollHeight)
                    this.charger_donnees();
            },
            charger_donnees : function(){
                var composant = this;
                composant.chargement_elements = true;

                if(Array.isArray(this.valeurs))
                    var ids_a_eviter = structuredClone(composant.valeurs);
                else
                    var ids_a_eviter = [];

                for(element of composant.donnees_select){
                    ids_a_eviter.push(element.id);
                }

                if(this.requete !== false)
                    this.requete.abort();

                this.requete = $.post({
                    url: '/eden/element/recherche/'+composant.filtre.modele.type_element_ajax+'/'+(this.recherche != '' ? this.recherche : '%25'),
                    dataType: "json",
                    data: {
                        ids_a_eviter: ids_a_eviter,
                        nombre_elements : 15,
                        source: {
                            type_element : this.filtre.modele.type_element,
                            nom_sql : this.filtre.modele.nom_sql,
                        }
                    },
                }).done(async (donnees) => {

                    this.requete = false;

                    if(donnees.length === 0 || donnees.length < 15)
                        composant.elements_a_charger = false;

                    composant.donnees_select = await composant.donnees_select.concat(donnees);
                    composant.chargement_elements = false;
                });
            },
            ajout_element : function(event,element,index){

                event.stopPropagation();

                var valeurs = structuredClone(this.valeurs);

                if(!Array.isArray(valeurs))
                    valeurs = [];

                valeurs.push(element.id);

                this.donnees_select.splice(index,1);

                if(!this.affichage_valeurs[element.id])
                    this.$set(this.affichage_valeurs,element.id,element.affichage_pour_recherche);

                this.changement_filtre(valeurs);
            },
            retirer_element : function(event,id){

                event.stopPropagation();

                var valeurs = structuredClone(this.valeurs);

                valeurs.splice(valeurs.indexOf(id),1);

                this.donnees_select = [];

                this.elements_a_charger = true;

                if(valeurs.length == 0)
                    this.changement_filtre(null);
                else
                    this.changement_filtre(valeurs);

                this.$nextTick(() => {
                    this.charger_donnees();
                });
            },
            recupere_affichages_elements_initialisation : function(){

                if(!Array.isArray(this.valeurs) || this.valeurs.length == 0)
                    return;

                var type_element = this.filtre.modele.type_element_ajax;

                var url = "/eden/elements/"+type_element;

                $.post({
                    url: url,
                    dataType: "json",
                    data: {
                        elements_id : this.valeurs,
                    }
                }).done((elements) => {
                    for(element of elements){
                        this.$set(this.affichage_valeurs,element.id,element.affichage_pour_recherche);
                    }
                });
            },
        },
        computed: {
            valeur_checkbox : {
                get(){
                    if(Array.isArray(this.valeurs))
                        return null;

                    return this.valeurs;
                },
                set(valeur){
                    this.changement_filtre(valeur);

                    this.$nextTick(() => {
                        this.charger_donnees();
                    });
                },
            },
            valeurs_selectionnes : function(){

                if(!Array.isArray(this.valeurs))
                    return [];

                return this.valeurs.map((x) => {
                    return this.affichage_valeurs[x];
                });
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

            if(!Array.isArray(this.valeurs) && this.valeurs.startsWith('lien_champ|'))
                this.gestion_lien_champ = true;
            
            this.recupere_affichages_elements_initialisation();
            this.charger_donnees();
        },
    });
</script>
