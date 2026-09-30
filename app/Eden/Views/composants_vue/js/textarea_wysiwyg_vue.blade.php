<script>
const textarea_wysiwyg_vue = Vue.component('textarea-wysiwyg-vue', {
    template: `<div style="width: 100%;">
        <textarea :disabled="lecture_seule" :id="id_dynamique" :name="name" :value="modele[nom_sql]"></textarea>
        <div v-show="objet_tinymce == null" style="position: absolute;top: 0;">
            <img style="width: 60px;" src="{{'eden/images/ajax_loader.gif'}}">
        </div>
    </div>
    `,
    props: {
        modele: {},
        nom_sql: '',
        name: '',
        plugins: {
            default: function () {
                var plugins = [
                    'advlist','autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
                    'emoticons', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'directionality',
                    'mention', 'autoresize','ai'
                ]
                @if(fonctionnalite('scribens_activation'))
                    plugins.push('scribensplugin');
                @endif
                @if(fonctionnalite('open_ai_activation'))
                    plugins.push('ai');
                @endif
                return plugins;
            }, type: Array
        },
        gestion_mise_a_jour_valeur:{
            type: String,
            default: 'Change KeyUp Undo Redo',
        },
        fontsize_formats:{
            type: String,
            default: '8pt 10pt 12pt 14pt 18pt 24pt 36pt 72pt',
        },
        toolbar: {
            default: function () {
                var retour = 'undo redo | styles | fontsize | bold italic forecolor backcolor';
                
                @if(fonctionnalite('open_ai_activation'))
                    retour += " ai";
                @endif
                
                retour += ' | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image';
                
                @if(fonctionnalite('scribens_activation'))
                    retour+= " | scribensplugin";
                @endif
                return retour;
            }, type: String

        },
        lecture_seule : {
            type: Boolean | Number,
            default: false,
        }
    },
    data: function () {
        return {
            objet_tinymce: null,
            extension_type_accepte: {!! collect(\App\Eden\Variables::extension_fichier_accepte()) !!},
        }
    },

    computed: {
        id_dynamique: function () {
            return 'editor-' + this.guidGenerator();
        },
    },
    watch : {
        'lecture_seule' : {

            handler : function() {

                if(this.objet_tinymce != null)
                    this.objet_tinymce.mode.set(this.lecture_seule ? 'readonly' : 'design');

            },
            deep:true
        },
    },

    mounted: async function () {

        var instance = this;

        var tinymce_existant = tinymce.get(this.id_dynamique);

        if(tinymce_existant != null)
            tinymce_existant.destroy();

        await this.$nextTick();

        const composant_visible = () => {
            for (let el = instance.$el; el; el = el.parentElement) {
                const style = window.getComputedStyle(el);
                if (style.display === 'none') 
                    return false;
            }
            return true;
        };

        const attendre_composant_visible = (callback) => {
            if(composant_visible()){
                callback();
            }else{
                const observer = new MutationObserver(() => {
                    if(composant_visible()){
                        observer.disconnect();
                        callback();
                    }
                });
                observer.observe(document.body, { attributes: true, childList: true, subtree: true });
            }
        };

        attendre_composant_visible(() => {
            instance.initialisation_editeur();

            instance.$watch('modele.'+instance.nom_sql,function(){

                var tinymce_existant = tinymce.get(instance.id_dynamique);

                if(tinymce_existant == null) 
                    instance.initialisation_editeur();

                var valeur = instance.modele[this.nom_sql];

                if(valeur == null || valeur == undefined)
                    valeur = '';

                if (valeur !== instance.objet_tinymce.getContent()){
                    instance.objet_tinymce.setContent(valeur);
                }
            });
        });
    },

    methods: {
        guidGenerator: function () {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }

            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                s4() + '-' + s4() + s4() + s4();
        },
        mise_a_jour_valeur: function (valeur) {
            this.modele[this.nom_sql] = valeur;
        },
        initialisation_editeur: async function(){

            var instance = this;

            var selecteur = document.getElementById(this.id_dynamique);

            var offset_defaut = document.querySelector('.fil_ariane')?.getBoundingClientRect().bottom ?? document.querySelector('#mainNav')?.getBoundingClientRect().bottom ?? 0;

            var formulaire = selecteur.closest('.css_bloc_formulaire_fiche');

            var formulaire_libre = selecteur.closest('.formulaire-libre-sur-fiche');

            var modale = selecteur.closest('.modal-content');

            var toolbar_offset = formulaire != null && formulaire.contains(selecteur) ? offset_defaut + formulaire.querySelector(':scope > .card-header')?.getBoundingClientRect().height
                                : formulaire_libre != null && formulaire_libre.contains(selecteur) ? offset_defaut + formulaire_libre.querySelector(':scope > .card-header')?.getBoundingClientRect().height    
                                : modale != null && modale.contains(selecteur) ? modale.querySelector(':scope > .modal-header')?.getBoundingClientRect().bottom
                                : offset_defaut;

            var initialisation_options = {
                selector:'textarea#'+this.id_dynamique,
                toolbar: this.toolbar,
                plugins: this.plugins,
                removed_menuitems: 'newdocument',
                font_size_formats: this.fontsize_formats,
                relative_urls : false,
                remove_script_host : false,
                readonly : this.lecture_seule ? true : false,
                file_picker_callback: this.upload_fichier,
                resize:true,
                min_height: 180,
                license_key: 'gpl',
                language: 'fr_FR',
                promotion: false,
                autoresize_bottom_margin: 15,
                toolbar_sticky: true,
                toolbar_sticky_offset: toolbar_offset,
                ui_mode: modale != null && modale.contains(selecteur) ? 'split' : 'combined',
                setup: (editor) => {
                    @if(fonctionnalite('scribens_activation'))
                        editor.options.register('scribens', {
                            processor: 'object',
                            default: {
                                api_key: "{{ fonctionnalite("scribens_api_key") }}",
                                lang: instance.$root.moi.langue
                            }
                        });
                    @endif
                    editor.on('drop', function (e) {
                        const file = e.dataTransfer.files[0];
                        e.preventDefault();
                        
                        var reader = new FileReader();
                        reader.onload = async function () {
                            
                            const xhr = new XMLHttpRequest();
                            xhr.withCredentials = false;
                            xhr.responseType = 'json';

                            var url = "{{ route('bibliotheque.upload_fichier', [], false) }}";
                            var nom_input = "image";

                            if(instance.$root.element_id && instance.$root.type_element){
                                url = "/eden/fiche/"+instance.$root.type_element+"/"+instance.$root.element_id+"/post/ajoute_piece_jointe"
                                nom_input = "piece_jointe";
                            }

                            xhr.open('POST', url, true);

                            xhr.onload = () => {
                                if (xhr.status === 403) {
                                    toastr.error(instance.$root.traduction('composant.champ_file.erreur_inattendue'));
                                    return;
                                }

                                if (xhr.status < 200 || xhr.status >= 300) {
                                    toastr.error(instance.$root.traduction('composant.champ_file.erreur_inattendue'));
                                    return;
                                }

                                if(xhr.response.erreur){
                                    toastr.error(xhr.response.message);
                                    return;
                                }

                                var lien_fichier = xhr.response.lien_fichier ?? xhr.response.chemin;

                                if (file && ['image/bmp','image/gif','image/vnd.microsoft.icon',
                                            'image/jpeg','image/png','image/svg+xml',
                                            'image/tiff','image/webp'].includes(file.type))
                                    editor.insertContent(`<img src="/storage/${lien_fichier}" alt="${file.name}"/>`);
                                else
                                    editor.insertContent(`<a href="/storage/${lien_fichier}" target="_blank">${file.name}</a>`);
                            };

                            xhr.onerror = () => {
                                toastr.error(instance.$root.traduction('composant.champ_file.erreur_inattendue'));
                            };

                            const formData = new FormData();

                            formData.append(nom_input, file, file.name);

                            xhr.send(formData)
                        };

                        reader.readAsDataURL(file);
                    });
                },
                mentions: {
                    source: instance.$root.utilisateurs_pour_mention,
                    insert: function(item) {
                        return '@' + item.username;
                    },
                    render: function(item) {

                        var base = '<li><a href="javascript:;">';

                        if (item.image) {
                            base+='<img class="mention_image" src="' + item.image + '">';
                        }
                        if (item.name) {
                            base+='<b class="mention_name">' + item.name + '</b>';
                        }
                        if (item.username) {
                            base+='<span class="mention_username"> @' + item.username + '</span>';
                        }

                        return base;
                    },
                },
                init_instance_callback: function (editor) {
                    editor.on(instance.gestion_mise_a_jour_valeur, function (e) {
                        instance.mise_a_jour_valeur(editor.getContent());
                    });
                    instance.objet_tinymce = editor;
                },
            };

            var options = Object.assign({}, initialisation_options);

            await tinymce.init(options);

            await this.$nextTick();

            this.$emit('editeur_initialise', this.objet_tinymce);
        },

        upload_fichier : function(cb, value, meta){

            var instance = this;

            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', this.extension_type_accepte.join(','));

            input.onchange = function () {
                var file = this.files[0];
                
                if(!instance.extension_type_accepte.includes(file.type)){
                    toastr.error(instance.$root.traduction('messages.php.upload.type_non_valide'));
                    return;
                }

                var reader = new FileReader();
                reader.onload = async function () {

                    var file = input.files[0];

                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.responseType = 'json';

                    var url = "{{ route('bibliotheque.upload_fichier', [], false) }}";
                    var nom_input = "image";
                    
                    if(instance.$root.element_id && instance.$root.type_element){
                        url = "/eden/fiche/"+instance.$root.type_element+"/"+instance.$root.element_id+"/post/ajoute_piece_jointe"
                        nom_input = "piece_jointe";
                    }
                    
                    xhr.open('POST', url, true);

                    xhr.onload = () => {
                        if (xhr.status === 403) {
                            toastr.error(instance.$root.traduction('composant.champ_file.erreur_inattendue'));
                            return;
                        }

                        if (xhr.status < 200 || xhr.status >= 300) {
                            toastr.error(instance.$root.traduction('composant.champ_file.erreur_inattendue'));
                            return;
                        }

                        if(xhr.response.erreur){
                            toastr.error(xhr.response.message);
                            return;
                        }

                        var lien_fichier = xhr.response.lien_fichier ?? xhr.response.chemin;
                        
                        if (meta.filetype == 'file')
                            cb('/storage/'+lien_fichier, { text: file.name});
                        else if (meta.filetype == 'image')
                            cb('/storage/'+lien_fichier, { alt: file.name});
                        else if (meta.filetype == 'media')
                            cb('/storage/'+lien_fichier, {source2: '/storage/'+lien_fichier, poster: file.name});
                    };

                    xhr.onerror = () => {
                        toastr.error(instance.$root.traduction('composant.champ_file.erreur_inattendue'));
                    };

                    const formData = new FormData();

                    formData.append(nom_input, file, file.name);

                    xhr.send(formData);
                };

                reader.readAsDataURL(file);
            };

            input.click();
        },
    },

});
</script>
