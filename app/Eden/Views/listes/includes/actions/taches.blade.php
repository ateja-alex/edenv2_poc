@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'taches'])

@section('texte_modale_taches')
    <formulaire ref="formulaire_tache" nom_formulaire="formulaire_masses_tache"></formulaire>
    <br/><br/>
@endsection

@push('donnees_pour_vuejs_methods')

    /**
    *
    * On crée les tâches sur les lignes sélectionnées
    *
    */
    taches_elements_selectionnes: function() {

        // on va chercher les ID des éléments sélectionnés
        var ids = [];

        var composant = this;

        // on va chercher les ID des éléments sélectionnés
        var ids = composant.liste.lignes_selectionnees;

        if(ids.length == 0)
            return;

        composant.eden_taches_en_masse(ids);
    },

    /**
    *
    * On envoie un mail à tous les éléments de la liste
    *
    */
    taches_tous_elements: async function() {

        // on récupère les ids...
        loading(true);

        var composant = this;

        // on récupère les ids...
        loading(true);

        // on va chercher la liste d'ids
        await composant.actualisation_filtres(true);

        composant.eden_taches_en_masse(composant.liste.ids);

    },

    eden_taches_en_masse: function(ids) {

        var composant = this;
        loading(true);

        var data = {};

        for(donnee of this.$refs.formulaire_tache.formulaire_donnees_renseignes()){

            data[donnee.name] = donnee.value;
        }

        // on va chercher le template
        $.post({
            url: 'eden/elements/taches_en_masse',
            method:"post",
            dataType: "json",
            data: { parametres:{
                ids_elements : ids,
                type_element : this.liste.type_element
            }, form:data },
        }).always((retour) => {

            if ( retour.success == true ) {

                this.modale_taches = false;

                composant.deselectionner_toutes_les_lignes();

                composant.actualisation_filtres();

                toastr.success(retour.lignes_modifiees+' tâche'+(retour.lignes_modifiees > 1 ? 's' : '')+' créée'+(retour.lignes_modifiees > 1 ? 's' : ''));

            } else {

                retour.message = retour.message.replace('<br>', '\n')

                alert("Des erreurs sont survenues !\n\nLignes modifiées : "+retour.lignes_modifiees+"\n"+retour.message);
                }

                // on cache le loader
                loading(false);
        });
    },


@endpush