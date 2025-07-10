(function($) {
    $(document).ready(function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.create('tinymce.plugins.gde_button', {
                init: function(editor, url) {
                    editor.addButton('gde_button', {
                        text: 'Insert Google Drive Embed',
                        icon: false,
                        onclick: function() {
                            var title = prompt("Enter the title:");
                            var link = prompt("Paste the Google Drive file link:");
                            if (title && link) {
                                const match = link.match(/\/d\/([^\/]+)/);
                                if (!match) return alert("Invalid Google Drive link");
                                const id = match[1];
                                const html = `<div><h2>${title}</h2><p><iframe src="https://drive.google.com/file/d/${id}/preview" width="640" height="480" allow="autoplay"></iframe><br><a href="https://drive.google.com/file/d/${id}/view" class="btn btn-primary" target="_blank" rel="noopener noreferrer"><br>Enlace a ${title}<br></a><br><br></p></div>`;
                                editor.insertContent(html);
                            }
                        }
                    });
                }
            });
            tinymce.PluginManager.add('gde_button', tinymce.plugins.gde_button);
        }
    });
})(jQuery);
