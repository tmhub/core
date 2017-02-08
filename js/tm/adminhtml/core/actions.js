TmcoreActions = Class.create();
TmcoreActions.prototype = {
    initialize: function() {
        document.on('click', '.tm-action-select', function(e, el) {
            e.stop();

            var wrapper = el.up(),
                isVisible = wrapper.hasClassName('active');

            $$('.tm-action-select-wrap').invoke('removeClassName', 'active');

            if (!isVisible) {
                wrapper.addClassName('active');
            }
        });
    }
};
new TmcoreActions();
