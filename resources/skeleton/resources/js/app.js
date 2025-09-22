import boui from '../../vendor/uxmaltech/backoffice-ui/resources/js/boui/index';

export default boui;

window.onSuccessCreateNewDesign = (element, event ) => {
    if( event.type === 'on-success-submit') {
        if( event.request.result.data.id ) {
            console.debug('New Design Id Created: ', event.request.result.data.id);
            window.document.location.href = '/boui/product/designer/' + event.request.result.data.id;
        }
    }
}

boui.on('loaded.boui.app', () => {
    console.debug('(app.js) BOUIApp Initialized');
    const masterLayoutSearchInputText = boui.get('inputs.masterLayoutSearchInputText');

    if (masterLayoutSearchInputText) {
        masterLayoutSearchInputText.on('keyup', (e) => {
            if (e.key === 'Enter') {
                console.debug('masterLayoutSearchInputText Enter: ', e.target.value);
            }
        });
    }
    /*
        boui.get('buttons.topBarNewDesign').on('dblclick', () => {
            console.debug('topBarNewDesign double-clicked');
        });

     */
});
