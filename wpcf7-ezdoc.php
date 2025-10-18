<?php
/**
 * Plugin Name: Contact Form 7 - EZDoc Integration Plugin
 * Plugin URI: https://ez-doc.net
 * Description: Contact Form 7 - EZDoc Integration
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (!class_exists('WPCF7_Submission')) {
  return;
}

const WPCF7_EZDOC_OPTION_API_KEY = 'wpcf7_ezdoc_api_key';
const WPCF7_EZDOC_FORM_PROPERTY_DOCUMENT_ID = 'ezdoc_document_id';

require_once __DIR__ . '/includes/ezdoc-client.php';
require_once __DIR__ . '/includes/utils.php';

/**
 * "wpcf7_pre_construct_contact_form_properties" filter
 */
function wpcf7_ezdoc_on_wpcf7_pre_construct_contact_form_properties($properties) {
  $properties[WPCF7_EZDOC_FORM_PROPERTY_DOCUMENT_ID] = '';
  return $properties;
}
add_filter('wpcf7_pre_construct_contact_form_properties', 'wpcf7_ezdoc_on_wpcf7_pre_construct_contact_form_properties', 10, 1);

if ( is_admin() ) {
  require_once __DIR__ . '/admin/wpcf7-ezdoc.php';
} else {
  require_once __DIR__ . '/public/wpcf7-ezdoc.php';
}