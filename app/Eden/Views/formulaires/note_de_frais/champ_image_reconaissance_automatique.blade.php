<div :class="'row reconaissance_automatique ' + (detection_reussie ? 'detection_reussie' : '')">
    <div class="col-sm-2">
        <span>{!! management('note_de_frais')->champ('scan')->nom() !!}</span>
    </div>
    <div class="col-sm-4">
        {!! management('note_de_frais')->champ('scan')->attr('mode_compact', true, 1)->cree() !!}
    </div>
    <div class="col-sm-6" v-if="note_de_frais.scan != '' && note_de_frais.scan != null">
        <span class="css_champ_file_comp" style="padding: 12px;cursor: pointer;border-radius: 5px;" @click="reconaissance_automatique">
            @traduction('messages.js.note_de_frais.reconnaissance_automatique.titre')
        </span>
    </div>
    <div v-if="!this.$root.intranet && this.sous_formulaires_retraite?.note_de_frais_sous_formulaire_note_de_frais_lignes == null" v-show="false">
        <template v-for="(article,index) in note_de_frais.articles">
            <input type="text" v-for="(valeur,champ) in article" :name="'articles['+index+']['+champ+']'" :value="valeur">
        </template>
        <input type="number" v-model="note_de_frais.montant_ht" name="montant_ht" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
        <input type="number" v-model="note_de_frais.montant_ttc" name="montant_ttc" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
    </div>
</div>

@include('eden::formulaires.note_de_frais.include.maj_montant')

@push('donnees_pour_vuejs_data')
    detection_reussie: false,
@endpush

