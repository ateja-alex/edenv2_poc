const client_coupons_reduction = Vue.component('client_coupons_reduction', {
    template: ` <div>

                    <div class="card mb-3">
                        <div class="card-header js_fermeture_bloc">

                            <span v-if="afficher_par_defaut && afficher_par_defaut == true" class="css_toggle_card_panel">
                                <span class="fa fa-chevron-up"></span>
                            </span>

                            <span v-else-if="afficher_par_defaut && afficher_par_defaut == false" class="css_toggle_card_panel">
                                <span class="fa fa-chevron-down"></span>
                            </span>

                            <h4>
                                @traduction('composant.client_coupons_reduction.titre')
                            </h4>
                        </div>
                        <div class="card-body" v-if="afficher_par_defaut === true">
                            
                            <div class="row">
                                <div class="col-sm-3">
                                    <b>@traduction('composant.client_coupons_reduction.code')</b>
                                </div>
                                <div class="col-sm-3">
                                    <b>@traduction('composant.client_coupons_reduction.montant')</b>
                                </div>
                                <div class="col-sm-3">
                                    <b>@traduction('composant.client_coupons_reduction.afficher')</b>
                                </div>
                                <div class="col-sm-3">
                                    <b>@traduction('composant.client_coupons_reduction.mail')</b>
                                </div>
                            </div>
                            <div class="row" v-for="(coupon_reduction) in coupons_reductions">
                                <div class="col-sm-3">@{{ coupon_reduction.code }}</div>
                                <div class="col-sm-3">@{{ $options.filters.montant(coupon_reduction.valeur) }}</div>
                                <div class="col-sm-3"><a :href="'eden/coupon_reduction/'+coupon_reduction.id+'/afficher_pdf'" target="_blank">@traduction('composant.client_coupons_reduction.afficher')</a></div>
                                <div class="col-sm-3"><i class="fa fa-envelope" style="cursor:pointer;" @click="envoyer_email_coupon_reduction(coupon_reduction.id)"></i></div>

                            </div>
                        
                        </div>
                    </div>

                </div>

                `,
                props:{

                    afficher_par_defaut:"",
                    element_standard:"",

                },
                data: function(){

                    return {

                        coupons_reductions:{},

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

                filters: {

                    montant: function (nombre) {

                        if (nombre === null || nombre == undefined || nombre == '')
                            return '0,00';

                        return parseFloat(nombre).toFixed(2).replace('.', ',');
                    },

                },

                methods:{

                    envoyer_email_coupon_reduction : function(id) {

                        var ids = [];
                        var ids_documents = [];
                        ids.push(id);
                        ids_documents.push({type_element: 'coupon_reduction', id_element : id});

                        this.$root.$emit('envoie_email',{ids_elements: ids, type_element: 'coupon_reduction', documents: ids_documents});
                    },
    
                    // Charge le contenu de l'élément
                    actualiser: function() {

                        var vue_composant = this;
                        
                        loading(true);
                        
                        $.get({

                            url: "/eden/fiche/client/"+this.element_id+"/recupere_coupons_reduction",
                            dataType: "json"
                        }).done(function(coupons_reductions) {

                            //console.log(coupons_reductions)
                            
                            // On retire le loader
                            loading(false);
                            vue_composant.coupons_reductions = coupons_reductions;
                        });
                    },                    

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                },  
    
                mounted: function() {

                    this.actualiser();

                },

                watch: {
                    element_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.actualiser();

                    }
                }
            });