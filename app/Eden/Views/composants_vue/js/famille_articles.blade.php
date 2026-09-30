const famille_articles = Vue.component('famille_articles', {
    template: ` <div>
			    	<div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header d-flex align-items-center">
                                    <h4>@traduction('composant.famille_articles.titre')</h4>
                                </div>
                                <div class="card-body">

                                    {{-- Affichage des articles de la famille dans un tableau --}}

                                    @if(fonctionnalite('fiche_famille_articles_avec_photo') == false)
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <th>@traduction('composant.famille_articles.id')</th>
                                            <th>@traduction('composant.famille_articles.code')</th>
                                            <th>@traduction('composant.famille_articles.designation')</th>
                                            <th>@traduction('composant.famille_articles.tarif')</th>
                                        </thead>
                                        <tbody class="js_sortable_articles">
                                            <tr v-for="(article, index) in articles" class="js_item_sort" :article-id="article.id">
                                                <td><a :href="'/eden/fiche/article/'+article.id+'/afficher'">@{{ article.id }}</a></td>
                                                <td>@{{ article.code_article }}</td>
                                                <td>@{{ article.designation }}</td>
                                                <td>@{{ article.tarif }} {!! maquette('devise_application_symbole') !!}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    {{-- Affichage des articles de la famille avec photo --}}
                                    @else
                                    <div class="row js_sortable_articles css_sortable_articles_fiche_famille" >
                                        <div class="col-md-3 mb-3 js_item_sort" v-for="(article, index) in articles" :article-id="article.id">
                                            <div class="css_block_article_sort_fiche_famille">
                                                <img :src="'/site/images/produits'+article.image1.replace('logos', '')" alt="" class="img-fluid">
                                                <a :href="'/eden/fiche/article/'+article.id+'/afficher'">@{{ article.designation }}</a>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

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

                    	articles:{},

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

                	tri(ordre_articles) {

                        var vue_composant = this;

						$.ajax({

							type: 'POST',
							url: 'eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/post/tri_ordre_article',
							data : { ordre_articles : ordre_articles },
							success: function (result) {

								vue_composant.actualiser();
							}
						});
					},
                        
                    // Charge le contenu de l'élément
                    actualiser: function() {

                        var vue_composant = this;
                        
                        loading(true);
                        
                        $.get({

                            url: 'eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/articles_famille',
                            dataType: "json"
                        }).done(function(articles_par_familles) {

                            //console.log(articles_par_familles)
                            
                            // On retire le loader
                            loading(false);
                            
                            vue_composant.articles = articles_par_familles;
                        });
                    },
                    

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                },  
    
                mounted: function() {

                    var vue_composant = this;

                    this.actualiser();

                    $('.js_sortable_articles').sortable({

			            items: '.js_item_sort',

						stop: function(event, ui) {

							var ordre_articles = [];

							$('.js_item_sort').each(function() {
								
								ordre_articles.push($(this).attr('article-id'));
							});

							vue_composant.tri(ordre_articles);
						}
			        });

                },

                watch: {
                    element_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.actualiser();

                    }
                }
            });
