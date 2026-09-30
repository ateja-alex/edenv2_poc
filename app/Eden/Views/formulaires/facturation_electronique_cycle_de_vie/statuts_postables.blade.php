<div class="row" v-if="facturation_electronique_cycle_de_vie.facturation_electronique_achat_id">
    <div class="col-sm-4">
        {!! management('facturation_electronique_cycle_de_vie')->champ('statut_achat')->nom_vue() !!}
    </div>
    <div class="col-sm-8">
        <div class="bloc_champ_formulaire formulaire_champ_statut_achat">
            <select v-if="!cycle_de_vie_enregistre" v-model="facturation_electronique_cycle_de_vie.statut_achat" name="statut_achat" class="form-control">
                <option v-for="statut in statuts_achat_postables" :key="statut.id_valeur" :value="statut.id_valeur" v-html="statut.valeur"></option>
            </select>
            <template v-else>
                <span v-html="libelle_statut(facturation_electronique_cycle_de_vie.statut_achat)"></span>
                <input type="hidden" name="statut_achat" :value="facturation_electronique_cycle_de_vie.statut_achat">
            </template>
            <div class="champ_obligatoire" v-if="!cycle_de_vie_enregistre">*</div>
        </div>
    </div>
</div>

<div class="row" v-if="facturation_electronique_cycle_de_vie.facture_vente_id || facturation_electronique_cycle_de_vie.avoir_vente_id">
    <div class="col-sm-4">
        {!! management('facturation_electronique_cycle_de_vie')->champ('statut_vente')->nom_vue() !!}
    </div>
    <div class="col-sm-8">
        <div class="bloc_champ_formulaire formulaire_champ_statut_vente">
            <select v-if="!cycle_de_vie_enregistre" v-model="facturation_electronique_cycle_de_vie.statut_vente" name="statut_vente" class="form-control">
                <option v-for="statut in statuts_vente_postables" :key="statut.id_valeur" :value="statut.id_valeur" v-html="statut.valeur"></option>
            </select>
            <template v-else>
                <span v-html="libelle_statut(facturation_electronique_cycle_de_vie.statut_vente)"></span>
                <input type="hidden" name="statut_vente" :value="facturation_electronique_cycle_de_vie.statut_vente">
            </template>
            <div class="champ_obligatoire" v-if="!cycle_de_vie_enregistre">*</div>
        </div>
    </div>
</div>

<div class="row" v-if="!cycle_de_vie_enregistre">
    <div class="col-sm-4">
        {!! management('facturation_electronique_cycle_de_vie')->champ('date_evenement')->nom_vue() !!}
    </div>
    <div class="col-sm-8">
        <div class="bloc_champ_formulaire formulaire_champ_date_evenement">
            <input type="date" v-model="facturation_electronique_cycle_de_vie.date_evenement" name="date_evenement" class="form-control">
        </div>
    </div>
</div>

<div class="row" v-if="motifs_du_statut.length > 0 || facturation_electronique_cycle_de_vie.motif_code">
    <div class="col-sm-4">
        {!! management('facturation_electronique_cycle_de_vie')->champ('motif_code')->nom_vue() !!}
    </div>
    <div class="col-sm-8">
        <div class="bloc_champ_formulaire formulaire_champ_motif_code">
            <select v-model="facturation_electronique_cycle_de_vie.motif_code" name="motif_code" class="form-control">
                <option value="">-</option>
                <option v-for="code in motifs_du_statut" :key="code" :value="code" v-html="$root.traduction('interface.facturation_electronique_cycle_de_vie.motif.' + code)"></option>
            </select>
            <div class="champ_obligatoire">*</div>
        </div>
    </div>
</div>

