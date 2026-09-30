<span class="js_options_sur_document d-flex align-items-center" @if(fonctionnalite('gescom_preselection_articles_sur_document') === true && $management->existe() === false) style="display: none!important;" @endif>

    @if(($management->existe() === false || ($management->existe() === true && $management->modele->valide != 1)) || $articles_modifiables === true)

        @foreach($options_lignes_divers as $nom_option_ligne_divers => $option_ligne_divers)

            @if(isset($option_ligne_divers['non_visible']) && $option_ligne_divers['non_visible'] == true)
                @continue
            @endif

            @if($option_ligne_divers['format'] == 'bouton_titre')
                <span class="css_bouton_custom_document_vente" @click="document_ajout_ligne_divers('{{$nom_option_ligne_divers}}', {{$haut_liste ?? 'false'}}@if(!empty($ajout_ligne)) , article_sur_document.index_article @endif);"><span>@traduction('document.blocs.saisie_des_articles.actions.{{$nom_option_ligne_divers}}')</span></span>
            @endif
        @endforeach

        @if(fonctionnalite('option_mettre_a_jour_nomenclatures_et_tarifs') && !isset($ajout_ligne))
            @if($management->_type_element == "devis_vente" || $management->_type_element == "commande_vente")
                <span class="css_bouton_custom_document_vente" @click="document_mise_a_jour();"><span>@traduction('document.blocs.saisie_des_articles.mettre_a_jour_article')</span></span>
            @endif
        @endif

        @if(fonctionnalite('gescom_document_bouton_rafraichir_article_tarifs') && !isset($ajout_ligne))
            @if($management->_type_element == "devis_vente" || $management->_type_element == "commande_vente")
                <span class="css_bouton_custom_document_vente" @click="document_mise_a_jour(true);"><span>@traduction('document.blocs.saisie_des_articles.mettre_a_jour') @traduction('document.blocs.saisie_des_articles.uniquement_tarifs')</span></span>
            @endif
        @endif

        @foreach($options_lignes_divers as $nom_option_ligne_divers => $option_ligne_divers)

            @if(isset($option_ligne_divers['non_visible']) && $option_ligne_divers['non_visible'] == true)
                @continue
            @endif

            @if($option_ligne_divers['format'] == 'bouton_icone' && (!isset($ajout_ligne) || $nom_option_ligne_divers != 'enregistre_lignes'))
                <span class="css_bouton_custom_document_vente" title="" data-toggle="tooltip" data-position="top" :data-original-title="traduction('document.blocs.saisie_des_articles.aide.{{$nom_option_ligne_divers}}')" style="background-color:var(--background_menus);border-color:var(--background_menus);color:white;" @click="document_ajout_ligne_divers('{{$nom_option_ligne_divers}}', {{$haut_liste ?? 'false'}}@if(!empty($ajout_ligne)) , article_sur_document.index_article @endif);"><i class="{{$option_ligne_divers['icone']}}"></i></span>
            @elseif($option_ligne_divers['format'] == 'fichier')
                @include('eden::formulaires.include.document.includes.options.'.$nom_option_ligne_divers)
            @endif
        @endforeach

        @include('eden::formulaires.include.document_options_sur_selections_articles')

        @if(!isset($ajout_ligne))
            <span class="css_tooltip" style="font-size:18px;cursor:pointer">
                <i class="fa fa-question"></i>
                <div class="css_top">
                    @foreach($options_lignes_divers as $nom_option_ligne_divers => $option_ligne_divers)

                        @if(isset($option_ligne_divers['non_visible']) && $option_ligne_divers['non_visible'] == true)
                            @continue
                        @endif

                        <p style="font-size:12px;"><span style="font-weight:bold">@traduction('document.blocs.saisie_des_articles.actions.{{$nom_option_ligne_divers}}') : </span> @traduction('document.blocs.saisie_des_articles.aide.{{$nom_option_ligne_divers}}') </p>
                    @endforeach
                </div>
            </span>
        @endif
    @endif
</span>
