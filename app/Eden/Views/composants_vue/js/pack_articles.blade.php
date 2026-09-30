const pack_articles = Vue.component('pack_articles', {
    template: ` <div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header d-flex align-items-center">
                                    <h4>@traduction('composant.pack_articles.pack')</h4>
                                    <div class="ml-auto">
                                        <span class="css_ajouter_element" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composant.pack_articles.nouveau')" @click="article_contenu_pack_creer">
                                            <i class="css_action_icon secondaire fa fa-fw fa-plus-square"></i>
                                        </span>
                                        
                                        <span class="css_ajouter_element" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composant.pack_articles.importer')">
                                            <i class="css_action_icon secondaire fa fa-fw fa-copy" @click="modale_copies_pack = true"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>@traduction('composant.pack_articles.id')</th>
                                                    <th>@traduction('composant.pack_articles.article')</th>
                                                    <th>@traduction('composant.pack_articles.quantite')</th>
                                                    <th>@traduction('composant.pack_articles.ordre')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-show="articles_contenu_pack.length == 0">
                                                    <td colspan="5">@traduction('composant.pack_articles.aucun_1') <span class="css__lien" @click="article_contenu_pack_creer">@traduction('composant.pack_articles.aucun_2')</span> @traduction('composant.pack_articles.aucun_3')</td>
                                                </tr>
                                                <tr v-for="article_contenu_pack in articles_contenu_pack">
                                                    <td>
                                                        <span class="btn btn-mini btn-xs btn-default css_btn_action_theme" style="padding: 5px; border: 1px solid white; background: white; color: #6f6f6f !important;">
                                                            <span class="fa fa-search"  @click="article_contenu_pack_modifier(article_contenu_pack)" data-toggle="tooltip" :title="$root.traduction('composant.pack_articles.afficher')"></span>
                                                        </span>
                                                    </td>
                                                    <td v-html="article_contenu_pack.article_enfant_id_affichage"></td>
                                                    <td>@{{ parseFloat(article_contenu_pack.quantite || 0) }}</td>
                                                    <td>@{{ article_contenu_pack.ordre }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>  

                    <div id="modales_pack_articles">
                        <!-- Modal copier article_contenu_pack -->
                        <template v-if="modale_copies_pack">
                            <transition name="modal">
                                <div class="modal-mask">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">@traduction('composant.pack_articles.titre_modal_import')</h5>
                                                <button type="button" class="close" @click="modale_copies_pack = false" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body" style="height: 100px;">
                                                <form action="#" id="formulaire_copier_article_contenu_pack" method="post" class="css_form">
                                                    <div class="row">
                                                        <div class="col-sm-2">@traduction('composant.pack_articles.article')</div>
                                                        <div class="col-sm-10">
                                                            <champ-selection-element :type_element="'article'" :nom_sql="'article_enfant_id'" name="article_enfant_id" :modele="article_a_copier_contenu_pack"></champ-selection-element>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" @click="modale_copies_pack = false">@traduction('composant.pack_articles.fermer')</button>
                                                <button type="button" class="btn btn-primary" @click="article_contenu_pack_copier">@traduction('composant.pack_articles.enregistrer')</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </template>

                        <!-- Modal ajout article_contenu_pack -->
                         <template v-if="modale_pack">
                            <transition name="modal">
                                <div class="modal-mask">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">@traduction('composant.pack_articles.titre_modal_ajout')</h5>
                                                <button type="button" class="close" @click="modale_pack =false" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body" style="height: 100px;">
                                                <formulaire ref="formulaire" nom_formulaire="article_contenu_pack" ></formulaire>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" @click="article_contenu_pack_supprimer" v-show="article_contenu_pack.id > 0">@traduction('composant.pack_articles.supprimer')</button>
                                                <button type="button" class="btn btn-primary" @click="article_contenu_pack_enregistrer">@traduction('composant.pack_articles.enregistrer')</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                         </template>
                    </div>

                </div>


                `,
                props:{

                    afficher_par_defaut:"",
                    element_standard:"",

                },
                data: function(){

                    return {

                        articles_contenu_pack: [],
                        article_contenu_pack: {},
                        article_a_copier_contenu_pack: {},
                        modale_copies_pack : false,
                        modale_pack : false,

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

                    article_contenu_pack_creer: function() {

                        var ordre = 1;
                        if(this.articles_contenu_pack.length > 0){
                            ordre = this.articles_contenu_pack.length +1;
                        }

                        this.$once('formulaire_charger',() => {
                            this.article_contenu_pack = this.$refs.formulaire.element;

                            this.article_contenu_pack.article_id = this.element_id;
                            this.article_contenu_pack.ordre = ordre;
                        });

                        this.modale_pack = true;
                    },
                        
                    
                    <!-- article_contenu_pack modif --> 
                    
                    article_contenu_pack_modifier: function(article_contenu_pack) {

                        this.article_contenu_pack = article_contenu_pack;

                        this.$once('formulaire_charger',() => {
                            this.$refs.formulaire.element = this.article_contenu_pack;
                        });

                        this.modale_pack = true;
                    },
                        
                    <!-- article_contenu_pack enregistrer -->       
                    article_contenu_pack_enregistrer: async function() {

                         var component = this;

                         // On afficher le loader
                         loading(true);

                         var donnees = await component.$refs.formulaire.enregistrer();

                         if(donnees.retour === true) {
                             this.modale_pack = false;
                             this.article_contenu_pack_actualiser();
                         }

                         loading(false);
                        
                    },
                    
                    <!-- article_contenu_pack supprimer-->  
                    
                    article_contenu_pack_supprimer: async function() {

                        vue_composant = this;

                        if(!await confirm_eden(vue_composant.$root.traduction('composant.pack_articles.confirmation_suppression')))
                            return false;

                        loading(true);


                        // on fait un appel ajax pour supprimer
                        $.ajax({
                            
                            url: "/eden/element/article_contenu_pack/" + vue_composant.article_contenu_pack.id + "/supprimer",
                            dataType: "json",
                        }).done(async (donnees) => {

                            if(donnees.retour !== true) {

                                loading(false);

                                await erreur(donnees.retour);
                                return;
                            }
                            
                            this.modale_pack = false;

                            // on actualise la liste
                            vue_composant.article_contenu_pack_actualiser();
                        });
                    },
                    
                    <!--article_contenu_pack actualiser-->  
                    
                    article_contenu_pack_actualiser: function() {
                        
                        loading(true);

                        vue_composant = this;
                        
                        $.get({

                            url: '/eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/articles_contenu_pack',
                            dataType: "json"
                        }).done(function(articles_contenu_pack) {
                            
                            // On retire le loader
                            loading(false);
                            
                            vue_composant.articles_contenu_pack = articles_contenu_pack;
                        });
                    },  


                    <!-- Copier le pack d'un autre article -->
                    article_contenu_pack_copier: function() {

                        loading(true);
                        
                        vue_composant = this;


                        $.post({
                            
                            url: '/eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/post/copier_article_contenu_pack',
                            dataType: "json",
                            data: {
                                id_article_copier : this.article_a_copier_contenu_pack.article_enfant_id
                            }
                        }).done(async (donnees) => {

                            if(donnees.retour !== true) {

                                loading(false);

                                await erreur(donnees.retour);
                                return;
                            }
                            
                            this.modale_copies_pack = false;

                            // on actualise la liste
                            vue_composant.article_contenu_pack_actualiser();
                        });




                    },
                    

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                },  
    
                mounted: function() {

                    $('#stack_modales_composants').append($('#modales_pack_articles'));

                    this.article_contenu_pack_actualiser();

                },

                watch: {
                    element_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.article_contenu_pack_actualiser();

                    }

                }
            });
