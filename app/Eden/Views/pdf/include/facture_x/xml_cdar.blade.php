<!-- XML Format CDAR (Cross Domain Acknowledgement and Response) - statuts de cycle de vie
     Norme AFNOR XP Z12-012 chapitre 5 : structure la plus complète qu'on peut établir sans XSD réel
     (pas de XSD CDAR bundlé comme pour Factur-X) - à corriger selon les erreurs InvoiceLCRuleError /
     InvoiceLCStatusError réellement renvoyées par Esalink en sandbox PPD. -->
<rsm:CrossDomainAcknowledgementAndResponse xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossDomainAcknowledgementAndResponse:100"
                           xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100"
                           xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"
                           xmlns:qdt="urn:un:unece:uncefact:data:standard:QualifiedDataType:100">

  <rsm:ExchangedDocumentContext>
    <ram:BusinessProcessSpecifiedDocumentContextParameter>
      <ram:ID>REGULATED</ram:ID>
    </ram:BusinessProcessSpecifiedDocumentContextParameter>
    <ram:GuidelineSpecifiedDocumentContextParameter>
      <ram:ID>urn.cpro.gouv.fr:1p0:CDV:invoice</ram:ID>
    </ram:GuidelineSpecifiedDocumentContextParameter>
  </rsm:ExchangedDocumentContext>

  <rsm:ExchangedDocument>
    <ram:ID>{{ $message_id }}</ram:ID>
    <ram:Name>Statut de cycle de vie</ram:Name>
    <ram:IssueDateTime>
      <udt:DateTimeString format="204">{{ date('YmdHis') }}</udt:DateTimeString>
    </ram:IssueDateTime>
    <ram:LanguageID>fr</ram:LanguageID>
    <ram:SenderTradeParty>
      <ram:GlobalID schemeID="0002">{{ $siren_emetteur_cdar }}</ram:GlobalID>
      <ram:RoleCode>{{ $role_emetteur_cdar }}</ram:RoleCode>
      <ram:URIUniversalCommunication>
        <ram:URIID schemeID="0225">{{ $siren_emetteur_cdar }}</ram:URIID>
      </ram:URIUniversalCommunication>
    </ram:SenderTradeParty>
    <ram:IssuerTradeParty>
      <ram:GlobalID schemeID="0002">{{ $siren_emetteur_cdar }}</ram:GlobalID>
      <ram:RoleCode>{{ $role_emetteur_cdar }}</ram:RoleCode>
      <ram:URIUniversalCommunication>
        <ram:URIID schemeID="0225">{{ $siren_emetteur_cdar }}</ram:URIID>
      </ram:URIUniversalCommunication>
    </ram:IssuerTradeParty>
    <ram:RecipientTradeParty>
      <ram:GlobalID schemeID="0002">{{ $siren_destinataire_cdar }}</ram:GlobalID>
      <ram:RoleCode>{{ $role_destinataire_cdar }}</ram:RoleCode>
      <ram:URIUniversalCommunication>
        <ram:URIID schemeID="0225">{{ $siren_destinataire_cdar }}</ram:URIID>
      </ram:URIUniversalCommunication>
    </ram:RecipientTradeParty>
  </rsm:ExchangedDocument>

  <rsm:AcknowledgementDocument>
    <ram:MultipleReferencesIndicator>
      <udt:Indicator>false</udt:Indicator>
    </ram:MultipleReferencesIndicator>
    <!-- 23 = phase de Traitement (statut posé par une des parties, pas par une Plateforme Agréée) -->
    <ram:TypeCode>23</ram:TypeCode>
    <ram:IssueDateTime>
      <udt:DateTimeString format="102">{{ date('Ymd', strtotime($date_evenement)) }}</udt:DateTimeString>
    </ram:IssueDateTime>
    <ram:ReferenceReferencedDocument>
      <ram:IssuerAssignedID>{{ $reference_document }}</ram:IssuerAssignedID>
      @if(!empty($type_code_facturx))
      <ram:TypeCode>{{ $type_code_facturx }}</ram:TypeCode>
      @endif
      @if(!empty($date_document))
      <ram:FormattedIssueDateTime>
        <qdt:DateTimeString format="102">{{ date('Ymd', strtotime($date_document)) }}</qdt:DateTimeString>
      </ram:FormattedIssueDateTime>
      @endif
      <ram:ProcessConditionCode>{{ $statut_code }}</ram:ProcessConditionCode>
      <ram:ProcessCondition>{{ $statut_label }}</ram:ProcessCondition>
      <ram:IssuerTradeParty>
        <ram:GlobalID schemeID="0002">{{ $siren_vendeur_facture }}</ram:GlobalID>
        <ram:RoleCode>SE</ram:RoleCode>
      </ram:IssuerTradeParty>
      @if(!empty($motif_code) || !empty($motif) || $statut_code == 212)
      <ram:SpecifiedDocumentStatus>
        @if(!empty($motif_code))
        <ram:ReasonCode>{{ $motif_code }}</ram:ReasonCode>
        @endif
        @if(!empty($motif))
        <ram:Reason>{{ $motif }}</ram:Reason>
        @endif
        <ram:ProcessConditionCode>{{ $statut_code }}</ram:ProcessConditionCode>
        <ram:SequenceNumeric>1</ram:SequenceNumeric>
        @if($statut_code == 212)
        <!-- BR-FR-CDV-14 : un bloc MEN par taux de TVA, la ventilation étant nécessaire pour
             déterminer la TVA devenue exigible à l'encaissement -->
        @foreach($encaissements_par_taux as $encaissement)
        <ram:SpecifiedDocumentCharacteristic>
          <ram:TypeCode>MEN</ram:TypeCode>
          <ram:ValueAmount>{{ number_format($encaissement['montant'], 2, '.', '') }}</ram:ValueAmount>
          <ram:ValuePercent>{{ number_format($encaissement['taux'], 2, '.', '') }}</ram:ValuePercent>
        </ram:SpecifiedDocumentCharacteristic>
        @endforeach
        @if(isset($reste_a_payer))
        <ram:SpecifiedDocumentCharacteristic>
          <ram:TypeCode>RAP</ram:TypeCode>
          <ram:ValueAmount>{{ number_format($reste_a_payer, 2, '.', '') }}</ram:ValueAmount>
        </ram:SpecifiedDocumentCharacteristic>
        @endif
        @endif
      </ram:SpecifiedDocumentStatus>
      @endif
    </ram:ReferenceReferencedDocument>
  </rsm:AcknowledgementDocument>

</rsm:CrossDomainAcknowledgementAndResponse>
