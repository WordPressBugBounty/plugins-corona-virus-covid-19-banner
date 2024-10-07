jQuery( document ).ready( function ( e ) {
    //  Set a default
    if ( typeof Cookies != 'undefined' ) 
        if ( !Cookies.get( 'ocvb-keep-banner-closed' ) )
            Cookies.set( 'ocvb-keep-banner-closed', 'false' );
});

window.ocvb = {
    allow_close_expiry: 2,

    init: function ( allow_close_expiry ) {
        var keep_banner_closed = false;
        var allow_close = jQuery( '#ocvb-container' ).data( 'allow-close' );
        var display_type = jQuery( '#ocvb-container' ).data( 'display-type' );

        window.ocvb.allow_close_expiry = allow_close_expiry == 0 ? 365 : allow_close_expiry;

        if ( typeof Cookies != 'undefined' )
            keep_banner_closed = Cookies.get( 'ocvb-keep-banner-closed' ) == 'false' ? false : true;

        if( !allow_close ) {
            if ( typeof Cookies != 'undefined' ) {
                Cookies.set( 'ocvb-keep-banner-closed', 'false' );
                keep_banner_closed = false;
            }
        }

        if ( display_type == 'none' ) return;

        if ( jQuery( '#ocvb-container' ).length > 0 && !keep_banner_closed ) {
            var container_height = jQuery( '#ocvb-container' ).outerHeight();

            jQuery( 'body' ).prepend( jQuery( '#ocvb-container' ) );

            if ( display_type == 'banner' ) jQuery( '#ocvb-container' ).css( 'height', container_height ).removeClass( 'not-ready' ).addClass( 'ready' );
            if ( display_type == 'leaderboard' ) jQuery( '#ocvb-container' ).css( 'height', container_height ).removeClass( 'not-ready' ).addClass( 'ready' );
            if ( display_type == 'overlay' ) jQuery( '#ocvb-container #ocvb-body' ).removeClass( 'not-ready' ).addClass( 'ready' );

            setTimeout( function () { 
                jQuery( '#ocvb-container' ).removeClass( 'ready' ).addClass( 'ready-and-display' );
                jQuery( document ).on( 'click', '#ocvb-container #ocvb-container-close-button', window.ocvb.close_banner );

                if ( display_type == 'banner' ) {
                    window.scrollTo( { top: 0, left: 0, behavior: 'auto' } );
                }

                if ( display_type == 'overlay' ) {
                    var container_body_height = jQuery( '#ocvb-container #ocvb-body' ).outerHeight();
                    jQuery( '#ocvb-container #ocvb-body' ).css( 'margin-top', - ( container_body_height / 2 ) );
                }
                
                if ( display_type == 'leaderboard' ) {
                    var container_body_height = jQuery( '#ocvb-container #ocvb-body' ).outerHeight();
                    jQuery( 'body' ).css( 'margin-bottom', container_body_height );
                }
            }, 100 );
        }
    },

    close_banner: function ( evt ) {
        var display_type = jQuery( '#ocvb-container' ).data( 'display-type' );

        if ( typeof Cookies != 'undefined')
            Cookies.set( 'ocvb-keep-banner-closed', 'true', { expires: window.ocvb.allow_close_expiry } );

        jQuery( '#ocvb-container' ).removeClass( 'ready-and-display ready ocvb-enabled' ).addClass( 'not-ready ocvb-disabled' );

        if( display_type == 'leaderboard' )
            jQuery( 'body' ).css( 'margin-bottom', '0' );

        evt.stopPropagation();
        return false;
    }
}