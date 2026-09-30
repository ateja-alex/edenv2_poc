const gestion_images = Vue.component('gestion-images', {
    template: ` <div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h4>@traduction('composant.gestion_images.titre')</h4>
                                </div>
                                <div class="card-body">

                                    <div class="row" id="fiche_liste_images" style="background: rgb(241, 241, 239); padding-top: 5px; padding-bottom: 0px; margin-bottom: 30px;" v-if="images.length > 0">
                                        <div class="col-sm-4 fiche_liste_images" v-for="image in images" style="text-align: center;  padding-bottom: 10px;" id_image="{{ '' /*$image->id*/ }}">
                                            <div class="css_fiche_suppression_image_conteneur">
                                                <a class="css_fiche_suppression_image" href="javascript:;" @click="supprimer(image)">@traduction('composant.gestion_images.supprimer')</a>
                                                <img :src="image.url_sur_serveur" style="max-width: 100%; max-height: 200px;" /><br/>
                                                @traduction('composant.gestion_images.dimensions') : <b>@{{ image.width }} px * @{{ image.height }} px</b><br/>
                                                @traduction('composant.gestion_images.poids') : <b>@{{ image.poids }}</b><br/>
                                                @traduction('composant.gestion_images.extension') : <b>@{{ image.extension }}</b><br/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="css_form">
                                        <div class="row">
                                            <div class="col-sm-12 css_form_ligne_titre">@traduction('composant.gestion_images.ajouter')</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">@traduction('composant.gestion_images.image')</div>
                                            <div class="col-sm-6"><input type="file" name="image" id="image_upload_gestion_images" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">@traduction('composant.gestion_images.titre_image')</div>
                                            <div class="col-sm-6"><input type="text" name="titre" v-model="titre" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">@traduction('composant.gestion_images.balise_alt')</div>
                                            <div class="col-sm-6"><input type="text" name="alt" v-model="alt" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">@traduction('composant.gestion_images.legende')</div>
                                            <div class="col-sm-6"><input type="text" name="legende" v-model="legende" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">@traduction('composant.gestion_images.description')</div>
                                            <div class="col-sm-6"><input type="text" name="description" v-model="description" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6"></div>
                                            <div class="col-sm-6">
                                                <button class="btn btn-primary" @click="ajouter">
                                                    @traduction('composant.gestion_images.enregistrer')
                                                </button>
                                            </div>
                                        </div>
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

                        images:{},
                        titre:"",
                        alt:"",
                        legende:"",
                        description:"",
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
                        
                    // Charge le contenu de l'élément
                    actualiser: function() {

                        var vue_composant = this;
                        
                        loading(true);
                        
                        $.get({

                            url: 'eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/images',
                            dataType: "json",
                        }).done(function(images) {
                            
                            // On retire le loader
                            loading(false);
                            
                            vue_composant.images = images;
                        });
                    },
                    

                    ajouter: function() {
                        
                        loading(true);

                        var vue_composant = this;

                        var donnees = new FormData();
                        var fichier = $('#image_upload_gestion_images')[0].files;
						
						donnees.append('titre', vue_composant.titre);
						donnees.append('alt', vue_composant.alt);
						donnees.append('legende', vue_composant.legende);
						donnees.append('description', vue_composant.description);
						
						// Check file selected or not
                        if(fichier.length > 0 ){

                            donnees.append('image',fichier[0]);

                            $.ajax({

                                url: '/eden/fiche/'+vue_composant.type_element+'/'+vue_composant.element_id+'/post/ajoute_image_ajax',
                                type: 'post',
                                data: donnees,
                                contentType: false,
                                processData: false,

                                success: function(retour){

                                    //console.log(retour)

                                    if(retour.succes){
                                        vue_composant.actualiser();
                                    } else {
                                        toastr.error(vue_composant.$root.traduction('composant.gestion_images.fichier_non_uploade'));
                                    }
                                },
                            });
                        }else{
                            toastr.error(vue_composant.$root.traduction('composant.gestion_images.aucun_fichier_selectionne'))
                        }
                    },

                    // Charge le contenu de l'élément
                    supprimer: function(image) {
                        
                        loading(true);

                        var vue_composant = this;
                        
                        $.get({

                            url: 'eden/fiche/' + vue_composant.type_element + '/'+vue_composant.element_id+'/supprimer_image_ajax/' + image.id,
                            dataType: "json",
                        }).done(function(images) {
                            
                            // On retire le loader
                            loading(false);

                            vue_composant.actualiser();

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