<script>
const champ_dropzone = Vue.component('champ-dropzone', {
    template: `<div>
             <div :id="'drop_zone'+id_random" @drop="dropHandler" @dragover="dragOverHandler" @dragleave="dragLeaveHandler">
              <div class="row">
                <div class="col-md-3" v-for="fichier in fichiers" style="text-align: center;">
                    <span class="fa fa-times"  style="cursor: pointer;" @click="supprimer(fichier)"></span>
                    <a target="_blank" :href="'/eden/bibliotheque/fichier/telecharger_fichier_element?url='+fichier.url_storage">
                        <div style=" border: 1px solid #aaa; margin: 5px; padding: 5px; height: 161px; cursor: pointer;">
                            <img :src="fichier.url_public" style="max-width: 100%; max-height: 130px;" v-if="fichier.image === true" />
                            <div v-if="fichier.image !== true" style="background: #eee;padding: 20px 0px;font-size: 43px;text-transform: uppercase;color: #676767; max-height: 130px;">
                                <span class="fa fa-file"></span>
                                @{{fichier.type}}
                            </div>
                            <br/>
                            @{{fichier.nom_original}}<br/>
                        </div>
                    </a>
                </div>
                <div class="col-md-12" style="text-align: center;" v-if="lecture_seule == false">
                  <div style="margin: 5px; padding: 5px;">
                    <span @click="$refs.input.click()" style="cursor: pointer; padding: 5px;"><span class="fa fa-upload"></span> @traduction('composant.champ_dropzone.deposez_vos_fichiers_ici')</span>
                    <input type="file" @change="modification_fichier()" ref="input" multiple style="display: none;" />
                  </div>
                </div>
              </div>
            </div>
            <input type="hidden" :name="name" v-model="fichiers_json" />
        </div>`,
   props: {

    valeur: '',
    type_element: '',
    name: '',
    accept: '',
    lecture_seule : {
        type: Boolean | Number,
        default : false,
    },
  },
  data: function () {
    return {
      fichiers: [],
      fichiers_attente: [],
    }
  },
  computed: {

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

    fichiers_json: function() {

        var json = JSON.stringify(this.fichiers);
        this.$emit('input', json);
        return json;
    },

  },
  methods: {

    envoi_fichier: async function(file) {

            var formData = new FormData();

            this.fichiers_attente.push({});

            formData.append('image', file, file.name);

            reponse = await $.post({
                url : "{{ route('bibliotheque.ajouter_fichier_element', [], false) }}",
                dataType: 'json',
                data : formData,
                contentType: false,
                processData: false
            });

            if(typeof reponse.retour == 'undefined'){

              this.fichiers.push(reponse.fichier);

              this.fichiers_attente.pop();
            }
            else{
              composant.fichiers_attente.pop();

              await alerte_eden(reponse.retour);
            }
        },

        dropHandler: function(ev) {

            if(this.lecture_seule)
                return;

          // Prevent default behavior (Prevent file from being opened)
          ev.preventDefault();

          if (ev.dataTransfer.items) {
            // Use DataTransferItemList interface to access the file(s)
            for (var i = 0; i < ev.dataTransfer.items.length; i++) {
              // If dropped items aren't files, reject them
              if (ev.dataTransfer.items[i].kind === 'file') {

                var file = ev.dataTransfer.items[i].getAsFile();

                this.envoi_fichier(file);

              }
            }
          } else {
            // Use DataTransfer interface to access the file(s)
            for (var i = 0; i < ev.dataTransfer.files.length; i++) {

              var file = ev.dataTransfer.files[i].getAsFile();

              this.envoi_fichier(file);
            }
          }

          // Pass event to removeDragData for cleanup
          this.removeDragData(ev)
        },

        dragOverHandler: function(ev) {

            if(this.lecture_seule)
                return;

          $('#drop_zone'+this.id_random).css('border', "2px dashed red");

          // Prevent default behavior (Prevent file from being opened)
          ev.preventDefault();
        },

        dragLeaveHandler: function(ev) {

            if(this.lecture_seule)
                return;

          $('#drop_zone'+this.id_random).css('border', "2px dashed rgb(142, 142, 142)");

          // Prevent default behavior (Prevent file from being opened)
          ev.preventDefault();
        },

        removeDragData: function(ev) {

          if (ev.dataTransfer.items) {
            // Use DataTransferItemList interface to remove the drag data
            ev.dataTransfer.items.clear();
          } else {
            // Use DataTransfer interface to remove the drag data
            ev.dataTransfer.clearData();
          }

            $('#drop_zone'+this.id_random).css('border', "");
        },

        supprimer: async function(fichier){

            var composant = this;

            if(!await confirm_eden('Voulez-vous vraiment supprimer le fichier ?'))
                return false;

            composant.fichiers.splice(composant.fichiers.indexOf(fichier), 1);
        },

        modification_fichier: async function(){

          input_file = this.$refs.input;

            for (var i = 0; i < input_file.files.length; i++) {

                var file = input_file.files[i];

                await this.envoi_fichier(file);
            }

          this.$emit('upload_fichiers',this.fichiers);
        },
  },
  created: function() {

      try {

          if (JSON.parse(this.valeur) != null) {
              this.fichiers = JSON.parse(this.valeur);
          }
    }
    catch (e) {

        this.fichiers = [];
    }
  },

  watch:  {

    valeur: function(nouvelle_valeur, ancienne_valeur) {

      if(nouvelle_valeur == '' || nouvelle_valeur == undefined || nouvelle_valeur === null) {

        this.fichiers = [];
        return;
      }

      this.fichiers = JSON.parse(nouvelle_valeur);
    }
  },

});
</script>
