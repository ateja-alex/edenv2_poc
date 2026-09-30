<div class="cellule_document_colonne_article" style="text-align: left;">
    <div class="css_label_input_article_document w-100">

        <span class="code_article_document css_input_article_document">
            @{{ article_sur_document.code_article }}
        </span>

        <input v-if="article_sur_document.new == true && {{ $edition_ligne }}" type="text" @if(!empty(moi_extranet())) :disabled="disable_champs_extranet"  @endif class="designation_article_document css_input_article_document js_focus" v-model="article_sur_document.designation">
        <input v-else-if="{{ $edition_ligne }}" type="text"  class="designation_article_document css_input_article_document" @if(!empty(moi_extranet())) :disabled="disable_champs_extranet"  @endif v-model="article_sur_document.designation">
        <span v-else class="css_lecture_ligne designation_article_document">@{{article_sur_document.designation}}</span>

        
    </div>
        
    <!-- le statut de la ligne -->
    <div v-if="document.valide == 1" style="display:flex;gap:5px;">
        @include('eden::formulaires.include.document.includes.colonnes.designation.statut_ligne')
    </div>
    
    <!-- nomenclature -->
    <template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">
        <div class="css_conteneur_input_nomenclature_document">

            <div class="css_saisie_articles_sur_document_input_group">

                @if(fonctionnalite('affichage_code_article_nomenclature') == true)
                    <span class="css_saisie_articles_sur_document_input_texte_gauche">@{{ article_nomenclature.code_article }}</span>
                @endif
                    <span v-if="article_nomenclature.type_article == 1" class="css_saisie_articles_sur_document_input_texte_gauche" title data-toggle="tooltip" data-placement="left" data-original-title="{{ management('article')->champ('type_article')->affiche(1) }}" style="background-color:#c37710;color:white;text-transform: uppercase;">{{ substr(management('article')->champ('type_article')->affiche(1),0,1) }}</span>
                    <span v-if="article_nomenclature.type_article == 3" class="css_saisie_articles_sur_document_input_texte_gauche" title data-toggle="tooltip" data-placement="left" data-original-title="{{ management('article')->champ('type_article')->affiche(3) }}" style="background-color:#1c69b1;color:white;text-transform: uppercase;">{{ substr(management('article')->champ('type_article')->affiche(3),0,1) }}</span>
                    <span v-if="article_nomenclature.modele.disponible_pour_saisie == 3" class="css_saisie_articles_sur_document_input_texte_gauche" title data-toggle="tooltip" data-placement="left" style="background-color:rgb(255,0,0);color:white;text-transform: uppercase;">@traduction('document.colonnes.designation.non_disponible')</span>
                    <span v-if="article_nomenclature.modele.inactif == 1" class="css_saisie_articles_sur_document_input_texte_gauche" title data-toggle="tooltip" data-placement="left" style="background-color:rgb(255,0,0);color:white;text-transform: uppercase;">@traduction('interface.valeurs_select.inactif')</span>

                    <input type="text" class="css_input_article_document css_saisie_articles_sur_document_input_input" style="color:#606060" v-model="article_nomenclature.designation" :class="{css_champ_readonly: contenu_produit_assemble(article_sur_document)}" :readonly="contenu_produit_assemble(article_sur_document)">
                @if($articles_modifiables === true && fonctionnalite('utiliser_conditionnement') && empty($recapitulatif))

                @endif
                @if(fonctionnalite('utiliser_conditionnement'))
                    @if($articles_modifiables === false && empty($recapitulatif))

                        <span class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document">Condi. <span style="margin:0 5px;">@{{ article_nomenclature.conditionnement_possible.filter(conditionnement => conditionnement.id == article_nomenclature.conditionnement)[0].conditionnement }}</span></span>

                    @endif
                @endif
                <span v-if="article_nomenclature.afficher_nomenclature && (article_nomenclature.type_article == 1 || article_nomenclature.type_article == 3)" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document" @click="$set(article_nomenclature,'afficher_nomenclature',false)"><i class="fas fa-arrow-up"></i></span>
                <span v-else-if="article_nomenclature.type_article == 1 || article_nomenclature.type_article == 3" @click="$set(article_nomenclature,'afficher_nomenclature',true)" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="fas fa-arrow-down"></i></span>
                <span @click="article_sur_document.nomenclature.splice(nomenclature_index, 1); calcule_tarif_nomenclature(article_sur_document);" v-if="!contenu_produit_assemble(article_sur_document)" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="far fa-trash-alt"></i></span>
                <span class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><a target="_blank" :href="'{{URL::to('eden/fiche/article')}}/'+article_nomenclature.article_id" class="far fa-eye css_lien_droite_nomenclature"></a></span>
                @if(fonctionnalite('calculateur_sur_document'))
                    <span v-if="article_nomenclature.calculateur != undefined && article_nomenclature.calculateur.resultat_calcul != undefined && article_nomenclature.calculateur.resultat_calcul != article_nomenclature.quantite" :title="traduction('document.colonnes.designation.afficher_calculateur')" style="background-color: yellow; color: white;" @click="afficher_calculateur(article_sur_document, nomenclature_index);" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="fas fa-calculator"></i></span>
                    <span v-else-if="article_nomenclature.calculateur && article_nomenclature.calculateur.erreur == false" :title="traduction('document.colonnes.designation.afficher_calculateur')" style="background-color: yellowgreen; color: white;" @click="afficher_calculateur(article_sur_document, nomenclature_index);" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="fas fa-calculator"></i></span>
                    <span v-else-if="article_nomenclature.calculateur && article_nomenclature.calculateur.erreur == true" :title="traduction('document.colonnes.designation.afficher_calculateur')" style="background-color: red; color: white;" @click="afficher_calculateur(article_sur_document, nomenclature_index);" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="fas fa-calculator"></i></span>
                    <span v-else @click="afficher_calculateur(article_sur_document, nomenclature_index);" :title="traduction('document.colonnes.designation.afficher_calculateur')" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="fas fa-calculator"></i></span>
                @endif

            </div>

            <template v-for="(sous_nomenclature,sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">

                <div class="css_conteneur_input_nomenclature_document" style="width:90%;margin-left:10%;">

                    <div class="css_saisie_articles_sur_document_input_group">

                        @if(fonctionnalite('affichage_code_article_nomenclature') == true)
                        <span class="css_saisie_articles_sur_document_input_texte_gauche">@{{ sous_nomenclature.code_article }}</span>
                        @endif
                        <input type="text" class="css_input_article_document css_saisie_articles_sur_document_input_input" style="color:#606060" v-model="sous_nomenclature.designation" :class="{css_champ_readonly: contenu_produit_assemble(article_nomenclature, article_sur_document)}" :readonly="contenu_produit_assemble(article_nomenclature, article_sur_document)">
                        @if($articles_modifiables === true && fonctionnalite('utiliser_conditionnement') && empty($recapitulatif))

                        @endif
                        @if(fonctionnalite('utiliser_conditionnement'))
                            @if($articles_modifiables === false && empty($recapitulatif))
                                <span class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document">@traduction('document.colonnes.designation.conditionnement')<span style="margin:0 5px;">@{{ sous_nomenclature.conditionnement_possible.filter(conditionnement => conditionnement.id == sous_nomenclature.conditionnement)[0].conditionnement }}</span></span>
                            @endif
                        @endif
                        <span @click="article_nomenclature.nomenclature.splice(sous_nomenclature_index, 1); calcule_tarif_nomenclature(article_nomenclature);calcule_tarif_nomenclature(article_sur_document);" v-if="!contenu_produit_assemble(article_nomenclature, article_sur_document)" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="far fa-trash-alt"></i></span>

                        <span class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><a target="_blank" :href="'{{URL::to('eden/fiche/article')}}/'+sous_nomenclature.article_enfant_id" class="far fa-eye css_lien_droite_nomenclature"></a></span>
                        @if(fonctionnalite('calculateur_sur_document'))
                            <span v-if="sous_nomenclature.calculateur != undefined && sous_nomenclature.calculateur.resultat_calcul != undefined && sous_nomenclature.calculateur.resultat_calcul != sous_nomenclature.quantite" :title="traduction('document.colonnes.designation.afficher_calculateur')" style="background-color: yellow; color: white;" @click="afficher_calculateur(article_sur_document, nomenclature_index, sous_nomenclature_index);" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="fas fa-calculator"></i></span>
                            <span v-else-if="sous_nomenclature.calculateur && sous_nomenclature.calculateur.erreur == false" :title="traduction('document.colonnes.designation.afficher_calculateur')" style="background-color: yellowgreen; color: white;" @click="afficher_calculateur(article_sur_document, nomenclature_index, sous_nomenclature_index);" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="fas fa-calculator"></i></span>
                            <span v-else-if="sous_nomenclature.calculateur && sous_nomenclature.calculateur.erreur == true" :title="traduction('document.colonnes.designation.afficher_calculateur')" style="background-color: red; color: white;" @click="afficher_calculateur(article_sur_document, nomenclature_index, sous_nomenclature_index);" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="fas fa-calculator"></i></span>
                            <span v-else @click="afficher_calculateur(article_sur_document, nomenclature_index, sous_nomenclature_index);" :title="traduction('document.colonnes.designation.afficher_calculateur')" class="css_saisie_articles_sur_document_input_texte_droite css_supprimer_ligne_supp_article_document"><i class="fas fa-calculator"></i></span>
                        @endif

                    </div>
                </div>
            </template>
            <span style="margin-left:10%;" class="css_ajouter_ligne_nomenclature" v-if="article_sur_document.modele && article_sur_document.modele.type_article && article_sur_document.modele.type_article==1 && article_nomenclature.type_article == 1 && article_nomenclature.afficher_nomenclature" @click="ajout_article_par_modale({nomenclature_parent : article_sur_document.index_article, sous_nomenclature_parent : nomenclature_index},'nomenclature');">@traduction('document.colonnes.designation.ajouter_ligne')</span>
        </div>
    </template>
    <span class="css_ajouter_ligne_nomenclature" v-if="article_sur_document.modele && article_sur_document.modele.type_article && article_sur_document.modele.type_article==1 && article_sur_document.afficher_nomenclature" @click="ajout_article_par_modale({nomenclature_parent : article_sur_document.index_article},'nomenclature');">@traduction('document.colonnes.designation.ajouter_ligne')</span>

    @if(isset($colonnes_articles['designation']['sous_colonnes']) && empty(moi_extranet()))
        @foreach($colonnes_articles['designation']['sous_colonnes'] as $nom_sous_colonne => $sous_colonne)
            <div @if(!empty($colonne['masquer'])) style="display: none" @endif>
                @include('eden::formulaires.include.document.includes.colonnes.designation.'.$nom_sous_colonne)     
            </div>
        @endforeach
    @endif
    
</div>
