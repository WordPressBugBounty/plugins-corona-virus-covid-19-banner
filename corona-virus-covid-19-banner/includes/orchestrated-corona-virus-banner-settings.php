<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Orchestrated_Corona_Virus_Banner_Settings {

	private static $_instance = null;
	public $parent = null;
	public $base = '';
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'orchestrated_corona_virus_banner_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
		$this->check_install();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Simple Website Banner', 'corona-virus-covid-19-banner' ) , __( 'Simple Website Banner', 'corona-virus-covid-19-banner' ) , 'manage_options' , 'orchestrated_corona_virus_banner' . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {
	  	wp_register_script( 'orchestrated_corona_virus_banner' . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery', 'corona-virus-covid-19-banner' ), '1.0.0', true );
	  	wp_enqueue_script( 'orchestrated_corona_virus_banner' . '-settings-js' );
	}


	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . 'orchestrated_corona_virus_banner' . '_settings">' . __( 'Settings', 'corona-virus-covid-19-banner' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Display notice
	 * @return string Returns HTML to display notice
	 */
	public function get_notice_display ( $preview = false ) {
		$option_values = $this->get_option_values();
		$banner_state_class = $option_values['enabled'] ? "ocvb-enabled" : "ocvb-disabled";
		$banner_display_type_class = "ocvb-display-type-banner";
		$link_state_class = "ocvb-disabled";
		$link_to_direct_to = "";
		$ready_state = $preview ? "ready-and-display" : "not-ready";

		switch( $option_values['display_type'] ) {
			case "banner":
				$banner_display_type_class = "ocvb-display-type-banner";
			break;
			case "overlay":
				$banner_display_type_class = "ocvb-display-type-overlay";
			break;
			case "leaderboard":
				$banner_display_type_class = "ocvb-display-type-leaderboard";
			break;
		}

		if ( ( $option_values['display_type'] == "leaderboard" || $option_values['display_type'] == "banner" ) && $option_values['allow_close'] == "true" ) {
			$close_button_state_class = "ocvb-enabled";
		} else if ( $option_values['display_type'] == "overlay" ) {
			$close_button_state_class = "ocvb-enabled";
		} else {
			$close_button_state_class = "ocvb-disabled";
		}

		if ( $option_values[ 'internal_link' ] == "ext" ) {
			//	Link to external URL
			if ( filter_var( $option_values[ 'external_link' ], FILTER_VALIDATE_URL ) === FALSE ) {
				//	Reset because URL is invalid: malformed
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_internal_link', "none" );
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_external_link', "" );
				$link_state_class = "ocvb-disabled";
			} else {
				$link_to_direct_to = $option_values[ 'external_link' ];
				$link_state_class = "ocvb-enabled";
			}
		} else if ( $option_values[ 'internal_link' ] == "none" ) {
			//	Remove any page/link info
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_internal_link', "none" );
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_external_link', "" );
			$link_state_class = "ocvb-disabled";
		} else {
			//	Link to internal Page
			if ( 'publish' == get_post_status ( $option_values[ 'internal_link' ] ) ) {
				$page_url = get_page_link ( $option_values[ 'internal_link' ] );
				$link_to_direct_to = $page_url;
				$link_state_class = "ocvb-enabled";
			} else {
				//	Reset because Page is not found
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_internal_link', "none" );
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_external_link', "" );
				$link_state_class = "ocvb-disabled";
			}
		}

		if ( $preview ) {
			$container_css = "";
			$container_css_mobile = "";
			$banner_display_type_class = "ocvb-display-type-banner";
			$close_button_state_class = "ocvb-enabled";
		}

		return <<<HTML
			<style>
				#ocvb-container #ocvb-body {
					color: ${option_values[ 'foreground_color' ]};
					background-color: ${option_values[ 'background_color' ]};
					text-align: ${option_values[ 'message_alignment' ]};
					${option_values[ 'container_css' ]}
				}
				@media screen and (max-width: 480px) {
					#ocvb-container #ocvb-body {
						${option_values[ 'container_css_mobile' ]}
					}
				}
				#ocvb-container #ocvb-body h1,
				#ocvb-container #ocvb-body h2,
				#ocvb-container #ocvb-body h3,
				#ocvb-container #ocvb-body h4,
				#ocvb-container #ocvb-body h5,
				#ocvb-container #ocvb-body h6 {
					color: ${option_values[ 'foreground_color' ]};
					text-align: ${option_values[ 'message_alignment' ]};
					margin-top: 4px;
					margin-bottom: 10px;
				}
				#ocvb-container #ocvb-body p {
					color: ${option_values[ 'foreground_color' ]};
					text-align: ${option_values[ 'message_alignment' ]};
				}
				#ocvb-container #ocvb-body a {
					color: ${option_values[ 'foreground_color' ]};
					text-align: ${option_values[ 'message_alignment' ]};
				}
				#ocvb-container[data-message-alignment="right"][data-allow-close="true"] #ocvb-container-notice-text {
					padding-right: 30px;
				}
				#ocvb-container[data-allow-close="true"] #ocvb-container-notice-text {
					padding-right: 20px;
				}
			</style>
			<script>
				if ( typeof Cookies != 'undefined' ) 
					if ( "${option_values['allow_close']}" == false && ( "${option_values['display_type']}" == "leaderboard" || "${option_values['display_type']}" == "banner" ) )
						Cookies.set( 'ocvb-keep-banner-closed', 'false' );
			</script>
			<div id="ocvb-container" class="${ready_state} ${banner_state_class} ${banner_display_type_class}" data-message-alignment="${option_values['message_alignment']}" data-display-type="${option_values['display_type']}" data-allow-close="${option_values['allow_close']}" data-title-header-size="${option_values['message_title_header_size']}">
				<div id="ocvb-body">
					<div id="ocvb-container-close-button" class="${close_button_state_class}"><a href="#">x</a></div>
					<div id="ocvb-container-notice-text">
						<${option_values[ 'message_title_header_size' ]}>${option_values[ 'message_title' ]}</${option_values[ 'message_title_header_size' ]}>
						<p>${option_values[ 'message_text' ]}</p>
						<div id="ocvb-container-notice-link" class="${link_state_class}">
							<a href="${link_to_direct_to}" target="${option_values[ 'link_target' ]}">${option_values[ 'link_text' ]}</a>
						</div>
					</div>
				</div>
			</div>
