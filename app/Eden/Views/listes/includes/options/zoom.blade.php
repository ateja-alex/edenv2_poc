<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="fas fa-info-circle"
        @click="zoom_ligne(ligne.element.id)"
        data-toggle="tooltip"
        :title="$root.traduction('interface.listes.zoom')">
    </span>
</span>