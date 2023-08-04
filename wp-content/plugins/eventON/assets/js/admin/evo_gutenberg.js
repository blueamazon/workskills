var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = { backgroundColor: 'transparent', color: '#808080', padding: '20px', 'border-radius':'5px' };

const iconEl = el(
    'svg', 
    { width: 20, height: 20 },
    el('path', { d: "M0 0h24v24H0V0z" } )
);
registerBlockType( 'gutenberg-boilerplate-es5/hello-world-step-01', {
    title: 'EventON',

    icon: iconEl,

    category: 'layout',
   
    edit: function() {
        return el( 'p', { style: blockStyle }, 'Basic EventON Calendar' );
    },

    save: function() {
        return el( 'p', { style: blockStyle }, '[add_eventon]' );
    },
} );