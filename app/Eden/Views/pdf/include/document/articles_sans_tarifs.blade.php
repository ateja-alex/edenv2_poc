<table class="liste_produits">
	    <thead>
	        <tr style="background-color: #4C4C4C;color: #EEEEEE;">
				<th style="text-align: left;">{!! traduction('document.colonnes.designation.code_article.titre') !!}</th>
				<th style="text-align: left">{!! traduction('document.colonnes.designation.titre') !!}</th>
				<th style="text-align: center">{!! traduction('document.colonnes.quantite.titre') !!}</th>
	        </tr>
	       
	    </thead>
	    <tbody style="background-color: #EEEEEE ;color: #858585">

	        @php 
	            $sous_total = 0.00;
				$total_avant_remise = 0.00;
	        @endphp

	        @foreach($lignes_divers as $ligne_divers)
	            @if($ligne_divers->ligne >= $articles[0]->ligne)
	                @continue
	            @endif
	            
	            @if($ligne_divers->type == 'commentaire')
	                <tr>
	                    <td colspan="3" style="padding:5px;">{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->contenu)) !!}</td>
	                </tr>
	            @endif

	            @if($ligne_divers->type == 'saut_de_ligne')
	                <tr>
	                     <td colspan="3" style="padding:5px;"></td>
	                </tr>
	            @endif

	            @if($ligne_divers->type == 'titre')
	                <tr>
	                    <td colspan="3" style="padding:5px;font-weight: bold;font-size: 20px;">{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->nom)) !!}</td>
	                </tr>
	            @endif

	            @if($ligne_divers->type == 'image')
	                <tr>
	                    <td colspan="3" style="padding:5px;font-weight: bold;font-size: 20px;"><img height="110" src="{{ asset('storage/'.$ligne_divers->nom,false) }}" alt="" style="display: inline-block;"></td>
	                </tr>
	            @endif

	            @if($ligne_divers->type == 'sous_total')
	                <tr>
	                    <td colspan="2" style="padding:5px;"><span style="font-weight: bold;">{{$ligne_divers->nom}}</span></td>
	                    <td style="text-align: center;">{{ montant($sous_total) }} {!! maquette('devise_application_symbole') !!}</td>
	                </tr>

	                @php 
	                    $sous_total = 0.00;
						$total_avant_remise = 0.00;
	                @endphp
	            @endif

				@if($ligne_divers->type == 'remise')

					 @php
                        if($ligne_divers->type_ligne == 1)
                            $montant_remise = $ligne_divers->remise;
                        else
                            $montant_remise = $total_avant_remise * $ligne_divers->remise / 100;
                     @endphp

					 <tr>
						<td colspan="2" style="padding:5px;"><span style="font-weight: bold;">{{$ligne_divers->nom}}</span></td>
						<td style="text-align: center;">{{montant($montant_remise)}} {!! maquette('devise_application_symbole') !!}

						</td>
					</tr>

					@php
						$total_avant_remise = 0.00;
                        $sous_total -= $montant_remise;

					@endphp
				@endif

	        @endforeach

	        @foreach($articles as $ligne)

	            @php
	                $remise =  floatval($ligne->tarif)  * floatval($ligne->remise / 100);
	                $prix = $ligne->tarif - $remise;
	                $sous_total = $sous_total + ( $prix * $ligne->quantite );
	                $total_avant_remise += $prix * $ligne->quantite;

				@endphp

	            <tr>
	                <td style="text-align: left;">{{ $ligne->code_article }}</td>

	                <td style="padding:5px;">{{$ligne->designation}}</td>

	                <td style="text-align: center;padding:5px;"> {{$ligne->quantite}} </td>

	            </tr>

	            @if($ligne->description != null)
	                <tr>
	                    <td colspan="3"> {!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne->description)) !!}</td>
	                </tr>
	            @endif

	            @foreach($lignes_divers as $ligne_divers)
	                @if($ligne_divers->ligne != $ligne->ligne)
	                    @continue
	                @endif
	                
	                @if($ligne_divers->type == 'commentaire')
	                    <tr>
	                        <td colspan="3" style="padding:5px;">{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->contenu)) !!}</td>
	                    </tr>
	                @endif

	                @if($ligne_divers->type == 'saut_de_ligne')
	                    <tr>
	                        <td colspan="3" style="padding:5px;"></td>
	                    </tr>
	                @endif

	                @if($ligne_divers->type == 'titre')
	                    <tr>
	                        <td colspan="3" style="padding:5px;font-weight: bold;font-size: 20px;">{!! nl2br(str_replace(array('<', '>'), array('&lsaquo;', '&rsaquo;'), $ligne_divers->nom)) !!}</td>
	                    </tr>
	                @endif

					@if($ligne_divers->type == 'image')
						<tr>
							<td colspan="3" style="padding:5px;font-weight: bold;font-size: 20px;"><img height="110" src="{{ asset('storage/'.$ligne_divers->nom,false) }}" alt="" style="display: inline-block;"></td>
						</tr>
					@endif

	                @if($ligne_divers->type == 'sous_total')
						<tr>
							<td colspan="2" style="padding:5px;"><span style="font-weight: bold;">{{$ligne_divers->nom}}</span></td>
							<td style="text-align: center;" colspan="1">{{ montant($sous_total) }} {!! maquette('devise_application_symbole') !!}</td>
						</tr>

						@php
							$sous_total = 0.00;
							$total_avant_remise = 0.00;
						@endphp
	            	@endif

					@if($ligne_divers->type == 'remise')

						@php
							if($ligne_divers->type_ligne == 1)
								$montant_remise = $ligne_divers->remise;
							else
								$montant_remise = $total_avant_remise * $ligne_divers->remise / 100;
                    	@endphp
						<tr>
							<td colspan="2" style="padding:5px;"><span style="font-weight: bold;">{{$ligne_divers->nom}}</span></td>
							<td style="text-align: center;">{{montant($montant_remise)}} {!! maquette('devise_application_symbole') !!}</td>
						</tr>

						@php


							$total_avant_remise = 0.00;
                        	$sous_total -= $montant_remise;

						@endphp
					@endif
	            
	            @endforeach

	        @endforeach

			@if($type_element == "commande_vente" && !empty(fonctionnalite('gescom_commande_vente_annulable_non_supprimable')) && $document->annule == 2)
				<tr>
					<td colspan="3" style="padding:5px;color: red">{!! nl2br($document->motif_annulation) !!}</td>
				</tr>
			@endif

	    </tbody>
	</table>