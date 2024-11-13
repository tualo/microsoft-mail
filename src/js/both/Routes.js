Ext.define('Tualo.routes.MicrosoftMail.Setup', {
    statics: {
        load: async function() {
            return [
                {
                    name: 'msmail/setup',
                    path: '#msmail/setup'
                }
            ]
        }
    }, 
    url: 'msmail/setup',
    handler: {
        action: function () {
            
            let mainView = Ext.getApplication().getMainView(),
                stage = mainView.getComponent('dashboard_dashboard').getComponent('stage'),
                component = null,
                cmp_id = 'msmail_setup';
                component = stage.down(cmp_id);
            if (component){
                stage.setActiveItem(component);
            }else{
                Ext.getApplication().addView('Tualo.MicrosoftMail.lazy.Setup',{
                
                });
            }

            
        },
        before: function (action) {
            
            action.resume();
        }
    }
});