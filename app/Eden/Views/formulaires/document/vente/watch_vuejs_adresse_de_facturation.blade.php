@push('donnees_pour_vuejs_watch')

    'document.adresse_de_facturation' : function(nouvelle_valeur) {

        if(nouvelle_valeur == null)
        return;

        this.document.adresse_de_facturation_texte = null;
    },

    'document.adresse_de_facturation_texte' : function(nouvelle_valeur) {

        if(nouvelle_valeur == null)
        return;

        this.document.adresse_de_facturation = null;
    },
@endpush