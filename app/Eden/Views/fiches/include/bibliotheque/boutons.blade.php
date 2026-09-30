<div class="css_titre_liste">
    @if(empty(moi_extranet()))
        <span class="css_ajouter_element" data-toggle="tooltip" data-placement="left" :title="traduction('interface.bibliotheque.title.ajouter_fichier')" >
            <i class="css_action_icon fas fa-file-upload" id="bouton_fichier" onclick='$("#input_fichier").trigger("click");'></i>
            <input type="file" id="input_fichier" multiple style="display: none;" />
        </span>
    @endif
    @if(!empty(moi()))
        <span class="css_ajouter_element" data-toggle="tooltip" data-placement="left" :title="traduction('interface.bibliotheque.title.ajouter_dossier')"
              @click="modal_gestion_dossier({
                droit_entites : {
                    @foreach(modele('entite')->get()->pluck('id') as $id)
                        {{ $id }} : false,
                    @endforeach
                },
               })">
            <i class="css_action_icon fas fa-folder-plus"></i>
        </span>
    @endif
    @if(!empty(moi()))
        <span class="css_ajouter_element" v-if="!_.isEmpty(dossier_parent) && (!dossier_parent || !dossier_parent.gdrive || !dossier_parent.sharepoint)" data-toggle="tooltip" data-placement="left" :title="traduction('interface.bibliotheque.title.modifier_dossier')" @click="modal_gestion_dossier(dossier_parent)">
            <i class="css_action_icon fas fa-pen"></i>
        </span>
    @endif
    @if(!empty(moi()))
        <span class="css_ajouter_element" v-if="!_.isEmpty(dossier_parent)" data-toggle="tooltip" data-placement="left" :title="traduction('interface.bibliotheque.title.supprimer_dossier')" @click="supprimer_dossier(dossier_parent)">
            <i class="css_action_icon fas fa-trash"></i>
        </span>
    @endif
    <span class="css_ajouter_element" v-if="!_.isEmpty(dossier_parent)" data-toggle="tooltip" data-placement="left" :title="traduction('interface.bibliotheque.title.retour')" @click="dossier_parent.dossier_parent == null ? deplacement_dans_un_dossier(null) : deplacement_dans_un_dossier(dossier_parent.dossier_parent.id)">
        <i class="css_action_icon fas fa-arrow-left"></i>
    </span>
</div>