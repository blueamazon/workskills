/**
 * xpanel.frontend.js
 *
 * @author SaurabhSharma
 * @version 1.3.0
 */
jQuery(function ($) {

    'use strict';

    var xp_sb_container = '.xpanel-sidebar';

    if (xpanel_localize !== 'undefined') {
        if (xpanel_localize.sb_container !== '' && xpanel_localize.sb_convert) {
            xp_sb_container = xpanel_localize.sb_container;
        }

        if (xpanel_localize.panel_transition == 'offcanvas') {
            $('body').addClass('offcanvas-ready');
        }

        if (xpanel_localize.panel_pos == 'right') {
            $('body').addClass('panel-pos-right');
        }
    }

    $(xp_sb_container).wrapInner('<div class="panel-wrap"></div>');

    if (xpanel_localize !== 'undefined' && xpanel_localize.button_style == 'icon') {
        $('body').addClass('panel-full-height');
        $(xp_sb_container).append('<a href="#" class="panel-toggle icon-style"><span class="screen-reader-text">' + xpanel_localize.button_text + '</a>');
    }

    $(document).on('click','.panel-toggle, .panel-body-mask', function (e) {
        e.preventDefault();
        $(xp_sb_container).toggleClass('side-panel-active');
		$('body').toggleClass('panel-active');
        $('.panel-body-mask').toggleClass('show-mask');
        $('#sliding-panel-actions').toggleClass('active-toggle');
        if (xpanel_localize !== 'undefined' && xpanel_localize.panel_transition == 'offcanvas') {
            $('body').toggleClass('off-canvas');
        }
    });

    if (matchMedia) {
        var viewport_width = xpanel_localize !== 'undefined' ? xpanel_localize.viewport_width : '768',
            mq = window.matchMedia("(max-width: " + viewport_width + "px)");
        mq.addListener(xpanel_width_change);
        xpanel_width_change(mq);
    }

    // Media query change
    function xpanel_width_change(mq) {
        if (mq.matches) {
            $(xp_sb_container).addClass('sliding-sidebar');
            $('#sliding-panel-actions').addClass('show-actions');
            $('.panel-body-mask').removeClass('disable-mask');
            $('body').removeClass('disable-offcanvas');
            xpanel_attach_accordion_lists();
        } else {
            $(xp_sb_container).removeClass('sliding-sidebar');
            $('#sliding-panel-actions').removeClass('show-actions');
            $('.panel-body-mask').addClass('disable-mask');
            $('body').addClass('disable-offcanvas');
            xpanel_detach_accordion_lists();
        }
    }

    // Add accordion like submenu items to side panel lists
    function xpanel_attach_accordion_lists() {
        if (xpanel_localize !== 'undefined' && xpanel_localize.collapse_lists && xpanel_localize.list_selectors !== '') {
            var list_widgets = $(xp_sb_container).find(xpanel_localize.list_selectors),
                sub_menus,
                expand_menus,
                expand_text = xpanel_localize.expand_text;

            if (list_widgets.length) {
                $(list_widgets).find('ul:eq(0)').addClass('accordion-menu');
                sub_menus = $('.accordion-menu').find('ul').parent();

                if (sub_menus.length) {
                    $(sub_menus).each(function () {
                        if (!$(this).hasClass('has-children')) {
                            $(this).addClass('has-children').append('<a class="expand-menu" href="#" title="' + expand_text + '"><i class="mdi mdi-keyboard_arrow_down"></i></a>').find('ul').hide();
                        }
                    });
                }

                expand_menus = $(list_widgets).find('.expand-menu');

                if (expand_menus.length) {
                    $(expand_menus).on('click', function (e) {
                        var icon = $(this).find('.mdi');
                        icon.toggleClass('rotate-180');
                        $(this).prev().slideToggle(300);
                        e.preventDefault();
                    });
                }
            }
        }
    }

    // Detach accordion feature from lists
    function xpanel_detach_accordion_lists() {
        if (xpanel_localize !== 'undefined' && xpanel_localize.collapse_lists && xpanel_localize.list_selectors !== '') {
            var list_widgets = $(xp_sb_container).find(xpanel_localize.list_selectors);

            if (list_widgets.length) {
                $(list_widgets).find('ul:eq(0)').removeClass('accordion-menu').find('li.has-children').removeClass('has-children').find('a.expand-menu').remove();
            }
        }
    }
}); //$