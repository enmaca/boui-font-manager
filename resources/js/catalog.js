/**
 * Font Manager
 * @param {BackofficeUI} boui - The BackofficeUI instance.
 */
const bouiFontManagerCatalog = (boui) => {

    boui.on('ui-gridjs.boui.ready', (event) => {
        switch (event.name) {
            case 'typography':
                const divFontPreviewEl = event.element.querySelectorAll('.font-preview');
                divFontPreviewEl.forEach((el) => {
                    const fontName = el.getAttribute('data-uxmal-font-name');
                    const fontUrl = el.getAttribute('data-uxmal-font-url');
                    const newFont = new FontFace(fontName, `url("${fontUrl}")`);
                    newFont.load().then((loadedFont) => {
                        document.fonts.add(loadedFont);
                    }).catch((error) => {
                        console.error('Failed to load font:', error);
                    });
                });
                break;
        }
    });

//cmd.font-manager.typography.file.process.v1
//boui.constructor
    boui.on('form-input-filepond.boui.server.response', (event) => {
        if (event.meta.cmd) {
            switch (event.meta.cmd) {
                case 'cmd.font-manager.typography.file.process.v1':
                    boui.get('forms.uploadTypography.typographyName').setValue(event.meta.font);
                    boui.get('forms.uploadTypography.typographySubFamily').setValue(event.meta.sub_family);
                    boui.get('forms.uploadTypography.typographyVersion').setValue(event.meta.version);
                    boui.get('forms.uploadTypography.typographyPostScriptName').setValue(event.meta.post_script_name);
                    boui.get('forms.uploadTypography.typographyCopyright').setValue(event.meta.copyright);
                    break;
            }
        }
    });

    boui.on('ui-modal.boui.hidden', (event) => {
        switch (event.name) {
            case 'addTypography':
                boui.get('content.typography').reload();
                boui.get('forms.uploadTypography').reset();
                break;
        }
    });
}

export default bouiFontManagerCatalog;
