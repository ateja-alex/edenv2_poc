<a v-if="ligne.element.facture_scannee != null" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme"
   :href="'/storage/'+ligne.element.facture_scannee" target="_blank">
    <span class="fa fa-file-pdf" data-toggle="tooltip"
          :title="$root.traduction('interface.listes.pdf')">
    </span>
</a>