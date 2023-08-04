(function() {
    tinymce.PluginManager.add('nextgates_custom_buttons', function(editor, url) {
        // Add the tip_tag button
        editor.addButton('tip_tag', {
            title: 'Tip/Idea Span',
            icon: 'info', // Built-in TinyMCE icon
            onclick: function() {
                editor.insertContent(
                    '<span class="idea01">' +
                    '! ' +
                    '</span>'
                );
            }
        });

        // Add the var_tag button
        editor.addButton('var_tag', {
            title: 'var_tag around content',
            icon: 'code', // Built-in TinyMCE icon
            onclick: function() {
                if (editor.selection.getContent()) {
                    editor.insertContent(
                        '<var>' +
                        editor.selection.getContent() +
                        '</var>'
                    );
                }
            }
        });

        // Add the sayit_tag button
        editor.addButton('sayit_tag', {
            title: 'sayit around content',
            icon: 'fullscreen', // Built-in TinyMCE icon
            onclick: function() {
                if (editor.selection.getContent()) {
                    editor.insertContent(
                        '[sayit block="1" speed="0.9"]' +
                        editor.selection.getContent() +
                        '[/sayit]'
                    );
                }
            }
        });
    });
})();
