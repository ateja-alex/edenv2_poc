<html>
<head>

	<title>{{ $document->reference_document }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <!-- Déclaration des styles CSS -->
    <style>
        @page {
            margin: 1cm;
        }
        table.liste_produits {
            width: 100%;
            border-collapse: collapse;
            border-width: 1px;
            border-color: #363636;
            border-style: solid;
            margin: 15px 0;
        }
        
        .text-center {
            text-align: center;
        }
        .text-uppercase {
            text-transform: uppercase;
        }
        .text-capitalize {
            text-transform: capitalize;
        }
        .css_footer {
            position: fixed;
            bottom: 70px;
            border-top: 1px solid #cecece;
        }
        .css_footer_text {
            font-size: 14px;
            color: #444444;
        }
        .css_info_retard_paiement {
            font-size: 12px;
            border: 1px solid black;
            padding: 5px 2px;
        }
    </style>
</head>

@php

	$langue = maquette('langue_par_defaut_code');

	if(strpos($type_element, 'achat') !== false)
		$doc_achat = true;
	else
		$doc_achat = false;

	$affichages_totaux = true;

	if(strpos($type_element, 'bl') !== false)
		$affichages_totaux = false;
	else if (strpos($type_element, 'devis_achat') !== false)
		$affichages_totaux = false;

@endphp

<!-- Contenu du template -->
<body style="padding-bottom: 100px;">
    {{-- Logo --}}
	@if($document->annule == 1)
		<img width="100%" src="{{ str_replace('https://', 'http://', URL::to('/eden/images/filigrane.svg')) }}" alt="" style="position: fixed; top: 50%; transform: translateY(-50%);opacity: 0.5">
	@endif

	<table style="width: 100%;">
        <tr>
            <td style="width: 20%">
                <img height="110" src="{{ str_replace('https://', 'http://', asset('storage/'.maquette('logo_application_connexion'))) }}" alt="" style="display: inline-block;">
            </td>
            <td style="vertical-align: top;">
                @include('eden::pdf.include.document.emmeteur')
            </td>
            <td style="font-size: 12px;vertical-align: top;">
                @yield('informations_document')
            </td>
        </tr>
        <tr>
            <td></td>
            <td> 
            	@if($document_management->management_fiche()->presence_module('adresse_de_livraison'))
	           		<p>
	            		<b>{!! traduction('pdf.document_gescom.adresse_livraison', $langue) !!}</b>
	            		<br>
                        @if(isset($document->adresse_de_livraison_texte) && $document->adresse_de_livraison_texte !== '')
                            {!! $document->adresse_de_livraison_texte !!}
                        @else
                            <span class='text-uppercase'>{{ $adresse_de_livraison->modele->societe }}</span>
                            <br>
                            {{ $adresse_de_livraison->modele->adresse }}<br/>

                            @if(!empty($adresse_de_livraison->modele->adresse_complement))
                            {{ $adresse_de_livraison->modele->adresse_complement }}<br/>
                            @endif
                            {{ $adresse_de_livraison->modele->code_postal }} {{ $adresse_de_livraison->modele->ville }}<br/>
                            {{ $adresse_de_livraison->champ('pays_id')->affiche() }}<br/>
                        @endif
	                </p>
            	@endif
            </td>
            <td>
                <p>
                	<b>{!! traduction('pdf.document_gescom.adresse_facturation', $langue) !!}</b>
            		<br>
                    @if(isset($document->adresse_de_facturation_texte) && $document->adresse_de_facturation_texte !== '')
                        {!! $document->adresse_de_facturation_texte !!}
                    @else
                        <span class='text-uppercase'>{{ $adresse_de_facturation->modele->societe }}</span>
                        <br>
                        {{ $adresse_de_facturation->modele->adresse }}<br/>

                        @if(!empty($adresse_de_facturation->modele->adresse_complement))
                        {{ $adresse_de_facturation->modele->adresse_complement }}<br/>
                        @endif
                        {{ $adresse_de_facturation->modele->code_postal }} {{ $adresse_de_facturation->modele->ville }}<br/>
                        {{ $adresse_de_facturation->champ('pays_id')->affiche() }}<br/>
                    @endif
                </p>
            </td>
        </tr>
    </table>

    {{-- Nom et adresse de l’entreprise de livraison --}}
    {{-- Nom et prénom du client --}}
    {{-- Numéro de commande --}}
    @if($affichages_totaux)
    	<p style="float:right;font-size: 12px;">
			{!! traduction('pdf.document_gescom.montants_exprimes_en', $langue) !!} {!! maquette('devise_application_iso') !!}
	    </p>
    @endif
    

	@section('articles')
		@include('eden::pdf.include.document.articles')
	@endsection
	@yield('articles')
	
    <table style="width: 100%; font-weight: 700; border-collapse: collapse;" id="">
        <tr style="font-size: 13px;">
            <td style="width: 45%">
            	@section('commentaires')
	            	<table style="width: 100%;border : 1px solid black">
	            		<tr>
	            			<td style="font-weight: bold;">{!! traduction('pdf.document_gescom.commentaire', $langue) !!} :</td>
	            		</tr>
	            		<tr>
	            			<td style="color: #858585">
	            				{!! $document->commentaires !!}
	            			</td>
	            		</tr>
	            	</table>
            	@endsection
            	@yield('commentaires')
            </td>
            <td></td>
            <td style="width: 45%;">
                @if($affichages_totaux)
                    <table style="width: 90%;">
                        @if($document->remise_globale > 0)
                            <tr>
                                <td style="border-bottom: 1px solid black;">
                                    {!! traduction('pdf.document_gescom.total_ht_avant_remise', $langue) !!}
                                </td>
                                <td style="border-bottom: 1px solid black;">
                                    {{ management($type_element)->champ('montant_document_ht')->affiche($calcule_total_document['avant_remise']['ht']) }}{!! maquette('devise_application_nom') !!}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td style="border-bottom: 1px solid black;">
                                {!! traduction('pdf.document_gescom.total_ht', $langue) !!}
                            </td>
                            <td style="border-bottom: 1px solid black;">
                                {{ management($type_element)->champ('montant_document_ht')->affiche($document->montant_document_ht) }}{!! maquette('devise_application_nom') !!}
                            </td>
                        </tr>
    
    
                        @foreach($tableau_tva as $tva => $info)
                            <tr>
                                <td style="border-bottom: 1px solid black;">
                                    {!! traduction('pdf.document_gescom.total_tva', $langue) !!} {{ $tva }} %
                                </td>
                                <td style="border-bottom: 1px solid black;">
                                    {{ management($type_element)->champ('montant_document_tva')->affiche($info['tva']) }}{!! maquette('devise_application_nom') !!}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="border-bottom: 1px solid black;">
                                {!! traduction('pdf.document_gescom.total_ttc', $langue) !!}
                            </td>
                            <td style="border-bottom: 1px solid black;">
                                {{ management($type_element)->champ('montant_document_ttc')->affiche($document->montant_document_ttc) }}{!! maquette('devise_application_nom') !!}
                            </td>
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>
    
    @yield('texte_sous_totaux')
    
    @php 
    	$compte_bancaire = modele('compte_bancaire',$document->compte_bancaire_id);
    @endphp

    @if(!$doc_achat && $type_element != "bl_vente" && $type_element != "devis_vente")
    	<p class="css_info_retard_paiement">
			{!! traduction('pdf.document_gescom.reglement', $langue) !!} :<br>
			{!! traduction('pdf.bordereau.iban', $langue) !!} : {!! $compte_bancaire->iban !!}<br>
			{!! traduction('pdf.bordereau.bic', $langue) !!} : {!! $compte_bancaire->bic !!}
	    </p>
    @endif
    
    <div class="css_footer">
        @include('eden::pdf.include.document.footer')
    </div>
</body>
</html>