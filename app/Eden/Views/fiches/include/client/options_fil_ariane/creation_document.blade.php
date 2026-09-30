<span class="dropdown" title="{{ traduction('module_sur_fiche.fiche.client.creer_document') }}" data-toggle="tooltip">
    <i class="css_action_icon primaire fa fa-fw fa-plus-square" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
        <div class="dropdown-menu">
            @if(fonctionnalite('gescom_document')['devis_vente'])
                <a class='dropdown-item' href="{{ route('document.creer_avec_element', ['devis_vente', 'client_id', $management_element->modele->id]) }}"><span class="fa fa-plus"></span> {{ traduction('module_sur_fiche.fiche.client.nouveau_devis') }}</a>
            @endif
            @if(fonctionnalite('gescom_document')['facture_vente'])
                <a class='dropdown-item' href="{{ route('document.creer_avec_element', ['facture_vente','client_id', $management_element->modele->id]) }}"><span class="fa fa-plus"></span> {{ traduction('module_sur_fiche.fiche.client.nouvelle_facture') }}</a>
            @endif
            @if(fonctionnalite('gescom_document')['avoir_vente'])
                <a class="dropdown-item" href="{{ route('document.creer_avec_element', ['avoir_vente','client_id', $management_element->modele->id]) }}"><span class="fa fa-plus"></span> {{ traduction('module_sur_fiche.fiche.client.nouvel_avoir') }}</a>
            @endif
        </div>
    </span>