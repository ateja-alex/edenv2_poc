<div class="row">
    <div class="col-md-12">
        <div class="js_sticky_document_footer d-none d-sm-none d-md-block">
            <div class="row">
                <div class="col-sm-6 show_sticky">
                    <div :class="'bloc_'+information.id" v-for="information in informations_bandeau_bas">
                        <span>
                            @{{ information.nom }}
                        </span>
                        <span :class="information.id">
                            @{{ information.valeur }}
                        </span>
                    </div>
                </div>
                <div class="col-sm-6 hide_sticky">
                </div>
                <div class="col-sm-6" style="text-align: right">
                    @if($management->existe())
                        @if( (fonctionnalite('gescom_commande_vente_annulable_non_supprimable') != 'annulable_non_supprimable' ||$management->_type_element != "commande_vente" || $management->modele->valide != 1) && ((fonctionnalite('gescom_suppression_facture_valide') != 'empecher_suppression' || $management->_type_element != "facture_vente") || $management->modele->valide != 1))
                            <button @click="verification_suppression_document()" class="btn btn-danger btn_responsive_footer_document">@traduction('interface.modales.supprimer')</button>
                        @endif
                        <div class="btn-group dropup">
                            <button type="button" class="btn btn-default dropdown-toggle btn_responsive_footer_document" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                @traduction('interface.modales.dupliquer')
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('document.transformer_date', [$management->_type_element, $management->modele->id, $management->_type_element, date('Y-m-d')]) }}">@traduction('interface.document.dupliquer_avec_date_actuelle')</a>
                                <a class="dropdown-item" href="{{ route('document.transformer_date', [$management->_type_element, $management->modele->id, $management->_type_element, formate_date('Y-m-d', $management->modele->date ?? date('Y-m-d'))]) }}">@traduction('interface.document.dupliquer_avec_date_initiale')</a>
                                <!-- variantes -->
                                @if(fonctionnalite('gescom_variantes_devis') && $management->_type_element == 'devis_vente')
                                    <a class="dropdown-item" href="{{ route('document.transformer_variante', [$management->_type_element, $management->modele->id, $management->_type_element, date('Y-m-d'), $management->modele->id]) }}">@traduction('interface.document.dupliquer_variante')</a>
                                @endif
                            </div>
                        </div>

                    @endif
                    <div class="btn btn-primary" @click="enregistre_document_avec_verification();">@traduction('interface.modales.enregistrer')</div>
                </div>
            </div>
        </div>
        <div class="js_sticky_document_footer d-block d-sm-none">
            @if($management->existe())
                @if(fonctionnalite('gescom_commande_vente_annulable_non_supprimable') != 'annulable_non_supprimable' || $management->_type_element != "commande_vente" || $management->modele->valide != 1)
                    <a href="{{ route('document.supprimer', [$management->_type_element, $management->modele->id]) }}" class="btn btn-danger col-sm-12 btn_responsive_footer_document">@traduction('interface.modales.supprimer')</a>
                @endif
                <div class="btn-group dropup col-sm-12">
                    <button type="button" class="btn btn-default dropdown-toggle col-sm-12 btn_responsive_footer_document" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @traduction('interface.modales.dupliquer')
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('document.transformer_date', [$management->_type_element, $management->modele->id, $management->_type_element, date('Y-m-d')]) }}">@traduction('interface.document.dupliquer_avec_date_actuelle')</a>
                        <a class="dropdown-item" href="{{ route('document.transformer_date', [$management->_type_element, $management->modele->id, $management->_type_element, formate_date('Y-m-d', $management->modele->date ?? date('Y-m-d'))]) }}">@traduction('interface.document.dupliquer_avec_date_facture')</a>
                        <!-- variantes -->
                        @if(fonctionnalite('gescom_variantes_devis') && $management->_type_element == 'devis_vente')
                            <a class="dropdown-item" href="{{ route('document.transformer_variante', [$management->_type_element, $management->modele->id, $management->_type_element, date('Y-m-d'), $management->modele->id]) }}">@traduction('interface.document.dupliquer_variante')</a>
                        @endif
                    </div>
                </div>
            @endif
            <div class="btn btn-primary col-sm-12 btn_responsive_footer_document" @click="enregistre_document_avec_verification();">@traduction('interface.modales.enregistrer')</div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_computed')

	informations_bandeau_bas : function(){
		
		var informations_tmp = {!! collect(fonctionnalite('bandeau_bas_informations')) !!};

		var informations = [];

		var number_formater = new Intl.NumberFormat('fr-FR', 
			{ style: 'currency', currency: '{!! maquette('devise_application_iso') !!}' }
		);

		for(colonne in informations_tmp){

			if(informations_tmp[colonne])
				informations.push({
					id : colonne,
					nom: this.traduction('document.blocs.recap.'+colonne),
					valeur: this.totaux_affichage[colonne] ? number_formater.format(this.totaux_affichage[colonne]) : this['calcul_'+colonne] ?? '',
				});

		}

		return informations;
	},

	calcul_total_quantite : function(){

		articles = Object.values(this.articles_du_document).filter(article => !article.hasOwnProperty('type_ligne'));

		return articles.map(article => {
			if(article.quantite == undefined || article.quantite == null)
				return 0;

			return parseFloat(article.quantite);
		}).reduce((a, b) => a + b, 0);
	},

	calcul_nombre_articles : function(){

		articles = Object.values(this.articles_du_document).filter(article => !article.hasOwnProperty('type_ligne'));
		return articles.length;
	},

@endpush