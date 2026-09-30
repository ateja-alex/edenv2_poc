<script>
    const champ_adresse = Vue.component('champ-adresse', {
        template: `<div style="width: 100%;">
                <div style="display:flex">
                    <div v-click_outside="desafficher_select" style="width: 100%;">
                        <input @input="debounce_recherche" @click="affichage_select = true" v-model="modele[nom_sql]" :name="name" :placeholder="placeholder" :disabled="lecture_seule" type="text" />
                        <div class="select_adresse" v-if="affichage_select && suggestions.length > 0">
                            <div v-for="suggestion of suggestions" @mousedown.stop="choix_suggestion(suggestion)">
                              @{{ suggestion.description }}
                            </div>
                            <div class="aucun_resultat" v-if="suggestions.length == 0">
                              @traduction('composant.champ_selection_element_multiple.aucun_resultat')
                            </div>
                        </div>
                    </div>
                    <i class="fas fa-search-location css_pointer css_input_ajout_selection_element css_background_couleur_primaire"
                        :title="$root.traduction('composant.champ_adresse.titre_bouton_localisation')"
                        @click="localisation_actuel"></i>
                </div>
        </div>`,
        props: {

            modele: {},
            nom_sql: '',
            name: '',
            placeholder: '',
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
            mappage: {},
        },
        data: function(){
            return {
                token_session : null,
                suggestions: [],
                debounce: null,
                affichage_select: null,
                requete_en_cours: false,
            }
        },
        methods : {

            localisation_actuel : function(){

                loading(true);

                const success = (position) => {
                    const latitude  = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    $.post({
                        url : '{{route('google.geocodage_position', [], false)}}',
                        dataType: 'json',
                        data : {
                            latitude : latitude,
                            longitude : longitude,
                        }
                    }).done((response) => {

                        if(response.status != "OK" || response.results.length == 0) {
                            loading(false);
                            return;
                        }

                        this.appliquer_valeur(response.results[0].address_components);

                        loading(false);
                    });
                };

                const error = (err) => {
                    loading(false);
                };

                // This will open permission popup
                navigator.geolocation.getCurrentPosition(success, error,{enableHighAccuracy : true,maximumAge:1000});
            },

            debounce_recherche:function () {
                clearTimeout(this.debounce);
                this.debounce = setTimeout(() => { this.chargement_localisation(); }, 300);
            },

            chargement_localisation : async function(){

                if(this.modele[this.nom_sql] == null || this.modele[this.nom_sql].length <= 3){
                    this.suggestions = [];
                    this.affichage_select = false;
                    return;
                }

                if(this.requete_en_cours !== false)
                    this.requete_en_cours.abort();

                this.requete_en_cours = $.post({
                    url : '/eden/adresse/chargement_adresse',
                    dataType: 'json',
                    data : {
                        valeur : this.modele[this.nom_sql],
                    }
                }).done((response) => {
                    this.suggestions = response.predictions;
                    this.affichage_select = true;
                    this.requete_en_cours = false;
                });
            },

            choix_suggestion : async function(suggestion){

                $.post({
                    url : '/eden/adresse/detail_adresse',
                    dataType: 'json',
                    data : {
                        place_id : suggestion.place_id,
                    }
                }).done((detail) => {
                    this.appliquer_valeur(detail.address_components);
                    this.suggestions = [];
                    this.affichage_select = false;
                });
            },

            desafficher_select : function(){
                this.affichage_select = false;
            },

            appliquer_valeur : function(addressComponents) {

                var correspondance_champ_google = {
                    'adresse':[
                        'street_number',
                        'route'
                    ],
                    'numero_rue' : ['street_number'],
                    'nom_rue' : ['route'],
                    'ville' : ['locality'],
                    'region' : ['administrative_area_level_1'],
                    'departement' : ['administrative_area_level_2'],
                    'pays' : ['country'],
                    'code_postal' : ['postal_code']
                };

                for (champ_google in this.mappage) {

                    var champ_eden = this.mappage[champ_google];

                    if (champ_eden == null)
                        continue;

                    var valeur = '';

                    for (champ of correspondance_champ_google[champ_google]) {

                        var valeur_google = addressComponents.filter(composant => composant.types.includes(champ));

                        if (valeur_google.length == 0)
                            continue;

                        if (valeur != '')
                            valeur += ' ';

                        valeur += valeur_google[0].longText ? valeur_google[0].longText : valeur_google[0].long_name;
                    }

                    this.modele[champ_eden] = valeur;
                }
            },
        },

        directives: {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (!(el.contains(event.target))) {
                            vnode.context[binding.expression]();
                        }
                    };
                    document.body.addEventListener('click', el.clickOutsideEvent)
                },
                unbind: function (el) {
                    document.body.removeEventListener('click', el.clickOutsideEvent)
                },
            }
        }
    });
</script>
