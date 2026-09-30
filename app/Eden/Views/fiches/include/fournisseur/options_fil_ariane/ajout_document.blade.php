<span class="dropdown" title="{{ traduction('module_sur_fiche.fiche.fournisseur.creer_document') }}" data-toggle="tooltip">
    <i class="css_action_icon primaire fa fa-fw fa-plus-square" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
    <div class="dropdown-menu">
        <a class='dropdown-item' href="{{ route('document.creer_avec_element', ['devis_achat', 'fournisseur_id', $management_element->modele->id]) }}"><span class="fa fa-plus"></span> @traduction('module_sur_fiche.fiche.fournisseur.nouveau_devis')</a>
        <a class='dropdown-item' href="{{ route('document.creer_avec_element', ['facture_achat', 'fournisseur_id', $management_element->modele->id]) }}"><span class="fa fa-plus"></span> @traduction('module_sur_fiche.fiche.fournisseur.nouvelle_facture')</a>
        <a class="dropdown-item" href="{{ route('document.creer_avec_element', ['avoir_achat', 'fournisseur_id', $management_element->modele->id]) }}"><span class="fa fa-plus"></span> @traduction('module_sur_fiche.fiche.fournisseur.nouvel_avoir')</a>
    </div>
</span>