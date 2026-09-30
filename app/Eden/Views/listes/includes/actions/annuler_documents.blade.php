@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'annuler_documents'])

@push('donnees_pour_vuejs_methods')

    /**
    *
    * On supprime les lignes sélectionnées
    *
    */
    annuler_documents_elements_selectionnes : function() {

        // on va chercher les ID des éléments sélectionnés
        var ids = [];

        var composant = this;

        // on va chercher les ID des éléments sélectionnés
        var ids = composant.liste.lignes_selectionnees;

        if(ids.length == 0)
            return;

        composant.eden_annuler_en_masse(ids, composant.liste.type_element);

    },

    /**
    *
    * On supprime tous les éléments de la liste
    *
    */
    annuler_documents_tous_elements : function() {

        // on récupère les ids...
        loading(true);

        var composant = this;

        var type_element = composant.liste.type_element;

        // on récupère les ids...
        loading(true);

        // on va chercher la liste d'ids
        composant.actualisation_filtres(true);

        setTimeout(function() {

            composant.eden_annuler_en_masse(composant.liste.ids, composant.liste.type_element);

        }, 1000);

    },

    eden_annuler_en_masse: function(ids, type_element) {

        var composant = this;
        loading(true);

        // on va chercher le template
        $.post({

            url: "{{ route('document.vente.commande.annuler_documents', [], false) }}",
            method:"post",
            dataType: "json",
            data: {
                ids:ids,
            },
        }).done(function(donnees) {

            // On retire le loader
            loading(false);

            composant.deselectionner_toutes_les_lignes();

            composant.actualisation_filtres();

            if(donnees.retour !== true) {

                erreur(donnees.message);
                return;
            }

            composant.modale_annuler_documents = false;

            info(composant.$root.traduction('interface.listes.documents_annules', null, [donnees.nombre_annules]));
        });
    },

@endpush
