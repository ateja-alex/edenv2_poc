<script>
    const filtre_type_element_dynamique = Vue.component('filtre-type-element-dynamique', {
        template: `
        <div>
            <div v-if="affichage">
                <span v-if="elements_ids.length == 0">
                    @traduction('filtres.champ_type_element_dynamique.contenu')
                    <span class="valeur" v-html="types_elements.join(', ')"></span>
                </span>
                <span v-else>
                    @traduction('filtres.champ_type_element_dynamique.un_des_elements')
                    <span class="valeur" v-html="elements_affichage.join(', ')"></span>
                </span>
            </div>
            <div v-else class="css_liste_checkbox_popover" style="max-height:unset;">
                <div style="max-height:200px;overflow-y:auto;">
                    <div class="form-check form-check-inline css_checkbox_popover" v-for="type_element in champ_types_elements">
                        <label class="d-flex align-items-center" style="gap: 5px">
                          <input
                              class="form-check-input"
                              type="checkbox"
                              v-model="types_elements"
                              :value="type_element"
                              name="valeurs[]"
                          >
                          <div class="d-flex flex-column" v-html="$root.traduction('tables_libres.'+type_element+'.nom_table')" ></div>
                        </label>
                      </div>
                    </div>
                <div v-if="elements_ids.length > 0" style="display: inline-flex;align-items: center;flex-wrap: wrap;gap: 10px 5px;padding: 10px;width: 422px;">
                    <div class="block_selection_multiple_elements" style="max-width: 130px;" :key="index" v-for="(element,index) in elements_ids">
                          <span class="css_selection_element_multiple" style="padding: 5px 10px;">
                                <span class="css_selection_element_multiple_icone" @click="retirer_element($event,element,index)">
                                    <span class="fa fa-times"></span>
                                </span>
                                <span style="margin-left: 5px;" v-html="$root.traduction('tables_libres.'+element.type_element+'.nom_table') + '|' + affichage_valeurs[element.type_element+'.'+element.id]"></span>
                            </span>
                    </div>
                </div>
                <div class="d-flex align-items-center col-12 my-2">
                    <span>@traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.recherche')</span>
                    <input @keyup="donnees_select = {};elements_fin_chargement=[];charger_donnees()" v-model="recherche" class="mx-1" type="text" style="height: 24px; border: 1px solid #e4e0e0;">
                </div>
                <template v-for="(elements,type_element) in donnees_select">
                  <div class="filtre_type_element_dynamique_titre" v-html="$root.traduction('tables_libres.'+type_element+'.nom_table')"></div>
                  <div @scroll="affichage_elements_supplementaires($event,type_element)" style="position: unset;" class="css_select_multiselection_element">
                    <div @click="ajout_element($event,donnee,index,type_element)" style="min-height:36px" v-for="(donnee,index) in elements" v-html="donnee.affichage_pour_recherche"></div>
                    <div style="display: flex;align-items: center;justify-content: center;" v-if="chargement_elements.includes(type_element)">
                      <img style="width: 35px;height: 35px;" src="/eden/images/ajax_loader.gif" />
                    </div>
                    <div style="display: flex;align-items: center;justify-content: center;" v-else-if="donnees_select[type_element].length == 0">
                      @traduction('composant.champ_selection_element_multiple.aucun_resultat')
                    </div>
                  </div>
                </template>
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
                type : Object,
                default: function(){
                    return {
                        types_elements : [],
                        elements_ids : []
                    };
                }
            },
            affichage: {
                type : Boolean,
                default: false
            }
        },
        data : function(){
            return {
                donnees_select: {},
                elements_fin_chargement: [],
                chargement_elements: [],
                recherche:'',
                requete: {},
                affichage_valeurs : {},
            };
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
            affichage_elements_supplementaires : function(event,type_element){

                if (!this.elements_fin_chargement.includes(type_element) && !this.chargement_elements.includes(type_element) && $(event.target).scrollTop() + $(event.target).innerHeight() + 1 >= $(event.target)[0].scrollHeight)
                    this.charger_donnees(type_element);
            },
            charger_donnees : async function(type_element = null){

                var types_elements = this.types_elements;

                for(type_element of types_elements) {

                    this.chargement_element(type_element);
                }
            },
            chargement_element : function(type_element){

                if(!this.chargement_elements.includes(type_element))
                    this.chargement_elements.push(type_element);

                var ids_a_eviter = [];

                ids_a_eviter = this.elements_ids.filter(x => x.type_element == type_element).map(x => x.id);

                if(this.donnees_select[type_element] == null)
                    this.$set(this.donnees_select,type_element,[]);

                for (element of this.donnees_select[type_element]) {
                    ids_a_eviter.push(element.id);
                }

                if (this.requete[type_element] != null)
                    this.requete[type_element].abort();

                this.requete[type_element] = $.post({
                    url: '/eden/element/recherche/' + type_element + '/' + (this.recherche != '' ? this.recherche : '%25'),
                    dataType: "json",
                    data: {
                        ids_a_eviter: ids_a_eviter,
                        nombre_elements: 15,
                    },
                }).done((donnees) => {

                    this.requete[type_element] = null;

                    if (donnees.length === 0 || donnees.length < 15)
                        this.elements_fin_chargement.push(type_element);

                    this.donnees_select[type_element] = this.donnees_select[type_element].concat(donnees);

                    this.chargement_elements.splice(this.chargement_elements.indexOf(type_element),1);
                });
            },
            ajout_element : function(event,element,index, type_element){

                event.stopPropagation();

                var elements_ids = structuredClone(this.valeurs.elements_ids);

                if(!Array.isArray(elements_ids))
                    elements_ids = [];

                elements_ids.push({
                    type_element : type_element,
                    id : element.id
                });

                this.donnees_select[type_element].splice(index,1);

                if(!this.affichage_valeurs[type_element+'.'+element.id])
                    this.$set(this.affichage_valeurs,type_element+'.'+element.id,element.affichage_pour_recherche);

                this.changement_filtre({
                    types_elements : this.types_elements,
                    elements_ids : elements_ids
                });
            },
            retirer_element : function(event,element,index){

                event.stopPropagation();

                var elements_ids = structuredClone(this.elements_ids);

                elements_ids.splice(index,1);

                this.donnees_select[element.type_element] = [];

                if(this.elements_fin_chargement.includes(element.type_element))
                    this.elements_fin_chargement.splice(this.elements_fin_chargement.indexOf(element.type_element),1);

                this.chargement_element(element.type_element);

                this.changement_filtre({
                    types_elements : this.types_elements,
                    elements_ids : elements_ids
                });
            },
            recupere_affichages_elements_initialisation : async function(){

                if(this.elements_ids.length == 0)
                    return;

                for(type_element of this.types_elements) {

                    var url = "/eden/elements/" + type_element;

                    var elements = await $.post({
                        url: url,
                        dataType: "json",
                        data: {
                            elements_id: this.elements_ids.filter(x => x.type_element == type_element).map((x) => {return x.id}),
                        }
                    });

                    for (element of elements) {
                        this.$set(this.affichage_valeurs,type_element+'.'+element.id,element.affichage_pour_recherche);
                    }
                }
            },
        },
        computed: {
            champ_types_elements: function(){
                try{
                    return JSON.parse(this.filtre.modele.contenu).filter(x => x.valeur == true).map(o => o.type_element);
                }
                catch(e){
                    return [];
                }
            },
            types_elements : {
                get(){
                    return this.valeurs.types_elements;
                },
                set(valeur){

                    if(valeur.length == 0) {

                        this.elements_fin_chargement = [];
                        this.donnees_select = {};

                        this.changement_filtre(null);
                    }
                    else {

                        var elements_ids = this.valeurs.elements_ids;

                        // AJOUT
                        var type_element_a_charger = valeur.filter(x => !this.valeurs.types_elements.includes(x))[0];

                        if(type_element_a_charger)
                            this.chargement_element(type_element_a_charger);

                        var type_element_a_decharger = this.valeurs.types_elements.filter(x => !valeur.includes(x))[0];

                        if(type_element_a_decharger) {
                            for(index of Object.keys(elements_ids).reverse()){
                                element = elements_ids[index];

                                if(element.type_element == type_element_a_decharger)
                                    elements_ids.splice(index,1);
                            }

                            delete this.donnees_select[type_element_a_decharger];

                            if(this.elements_fin_chargement.includes(type_element_a_decharger))
                                this.elements_fin_chargement.splice(this.elements_fin_chargement.indexOf(type_element_a_decharger),1);

                        }

                        this.changement_filtre({
                            types_elements: valeur,
                            elements_ids: elements_ids,
                        });
                    }
                },
            },
            elements_ids : function(){
                return this.valeurs.elements_ids ? this.valeurs.elements_ids : [];
            },
            elements_affichage : function(){
                return this.elements_ids.map((x) => {
                    return x.type_element +' | '+this.affichage_valeurs[x.type_element+'.'+x.id];
                });
            },
        },
        mounted : function(){
            this.recupere_affichages_elements_initialisation();
            this.charger_donnees();
        }
    });
</script>
