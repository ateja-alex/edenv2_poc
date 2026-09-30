<span  v-if="liste.droits_liste.profil_creation" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="far fa-copy"
          @click="dupliquer_dans_liste(ligne.element)"
          data-toggle="tooltip"
          :title="$root.traduction('interface.listes.dupliquer')"
          :id_element="ligne.element.id">
    </span>
</span>