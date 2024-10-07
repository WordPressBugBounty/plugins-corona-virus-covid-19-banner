jQuery( document ).ready( function ( $ ) { 
  $( document ).on( 'click', '.settings-image-option', ocvb_display_view_changed );
  $( document ).on( 'change', '[name=orchestrated_corona_virus_banner_enabled]', ocvb_enabled_changed );
  $( document ).on( 'change', '[name=orchestrated_corona_virus_banner_allow_close]', ocvb_allow_close_changed );
  $( document ).on( 'change', '[name=orchestrated_corona_virus_banner_allow_close_expiry]', ocvb_allow_close_expiry_changed );
  $( document ).on( 'change', '[name=orchestrated_corona_virus_banner_display_type]', ocvb_display_type_changed );
  $( document ).on( 'change, keyup', '[name=orchestrated_corona_virus_banner_message_title]', ocvb_title_changed );
  $( document ).on( 'change', '[name=orchestrated_corona_virus_banner_message_title_header_size]', ocvb_title_header_size_changed );
  $( document ).on( 'change, keyup', '[name=orchestrated_corona_virus_banner_message_text]', ocvb_message_changed );
  $( document ).on( 'change', '[name=orchestrated_corona_virus_banner_message_alignment]', ocvb_internal_alignment_changed );
  $( document ).on( 'change', '[name=orchestrated_corona_virus_banner_internal_link]', ocvb_internal_link_changed );
  $( document ).on( 'change, keyup', '[name=orchestrated_corona_virus_banner_external_link]', ocvb_external_link_changed );
  $( document ).on( 'change, keyup', '[name=orchestrated_corona_virus_banner_link_text]', ocvb_link_text_changed );
  $( document ).on( 'change', '[name=orchestrated_corona_virus_banner_foreground_color]', ocvb_foreground_color_changed );
  $( document ).on( 'change', '[name=orchestrated_corona_virus_banner_background_color]', ocvb_background_color_changed );

  $( '[name=orchestrated_corona_virus_banner_enabled], [name=orchestrated_corona_virus_banner_allow_close], [name=orchestrated_corona_virus_banner_allow_close_expiry], [name=orchestrated_corona_virus_banner_display_type], [name=orchestrated_corona_virus_banner_message_alignment], [name=orchestrated_corona_virus_banner_internal_link], [name=orchestrated_corona_virus_banner_data_country]' ).trigger( 'change' );
  $( '[name=orchestrated_corona_virus_banner_link_text], [name=orchestrated_corona_virus_banner_message_title], [name=orchestrated_corona_virus_banner_message_text], [name=orchestrated_corona_virus_banner_external_link], [name=orchestrated_corona_virus_banner_foreground_color], [name=orchestrated_corona_virus_banner_background_color]' ).trigger( 'keyup' );

  var display_type = $( '[name=orchestrated_corona_virus_banner_display_type]' ).val();
  $( '.settings-image-option .container' ).removeClass( 'selected' );
  $( '.settings-image-option-' + display_type ).find( '.container' ).addClass( 'selected' );
  $( '.settings-image-option-' + display_type ).trigger( 'click' );

  $( document ).foundation();

});

var $ = jQuery;
var display = {
  enabled: false,
  allow_close: false,
  allow_close_expiry: 2,
  display_type: 'none',
  title: '',
  title_header_size: 'h4',
  message: '',
  alignment: 'center',
  internal_link: '',
  external_link: '',
  link_text: '',
  foreground_color: '',
  background_color: '',
  data_last_updated: 'just now',
}

