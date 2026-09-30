<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="fa fa-envelope"
          data-toggle="tooltip"
          :title="$root.traduction('interface.listes.envoyer_par_mail')"
          @click="$root.$emit('envoie_email',{
                type_element: type_element,
                id_element: ligne.element.id,
                modele_email: liste.modele_liste_libre.modele_email_defaut ?? null,
                @if(in_array($type_element,\App\Eden\Variables::$documents_gescom ))
                    documents: [{type_element: type_element, id_element: ligne.element.id}]
                @endif
          });">
    </span>
</span>