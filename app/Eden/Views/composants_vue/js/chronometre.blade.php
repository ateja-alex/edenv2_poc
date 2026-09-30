<script>
const chronometre = Vue.component('chronometre', {
    template: `<div>
                    <div class="composant_chronometre">
                        <div class="nav-item dropdown dropdown_hover" aria-haspopup="true" aria-expanded="false">
                            <div class="css_ajouter_element">
                                <i :class="'css_btn_action_header fas '+ ( chronometre.en_cours ? 'fa-hourglass-half' : ( chronometre.temps == '00:00:00' ? 'fa-hourglass-start' : 'fa-hourglass-end'))" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composants.chronometre.chronometre')"></i>
                            </div>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="width: 220px;height:60px;">
                                <div style="position: relative;top: 6px;left: 10px;">
                                    <i :class="'css_btn_action_header fas fa-play bouton_chronometre '+(chronometre.en_cours ?'desactive' : '')" :title="$root.traduction('composants.chronometre.debut')" data-toggle="tooltip" data-placement="bottom"  @click="debut_chronometre(false)"></i>
                                    <i :class="'css_btn_action_header fas fa-pause bouton_chronometre '+(chronometre.en_cours == false ?'desactive' : '')" :title="$root.traduction('composants.chronometre.arret')" data-toggle="tooltip" data-placement="bottom"  @click="arret_chronometre"></i>
                                    <i :class="'css_btn_action_header fas fa-save bouton_chronometre '+(chronometre.temps == '00:00:00' && chronometre.en_cours == false ?'desactive' : '')" :title="$root.traduction('composants.chronometre.enregistrer')" data-placement="bottom" data-toggle="tooltip" @click="enregistrer_chronometre"></i>
                                    <i :class="'css_btn_action_header fas fa-history bouton_chronometre '+(chronometre.temps == '00:00:00' && chronometre.en_cours == false ?'desactive' : '')" :title="$root.traduction('composants.chronometre.supprimer')" data-placement="bottom" data-toggle="tooltip" @click="supprimer_chronometre(false)"></i>
                                    <i :class="'css_btn_action_header fas fa-pencil-alt bouton_chronometre '+(chronometre.temps == '00:00:00' && chronometre.en_cours == false ?'desactive' : '')" :title="$root.traduction('composants.chronometre.modifier')" data-placement="bottom" data-toggle="tooltip" @click="chronometre.temps != '00:00:00'?modifier_chronometre():''"></i>
                                </div>
                            </div>
                        </div>

                        <span v-if="chronometre.temps != '00:00:00'" style="color: white;position: relative;top: 7px;" v-html="chronometre.temps" ></span>
                    </div>
                    <template v-if="modale_chronometre">
                        <transition name="modal">
                            <div class="modal-mask modal_chronometre">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Chronométre</h5>
                                            <button type="button" @click="modale_chronometre = false;chronometre_enregistrement_en_cours = false;" aria-label="Close"
                                                    class="close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>

                                        <div class="modal-body css_form" v-if="parametrages_chronometres.length == 0">
                                            <span v-if="$root.moi.type_utilisateur > 0 || $root.moi.super_admin == 1">
                                                Le paramétrage du chronométre doit être effectué : <a href="{{route('base_eden.liste.index',array('parametrage_chronometre'), false)}}">Paramétrage du chronométre</a>
                                            </span>
                                            <span v-else>
                                                Le paramétrage du chronométre n'a pas été effectué, veuillez contacter un administrateur
                                            </span>
                                        </div>
                                        <div class="modal-body css_form" v-else>

                                            <div class="row" v-if="parametrages_chronometres.length > 1 && !chronometre_enregistrement_en_cours">
                                                <div class="col-sm-2">
                                                    Type élément
                                                </div>
                                                <div class="col-sm-4">
                                                    <select v-model.lazy="chronometre.type_element" @change="affectation_type_element">
                                                        <option v-for="parametrage_chronometre in parametrages_chronometres" :value="parametrage_chronometre.type_element" v-html="$root.traduction('tables_libres.'+parametrage_chronometre.type_element+'.nom_table')"></option>
                                                    </select>
                                                </div>
                                            </div>

                                            <formulaire ref="formulaire" :nom_formulaire="chronometre.type_element" contexte="chronometre_"></formulaire>

                                            <div class="row" v-if="chronometre_enregistrement_en_cours && chronometre.type_element != null">
                                                <div class="col-sm-2">
                                                    @{{ $root.traduction('champs_libres.'+parametrage_chronometre_selectionne.type_element+'.'+parametrage_chronometre_selectionne.champ_libre_correspondance_temps+'.nom') }}
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="text" v-model="chronometre.element[parametrage_chronometre_selectionne.champ_libre_correspondance_temps]"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" @click="modale_chronometre = false;chronometre_enregistrement_en_cours = false;">Fermer</button>
                                            <button type="button" v-if="chronometre.en_cours == false && chronometre_enregistrement_en_cours == false" class="btn btn-primary" @click="debut_chronometre(false);modale_chronometre = false;">Démarrer le chronométre</button>
                                            <button type="button" v-if="chronometre_enregistrement_en_cours && chronometre.type_element != null && chronometre.type_element != ''" class="btn btn-primary" @click="terminer_chronometre">Terminer le chronométre</button>
                                            <button type="button" v-if="chronometre_enregistrement_en_cours == false && chronometre.en_cours" class="btn btn-primary" @click="enregistrer_parametres_chronometre();modale_chronometre = false;">Enregistrer changement</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </template>
                </div>`,
                data: function(){

                    return {

                        @yield('donnees_pour_vuejs_data')
			            @stack('donnees_pour_vuejs_data')

                        chronometre:{
                            temps: '00:00:00',
                            en_cours: false,
                            temps_debut: null,
                            temps_arret: null,
                            duree_arret: 0,
                            demarrage: null,
                            element : {},
                            type_element:null,
                        },
                        modale_chronometre:false,
                        chronometre_enregistrement_en_cours: false,
                        parametrages_chronometres:[],

                    }

                },
                computed: {

                    @yield('donnees_pour_vuejs_computed')
                    @stack('donnees_pour_vuejs_computed')
                },
                created: function() {

                    @yield('donnees_pour_vuejs_created')
                    @stack('donnees_pour_vuejs_created')
                },
                methods:{

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                    debut_chronometre: async function (sans_enregistrement = false,forcer_debut = false) {
                        
                        var chronometre = this.chronometre;

                        if (chronometre.en_cours) return;

                        if (chronometre.temps_debut === null){
                            if(this.modale_chronometre === false && forcer_debut === false) {
                                await this.supprimer_chronometre(true);
                                this.$once('formulaire_charger',() =>{

                                    this.chronometre.element = this.$refs.formulaire.element;

                                    this.$root.$emit('lancement_chronometre',this.chronometre.element);
                                });
                                this.modale_chronometre = true;
                                return;
                            }
                            else
                                chronometre.temps_debut = new Date();
                        }

                        if (chronometre.temps_arret !== null) {
                            chronometre.duree_arret += (new Date() - chronometre.temps_arret);
                        }

                        chronometre.temps_arret = null;

                        chronometre.demarrage = setInterval(()=>{
                            this.chronometrage();
                        }, 500);
                        chronometre.en_cours = true;

                        if(sans_enregistrement == false)
                            this.enregistrer_parametres_chronometre();

                    },
                    arret_chronometre: function () {

                        var chronometre = this.chronometre;

                        if (chronometre.en_cours == false) return;

                        chronometre.en_cours = false;
                        chronometre.temps_arret = new Date();
                        clearInterval(chronometre.demarrage);

                        this.enregistrer_parametres_chronometre();
                    },
                    supprimer_chronometre: async function (sans_enregistrement = false) {

                        var chronometre = this.chronometre;
                        chronometre.en_cours = false;
                        clearInterval(chronometre.demarrage);
                        chronometre.duree_arret = 0;
                        chronometre.temps_debut = null;
                        chronometre.temps_arret = null;
                        chronometre.temps = "00:00:00";
                        chronometre.element = {};

                        if(sans_enregistrement == false)
                            this.enregistrer_parametres_chronometre(null);
                    },
                    enregistrer_parametres_chronometre:function(valeur = false){
                        var chronometre = this.chronometre;

                        if(valeur == false){
                            valeur = JSON.stringify(chronometre);
                        }

                        var moi_id = this.$root.moi.id;

                        var parametres = {};

                        parametres[moi_id] = {
                            'valeur_chronometre' : valeur,
                        };
                        $.post({
                            url: '{{route('base_eden.parametres_erp.enregistrer', [], false)}}',
                            data:{
                                type:'utilisateur',
                                parametres:parametres,
                            },
                        });
                    },
                    enregistrer_chronometre: function(){

                        var component = this;

                        if(this.chronometre.temps == '00:00:00')
                            return;

                        this.arret_chronometre();
                        this.traitement_champ_temps();
                        this.chronometre_enregistrement_en_cours = true;
                        component.$once('formulaire_charger',function(){

                            component.$refs.formulaire.element = component.chronometre.element;

                        });
                        this.modale_chronometre = true;

                    },
                    terminer_chronometre: async function(){

                        var component = this;

                        // On afficher le loader
                        loading(true);

                        var informations = {};

                        var champ = this.parametrage_chronometre_selectionne.champ_libre_correspondance_temps;

                        informations[champ] = this.chronometre.element[champ];

                        var donnees = await component.$refs.formulaire.enregistrer(informations);

                        if(donnees.retour === true) {
                            component.modale_chronometre = false;
                            component.chronometre_enregistrement_en_cours = false;
                            component.supprimer_chronometre();
                            this.$root.$emit('enregistrement_chronometre');
                        }

                        loading(false);

                    },
                    traitement_champ_temps:function(){

                        var component = this;

                        if(component.chronometre.type_element == null || component.chronometre.type_element == '')
                            return;

                        var requete_champ_libre = typeof this.$root.recuperer_champ_libre == 'function'
                            ? this.$root.recuperer_champ_libre(component.chronometre.type_element, this.parametrage_chronometre_selectionne.champ_libre_correspondance_temps)
                            : $.ajax({
                                url:'eden/champs/valeurs/'+component.chronometre.type_element+'/'+this.parametrage_chronometre_selectionne.champ_libre_correspondance_temps,
                                dataType:'json'
                            });

                        requete_champ_libre.then((champ_libre) => {
                            var temps = this.chronometre.temps;

                            temps = temps.split(':');

                            temps = parseInt(temps[0], 10) + (parseInt(temps[1], 10) / 60);

                            if(this.parametrage_chronometre_selectionne.type_saisie == null || this.parametrage_chronometre_selectionne.type_saisie == 1)
                                temps = temps * 60;

                            if(champ_libre.type == 2)
                                temps = Math.ceil(temps);
                            else if(champ_libre.type == 3)
                                temps = parseFloat(temps).toFixed(1);

                            component.chronometre.element[champ_libre.nom_sql] = temps;
                        })

                    },
                    correction_chiffre: function (valeur, chiffre) {
                        var zero = '';
                        for (var i = 0; i < chiffre; i++) {
                            zero += '0';
                        }
                        return (zero + valeur).slice(-chiffre);
                    },
                    affectation_type_element: function(){
                        this.$once('formulaire_charger',() =>{
                            this.chronometre.element = this.$refs.formulaire.element;
                            this.traitement_champ_temps();
                        });
                    },
                    modifier_chronometre : function() {

                        var component = this;
                        component.$once('formulaire_charger',function(){

                            component.$refs.formulaire.element = component.chronometre.element;

                        });
                        this.modale_chronometre = true;
                    },
                    chronometrage() {

                        var temps_actuel = new Date()
                            , temps_passe = new Date(temps_actuel - this.chronometre.temps_debut - this.chronometre.duree_arret)
                            , heure = temps_passe.getUTCHours()
                            , min = temps_passe.getUTCMinutes()
                            , sec = temps_passe.getUTCSeconds();

                        this.chronometre.temps =
                            this.correction_chiffre(heure, 2) + ":" +
                            this.correction_chiffre(min, 2) + ":" +
                            this.correction_chiffre(sec, 2);

                    }
                } ,

                mounted: async function() {

                    @yield('donnees_pour_vuejs_mounted')
                    @stack('donnees_pour_vuejs_mounted')

                    var component = this;

                    var moi = component.$root.moi;

                    this.parametrages_chronometres = await $.post({
                        url: '{{route('base_eden.element.recuperer_liste','parametrage_chronometre', false)}}',
                        dataType:'json'
                    });

                    if(this.parametrages_chronometres.length > 0)
                        component.chronometre.type_element = this.parametrages_chronometres[0].type_element;

                    $.ajax({
                        url: '/eden/parametres_erp/recuperer_parametre/valeur_chronometre/utilisateur/'+moi.id,
                        dataType:'json'
                    }).done(function(donnees){
                        if(donnees.parametre != null){

                            component.chronometre = JSON.parse(donnees.parametre);
                            component.chronometre.temps_debut = new Date(component.chronometre.temps_debut);
                            if(component.chronometre.temps_arret != null)
                                component.chronometre.temps_arret = new Date(component.chronometre.temps_arret);

                            if(component.chronometre.type_element != null)
                                component[component.chronometre.type_element] = component.chronometre.element;

                            if(component.chronometre.en_cours == true){
                                component.chronometre.en_cours = false;
                                component.debut_chronometre(true);
                            }

                        }

                        component.$root.$emit('chronometre_initialiser');
                    });
                },

                computed : {

                    parametrage_chronometre_selectionne : function() {

                        if(this.chronometre.type_element == '' || this.parametrages_chronometres.length == 0)
                            return;

                        return this.parametrages_chronometres.filter((parametrage_chronometre) => {
                            return parametrage_chronometre.type_element == this.chronometre.type_element;
                        })[0];
                    }
                }

            });
</script>
