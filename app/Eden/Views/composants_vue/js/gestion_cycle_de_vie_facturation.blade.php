<script>
    const gestion_cycle_de_vie_facturation = Vue.component('gestion-cycle-de-vie-facturation', {
        template: /*template*/`<span>
            <template v-if="modal_ouvert">
                <transition name="modal">
                    <div class="modal-mask">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">@traduction('interface.listes.poser_statut_cycle_de_vie')</h5>
                                    <button type="button" class="close" @click="modal_ouvert = false" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body css_form">
                                    <div v-if="chargement" style="text-align:center;padding:30px;">
                                        <img style="width: 60px;" src="{{ 'eden/images/ajax_loader.gif' }}">
                                    </div>
                                    <div v-else-if="statut_en_attente" class="css_statut_cycle_de_vie_en_attente">
                                        <i class="fas fa-hourglass-half css_statut_cycle_de_vie_en_attente_icone"></i>
                                        <div class="css_statut_cycle_de_vie_en_attente_libelle">@{{ libelle_statut_en_attente }}</div>
                                        <div class="css_statut_cycle_de_vie_en_attente_message">@{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.statut_en_attente_message') }}</div>
                                    </div>
                                    <formulaire v-else ref="formulaire_statut_cycle_de_vie" nom_formulaire="facturation_electronique_cycle_de_vie"></formulaire>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" @click="modal_ouvert = false">@traduction('interface.listes.fermer')</button>
                                    <div v-if="!chargement && !statut_en_attente" class="btn btn-primary" @click="enregistrer()">@traduction('interface.listes.enregistrer')</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </template>
        </span>`,
        data: function(){
            return {
                modal_ouvert: false,
                chargement: false,
                statut_en_attente: null,
                nom_champ_courant: null,
                id_element_courant: null,
                intervalle_verification: null,
            };
        },
        computed: {
            libelle_statut_en_attente: function(){

                if(!this.statut_en_attente)
                    return '';

                var code = this.statut_en_attente.statut_achat || this.statut_en_attente.statut_vente;

                return this.$root.recuperer_valeur_liste_formatee(729, code)?.valeur ?? code;
            },
        },
        watch: {
            modal_ouvert: function(ouvert){

                if(!ouvert)
                    this.arreter_verification();
            },
        },
        beforeDestroy: function(){

            this.arreter_verification();
        },
        methods: {
            ouvrir: function(nom_champ, id_element){

                this.nom_champ_courant = nom_champ;
                this.id_element_courant = id_element;
                this.statut_en_attente = null;
                this.chargement = true;

                this.vider_cache_element();

                this.modal_ouvert = true;

                this.verifier_statut_en_attente();
            },
            vider_cache_element: function(){

                var type_element = this.nom_champ_courant.replace(/_id$/, '');

                this.$delete(this.$root.cache.affichage_element, type_element + '_' + this.id_element_courant);
            },
            verifier_statut_en_attente: function(){

                $.post({
                    url: "{{ route('base_eden.element.recuperer_liste','facturation_electronique_cycle_de_vie') }}",
                    data: {
                        filtrage: [
                            {champ: this.nom_champ_courant, condition: 'where', valeur: this.id_element_courant},
                            {champ: 'statut_envoi', condition: 'where', valeur: 2},
                        ],
                    },
                    dataType: 'json',
                }).done((en_attente) => {

                    this.statut_en_attente = en_attente[0] ?? null;
                    this.chargement = false;

                    if(this.statut_en_attente){

                        if(this.intervalle_verification === null)
                            this.intervalle_verification = setInterval(() => this.verifier_statut_en_attente(), 30000);

                        return;
                    }

                    this.arreter_verification();

                    this.$once('formulaire_charger',() => {
                        this.$refs.formulaire_statut_cycle_de_vie.element[this.nom_champ_courant] = this.id_element_courant;
                    });
                }).fail(() => {

                    this.chargement = false;
                });
            },
            arreter_verification: function(){

                if(this.intervalle_verification === null)
                    return;

                clearInterval(this.intervalle_verification);
                this.intervalle_verification = null;
            },
            enregistrer: async function(){

                loading(true);

                var donnees = await this.$refs.formulaire_statut_cycle_de_vie.enregistrer();

                if(donnees.retour === true)
                    this.modal_ouvert = false;

                loading(false);
            },
        }
    });
</script>
