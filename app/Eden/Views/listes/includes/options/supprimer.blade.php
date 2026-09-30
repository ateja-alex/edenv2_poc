<span v-if="ligne.droits_suppression" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="fa fa-trash"
          data-toggle="tooltip"
          @click="supprimer_dans_liste(ligne.element.id)"
          :title="$root.traduction('interface.listes.supprimer')"
          :id_element="ligne.element.id">
    </span>
</span>