@extends('eden::templates.template')

@section('title') Contrôle du cache @stop

@section('content')

    <div class="content-wrapper controle_cache">
        <div id="base-content" class="container-fluid">

            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="card-header"><h4>Contrôle du cache</h4></div>
                </div>
                <div class="col-md-4 text-right">
                    <button class="btn btn-secondary" :disabled="cache_chargement || cache_action" @click="charger_cache()">Actualiser</button>
                </div>
            </div>

            <div class="row" v-if="!cache_chargement">
                <div class="col-md-12">
                    <div class="css_cache_etat">
                        <span :class="cache_etat.actif ? 'css_cache_pastille_ok' : 'css_cache_pastille_ko'"></span>
                        <span v-text="cache_etat.actif ? 'Cache actif' : 'Cache désactivé (EDENPME_CACHE)'"></span>
                        <span class="css_cache_sep">·</span>
                        <span v-text="'store ' + cache_etat.driver"></span>
                        <span class="css_cache_sep">·</span>
                        <span v-text="'sessions ' + cache_etat.session"></span>
                        <span class="css_cache_sep">·</span>
                        <span v-text="cache_etat.invalide_le ? 'dernière invalidation le ' + cache_etat.invalide_le : 'jamais invalidé'"></span>
                        <span class="css_cache_sep">·</span>
                        <span v-text="cache_sondes + ' clés sondées, ' + cache_nb_cles + ' trouvées en ' + cache_duree + ' ms'"></span>
                    </div>
                </div>
            </div>

            <div class="row" v-if="cache_chargement">
                <div class="col-md-12 css_cache_attente">Analyse du cache en cours…</div>
            </div>

            <template v-if="!cache_chargement">

                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" :class="{active: cache_onglet == 'applicatif'}" href="javascript:;"
                                   @click="changer_onglet('applicatif')" style="color:#495057 !important;">Cache applicatif</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{active: cache_onglet == 'session'}" href="javascript:;"
                                   @click="changer_onglet('session')" style="color:#495057 !important;">Cache session</a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- ================= ONGLET CACHE APPLICATIF ================= --}}
                <div class="css_cache_onglet" v-show="cache_onglet == 'applicatif'">

                    <p class="css_cache_note">Identique pour tous les utilisateurs : définitions de tables et de champs, traductions, triggers, workflows.</p>

                    <div class="row css_cache_tuiles">
                        <div class="col-md-4">
                            <div class="css_cache_tuile">
                                <div class="css_cache_tuile_valeur" v-text="poids_lisible(cache_total_partage)"></div>
                                <div class="css_cache_tuile_libelle">Poids en cache</div>
                                <div class="css_cache_tuile_detail" v-text="cache_nb_cles + ' clé(s) sur ' + cache_partage.length + ' espace(s)'"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="css_cache_tuile">
                                <div class="css_cache_tuile_valeur" v-text="poids_lisible(cache_disque.poids)"></div>
                                <div class="css_cache_tuile_libelle">Sur disque</div>
                                <div class="css_cache_tuile_detail" v-text="cache_disque.nb + ' fichier(s)'"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="css_cache_tuile" :class="cache_orphelins > 0 ? 'css_cache_tuile_alerte' : ''">
                                <div class="css_cache_tuile_valeur" v-text="poids_lisible(cache_orphelins)"></div>
                                <div class="css_cache_tuile_libelle">Orphelins</div>
                                <div class="css_cache_tuile_detail">Sur disque sans clé connue</div>
                            </div>
                        </div>
                    </div>

                    <div class="row css_cache_actions">
                        <div class="col-md-12">
                            <button class="btn btn-danger" :disabled="cache_action" @click="vider_cache('partage')">Vider le cache applicatif</button>
                            <span class="css_cache_action_message" v-if="cache_message" v-text="cache_message"></span>
                        </div>
                    </div>

                    <div class="row" v-if="cache_partage.length > 1">
                        <div class="col-md-12"><div class="css_cache_bloc"><div id="graphique_cache_partage"></div></div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="css_cache_bloc">
                                <table class="table table-sm css_cache_table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Espace de noms</th>
                                            <th class="text-right">Clés</th>
                                            <th class="text-right">Poids</th>
                                            <th class="text-right">Part</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="ligne in cache_partage">
                                            <tr :key="ligne.espace">
                                                <td v-text="ligne.espace"></td>
                                                <td class="text-right" v-text="ligne.nb"></td>
                                                <td class="text-right" v-text="poids_lisible(ligne.poids)"></td>
                                                <td class="text-right" v-text="part_lisible(ligne.poids, cache_total_partage)"></td>
                                                <td class="text-right css_cache_actions_ligne">
                                                    <button class="btn btn-sm btn-secondary" @click="basculer_detail(ligne.espace)"
                                                            v-text="cache_detail === ligne.espace ? 'Masquer' : 'Détail'"></button>
                                                    <button class="btn btn-sm btn-danger" :disabled="cache_action" @click="vider_cache('espace', ligne.espace)">Vider</button>
                                                </td>
                                            </tr>
                                            <tr :key="ligne.espace + '_detail'" v-if="cache_detail === ligne.espace" class="css_cache_ligne_detail">
                                                <td colspan="5">
                                                    <div v-if="cache_detail_chargement">Chargement des clés…</div>
                                                    <table class="table table-sm mb-0" v-if="!cache_detail_chargement">
                                                        <thead>
                                                            <tr><th>Clé</th><th class="text-right">Entrées</th><th class="text-right">Poids</th></tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="detail in cache_detail_lignes" :key="detail.cle">
                                                                <td v-text="detail.cle"></td>
                                                                <td class="text-right" v-text="detail.entrees === null ? '—' : detail.entrees"></td>
                                                                <td class="text-right" v-text="poids_lisible(detail.poids)"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr v-if="cache_partage.length === 0"><td colspan="5">Le cache applicatif est vide.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ================= ONGLET CACHE SESSION ================= --}}
                <div class="css_cache_onglet" v-show="cache_onglet == 'session'">

                    <p class="css_cache_note">Propre à chaque utilisateur : droits, maquette, formulaires rendus. Le vidage passe par un bump de version, chaque session le prend en compte à sa requête suivante — personne n'est déconnecté.</p>

                    <div class="row css_cache_tuiles">
                        <div class="col-md-3">
                            <div class="css_cache_tuile">
                                <div class="css_cache_tuile_valeur" v-text="poids_lisible(cache_total_sessions)"></div>
                                <div class="css_cache_tuile_libelle">Poids en cache</div>
                                <div class="css_cache_tuile_detail" v-text="'sur ' + poids_lisible(cache_total_poids_sessions) + ' de sessions'"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="css_cache_tuile">
                                <div class="css_cache_tuile_valeur" v-text="cache_sessions.length"></div>
                                <div class="css_cache_tuile_libelle">Sessions ouvertes</div>
                                <div class="css_cache_tuile_detail" v-text="cache_nb_internes + ' interne(s), ' + cache_nb_extranet + ' extranet, ' + cache_nb_anonymes + ' anonyme(s)'"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="css_cache_tuile" :class="cache_nb_perimees > 0 ? 'css_cache_tuile_alerte' : ''">
                                <div class="css_cache_tuile_valeur" v-text="cache_nb_perimees"></div>
                                <div class="css_cache_tuile_libelle">Sessions périmées</div>
                                <div class="css_cache_tuile_detail">Elles rechargeront leur cache</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="css_cache_tuile">
                                <div class="css_cache_tuile_valeur" v-text="poids_lisible(cache_session_max)"></div>
                                <div class="css_cache_tuile_libelle">Session la plus lourde</div>
                                <div class="css_cache_tuile_detail" v-text="cache_sessions.length ? cache_sessions[0].utilisateur : '—'"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row css_cache_actions">
                        <div class="col-md-12">
                            <button class="btn btn-danger" :disabled="cache_action" @click="vider_cache('sessions')">Vider les caches session</button>
                            <button class="btn btn-danger" :disabled="cache_action" @click="vider_cache('tout')">Vider tout (applicatif + sessions)</button>
                            <span class="css_cache_action_message" v-if="cache_message" v-text="cache_message"></span>
                        </div>
                    </div>

                    <div class="row" v-if="cache_sessions.length > 1">
                        <div class="col-md-12"><div class="css_cache_bloc"><div id="graphique_cache_sessions"></div></div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="css_cache_bloc">
                                <table class="table table-sm css_cache_table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Session</th>
                                            <th>Utilisateur</th>
                                            <th>Type</th>
                                            <th>Langue</th>
                                            <th class="text-right">Poids</th>
                                            <th class="text-right">dont cache</th>
                                            <th class="text-right">Espaces</th>
                                            <th class="text-center">À jour</th>
                                            <th class="text-right">Dernière activité</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="session in cache_sessions">
                                            <tr :key="session.id">
                                                <td><code v-text="session.id"></code></td>
                                                <td>
                                                    <span v-text="session.utilisateur"></span>
                                                    <span class="css_cache_muet" v-if="session.id_utilisateur" v-text="' #' + session.id_utilisateur"></span>
                                                </td>
                                                <td v-text="session.type"></td>
                                                <td v-text="session.langue"></td>
                                                <td class="text-right" v-text="poids_lisible(session.poids)"></td>
                                                <td class="text-right" v-text="poids_lisible(session.poids_cache) + ' (' + part_lisible(session.poids_cache, session.poids) + ')'"></td>
                                                <td class="text-right" v-text="session.nb_espaces"></td>
                                                <td class="text-center">
                                                    <span :class="session.a_jour ? 'css_cache_pastille_ok' : 'css_cache_pastille_ko'"></span>
                                                    <span class="css_cache_muet" v-text="session.a_jour ? 'oui' : 'non'"></span>
                                                </td>
                                                <td class="text-right">
                                                    <span v-text="session.activite"></span>
                                                    <div class="css_cache_muet" v-text="'il y a ' + session.inactif_depuis"></div>
                                                </td>
                                                <td class="text-right">
                                                    <button class="btn btn-sm btn-secondary" :disabled="session.nb_espaces === 0"
                                                            @click="basculer_detail_session(session.id)"
                                                            v-text="cache_detail_session === session.id ? 'Masquer' : 'Détail'"></button>
                                                </td>
                                            </tr>
                                            <tr :key="session.id + '_detail'" v-if="cache_detail_session === session.id" class="css_cache_ligne_detail">
                                                <td colspan="10">
                                                    <table class="table table-sm mb-0">
                                                        <thead>
                                                            <tr><th>Espace de noms en session</th><th class="text-right">Poids</th><th class="text-right">Part du cache</th></tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="espace in session.espaces" :key="espace.espace">
                                                                <td v-text="espace.espace"></td>
                                                                <td class="text-right" v-text="poids_lisible(espace.poids)"></td>
                                                                <td class="text-right" v-text="part_lisible(espace.poids, session.poids_cache)"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr v-if="cache_sessions.length === 0"><td colspan="10">Aucune session ouverte.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </template>

        </div>
    </div>

