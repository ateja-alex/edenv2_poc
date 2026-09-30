<span class="css_ajouter_element ml-auto mr-3" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composant.gestion_pieces_jointes.telecharger_tout')" @click="telecharger_tout()">
    <i class="css_action_icon fas fa-download"></i>
</span>

<span class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composant.gestion_pieces_jointes.ajouter_fichier')" >
    <i class="css_action_icon fas fa-file-upload" id="bouton_fichier" onclick='$("#piece_jointe").trigger("click");'></i>
    <form id="form_upload_fichier_simple" enctype='multipart/form-data'>
        <input type="file" id="piece_jointe" name="piece_jointe" multiple style="display: none;" @change="ajout_fichier_simple()" />
    </form>
</span>

<span class="css_ajouter_element mr-3"
      data-toggle="tooltip" data-placement="left"
      :title="$root.traduction('composant.gestion_pieces_jointes.ajouter_dossier')"
      v-if="!synchronisation_mfiles && (admin || infos_profil.creation)"
      @click="modal_gestion_dossier({
        droit_entites : {
            @foreach(modele('entite')->get()->pluck('id') as $id)
                {{ $id }} : false,
            @endforeach
        },
       })">
    <i class="css_action_icon fas fa-folder-plus"></i>
</span>

<span class="css_ajouter_element mr-3" v-if="!synchronisation_mfiles && (admin || infos_profil.modification) && !_.isEmpty(dossier_parent) && (!dossier_parent || !dossier_parent.gdrive)" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composant.gestion_pieces_jointes.modifier_dossier')" @click="modal_gestion_dossier(dossier_parent)">
    <i class="css_action_icon fas fa-pen"></i>
</span>

<span class="css_ajouter_element mr-3" v-if="!synchronisation_mfiles && (admin || infos_profil.suppression) && !_.isEmpty(dossier_parent)" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composant.gestion_pieces_jointes.supprimer_dossier')" @click="supprimer_dossier(dossier_parent)">
    <i class="css_action_icon fas fa-trash"></i>
</span>
    
<span class="css_ajouter_element" v-if="!synchronisation_mfiles && !_.isEmpty(dossier_parent)" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composant.gestion_pieces_jointes.retour')" @click="dossier_parent.dossier_parent == null ? deplacement_dans_un_dossier(null) : deplacement_dans_un_dossier(dossier_parent.dossier_parent.id)">
    <i class="css_action_icon fas fa-arrow-left"></i>
</span>

@push('donnees_pour_vuejs_methods')

    async ajout_fichier_simple() {

        var vue_composant = this;

        vue_composant.chargement_en_cours = true;

        let fichiers = $('#piece_jointe')[0].files;
        let retours_mfiles = '';

        for(fichier of Object.values(fichiers)){

            formData = new FormData();
            formData.append('piece_jointe', fichier);
            formData.append('dossier_parent', JSON.stringify(vue_composant.dossier_parent));

            await $.ajax({

                // Your server script to process the upload
                url: "/eden/fiche/"+vue_composant.type_element+"/"+vue_composant.element_id+"/post/ajoute_piece_jointe",
                type: 'POST',

                // Form data
                data: formData,

                // Tell jQuery not to process data or worry about content-type
                // You *must* include these options!
                cache: false,
                contentType: false,
                processData: false,

            }).done(await function(donnees) {

                if(donnees.succes === false && donnees.mfiles === true)
                    retours_mfiles += donnees.message + '<br>';
                else if(donnees.retour === 'mfiles')
                    toastr.success(vue_composant.$root.traduction('messages.js.mfiles.fichier_cree'));
                else
                    toastr.success(vue_composant.$root.traduction('messages.js.element_piece_jointe.fichier_cree'));
            });
        }


        if(retours_mfiles !== ''){

            vue_composant.message_erreur_ajout_mfiles = retours_mfiles;
            vue_composant.modale_erreur_ajout_mfiles = true;
            return false;
        }

        vue_composant.deplacement_dans_un_dossier(vue_composant.dossier_actuel);
    },

    telecharger_tout: function(){

        var vue_instance = this;
        loading(true);

        $.ajax({

            url: '/eden/fiche/' + vue_instance.type_element + '/' + vue_instance.element_id + '/telecharger_toutes_pieces_jointes/' + (vue_instance.dossier_actuel != null ? vue_instance.dossier_actuel : ''),
        }).done(function(donnees) {

            loading(false);

            if(donnees.retour === true)
                window.location = donnees.chemin;
            else
                toastr.error(donnees.message);
        });
    },
@endpush
