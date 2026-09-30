<table class="liste_produits">
    <thead>
        <tr style="background-color: #4C4C4C;color: #EEEEEE;">
            <th style="text-align: left;">{!! traduction('document.colonnes.designation.code_article.titre') !!}</th>
            <th style="text-align: left">{!! traduction('document.colonnes.designation.titre') !!}</th>
            <th style="text-align: center">{!! traduction('document.colonnes.quantite.titre') !!}</th>
            <th style="text-align: center">{!! traduction('document.pdf.articles.prix_unitaire_ht') !!}</th>

            @if($promo)
                <th style="text-align: center">{!! traduction('document.lignes_diverses.remise.titre') !!}</th>
            @endif

            <th style="text-align: center">{!! traduction('document.blocs.recap.total_ht') !!}</th>
        </tr>

    </thead>
    <tbody style="background-color: #EEEEEE ;color: #858585">

        @php
            $sous_total = 0.00;
            $total_avant_remise = 0.00;
        @endphp

        @foreach($lignes_divers as $ligne_divers)
            @if($ligne_divers->ligne != 0)
                @continue
            @endif

            @php
                $style_ligne = '';
                $style_present = false;
            @endphp

            @if($ligne_divers->id_style_ligne_document != null)

                @php

                    $style = modele('style_ligne_document',$ligne_divers->id_style_ligne_document);
                    $style_present = true;

                    if ($style->gras == 1)
                        $style_ligne .= 'font-weight:bold;';

                    if ($style->italique == 1)
                        $style_ligne .= 'font-style:italic;';

                    if ($style->taille_police != null)
                        $style_ligne .= 'font-size:'.$style->taille_police.'px;';

                    if ($style->couleur != null)
                        $style_ligne .= 'color:'.$style->couleur.';';
                @endphp
            @endif

            @if($ligne_divers->type == 'commentaire')
                <tr>
                    <td colspan="{{$promo ? 6 : 5}}" @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;" @endif>{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->contenu)) !!}</td>
                </tr>
            @endif

            @if($ligne_divers->type == 'saut_de_ligne')
                <tr>
                     <td colspan="{{$promo ? 6 : 5}}" @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;" @endif></td>
                </tr>
            @endif
            @if($ligne_divers->type == 'saut_de_page')

                    </tbody>
                </table>
                <div style="page-break-after: always;"></div>
                <table class="liste_produits">
                    <thead>
                        <tr style="background-color: #4C4C4C;color: #EEEEEE;">
                            <th style="text-align: left;">{!! traduction('document.colonnes.designation.code_article.titre') !!}</th>
                            <th style="text-align: left">{!! traduction('document.colonnes.designation.titre') !!}</th>
                            <th style="text-align: center">{!! traduction('document.colonnes.quantite.titre') !!}</th>
                            <th style="text-align: center">{!! traduction('document.pdf.articles.prix_unitaire_ht') !!}</th>

                            @if($promo)
                                <th style="text-align: center">{!! traduction('document.lignes_diverses.remise.titre') !!}</th>
                            @endif

                            <th style="text-align: center">{!! traduction('document.blocs.recap.total_ht') !!}</th>
                        </tr>

                    </thead>
                    <tbody style="background-color: #EEEEEE ;color: #858585">
            @endif

            @if($ligne_divers->type == 'titre')
                <tr>
                    <td colspan="{{$promo ? 6 : 5}}" @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;font-weight: bold;font-size: 20px;" @endif>{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->nom)) !!}</td>
                </tr>
            @endif

            @if($ligne_divers->type == 'image')
                <tr>
                    <td colspan="{{$promo ? 6 : 5}}" @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;font-weight: bold;font-size: 20px;" @endif><img height="110" src="{{ asset('storage/'.$ligne_divers->nom,false) }}" alt="" style="display: inline-block;"></td>
                </tr>
            @endif

            @if($ligne_divers->type == 'sous_total')
                <tr>
                    <td colspan="{{$promo ? 5 : 4}}"  @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;" @endif><span style="font-weight: bold;">{{$ligne_divers->nom}}</span></td>
                    <td style="text-align: center;">{{management($type_element . '_lignes')->champ('tarif')->affiche($sous_total)}} {!! maquette('devise_application_symbole') !!}</td>
                </tr>

                @php
                    $sous_total = 0.00;
                    $total_avant_remise = 0.00;
                @endphp
            @endif

            @if($ligne_divers->type == 'remise')
                @php
                    if($ligne_divers->type_remise == 1)
                        $montant_remise = $ligne_divers->remise;
                    else
                        $montant_remise = $total_avant_remise * $ligne_divers->remise / 100;
                @endphp

                <tr>
                    <td colspan="{{$promo ? 5 : 4}}"  @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;" @endif><span style="font-weight: bold;">{{$ligne_divers->nom}}</span></td>
                    <td style="text-align: center;">{{montant($montant_remise)}} {!! maquette('devise_application_symbole') !!}</td>
                </tr>

                @php
                    $total_avant_remise = 0.00;
                    $sous_total -= $montant_remise;
                @endphp
            @endif

        @endforeach

        @foreach($articles as $ligne)

            @php
                $prix =  round(floatval($ligne->tarif) * (1 - floatval($ligne->remise) / 100), 2);
                $sous_total = $sous_total + ( $prix * $ligne->quantite );
                $total_avant_remise += $prix * $ligne->quantite;
            @endphp

            <tr>
                <td style="text-align: left;">{{ $ligne->code_article }}</td>

                <td style="padding:5px;">{{$ligne->designation}}</td>

                <td style="text-align: center;padding:5px;"> {{$ligne->quantite}} </td>

                <td style="text-align: center;padding:5px;">{{management($type_element . '_lignes')->champ('tarif')->affiche($ligne->tarif)}} {!! maquette('devise_application_symbole') !!}</td>

                @if($promo)
                    <td style="text-align: center;padding:5px;">{{$ligne->remise}} %</td>
                @endif

                <td style="text-align: center;padding:5px;">{{management($type_element . '_lignes')->champ('tarif')->affiche($prix * $ligne->quantite)}} {!! maquette('devise_application_symbole') !!} </td>
            </tr>
            @if($ligne->description != null)
                <tr>
                    <td colspan="{{$promo ? 6 : 5}}"> {!! $ligne->description !!}</td>
                </tr>
            @endif
            @foreach($lignes_divers as $ligne_divers)
                @if($ligne_divers->ligne != $ligne->ligne)
                    @continue
                @endif

                @php
                    $style_ligne = '';
                    $style_present = false;
                @endphp

                @if($ligne_divers->id_style_ligne_document != null)

                    @php
                        $style = modele('style_ligne_document',$ligne_divers->id_style_ligne_document);
                        $style_present = true;

                        if ($style->gras == 1)
                            $style_ligne .= 'font-weight:bold;';

                        if ($style->italique == 1)
                            $style_ligne .= 'font-style:italic;';

                        if ($style->taille_police != null)
                            $style_ligne .= 'font-size:'.$style->taille_police.'px;';

                        if ($style->couleur != null)
                            $style_ligne .= 'color:'.$style->couleur.';';
                    @endphp
                @endif

                @if($ligne_divers->type == 'commentaire')
                    <tr>
                        <td colspan="{{$promo ? 6 : 5}}" @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;" @endif>{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->contenu)) !!}</td>
                    </tr>
                @endif

                @if($ligne_divers->type == 'saut_de_ligne')
                    <tr>
                         <td colspan="{{$promo ? 6 : 5}}" @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;" @endif></td>
                    </tr>
                @endif

                @if($ligne_divers->type == 'saut_de_page')
                        </tbody>
                    </table>
                    <div style="page-break-after: always;"></div>
                    <table class="liste_produits">
                        <thead>
                            <tr style="background-color: #4C4C4C;color: #EEEEEE;">
                                <th style="text-align: left;">{!! traduction('document.colonnes.designation.code_article.titre') !!}</th>
                                <th style="text-align: left">{!! traduction('document.colonnes.designation.titre') !!}</th>
                                <th style="text-align: center">{!! traduction('document.colonnes.quantite.titre') !!}</th>
                                <th style="text-align: center">{!! traduction('document.pdf.articles.prix_unitaire_ht') !!}</th>

                                @if($promo)
                                    <th style="text-align: center">{!! traduction('document.lignes_diverses.remise.titre') !!}</th>
                                @endif

                                <th style="text-align: center">{!! traduction('document.blocs.recap.total_ht') !!}</th>
                            </tr>

                        </thead>
                        <tbody style="background-color: #EEEEEE ;color: #858585">
                @endif

                @if($ligne_divers->type == 'titre')
                    <tr>
                        <td colspan="{{$promo ? 6 : 5}}" @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;font-weight: bold;font-size: 20px;" @endif>{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->nom)) !!}</td>
                    </tr>
                @endif

                @if($ligne_divers->type == 'image')
                    <tr>
                        <td colspan="{{$promo ? 6 : 5}}" @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;font-weight: bold;font-size: 20px;" @endif><img height="110" src="{{ asset('storage/'.$ligne_divers->nom,false) }}" alt="" style="display: inline-block;"></td>
                    </tr>
                @endif

                @if($ligne_divers->type == 'sous_total')
                    <tr>
                        <td colspan="{{$promo ? 5 : 4}}"  @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;" @endif><span style="font-weight: bold;">{{$ligne_divers->nom}}</span></td>
                        <td style="text-align: center;">{{management($type_element . '_lignes')->champ('tarif')->affiche($sous_total)}} {!! maquette('devise_application_symbole') !!}</td>
                    </tr>

                    @php
                        $sous_total = 0.00;
                        $total_avant_remise = 0.00;
                    @endphp
                @endif

                @if($ligne_divers->type == 'remise')
                    @php
                        if($ligne_divers->type_remise == 1)
                            $montant_remise = $ligne_divers->remise;
                        else
                            $montant_remise = $total_avant_remise * $ligne_divers->remise / 100;
                    @endphp

                    <tr>
                        <td colspan="{{$promo ? 5 : 4}}"  @if($style_present) style="{!! $style_ligne !!}" @else style="padding:5px;" @endif><span style="font-weight: bold;">{{$ligne_divers->nom}}</span></td>
                        <td style="text-align: center;">{{montant($montant_remise)}} {!! maquette('devise_application_symbole') !!}</td>
                    </tr>

                    @php
                        $total_avant_remise = 0.00;
                        $sous_total -= $montant_remise;
                    @endphp
                @endif

            @endforeach

        @endforeach

        @if($type_element == "commande_vente" && fonctionnalite('gescom_commande_vente_annulable_non_supprimable') == true && $document->annule == 2)
            <tr>
                <td colspan="{{$promo ? 6 : 5}}" style="padding:5px;color: red">{!! nl2br($document->motif_annulation) !!}</td>
            </tr>
        @endif

    </tbody>
</table>
