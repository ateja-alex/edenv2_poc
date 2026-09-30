<?php
	if(!isset($noColonnes)) {

		$noColonnes = count($colonnes);
	}

	foreach($colonnes as $colonne) {

		if(isset($colonne->afficher_remise) && $colonne->afficher_remise === true && !$promo) {

			$noColonnes -= 1;
		}


	}

    $niveau_regroupement = 0;

    $option_regroupement_ids = [];
?>
<table id="articles" width="100%">
    <thead>
        <tr>

            @foreach($colonnes as $i => $colonne)

                @if(!isset($colonne->afficher_remise) || $colonne->afficher_remise === false || $promo)
                    <th class="col_{{ $i }} {{ $colonne->classe }}">{!! $colonne->label !!}</th>
				@endif
            @endforeach

        </tr>
    </thead>
    <tbody>
        @php
            $sous_total = 0.00;
            $presence_article_acompte = false;
        @endphp

        @if(count($colonnes) > 0)

            @foreach($lignes_divers as $ligne_divers)
                @if($ligne_divers->ligne != 0)
                    @continue
                @endif
                @php
                    $style_css = '';
                @endphp
                @if(!empty($ligne_divers->id_style_ligne_document))
                    @php
                        $style = modele('style_ligne_document',$ligne_divers->id_style_ligne_document);

                        if(!empty($style->gras))
                            $style_css .= 'font-weight:bold;';

                        if(!empty($style->italique == true))
                            $style_css .= 'font-style: italic;';

                        if(!empty($style->taille_police))
                            $style_css .= "font-size:$style->taille_police;";


                        if(!empty($style->couleur))
                            $style_css .= "color:$style->couleur;";

                    @endphp
                @endif

                @if($ligne_divers->type == 'titre')
                    <tr class="colonne_ligne_divers colonne_ligne_divers_titre colonne_regroupement_niveau_{{$niveau_regroupement}}">
                        <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_titre" style="padding:5px;{{$style_css}}">{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->nom)) !!}</td>
                    </tr>
                @endif

                @if($ligne_divers->type == 'regroupement')
                    @php
                        if($ligne_divers->contenu == 1 || (
                            !empty($ligne_divers->regroupement_id) && in_array($ligne_divers->regroupement_id,$option_regroupement_ids)
                        ))
                            $option_regroupement_ids[] = $ligne_divers->id;
                    @endphp
                    <tr class="colonne_ligne_divers colonne_ligne_divers_regroupement colonne_regroupement_niveau_{{$niveau_regroupement}} {{in_array($ligne_divers->id,$option_regroupement_ids) ? 'option' : ''}}">
                        <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_regroupement" style="background:{{$ligne_divers->couleur_regroupement}};color:white;padding:5px;{{$style_css}}">
                            <span class="titre_regroupement">
                                {!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->nom)) !!}
                            </span>
                        </td>
                    </tr>
                    @php $niveau_regroupement++ @endphp
                @endif

                @if($ligne_divers->type == 'commentaire')
                    <tr class="colonne_ligne_divers colonne_ligne_divers_commentaire colonne_regroupement_niveau_{{$niveau_regroupement}}">
                        <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_commentaire" style="padding:5px;{{$style_css}}">{!! nl2br($ligne_divers->contenu) !!}</td>
                    </tr>
                @endif

                @if($ligne_divers->type == 'saut_de_page')

                        </tbody>
                    </table>
                    <div style="page-break-after: always;"></div>
                    <table id="articles" width="100%">
                        <thead>
                            <tr>

                                @foreach($colonnes as $i => $colonne)

                                    @if(!isset($colonne->afficher_remise) || $colonne->afficher_remise === false || $promo)
                                        <th class="col_{{ $i }} {{ $colonne->classe }}">{!! $colonne->label !!}</th>
                                    @endif
                                @endforeach

                            </tr>
                        </thead>
                        <tbody>
                @endif

                @if($ligne_divers->type == 'saut_de_ligne')
                    <tr class="colonne_ligne_divers colonne_ligne_divers_saut_de_ligne">
                        <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_saut_de_ligne" style="padding:5px;{!! $style_css !!}"></td>
                    </tr>
                @endif

                @if($ligne_divers->type == 'image')
                    <tr class="colonne_ligne_divers colonne_ligne_divers_image colonne_regroupement_niveau_{{$niveau_regroupement}}">
                        <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_image" style="padding:5px;{!! $style_css !!}">
                            <img height="{{ $ligne_divers->format }}" src="{{ asset('storage/'.$ligne_divers->nom,false) }}" alt="" style="display: inline-block;">
                        </td>
                    </tr>
                @endif

                @if($ligne_divers->type == 'sous_total')

                    <tr class="colonne_ligne_divers colonne_ligne_divers_sous_total colonne_regroupement_niveau_{{$niveau_regroupement}}">
                        <td colspan="{{ $noColonnes - 1 }}"><span style="font-weight: bold;">{!! $ligne_divers->nom !!} :</span></td>
                        <td style="padding:5px;{!! $style_css !!}">
                            <div style="text-align: right;">
                                {{ montant($sous_total,2,',',' ',false) }} {!! maquette('devise_application_symbole') !!}
                            </div>
                        </td>
                    </tr>

                    @php
                        $sous_total = 0.00;
                    @endphp
                @endif

            @endforeach

            @foreach($articles as $article)

                @php
                if($article->masquer_ligne != 1){
                    $prix =  round(floatval($article->tarif) * (1 - floatval($article->remise) / 100), 2);
                    $sous_total = $sous_total + ( $prix * $article->quantite );
                }

                if($article->article_id == fonctionnalite('compta_article_id_pour_acompte'))
                    $presence_article_acompte = true;

                @endphp

                @if($article->masquer_ligne != 1)
                    <tr class="colonne_regroupement_niveau_{{$niveau_regroupement}} {{in_array($article->regroupement_id,$option_regroupement_ids) ? 'article_option' : ''}}">

                        @foreach($colonnes as $i => $colonne)

                            @if(!isset($colonne->afficher_remise) || $colonne->afficher_remise === false || $promo)


                                <td class="colonne_{{ $i }} {{ $colonne->classe }}">{!! @blade_compile($colonne->contenu, ['article' => $article, 'document' => $document, 'calcule_total_document' => $calcule_total_document]) !!}</td>

                            @endif
                        @endforeach


                        @if(isset($afficher_code_article) && $afficher_code_article)
                            <td class="colonne_code_article">{{$article->code_article}}</td>
                        @endif

                        @if(isset($afficher_designation) && $afficher_designation)
                            <td class="colonne_designation">{{$article->designation}}</td>
                        @endif

                        @if(isset($afficher_quantite) && $afficher_quantite)
                            <td class="colonne_quantite"> {{$article->quantite}} </td>
                        @endif

                        @if(isset($afficher_prix_unitaire) && $afficher_prix_unitaire)
                            <td class="colonne_prix_unitaire">{{management($type_element . '_lignes')->champ('tarif')->affiche($article->tarif * $article->quantite)}} {!! maquette('devise_application_symbole') !!}</td>
                        @endif

                        @if(isset($afficher_remise_pc) && $afficher_remise_pc && $promo)
                            <td class="colonne_remise">{{$article->remise}} %</td>
                        @endif

                        @if(isset($afficher_sous_total_ht) && $afficher_sous_total_ht)
                            <td class="colonne_total_ht">{{management($type_element . '_lignes')->champ('tarif')->affiche($prix * $article->quantite)}} {!! maquette('devise_application_symbole') !!} </td>
                        @endif

                        @if(isset($afficher_tva) && $afficher_tva)
                            <td class="colonne_tva">{{ $article->tva }} % </td>
                        @endif


                    </tr>
                @endif

                {{--
                @if(!empty($article->description))
                    <tr>
                        <td colspan="{{ $noColonnes }}" class="colonne_code_article">{!! $article->description !!}</td>
                    </tr>
                @endif
                --}}


                @foreach($lignes_divers as $ligne_divers)
                    @if($ligne_divers->ligne != $article->ligne)
                        @continue
                    @endif
                    @php
                        $style_css = '';
                    @endphp
                    @if(!empty($ligne_divers->id_style_ligne_document))
                        @php
                            $style = modele('style_ligne_document',$ligne_divers->id_style_ligne_document);

                            if(!empty($style->gras))
                                $style_css .= 'font-weight:bold;';

                            if(!empty($style->italique == true))
                                $style_css .= 'font-style: italic;';

                            if(!empty($style->taille_police))
                                $style_css .= "font-size:$style->taille_police;";


                            if(!empty($style->couleur))
                                $style_css .= "color:$style->couleur;";

                        @endphp
                    @endif
                    @if($ligne_divers->type == 'titre')

                        <tr class="colonne_ligne_divers colonne_ligne_divers_titre colonne_regroupement_niveau_{{$niveau_regroupement}}">
                            <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_titre" style="padding:5px;{{$style_css}}">{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->nom)) !!}</td>
                        </tr>
                    @endif

                    @if($ligne_divers->type == 'regroupement')
                        @php
                            if($ligne_divers->contenu == 1 || (
                                !empty($ligne_divers->regroupement_id) && in_array($ligne_divers->regroupement_id,$option_regroupement_ids)
                            ))
                                $option_regroupement_ids[] = $ligne_divers->id;
                        @endphp
                        <tr class="colonne_ligne_divers colonne_ligne_divers_regroupement colonne_regroupement_niveau_{{$niveau_regroupement}} {{in_array($ligne_divers->id,$option_regroupement_ids) ? 'option' : ''}}">
                            <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_regroupement" style="background:{{$ligne_divers->couleur_regroupement}};color:white;padding:5px;{{$style_css}};">
                                <span class="titre_regroupement">
                                    {!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->nom)) !!}
                                </span>
                            </td>
                        </tr>
                        @php $niveau_regroupement++ @endphp
                    @endif

                    @if($ligne_divers->type == 'regroupement_fermeture')
                        <tr class="colonne_ligne_divers colonne_ligne_divers_regroupement_fermeture {{in_array($ligne_divers->id,$option_regroupement_ids) ? 'option' : ''}}">
                            <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_regroupement_fermeture" style="background:{{$ligne_divers->couleur_regroupement}};height:3px;color:white;{{$style_css}}"></td>
                        </tr>
                        @php $niveau_regroupement--; @endphp
                    @endif

                    @if($ligne_divers->type == 'commentaire')
                        <tr class="colonne_ligne_divers colonne_ligne_divers_commentaire colonne_regroupement_niveau_{{$niveau_regroupement}}">
                            <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_commentaire" style="padding:5px;{{$style_css}}">{!! nl2br($ligne_divers->contenu) !!}</td>
                        </tr>
                    @endif

                    @if($ligne_divers->type == 'saut_de_ligne')
                        <tr class="colonne_ligne_divers colonne_ligne_divers_saut_de_ligne">
                            <td colspan="{{ $noColonnes }}" style="padding:5px;{!! $style_css !!}"></td>
                        </tr>
                    @endif

                    @if($ligne_divers->type == 'saut_de_page')

                            </tbody>
                        </table>
                        <div style="page-break-after: always;"></div>
                        <table id="articles" width="100%">
                            <thead>
                                <tr>

                                    @foreach($colonnes as $i => $colonne)

                                        @if(!isset($colonne->afficher_remise) || $colonne->afficher_remise === false || $promo)
                                            <th class="col_{{ $i }} {{ $colonne->classe }}">{!! $colonne->label !!}</th>
                                        @endif
                                    @endforeach

                                </tr>
                            </thead>
                            <tbody>
                    @endif

                    @if($ligne_divers->type == 'image')
                        <tr class="colonne_ligne_divers colonne_ligne_divers_image colonne_regroupement_niveau_{{$niveau_regroupement}}">
                            <td colspan="{{ $noColonnes }}" class="colonne_ligne_divers colonne_ligne_divers_image" style="padding:5px;{!! $style_css !!}">
                                <img height="{{ $ligne_divers->format }}" src="{{ asset('storage/'.$ligne_divers->nom,false) }}" alt="" style="display: inline-block;">
                            </td>
                        </tr>
                    @endif

                    @if($ligne_divers->type == 'sous_total')
                        <tr class="colonne_ligne_divers colonne_ligne_divers_sous_total colonne_regroupement_niveau_{{$niveau_regroupement}}">
                            <td colspan="{{ $noColonnes - 1 }}"><span style="font-weight: bold;">{!! $ligne_divers->nom !!} :</span></td>
                            <td style="padding:5px;{!! $style_css !!}">
                                <div style="text-align: right;">
                                    {{ montant($sous_total,2,',',' ',false) }} {!! maquette('devise_application_symbole') !!}
                                </div>
                            </td>
                        </tr>

                        @php
                            $sous_total = 0.00;
                        @endphp
                    @endif

                @endforeach

            @endforeach

        @endif
    </tbody>
    @php $tfoot = view('eden::pdf.includes.modele_document_totaux',get_defined_vars())->render();@endphp
    @if (trim($tfoot) !== '')
        <tfoot>
            {!! $tfoot !!}
        </tfoot>
    @endif
</table>
