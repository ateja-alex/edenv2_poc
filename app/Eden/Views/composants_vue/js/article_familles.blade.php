const article_familles = Vue.component('article_familles', {
    template: ` <div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header d-flex align-items-center">
                                    <h4>@traduction('composant.article_familles.titre')</h4>
                                    <span class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composant.article_familles.nouveau')" @click="article_famille_creer">
                                        <i class="css_action_icon secondaire fa fa-fw fa-plus-square"></i>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>@traduction('composant.article_familles.ref')</th>
                                                    <th>@traduction('composant.article_familles.famille')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-show="articles_par_familles.length == 0">
                                                    <td colspan="5">@traduction('composant.article_familles.aucun_1') <span class="css__lien" @click="article_famille_creer">@traduction('composant.article_familles.aucun_2')</span> @traduction('composant.article_familles.aucun_3')</td>
                                                </tr>
                                                <tr v-for="article_par_famille in articles_par_familles">
                                                    <td>
                                                        <span class="btn btn-mini btn-xs btn-default" style="padding: 5px; border: 1px solid white; background: white; color: #6f6f6f !important;">
                                                            <span class="fa fa-search"  @click="article_par_famille_modifier(article_par_famille)" data-toggle="tooltip" :title="$root.traduction('composant.article_familles.afficher')"></span>
                                                        </span>
                                                    </td>
                                                    <td>@{{ article_par_famille.famille.nom }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div id="modales_familles_articles">
                        <div class="modal fade" id="modal_ajout_article_par_famille" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">@traduction('composant.article_familles.titre_modal')</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <form action="#" id="formulaire_ajout_article_famille" method="post" class="css_form">
                                            <input type="hidden" name="id" v-model="article_famille.id" />
                                            <input type="hidden" name="article_id" v-model="article_famille.article_id" />
                                            <div class="row">
                                                <div class="col-sm-2">@traduction('composant.article_familles.famille')</div>
                                                <div class="col-sm-4" style="height: 100px;">
                                                {!! management('article_famille')->champ('famille_id')->cree() !!}
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" @click="article_famille_supprimer" v-show="article_famille.id != ''">@traduction('composant.article_familles.supprimer')</button>
                                        <button type="button" class="btn btn-primary" @click="article_famille_enregistrer">@traduction('composant.article_familles.enregistrer')</button>
                                    </div>
                                </div>
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

                        articles_par_familles:[],
                        article_famille : {id: '', article_id : this.element_id },

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

                    article_famille_creer: function() {

                        $('#modal_ajout_article_par_famille').modal('show');

                        this.article_famille = {id: '', famille_id : '', article_id : this.element_id}

                    },

                    article_par_famille_modifier: function(article_famille) {

                        this.article_famille = article_famille;

                        $('#modal_ajout_article_par_famille').modal('show');
                    },

                    article_famille_enregistrer: function() {

                        var vue_composant = this;

                        // On affiche le loader
                        loading();

                        // on enregistre les infos du champ libre
                        $.post({

                            url: 'eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/post/enregistrer_article_par_famille',
                            dataType: "json",
                            method: 'POST',
                            data: $('#formulaire_ajout_article_famille').serialize()
                        }).done(async function(donnees) {

                            // On retire le loader
                            loading(false);

                            if(donnees.retour !== true) {

                                await erreur(donnees.retour);
                                return;
                            }

                            $('#modal_ajout_article_par_famille').modal('hide');

                            // on actualise la liste
                            vue_composant.actualiser();
                        });

                    },

                    article_famille_supprimer: async function() {

                        var vue_composant = this;

                        if(!await confirm_eden(vue_composant.$root.traduction('composant.article_familles.confirmation_suppression')))
                            return false;

                        loading(true);


                        // on fait un appel ajax pour supprimer
                        $.post({

                            url: 'eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/post/supprimer_article_par_famille',
                            dataType: "json",
                            data: {

                                id: vue_composant.$data.article_famille.id
                            }
                        }).done(async function(donnees) {

                            if(donnees.retour !== true) {

                                loading(false);

                                await erreur(donnees.retour);
                                return;
                            }

                            $('#modal_ajout_article_par_famille').modal('hide');

                            // on actualise la liste
                            vue_composant.actualiser();
                        });
                    },

                    // Charge le contenu de l'élément
                    actualiser: function() {

                        //console.log('actualiser famille_article');

                        var vue_composant = this;

                        loading(true);

                        $.get({

                            url: 'eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/articles_par_familles',
                            dataType: "json"
                        }).done(function(articles_par_familles) {

                            //console.log(articles_par_familles)

                            // On retire le loader
                            loading(false);

                            vue_composant.articles_par_familles = articles_par_familles;
                        });
                    },


                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                },

                mounted: function() {

                    $('#stack_modales_composants').append($('#modales_familles_articles'));

                    this.actualiser();

                },

                watch: {
                    element_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.actualiser();

                    }

                }
});