<div class="row" v-if="!cycle_de_vie_enregistre && facturation_electronique_cycle_de_vie.statut_vente == 212 && ventilation_encaissement.length > 0">
    <div class="col-sm-12">

        <table class="table css_ventilation_encaissement">
            <thead>
                <tr>
                    <th>@{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.taux_tva') }}</th>
                    <th class="alignement_right">@{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.montant_ttc_facture') }}</th>
                    <th class="alignement_right">@{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.deja_encaisse') }}</th>
                    <th class="alignement_right">@{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.reste_a_encaisser') }}</th>
                    <th class="alignement_right">@{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.a_encaisser') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="ligne in ventilation_encaissement" :key="ligne.taux">
                    <td>@{{ ligne.taux }} %</td>
                    <td class="alignement_right">@{{ montant_formate(ligne.ttc) }}</td>
                    <td class="alignement_right">@{{ montant_formate(ligne.deja_encaisse) }}</td>
                    <td class="alignement_right">@{{ montant_formate(ligne.reste) }}</td>
                    <td class="alignement_right">
                        <input type="number" step="0.01" min="0" :max="ligne.reste" class="form-control alignement_right"
                               v-model.number="ligne.montant" @input="controle_montant_encaissement(ligne)">
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="alignement_right">@{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.total_encaisse') }}</th>
                    <th class="alignement_right">@{{ montant_formate(total_encaissement) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="css_ventilation_encaissement_actions">
            <label>
                @{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.montant_global') }}
                <input type="number" step="0.01" min="0" :max="total_reste_a_encaisser" class="form-control" v-model.number="montant_global_encaissement">
            </label>
            <button type="button" class="btn btn-secondary btn-sm" @click="repartir_au_prorata()">
                @{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.repartir_au_prorata') }}
            </button>
            <button type="button" class="btn btn-secondary btn-sm" @click="encaisser_solde_total()">
                @{{ $root.traduction('interface.facturation_electronique_cycle_de_vie.encaisser_solde_total') }}
            </button>
        </div>

    </div>
</div>

<input type="hidden" v-if="facturation_electronique_cycle_de_vie.statut_vente == 212"
       name="encaissements_par_taux" :value="encaissements_par_taux_json">

@push('donnees_pour_vuejs_data')
    motifs_par_statut : @json(management('facturation_electronique_cycle_de_vie')->motifs_statut()),
    statuts_achat_postables : [],
    statuts_vente_postables : [],
    document_vente_nom_champ : null,
    document_vente_id : null,
    ventilation_encaissement : [],
    montant_global_encaissement : null,
    date_aujourdhui : '{{ date("Y-m-d") }}',
@endpush

@push('donnees_pour_vuejs_methods')

    montant_formate : function(montant){

        return (Math.round((montant ?? 0) * 100) / 100).toFixed(2) + ' €';
    },

    controle_montant_encaissement : function(ligne){

        if(ligne.montant === '' || ligne.montant === null || isNaN(ligne.montant)){

            ligne.montant = 0;
            return;
        }

        if(ligne.montant < 0)
            ligne.montant = 0;

        if(ligne.montant > ligne.reste)
            ligne.montant = ligne.reste;
    },

    encaisser_solde_total : function(){

        this.ventilation_encaissement.forEach(ligne => this.$set(ligne, 'montant', ligne.reste));

        this.montant_global_encaissement = this.total_reste_a_encaisser;
    },

    repartir_au_prorata : function(){

        var global = this.montant_global_encaissement ?? 0;
        var total_reste = this.total_reste_a_encaisser;

        if(global <= 0 || total_reste <= 0){

            this.ventilation_encaissement.forEach(ligne => this.$set(ligne, 'montant', 0));
            return;
        }

        if(global > total_reste)
            global = this.montant_global_encaissement = total_reste;

        var reparti = 0;

        this.ventilation_encaissement.forEach((ligne, index) => {

            var derniere_ligne = index == this.ventilation_encaissement.length - 1;

            // le reliquat d'arrondi va sur la dernière ligne pour que la somme soit exacte
            var montant = derniere_ligne
                ? Math.round((global - reparti) * 100) / 100
                : Math.round(global * (ligne.reste / total_reste) * 100) / 100;

            reparti += montant;

            this.$set(ligne, 'montant', Math.min(montant, ligne.reste));
        });
    },

    charge_ventilation_encaissement : async function(nom_champ, id_document){

        this.ventilation_encaissement = [];
        this.montant_global_encaissement = null;

        var type_element = nom_champ.replace(/_id$/, '');

        var totaux = await $.get({
            url: "{{ route('document.totaux', ['__TYPE__', '__ID__'], false) }}"
                .replace('__TYPE__', type_element).replace('__ID__', id_document),
            dataType: 'json',
        });

        var encaissements = await $.post({
            url: "{{ route('base_eden.element.recuperer_liste','facturation_electronique_cycle_de_vie') }}",
            data: {
                filtrage: [
                    {champ: nom_champ, condition: 'where', valeur: id_document},
                    {champ: 'statut_vente', condition: 'where', valeur: 212},
                ],
            },
            dataType: 'json',
        });

        var paiements = await $.post({
            url: "{{ route('base_eden.element.recuperer_liste','paiement') }}",
            data: {
                filtrage: [
                    {champ: 'type_element', condition: 'where', valeur: type_element},
                    {champ: 'id_document', condition: 'where', valeur: id_document},
                ],
            },
            dataType: 'json',
        });

        var dates_encaissements_declares = encaissements
            .filter(encaissement => encaissement.statut_envoi != 3)
            .map(encaissement => encaissement.date_evenement || encaissement.cree_le)
            .filter(date => date)
            .map(date => date.substring(0, 10))
            .sort();

        var derniere_date_encaissement_declare = dates_encaissements_declares[dates_encaissements_declares.length - 1];

        var paiements_non_declares = derniere_date_encaissement_declare
            ? paiements.filter(paiement => paiement.date && paiement.date.substring(0, 10) > derniere_date_encaissement_declare)
            : paiements;

        var dates_paiements = paiements_non_declares
            .map(paiement => paiement.date)
            .filter(date => date)
            .map(date => date.substring(0, 10))
            .sort();

        if(dates_paiements.length > 0)
            this.$set(this.facturation_electronique_cycle_de_vie, 'date_evenement', dates_paiements[dates_paiements.length - 1]);

        var par_tva = totaux.par_tva ?? {};

        var deja_encaisse = {};

        encaissements.forEach(encaissement => {

            if(encaissement.statut_envoi == 3)
                return;

            JSON.parse(encaissement.encaissements_par_taux || '[]').forEach(detail => {

                var taux = parseFloat(detail.taux);

                deja_encaisse[taux] = (deja_encaisse[taux] ?? 0) + parseFloat(detail.montant ?? 0);
            });
        });

        this.ventilation_encaissement = Object.keys(par_tva).map(taux => {

            var taux_arrondi = parseFloat(taux);
            var ttc = Math.round(par_tva[taux].ttc * 100) / 100;
            var encaisse = Math.round((deja_encaisse[taux_arrondi] ?? 0) * 100) / 100;
            var reste = Math.max(0, Math.round((ttc - encaisse) * 100) / 100);

            return {taux: taux_arrondi, ttc: ttc, deja_encaisse: encaisse, reste: reste, montant: reste};
        }).filter(ligne => ligne.ttc != 0);

        var somme_paiements = Math.round(paiements_non_declares.reduce((somme, paiement) => somme + parseFloat(paiement.montant ?? 0), 0) * 100) / 100;

        this.montant_global_encaissement = somme_paiements > 0
            ? Math.min(somme_paiements, this.total_reste_a_encaisser)
            : this.total_reste_a_encaisser;

        if(somme_paiements > 0)
            this.repartir_au_prorata();
    },

    libelle_statut : function(id_valeur){

        return (Object.values(this.$root.valeurs_listes_formatees[729] ?? []).find(statut => statut.id_valeur == id_valeur) ?? {}).valeur;
    },

    gestion_selection_document_cycle_de_vie : function(payload){

        if(payload.modele !== this.facturation_electronique_cycle_de_vie)
            return;

        var transitions_achat = {
            203: [204, 210],
            204: [205, 206, 207, 208],
            207: [205, 206, 209],
            208: [205, 206, 209],
            205: [209, 211],
            206: [209, 211],
        };

        var valeurs_729 = Object.values(this.$root.valeurs_listes_formatees[729] ?? []);

        if(payload.nom_champ == 'facturation_electronique_achat_id'){

            var statut_courant = payload.element.statut_facturation_electronique;
            var codes_postables = transitions_achat[statut_courant] ?? (statut_courant ? [] : transitions_achat[203]);

            this.statuts_achat_postables = valeurs_729.filter(statut => codes_postables.includes(statut.id_valeur));
        }
        else if(payload.nom_champ == 'facture_vente_id' || payload.nom_champ == 'avoir_vente_id'){

            this.statuts_vente_postables = valeurs_729.filter(statut => statut.id_valeur == 212);

            this.document_vente_nom_champ = payload.nom_champ;
            this.document_vente_id = payload.element.id;
        }
    },
    gestion_suppression_document_cycle_de_vie : function(payload){

        if(payload.modele !== this.facturation_electronique_cycle_de_vie)
            return;

        if(payload.nom_champ == 'facturation_electronique_achat_id')
            this.statuts_achat_postables = [];
        else if(payload.nom_champ == 'facture_vente_id' || payload.nom_champ == 'avoir_vente_id'){

            this.statuts_vente_postables = [];
            this.document_vente_nom_champ = null;
            this.document_vente_id = null;
            this.ventilation_encaissement = [];
            this.montant_global_encaissement = null;
        }
    },
    confirmation_enregistrement : async function(){

        if(this.facturation_electronique_cycle_de_vie.statut_vente != 212)
            return true;

        var depassement = this.ventilation_encaissement.find(ligne => (ligne.montant ?? 0) > ligne.reste);

        if(depassement){

            await erreur(this.$root.traduction('interface.facturation_electronique_cycle_de_vie.montant_superieur_au_reste'));

            return {retour : false};
        }

        if(this.total_encaissement <= 0){

            await erreur(this.$root.traduction('interface.facturation_electronique_cycle_de_vie.montant_encaisse_obligatoire'));

            return {retour : false};
        }

        return true;
    },
@endpush

@push('donnees_pour_vuejs_computed')

    total_encaissement : function(){

        return Math.round(this.ventilation_encaissement.reduce((total, ligne) => total + (parseFloat(ligne.montant) || 0), 0) * 100) / 100;
    },
    total_reste_a_encaisser : function(){

        return Math.round(this.ventilation_encaissement.reduce((total, ligne) => total + ligne.reste, 0) * 100) / 100;
    },
    encaissements_par_taux_json : function(){

        return JSON.stringify(
            this.ventilation_encaissement
                .filter(ligne => (parseFloat(ligne.montant) || 0) > 0)
                .map(ligne => ({taux: ligne.taux, montant: parseFloat(ligne.montant)}))
        );
    },
@endpush

@push('donnees_pour_vuejs_created')

    this.$root.$on('selection-element', this.gestion_selection_document_cycle_de_vie);
    this.$root.$on('suppression-selection-element', this.gestion_suppression_document_cycle_de_vie);
@endpush

@push('donnees_pour_vuejs_watch')

    'facturation_electronique_cycle_de_vie.statut_vente' : function(nouveau_statut){

        if(!this.cycle_de_vie_enregistre) {

            if(nouveau_statut == 212)
                this.charge_ventilation_encaissement(this.document_vente_nom_champ, this.document_vente_id);
            else
                this.$set(this.facturation_electronique_cycle_de_vie, 'date_evenement', this.date_aujourdhui);
        }
    },
@endpush

@push('donnees_pour_vuejs_computed')

    cycle_de_vie_enregistre : function(){

        return this.facturation_electronique_cycle_de_vie.id > 0;
    },

    motifs_du_statut : function(){

        var statut = Number(this.facturation_electronique_cycle_de_vie.statut_achat || this.facturation_electronique_cycle_de_vie.statut_vente);

        return this.motifs_par_statut[statut] ?? [];
    },
@endpush
