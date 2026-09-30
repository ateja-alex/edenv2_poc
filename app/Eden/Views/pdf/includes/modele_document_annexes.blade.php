@foreach($annexes as $i => $annexe)


    @php
        $donnees = (
                    isset($element)
                    ?
                        [
                            'element'                  => $element,
                        ]
                    :
                        [
                            'document'                  => $document,
                            'entite'                    => $entite_management->modele,
                            'client'                    => $client,
                            'projet'                    => $projet,
                            'adresse_de_facturation'    => $adresse_de_facturation->modele,
                            'adresse_de_livraison'      => $adresse_de_livraison->modele,
                            'nom_document'              => $nom_document,
                            'type_element'              => $type_element,
                            'client_management'         => $client_management,
                            'tableau_tva'               => $tableau_tva,
                            'paiements'                 => $paiements,
                            'echeances'                 => $echeances,
                            '__env'                     => $__env,

                            'document_management'       => $document_management,
                        ]
                );

        if(isset($fournisseur))
            $donnees['fournisseur'] = $fournisseur;

        if(isset($contacts))
            $donnees['contacts'] = $contacts;

        $contenu = blade_compile($annexe, $donnees);
        
    @endphp

	@if(empty($contenu) || $contenu == "<p></p>")
		@continue
	@endif

	<div style="page-break-before: always;"></div>

	{!! $contenu !!}

@endforeach