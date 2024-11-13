Ext.define('Tualo.MicrosoftMail.lazy.Setup', {
    extend: 'Ext.panel.Panel',
    requires: [
        'Tualo.MicrosoftMail.lazy.models.Setup',
        'Tualo.MicrosoftMail.lazy.controller.Setup'
    ],
    alias: 'widget.msmail_setup',
    controller: 'msmail_setup',

    viewModel: {
        type: 'msmail_setup'
    },
    listeners: {
        boxReady: 'onBoxReady'
    },

    layout: 'fit',
    /*
    bind:{
        title: '{ftitle}'
    },
    */
    items: [

        {
            hidden: false,
            xtype: 'panel',
            itemId: 'startpanel',
            layout: {
                type: 'vbox',
                align: 'center'
            },
            items: [
                {
                    xtype: 'component',
                    cls: 'lds-container-compact',
                    html: '<div class=" "><div class="blobs-container"><div class="blob gray"></div></div></div>'
                        + '<div><h3>Microsoft Zugriff</h3>'
                        + '<span>Einen Moment, die Daten werden geprüft.</span></div>'
                }
            ]
        },
        {
            hidden: true,
            xtype: 'panel',
            itemId: 'devicetoken',
            layout: {
                type: 'vbox',
                align: 'center'
            },
            items: [
                {
                    xtype: 'component',
                    cls: 'lds-container-compact',
                    bind:{
                        html: '{devicetokenhtml}'
                    }
                }
            ]
        },
        {
            hidden: true,
            xtype: 'panel',
            itemId: 'user',
            layout: {
                type: 'vbox',
                align: 'center'
            },
            items: [
                {
                    xtype: 'component',
                    cls: 'lds-container-compact',
                    bind:{
                        html: '{userhtml}'
                    }
                }
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: [
                    { xtype: 'button', text: 'Erneuern',bind:{
                        handler: 'getDeviceToken'
                    } }
                ]
            }]
        }
    ],

});
