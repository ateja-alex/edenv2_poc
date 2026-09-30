<div class="bloc_selections_elements" v-if="lignes_selectionnes.length > 0 || elements_a_coller !== false">
    <div v-if="elements_a_coller !== false" class="elements_a_coller" :style="(lignes_selectionnes.length > 0 ? 'border-right: 1px solid lightgrey;' : '')">
        <span v-html="elements_a_coller.length +' '+traduction('document.blocs.saisie_des_articles.actions.elements_a_coller')"></span>
        <i class="fas fa-trash" @click="supprimer_copie" :title="traduction('document.blocs.saisie_des_articles.actions.supprimer_copie')" @click="supprimer_copie"></i>
        <i class="fas fa-paste" @click="coller_lignes(-1)" :title="traduction('document.blocs.saisie_des_articles.actions.coller')" @click="supprimer_copie"></i>
    </div>
    <div v-if="lignes_selectionnes.length > 0" class="selection">
        <span v-html="lignes_selectionnes.length +' '+traduction('document.blocs.saisie_des_articles.actions.elements_selectionnes')"></span>
        <i class="fas fa-trash" @click="supprimer_lignes" :title="traduction('document.blocs.saisie_des_articles.actions.supprimer')" @click="supprimer_lignes"></i>
        <i class="fas fa-copy" @click="copier_lignes" :title="traduction('document.blocs.saisie_des_articles.actions.copier')"></i>
        @if($management->_type_element == 'commande_achat')
            <div v-if="document.id > 0 && changement_suppression_manuelle_reliquat !== false" 
                class="suppression_manuelle_reliquat"
                :title="traduction('document.suppression_manuelle_reliquat.etat_'+changement_suppression_manuelle_reliquat)" 
                @click="suppression_manuelle_reliquat(lignes_selectionnes.map(l => articles_du_document[l]),changement_suppression_manuelle_reliquat, true)">
                <i class="fas fa-truck-loading"></i>
                <i class="fa fa-undo" v-if="changement_suppression_manuelle_reliquat == 0"></i>
                <i class="fa fa-times" v-else></i>
            </div>
        @endif
    </div>
</div>

@push('donnees_pour_vuejs_data')
    lignes_selectionnes : [],
    elements_a_coller : false,
@endpush

@push('donnees_pour_vuejs_mounted')

    @if(($management->existe() === false || ($management->existe() === true && $management->modele->valide != 1)) || $articles_modifiables === true)

        if(localStorage.elements_a_coller)
            this.elements_a_coller = JSON.parse(localStorage.elements_a_coller);

        $(window).focus(() => {

            if(localStorage.elements_a_coller)
                this.elements_a_coller = JSON.parse(localStorage.elements_a_coller);
            else
                this.elements_a_coller = false;
        });
    @endif
@endpush

