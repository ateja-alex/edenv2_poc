<span v-if="ligne.element.valide == 1 && ligne.element.annule != 1" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme" >
    <span class="fa fa-times js_liste_annulation_element"
          @click="annuler_dans_liste(ligne.element.id)"
          data-toggle="tooltip"
          :title="$root.traduction('interface.listes.annuler')" >
    </span>
</span>