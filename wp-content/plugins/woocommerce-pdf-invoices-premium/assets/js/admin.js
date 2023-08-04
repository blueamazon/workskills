var PremiumSettings = {};

PremiumSettings.removeFilename = function ( id ) {
    document.getElementById( id ).value = "";
};

PremiumSettings.changeFilename = function (elem, id) {
    document.getElementById( id ).value = elem.value.replace(/.*[\/\\]/, '');
};

PremiumSettings.switchSettings = function () {
    var template = document.querySelector('select#bewpi-template-name');
    if (template !== null) {
        if (template.value.toLowerCase().indexOf('minimal') !== -1) {
            var index = bewpi.setting.settings.indexOf('bewpi-show-tax');
            if (index !== -1) {
                bewpi.setting.settings.splice(index, 1);
            }
        }
    }
};

window.addEventListener('load', function () {
    var chooseInput = document.getElementById('choose-input');
    var fileInput = document.getElementById('file-input');
    if (chooseInput && fileInput) {
        fileInput.style.width = chooseInput.offsetWidth + "px";
        fileInput.style.height = chooseInput.offsetHeight + "px";
    }
});

var template = document.querySelector('select#bewpi-template-name');
if (template !== null) {
    template.addEventListener('change', PremiumSettings.switchSettings );
    PremiumSettings.switchSettings();
}