HTML;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {
		$option_values = $this->get_option_values();
		$pages = $this->parent->get_pages();

		$settings['settings'] = array(
			'title'						=> '',
			'description'				=> '',
			'fields'					=> array()
		);

		$settings = apply_filters( 'orchestrated_corona_virus_banner' . '_settings_fields', $settings );

		return $settings;
	}


	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_enabled', array( 'type' => 'boolean', 'default' => false ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_display_type', array( 'type' => 'string', 'default' => 'none' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_allow_close', array( 'type' => 'boolean', 'default' => false ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_allow_close_expiry', array( 'type' => 'integer', 'default' => 2 ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_display_page', array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_message_title', array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_message_title_header_size', array( 'type' => 'string', 'default' => 'h4' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_message_text', array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_message_alignment', array( 'type' => 'string', 'default' => 'center' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_internal_link', array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_external_link', array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_link_text', array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_link_target', array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_foreground_color', array( 'type' => 'string', 'default' => '#ffffff' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_background_color', array( 'type' => 'string', 'default' => '#cc0000' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_container_css', array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_container_css_mobile', array( 'type' => 'string', 'default' => '' ) );

		register_setting( 'orchestrated_corona_virus_banner' . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date', array( 'type' => 'string', 'default' => gmdate( 'Y-m-d h:i:sa' ) ) );
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {
		ob_start();

		$admin_url = admin_url('admin-ajax.php');

		$notice_display = $this->get_notice_display( true );

		$option_values = $this->get_option_values();

		$field_enabled_value = $option_values['enabled'] == true ? "checked" : "";
		$field_display_type_value = in_array( $option_values['display_type'], array( 'none', 'banner', 'overlay', 'leaderboard' ) ) ? $option_values['display_type'] : 'none';
		$field_allow_close_value = $option_values['allow_close'] == "true" ? "true" : "false";
		$field_allow_close_expiry_value = $option_values['allow_close_expiry'];
		$field_display_page_value = wp_strip_all_tags( $option_values['display_page'] );
		$field_message_title_value = wp_strip_all_tags( $option_values['message_title'] );
		$field_message_title_header_size = in_array( $option_values['message_title_header_size'], array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) ) ? $option_values['message_title_header_size'] : 'h4';
		$field_message_text_value = wp_strip_all_tags( $option_values['message_text'] );
		$field_message_alignment_value = in_array( $option_values[ 'message_alignment'], array( 'left', 'center', 'right', 'justify', 'inherit' ) ) ? $option_values[ 'message_alignment' ] : 'center';
		$field_external_link_value = wp_strip_all_tags( $option_values['external_link'] );
		$field_internal_link_value = wp_strip_all_tags( $option_values['internal_link'] );
		$field_link_text_value = wp_strip_all_tags( $option_values['link_text'] );
		$field_link_target_value = wp_strip_all_tags( $option_values['link_target'] );
		$field_foreground_color_value = wp_strip_all_tags( $option_values['foreground_color'] );
		$field_background_color_value = wp_strip_all_tags( $option_values['background_color'] );
		$field_container_css_value = wp_strip_all_tags( $option_values['container_css'] );
		$field_container_css_mobile_value = wp_strip_all_tags( $option_values['container_css_mobile'] );

		$pages_options_html = $this->get_select_options( 'pages', $field_internal_link_value );
		$alignment_options_html = $this->get_select_options( 'alignment', $field_message_alignment_value );
		$display_type_options_html = $this->get_select_options( 'display_type', $field_display_type_value );
		$link_target_options_html = $this->get_select_options( 'targets', $field_link_target_value );
		$header_size_options_html = $this->get_select_options( 'header_sizes', $field_message_title_header_size );
		$display_page_options_html = $this->get_select_options( 'display_pages', $field_display_page_value );

		$field_allow_close_checked_true = ( $field_allow_close_value == "true" || $field_allow_close_value == "checked" ) ? "checked" : "";
		$field_allow_close_checked_false = $field_allow_close_value == "false" ? "checked" : "";

		$data_last_run_date = $this->time_elapsed_string( ( new DateTime( $option_values[ 'last_job_run_date' ] ) )->format( 'Y-m-d h:i:sa' ) );

		$asset_url = $this->parent->assets_url;

		$display_states = [
			'none' => __( 'Notice is currently set to not display to users. Choose "Banner" or "Overlay" to display the notice.', 'corona-virus-covid-19-banner' ),
			'banner' => __( 'Notice is displaying on the website as a Banner. If you are experiencing display issues, try "Overlay."', 'corona-virus-covid-19-banner' ),
			'overlay' => __( 'Notice is displaying on the website as an Overlay.', 'corona-virus-covid-19-banner' ),
			'leaderboard' => __( 'Notice is displaying on the website as a Leaderboard.', 'corona-virus-covid-19-banner' ),
		];

		$labels = $this->get_labels();


		echo '<form method="post" action="options.php" enctype="multipart/form-data" data-abide novalidate>';
		settings_fields( 'orchestrated_corona_virus_banner' . '_settings' );
		echo '<div id="ocvb-admin-container" data-admin-url="' . wp_strip_all_tags($admin_url) . '">';
		echo '<input id="enabled" type="hidden" name="orchestrated_corona_virus_banner_enabled" value="' . wp_strip_all_tags($field_enabled_value) . '">';
		echo '<input id="display_type" type="hidden" name="orchestrated_corona_virus_banner_display_type" value="' . wp_strip_all_tags($field_display_type_value) . '">';
		echo '<div class="grid-x grid-container grid-padding-y admin-settings">';
		echo '<div class="cell small-12">';
		echo '<div class="callout">';
		echo '<h2>' . wp_strip_all_tags($labels['ocvb_title']) . '</h2>';
		echo '<p>' . wp_strip_all_tags($labels['ocvb_description']) . '</p>';
		echo '</div>';
		echo '<div data-abide-error class="alert callout" style="display: none;">';
		echo '<p>' . wp_strip_all_tags($labels['please_complete_notice']) . '</p>';
		echo '</div>';
		echo '<ul class="tabs" data-tabs id="setting-tabs" data-deep-link="true">';
		echo '<li class="tabs-title is-active"><a href="#display" aria-selected="true">' . wp_strip_all_tags($labels['display']) . '</a></li>';
		echo '<li class="tabs-title"><a href="#options">' . wp_strip_all_tags($labels['options']) . '</a></li>';
		echo '<li class="tabs-title"><a href="#preview">' . wp_strip_all_tags($labels['preview']) . '</a></li>';
		echo '</ul>';
		echo '<div class="tabs-content grid-x" data-tabs-content="setting-tabs">';
		echo '<div class="tabs-panel is-active cell small-12" id="display">';
		echo '<label>' . wp_strip_all_tags($labels['how_display_notice']) . '</label>';
		echo '<p></p>';
		echo '<div class="grid-x grid-padding-y align-middle">';
		echo '<div class="small-6 cell">';
		echo '<div class="grid-x">';
		echo '<div class="settings-image-option settings-image-option-none cell small-3" data-selection="none">';
		echo '<div class="container">';
		echo '<img src="' . wp_strip_all_tags($asset_url) . '/images/display-type-none.png" alt="' . wp_strip_all_tags($labels['display']) . ': ' . wp_strip_all_tags($labels['none']) . '" />';
		echo '<label>' . wp_strip_all_tags($labels['none']) . '</label>';
		echo '</div>';
		echo '</div>';
		echo '<div class="settings-image-option settings-image-option-banner cell small-3" data-selection="banner">';
		echo '<div class="container">';
		echo '<img src="' . wp_strip_all_tags($asset_url) . '/images/display-type-banner.png" alt="' . wp_strip_all_tags($labels['display']) . ': ' . wp_strip_all_tags($labels['banner']) . '" />';
		echo '<label>' . wp_strip_all_tags($labels['banner']) . '</label>';
		echo '</div>';
		echo '</div>';
		echo '<div class="settings-image-option settings-image-option-leaderboard cell small-3" data-selection="leaderboard">';
		echo '<div class="container">';
		echo '<img src="' . wp_strip_all_tags($asset_url) . '/images/display-type-leaderboard.png" alt="' . wp_strip_all_tags($labels['display']) . ': ' . wp_strip_all_tags($labels['leaderboard']) . '" />';
		echo '<label>' . wp_strip_all_tags($labels['leaderboard']) . '</label>';
		echo '</div>';
		echo '</div>';
		echo '<div class="settings-image-option settings-image-option-overlay cell small-3" data-selection="overlay">';
		echo '<div class="container">';
		echo '<img src="' . wp_strip_all_tags($asset_url) . '/images/display-type-overlay.png" alt="' . wp_strip_all_tags($labels['display']) . ': ' . wp_strip_all_tags($labels['overlay']) . '" />';
		echo '<label>' . wp_strip_all_tags($labels['overlay']) . '</label>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="small-4 small-offset-1 end cell">';
		echo '<div class="grid-x align-middle callout text-center">';
		echo '<div class="small-12 cell display-type-status">';
		echo $display_states[$field_display_type_value];
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="grid-x grid-padding-y display-required">';
		echo '<div class="small-12 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['title']) . '</div>';
		echo '<input id="message_title" type="text" name="orchestrated_corona_virus_banner_message_title" placeholder="' . wp_strip_all_tags($labels['enter_notice_title']) . '" value="' . wp_strip_all_tags($field_message_title_value) . '">';
		echo '<span class="form-error" data-form-error-for="message_title">' . wp_strip_all_tags($labels['required']) . '</span>';
		echo '</label>';
		echo '</div>';
		echo '<div class="small-12 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['message']) . '</div>';
		echo '<textarea id="message_text" rows="5" cols="50" name="orchestrated_corona_virus_banner_message_text" placeholder="' . wp_strip_all_tags($labels['enter_notice_text']) . '">' . wp_strip_all_tags($field_message_text_value) . '</textarea>';
		echo '<span class="form-error" data-form-error-for="message_text">' . wp_strip_all_tags($labels['required']) . '</span>';
		echo '</label>';
		echo '</div>';
		echo '</div>';
		echo '<div class="grid-x grid-padding-y display-required">';
		echo '<div class="small-4 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['where_to_direct_users']) . '</div>';
		echo '<select name="orchestrated_corona_virus_banner_internal_link" id="internal_link">';
		echo ($pages_options_html);
		echo '</select>';
		echo '</label>';
		echo '</div>';
		echo '<div class="small-4 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['link_target']) . '</div>';
		echo '<select name="orchestrated_corona_virus_banner_link_target" id="link_target">';
		echo ($link_target_options_html);
		echo '</select>';
		echo '</label>';
		echo '</div>';
		echo '<div class="small-3 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['display_page']) . '</div>';
		echo '<select name="orchestrated_corona_virus_banner_display_page" id="display_page">';
		echo ($display_page_options_html);
		echo '</select>';
		echo '</label>';
		echo '</div>';
		echo '</div>';
		echo '<div class="grid-x grid-padding-y display-required option-link-url">';
		echo '<div class="small-6 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['link_url']) . '</div>';
		echo '<input id="external_link" type="url" name="orchestrated_corona_virus_banner_external_link" placeholder="http://www.host.com" value="' . wp_strip_all_tags($field_external_link_value) . '" pattern="url">';
		echo '</label>';
		echo '</div>';
		echo '</div>';
		echo '<div class="grid-x grid-padding-y display-required option-link-text">';
		echo '<div class="small-6 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['link_label']) . '</div>';
		echo '<input id="link_text" type="text" name="orchestrated_corona_virus_banner_link_text" placeholder="' . wp_strip_all_tags($labels['more_information']) . '" value="' . wp_strip_all_tags($field_link_text_value) . '">';
		echo '</label>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="tabs-panel" id="options">';
		echo '<div class="grid-x display-required callout">';
		echo '<div class="small-12 cell">';
		echo '<h3>' . wp_strip_all_tags($labels['design']) . '</h3>';
		echo '</div>';
		echo '<div class="small-3 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['header_size']) . '</div>';
		echo '<select name="orchestrated_corona_virus_banner_message_title_header_size" id="message_title_header_size">';
		echo $header_size_options_html;
		echo '</select>';
		echo '</label>';
		echo '</div>';
		echo '<div class="small-3 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['text_alignment']) . '</div>';
		echo '<select name="orchestrated_corona_virus_banner_message_alignment" id="message_alignment">';
		echo $alignment_options_html;
		echo '</select>';
		echo '</label>';
		echo '</div>';
		echo '<div class="small-3 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['foreground_color']) . '</div>';
		echo '<input type="text" id="foreground_color" name="orchestrated_corona_virus_banner_foreground_color" class="color jscolor {hash:true}" value="' . wp_strip_all_tags($field_foreground_color_value) . '" autocomplete="off" pattern="color">';
		echo '<span class="form-error" data-form-error-for="foreground_color">' . wp_strip_all_tags($labels['required']) . '</span>';
		echo '</label>';
		echo '</div>';
		echo '<div class="small-3 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['background_color']) . '</div>';
		echo '<input type="text" id="background_color" name="orchestrated_corona_virus_banner_background_color" class="color jscolor {hash:true}" value="' . wp_strip_all_tags($field_background_color_value) . '" autocomplete="off" pattern="color">';
		echo '<span class="form-error" data-form-error-for="background_color">' . wp_strip_all_tags($labels['required']) . '</span>';
		echo '</label>';
		echo '</div>';
		echo '</div>';
		echo '<div class="grid-x display-required callout display-type-banner-required">';
		echo '<div class="small-12 cell">';
		echo '<h3>' . wp_strip_all_tags($labels['user_preferences']) . '</h3>';
		echo '</div>';
		echo '<div class="small-6 cell">';
		echo '<label>' . wp_strip_all_tags($labels['should_allowed_close_notice']) . '</label>';
		echo '<p></p>';
		echo '<div>';
		echo '<span class="radio-item"><input type="radio" name="orchestrated_corona_virus_banner_allow_close" value="true" id="allow_close_true" ' . wp_strip_all_tags($field_allow_close_checked_true) . '> ' . wp_strip_all_tags($labels['yes']) . '</span>';
		echo '<span class="radio-item"><input type="radio" name="orchestrated_corona_virus_banner_allow_close" value="false" id="allow_close_false" ' . wp_strip_all_tags($field_allow_close_checked_false) . '> ' . wp_strip_all_tags($labels['no']) . '</span>';
		echo '</div>';
		echo '</div>';
		echo '<div class="small-6 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['how_many_days_notice']) . '</div>';
		echo '<input id="allow_close_expiry" type="number" name="orchestrated_corona_virus_banner_allow_close_expiry" placeholder="2" value="' . wp_strip_all_tags($field_allow_close_expiry_value) . '" pattern="number">';
		echo '<small>' . wp_strip_all_tags($labels['use_0_to_never_reappear']) . '</small>';
		echo '</label>';
		echo '</div>';
		echo '</div>';
		echo '<div class="grid-x display-required callout">';
		echo '<div class="small-12 cell">';
		echo '<h3>' . wp_strip_all_tags($labels['styling']) . '</h3>';
		echo '</div>';
		echo '<div class="small-6 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['custom_css']) . '</div>';
		echo '<textarea id="container_css" rows="5" cols="50" name="orchestrated_corona_virus_banner_container_css" placeholder="e.g. margin-top: 20px;">' . wp_strip_all_tags($field_container_css_value) . '</textarea>';
		echo '</label>';
		echo '</div>';
		echo '<div class="small-6 cell">';
		echo '<label>';
		echo '<div class="form-label">' . wp_strip_all_tags($labels['custom_css_mobile']) . '</div>';
		echo '<textarea id="container_css_mobile" rows="5" cols="50" name="orchestrated_corona_virus_banner_container_css_mobile" placeholder="e.g. margin-top: 20px;">' . wp_strip_all_tags($field_container_css_mobile_value) . '</textarea>';
		echo '</label>';
		echo '</div>';
		echo '<div class="small-12 cell"><small>' . wp_strip_all_tags($labels['styles_not_applied_to_preview']) . '</small></div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="tabs-panel" id="preview">';
		echo '<div class="grid-x">';
		echo '<div class="ocvb-preview-container small-12 cell" data-preview-enabled="true">';
		echo $notice_display;
		echo '</div>';
		echo '<div class="small-6 small-offset-3 cell" data-preview-enabled="false">';
		echo '<div class="callout text-center">';
		echo wp_strip_all_tags($labels['preview_not_available']);
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="cell small-12">';
		echo '<div class="submit">';
		echo '<input name="Submit" type="submit" class="button-primary" value="' . wp_strip_all_tags($labels['save_settings']) . '" />';
		echo '</div>';
		echo '</div>';
		echo '<div class="display-type-caption" data-display-type-caption="none">' . wp_strip_all_tags($display_states['none']) . '</div>';
		echo '<div class="display-type-caption" data-display-type-caption="banner">' . wp_strip_all_tags($display_states['banner']) . '</div>';
		echo '<div class="display-type-caption" data-display-type-caption="overlay">' . wp_strip_all_tags($display_states['overlay']) . '</div>';
		echo '<div class="display-type-caption" data-display-type-caption="leaderboard">' . wp_strip_all_tags($display_states['leaderboard']) . '</div>';
		echo '</div>';
		echo '</form>';
	}

	public function get_labels() {
		return [
			'ocvb_title' => __( 'Simple Website Banner', 'corona-virus-covid-19-banner' ),
			'ocvb_description' => __( 'This is a very simple plugin with a sole purpose of allowing you to inform your visitors of an upcoming event, updated store hours, or other important message you want to display.', 'corona-virus-covid-19-banner' ),
			'please_complete_notice' => __( 'Please complete all required fields.', 'corona-virus-covid-19-banner' ),
			'title' => __( 'Title', 'corona-virus-covid-19-banner' ),
			'message' => __( 'Message', 'corona-virus-covid-19-banner' ),
			'display' => __( 'Display', 'corona-virus-covid-19-banner' ),
			'options' => __( 'Options', 'corona-virus-covid-19-banner' ),
			'about' => __( 'About', 'corona-virus-covid-19-banner' ),
			'preview' => __( 'Preview', 'corona-virus-covid-19-banner' ),
			'none' => __( 'None', 'corona-virus-covid-19-banner' ),
			'banner' => __( 'Banner', 'corona-virus-covid-19-banner' ),
			'overlay' => __( 'Overlay', 'corona-virus-covid-19-banner' ),
			'leaderboard' => __( 'Leaderboard', 'corona-virus-covid-19-banner' ),
			'design' => __( 'Design', 'corona-virus-covid-19-banner' ),
			'styling' => __( 'Styling', 'corona-virus-covid-19-banner' ),
			'required' => __( 'Required', 'corona-virus-covid-19-banner' ),
			'yes' => __( 'Yes', 'corona-virus-covid-19-banner' ),
			'no' => __( 'No', 'corona-virus-covid-19-banner' ),
			'enter_notice_title' => __( 'Enter your notice title', 'corona-virus-covid-19-banner' ),
			'enter_notice_text' => __( 'Enter your notice text', 'corona-virus-covid-19-banner' ),
			'how_display_notice' => __( 'How would you like to display the notice?', 'corona-virus-covid-19-banner' ),
			'header_size' => __( 'Message title size', 'corona-virus-covid-19-banner' ),
			'link_url' => __( 'Link URL', 'corona-virus-covid-19-banner' ),
			'link_label' => __( 'Link label', 'corona-virus-covid-19-banner' ),
			'more_information' => __( 'More information', 'corona-virus-covid-19-banner' ),
			'link_target' => __( 'Should the link open in a new tab?', 'corona-virus-covid-19-banner' ),
			'where_to_direct_users' => __( 'Where do you want to direct users for more information?', 'corona-virus-covid-19-banner' ),
			'user_preferences' => __( 'User preferences', 'corona-virus-covid-19-banner' ),
			'save_settings' => __( 'Save Settings', 'corona-virus-covid-19-banner' ),
			'should_allowed_close_notice' => __( 'Should users be allowed to close the notice?', 'corona-virus-covid-19-banner' ),
			'how_many_days_notice' => __( 'How many days before the notice re-appears?', 'corona-virus-covid-19-banner' ),
			'use_0_to_never_reappear' => __( 'Use "0" to never re-appear', 'corona-virus-covid-19-banner' ),
			'text_alignment' => __( 'Text alignment', 'corona-virus-covid-19-banner' ),
			'foreground_color' => __( 'Foreground color', 'corona-virus-covid-19-banner' ),
			'background_color' => __( 'Background color', 'corona-virus-covid-19-banner' ),
			'custom_css' => __( 'Custom CSS', 'corona-virus-covid-19-banner' ),
			'custom_css_mobile' => __( 'Custom CSS (mobile)', 'corona-virus-covid-19-banner' ),
			'styles_not_applied_to_preview' => __( 'Note: Custom CSS is not applied to Preview.', 'corona-virus-covid-19-banner' ),
			'preview_not_available' => __( 'Once you have enabled "Banner" or "Overlay", a preview will appear here. See the "Display" tab to continue.', 'corona-virus-covid-19-banner' ),
			// CALL TO LIKE OUR FACEBOOK PAGE
			'about_line_4' => __( '– Team <a href="http://orchestrated.ca" target="_blank">Orchestrated</a>', 'corona-virus-covid-19-banner' ),
			'world' => __( 'World', 'corona-virus-covid-19-banner' ),
			'just_now' => __( 'just now', 'corona-virus-covid-19-banner' ),
			'api_error' => __( 'There is an issue with data source at the moment.', 'corona-virus-covid-19-banner' ),
			'support_title' => __( 'Support', 'corona-virus-covid-19-banner' ),
			'support_line_1' => __( 'Need a hand with the plugin? Send us <a href="mailto:support@orchestrated.ca?subject=SUPPORT with Simple Web Banner Plugin" target="_blank">an email</a>. We\'ll get back to you within 3-4 business days.', 'corona-virus-covid-19-banner' ),
			'display_page' => __( 'Where to display notice?', 'corona-virus-covid-19-banner' ),
		];
	}

	public function get_option_values () {
		return [
			'enabled' => ( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_enabled' ) ?: false ),
			'display_type' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_display_type' ), FILTER_SANITIZE_STRING ) ?: 'none' ),
			'allow_close' => ( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_allow_close' ) ?: 'false' ),
			'allow_close_expiry' => ( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_allow_close_expiry' ) ?: 2 ),
			'display_page' => ( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_display_page' ) ?: '' ),
			'message_title' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_message_title' ) ), FILTER_SANITIZE_STRING ) ?: '' ),
			'message_title_header_size' => ( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_message_title_header_size' ) ) ?: 'h4' ),
			'message_text' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_message_text' ) ), FILTER_SANITIZE_STRING ) ?: '' ),
			'message_alignment' => ( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_message_alignment' ) ) ?: 'center'  ),
			'internal_link' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_internal_link' ) ), FILTER_SANITIZE_STRING ) ?: 'none' ),
			'external_link' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_external_link' ) ), FILTER_SANITIZE_STRING ) ?: '' ),
			'link_text' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_link_text' ) ), FILTER_SANITIZE_STRING ) ?: __( 'More Information', 'corona-virus-covid-19-banner' ) ),
			'link_target' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_link_target' ) ), FILTER_SANITIZE_STRING ) ?: '' ),
			'foreground_color' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_foreground_color' ) ), FILTER_SANITIZE_STRING ) ?: '#ffffff' ),
			'background_color' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_background_color' ) ), FILTER_SANITIZE_STRING ) ?: '#cc0000' ),
			'container_css' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_container_css' ) ), FILTER_SANITIZE_STRING ) ?: '' ),
			'container_css_mobile' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_container_css_mobile' ) ), FILTER_SANITIZE_STRING ) ?: '' ),
			'last_job_run_date' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date' ) ), FILTER_SANITIZE_STRING ) ?: gmdate( 'Y-m-d h:i:sa' ) ),
			'data_frequency' => ( filter_var( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_frequency' ) ), FILTER_SANITIZE_NUMBER_INT ) ?: 10 ),
		];
	}

	public function get_select_options ( $type = 'pages', $selected_value ) {
		$return_html = "";

		switch( $type ) {
			case 'pages':
				$pages = [
					"none" => __( "No link", 'orchestrated_corona_virus_banner' ),
					"–1" => "––––––––––––––",
					"ext" => __( "Link to another website", 'orchestrated_corona_virus_banner' ),
					"–2" => "––––––––––––––",
				];
				$pages = $pages + $this->parent->get_pages();
				foreach ( $pages as $k => $v ) {
					$selected = false;
					$disabled = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					if ( $v == "––––––––––––––" ) {
						$disabled = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' ' . ( $disabled ? "disabled" : "" ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'display_pages':
				$pages = [
					"" => __( "Display on all Pages", 'orchestrated_corona_virus_banner' ),
					"home" => __( "Only on Homepage", 'orchestrated_corona_virus_banner' ),
				];
				foreach ( $pages as $k => $v ) {
					$selected = false;
					$disabled = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					if ( $v == "––––––––––––––" ) {
						$disabled = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' ' . ( $disabled ? "disabled" : "" ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'alignment':
				$alignments = array(
					'center' => __( 'Center', 'corona-virus-covid-19-banner' ),
					'left' => __( 'Left', 'corona-virus-covid-19-banner' ),
					'right' => __( 'Right', 'corona-virus-covid-19-banner' ),
					'justify' => __( 'Justified', 'corona-virus-covid-19-banner' ),
					'inherit' => __( 'Default', 'corona-virus-covid-19-banner' ),
				);
				foreach ( $alignments as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'targets':
				$targets = array(
					'' => __( 'Default', 'corona-virus-covid-19-banner' ),
					'_blank' => __( 'New tab', 'corona-virus-covid-19-banner' ),
				);
				foreach ( $targets as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'header_sizes':
				$header_sizes = array(
					'h2' => __( 'Biggest (H2)', 'corona-virus-covid-19-banner' ),
					'h3' => __( 'Bigger (H3)', 'corona-virus-covid-19-banner' ),
					'h4' => __( 'Big (H4)', 'corona-virus-covid-19-banner' ),
					'h5' => __( 'Normal (H5)', 'corona-virus-covid-19-banner' ),
					'h6' => __( 'Small (H6)', 'corona-virus-covid-19-banner' ),
				);
				foreach ( $header_sizes as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'display_type':
				$display_types = array(
					'none' => __( 'None', 'corona-virus-covid-19-banner' ),
					'banner' => __( 'Banner', 'corona-virus-covid-19-banner' ),
					'overlay' => __( 'Overlay', 'corona-virus-covid-19-banner' ),
					'leaderboard' => __( 'Leaderboard', 'corona-virus-covid-19-banner' ),
				);
				foreach ( $display_types as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'frequency_times':
				$frequency_times = array(
					'10' => __( 'Every 10 minutes', 'corona-virus-covid-19-banner' ),
					'30' => __( 'Every 30 minutes', 'corona-virus-covid-19-banner' ),
					'60' => __( 'Every hour', 'corona-virus-covid-19-banner' ),
					'120' => __( 'Every 2 hours', 'corona-virus-covid-19-banner' ),
					'360' => __( 'Every 6 hours', 'corona-virus-covid-19-banner' ),
					'1440' => __( 'Every 24 hours', 'corona-virus-covid-19-banner' ),
				);
				foreach ( $frequency_times as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;
		}

		return $return_html;
	}

	public function check_install() {
		$today_date = new DateTime();

		if( wp_strip_all_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date' ) ) == "" ) {
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date', $today_date->format( 'Y-m-d h:i:sa' ) );
		}
	}

	public function time_elapsed_string($datetime, $full = false) {
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);
	
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;
	
		$string = array(
			'y' => __( 'year', 'corona-virus-covid-19-banner' ),
			'm' => __( 'month', 'corona-virus-covid-19-banner' ),
			'w' => __( 'week', 'corona-virus-covid-19-banner' ),
			'd' => __( 'day', 'corona-virus-covid-19-banner' ),
			'h' => __( 'hour', 'corona-virus-covid-19-banner' ),
			'i' => __( 'minute', 'corona-virus-covid-19-banner' ),
			's' => __( 'second', 'corona-virus-covid-19-banner' ),
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}
	
		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . __( ' ago', 'corona-virus-covid-19-banner' ) : __( 'just now', 'corona-virus-covid-19-banner' );
	}

	/**
	 * Main Orchestrated_Corona_Virus_Banner Instance
	 *
	 * Ensures only one instance of Orchestrated_Corona_Virus_Banner is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Orchestrated_Corona_Virus_Banner
	 * @return Main Orchestrated_Corona_Virus_Banner instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} 


	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Forbidden', 'corona-virus-covid-19-banner' ), intval( $this->parent->_version ) );
	}


	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Forbidden', 'corona-virus-covid-19-banner' ), intval( $this->parent->_version ) );
	}
}
