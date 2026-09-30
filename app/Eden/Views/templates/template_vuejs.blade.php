@push('donnees_pour_vuejs_data')
    cache_formulaires: {},
    cache_sous_formulaires: {},
    cache_liste_utilisateurs: {},
    cache_liste_familles: {},
    variables_dates : false,
@endpush

@push('donnees_pour_vuejs_methods')

    recuperer_valeur_liste_formatee: function(id_liste_formatee,id_valeur){

        var liste_formatee = null;

        $.each(this.valeurs_listes_formatees[id_liste_formatee],function(index,liste){

            if(liste.id_valeur == id_valeur)
                liste_formatee = liste;
        });

        return liste_formatee;
    },
    recuperer_valeur_liste_libre: function(id_liste_libre,id_valeur){

        var liste_libre = null;

        if(this.valeurs_listes_libres[id_liste_libre] == undefined)
            return '';

        $.each(this.valeurs_listes_libres[id_liste_libre].sans_categorie,function(index,liste){

            if(liste.id_valeur == id_valeur)
                liste_libre = liste;
        });

        return liste_libre;
    },

    valeur_titre_champ: function(modele, champ){

        if(champ.type == 6 && modele){

            var tempDivElement = document.createElement("div");

            tempDivElement.innerHTML = modele.replaceAll(/[@#{};:/*!.()]/gm, "").toString();

            return tempDivElement.textContent || tempDivElement.innerText || this.$root.traduction(champ.index_traduction + '.nom');
        }
        else if([-2,-1,0,4,5].includes(champ.type) && modele != '' && modele != null)
            return modele;
        else if(champ.type == 1 && modele != '' && modele != null){

            valeur = this.recuperer_valeur_liste_libre(champ.liste_choix != undefined && champ.liste_choix != 0 ? champ.liste_choix : champ.id_cl, modele);

            if(valeur != undefined)
                return valeur.valeur;
        }
        else if([2,3,14,17].includes(champ.type) && !isNaN(modele) && modele != null && modele != '')
            return modele;
        else if(champ.type == 20 && modele != null && modele != ''){

            valeur = this.recuperer_valeur_liste_formatee(champ.liste_choix, modele);

            if(valeur != undefined)
                return valeur.valeur;
        }
        else if([7,9,10,11,12,13,15,16,21,22,42].includes(champ.type))
            return '';

        return this.$root.traduction(champ.index_traduction + '.nom');
    },

    formate_date : function(date){

        var annee = date.getFullYear();
        var mois = (date.getMonth()+1).toString();

        if(mois.length == 1)
            mois = '0' + mois;

        var jour = date.getDate().toString();

        if(jour.length == 1)
            jour = '0' + jour;

        return {
            fr : jour + '/' + mois + '/' + annee,
            en : annee + '-' + mois + '-' + jour
        };
    },
@endpush

@push('donnees_pour_vuejs_computed')

    aujourdhui : function() {

        var aujourdhui = new Date();

        var mois = aujourdhui.getMonth() + 1;

        mois = (mois < 10 ? '0' : '') + mois;
        var jour = (aujourdhui.getDate() < 10 ? '0' : '') + aujourdhui.getDate();

        return aujourdhui.getFullYear()+'-'+mois+'-'+jour;

    },
@endpush
