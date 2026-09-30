const consommation_de_stock = Vue.component('consommation_de_stock', {
    template: `
    <div>
        <div class="card mb-3">
            <div class="card-header js_fermeture_bloc">

                <span v-if="afficher_par_defaut && afficher_par_defaut == true" style="float: right; margin-top: 3px; cursor: pointer;">
                    <span class="fa fa-chevron-up"></span>
                </span>

                <span v-else-if="afficher_par_defaut && afficher_par_defaut == false" style="float: right; margin-top: 3px; cursor: pointer;">
                    <span class="fa fa-chevron-down"></span>
                </span>

                <h4>
                    @traduction('composants.consommation_de_stocks.titre')
                </h4>
            </div>
            <div class="card-body" v-if="afficher_par_defaut === true">
                
                <div class="row">
                    <div class="col-sm-7">
                        <b>@traduction('composants.consommation_de_stocks.article')</b>
                    </div>
                    <div class="col-sm-1">
                        <b>@traduction('composants.consommation_de_stocks.quantite_vendue')</b>
                    </div>
                    <div class="col-sm-1">
                        <b>@traduction('composants.consommation_de_stocks.quantite_livree')</b>
                    </div>
                    <div class="col-sm-1">
                        <b>@traduction('composants.consommation_de_stocks.quantite_a_livrer')</b>
                    </div>
                    <div class="col-sm-1">
                        <b>@traduction('composants.consommation_de_stocks.stock_actuel')</b>
                    </div>
                    <div class="col-sm-1">
                        <b>@traduction('composants.consommation_de_stocks.stock_a_terme')</b>
                    </div>
                </div>

                <div class="row" v-for="stock in consommation_de_stock">
                    <div class="col-sm-7" v-html="stock.article"></div>
                    <div class="col-sm-1">@{{ stock.vendus }}</div>
                    <div class="col-sm-1">@{{ stock.livres }}</div>
                    <div class="col-sm-1">@{{ stock.reliquat }}</div>
                    <div class="col-sm-1">@{{ stock.stock_actuel }}</div>
                    <div class="col-sm-1">@{{ stock.stock_a_terme }}</div>
                </div>
                
            </div>
        </div>

    </div>`,
    	
	props: {

        afficher_par_defaut:"",
        element_standard:"",
		
	},

    data: function() {

        return {

            consommation_de_stock: {},

            @yield('donnees_pour_vuejs_data')
            @stack('donnees_pour_vuejs_data')

        }
        
    },

    computed:{

        @yield('donnees_pour_vuejs_computed')
        @stack('donnees_pour_vuejs_computed')

        element_id: function() {
            return this.$root.element_id;
        },

        type_element: function() {
            return this.$root.type_element;
        },

    },


    methods:{
        
        actualiser: function() {

            var vue_composant = this;
            
            loading(true);
            
            $.get({

                url: 'eden/fiche/'+vue_composant.type_element+'/' + vue_composant.element_id + '/consommation_de_stock',
                dataType: "json"
            }).done(function(consommation_de_stock) {
                // On retire le loader
                loading(false);
                
                vue_composant.consommation_de_stock = consommation_de_stock;
            });
        },

        @yield('donnees_pour_vuejs_methods')
        @stack('donnees_pour_vuejs_methods')
	
    },

    watch: {

        element_id: function(nouvelle_valeur, ancienne_valeur) {

            this.actualiser();
        }

    },

    mounted: function() {
		
		this.actualiser();
	},
});