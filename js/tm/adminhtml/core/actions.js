TmcoreActions = Class.create();
TmcoreActions.prototype = {
    initialize: function() {
        document.on('click', '.tm-action-select', function(e, el) {
            e.stop();

            var menu = el.next(),
                isVisible = menu.visible();

            $$('.tm-action-menu').invoke('hide');

            if (!isVisible) {
                el.next().show();
            }
        });
    }
};
new TmcoreActions();
