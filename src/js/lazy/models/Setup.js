Ext.define('Tualo.MicrosoftMail.lazy.models.Setup', {
    extend: 'Ext.app.ViewModel',
    alias: 'viewmodel.msmail_setup',
    data: {
      message: 'OK',
     
      state: 1,
      devicetoken_message: -1,
      devicetoken_expires_in: -1,
      devicetoken_interval: 1000,
      devicetoken_user_code: -1,
      devicetoken_verification_uri: '',

      displayName:'',
      email:''
    },
    formulas: {
      ftitle: function(get){
        let txt='MS Login';
        return txt;
      },

      info: function(get){
        let txt='MS Login';
        return get('message');
      },


      devicetokenhtml: function(get){
        return [
            '<div class=" ">',
              '<div class="blobs-container">',
                '<div class="blob yellow">',
                '</div>',
              '</div>',
            '</div>',
            '<div>',
              '<h3>Microsoft Zugriff</h3>',
              '<span>',
                'Bitte geben Sie den Zugriff frei.',
              '</span>',
              '<br>',
              '<a href="', get('devicetoken_verification_uri') ,'" target="_blank" style="font-size: 1.5em;line-height: 1.5em;">',
                  get('devicetoken_verification_uri'),
              '</a>',
              '<br>',
              '<span>',
                'Der Code für die Freigabe lautet: ',
                '<br>',
                '<strong style="font-size: 2em;line-height: 2em;">',
                  get('devicetoken_user_code'),
                '</strong>',
              '</span>',
            '</div>'
        ].join('')
      },



      userhtml: function(get){
        return [
            '<div class=" ">',
              '<div class="blobs-container">',
                '<div class="blob green">',
                '</div>',
              '</div>',
            '</div>',
            '<div>',
              '<h3>Microsoft Zugriff</h3>',
              '<span>',
                'Sie sind angemeldet als',
              '</span>',
              '<br>',
              '<strong style="font-size: 2em;line-height: 2em;">',
                get('displayName'),
              '</strong>',
              '<br>',
              '<strong style="font-size: 1.2em;line-height: 1.2em;">',
                get('mail'),
              '</strong>',
            '</div>'
        ].join('')
      },

    },
    stores: {
        
      
    }
  });
  