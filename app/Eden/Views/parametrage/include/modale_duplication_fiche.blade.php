<span data-toggle="tooltip" title="" class="css_ajouter_element" data-original-title="Dupliquer" @click="modale_duplication_fiche = true">
    <i class="css_action_icon fas fa-copy css_font_16" aria-hidden="true"></i>
</span>

@push('modales')

    <template v-if="modale_duplication_fiche">
        <transition name="modal" >
            <div class="modal-mask">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Duplication de la fiche
                            </h5>
                            <button @click="modale_duplication_fiche = false" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" >
                            <div v-for="(informations,type_document) in documents">
                                <div style="padding: 10px;border-bottom: 1px solid lightgrey;display:flex;align-items:center">
                                    <h6 v-html="informations.nom"></h6>
                                    <div class="ml-auto">
                                        <span class="badge badge-default" v-if="!selectionne(type_document,true)" @click="tout_selectionner(type_document)">
                                            Tout séléctionner
                                        </span>
                                        <span class="badge badge-default" v-if="!selectionne(type_document,false)" @click="tout_selectionner(type_document,false)">
                                            Tout déséléctionner
                                        </span>
                                    </div>
                                </div>
                                <div style="display: flex;padding: 20px;gap: 30px;width: min-content;">
                                    <span v-for="element in informations.elements" style="display: flex;align-items: center;gap: 0 10px;">
                                        <input class="css_pointer" :id="'duplication_'+element" @change="$forceUpdate()" type="checkbox" v-model="documents_a_modifier[element]">
                                        <label class="css_pointer" :for="'duplication_'+element" v-html="traduction('tables_libres.'+element+'.nom_table') +' ('+element+')'"></label>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button @click="modale_duplication_fiche = false" type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.modales.fermer')</button>
                            <button @click="dupliquer" type="button" class="btn btn-primary" data-dismiss="modal">@traduction('interface.modales.dupliquer')</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')
    modale_duplication_fiche : false,
    documents_a_modifier : {},
    documents : {
        vente : {
            nom : "Documents de vente",
            elements : {!! collect(array_values(array_filter(\App\Eden\Variables::$documents_vente_gescom,function($value) use ($type_element) {return $value != $type_element;}))) !!},
        },
        achat : {
            nom : "Documents d'achat",
            elements : {!! collect(array_values(array_filter(\App\Eden\Variables::$documents_achat_gescom,function($value) use ($type_element) {return $value != $type_element;}))) !!},
        }
    },
@endpush

@push('donnees_pour_vuejs_methods')

    tout_selectionner : function(type_document,nouvelle_valeur = true){

        var documents_a_modifier = this.documents_a_modifier;

        this.documents[type_document].elements.forEach(function(element){

            documents_a_modifier[element] = nouvelle_valeur;
        });

        this.$forceUpdate();
    },

    selectionne : function(type_document,valeur){

        var documents_a_modifier = this.documents_a_modifier;

        var selectionne = true;

        this.documents[type_document].elements.forEach(function(element){

            if(documents_a_modifier[element] != valeur)
                selectionne = false;
        });

        return selectionne;
    },

    dupliquer : async function(){

        if(!await confirm_eden('Êtes vous sur ? Les anciennes structures de fiche seront perdues !'))
            return false;

        loading(true);

        var vue_instance = this;

        var documents_a_modifier = this.documents_a_modifier;

        var extranet = window.location.pathname == "/eden/extranet/parametrage/fiche/{!! $type_element !!}";

        var url = "{{ route('parametrage.fiche.dupliquer', [$type_element]) }}";

        if(extranet)
            url += '/true';

        $.post({

			url: url,
			dataType: "json",
			data: vue_instance.documents_a_modifier,
		}).done(function(donnees) {
            loading(false);

            info('Duplication effectué avec succès !');

            vue_instance.modale_duplication_fiche = false;

            $.each(this.documents,function(type_document,informations){
                informations.elements.forEach(function(element){
                    documents_a_modifier[element] = false;
                });
            });
		});
    },
@endpush

@push('donnees_pour_vuejs_created')

    var documents_a_modifier = this.documents_a_modifier;

    $.each(this.documents,function(type_document,informations){
        informations.elements.forEach(function(element){
            documents_a_modifier[element] = false;
        });
    });
@endpush