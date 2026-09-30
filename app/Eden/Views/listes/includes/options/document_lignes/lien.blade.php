<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <a :href="'/eden/document/'+type_element.replace('_lignes','')+'/'+ligne.element.document_id">
        <span class="fas fa-search js_liste_lien_element"
              data-toggle="tooltip"
              :title="$root.traduction('interface.listes.voir_document')" >
        </span>
    </a>
</span>