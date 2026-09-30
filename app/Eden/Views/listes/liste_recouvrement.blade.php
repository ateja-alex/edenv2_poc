@extends('eden::composants_vue.js.liste_libre')

@push('scripts')
    <script type="text/javascript">

        function eden_relance_pdf_en_masse(parametres, id_liste) {
            
            loading(true);

            $('#modal_relance_pdf_'+id_liste).hide();
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();

            var form = document.getElementById('formulaire_relance_pdf_en_masse_'+id_liste);
            var formData = new FormData(form);

            var data = {};
            for (var [key, value] of formData.entries()) { 

                data[key] = value;
            }
            
            // on va créer le / les PDF
            $.post({
                    
                url: '/eden/document/export_relance_pdf_en_masse',
                method:"post",
                dataType: "json",
                data: { parametres:parametres, form:data },
            }).done(function(retour) {
                
                window.open(retour.chemin);
                loading(false);
            });          
        }
    </script>
@endpush

@push('donnees_pour_vuejs_methods')

	ajouter_une_relance(id, type, type_element) {

        var parametres = {  id : id,  type : type, type_element : type_element };

        var context = this;

        $.post({
					
            url: "{{ route('recouvrement.ajoute_relance', [], false) }}",
            data: parametres
        })
        .done(function(donnees) {
            document.location.reload();      
        });

    },

    supprimer_une_relance(id) {

        var context = this;

        $.post({
                    
            url: "{{ route('recouvrement.supprimer_relance', [], false) }}",
            data: {
                'id': id,
            } 
        })
        .done(function(donnees) { 
            document.location.reload();
        });

    },

    /**
     * 
     * récupère les elements selectionnées
     * 
     */
    eden_relance_pdf_elements_selectionnes: function(id_liste) {
        
        // on va chercher les ID des éléments sélectionnés
        var ids = [];

        // on va chercher les ID des éléments sélectionnés
        var ids = this.liste.lignes_selectionnees;
        
        if(ids.length == 0)
            return;

        this.eden_relance_pdf_en_masse({ids_elements: ids, type_element: this.liste.type_element}, id_liste);
    },

        /**
     * 
     * On récupère tous les élements
     * 
     */
    eden_relance_pdf_tous_les_elements: async function(id_liste) {

        loading(true);
        var vue_instance =this;

        await vue_instance.actualisation_filtres(true);

        loading(false);

        vue_instance.eden_relance_pdf_en_masse({ids_elements: vue_instance.liste.ids, type_element: vue_instance.liste.type_element}, id_liste);

    },

    eden_relance_pdf_en_masse(parametres, id_liste) {

        loading(true);

        $('#modal_relance_pdf_'+id_liste).hide();
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();

        var form = document.getElementById('formulaire_relance_pdf_en_masse_'+id_liste);
        var formData = new FormData(form);

        var data = {};
        for (var [key, value] of formData.entries()) {

            data[key] = value;
        }

        // on va créer le / les PDF
        $.post({

            url: '/eden/document/export_relance_pdf_en_masse',
            method:"post",
            dataType: "json",
            data: { parametres:parametres, form:data },
        }).done(function(retour) {

            window.open(retour.chemin);
            loading(false);
        });
    },

    
@endpush

@include('eden::listes.includes.modal_relance_pdf')
