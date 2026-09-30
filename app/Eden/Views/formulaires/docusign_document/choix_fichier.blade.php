<div class="row">
    <div class="col-sm-2">
        {!! management('docusign_document')->champ('lien_fichier')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <div class="bloc_champ_formulaire formulaire_champ_lien_fichier">
            <select name="lien_fichier" v-model="docusign_document.lien_fichier">
                <optgroup v-for="fichiers in fichiers_disponibles" :label="fichiers.nom">
                    <option v-for="fichier in fichiers.valeurs" :value="fichier.chemin">@{{ fichier.nom }}</option>
                </optgroup>
                <option value="nouveau_fichier" v-html="$root.traduction('interface.docusign.nouveau_fichier')"></option>
            </select>
            <div class="champ_obligatoire">*</div>
        </div>
    </div>
    <div class="col-md-3" v-if="fichier_choisi !== null">
        <a target="_blank" :href="'{{ URL::to('/eden/bibliotheque/fichier/telecharger_fichier_element')}}?url=public/'+docusign_document.lien_fichier">
            <div class="champ_dropzone_bloc_image">
                <img :src="'storage/'+docusign_document.lien_fichier" v-if="fichier_choisi.image === true" />
                <div v-if="fichier_choisi.image !== true">
                    <span class="fa fa-file"></span>
                </div>
                @{{fichier_choisi.nom}}
            </div>
        </a>
        <input name="nom_fichier" type="hidden" :value="fichier_choisi.nom_fichier" />
    </div>
</div>

<div class="row">
    <div class="col-sm-2">
        {!! management('docusign_document')->champ('destination_document_signe')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <div class="bloc_champ_formulaire formulaire_champ_destination_document_signe">
            <select name="destination_document_signe" v-model="docusign_document.destination_document_signe" @change="gestion_remplacement">
                <optgroup v-for="destinations in destinations_possibles" :label="destinations.nom">
                    <option v-for="destination in destinations.valeurs" :value="destination.destination_document_signe">@{{ destination.nom }}</option>
                </optgroup>
            </select>
            <div class="champ_obligatoire">*</div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    fichiers_disponibles : {},
    destinations_possibles : {},
@endpush

@push('donnees_pour_vuejs_mounted')
    this.recuperer_informations_fichiers();
@endpush

@push('donnees_pour_vuejs_computed')
    fichier_choisi : function(lien_fichier){

        var lien_fichier = this.docusign_document.lien_fichier;

        if(!lien_fichier)
            return null;

        var fichier_choisi = null;

        for(type_fichier of Object.keys(this.fichiers_disponibles)){

            fichiers = this.fichiers_disponibles[type_fichier];

            for(fichier of fichiers.valeurs){

                if(fichier.chemin == lien_fichier){
                    fichier_choisi = fichier;
                    fichier_choisi.type_fichier = type_fichier;
                }
            }
        }

        return fichier_choisi;
    },

    destination_choisi : function(){

        var destination_document_signe = this.docusign_document.destination_document_signe;

        if(!destination_document_signe)
            return null;

        var destination_choisi = null;

        for(type_destination of Object.keys(this.destinations_possibles)){

            destinations = this.destinations_possibles[type_destination];

            for(destination of destinations.valeurs){

                if(destination.destination_document_signe == destination_document_signe){
                    destination_choisi = destination;
                    destination_choisi.type_destination = type_destination;
                }
            }
        }

        return destination_choisi;
    },

    remplacement_possible : function(){

        if(this.destination_choisi == null || this.fichier_choisi == null)
            return false;

        if(this.destination_choisi.destination_document_signe == "table.element_piece_jointe" && this.fichier_choisi.type_fichier == 'bloc_piece_jointe')
            return true;

        if(this.destination_choisi.type_destination != this.fichier_choisi.type_fichier)
            return false;

        return this.destination_choisi.type == 7 || this.fichier_choisi.champ == this.destination_choisi.destination_document_signe;
    },
@endpush

@push('donnees_pour_vuejs_methods')
    
    recuperer_informations_fichiers : function(){

        if(this.$root.cache_formulaires.docusign_document == undefined || this.$root.cache_formulaires.docusign_document.informations_fichiers == undefined){
            this.$set(this.$root.cache_formulaires,'docusign_document',{});
            this.$set(this.$root.cache_formulaires.docusign_document,'informations_fichiers',{});
        }

        var type_element = this.sous_formulaire ? this.docusign_enveloppe.type_element : this.$root.docusign_enveloppe.type_element;
        var id_element = this.sous_formulaire ? this.docusign_enveloppe.element_id : this.$root.docusign_enveloppe.element_id;

        if(this.$root.cache_formulaires.docusign_document.informations_fichiers[type_element+'/'+id_element] == undefined){

            $.ajax({
                url: '{{URL::to('/eden/docusign/informations_fichiers')}}/'+type_element+'/'+id_element,
                dataType:'json',
            }).done((donnees) => {

                this.fichiers_disponibles = donnees.fichiers_disponibles;
                this.destinations_possibles = donnees.destinations_possibles;

                this.$set(this.$root.cache_formulaires.docusign_document.informations_fichiers,type_element+'/'+id_element,donnees);
            });
        }
        else{
            informations_fichiers = this.$root.cache_formulaires.docusign_document.informations_fichiers[type_element+'/'+id_element];

            this.fichiers_disponibles = informations_fichiers.fichiers_disponibles;
            this.destinations_possibles = informations_fichiers.destinations_possibles;
        }
    },

    gestion_remplacement : function(valeur){

        var destination_choisi = this.destination_choisi;

        if(destination_choisi.type == 7)
            this.docusign_document.remplacement_fichier = 1;

    },
    
@endpush