@push('donnees_pour_vuejs_methods')

    selections_enfants_regroupement : function(regroupement_id, suppression){

        for(index_article in this.articles_du_document){

            article = this.articles_du_document[index_article];

            if(article.regroupement_id == regroupement_id){

                if(suppression)
                    this.lignes_selectionnes.splice(this.lignes_selectionnes.indexOf(index_article),1);
                else
                    this.lignes_selectionnes.push(index_article);

                if(article.type_ligne == 'regroupement')
                    this.selections_enfants_regroupement(article.id,suppression);
            }
        }
    },

    supprimer_lignes : async function(){

        if(!await confirm_eden())
            return;

        var index_lignes = this.lignes_selectionnes.sort((a, b) =>{
            return b - a;
        });

        for(index_article of index_lignes){

            var article = this.articles_du_document[index_article];

            if(article.type_ligne == 'regroupement')
                this.suppression_regroupement(article, index_article,false);
            else
                this.supprimer_article_du_document(article, index_article);
        }

        this.mise_a_jour_total_document_vue();
        this.$set(this,'lignes_selectionnes',[]);
    },

    copier_lignes : function(){

        var articles = [];

        for(ligne of this.lignes_selectionnes.sort((a, b) => a - b)){
            articles.push(this.articles_du_document[ligne]);
        }

        var selection_lignes_articles = this.selection_lignes_articles_informations_necessaires(articles);

        this.elements_a_coller = selection_lignes_articles;
        localStorage.elements_a_coller = JSON.stringify(selection_lignes_articles);
        toastr.success(vue_instance.traduction('messages.js.documents.selection_copiee'));
        this.lignes_selectionnes = [];
    },

    selection_lignes_articles_informations_necessaires(selection_lignes_articles){

        var champ_a_supprimer = [
            'chaine_affichage',
            'modifie_le',
            'cree_le',
            'cree_par',
            'modifie_par',
            'cle_externe',
            'chaine_tags_recherche',
            'ligne',
            'document_id',
            'type_element_source',
            'id_element_source',
            'id_ligne_source',
            'index_article',
            'nomenclature_ligne_parent'
        ];

        var selection_lignes_articles = structuredClone(selection_lignes_articles);

        for(article of selection_lignes_articles){

            for(champ of champ_a_supprimer){
                if(article[champ] !== undefined)
                    delete article[champ];
            }

            if(article.nomenclature != undefined && article.nomenclature.length > 0)
                article.nomenclature = this.selection_lignes_articles_informations_necessaires(article.nomenclature);

        }

        return selection_lignes_articles;
    },

    supprimer_copie : function(){

        this.elements_a_coller = false;
        delete localStorage.elements_a_coller;
    },

    coller_lignes : function(index_coller_ligne) {

        var elements_a_coller = structuredClone(this.elements_a_coller);

        var ligne_type_divers = this.articles_du_document.filter(ligne => ligne.type_ligne != undefined && ligne.type_ligne != "regroupement_fermeture" && ligne.type_ligne != "undefined" && ligne.type_ligne != null);

        var dernier_id = 0;

        if(ligne_type_divers.length != 0){

            ligne_type_divers.forEach(function(ligne, index){

                if(ligne.id != undefined && ligne.id != null && ligne.id != "undefined" && dernier_id < ligne.id)
                    dernier_id = ligne.id;

            })

        }

        var regroupement_id = null;

        if(index_coller_ligne != -1){

            var article_a_ajouter_apres = this.articles_du_document[index_coller_ligne];

            if(article_a_ajouter_apres.type_ligne == 'regroupement' && article_a_ajouter_apres.afficher_regroupement)
                regroupement_id = article_a_ajouter_apres.id;
            else if(article_a_ajouter_apres.regroupement_id > 0 && article_a_ajouter_apres.type_ligne != 'regroupement_fermeture')
                regroupement_id = article_a_ajouter_apres.id;

        }

        var regroupements_ids = [];

        for(element of elements_a_coller){

            if(element.type_ligne == 'regroupement'){

                dernier_id++;

                regroupements_ids.push(dernier_id);

                elements_a_coller.map((element_a_modifier) => {

                    if(element_a_modifier.regroupement_id == element.id)
                        element_a_modifier.regroupement_id = dernier_id;

                    return element_a_modifier;
                });

                element.id = dernier_id;
                element.id_temporaire = true;
            }
            else{
                delete element.id;

                if(!regroupements_ids.includes(element.regroupement_id))
                    delete element.regroupement_id;
            }

            if(element.regroupement_id == null && regroupement_id > 0)
                element.regroupement_id = regroupement_id;

            this.$set(element, 'affichage_nouvelle_ligne', true);

        }

        var articles = [];

        if(index_coller_ligne == -1)
            articles = elements_a_coller;

        for(index_article in this.articles_du_document){

            articles.push(this.articles_du_document[index_article]);

            if(index_article == index_coller_ligne)
                articles = articles.concat(elements_a_coller);
        }

        this.$set(this,'articles_du_document',articles);

        this.elements_a_coller = false;
        delete localStorage.elements_a_coller;

        this.changement_couleur_regroupement_chaque_article();
        this.mise_a_jour_total_document_vue();
        this.gestion_affichage_nouvelle_lignes();

    },
@endpush

@push('donnees_pour_vuejs_computed')

    changement_suppression_manuelle_reliquat : function(){

        if(this.lignes_selectionnes.map(l => this.articles_du_document[l])
            .filter(a => a.suppression_manuelle_reliquat == 1 || a.reliquat_reception == 0 || !a.reliquat_reception || a.id == null).length == 0)
            return 1;

        if(this.lignes_selectionnes.map(l => this.articles_du_document[l]).filter(a => a.suppression_manuelle_reliquat == 0 || a.suppression_manuelle_reliquat == null).length == 0)
            return 0;

        return false;
    },

@endpush