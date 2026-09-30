<span v-if="ligne.droits_suppression" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="fa fa-trash"
          data-toggle="tooltip" :title="$root.traduction('interface.listes.supprimer')"
          @click="suppression_element_depuis_liste(ligne.element.id,id_liste)"
          :id_element="ligne.element.id">
    </span>
</span>

@include('eden::fiches.include.options_fil_ariane.modale_suppression_element')