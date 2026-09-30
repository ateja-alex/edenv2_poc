<script>
const traduction_modale = Vue.component('traduction-modale', {
        template: `<div>
                <template v-if="modale_ajout_traduction">
                    <transition name="modal" >
                        <div style="z-index:1059" class="modal-mask">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">@{{ $root.traduction('composant.traduction_modale.ajout_traduction') }}</h5>
                                    </div>

                                    <div class="modal-body css_form js_selection_element" >
                                        {!! formulaire('traduction_valeur') !!}
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" @click="modale_ajout_traduction = false">@{{ $root.traduction('composant.traduction_modale.fermer') }}</button>
                                        <button type="button" class="btn btn-primary" @click="enregistrement_traduction_element()">@{{ $root.traduction('composant.traduction_modale.enregistrer') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>
                </template>
            </div>`,
        data: function () {
            return {

                langues:{},
                modale_ajout_traduction:false,
                traduction_element:{},
                modele_traduction:{
                    index : '',
                    categorie : null,
                },
                index_langue_affichage_ajout:0,
                categorie : 0,
                index_traduction: '',
                champ: '',
            }
        },
        methods: {

            enregistrement_traduction_element: function(){

                var vue_instance = this;

                loading(true);

                $.post({
                    url : '/eden/parametrage/traduction/enregistrer',
                    dataType : 'json',
                    data : vue_instance.traduction_element,
                }).done(async function(donnees){

                    loading(false);

                    if(donnees.retour == false){
                        toastr.error(donnees.message);
                        return;
                    }

                    toastr.success(vue_instance.$root.traduction('composant.traduction_modale.enregistrement_effectue'));

                    vue_instance.$root.$emit('enregistrement_traduction');

                    await vue_instance.$root.mise_a_jour_traductions_valeurs();

                    vue_instance.modale_ajout_traduction = false;

                });
            },

            modification_langue: function(ajout){

                this.index_langue_affichage_ajout+=ajout;
            },

            affectation_valeur_specifique(event,traduction,langue){

                traduction[langue.code].traduction_specifique = $(event.target).val();

            },

            chargement_traduction(){

                var vue_instance = this;

                if(vue_instance.index_traduction !== ''){

                    var index_traduction = vue_instance.index_traduction;

                    if(vue_instance.champ !== '' && vue_instance.champ != null)
                        index_traduction += '.'+vue_instance.champ;

                    loading(true);

                    $.post({
                        url: '/eden/parametrage/traduction/recuperer_element',
                        dataType: 'json',
                        data:{
                            'index_traduction' : index_traduction,
                        }
                    }).done(function(donnees){

                        loading(false);

                        if(donnees.retour !== true){
                            toastr.error(donnees.message);
                            return;
                        }

                        vue_instance.traduction_element = donnees.element;

                        vue_instance.modale_ajout_traduction = true;
                    });
                }
                else{

                    vue_instance.traduction_element = JSON.parse(JSON.stringify(this.modele_traduction));

                    vue_instance.traduction_element.categorie = this.categorie;

                    vue_instance.traduction_element.creation = true;

                    vue_instance.modale_ajout_traduction = true;
                }

            },
        },
        computed: {
             langues_affichage_ajout:function(){

                var langues_affichage_ajout = [];

                var compteur = 1;

                var vue_instance = this;

                $.each(vue_instance.langues,function(index,langue){

                    if(compteur > vue_instance.index_langue_affichage_ajout){

                        if(langues_affichage_ajout.length < 3)
                            langues_affichage_ajout.push(langue);

                    }

                    compteur++;
                });

                return langues_affichage_ajout;

            },
        },
        mounted: async function() {

            var vue_instance = this;

            vue_instance.langues = vue_instance.$root.langues_traduction_erp;

            $.each(this.langues, function (index, langue) {

                vue_instance.modele_traduction[langue.code] = {};
                vue_instance.modele_traduction[langue.code].traduction_standard = null;
                vue_instance.modele_traduction[langue.code].traduction_specifique = null;
            });

            vue_instance.traduction_element = JSON.parse(JSON.stringify(vue_instance.modele_traduction));
        },
    });
</script>