@push('donnees_pour_vuejs_methods')

    reconaissance_automatique : async function(){

        if(this.note_de_frais.articles !== undefined && this.note_de_frais.articles.length > 0){

            var confirmation = await confirm_eden(this.$root.traduction('messages.js.note_de_frais.reconnaissance_automatique.lignes_deja_renseignes'));

            if(!confirmation)
                return false;
        }

        this.$set(this.note_de_frais,'articles',[]);

        var fichiers = $('.reconaissance_automatique input[type="file"]').prop('files');

        if (!fichiers) {
            return false;
        }

        var liste_formatees = {};

        for(valeur of Object.values(this.$root.valeurs_listes_formatees[615])){
            liste_formatees[valeur.id_valeur] = valeur.valeur;
        }

        var article_note_de_frais = {};

        if(this.articles_pour_note_de_frais != undefined){

            for(valeur of Object.values(this.articles_pour_note_de_frais)){

                if(liste_formatees[valeur.categorie_depense_mindee] != undefined){
                    var categorie_mindee = liste_formatees[valeur.categorie_depense_mindee];

                    article_note_de_frais[categorie_mindee] = valeur.id;
                }
            }
        }

        var taux_de_tva = {};
        var taux_de_tva_defaut = null;

        if(this.taux_de_tva){

            for(valeur of Object.values(this.taux_de_tva)){

                if(valeur.taux_tva_defaut == true)
                    taux_de_tva_defaut = valeur.id;

                taux_de_tva[parseFloat(valeur.taux)] = valeur.id;
            }
        }

        loading(true);

        var utilisation_devise_etrangere = this.utilisation_devise_etrangere && this.utilisation_devise_etrangere(true);

        for(fichier of fichiers){

            var donnees = new FormData();
            donnees.append("document", fichier, fichier.name);

            await $.post({
                url: 'https://api.mindee.net/v1/products/mindee/expense_receipts/v5/predict',
                headers: {
                    "Authorization":"Token {{fonctionnalite('mindee_api_key')}}",
                },
                data: donnees,
                dataType: 'json',
                contentType: false,
                processData: false
            }).done((retour) => {

                if(retour.api_request.status_code != 201)
                    toastr.error(this.$root.traduction('messages.js.note_de_frais.reconnaissance_automatique.erreur',null,[fichier.name]));

                else{

                    var prediction = retour.document.inference.prediction;

                    var detection_reussie = false;

                    var date = prediction.date.value;
                    var heure = prediction.time.value;
                    var categorie = prediction.category.value;
                    var calcul_ecart_gestion = true;

                    if(date != null){

                        date = new Date(Date.parse(date));

                        mois = date.getMonth() + 1;

                        mois = (mois < 10 ? '0' : '') + mois;
                        jour = (date.getDate() < 10 ? '0' : '') + date.getDate();

                        this.note_de_frais.date = date.getFullYear() +'-'+mois+'-'+jour;
                    }

                    if(utilisation_devise_etrangere && prediction.locale.currency != '{!! maquette('devise_application_iso') !!}'){

                        for(devise of this.devises){

                            if(devise.code == prediction.locale.currency){

                                this.note_de_frais.devise = devise.id;
                                break;
                            }
                        }
                    }
                
                    if(heure != null){
                    
                        this.note_de_frais.heure = heure;
                        heure = heure.split(':');

                        if(categorie === 'food' && ((heure[0] == 18 && heure[1] >= 30) || heure[0] > 18))
                            categorie = 'food dinner';
                    }

                    var article_id = null;

                    if(article_note_de_frais[categorie] != undefined)
                        article_id = article_note_de_frais[categorie];

                    for(taxe of prediction.taxes){

                        var article = {
                            article_id : article_id
                        };

                        var valeur_tva = taxe.value;
                        var taux_tva = taxe.rate;

                        if(taux_de_tva[taux_tva] != undefined)
                            article.taux_tva = taux_de_tva[taux_tva];
                        else if(taux_de_tva_defaut != undefined){

                            toastr.error(this.$root.traduction('messages.js.note_de_frais.reconnaissance_automatique.taux_tva_defaut',null,[taux_tva, parseFloat(this.taux_de_tva[taux_de_tva_defaut].taux)]));
                            article.taux_tva = taux_de_tva_defaut;
                            calcul_ecart_gestion = false;
                        }

                        if(article.taux_tva == undefined){
                            toastr.error(this.$root.traduction('messages.js.note_de_frais.reconnaissance_automatique.taux_tva_inconnu',null,[taux_tva]));
                            calcul_ecart_gestion = false;
                        }
                        else{

                            if(this.utilisation_devise_etrangere && this.utilisation_devise_etrangere()){

                                if(taxe.base != null)
                                    article.montant_devise = taxe.base;
                                else
                                    article.montant_devise = Math.round((valeur_tva*100)/taux_tva * 100,2)/100;

                                article.montant_ht = article.montant_devise * this.note_de_frais.taux_de_change;

                                article.montant_ttc = Math.round((article.montant_ht * (100 + taux_tva)),2)/100;
                            }
                            else{

                                if(taxe.base != null)
                                    article.montant_ht = taxe.base;
                                else
                                    article.montant_ht = Math.round((valeur_tva*100)/taux_tva * 100,2)/100;

                                article.montant_ttc = Math.round((article.montant_ht + valeur_tva )* 100,2)/100;
                            }

                            this.note_de_frais.articles.push(article);
                            detection_reussie = true;
                        }
                    }

                    if(detection_reussie == false && prediction.total_amount.value > 0){

                        var article = {
                            article_id : article_id
                        };

                        if(this.utilisation_devise_etrangere && this.utilisation_devise_etrangere()){
                            article.montant_devise = prediction.total_amount.value;
                            article.montant_ht = article.montant_devise * this.note_de_frais.taux_de_change;
                            article.montant_ttc = article.montant_ht;
                        }
                        else{
                            article.montant_ht = prediction.total_amount.value;
                            article.montant_ttc = prediction.total_amount.value;
                        }

                        if(taux_de_tva[0] != undefined)
                            article.taux_tva = taux_de_tva[0];
                        else if(taux_de_tva_defaut != undefined){

                            toastr.error(this.$root.traduction('messages.js.note_de_frais.reconnaissance_automatique.taux_tva_defaut',null,[0, 0]));
                            article.taux_tva = taux_de_tva_defaut;
                        }

                        if(article.taux_tva == undefined)
                            toastr.error(this.$root.traduction('messages.js.note_de_frais.reconnaissance_automatique.taux_tva_inconnu',null,[0]));
                        else{
                            this.note_de_frais.articles.push(article);
                            detection_reussie = true;
                        }
                    }

                    this.mise_a_jour_montant();

                    if(this.sous_formulaires_retraite?.note_de_frais_sous_formulaire_note_de_frais_lignes != undefined)
                        this.ajout_donnees_sous_formulaire();

                    if(prediction.total_amount.value != this.note_de_frais.montant_ttc && calcul_ecart_gestion){
                        var ecart_gestion = Math.round((prediction.total_amount.value - this.note_de_frais.montant_ttc) * 100,2)/100;

                        if(ecart_gestion > {!! fonctionnalite('seuil_ecart_gestion_ttc') !!})
                            alerte_eden(this.$root.traduction('messages.js.note_de_frais.reconnaissance_automatique.ecart_montant_trop_elevee'));
                        else{
                            this.note_de_frais.ecart_gestion_ttc = ecart_gestion;
                            this.note_de_frais.montant_ttc += this.note_de_frais.ecart_gestion_ttc;
                        }
                    }

                    this.detection_reussie = detection_reussie;
                    if(detection_reussie)
                        toastr.success(this.$root.traduction('messages.js.note_de_frais.reconnaissance_automatique.reconnaissance_effectuee'));
                    else
                        toastr.error(this.$root.traduction('messages.js.note_de_frais.reconnaissance_automatique.erreur',null,[fichier.name]));

                }
            });
        }

        loading(false);
    },

    ajout_donnees_sous_formulaire: async function(){

        this.$set(this.sous_formulaires_par_type_element,'note_de_frais_lignes',[]);

        for(index_article in this.note_de_frais.articles){

            var article = this.note_de_frais.articles[index_article];
            
            await this.ajout_sous_formulaire_optionnel(this.sous_formulaires_retraite.note_de_frais_sous_formulaire_note_de_frais_lignes, 'note_de_frais');

            for(champ of Object.keys(article)){
                this.note_de_frais['note_de_frais_lignes'+(index_article != 0 ? '_'+index_article : '')][champ] = article[champ];
            }
        }
    },

@endpush

@push('donnees_pour_vuejs_watch')

    'note_de_frais.scan': function (nouvelle_valeur, ancienne_valeur) {

        if(!nouvelle_valeur)
            this.detection_reussie = false;
        else if(!(this.note_de_frais.id > 0))
            this.reconaissance_automatique();

    },

@endpush