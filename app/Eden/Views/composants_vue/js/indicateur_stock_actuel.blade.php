<script>
    const indicateur_stock_actuel = Vue.component('indicateur-stock-actuel', {
        template: `
        <div class="row">
            <div class="col-md-12" style="min-height: 50px;">
                <div class="card mb-3" :style="{'background-color' : couleur_fond}" v-show="chargement_en_cours === false">
                    <div class="card-body" :style="{'color': couleur_texte, 'font-size': '20px'}">
                        <span style="font-weight: bold;">@traduction('composant.indicateur_stock_actuel.stock_actuel')</span> :
                        <span style="float: right;">
                            @{{ stock_actuel }}
                            <span :class="{'fas' : true, 'fa-caret-down' : !detail_stock_actuel, 'fa-caret-up' : detail_stock_actuel}" style="cursor: pointer" @click="detail_stock_actuel = !detail_stock_actuel"></span>
                        </span>
                        <template v-if="detail_stock_actuel">
                            <div class="row" :style="{'color': couleur_texte_conditionnement(stock_actuel_conditionnement)}" style="margin: 0;justify-content: space-between;font-size: 15px;" v-for="stock_actuel_conditionnement in stock_actuel_par_conditionnement">
                                <span style="font-weight: bold;">@{{ stock_actuel_conditionnement.nom }} : </span><span style="float: right;">@{{ stock_actuel_conditionnement.stock_actuel }}</span>
                            </div>
                        </template>
                    </div>
                </div>
                <div v-show="chargement_en_cours === true" style="position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);">
                    <img style="width: 60px;" src="{{ 'eden/images/ajax_loader.gif' }}">
                </div>
            </div>
        </div>
        `,
        props:{
        },
        data: function(){

            return {

                stock_actuel: 0,
                seuil_alerte: 0,
                seuil_minimum: 0,
                chargement_en_cours: true,
                detail_stock_actuel: false,
                stock_actuel_par_conditionnement: {},

            }

        },

        methods:{

            actualiser_indicateur: function (){

                var vue_composant = this;

                $.get({
                    url: 'eden/fiche/' + vue_composant.$root.type_element + '/' + vue_composant.$root.element_id + '/recupere_indicateur_stock_actuel',
                }).done(function (retour){

                    vue_composant.stock_actuel = parseFloat(retour.stock_actuel);
                    vue_composant.seuil_alerte = parseFloat(retour.seuil_alerte);
                    vue_composant.seuil_minimum = parseFloat(retour.seuil_minimum);
                    vue_composant.chargement_en_cours = false;
                    vue_composant.stock_actuel_par_conditionnement = retour.stock_actuel_par_conditionnement;
                })

            },

            couleur_texte_conditionnement: function (conditionnement){

                var vue_composant = this;

                if(parseFloat(conditionnement.stock_actuel) < parseFloat(conditionnement.seuils.seuil_alerte))
                    return "red";
                else if(parseFloat(conditionnement.seuils.seuil_mini) > parseFloat(conditionnement.stock_actuel) && parseFloat(conditionnement.seuils.stock_actuel) > parseFloat(vue_composant.seuil_alerte))
                    return "orange";
                else
                    return "green";

            },

        },

        computed: {

            couleur_texte: function (){

                var vue_composant = this;

                if(parseFloat(vue_composant.stock_actuel) < parseFloat(vue_composant.seuil_alerte))
                    return "red";
                else if(parseFloat(vue_composant.seuil_minimum) > parseFloat(vue_composant.stock_actuel) && parseFloat(vue_composant.stock_actuel) > parseFloat(vue_composant.seuil_alerte))
                    return "orange";
                else
                    return "green";

            },

            couleur_fond: function (){

                var vue_composant = this;

                if(parseFloat(vue_composant.stock_actuel) < parseFloat(vue_composant.seuil_alerte))
                    return "#ffd2d2";
                else if(parseFloat(vue_composant.seuil_minimum) > parseFloat(vue_composant.stock_actuel) && parseFloat(vue_composant.stock_actuel) > parseFloat(vue_composant.seuil_alerte))
                    return "#ffe7ba";
                else
                    return "#b2d5b2";

            }

        },

        mounted: function(){

            this.actualiser_indicateur();

        },
    });
</script>