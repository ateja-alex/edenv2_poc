<!-- le statut de la ligne -->
<template v-if="document.valide == 1">

    @if($management->_type_element != 'commande_achat' && fonctionnalite('gescom_afficher_etat_reliquats_lignes_documents'))
        <!-- cas générique -->
        <span class="badge badge-default" v-if="article_sur_document.id && (article_sur_document.transforme == 0 || article_sur_document.transforme == undefined || article_sur_document.transforme == null)">@traduction('document.colonnes.designation.non_traite')</span>
        <span class="badge badge-warning" style="background-color:#f0ae41;" v-if="article_sur_document.id && (article_sur_document.transforme == 1)">@traduction('document.colonnes.designation.partiellement_traite') : @{{ Number.parseFloat(article_sur_document.transforme_reliquat).toFixed(2) }}</span>
        <span class="badge badge-success" v-if="article_sur_document.id && (article_sur_document.transforme == 2)">@traduction('document.colonnes.designation.traite')</span>
    @endif

    @if($management->_type_element == 'commande_vente' && fonctionnalite('gescom_document')['commande_achat'])
        <!-- cas particulier des commandes vente : commande fournisseur -->
        <span class="badge badge-default" v-if="article_sur_document.id && (article_sur_document.transforme_fournisseur == 0 || article_sur_document.transforme_fournisseur == undefined || article_sur_document.transforme_fournisseur == null)" title="Commande fournisseur réalisée ?" data-toggle="tooltip">@traduction('document.colonnes.designation.non_commande')</span>
        <span class="badge badge-warning" style="background-color:#f0ae41;" v-if="article_sur_document.id && (article_sur_document.transforme_fournisseur == 1)" :title="traduction('document.colonnes.designation.commande_fournisseur_realisee')" data-toggle="tooltip">@traduction('document.colonnes.designation.commande_reliquat') : @{{ Number.parseFloat(article_sur_document.transforme_reliquat_commande_fournisseur).toFixed(2) }}</span>
        <span class="badge badge-success" v-if="article_sur_document.id && (article_sur_document.transforme_fournisseur == 2)" :title="traduction('document.colonnes.designation.commande_fournisseur_realisee')" data-toggle="tooltip">@traduction('document.colonnes.designation.commande')</span>
        <span class="badge badge-success" v-if="article_sur_document.id && (article_sur_document.transforme_fournisseur == 3)" :title="traduction('document.colonnes.designation.commande_fournisseur_realisee')" data-toggle="tooltip">@traduction('document.colonnes.designation.pris_sur_stock')</span>

        <!-- cas particulier des commandes vente : réception -->
        <span class="badge badge-default" v-if="article_sur_document.id && (article_sur_document.transforme_livraison == 0 || article_sur_document.transforme_livraison == undefined || article_sur_document.transforme_livraison == null)" :title="traduction('document.colonnes.designation.commande_fournisseur_receptionnee')" data-toggle="tooltip">@traduction('document.colonnes.designation.non_traite')</span>
        <span class="badge badge-warning" style="background-color:#f0ae41;" v-if="article_sur_document.id && (article_sur_document.transforme_livraison == 1)" :title="traduction('document.colonnes.designation.commande_fournisseur_receptionnee')" data-toggle="tooltip">@traduction('document.colonnes.designation.partiellement_recu') : @{{ Number.parseFloat(article_sur_document.transforme_reliquat_reception_fournisseur).toFixed(2) }}</span>
        <span class="badge badge-success" v-if="article_sur_document.id && (article_sur_document.transforme_livraison == 2)" :title="traduction('document.colonnes.designation.commande_fournisseur_receptionnee')" data-toggle="tooltip">@traduction('document.colonnes.designation.recu')</span>
        <span class="badge badge-success" v-if="article_sur_document.id && (article_sur_document.transforme_livraison == 3)" :title="traduction('document.colonnes.designation.commande_fournisseur_receptionnee')" data-toggle="tooltip">@traduction('document.colonnes.designation.pris_sur_stock')</span>

    @elseif($management->_type_element == 'commande_achat')
        <!-- cas particulier des commandes achat : réception -->
        <span class="badge badge-default" v-if="article_sur_document.id && (article_sur_document.recue == 0 || article_sur_document.recue == undefined || article_sur_document.recue == null) && article_sur_document.quantite_recue <= 0" :title="traduction('document.colonnes.designation.commande_fournisseur_receptionnee')" data-toggle="tooltip">@traduction('document.colonnes.designation.non_recue') : @{{ Number.parseFloat(article_sur_document.reliquat_reception).toFixed(2) }}</span>
        <span class="badge badge-warning" style="background-color:#f0ae41;" v-if="article_sur_document.id && (article_sur_document.recue == 0 || article_sur_document.recue == undefined || article_sur_document.recue == null) && article_sur_document.quantite_recue > 0" :title="traduction('document.colonnes.designation.commande_fournisseur_receptionnee')" data-toggle="tooltip">@traduction('document.colonnes.designation.partiellement_recue') : @{{ Number.parseFloat(article_sur_document.reliquat_reception).toFixed(2) }}</span>

        <span class="badge badge-success" v-if="article_sur_document.id && (article_sur_document.recue == 1)" :title="traduction('document.colonnes.designation.commande_fournisseur_receptionnee')" data-toggle="tooltip">@traduction('document.colonnes.designation.recue')</span>

    @endif
</template>