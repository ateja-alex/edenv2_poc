<a class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme"
   :href="'eden/element/'+type_element+'/'+ligne.element.id+'/afficher_pdf/'+$root.basename_url(ligne.element.pdf)"
   target="_blank">
    <span class="fa fa-file-pdf" data-toggle="tooltip" :title="$root.traduction('interface.listes.pdf')"></span>
</a>