<template v-if="modale_correspondance">
    <transition name="modal">
        <div class="modal-mask gestion_correspondances">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('interface.formulaires.synchronisation_service_champs.correspondances')</h5>
                        <button type="button" class="close" @click="modale_correspondance = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body css_form">

                        <div class="row bloc_titre">
                            <div class="col-sm-5 titre">
                                <span>@{{ champ_externe.nom }}</span>
                            </div>
                            <div class="col-sm-2 titre">
                                <span><i class="fas fa-long-arrow-alt-right"></i></span>
                            </div>
                            <div class="col-sm-5 titre">
                                <span>@{{ synchronisation_service_champs.nom_sql }}</span>
                            </div>
                        </div>
                        <div class="row mb-5" v-for="correspondance in correspondances">
                            <div class="col-sm-5 titre">
                                <span>@{{ correspondance.valeur_externe.nom }} ( Valeur : @{{correspondance.valeur_externe.valeur}} )</span>
                            </div>
                            <div class="col-sm-2 titre">
                                <span><i class="fas fa-long-arrow-alt-right"></i></span>
                            </div>
                            <div class="col-sm-5 titre">
                                <champ-liste-formatee v-if="champ_libre_selectionne.type == 20"
                                    :modele="correspondance"
                                    :nom_sql="champ_libre_selectionne.nom_sql"
                                    :type_element="champ_libre_selectionne.type_element"
                                    :liste_choix="champ_libre_selectionne.liste_choix"
                                ></champ-liste-formatee>
                                <champ-liste-libre v-else-if="champ_libre_selectionne.type == 1"
                                    :modele="correspondance"
                                    :nom_sql="champ_libre_selectionne.nom_sql"
                                    :type_element="champ_libre_selectionne.type_element"
                                    :id_cl="champ_libre_selectionne.id_cl"
                                ></champ-liste-libre>
                                <champ-selection-element v-else-if="champ_libre_selectionne.type == 42"
                                    :modele="correspondance"
                                    :nom_sql="champ_libre_selectionne.nom_sql"
                                    :desactiver_creation_a_la_volee="true"
                                    :type_element="champ_libre_selectionne.type_element_ajax"
                                    :type_element_origine="champ_libre_selectionne.type_element"
                                ></champ-selection-element>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_correspondance = false">@traduction('interface.modales.fermer')</button>
                        <button type="button" class="btn btn-primary" @click="enregistrer_correspondances">@traduction('interface.modales.enregistrer')</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_data')
    champs_externes : [],
    synchronisation_service_element: null,
    champs_libres : [],
    modale_correspondance: false,
    correspondances: [],
    table_externe : null,
@endpush

@push('donnees_pour_vuejs_mounted')
    this.$root.$on('selection-element',(parametres) => {
        if(parametres.nom_sql == "synchronisation_service_element_id"){
            this.synchronisation_service_element = parametres.element;
            this.chargement_champs_externes();
            this.chargement_champs_libres();
        }
    });

    if(this.synchronisation_service_champs.synchronisation_service_element_id != null)
        this.synchronisation_service_element = await $.ajax({
            url : "/eden/element/synchronisation_service_element/" + this.synchronisation_service_champs.synchronisation_service_element_id,
            dataType:'json'
        });
    else if(this.sous_formulaire)
        this.synchronisation_service_element = this.$parent.synchronisation_service_element;

    this.chargement_champs_externes();
    this.chargement_champs_libres();
@endpush

@push('donnees_pour_vuejs_computed')

    champ_externe (){
        if(this.synchronisation_service_champs.nom_externe == null)
            return null;

        var nom_externe_partie = this.synchronisation_service_champs.nom_externe.split('.');

        var champ_externe = null;
        var champs_externes = this.champs_externes;

        if(champs_externes.length == 0)
            return null;

        for(index_nom_externe in nom_externe_partie){

            if(index_nom_externe == nom_externe_partie.length - 1)
                break;

            champs_externes = champs_externes.find(champ => champ.nom == nom_externe_partie[index_nom_externe]).valeurs ?? [];
        }

        champ_externe = champs_externes.find(champ => champ.nom == nom_externe_partie[nom_externe_partie.length - 1]);

        return champ_externe;
    },
@endpush


@push('donnees_pour_vuejs_methods')
    chargement_champs_externes : async function(){

        if(this.synchronisation_service_element == null)
            return;

        this.champs_externes = await $.post({
            url: 'eden/synchronisation_service/champs_externes/'+this.synchronisation_service_element.id,
            dataType:'json'
        });

        this.table_externe = await $.post({
            url: 'eden/synchronisation_service/table_externe/'+this.synchronisation_service_element.id+'/'+this.synchronisation_service_element.type_externe,
            dataType:'json'
        });
    },

    chargement_champs_libres : async function(){

        var type_element_champs = (this.synchronisation_service_element.type_synchronisation == 3 && this.synchronisation_service_champs.sens == 1)
            ? this.synchronisation_service_element.type_element_destination
            : this.synchronisation_service_element.type_element;

        if(type_element_champs == null)
            return;

        this.champs_libres = await $.ajax({
            url: 'eden/champs/valeurs/'+type_element_champs,
            dataType:'json'
        });
    },

    gestion_correspondances : function(){

        var correspondances = [];

        for(valeur of this.champ_externe.valeurs){

            var correspondance = {
                valeur_externe : {
                    nom : valeur.nom,
                    valeur : valeur.valeur,
                }
            };

            var valeur_interne = null;

            if(this.synchronisation_service_champs.correspondances != null && this.synchronisation_service_champs.correspondances != '')
                valeur_interne = JSON.parse(this.synchronisation_service_champs.correspondances).find(correspondance => correspondance.valeur_externe == valeur.valeur)?.valeur_interne ?? null;

            correspondance[this.champ_libre_selectionne.nom_sql] = valeur_interne;

            correspondances.push(correspondance);
        }

        this.correspondances = correspondances;

        this.modale_correspondance = true;
    },
    enregistrer_correspondances : function(){

        var correspondances = this.correspondances.map(correspondance => {
            return {
                valeur_externe : correspondance.valeur_externe.valeur,
                valeur_interne : correspondance[this.champ_libre_selectionne.nom_sql]
            }
        });

        this.$set(this.synchronisation_service_champs,'correspondances',JSON.stringify(correspondances));

        this.modale_correspondance = false;
    },

@endpush