{{-- Tarif NET --}}
<div class="cellule_document_colonne_article">
    @php
        $edition_tarif_net = fonctionnalite('gescom_prix_net_modifiable') ? $edition_ligne : 'false';
    @endphp

    <champ-montant v-if="{{ $edition_tarif_net }}"
            class_input="css_input_article_document"
            :modele="article_sur_document"
            nom_sql="tarif_net"
            :valeur_non_vide="true"
            :lecture_seule="article_sur_document.modele && [1,3].includes(article_sur_document.modele.type_article)">
    </champ-montant>
    <span v-else class="css_lecture_ligne css_prix_ligne_article_document_nomenclature">@{{article_sur_document.tarif_net}}</span>
</div>

@if(empty($recapitulatif))
    @push('donnees_pour_vuejs_mounted')

        this.$on('maj_champ_montant',(donnees) => {

            if(donnees.nom_sql != 'tarif_net')
                return;

            this.modification_prix_net(donnees.modele);
        });
    @endpush
@endif
