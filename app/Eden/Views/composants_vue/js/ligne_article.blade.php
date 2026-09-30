<script>

const racine_du_tableau_articles = function(instance) {

    var racine = instance.$parent;

    while(racine && racine.$options.methods && racine.$options.methods.grid_template_columns === undefined)
        racine = racine.$parent;

    return racine ? racine : instance.$root;
};

const preparer_relais_ligne_article = function(instance) {

    var prototype = Object.getPrototypeOf(instance);

    if(prototype.hasOwnProperty('_relais_prets'))
        return;

    var racine = racine_du_tableau_articles(instance);

    var deja_defini = function(nom) {
        return nom in prototype || Object.prototype.hasOwnProperty.call(instance, nom);
    };

    Object.keys(racine.$options.methods || {}).forEach(function(nom){

        if(deja_defini(nom))
            return;

        prototype[nom] = racine[nom].bind(racine);
    });

    var expose = function(nom){

        if(deja_defini(nom))
            return;

        Object.defineProperty(prototype, nom, {
            get: function(){ return racine[nom]; },
            set: function(valeur){ racine[nom] = valeur; },
            configurable: true,
        });
    };

    Object.keys(racine.$data || {}).forEach(expose);
    Object.keys(racine.$options.computed || {}).forEach(expose);
    Object.keys(racine.$props || {}).forEach(expose);

    prototype._relais_prets = true;
};

const options_ligne_article = {

    props: {
        article_sur_document: {},
        article_index: {},
        edition: {
            default: false,
        },
    },

    created: function() {

        preparer_relais_ligne_article(this);
    },
};

Vue.component('ligne-article-saisie', Object.assign({ template: '#tpl_ligne_article_saisie' }, options_ligne_article));
Vue.component('ligne-article-recap', Object.assign({ template: '#tpl_ligne_article_recap' }, options_ligne_article));

</script>
