@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'suppression_manuelle_reliquat'])


@push('donnees_pour_vuejs_methods')

    /**
    *
    * On supprime les lignes sélectionnées
    *
    */
    suppression_manuelle_reliquat_elements_selectionnes : function() {

        // on va chercher les ID des éléments sélectionnés
        var ids = [];

        var composant = this;

        // on va chercher les ID des éléments sélectionnés
        var ids = composant.liste.lignes_selectionnees;

        if(ids.length == 0)
            return;

        composant.eden_suppression_manuelle(ids);

    },

    /**
    *
    * On supprime tous les éléments de la liste
    *
    */
    suppression_manuelle_reliquat_tous_elements : function() {

        // on récupère les ids...
        loading(true);

        var composant = this;

        var type_element = composant.liste.type_element;

        // on récupère les ids...
        loading(true);

        // on va chercher la liste d'ids
        composant.actualisation_filtres(true);

        setTimeout(function() {

            composant.eden_suppression_manuelle(composant.liste.ids);

        }, 1000);

    },

    eden_suppression_manuelle: function(ids) {

		loading(true);

		$.post({
            url : '{{ route("document.achat.commande.suppression_manuelle_reliquat") }}',
            data : {
                ids_lignes : ids,
                suppression_manuelle_reliquat : 1,
            },
            dataType : 'json'
        }).done((retour) => {

            if(!retour.succes)
                alert(retour.message);
            else
                toastr.success(this.$root.traduction('document.actions.suppression_manuelle_reliquat.retour_etat_1'));

            loading(false);

            this.deselectionner_toutes_les_lignes();

			this.actualisation_filtres();

            this.modale_suppression_manuelle_reliquat = false;
        });
    },

@endpush
