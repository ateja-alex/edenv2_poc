<template v-if="modale_copie_composition">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">@traduction('composants.composition_des_articles.titre_modal_import')</h5>
                        <button type="button" class="close" @click="modale_copie_composition = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="height: 100px;">
                        <form action="#" id="formulaire_copier_composants_nomenclature" method="post" class="css_form">
                            <div class="row">
                                <div class="col-sm-2">@traduction('composants.composition_des_articles.modale_article')</div>
                                <div class="col-sm-10">
                                    <champ-selection-element :filtrage="[{'champ':'type_article','condition':'where','valeur':'1'},{'champ': 'id','condition':'where','symbole':'!=','valeur':$root.element_id}]" :type_element="'article'" :nom_sql="'article_enfant_id'" name="article_enfant_id" :modele="article_a_copier_composants" :type_element_origine="'article'"></champ-selection-element>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_copie_composition = false">@traduction('composants.composition_des_articles.fermer')</button>
                        <button type="button" class="btn btn-primary" @click="article_composants_copier">@traduction('composants.composition_des_articles.enregistrer')</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_data')

    article_a_copier_composants: {},
    modale_copie_composition: false,

@endpush

@push('donnees_pour_vuejs_methods')

    // Copier les composants d'un autre article
    article_composants_copier: function() {

        loading(true);

        vue_composant = this;

        $.post({

            url: '/eden/fiche/article/' + this.$root.element_id + '/post/copier_article_composants',
            dataType: "json",
            data: {
                id_article_copier : this.article_a_copier_composants.article_enfant_id
            }
        }).done(async (donnees) => {

            loading(false);

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            this.modale_copie_composition = false;

            // on actualise la liste
            this.actualisation_filtres();
        });

    },

@endpush