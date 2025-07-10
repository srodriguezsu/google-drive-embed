(function($) {
    $(document).ready(function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.create('tinymce.plugins.gde_button', {
                init: function(editor, url) {
                    editor.addButton('gde_button', {
                        text: 'Insertar Google Drive',
                        icon: false,
                        onclick: function() {
                            // Obtener entrada del usuario
                            var title = prompt("Introduce el título para el embed:", "Documento de Google Drive");
                            if (title === null) return; // Usuario canceló

                            var link = prompt("Pega el enlace del archivo o carpeta de Google Drive:");
                            if (link === null) return; // Usuario canceló

                            // Validar y analizar el enlace
                            var parsed = parseDriveLink(link);
                            if (!parsed.valid) {
                                return alert("Enlace de Google Drive no válido. Por favor, usa un enlace de archivo o carpeta válido.");
                            }

                            // Generar HTML de inserción
                            var html = generateEmbedHtml(parsed.id, title, parsed.type);
                            editor.insertContent(html);
                        }
                    });
                }
            });
            tinymce.PluginManager.add('gde_button', tinymce.plugins.gde_button);
        }

        function parseDriveLink(link) {
            link = (link || '').trim().split('?')[0].split('&')[0];

            const filePatterns = [
                /drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/i,
                /drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/i,
                /drive\.google\.com\/uc\?id=([a-zA-Z0-9_-]+)/i
            ];

            const folderPattern = /drive\.google\.com\/drive\/folders\/([a-zA-Z0-9_-]+)/i;

            for (const pattern of filePatterns) {
                const match = link.match(pattern);
                if (match) {
                    return { valid: true, id: match[1], type: 'file' };
                }
            }

            const folderMatch = link.match(folderPattern);
            if (folderMatch) {
                return { valid: true, id: folderMatch[1], type: 'folder' };
            }

            return { valid: false };
        }

        function generateEmbedHtml(id, title, type) {
            const containerId = 'gde-' + Math.random().toString(36).substr(2, 9);

            const urls = {
                file: {
                    iframe: `https://drive.google.com/file/d/${id}/preview`,
                    link: `https://drive.google.com/file/d/${id}/view`
                },
                folder: {
                    iframe: `https://drive.google.com/embeddedfolderview?id=${id}#grid`,
                    link: `https://drive.google.com/drive/folders/${id}`
                }
            };

            return `
                <div id="${containerId}" class="google-drive-embed">
                    <h3>${escapeHtml(title)}</h3>
                    <div class="gde-iframe-container">
                        <iframe src="${urls[type].iframe}" 
                                width="100%" 
                                height="${type === 'file' ? '480' : '600'}" 
                                frameborder="0" 
                                allowfullscreen
                                allow="autoplay">
                        </iframe>
                    </div>
                    <p class="gde-link">
                        <a href="${urls[type].link}" 
                           target="_blank" 
                           rel="noopener noreferrer">
                           Abrir ${type === 'file' ? 'archivo' : 'carpeta'} en Google Drive
                        </a>
                    </p>
                </div>
            `;
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
})(jQuery);