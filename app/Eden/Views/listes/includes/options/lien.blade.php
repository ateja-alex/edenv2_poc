<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <a :href="'eden/fiche/'+type_element+'/'+ligne.element.id">
        <span class="fas fa-search js_liste_lien_element"
              data-toggle="tooltip"
              :title="$root.traduction('interface.listes.voir_la_fiche')"
              :id_element="ligne.element.id">
        </span>
    </a>
</span>