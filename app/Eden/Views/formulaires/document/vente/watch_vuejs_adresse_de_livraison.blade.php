@push('donnees_pour_vuejs_watch')

    'document.adresse_de_livraison' : function(nouvelle_valeur) {

        if(nouvelle_valeur == null)
            return;

        this.document.adresse_de_livraison_texte = null;
    },

    'document.adresse_de_livraison_texte' : function(nouvelle_valeur) {

        if(nouvelle_valeur == null)
            return;

        this.document.adresse_de_livraison = null;
    },
@endpush