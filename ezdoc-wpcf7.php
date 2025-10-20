<?php
/**
 * Plugin Name: EZ Doc - Contact Form 7 Integration Plugin
 * Plugin URI: https://lp.ez-doc.net
 * Description: EZ Doc - Contact Form 7 Integration Plugin
 * Version: 1.0.0
 * Requires at least: 6.7
 * Requires PHP: 8.1
 * Author: Hikari Laboratory
 * Author URI: https://lp.ez-doc.net/owner_info
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: contact-form-7
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (!class_exists('WPCF7_Submission')) {
  return;
}

const EZDOC_WPCF7_OPTION_API_KEY = 'ezdoc_wpcf7_api_key';
const EZDOC_WPCF7_FORM_PROPERTY_DOCUMENT_ID = 'ezdoc_document_id';

require_once __DIR__ . '/includes/ezdoc-client.php';
require_once __DIR__ . '/includes/utils.php';

/**
 * "wpcf7_pre_construct_contact_form_properties" filter
 */
function ezdoc_wpcf7_on_wpcf7_pre_construct_contact_form_properties($properties) {
  $properties[EZDOC_WPCF7_FORM_PROPERTY_DOCUMENT_ID] = '';
  return $properties;
}
add_filter('wpcf7_pre_construct_contact_form_properties', 'ezdoc_wpcf7_on_wpcf7_pre_construct_contact_form_properties', 10, 1);

if ( is_admin() ) {
  require_once __DIR__ . '/admin/ezdoc-wpcf7.php';
} else {
  require_once __DIR__ . '/public/ezdoc-wpcf7.php';
}