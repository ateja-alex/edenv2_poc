@push('donnees_pour_vuejs_data')

    devises : {!! collect(modele('devise')->where('disponible','1')->get()) !!},
@endpush

@push('donnees_pour_vuejs_methods')
    calcul_montant_par_montant_devise(article_sur_document,article_parent = null,nomenclature_parent = null){

        var champ_montant = '{{fonctionnalite('documents_devise_etrangere_champ_conversion')}}';
        var champ_montant_devise = champ_montant + '_devise';
        this.corrige_virgule(article_sur_document,champ_montant_devise);

        if(this.document.devise !== this.devise_euro_id && !isNaN(this.document.taux_de_change) && this.document.taux_de_change > 0 && !isNaN(article_sur_document[champ_montant_devise])){
            article_sur_document[champ_montant] = (article_sur_document[champ_montant_devise] / this.document.taux_de_change).toString();
        }
        else{
            article_sur_document[champ_montant] = article_sur_document[champ_montant_devise];
        }

        if(champ_montant == 'prix_achat')
            this.modification_prix_achat(article_sur_document);
        else
            this.modification_prix_de_vente(article_sur_document);

        //GESTION DES NOMENCLATURES
        if(nomenclature_parent != null)
            this.calcul_montant_devise_par_montant(nomenclature_parent);

        if(article_parent != null)
            this.calcul_montant_devise_par_montant(article_parent);


    },
    calcul_montant_devise_par_montant(article_sur_document,champ_montant_modifier = false){

        var champ_montant = '{{fonctionnalite('documents_devise_etrangere_champ_conversion')}}';

        if(this.utilisation_devise_etrangere(true) === false || (champ_montant_modifier !== false && champ_montant_modifier != champ_montant))
            return;

        var champ_montant_devise = champ_montant + '_devise';

        if(this.document.devise !== this.devise_euro_id && !isNaN(this.document.taux_de_change) && this.document.taux_de_change > 0 && !isNaN(article_sur_document[champ_montant])){
            article_sur_document[champ_montant_devise] = (article_sur_document[champ_montant] * this.document.taux_de_change).toString();
        }
        else{
            article_sur_document[champ_montant_devise] = article_sur_document[champ_montant];
        }

    },
@endpush

@push('donnees_pour_vuejs_computed')
    devise_euro_id(){
        var devise_euro_id = 0;

        $.each(this.devises,function(index,devise){

            if(devise.code == '{!! maquette('devise_application_iso') !!}'){
                devise_euro_id = devise.id;
                return;
            }
        });

        return devise_euro_id.toString();
    },
    code_devise(){

        var vue_instance = this;

        var code_devise = '{!! maquette('devise_application_iso') !!}';

        $.each(this.devises,function(index,devise){

            if(vue_instance.document.devise == devise.id){
                code_devise = devise.code;
                return;
            }
        });

        if(code_devise != '')
            return code_devise;

        return '{!! maquette('devise_application_iso') !!}';
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    var vue_instance = this;

    $.each(vue_instance.articles_du_document,function(index,article_sur_document){

        if(article_sur_document.type_ligne == undefined){

            vue_instance.calcul_montant_devise_par_montant(article_sur_document);

            //GESTION DES NOMENCLATURES
            if(article_sur_document.nomenclature != undefined){

                $.each(article_sur_document.nomenclature,function(nomenclature_index,article_nomenclature){

                    vue_instance.calcul_montant_devise_par_montant(article_nomenclature);

                    if(article_nomenclature.nomenclature != undefined){

                        $.each(article_nomenclature.nomenclature,function(sous_nomenclature_index,sous_nomenclature){

                            vue_instance.calcul_montant_devise_par_montant(sous_nomenclature);
                        });

                    }
                });
            }

        }

    });

@endpush

@push('donnees_pour_vuejs_watch')
    'document.taux_de_change' : {
        handler: function() {
            var vue_instance = this;

            if(isNaN(vue_instance.document.taux_de_change) || vue_instance.document.taux_de_change <= 0)
                return false;

            vue_instance.corrige_virgule(this.document,'taux_de_change');

            var champ_montant = '{{fonctionnalite('documents_devise_etrangere_champ_conversion')}}';

            var champ_montant_devise = champ_montant + '_devise';

            $.each(vue_instance.articles_du_document,function(index,article_sur_document){

                if(article_sur_document.type_ligne == undefined){

                    //GESTION DES NOMENCLATURES
                    if(article_sur_document.nomenclature != undefined && article_sur_document.nomenclature.length > 0){

                        $.each(article_sur_document.nomenclature,function(nomenclature_index,article_nomenclature){

                            if(article_nomenclature.nomenclature != undefined && article_nomenclature.nomenclature.length > 0){

                                $.each(article_nomenclature.nomenclature,function(sous_nomenclature_index,sous_nomenclature){

                                    if(!isNaN(sous_nomenclature[champ_montant_devise]))
                                        vue_instance.calcul_montant_par_montant_devise(sous_nomenclature,article_sur_document,article_nomenclature);
                                });

                            }
                            else if(!isNaN(article_nomenclature[champ_montant_devise]))
                                vue_instance.calcul_montant_par_montant_devise(article_nomenclature,article_sur_document);
                        });
                    }
                    else if(!isNaN(article_sur_document[champ_montant_devise]))
                        vue_instance.calcul_montant_par_montant_devise(article_sur_document);
                }

            });
        },
        deep:true,
    },
@endpush