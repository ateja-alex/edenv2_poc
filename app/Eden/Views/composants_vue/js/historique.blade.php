<script>
const historique = Vue.component('historique', {
    template: `<div class="composant_historique">
                    <div>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4>@traduction('composant.historique.titre')</h4>
                            </div>
                            <div class="card-body">
                                <div class="css_conteneur_historique_documents">
                                    <div v-for="(element_historique,index) in historique" class="css_block_historique row">
                                        <div class="titre_element_bloc_historique">
                                            <div class="image_et_titre">
                                                <div class="css_img_utilisateur_block_historique">
                                                    <img v-if="element_historique.avatar && element_historique.avatar.length > 0" :src="'/storage/' + element_historique.avatar" alt="" class="mt-1 mb-1">
                                                    <img v-else src="eden/images/no_avatar.jpg" alt="" class="mt-1 mb-1">

                                                </div>
                                                <div class="css_description_historique">
                                                    <span class="css_header_description_historique">
                                                        <i :class="'fa mr-1 ' + element_historique.classe"></i>
                                                        @{{ $root.traduction(element_historique.intitule) }} - @{{ element_historique.date }}
                                                    </span>
                                                    <span v-if="element_historique.details" v-for="(cle, detail) in JSON.parse(element_historique.details)">(@{{ detail }} - @{{ cle }})</span>
                                                    <br>
                                                    <span v-html="element_historique.nom"></span>
                                                    <br>
                                                    <span v-if="element_historique.description">@{{ element_historique.description }}</span>

                                                </div>
                                            </div>

                                            <span v-if="Array.isArray(element_historique.details_lignes) && element_historique.details_lignes.length > 0"
                                                class="badge badge-secondary"
                                                onclick="$(this).parent().next().toggle('fast');$(this).children().toggleClass('fa-chevron-down').toggleClass('fa-chevron-up');">
                                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                            </span>
                                        </div>

                                        <div class="contenu">
                                            <template v-for="(detail_historique, index_detail) in element_historique.details_lignes">
                                                <strong v-html="$root.traduction('champs_libres.'+type_element+'.'+detail_historique.champ+'.nom') + ' : '"></strong>
                                                <template v-if="detail_historique.valeur_avant_txt != '' && detail_historique.valeur_avant_txt != null">
                                                    <iframe style="width:90%" v-if="(champs_libres.find(c => c.nom_sql == detail_historique.champ)?.type ?? 0) == 6" :scrdoc="detail_historique.valeur_avant_txt"></iframe>
                                                    <span v-else v-html="detail_historique.valeur_avant_txt"></span>
                                                    =>
                                                </template>
                                                <iframe style="width:90%;" v-if="(champs_libres.find(c => c.nom_sql == detail_historique.champ)?.type ?? 0) == 6" :srcdoc="detail_historique.valeur_apres_txt"></iframe>
                                                <span v-else v-html="detail_historique.valeur_apres_txt"></span>
                                                <br>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`,
                props:{
                    type_element : null,
                    element_id : null,
                },
                data: function(){

                    return {

                        historique:[],
                        champs_libres: [],
                    }

                },
                methods:{

                    charge_donnees: function(){

                        var vue_composant = this;
                        
                        loading(true);
                        
                        $.get({

                            url: 'eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/recuperer_historique',
                            dataType: "json"

                        }).done(function(historique) {
                            
                            // On retire le loader
                            loading(false);
                            
                            vue_composant.historique = historique;

                        });

                    },

                } ,
	
                mounted: function() {

                    $.ajax({
                        url : 'eden/champs/valeurs/'+this.type_element,
                        dataType : 'json'
                    }).done((champs_libres) => {
                        this.champs_libres = champs_libres;
                    });
                    
                    this.charge_donnees();
                },
            });
</script>
