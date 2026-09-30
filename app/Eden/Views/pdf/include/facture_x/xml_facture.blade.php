<!-- XML Format Factur-X (CrossIndustryInvoice) - profile: EN 16931 (Comfort) -->
<rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100"
                           xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100"
                           xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"
                           xmlns:qdt="urn:un:unece:uncefact:data:standard:QualifiedDataType:100">
  <rsm:ExchangedDocumentContext>
    <!-- BT-23 : type de facturation (CIUS-FR) -->
    <ram:BusinessProcessSpecifiedDocumentContextParameter>
      <ram:ID>{{ $cadre_facturation_facturx }}</ram:ID>
    </ram:BusinessProcessSpecifiedDocumentContextParameter>
    <ram:GuidelineSpecifiedDocumentContextParameter>
      <ram:ID>urn:cen.eu:en16931:2017</ram:ID>
    </ram:GuidelineSpecifiedDocumentContextParameter>
  </rsm:ExchangedDocumentContext>

  <!-- Document Header -->
  <rsm:ExchangedDocument>
    <ram:ID>{{ $document->reference_document }}</ram:ID>
    <ram:TypeCode>{{ $type_code_facturx }}</ram:TypeCode>
    <ram:IssueDateTime>
      <udt:DateTimeString format="102">{{ date('Ymd', strtotime($document->date)) }}</udt:DateTimeString>
    </ram:IssueDateTime>
    @if(!empty($document->objet))
    <ram:IncludedNote>
      <ram:Content>{{ $document->objet }}</ram:Content>
    </ram:IncludedNote>
    @endif
    <!-- BR-FR-05 : mentions légales obligatoires (Art. L441-10 et L441-6 du Code de commerce) -->
    <ram:IncludedNote>
      <ram:Content>{{ $mention_indemnite_forfaitaire_facturx }}</ram:Content>
      <ram:SubjectCode>PMT</ram:SubjectCode>
    </ram:IncludedNote>
    <ram:IncludedNote>
      <ram:Content>{{ $mention_penalites_retard_facturx }}</ram:Content>
      <ram:SubjectCode>PMD</ram:SubjectCode>
    </ram:IncludedNote>
    <ram:IncludedNote>
      <ram:Content>{{ $mention_escompte_facturx }}</ram:Content>
      <ram:SubjectCode>AAB</ram:SubjectCode>
    </ram:IncludedNote>
    <!-- BR-FR-20 : nature du traitement attendu, à indiquer explicitement pour que
         l'ensemble des contrôles schématron soit appliqué (sinon B2B par défaut, sans contrôles complets) -->
    <ram:IncludedNote>
      <ram:Content>B2B</ram:Content>
      <ram:SubjectCode>BAR</ram:SubjectCode>
    </ram:IncludedNote>
  </rsm:ExchangedDocument>

  <!-- Supply Chain Trade Transaction -->
  <rsm:SupplyChainTradeTransaction>

    @foreach($articles as $article)
    <ram:IncludedSupplyChainTradeLineItem>
      <ram:AssociatedDocumentLineDocument>
        <ram:LineID>{{ $article->ligne }}</ram:LineID>
      </ram:AssociatedDocumentLineDocument>
      <ram:SpecifiedTradeProduct>
        <ram:Name>{{ $article->designation }}</ram:Name>
      </ram:SpecifiedTradeProduct>
      <ram:SpecifiedLineTradeAgreement>
        <ram:NetPriceProductTradePrice>
          <ram:ChargeAmount>{{ number_format($article->tarif, 2, '.', '') }}</ram:ChargeAmount>
        </ram:NetPriceProductTradePrice>
      </ram:SpecifiedLineTradeAgreement>
      <ram:SpecifiedLineTradeDelivery>
        <ram:BilledQuantity unitCode="C62">{{ number_format($article->quantite, 4, '.', '') }}</ram:BilledQuantity>
      </ram:SpecifiedLineTradeDelivery>
      <ram:SpecifiedLineTradeSettlement>
        <ram:ApplicableTradeTax>
          <ram:TypeCode>VAT</ram:TypeCode>
          @if(!empty($article->exemption_facturx))
          <ram:ExemptionReason>{{ $article->exemption_facturx['texte'] }}</ram:ExemptionReason>
          @endif
          <ram:CategoryCode>{{ $article->categorie_tva_facturx }}</ram:CategoryCode>
          @if(!empty($article->exemption_facturx) && !empty($article->exemption_facturx['code']))
          <ram:ExemptionReasonCode>{{ $article->exemption_facturx['code'] }}</ram:ExemptionReasonCode>
          @endif
          <ram:RateApplicablePercent>{{ number_format($article->tva, 2, '.', '') }}</ram:RateApplicablePercent>
        </ram:ApplicableTradeTax>
        <ram:SpecifiedTradeSettlementLineMonetarySummation>
          <ram:LineTotalAmount>{{ number_format($article->total, 2, '.', '') }}</ram:LineTotalAmount>
        </ram:SpecifiedTradeSettlementLineMonetarySummation>
      </ram:SpecifiedLineTradeSettlement>
    </ram:IncludedSupplyChainTradeLineItem>
    @endforeach

    <ram:ApplicableHeaderTradeAgreement>
      <!-- Seller (Vendeur / notre entité) -->
      <ram:SellerTradeParty>
        @if(!empty($entite_management->modele->siret))
        <!-- émetteur identifié par Esalink via SIREN + qualifiant 0225 -->
        <ram:GlobalID schemeID="0225">{{ substr(str_replace(' ','',$entite_management->modele->siret), 0, 9) }}</ram:GlobalID>
        @endif
        <ram:Name>{{ $entite_management->champ('nom')->affiche() }}</ram:Name>
        @if(!empty($entite_management->modele->siret))
        <!-- bloc obligatoire (confirmé par Esalink) : SIREN du vendeur -->
        <ram:SpecifiedLegalOrganization>
          <ram:ID schemeID="0002">{{ substr(str_replace(' ','',$entite_management->modele->siret), 0, 9) }}</ram:ID>
        </ram:SpecifiedLegalOrganization>
        @endif
        <ram:PostalTradeAddress>
          <ram:PostcodeCode>{{ $entite_management->champ('code_postal')->affiche() }}</ram:PostcodeCode>
          <ram:LineOne>{{ $entite_management->champ('adresse')->affiche() }}</ram:LineOne>
          <ram:CityName>{{ $entite_management->champ('ville')->affiche() }}</ram:CityName>
          <ram:CountryID>FR</ram:CountryID>
        </ram:PostalTradeAddress>
        @if(!empty($entite_management->modele->identifiant_adressage))
        <ram:URIUniversalCommunication>
          <ram:URIID schemeID="0225">{{ $entite_management->modele->identifiant_adressage }}</ram:URIID>
        </ram:URIUniversalCommunication>
        @endif
        @if(!empty($entite_management->modele->numero_tva))
        <ram:SpecifiedTaxRegistration>
          <ram:ID schemeID="VA">{{ $entite_management->modele->numero_tva }}</ram:ID>
        </ram:SpecifiedTaxRegistration>
        @endif
      </ram:SellerTradeParty>

      <ram:BuyerTradeParty>
        <ram:Name>{{ $annuaire_facturation->nom ?? $client_management->modele->raison_sociale ?? $client_management->modele->nom }}</ram:Name>
        @php($siren_acheteur = $annuaire_facturation->siren ?? $client_management->modele->siren)
        @if(!empty($siren_acheteur))
        <ram:SpecifiedLegalOrganization>
          <ram:ID schemeID="0002">{{ $siren_acheteur }}</ram:ID>
        </ram:SpecifiedLegalOrganization>
        @endif
        @if(!empty($adresse_facturation))
        <ram:PostalTradeAddress>
          <ram:PostcodeCode>{{ $adresse_facturation->code_postal }}</ram:PostcodeCode>
          <ram:LineOne>{{ $adresse_facturation->adresse }}</ram:LineOne>
          <ram:CityName>{{ $adresse_facturation->ville }}</ram:CityName>
          <ram:CountryID>FR</ram:CountryID>
        </ram:PostalTradeAddress>
        @endif
        @if(!empty($annuaire_facturation->adressage_id))
        <ram:URIUniversalCommunication>
          <ram:URIID schemeID="0225">{{ $annuaire_facturation->adressage_id }}</ram:URIID>
        </ram:URIUniversalCommunication>
        @endif
      </ram:BuyerTradeParty>
      @if(!empty($document->numero_commande_client))
      <ram:BuyerOrderReferencedDocument>
        <ram:IssuerAssignedID>{{ $document->numero_commande_client }}</ram:IssuerAssignedID>
      </ram:BuyerOrderReferencedDocument>
      @endif
    </ram:ApplicableHeaderTradeAgreement>

    <ram:ApplicableHeaderTradeDelivery></ram:ApplicableHeaderTradeDelivery>

    <ram:ApplicableHeaderTradeSettlement>
      <ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>
      @foreach($tableau_tva as $informations)
      <ram:ApplicableTradeTax>
        <ram:CalculatedAmount>{{ number_format($informations['tva'], 2, '.', '') }}</ram:CalculatedAmount>
        <ram:TypeCode>VAT</ram:TypeCode>
        @if(!empty($informations['exemption']))
        <ram:ExemptionReason>{{ $informations['exemption']['texte'] }}</ram:ExemptionReason>
        @endif
        <ram:BasisAmount>{{ number_format($informations['ht'], 2, '.', '') }}</ram:BasisAmount>
        <ram:CategoryCode>{{ $informations['categorie'] }}</ram:CategoryCode>
        @if(!empty($informations['exemption']) && !empty($informations['exemption']['code']))
        <ram:ExemptionReasonCode>{{ $informations['exemption']['code'] }}</ram:ExemptionReasonCode>
        @endif
        <ram:RateApplicablePercent>{{ number_format($informations['taux'], 2, '.', '') }}</ram:RateApplicablePercent>
      </ram:ApplicableTradeTax>
      @endforeach
      <!-- BT-9 : échéance de paiement, requise dès lors que le montant dû est positif (BR-CO-25) -->
      <ram:SpecifiedTradePaymentTerms>
        <ram:DueDateDateTime>
          <udt:DateTimeString format="102">{{ date('Ymd', strtotime($document->date_de_reglement ?: $document->date)) }}</udt:DateTimeString>
        </ram:DueDateDateTime>
      </ram:SpecifiedTradePaymentTerms>
      <ram:SpecifiedTradeSettlementHeaderMonetarySummation>
        <ram:LineTotalAmount>{{ number_format($total_ht, 2, '.', '') }}</ram:LineTotalAmount>
        <ram:ChargeTotalAmount>0.00</ram:ChargeTotalAmount>
        <ram:AllowanceTotalAmount>0.00</ram:AllowanceTotalAmount>
        <ram:TaxBasisTotalAmount>{{ number_format($total_ht, 2, '.', '') }}</ram:TaxBasisTotalAmount>
        <ram:TaxTotalAmount currencyID="EUR">{{ number_format($total_tva, 2, '.', '') }}</ram:TaxTotalAmount>
        <ram:GrandTotalAmount>{{ number_format($total_ttc, 2, '.', '') }}</ram:GrandTotalAmount>
        <ram:TotalPrepaidAmount>0.00</ram:TotalPrepaidAmount>
        <ram:DuePayableAmount>{{ number_format($total_ttc, 2, '.', '') }}</ram:DuePayableAmount>
      </ram:SpecifiedTradeSettlementHeaderMonetarySummation>
      @if(!empty($facture_anterieure))
      <!-- BG-3 : référence à la facture antérieure, obligatoire pour les avoirs (BR-FR-CO-05) -->
      <ram:InvoiceReferencedDocument>
        <ram:IssuerAssignedID>{{ $facture_anterieure->reference_document }}</ram:IssuerAssignedID>
        <ram:FormattedIssueDateTime>
          <qdt:DateTimeString format="102">{{ date('Ymd', strtotime($facture_anterieure->date)) }}</qdt:DateTimeString>
        </ram:FormattedIssueDateTime>
      </ram:InvoiceReferencedDocument>
      @endif
    </ram:ApplicableHeaderTradeSettlement>

  </rsm:SupplyChainTradeTransaction>
</rsm:CrossIndustryInvoice>
