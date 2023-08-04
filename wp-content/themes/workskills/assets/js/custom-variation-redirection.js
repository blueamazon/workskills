jQuery(document).ready(function($) {
    $('form.variations_form').on('found_variation', function(event, variation) {
        if (variation.hasOwnProperty('custom_url') && variation.custom_url !== '') {
            window.location.href = variation.custom_url;
        }
    });
});

