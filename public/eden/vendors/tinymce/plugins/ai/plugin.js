;(function (f) {
    'use strict';

    // CommonJS
    if (typeof exports === 'object' && typeof module !== 'undefined') {
        module.exports = f(require('jquery'));
    } else {
        var g;
        if (typeof window !== 'undefined') {
            g = window;
        } else if (typeof global !== 'undefined') {
            g = global;
        } else if (typeof self !== 'undefined') {
            g = self;
        } else {
            g = this;
        }

        f(g.jQuery);
    }

})(function ($) {
    'use strict';
    tinymce.util.Tools.resolve("tinymce.PluginManager").add('ai', function(editor, url) {
        function loadStyleSheet(u, o) {
            o = o || {};
            var d = document,
                s = d.createElement('link'),
                a = o.attributes || {};
            s.rel = 'stylesheet';
            s.href = u;
            s.onload = function (e) {
                (o.callback || function () {})(s);
            };
            if (Object.keys(a).length > 0) {
                for (var k in a) {
                s.setAttribute(k, a[k]);
                }
            }
            (o.parentTag || d.getElementsByTagName('head')[0]).appendChild(s);
        }
        function openAIPopup() {
            if (document.getElementById('ai-popup')) return;
            let typeWriterTimeout = null;
            let lastText = '';
            var popup = document.createElement('div');
            popup.id = 'ai-popup';
            popup.innerHTML =
                '<div class="ai-header">' +
                    '<span>Agent IA</span>' +
                    '<span id="ai-cancel">' +
                        '<svg width="24" height="24"><path d="M17.3 8.2 13.4 12l3.9 3.8a1 1 0 0 1-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 0 1-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 0 1 1.5-1.5l3.8 3.9 3.8-3.9a1 1 0 0 1 1.5 1.5Z" fill-rule="evenodd"/></svg>'+                    
                    '</span>' +
                '</div>' +
                '<div style="position:relative">' +
                    '<iframe id="ai-preview" style="width:100%;height:250px;border:1px solid #ccc;background:#fff;"></iframe>' +
                    '<div id="ai-actions">' +
                        '<span id="ai-insert">Insérer</span>' +
                        '<span id="ai-retry">Réessayer</span>' +
                        '<span id="ai-stop" class="desactived">Stop</span>' +
                    '</div>' +
                    '<div class="ai-input-group">' +
                        '<input id="ai-input" type="text" placeholder="Votre demande à l\'agent IA" />' +
                        '<span id="ai-send" class="desactived">'+
                            '<svg fill="white" width="24" height="24"><path fill-rule="evenodd" clip-rule="evenodd" d="m13.3 22 7-18.3-18.3 7L9 15l4.3 7ZM18 6.8l-.7-.7L9.4 14l.7.7L18 6.8Z"/></svg>'+                    
                        '</span>' +
                    '</div>'+ 
                    '<div id="ai-loader">'+
                        '<div class="ai-loader-inner"><img src="eden/images/ajax_loader.gif"></div>'+ 
                    '</div>'+ 
                '</div>';
            var editorContainer = editor.getContainer ? editor.getContainer() : null;
            if (editorContainer) {
                editorContainer.style.position = 'inherit';
                editorContainer.appendChild(popup);
            } else {
                document.body.appendChild(popup);
            }
            document.getElementById('ai-input').focus();
            var aiInput = document.getElementById('ai-input');
            var aiSend = document.getElementById('ai-send');
            var aiCancel = document.getElementById('ai-cancel');
            var aiPreview = document.getElementById('ai-preview');
            var aiLoader = document.getElementById('ai-loader');
            var aiActions = document.getElementById('ai-actions');
            var aiInsert = document.getElementById('ai-insert');
            var aiRetry = document.getElementById('ai-retry');
            var aiStop = document.getElementById('ai-stop');

            aiCancel.onclick = function() {
                if (popup.parentNode) popup.parentNode.removeChild(popup);
            };

            function typeWriterEffect(text, onDone) {
                aiPreview.style.display = 'block';
                aiActions.style.display = 'flex';
                var iframeDoc = aiPreview.contentDocument || aiPreview.contentWindow.document;
                // Récupère tous les CSS utilisés par l'éditeur (tableau ou string)
                var cssLinks = '';
                var contentCSS = editor.contentCSS || editor.settings.content_css || [];
                if (typeof contentCSS === 'string') contentCSS = [contentCSS];
                for (var i = 0; i < contentCSS.length; i++) {
                    cssLinks += '<link rel="stylesheet" href="' + contentCSS[i] + '">';
                }
                iframeDoc.open();
                iframeDoc.write('<html><head>' + cssLinks + '</head><body></body></html>');
                iframeDoc.close();
                aiStop.classList.remove('desactived');
                aiInsert.classList.add('desactived');
                aiRetry.classList.add('desactived');
                aiSend.classList.add('desactived');

                // Typewriter dans l'iframe
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = text;
                var body = iframeDoc.body;
                body.innerHTML = '';
                function typeNode(srcNode, destParent, done) {
                    if (srcNode.nodeType === Node.TEXT_NODE) {
                        var txt = srcNode.textContent;
                        var span = iframeDoc.createElement('span');
                        destParent.appendChild(span);
                        let i = 0;
                        function typeChar() {
                            if (i < txt.length) {
                                span.textContent += txt.charAt(i);
                                i++;
                                typeWriterTimeout = setTimeout(typeChar, 4);
                            } else {
                                done();
                            }
                        }
                        typeChar();
                    } else if (srcNode.nodeType === Node.ELEMENT_NODE) {
                        var el = iframeDoc.createElement(srcNode.tagName);
                        for (var j = 0; j < srcNode.attributes.length; j++) {
                            el.setAttribute(srcNode.attributes[j].name, srcNode.attributes[j].value);
                        }
                        destParent.appendChild(el);
                        let k = 0;
                        function nextChild() {
                            if (k < srcNode.childNodes.length) {
                                typeNode(srcNode.childNodes[k], el, function() {
                                    k++;
                                    nextChild();
                                });
                            } else {
                                done();
                            }
                        }
                        nextChild();
                    } else {
                        done();
                    }
                }
                let idx = 0;
                function nextRootChild() {
                    if (idx < tempDiv.childNodes.length) {
                        typeNode(tempDiv.childNodes[idx], body, function() {
                            idx++;
                            nextRootChild();
                        });
                    } else {
                        aiActions.style.display = 'flex';
                        aiInput.disabled = false;
                        aiInsert.classList.remove('desactived');
                        aiRetry.classList.remove('desactived');
                        aiSend.classList.remove('desactived');
                        aiStop.classList.add('desactived');
                        if (onDone) onDone();
                    }
                }
                nextRootChild();
            }
            aiStop.onclick = function() {

                if (typeWriterTimeout) clearTimeout(typeWriterTimeout);

                aiInsert.classList.remove('desactived');
                aiRetry.classList.remove('desactived');
                aiSend.classList.remove('desactived');
                aiStop.classList.add('desactived');
                aiInput.disabled = false;
            };

            aiRetry.onclick = function() {
                sendRequest();
            };

            function sendRequest() {
                var demande = aiInput.value;
                aiSend.disabled = true;
                aiInput.disabled = true;
                aiLoader.style.display = 'block';

                var contenu_editor = new DOMParser()
                    .parseFromString(editor.getContent(), "text/html")
                    .documentElement.textContent.replaceAll('\n','');

                if (typeWriterTimeout) clearTimeout(typeWriterTimeout);
                $.ajax({
                    url: 'eden/ai/requete_open_ai/1',
                    method: 'POST',
                    data: JSON.stringify({ prompt: demande , complement_contexte: 'Contenu déjà présent dans le textarea : '+ contenu_editor}),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        aiLoader.style.display = 'none';
                        let text = response && response.reponse ? response.reponse : '';
                        lastText = text;
                        if (!text) {
                            aiPreview.style.display = 'block';
                            aiPreview.innerHTML = 'Aucune réponse.';
                            aiActions.style.display = 'flex';
                            aiSend.disabled = false;
                            aiInput.disabled = false;
                            return;
                        }
                        typeWriterEffect(text, function() {
                            aiActions.style.display = 'flex';
                            aiSend.disabled = false;
                            aiInput.disabled = false;
                        });

                        aiInsert.onclick = function() {
                            editor.insertContent(text);
                            if (popup.parentNode) popup.parentNode.removeChild(popup);
                        };
                    },
                    error: function(xhr, status, error) {
                        aiLoader.style.display = 'none';
                        aiPreview.style.display = 'block';
                        aiPreview.innerHTML = 'Service indisponible';
                        aiActions.style.display = 'flex';
                        aiSend.disabled = false;
                        aiInput.disabled = false;
                    }
                });
            }
            aiSend.onclick = function() {
                if(aiInput.value == '' || aiInput.value == null)
                    return;

                sendRequest();
            };

            aiInput.addEventListener('input', function(e) {
                if(![...aiSend.classList].includes('desactivated') && (aiInput.value == '' || aiInput.value == null))
                    aiSend.classList.add('desactived');
                else if([...aiSend.classList].includes('desactived') && (aiInput.value != '' && aiInput.value != null))
                    aiSend.classList.remove('desactived');
            });
            aiInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    sendRequest();
                }
            });
        }

        editor.ui.registry.addButton('ai', {
            icon: 'ai',
            onAction: function() {
                openAIPopup();
            }
        });

        editor.ui.registry.addMenuItem('ai', {
            text: 'AI',
            icon: 'ai',
            onAction: function() {
                openAIPopup();
            }
        });

        return {
            getMetadata: function () {
                return {
                    name: 'AI Plugin',
                };
            }
        };
    });
});