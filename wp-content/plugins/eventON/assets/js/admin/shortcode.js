jQuery(document).ready(function($) {
    tinymce.create('tinymce.plugins.eventon_shortcode_button', {
         init : function(ed, url) {
             ed.addButton('eventon_shortcode_button', {
                title : 'EventON Shortcode Generator',
                onclick : function() {
                  $('body').trigger('evo_open_admin_lightbox',['eventon_shortcode']);                  
                }
             });
          },
          createControl : function(n, cm) {   return null;   },
          getInfo : function() {
             return {
                longname : "EventON Shortcode",
                author : 'Ashan Jay',
                authorurl : 'http://www.ashanjay.com',
                version : "1.0"
             };
          }  
    });

    tinymce.PluginManager.add('eventon_shortcode_button', tinymce.plugins.eventon_shortcode_button);
});