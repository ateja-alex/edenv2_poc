<span v-if="seulement_inactif" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="fas fa-undo js_liste_retablir"
          data-toggle="modal"
          @click="retablir_dans_liste(ligne.element.id)"
          :title="$root.traduction('interface.listes.retablir')"
          :id_element="ligne.element.id">
    </span>
</span>