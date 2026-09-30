<script>
    const champ_signature = Vue.component('champ-signature', {
        template: `<div class="champ_signature">

            <div class="champ_signature_apercu" v-if="valeur" @click="ouvrir_consultation()">
                <img
                    :src="source_apercu"
                    :class="'css_img_champ_signature css_img_champ_signature_' + type_element + '_' + nom_sql"
                    :style="hauteur_apercu !== null ? 'height:' + hauteur_apercu + 'px;' : ''"
                />
            </div>

            <input type="hidden" :name="name" :value="valeur" />

            <div class="champ_signature_actions">
                <a
                    v-if="valeur"
                    :href="source_apercu"
                    :download="nom_fichier_telechargement"
                    target="_blank"
                    class="btn btn-secondary btn-sm champ_signature_bouton_telecharger"
                    :title="$root.traduction('composant.champ_signature.telecharger')"
                >
                    <i class="fas fa-download"></i>
                </a>
                <a href="javascript:;" class="btn btn-secondary btn-sm champ_signature_bouton_signer" v-if="!lecture_seule" @click="ouvrir_signature()">
                    <i class="fas fa-signature"></i>
                    <span v-text="valeur ? $root.traduction('composant.champ_signature.re_signer') : $root.traduction('composant.champ_signature.signer')"></span>
                </a>
                <i
                    class="fa fa-times champ_signature_supprimer"
                    v-if="valeur && !lecture_seule"
                    @click="supprimer()"
                    :title="$root.traduction('composant.champ_signature.supprimer')"
                ></i>
            </div>

            <div class="signature_overlay" v-if="mode_modale !== null">
                <div class="signature_modale">
                    <div class="signature_modale_entete">
                        <span v-text="titre"></span>
                        <i class="fa fa-times signature_modale_fermer" @click="fermer()"></i>
                    </div>
                    <div class="signature_modale_corps">
                        <canvas v-if="mode_modale == 'signature'" ref="canvas" class="signature_canvas"></canvas>
                        <div class="signature_consultation" v-else>
                            <img :src="source_apercu" class="signature_consultation_image" />
                        </div>
                    </div>
                    <div class="signature_actions">
                        <template v-if="mode_modale == 'signature'">
                            <a href="javascript:;" class="btn btn-secondary" @click="annuler_dernier_trait()" v-text="$root.traduction('composant.champ_signature.annuler_dernier_trait')"></a>
                            <a href="javascript:;" class="btn btn-secondary" @click="effacer()" v-text="$root.traduction('composant.champ_signature.effacer')"></a>
                            <a href="javascript:;" class="btn btn-danger" @click="fermer()" v-text="$root.traduction('composant.champ_signature.fermer')"></a>
                            <a href="javascript:;" class="btn btn-primary" :class="{ desactive: enregistrement_en_cours }" @click="valider()" v-text="$root.traduction('composant.champ_signature.valider')"></a>
                        </template>
                        <template v-else>
                            <a
                                :href="source_apercu"
                                :download="nom_fichier_telechargement"
                                target="_blank"
                                class="btn btn-secondary"
                                v-text="$root.traduction('composant.champ_signature.telecharger')"
                            ></a>
                            <a href="javascript:;" class="btn btn-primary" v-if="!lecture_seule" @click="ouvrir_signature()" v-text="$root.traduction('composant.champ_signature.re_signer')"></a>
                            <a href="javascript:;" class="btn btn-secondary" @click="fermer()" v-text="$root.traduction('composant.champ_signature.fermer')"></a>
                        </template>
                    </div>
                </div>
            </div>
        </div>`,
        props: {

            modele: {},
            nom_sql: '',
            name: '',
            type_element: '',
            lecture_seule: {
                type: Boolean | Number,
                default: false,
            },
            hauteur_apercu: {
                type: Number,
                default: null,
            },
        },
        data: function() {
            return {
                pad: null,
                mode_modale: null,
                enregistrement_en_cours: false,
                gestionnaire_redimensionnement: null,
            }
        },
        computed: {

            valeur: function() {

                if(this.modele === undefined || this.modele === null)
                    return '';

                return this.modele[this.nom_sql] || '';
            },

            source_apercu: function() {

                if(this.si_externe(this.valeur) || this.si_storage(this.valeur))
                    return this.valeur;

                return '/storage/' + this.valeur;
            },

            nom_fichier_telechargement: function() {

                if(!this.valeur)
                    return '';

                var extension = 'png';

                var position_point = this.valeur.lastIndexOf('.');

                if(position_point > -1 && position_point > this.valeur.lastIndexOf('/'))
                    extension = this.valeur.substring(position_point + 1).split('?')[0];

                return 'signature_' + this.type_element + '_' + this.nom_sql + '.' + extension;
            },

            titre: function() {

                if(this.nom_sql == null)
                    return this.$root.traduction('composant.champ_signature.signer');

                return this.$root.traduction('champs_libres.' + this.type_element + '.' + this.nom_sql + '.nom');
            },
        },
        methods: {

            si_storage(valeur) {

                if(valeur === null || valeur === undefined)
                    return false;

                return valeur.indexOf('storage/') > -1;
            },

            si_externe(valeur) {

                if(valeur === null || valeur === undefined)
                    return false;

                return valeur.indexOf('http://') > -1 || valeur.indexOf('https://') > -1;
            },

            ouvrir_consultation() {

                if(!this.valeur)
                    return;

                this.detruire_pad();

                this.mode_modale = 'consultation';
            },

            ouvrir_signature() {

                if(this.lecture_seule)
                    return;

                this.detruire_pad();

                this.mode_modale = 'signature';

                this.$nextTick(() => this.initialiser_pad());
            },

            initialiser_pad() {

                var canvas = this.$refs.canvas;

                if(canvas === undefined)
                    return;

                this.redimensionner_canvas();

                this.pad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 0)',
                    penColor: '#1c2b36',
                    minWidth: 0.7,
                    maxWidth: 2.6,
                });

                this.gestionnaire_redimensionnement = () => this.redimensionner_canvas(true);

                window.addEventListener('resize', this.gestionnaire_redimensionnement);
                window.addEventListener('orientationchange', this.gestionnaire_redimensionnement);
            },

            redimensionner_canvas(conserver_trace) {

                var canvas = this.$refs.canvas;

                if(canvas === undefined)
                    return;

                var trace = (conserver_trace && this.pad !== null) ? this.pad.toData() : null;

                var ratio = Math.max(window.devicePixelRatio || 1, 1);

                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;

                var contexte = canvas.getContext('2d');
                contexte.setTransform(1, 0, 0, 1, 0, 0);
                contexte.scale(ratio, ratio);

                if(this.pad === null)
                    return;

                this.pad.clear();

                if(trace !== null && trace.length > 0)
                    this.pad.fromData(trace);
            },

            effacer() {

                if(this.pad !== null)
                    this.pad.clear();
            },

            annuler_dernier_trait() {

                if(this.pad === null)
                    return;

                var trace = this.pad.toData();

                if(trace.length === 0)
                    return;

                trace.pop();

                this.pad.fromData(trace);
            },

            valider() {

                if(this.pad === null || this.enregistrement_en_cours)
                    return;

                if(this.pad.isEmpty()){
                    toastr.error(this.$root.traduction('composant.champ_signature.signature_vide'));
                    return;
                }

                this.enregistrement_en_cours = true;

                this.$refs.canvas.toBlob((blob) => this.envoyer(blob), 'image/png');
            },

            envoyer(blob) {

                var vue_contexte = this;

                var formData = new FormData();

                formData.append('image', blob, 'signature_' + this.id_random() + '.png');

                var xhr = new XMLHttpRequest();
                xhr.responseType = 'json';

                xhr.open('POST', "{{ route('bibliotheque.upload_fichier', [], false) }}", true);

                xhr.onload = () => {

                    vue_contexte.enregistrement_en_cours = false;

                    if(xhr.status !== 200){
                        toastr.error(vue_contexte.$root.traduction('composant.champ_signature.erreur_inattendue'));
                        return;
                    }

                    if(xhr.response.erreur){
                        toastr.error(xhr.response.message);
                        return;
                    }

                    vue_contexte.modele[vue_contexte.nom_sql] = xhr.response.lien_fichier;

                    vue_contexte.fermer();
                };

                xhr.onerror = () => {
                    vue_contexte.enregistrement_en_cours = false;
                    toastr.error(vue_contexte.$root.traduction('composant.champ_signature.erreur_inattendue'));
                };

                xhr.send(formData);
            },

            async supprimer() {

                if(this.lecture_seule)
                    return false;

                if(!await confirm_eden(this.$root.traduction('composant.champ_signature.confirmation_suppression')))
                    return false;

                this.modele[this.nom_sql] = '';
            },

            detruire_pad() {

                if(this.gestionnaire_redimensionnement !== null){
                    window.removeEventListener('resize', this.gestionnaire_redimensionnement);
                    window.removeEventListener('orientationchange', this.gestionnaire_redimensionnement);
                    this.gestionnaire_redimensionnement = null;
                }

                if(this.pad !== null){
                    this.pad.off();
                    this.pad = null;
                }
            },

            fermer() {

                this.detruire_pad();

                this.enregistrement_en_cours = false;

                this.mode_modale = null;
            },

            id_random: function() {

                length = 15;

                var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

                if (! length) {
                    length = Math.floor(Math.random() * chars.length);
                }

                var str = '';
                for (var i = 0; i < length; i++) {
                    str += chars[Math.floor(Math.random() * chars.length)];
                }

                return str;
            },
        },
        beforeDestroy: function() {

            this.fermer();
        },
    });
</script>
