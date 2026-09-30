@foreach ($lignes as $i => $ligne)

    @if(isset($ligne->type) && $ligne->type == 'articles')

        @include('eden::pdf.includes.modele_document_articles', (array)$ligne)

    @elseif(isset($ligne->type) && $ligne->type == 'div')

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
                                'contenu_specifique_document'=> $contenu_specifique_document,

                                'document_management'       => $document_management,
                            ]
                    );

            if(isset($fournisseur))
                $donnees['fournisseur'] = $fournisseur;

            if(isset($contacts))
                $donnees['contacts'] = $contacts;

        @endphp

        {!! blade_compile($ligne->texte, $donnees) !!}
    @else

		  <table width="100%">
            <tr>
                @foreach ($ligne as $j => $bloc)

                    <td width="{{ round($bloc->col / 12 * 100) }}%" class="{{ $type }}_bloc_{{ $i }}_{{ $j }}" valign="top">

                        <!-- Affichage d'une vue -->
                        @if(@$bloc->type == 'vue')

                            {!! view('eden::'.$bloc->vue, (array)$ligne)->render() !!}

                        <!-- Affichage des totaux -->
                        @elseif(@$bloc->type == 'totaux')

                            <table width="100%">
                                @include('eden::pdf.includes.modele_document_totaux', (array)$bloc)
                            </table>

                        @elseif(@$bloc->type == 'totaux_sans_detail')

                            <table width="100%">
                                @include('eden::pdf.includes.modele_document_totaux_sans_detail', (array)$bloc)
                            </table>

                        @elseif(@$bloc->type == 'recap_tva')
                            <table width="100%">
                                @include('eden::pdf.includes.modele_document_recap_tva', (array)$bloc)
                            </table>

                        @elseif(@$bloc->type == 'recap_option')
                            <table width="100%">
                                @include('eden::pdf.includes.modele_document_recap_option', (array)$bloc)
                            </table>

                        <!-- Affichage du code HTML configuré -->
                        @else

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
                                                'contenu_specifique_document'=> $contenu_specifique_document,

                                                'document_management'       => $document_management,
                                            ]
                                    );

                            if(isset($fournisseur))
                                $donnees['fournisseur'] = $fournisseur;

                            if(isset($contacts))
                                $donnees['contacts'] = $contacts;

                        @endphp

                        {!! blade_compile(@$bloc->contenu, $donnees) !!}
                            
                        @endif
                    </td>

                @endforeach
            </tr>
    	</table>

    @endif

@endforeach