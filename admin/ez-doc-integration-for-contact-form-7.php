<?php
namespace EZDocWpcf7;
if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once __DIR__ . '/renderer/api-key-setting-field-renderer.php';
require_once __DIR__ . '/renderer/ezdoc-wpcf7-editor-panel-renderer.php';

/**
 * "admin_init" action
 */
function on_admin_init() {
  \register_setting(
    'general',
    EZDOC_WPCF7_OPTION_API_KEY,
    array(
      'type' => 'string',
      'label' => \__('EZ Doc API key', 'ez-doc-integration-for-contact-form-7'),
      'description' => \__('API key necessary for integrating with EZ Doc.', 'ez-doc-integration-for-contact-form-7'),
      'show_in_rest' => false,
      'sanitize_callback' => 'sanitize_text_field'
    )
  );

  \add_settings_field(
    EZDOC_WPCF7_OPTION_API_KEY,
    \__('EZ Doc API key', 'ez-doc-integration-for-contact-form-7'),
    'EZDocWpcf7\renderer\render_api_key_setting_field',
    'general'
  );
}
\add_action('admin_init', 'EZDocWpcf7\on_admin_init');

/**
 * "admin_enqueue_scripts" action
 */
function on_admin_enqueue_scripts() {
  \wp_enqueue_style('ezdoc_wpcf7_admin_style', \plugins_url('css/styles.css', __FILE__));
}
\add_action('admin_enqueue_scripts', 'EZDocWpcf7\on_admin_enqueue_scripts');

/**
 * "wpcf7_editor_panels" filter
 */
function on_wpcf7_editor_panels($panels) {
  $panels['ezdoc'] = array(
    'title' => \__('EZ Doc settings', 'ez-doc-integration-for-contact-form-7'),
		'callback' => 'EZDocWpcf7\renderer\render_wpcf7_editor_panel',
  );
  return $panels;
}
\add_filter('wpcf7_editor_panels', 'EZDocWpcf7\on_wpcf7_editor_panels');

/**
 * "wpcf7_save_contact_form" action
 */
function on_wpcf7_save_contact_form($form, $input, $context) {
  $ezdoc_document_id = \wpcf7_superglobal_post( 'ezdoc-document-id', '' );
  if ($context == 'save') {
    $form->set_properties(
      array(
        EZDOC_WPCF7_FORM_PROPERTY_DOCUMENT_ID => $ezdoc_document_id
      )
    );
  }
}
\add_action('wpcf7_save_contact_form', 'EZDocWpcf7\on_wpcf7_save_contact_form', 10, 3);