@stop

@push('link_styles')
<style>
    .controle_cache .css_cache_etat { background: #ffffff; border: 1px solid #eeeeec; border-radius: 6px; padding: .5rem .9rem; margin-bottom: 1rem; color: #52514e; font-size: .85rem; }
    .controle_cache .css_cache_sep { color: #c9c9c4; margin: 0 .4rem; }
    .controle_cache .css_cache_pastille_ok,
    .controle_cache .css_cache_pastille_ko { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: .45rem; vertical-align: middle; }
    .controle_cache .css_cache_pastille_ok { background: #1baf7a; }
    .controle_cache .css_cache_pastille_ko { background: #e34948; }
    .controle_cache .css_cache_onglet { background: #ffffff; border: 1px solid #dee2e6; border-top: 0; border-radius: 0 0 6px 6px; padding: 1.25rem; }
    .controle_cache .nav-tabs { border-bottom: 1px solid #dee2e6; }
    .controle_cache .nav-tabs .nav-link { background: #f8f9fa; border-color: #dee2e6; }
    .controle_cache .nav-tabs .nav-link.active { background: #ffffff; border-bottom-color: #ffffff; font-weight: 600; }
    .controle_cache .css_cache_tuiles { margin-bottom: 1rem; }
    .controle_cache .css_cache_tuile { background: #ffffff; border: 1px solid #eeeeec; border-radius: 6px; padding: 1rem 1.25rem; height: 100%; }
    .controle_cache .css_cache_tuile_alerte { border-color: #eda100; }
    .controle_cache .css_cache_tuile_valeur { font-size: 1.75rem; font-weight: 600; color: #0b0b0b; line-height: 1.2; }
    .controle_cache .css_cache_tuile_libelle { color: #52514e; }
    .controle_cache .css_cache_tuile_detail { color: #8a8a85; font-size: .85rem; margin-top: .25rem; }
    .controle_cache .css_cache_bloc { background: #ffffff; border: 1px solid #eeeeec; border-radius: 6px; padding: .75rem; margin-bottom: 1rem; }
    .controle_cache .css_cache_actions { margin-bottom: 1rem; }
    .controle_cache .css_cache_actions .btn { margin-right: .5rem; }
    .controle_cache .css_cache_action_message { color: #1baf7a; font-size: .9rem; }
    .controle_cache .css_cache_note { color: #8a8a85; font-size: .85rem; margin: 0 0 1rem; }
    .controle_cache .css_cache_attente { color: #52514e; padding: 2rem 0; }
    .controle_cache .css_cache_muet { color: #8a8a85; font-size: .8rem; }
    .controle_cache .css_cache_table td, .controle_cache .css_cache_table th { border-color: #eeeeec; vertical-align: middle; }
    .controle_cache .css_cache_actions_ligne .btn { margin-left: .35rem; }
    .controle_cache .css_cache_ligne_detail > td { background: #ffffff; }
</style>
@endpush

@push('donnees_pour_vuejs_data')

    cache_onglet: 'applicatif',
    cache_partage: [],
    cache_sessions: [],
    cache_disque: { nb: 0, poids: 0 },
    cache_etat: { actif: true, driver: '', session: '', invalide_le: null },
    cache_sondes: 0,
    cache_duree: 0,
    cache_chargement: true,
    cache_action: false,
    cache_message: null,
    cache_detail: null,
    cache_detail_lignes: [],
    cache_detail_chargement: false,
    cache_detail_session: null,

@endpush

@push('donnees_pour_vuejs_computed')

    cache_total_partage(){
        return this.cache_partage.reduce((total, ligne) => total + ligne.poids, 0);
    },
    cache_total_sessions(){
        return this.cache_sessions.reduce((total, ligne) => total + ligne.poids_cache, 0);
    },
    cache_total_poids_sessions(){
        return this.cache_sessions.reduce((total, ligne) => total + ligne.poids, 0);
    },
    cache_nb_cles(){
        return this.cache_partage.reduce((total, ligne) => total + ligne.nb, 0);
    },
    cache_orphelins(){
        return Math.max(0, this.cache_disque.poids - this.cache_total_partage);
    },
    cache_nb_perimees(){
        return this.cache_sessions.filter(s => !s.a_jour).length;
    },
    cache_nb_internes(){
        return this.cache_sessions.filter(s => s.type === 'Interne').length;
    },
    cache_nb_extranet(){
        return this.cache_sessions.filter(s => s.type === 'Extranet').length;
    },
    cache_nb_anonymes(){
        return this.cache_sessions.filter(s => s.type === 'Anonyme').length;
    },
    cache_session_max(){
        return this.cache_sessions.length ? this.cache_sessions[0].poids : 0;
    },

@endpush

@push('donnees_pour_vuejs_methods')

    poids_lisible(octets){

        if(!octets) return '0 o';
        if(octets < 1024) return octets + ' o';
        if(octets < 1024 * 1024) return (octets / 1024).toFixed(1).replace('.', ',') + ' Ko';

        return (octets / 1024 / 1024).toFixed(2).replace('.', ',') + ' Mo';
    },

    part_lisible(valeur, total){

        if(!total) return '—';

        return Math.round(valeur / total * 100) + ' %';
    },

    changer_onglet(onglet){

        this.cache_onglet = onglet;

        this.$nextTick(() => this.dessiner_graphiques());
    },

    async charger_cache(){

        this.cache_chargement = true;
        this.cache_detail = null;
        this.cache_detail_session = null;

        try {
            var retour = await $.ajax({
                url: "{{ route('maintenance.cache_donnees') }}",
                dataType: 'json',
                method: 'GET'
            });

            this.cache_partage = retour.partage;
            this.cache_sessions = retour.sessions;
            this.cache_disque = retour.disque;
            this.cache_etat = retour.etat;
            this.cache_sondes = retour.sondes;
            this.cache_duree = retour.duree;
        }
        finally {
            this.cache_chargement = false;
        }

        this.$nextTick(() => this.dessiner_graphiques());
    },

    async basculer_detail(espace){

        if(this.cache_detail === espace) {

            this.cache_detail = null;
            return;
        }

        this.cache_detail = espace;
        this.cache_detail_lignes = [];
        this.cache_detail_chargement = true;

        try {
            this.cache_detail_lignes = await $.ajax({
                url: "{{ route('maintenance.cache_espace') }}",
                dataType: 'json',
                method: 'GET',
                data: { espace: espace }
            });
        }
        finally {
            this.cache_detail_chargement = false;
        }
    },

    basculer_detail_session(id){

        this.cache_detail_session = this.cache_detail_session === id ? null : id;
    },

    async vider_cache(portee, espace){

        this.cache_action = true;
        this.cache_message = null;

        try {
            await $.ajax({
                url: "{{ route('maintenance.cache_vider') }}",
                dataType: 'json',
                method: 'POST',
                data: { portee: portee, espace: espace || null }
            });

            this.cache_message = portee === 'espace' ? 'Espace ' + espace + ' vidé.' : 'Cache vidé.';
        }
        finally {
            this.cache_action = false;
        }

        await this.charger_cache();
    },

    graphique_cache(conteneur, lignes, libelle){

        if(document.getElementById(conteneur) === null || lignes.length < 2)
            return;

        Highcharts.chart(conteneur, {
            chart: {
                type: 'bar',
                height: Math.max(200, lignes.length * 34 + 70),
                backgroundColor: '#ffffff',
                style: { fontFamily: 'inherit' }
            },
            title: { text: null },
            credits: { enabled: false },
            legend: { enabled: false },
            exporting: { enabled: false },
            xAxis: {
                categories: lignes.map(l => l.libelle),
                lineWidth: 0,
                tickLength: 0,
                labels: { style: { color: '#52514e', fontSize: '12px' } }
            },
            yAxis: {
                title: { text: null },
                tickAmount: 5,
                gridLineColor: '#eeeeec',
                gridLineDashStyle: 'Dot',
                labels: {
                    style: { color: '#8a8a85', fontSize: '11px' },
                    formatter: function(){
                        if(this.value >= 1024 * 1024) return Math.round(this.value / 1024 / 1024) + ' Mo';
                        return Math.round(this.value / 1024) + ' Ko';
                    }
                }
            },
            tooltip: {
                backgroundColor: '#ffffff',
                borderColor: '#eeeeec',
                borderRadius: 6,
                shadow: false,
                style: { color: '#0b0b0b' },
                formatter: function(){
                    return '<b>' + this.key + '</b><br>' + (this.y / 1024).toFixed(1).replace('.', ',') + ' Ko';
                }
            },
            plotOptions: {
                bar: {
                    color: '#2a78d6',
                    borderWidth: 0,
                    borderRadius: 4,
                    pointWidth: 18,
                    dataLabels: {
                        enabled: true,
                        style: { color: '#52514e', fontWeight: 'normal', fontSize: '11px', textOutline: 'none' },
                        formatter: function(){
                            if(this.y >= 1024 * 1024) return (this.y / 1024 / 1024).toFixed(2).replace('.', ',') + ' Mo';
                            return (this.y / 1024).toFixed(1).replace('.', ',') + ' Ko';
                        }
                    }
                }
            },
            series: [{ name: libelle, data: lignes.map(l => l.poids) }]
        });
    },

    dessiner_graphiques(){

        if(this.cache_onglet === 'applicatif')
            this.graphique_cache(
                'graphique_cache_partage',
                this.cache_partage.map(l => ({ libelle: l.espace, poids: l.poids })),
                'Poids en cache'
            );

        if(this.cache_onglet === 'session')
            this.graphique_cache(
                'graphique_cache_sessions',
                this.cache_sessions.map(l => ({ libelle: l.utilisateur, poids: l.poids_cache })),
                'Poids du cache en session'
            );
    },

@endpush

@push('donnees_pour_vuejs_mounted')

    this.charger_cache();

@endpush
