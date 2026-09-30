@if(!defined('include_maj_montant'))

    @php
        define('include_maj_montant', true);
    @endphp

    @push('donnees_pour_vuejs_data')
        articles_pour_note_de_frais : [],
        taux_de_tva : {},
    @endpush

    @push('donnees_pour_vuejs_mounted')

        $.get({

            url: "{{URL::to('eden/note_de_frais/informations')}}",
            dataType: "json",
            method: 'GET'
        }).done((donnees) => {

            this.articles_pour_note_de_frais = donnees.articles_pour_note_de_frais;
            this.taux_de_tva = donnees.taux_de_tva;

        });
    @endpush

    @push('donnees_pour_vuejs_methods')

        mise_a_jour_montant: function(){

            var taux_de_tva = {};

            if(typeof this.taux_de_tva == 'object'){

                for(valeur of Object.values(this.taux_de_tva)){
                    taux_de_tva[valeur.id] = valeur.code;
                }
            }

            //On calcule les montant globaux

            var total_ht = 0;
            var total_ht_devise = 0;
            var total_ttc = 0;
            var totaux_tva = {};

            this.note_de_frais.articles.forEach(function(ligne){

                var montant_ht = parseFloat(ligne.montant_ht);
                var montant_devise = parseFloat(ligne.montant_devise);
                var montant_ttc = parseFloat(ligne.montant_ttc);

                if(!isNaN(montant_ht)){

                    total_ht += parseFloat(montant_ht);
                    total_ht_devise += parseFloat(montant_devise);
                    total_ttc += montant_ttc;

                    if(totaux_tva[ligne.taux_tva] == undefined)
                        totaux_tva[ligne.taux_tva] = 0;

                    totaux_tva[ligne.taux_tva] += (montant_ttc - montant_ht);
                }

            });

            if(this.note_de_frais.ecart_gestion_ttc != 0 && !isNaN(this.note_de_frais.ecart_gestion_ttc))
                total_ttc = Math.round(total_ttc * 100,2)/100 + parseFloat(this.note_de_frais.ecart_gestion_ttc);

            this.$set(this.note_de_frais,'montant_ht',Math.round(total_ht * 100,2)/100);
            this.$set(this.note_de_frais,'montant_devise',Math.round(total_ht_devise * 100,2)/100);
            this.$set(this.note_de_frais,'montant_ttc',Math.round(total_ttc * 100,2)/100);

            this.note_de_frais.totaux_tva = {};

            for(cle_taux_tva in totaux_tva){

                if(taux_de_tva[cle_taux_tva] !== undefined)
                    this.note_de_frais.totaux_tva[taux_de_tva[cle_taux_tva]] = Math.round(totaux_tva[cle_taux_tva] * 100,2)/100;
            }
        },

    @endpush

@endif