function update_display() {

  var $field_enabled_elm = $( '[name=orchestrated_corona_virus_banner_enabled]' );
  var $field_allow_close_elm = $( '[name=orchestrated_corona_virus_banner_allow_close]:checked' );
  var $preview_container_elm = $( '#ocvb-container' );
  var $preview_body_elm = $( '#ocvb-container #ocvb-body' );
  var $preview_title_elm = $( '#ocvb-container-notice-text ' + display.title_header_size );
  var $preview_message_elm = $( '#ocvb-container-notice-text p' );
  var $preview_link_elm = $( '#ocvb-container-notice-link' );
  var $preview_link_anchor_elm = $( '#ocvb-container-notice-link a' );
  var $preview_close_elm = $( '#ocvb-container #ocvb-container-close-button' );
  var $preview_enabled_elm = $( '[data-preview-enabled=true]' );
  var $preview_disabled_elm = $( '[data-preview-enabled=false]' );

  if ( display.enabled ) {
    $field_enabled_elm.val( true );
    $preview_container_elm.removeClass( 'preview-disabled' );
    $preview_enabled_elm.show();
    $preview_disabled_elm.hide();
    $( '.display-required' ).show();
    ocvb_show_display_required();
  } else {
    $field_enabled_elm.val( false );
    $preview_container_elm.addClass( 'preview-disabled' );
    $preview_enabled_elm.hide();
    $preview_disabled_elm.show();
    display.display_type = "none";
    ocvb_hide_display_required();
  }

  switch ( display.display_type ) {
    case "none":
      $( '.display-type-banner-required' ).hide();
      break;
    case "banner":
      $( '.display-type-banner-required' ).show();
      break;
    case "overlay":
      $( '.display-type-banner-required' ).hide();
      break;
    case "leaderboard":
      $( '.display-type-banner-required' ).show();
      break;
  }

  if ( ( ( display.display_type == 'banner' || display.display_type == 'leaderboard' ) && $field_allow_close_elm.val() == "true" ) || display.display_type == 'overlay' ) {
    $preview_close_elm.show();
  } else {
    $preview_close_elm.hide();
  }

  $( '.option-link-url' ).hide();
  $( '.option-link-text' ).hide();

  switch ( display.link_type ) {
    case "none":
      $( '.option-link-url' ).hide();
      $( '.option-link-text' ).hide();
      $preview_link_elm.hide();
      break;
    case "ext":
      $( '.option-link-url' ).show();
      $( '.option-link-text' ).show();
      $preview_link_elm.show();
      $preview_link_anchor_elm.attr( 'href', display.external_link );
      break;
    default:
      if ( parseInt ( display.link_type ) ) {
        $( '.option-link-url' ).hide();
        $( '.option-link-text' ).show();  
        $preview_link_elm.show();
      } else {
        $( '.option-link-url' ).hide();
        $( '.option-link-text' ).hide();
        $preview_link_elm.show();
        $preview_link_anchor_elm.attr( 'href', display.external_link );
      }
      break;
  }

  //  Header
  $( '#ocvb-container-notice-text h1, #ocvb-container-notice-text h2, #ocvb-container-notice-text h3, #ocvb-container-notice-text h4, #ocvb-container-notice-text h5, #ocvb-container-notice-text h6' ).replaceWith( function() {
    return jQuery( "<" + display.title_header_size + "/>", { html: jQuery( this ).html() } );
  } );

  $preview_title_elm = $( '#ocvb-container-notice-text ' + display.title_header_size );

  //  Content
  $preview_title_elm.html( display.title );
  $preview_message_elm.html( display.message );

  //  Foreground/background color
  $preview_container_elm.css( { 'color': display.foreground_color, 'text-align': display.alignment } );
  $preview_title_elm.css( { 'color': display.foreground_color, 'text-align': display.alignment } );
  $preview_message_elm.css( { 'color': display.foreground_color, 'text-align': display.alignment } );
  $preview_link_elm.css( { 'color': display.foreground_color, 'text-align': display.alignment } );
  $preview_body_elm.css( 'background-color', display.background_color );

  //  Link
  $preview_link_anchor_elm.text( display.link_text );

  //  Container references
  $preview_container_elm.attr( { 'data-allow-close': display.allow_close, 'data-message-alignment': display.alignment } )
}

function ocvb_show_display_required() {
  $( '.display-required' ).css( { 'opacity': 1, 'pointer-events': 'inherit' } );
  $( '[name=orchestrated_corona_virus_banner_message_title], [name=orchestrated_corona_virus_banner_message_text], [name=orchestrated_corona_virus_banner_foreground_color], [name=orchestrated_corona_virus_banner_background_color]' ).attr( 'required', '' );
  $( '.display-required' ).find( 'select' ).attr( 'readonly', null );
  $( '.display-required' ).find( 'input' ).attr( 'readonly', null );
  $( '.display-required' ).find( 'textarea' ).attr( 'readonly', null );
}

function ocvb_hide_display_required() {
  $( '.display-required' ).css( { 'opacity': 0.4, 'pointer-events': 'none' } );
  $( '[name=orchestrated_corona_virus_banner_message_title], [name=orchestrated_corona_virus_banner_message_text], [name=orchestrated_corona_virus_banner_foreground_color], [name=orchestrated_corona_virus_banner_background_color]' ).attr( 'required', null );
  $( '.display-required' ).find( 'select' ).attr( 'readonly', 'readonly' );
  $( '.display-required' ).find( 'input' ).attr( 'readonly', 'readonly' );
  $( '.display-required' ).find( 'textarea' ).attr( 'readonly', 'readonly' );
}

function ocvb_display_view_changed( evt ) {
  var selected_display_type = $( evt.target ).data( 'selection' );
  var $field_display_type_elm = $( '[name=orchestrated_corona_virus_banner_display_type]' );
  $( '.settings-image-option .container' ).removeClass( 'selected' );
  $( '[data-selection=' + selected_display_type + ']' ).find( '.container' ).addClass( 'selected' );
  switch ( selected_display_type ) {
    case 'none':
      display.enabled = false;
      display.display_type = 'none';
      break;
    case 'banner':
      display.enabled = true;
      display.display_type = 'banner';
      break;
    case 'overlay':
      display.enabled = true;
      display.display_type = 'overlay';
      break;
    case 'leaderboard':
      display.enabled = true;
      display.display_type = 'leaderboard';
      break;
  }
  $field_display_type_elm.val( display.display_type );
  $( '.display-type-status' ).text( $( '[data-display-type-caption=' + display.display_type + ']' ).text() );
  update_display();
}

function ocvb_enabled_changed( evt ) {
  display.enabled = evt.target.checked;
  update_display();
}

function ocvb_allow_close_changed( evt ) {
  display.allow_close = evt.target.value == "true" ? true : false;
  update_display();
}

function ocvb_allow_close_expiry_changed( evt ) {
  display.allow_close_expiry = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_display_type_changed( evt ) {
  display.display_type = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_title_changed( evt ) {
  display.title = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_title_header_size_changed( evt ) {
  display.title_header_size = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_message_changed( evt ) {
  display.message = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_internal_alignment_changed( evt ) {
  display.alignment = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_internal_link_changed( evt ) {
  display.link_type = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_external_link_changed( evt ) {
  display.external_link = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_link_text_changed( evt ) {
  display.link_text = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_foreground_color_changed( evt ) {
  display.foreground_color = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_background_color_changed( evt ) {
  display.background_color = ocvb_filter_text( evt.target.value );
  update_display();
}

function ocvb_number_formatted( number ) {
  var parts = number.toString().split( "." );
  parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, "," );
  return parts.join( "." );
}

function ocvb_filter_text( text ) {
  return text.replace( /<.*?>/g, '' );
}