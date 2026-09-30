<script>
    const enrichissement = Vue.component('enrichissement', {
        template: `
            <span class="enrichissement">
                <slot name="bouton" :parametrage_modale="parametrage_modale">
                </slot>

                <div id="modales_enrichissement" ref="modales">
                    <template v-if="modale_enrichissement_parametrage">
                        <transition name="modal" >
                            <div class="modal-mask">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@traduction('composant.enrichissement.modale_enrichissement_parametrage.titre')</h5>
                                            <button type="button" class="close" @click="modale_enrichissement_parametrage = false"  aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body css_form">

                                            <div class="row">
                                                <div class="col-sm-2">
                                                    @traduction('composant.enrichissement.modale_enrichissement_parametrage.modele_enrichissement')
                                                </div>
                                                <div class="col-sm-4">
                                                    <select class="form-control" v-model="modele_enrichissement_id" @change="chargement_modele_enrichissement()">
                                                        <option :value="null" v-html="$root.traduction('composant.enrichissement.modale_enrichissement_parametrage.modele_enrichissement_nouveau')"></option>
                                                        <option v-for="modele in modeles_enrichissement" :key="modele.id" :value="modele.id">
                                                            @{{ modele.nom }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-4" v-if="element_id == null">
                                                    <input type="text" v-model="modele_enrichissement.nom" :placeholder="$root.traduction('composant.enrichissement.modale_enrichissement_parametrage.modele_enrichissement_nom')">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-2">
                                                    @traduction('composant.enrichissement.modale_enrichissement_parametrage.champs_a_enrichir')
                                                </div>
                                                <div class="col-sm-10">
                                                    <select-champs-libres
                                                    
                                                        :champs_libres="champs_enrichissement"
                                                        :type_element_origine="type_element"
                                                        :type_element="type_element"
                                                        @changement_select_champs_libres="champs_libres_enrichir.push($event.nom_sql)"

                                                    ></select-champs-libres>
                                                    <div>
                                                        <span v-for="(nom_sql, index) in champs_libres_enrichir" :key="index" class="badge badge-secondary mr-1">
                                                            <span v-html="$root.traduction('champs_libres.' + type_element + '.' + nom_sql + '.nom')+' ('+nom_sql+')'"></span>
                                                            <i class="fas fa-times-circle ml-1" @click="champs_libres_enrichir.splice(index, 1)"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-2">
                                                    @traduction('composant.enrichissement.modale_enrichissement_parametrage.champs_aide_enrichissement')
                                                </div>
                                                <div class="col-sm-10">
                                                    <select-champs-libres
                                                    
                                                        @changement_select_champs_libres="champs_libres_aide_enrichissement.push($event.nom_sql)"
                                                        :champs_libres="champs_aides"
                                                        :type_element_origine="type_element"
                                                        :type_element="type_element"

                                                    ></select-champs-libres>
                                                    <div>
                                                        <span v-for="(nom_sql, index) in champs_libres_aide_enrichissement" :key="index" class="badge badge-secondary mr-1">
                                                            <span v-html="$root.traduction('champs_libres.' + type_element + '.' + nom_sql + '.nom')+' ('+nom_sql+')'"></span>
                                                            <i class="fas fa-times-circle ml-1" @click="champs_libres_aide_enrichissement.splice(index, 1)"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row" v-if="element_id > 0">
                                                <div class="col-sm-2">
                                                    @traduction('composant.enrichissement.modale_enrichissement_parametrage.valeurs_envoyes')
                                                </div>
                                                <div class="col-sm-10">
                                                    <span v-html="champs_libres_aide_enrichissement.map(champ => modele.affichages[champ]).join(', ')"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" @click="modale_enrichissement_parametrage = false">@traduction('interface.listes.fermer')</button>
                                            <button class="btn btn-sm btn-success" v-if="element_id > 0" @click="enrichir_fiche">@traduction('composant.enrichissement.modale_enrichissement_parametrage.enrichir')</button>
                                            <template v-else>
                                                <button class="btn btn-sm btn-danger" v-if="modele_enrichissement_id > 0" @click="supprimer_modele_enrichissement">@traduction('composant.enrichissement.modale_enrichissement_parametrage.supprimer_modele_enrichissement')</button>
                                                <button class="btn btn-sm btn-success"  @click="enregistrement_modele_enrichissement">@traduction('composant.enrichissement.modale_enrichissement_parametrage.enregistrement_modele_enrichissement')</button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </template>

                    <template v-if="modale_enrichissement_resultat">
                        <transition name="modal" >
                            <div class="modal-mask modale_enrichissement_resultat">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@traduction('composant.enrichissement.modale_enrichissement_resultat.titre')</h5>
                                            <button type="button" class="close" @click="modale_enrichissement_resultat= false"  aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body css_form">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            <span>
                                                                <input type="checkbox" :checked="valeurs_a_enregistrer.length === retour_enrichissement.filter(r => r.valeur != null).length" @click.stop="valeurs_a_enregistrer = valeurs_a_enregistrer.length === retour_enrichissement.filter(r => r.valeur != null).length ? [] : retour_enrichissement.filter(r => r.valeur != null).map(r => r.champ)">
                                                            </span>
                                                        </th>
                                                        <th>@traduction('composant.enrichissement.modale_enrichissement_resultat.champ')</th>
                                                        <th>@traduction('composant.enrichissement.modale_enrichissement_resultat.valeur_actuelle')</th>
                                                        <th>@traduction('composant.enrichissement.modale_enrichissement_resultat.valeur')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="retour in retour_enrichissement" :key="retour.champ" :class="{'selectionne' : valeurs_a_enregistrer.includes(retour.champ)}">
                                                        <td @click="gestion_valeur_enregistrement(retour)">
                                                            <span>
                                                                <input type="checkbox" :checked="valeurs_a_enregistrer.includes(retour.champ)" :disabled="retour.valeur == null" @click.stop="gestion_valeur_enregistrement(retour)">
                                                            </span>
                                                        </td>
                                                        <td @click="gestion_valeur_enregistrement(retour)" v-html="$root.traduction('champs_libres.' + type_element + '.' + retour.champ + '.nom')+' ('+retour.champ+')'">
                                                        </td>
                                                        <td @click="gestion_valeur_enregistrement(retour)"> 
                                                            <textarea disabled v-if="types_champs[retour.champ] == 'textarea'" @click.stop v-model="modele[retour.champ]"></textarea>
                                                            <input disabled v-else @click.stop :type="types_champs[retour.champ]" v-model="modele[retour.champ]">
                                                        </td>
                                                        <td @click="gestion_valeur_enregistrement(retour)">
                                                            <span v-if="retour.valeur == null">@traduction('composant.enrichissement.modale_enrichissement_resultat.valeur_null')</span>
                                                            <textarea v-else-if="types_champs[retour.champ] == 'textarea'" @click.stop v-model="retour.valeur"></textarea>
                                                            <input v-else @click.stop :type="types_champs[retour.champ]" v-model="retour.valeur">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" @click="modale_enrichissement_resultat = false">@traduction('interface.listes.fermer')</button>
                                            <button type="button" class="btn btn-primary" @click="enregistrement()">@traduction('interface.listes.enregistrer')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </template>

                </div>
            </span>
        `,
        props : {
            type_element: {
                type: String,
                required: true
            },
            element_id: {
                type: Number,
                default: null
            }
        },
        data(){
            return {
                modele : {},
                modeles_enrichissement : [],
                modele_enrichissement: {
                    nom: '',
                    type_element: this.type_element,
                },
                modele_enrichissement_id: null,
                modale_enrichissement_parametrage: false,
                modale_enrichissement_resultat: false,
                champs_libres_type_element : [],
                champs_libres_enrichir: [],
                champs_libres_aide_enrichissement: [],
                retour_enrichissement: {},
                valeurs_a_enregistrer: []
            };
        },

        mounted(){
            $('#stack_modales_composants').append($(this.$refs.modales));
        },

        methods: {

            parametrage_modale : async function(){

                await this.chargement_modeles_enrichissement();
                await this.chargement_modele();

                await this.chargement_champs_libres();

                this.champs_libres_enrichir = [];
                this.champs_libres_aide_enrichissement = [];
                this.modele_enrichissement = {
                    nom: '',
                    type_element: this.type_element,
                };
                this.modele_enrichissement_id = null;

                this.modale_enrichissement_parametrage = true;
            },

            chargement_champs_libres : async function(){

                this.champs_libres_type_element = [];

                await $.ajax({
                    url: 'eden/champs/valeurs/'+this.type_element,
                    dataType:'json'
                }).done((champs_libres) => {
                    this.champs_libres_type_element = champs_libres;
                });
            },

            enrichir_fiche : function(){
                // Génère la structure pour les champs à enrichir
                var champs_a_enrichir = this.champs_libres_enrichir.map((champ) => {
                    var champ_info = this.champs_libres_type_element.find(c => c.nom_sql === champ) || {};
                    return {
                        nom: champ,
                        nom_interface: this.$root.traduction('champs_libres.' + this.type_element + '.' + champ_info.nom_sql + '.nom'),
                        type: champ_info.type || null,
                        valeur_actuel: (this.modele && this.modele[champ]) ? this.modele[champ] : ''
                    };
                });
                // Génère la structure pour les champs d'aide à l'enrichissement
                var champs_aide = {};
                
                
                for(champ_aide of this.champs_libres_aide_enrichissement){
                    var champ_info = this.champs_libres_type_element.find(c => c.nom_sql === champ_aide) || {};
                    champs_aide[this.$root.traduction('champs_libres.' + this.type_element + '.' + champ_info.nom_sql + '.nom')] = this.modele.affichages[champ_aide] ?? '';
                }

                var demande = {
                    "Informations sur l'élément": champs_aide,
                    "Champs à enrichir": champs_a_enrichir
                };

                var prompt = "J'ai besoin d'enrichir une fiche de " + this.$root.traduction('tables_libres.'+this.type_element+'.nom_table') + 
                        ".Je vais te fournir 2 paramétres dans la demande : les valeurs (Champs d'aide à l'enrichissement) que l'on dispose sur cet élément qui vont te permettre de faire tes recherches et les valeurs qu'on veut récupérer (Champs à enrichir)."+
                        "Tu dois retourner les informations publiques disponibles que tu peux trouver pour chaque champs à enrichir."+
                        "Si tu ne trouves pas d'informations, tu dois retourner une valeur NULL pour ce champ."+
                        "Les champs comportent des types de données différents,"+
                        "et voilà la correspondance entre le type qui t'es transmis et à quoi cela correspond : 0 => 'texte', 2 => 'nombre', 3 => 'nombre décimal', 4 => 'date format Y-m-d', 5 => 'datetime format Y-m-d H:i:s', 6 => 'textarea'"+
                        "Voila les paramétres en json :" + JSON.stringify(demande);

                loading(true);

                $.ajax({
                    url: 'eden/ai/requete_open_ai/2',
                    method: 'POST',
                    data: JSON.stringify({ prompt: prompt }),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: (response) => {
                        this.retour_enrichissement = JSON.parse(response.reponse).resultats;
                        this.modale_enrichissement_resultat = true;
                        this.modale_enrichissement_parametrage = false;

                        loading(false);
                    },
                });
            },
            
            gestion_valeur_enregistrement : function(retour){

                if(retour.valeur == null)
                    return;

                if(this.valeurs_a_enregistrer.includes(retour.champ)){
                    this.valeurs_a_enregistrer.splice(this.valeurs_a_enregistrer.indexOf(retour.champ), 1);
                } else {
                    this.valeurs_a_enregistrer.push(retour.champ);
                }
            },

            enregistrement : function(){

                var donnees_a_enregistrer = {};

                for(champ of this.valeurs_a_enregistrer){
                    var retour = this.retour_enrichissement.find(r => r.champ === champ);
                    if(retour && retour.valeur != null){
                        donnees_a_enregistrer[retour.champ] = retour.valeur;
                    }
                }

                $.post({
                    url : 'eden/element/' + this.type_element + '/'+ this.modele.id +'/enregistrer',
                    dataType : 'json',
                    data : donnees_a_enregistrer,
                }).done(async (donnees) => {

                    if (donnees.retour !== true) {

                        await erreur(donnees.retour);
                        return;
                    }

                    this.modale_enrichissement_resultat = false;
                    this.$emit('enregistrement', donnees);
                });
            },

            chargement_modele : function(){

                if(this.element_id === null)
                    return;

                $.ajax({
                    url: 'eden/element/' + this.type_element + '/' + this.element_id,
                    dataType:'json'
                }).done((modele) => {
                    this.modele = modele;
                });
            },

            chargement_modeles_enrichissement : async function(){

                this.modeles_enrichissement = await $.post({
                    url : 'eden/elements/modele_enrichissement',
                    dataType:'json',
                    data:{
                        filtrage:[
                            {
                                champ : 'type_element',
                                condition : 'where',
                                valeur : this.type_element
                            },
                        ]
                    }
                });

                if(this.modeles_enrichissement.length > 0){

                    var champs = await $.post({
                        url : 'eden/elements/modele_enrichissement_champ',
                        dataType:'json',
                        data:{
                            filtrage:[
                                {
                                    champ : 'modele_enrichissement_id',
                                    condition : 'whereIn',
                                    valeur : this.modeles_enrichissement.map(m => m.id)
                                },
                            ]
                        }
                    });

                    for(var modele of this.modeles_enrichissement){
                        modele.champs = champs.filter(c => c.modele_enrichissement_id === modele.id);
                    }
                }
            },

            chargement_modele_enrichissement : function(){
                
                if(this.modele_enrichissement_id === null){
                    this.modele_enrichissement = {
                        nom: '',
                        type_element: this.type_element,
                    };
                    this.champs_libres_enrichir = [];
                    this.champs_libres_aide_enrichissement = [];
                } else {
                    this.modele_enrichissement = this.modeles_enrichissement.find(m => m.id === this.modele_enrichissement_id) || {};
                    this.champs_libres_enrichir = this.modele_enrichissement.champs.filter(c => c.type === 1).map(c => c.nom_sql);
                    this.champs_libres_aide_enrichissement = this.modele_enrichissement.champs.filter(c => c.type === 2).map(c => c.nom_sql);
                }
            },

            enregistrement_modele_enrichissement : function(){

                if(this.modele_enrichissement.nom == ''){
                    erreur(this.$root.traduction('composant.enrichissement.modale_enrichissement_parametrage.modele_enrichissement_nom_obligatoire'));
                    return;
                }

                loading(true);

                var champs = this.champs_libres_enrichir.map(champ => {
                    return {
                        nom_sql: champ,
                        type: 1,
                    };
                }).concat(this.champs_libres_aide_enrichissement.map(champ => {
                    return { 
                        nom_sql: champ,
                        type: 2,
                    };
                }));

                var url = 'eden/element/modele_enrichissement/creer';

                if(this.modele_enrichissement !== null && this.modele_enrichissement.id > 0){
                    url = 'eden/element/modele_enrichissement/' + this.modele_enrichissement.id + '/enregistrer';
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        type_element: this.type_element,
                        nom: this.modele_enrichissement.nom,
                        champs: champs ?? '[]'
                    },
                }).done((response) => {
                    this.modele_enrichissement = response.element;
                    if(this.modele_enrichissement_id === null){
                        this.modeles_enrichissement.push(this.modele_enrichissement);
                        this.modele_enrichissement_id = this.modele_enrichissement.id;
                    }

                    loading(false);
                });
            },

            supprimer_modele_enrichissement : function(){

                loading(true);

                $.ajax({
                    url : 'eden/element/modele_enrichissement/' + this.modele_enrichissement_id + '/supprimer',
                }).done((response) => {
                    this.modeles_enrichissement.splice(this.modeles_enrichissement.findIndex(m => m.id === this.modele_enrichissement_id), 1);
                    this.modele_enrichissement_id = null;
                    this.chargement_modele_enrichissement();

                    loading(false);
                });
            },
        },

        computed: {
            champs_enrichissement: function(){
                return [{ 
                    type_element : this.type_element,
                    index_traduction : 'tables_libres.'+this.type_element+'.nom_table',
                    champs_libres : this.champs_libres_type_element.filter((champ) => {
                        return [0, 2, 3, 4, 5, 6].includes(champ.type) && !this.champs_libres_enrichir.includes(champ.nom_sql) && !this.champs_libres_aide_enrichissement.includes(champ.nom_sql);
                    })
                }];
            },
            champs_aides: function(){
                return [{ 
                    type_element : this.type_element,
                    index_traduction : 'tables_libres.'+this.type_element+'.nom_table',
                    champs_libres : this.champs_libres_type_element.filter((champ) => {
                        return !this.champs_libres_enrichir.includes(champ.nom_sql) && !this.champs_libres_aide_enrichissement.includes(champ.nom_sql);
                    })
                }];
            },
            types_champs : function(){
                var types_champs = {};

                var type_par_numero = {
                    0: 'text',
                    2: 'number',
                    3: 'number',
                    4: 'date',
                    5: 'datetime',
                    6: 'textarea'
                };

                for(var champ of this.champs_libres_type_element){
                    types_champs[champ.nom_sql] = type_par_numero[champ.type] || 'text';
                }

                return types_champs;
            }
        }
    });
</script>