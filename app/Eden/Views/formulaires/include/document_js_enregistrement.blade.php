@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * On enregistre le document
	 *
	 */
	enregistre_document : async function(que_les_lignes = false, continuer_enregistrement = false) {

		loading(true);

		@if($management->existe() && $management->_type_element == "commande_vente" && fonctionnalite('gescom_commande_vente_annulable_non_supprimable') && $management->modele->annule != 1 && $management->modele->valide == 1)

			if(this.document.annule != 1 && continuer_enregistrement === false) {
				if(await this.verification_annulation_partielle_document() === true)
					return;
			}

		@endif

		$('#form_liste_articles').html("");

		var position = 0;
		var ligne_pour_tableau = 1;
		var id_ligne_divers = 0;
		var ligne_divers_quelle_ligne_article = 0;

		@if(!empty(moi_extranet()))
			this.$root.disable_champs_extranet = false;
		@endif
		this.articles_du_document.forEach((article) => {

			//article.designation = article.designation.replace(regex, '"')
			position++;

			// l'id de la ligne

			if(article.type_ligne !== undefined && article.id != undefined) {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][id]" value="'+article.id+'" />');

			}

			if(article.type_ligne !== undefined){
				@foreach($options_lignes_divers_champs_supplementaires_enregistrement as $nom_champ => $actif)
					@if($actif === true)

						$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][{{$nom_champ}}]" value="'+article.{{$nom_champ}}+'" />');
					@endif
				@endforeach
			}

			// c'est un sous total
			if(article.type_ligne == 'sous_total') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="sous_total" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');
				if(article.nom != undefined && article.nom != "")
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="'+article.nom.replace('"', "''")+'" />');
				else
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="" />');

				@if(fonctionnalite('gescom_activer_style_sur_ligne_document'))
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][id_style_ligne_document]" value="'+article.id_style_ligne_document+'" />');
				@endif

				id_ligne_divers++;
			}

			// c'est un saut de ligne
			if(article.type_ligne == 'saut_de_ligne') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="saut_de_ligne" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');

				id_ligne_divers++;
			}

			// c'est un saut de page
			if(article.type_ligne == 'saut_de_page') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="saut_de_page" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');

				id_ligne_divers++;
			}

			// c'est un titre
			if(article.type_ligne == 'titre') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="titre" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');
				if(article.nom != undefined && article.nom != "")
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="'+article.nom.replace('"', "''")+'" />');
				else
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="" />');

				@if(fonctionnalite('gescom_activer_style_sur_ligne_document'))
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][id_style_ligne_document]" value="'+article.id_style_ligne_document+'" />');
				@endif
				id_ligne_divers++;
			}

			// c'est un commentaire
			if(article.type_ligne == 'commentaire') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="commentaire" />');
				$('#form_liste_articles').append('<textarea name="lignes_divers['+(id_ligne_divers)+'][contenu]">'+article.contenu+'</textarea>');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');

				@if(fonctionnalite('commentaires_wysiwyg_documents'))
					$('#form_liste_articles').append('<textarea name="lignes_divers['+(id_ligne_divers)+'][commentaire_wysiwyg]">'+article.commentaire_wysiwyg+'</textarea>');
				@endif
				@if(fonctionnalite('gescom_activer_style_sur_ligne_document'))
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][id_style_ligne_document]" value="'+article.id_style_ligne_document+'" />');
				@endif

				id_ligne_divers++;
			}

			// c'est une note interne
			if(article.type_ligne == 'note_interne') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="note_interne" />');
				$('#form_liste_articles').append('<textarea name="lignes_divers['+(id_ligne_divers)+'][contenu]">'+article.contenu+'</textarea>');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');

				@if(fonctionnalite('commentaires_wysiwyg_documents'))
					$('#form_liste_articles').append('<textarea name="lignes_divers['+(id_ligne_divers)+'][note_interne_wysiwyg]">'+article.note_interne_wysiwyg+'</textarea>');
				@endif
				@if(fonctionnalite('gescom_activer_style_sur_ligne_document'))
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][id_style_ligne_document]" value="'+article.id_style_ligne_document+'" />');
				@endif

				id_ligne_divers++;
			}


			// c'est une image
			if(article.type_ligne == 'image') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="image" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="'+article.nom+'"/>');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][format]" value="'+article.format+'"/>');

				id_ligne_divers++;
			}

			// c'est un regroupement
			if(article.type_ligne == 'regroupement') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="regroupement" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');

				@if($management->_type_element == 'devis_vente')
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][contenu]" value="' + article.contenu + '" />');
				@endif
				if(article.nom != "undefined" && article.nom != undefined && article.nom != null)
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="'+article.nom+'"/>');
				else
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value=" "/>');

				$('#form_liste_articles').append('<textarea name="lignes_divers['+(id_ligne_divers)+'][coefficient]">'+ JSON.stringify(article.coefficient) +'</textarea>');

				if(article.id_temporaire != undefined && article.id_temporaire != null && article.id_temporaire != "undefined" && article.id_temporaire == true)
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][id_temporaire]" value="'+ article.id_temporaire +'" />');

				if(article.calculateur)
					$('#form_liste_articles').append('<textarea name="lignes_divers['+(id_ligne_divers)+'][calculateur]">'+ JSON.stringify(article.calculateur) +'</textarea>');


				id_ligne_divers++;
			}

			// c'est une fin de regroupement
			if(article.type_ligne == 'regroupement_fermeture') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="regroupement_fermeture" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="Fin de regroupement"/>');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');

				id_ligne_divers++;
			}

			if(article.type_ligne == 'coefficient') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');
				if(article.nom != "undefined" && article.nom != undefined && article.nom != null)
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="'+article.nom+'"/>');
				else
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="Coefficient"/>');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="coefficient" />');
				$('#form_liste_articles').append('<input type="number" name="lignes_divers['+(id_ligne_divers)+'][type_coefficient]" value="'+parseInt(article.type_coefficient)+'" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>');
				$('#form_liste_articles').append('<input type="number" name="lignes_divers['+(id_ligne_divers)+'][quantite]" value="'+parseFloat(article.quantite.toString().replace(',','.'))+'" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>');
				id_ligne_divers++;
			}

			if(article.type_ligne == 'calculateur') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');
				if(article.nom != "undefined" && article.nom != undefined && article.nom != null)
					$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="'+article.nom+'"/>');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="calculateur" />');

				if(article.calculateur)
					$('#form_liste_articles').append('<textarea name="lignes_divers['+(id_ligne_divers)+'][calculateur]">'+ JSON.stringify(article.calculateur) +'</textarea>');

				id_ligne_divers++;
			}

			// c'est une option
			if(article.type_ligne == 'option') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="option" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="'+article.option+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][quantite]" value="'+ parseFloat(article.quantite.replace(',','.')) +'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][tarif]" value="'+ parseFloat(article.tarif.replace(',','.')) +'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][remise]" value="'+ parseFloat(article.remise.toString().replace(',','.')) +'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][tva]" value="'+ parseFloat(article.tva.replace(',','.'))+'" />');

				id_ligne_divers++;
			}

			// c'est une remise
			if(article.type_ligne == 'remise') {

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][ligne]" value="'+ligne_divers_quelle_ligne_article+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][position]" value="'+position+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type]" value="remise" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][couleur_regroupement]" value="' + article.couleur_regroupement + '" />');

				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][nom]" value="'+article.nom.replace('"', "''")+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][type_remise]" value="'+article.type_remise+'" />');
				$('#form_liste_articles').append('<input name="lignes_divers['+(id_ligne_divers)+'][remise]" value="'+ parseFloat(article.remise.toString().replace(',','.')) +'" />');

				id_ligne_divers++;
			}

			if(article.type_ligne != undefined) {

				return;
			}

			// c'est un article
			position = 0;
			ligne_divers_quelle_ligne_article = ligne_pour_tableau;

			@foreach($colonnes_articles_enregistrement as $nom_champ => $actif)
				@if($actif === true)


					@if($nom_champ == 'tva')

						if(this.$data.exoneration_tva)
							$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][tva]" value="0" />');
						else if(article.tva)
							$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][tva]" value="'+parseFloat(article.tva.toString().replace(',','.'))+'" />');
						else
							$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][tva]" value="'+ article.tva +'" />');

					@else

						var valeur_champ = article.{{$nom_champ}};

						@php
							$modele_champ_libre = champ_libre_modele($management->_type_element.'_lignes',$nom_champ);
						@endphp

						@if(!empty($modele_champ_libre) && in_array($modele_champ_libre->type,[0,6]))
							if(valeur_champ != undefined && typeof valeur_champ === 'string') {
								valeur_champ = valeur_champ.replaceAll('"', "&quot;");
								valeur_champ = valeur_champ.replaceAll('<', "&lt;");
								valeur_champ = valeur_champ.replaceAll('>', "&gt;");
							}
						@endif

						@if($nom_champ == 'description')
							$('#form_liste_articles').append('<textarea name="articles['+(ligne_pour_tableau)+'][{{$nom_champ}}]">'+valeur_champ+'</textarea>');
						@elseif($nom_champ == 'coefficient')

							var total_coefficient = 0;
							if(valeur_champ != undefined && valeur_champ != null && valeur_champ != "undefined" && valeur_champ.length > 0){

								valeur_champ.forEach(function(information,index){

									total_coefficient += parseFloat(information.quantite.toString().replace(',','.'));

								});

							}

							$('#form_liste_articles').append('<input type="text" name="articles['+(ligne_pour_tableau)+'][{{$nom_champ}}_article]" value="'+ total_coefficient +'"/>' );
							$('#form_liste_articles').append('<textarea name="articles['+(ligne_pour_tableau)+'][{{$nom_champ}}]">'+ JSON.stringify(valeur_champ) +'</textarea>');
						@elseif($nom_champ == 'calculateur')
							if(valeur_champ !== undefined && valeur_champ !== null)
								$('#form_liste_articles').append('<textarea name="articles['+(ligne_pour_tableau)+'][{{$nom_champ}}]">'+ JSON.stringify(valeur_champ) +'</textarea>');
						@else

							$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][{{$nom_champ}}]" value="'+valeur_champ+'" />');
						@endif
					@endif

				@endif
			@endforeach

			if(article.regroupement_id != undefined && article.regroupement_id != null && article.regroupement_id !== false){

				var regroupement = this.articles_du_document.filter(ligne => ligne.type_ligne == 'regroupement' && ligne.id == article.regroupement_id);

				regroupement = regroupement[0];

				var total_coefficient_regroupement = 0;

				// console.log(regroupement);

				if(regroupement.coefficient != undefined && regroupement.coefficient != null && regroupement.coefficient.length > 0){

					regroupement.coefficient.forEach(function(information,index){

						total_coefficient_regroupement += parseFloat(information.quantite.toString().replace(',','.'));

					});

				}

				$('#form_liste_articles').append('<input type="text" name="articles['+(ligne_pour_tableau)+'][coefficient_regroupement]" value="'+ total_coefficient_regroupement +'"/>' );

			}

			var coefficient_document = this.articles_du_document.filter(ligne => ligne.type_ligne == 'coefficient');

			if(coefficient_document != undefined && coefficient_document != null && coefficient_document.length > 0){

				var total_coefficient_document = 0;

				coefficient_document.forEach(function(information,index){

					total_coefficient_document += parseFloat(information.quantite.toString().replace(',','.'));

				});

				$('#form_liste_articles').append('<input type="text" name="articles['+(ligne_pour_tableau)+'][coefficient_devis]" value="'+ total_coefficient_document +'"/>' );

			}

			// certaines infos en dur...
			$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][afficher_photo]" value="'+article.afficher_photo+'" />');
			$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][masquer_ligne]" value="'+article.masquer_ligne+'" />');
			$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][declinaison_id]" value="'+article.declinaison_id+'" />');

			// l'id de l'article
			$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][article_id]" value="'+article.article_id+'" />');

			// l'id de la ligne
			if(article.id !== undefined)
				$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][id]" value="'+article.id+'" />');

			// les infos source
			if(article.type_element_source !== undefined) {

				$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][type_element_source]" value="'+article.type_element_source+'" />');
				$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][id_element_source]" value="'+article.id_element_source+'" />');
				$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][id_ligne_source]" value="'+article.id_ligne_source+'" />');
			}

			if(article.categorie_eco_contribution_id > 0)
				$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][categorie_eco_contribution_id]" value="'+article.categorie_eco_contribution_id+'" />');

			if(article.application_eco_contribution)
				$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][application_eco_contribution]" value="'+article.application_eco_contribution+'" />');

			if(article.quantite_unite_eco_contribution)
				$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][quantite_unite_eco_contribution]" value="'+article.quantite_unite_eco_contribution+'" />');

			if(article.tarif_eco_contribution)
				$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][tarif_eco_contribution]" value="'+article.tarif_eco_contribution+'" />');

			$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][code_article]" value="'+article.code_article+'" />');

			$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][type_tarif]" value="{{ strtoupper(config('eden.mode_calcul_gescom')) }}" />');


			if(this.type_element == 'bl_vente') {
				$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][preparation_partielle]" value="'+article.preparation_partielle+'" />');
			}

			// on traite les achats de la ligne
			var achats = [];

			article.achats.forEach(function(achat) {

				// pas d'article sélectionné
				if(achat.article_id == '' || achat.article_id == undefined)
					return;

				// ok on ajoute l'achat
				achats.push(achat);

			});

			// on traite la nomenclature de la ligne
			var nomenclature = [];

			if(Array.isArray(article.nomenclature)){
				article.nomenclature.forEach(function(composition) {

					// ok on ajoute l'achat
					nomenclature.push(composition);

				});
			}

			// on traite la nomenclature de la ligne
			var numeros_de_lot = [];

			article.numeros_de_lot.forEach(function(composition) {

				// ok on ajoute l'achat
				numeros_de_lot.push(composition);

			});

			$('#form_liste_articles').append('<textarea name="articles['+(ligne_pour_tableau)+'][achats]">'+JSON.stringify(achats)+'</textarea>');
			$('#form_liste_articles').append('<textarea name="articles['+(ligne_pour_tableau)+'][nomenclature]">'+JSON.stringify(nomenclature)+'</textarea>');
			$('#form_liste_articles').append('<textarea name="articles['+(ligne_pour_tableau)+'][numeros_de_lot]">'+JSON.stringify(numeros_de_lot)+'</textarea>');

			if(Array.isArray(article.feuille_de_temps_ids)){
				for(feuille_de_temps_id of article.feuille_de_temps_ids){
					$('#form_liste_articles').append('<input name="articles['+(ligne_pour_tableau)+'][feuille_de_temps_ids][]" value="'+feuille_de_temps_id+'">');
				}
			}

			ligne_pour_tableau++;

		});

		// on va chercher les marges par nature
		var composant = this;

		// on disable les champs des adresses
		$('.js_selection_adresse input').attr('disabled', true);
		$('.js_selection_adresse select').attr('disabled', true);

        $('.js_selection_element input').attr('disabled', true);
        $('.js_selection_element select').attr('disabled', true);
        $('.js_selection_element textarea').attr('disabled', true);

        $('#gestion_achats textarea').attr('disabled', true);
        $('#gestion_achats input').attr('disabled', true);
        $('#gestion_achats select').attr('disabled', true);

		if(que_les_lignes === true) {

			$.ajax({

				url: "{{ route('document.enregistrer_lignes', [$management->_type_element]) }}",
				dataType: "json",
				method: 'POST',
				data: $('#formulaire_saisie_document').serialize()
			}).done(function(donnees) {

				info("Lignes enregistrées avec succès");
				loading(false);
				return;

			});

		}
		else {

			var refs_formulaire = Object.keys(this.$refs).filter((ref) => ref.includes('formulaire'));

			if(refs_formulaire.length === 0) {
				loading(false);
				return false;
			}

			for(ref_formulaire of refs_formulaire) {

				var form = $('#formulaire_saisie_document');

				var informations = this.$refs[ref_formulaire].formulaire_donnees_renseignes({},form);

				var champ_non_remplis = this.$refs[ref_formulaire].verification_champs_obligatoires(informations);

				if(champ_non_remplis.length > 0) {

					var nom_champ_non_remplis = champ_non_remplis.map(function(champ_obligatoire){
						return champ_obligatoire.nom;
					});

					var erreur_affichage = '';

					if(champ_non_remplis.length == 1)
						erreur_affichage = this.$root.traduction('messages.php.champ_obligatoire')+nom_champ_non_remplis.join(', ');
					else
						erreur_affichage = this.$root.traduction('messages.php.champs_obligatoires')+nom_champ_non_remplis.join(', ');

					await erreur(erreur_affichage);

					this.$refs[ref_formulaire].indication_champs_obligatoires(champ_non_remplis,form);

					loading(false);
					return false;
				}
			}

			const formObject = {};
			const array = $('#formulaire_saisie_document').serializeArray();

			$.each(array, function() {
				const parts = this.name.replace(/\]/g, '').split('[');
				let current = formObject;

				for (let i = 0; i < parts.length; i++) {
					const part = parts[i];
					
					if (i === parts.length - 1) {
						current[part] = this.value;
					} else {
						if (!current[part]) {
							current[part] = {};
						}
						current = current[part];
					}
				}
			});

			var donnees = await $.ajax({

				url: "{{ route('document.test_enregistrer', [$management->_type_element]) }}",
				dataType: "json",
				method: 'POST',
				contentType: "application/json",
				data: JSON.stringify(formObject),
			});

			if(donnees.test_succes === true) {

				donnees = await $.ajax({
					url: "{{ route('document.enregistrer', [$management->_type_element]) }}",
					dataType: "json",
					method: 'POST',
					contentType: "application/json",
					data: JSON.stringify(formObject),
				});

				if(donnees.retour !== true){
					await erreur(donnees.erreur);
					return;
				}

				if(this.document.id != donnees.modele.id){
					document.location = 'eden/document/'+this.type_element+'/'+donnees.modele.id;
					return;
				}
				
				info(this.$root.traduction('messages.js.enregistrement_succes'));

				this.operations_document_post_modification(donnees);

				this.$emit('enregistrement_document');

				this.$forceUpdate();

				loading(false);

			} else {

				@if(!empty(moi_extranet()))
					this.$root.disable_champs_extranet = true;
				@endif
				await erreur(donnees.erreur);
				loading(false);
				return;
			}
		}

	},

	document_ajout_enregistre_lignes : function() {

		this.enregistre_document(true);
	},

	@if($management->existe())
		verification_annulation_partielle_document : function(){

			var articles = this.articles_du_document.filter(article_document => article_document.type_ligne == undefined);

			return new Promise((resolve, reject) => {
				$.post({
					url: "{{ route('document.verification_articles_supprimes') }}",
					dataType: "json",
					data: {
						articles_document: articles,
						type_element: '{!! $management->_type_element !!}',
						id_element: {!! $management->modele->id !!}
					}
				}).done((donnees) => {

					if (donnees.retour == true) {

						loading(false);
						this.modale_annulation_partielle = true;
						this.document.annule = 2;
						resolve(true);

					}

					resolve(false);

				});
			});

		},
	@endif

@endpush