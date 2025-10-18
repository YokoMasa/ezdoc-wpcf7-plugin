<?php
namespace Wpcf7Ezdoc;

use Wpcf7Ezdoc\EZDocClient;
use Wpcf7Ezdoc\EZDocException;
use function Wpcf7Ezdoc\to_attr_id;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function on_wpcf7_submit($form, $submission_result) {
  $ezdoc_api_key = \get_option(\WPCF7_EZDOC_OPTION_API_KEY);
  $ezdoc_document_id = \array_key_exists(\WPCF7_EZDOC_FORM_PROPERTY_DOCUMENT_ID, $form->get_properties())
    ? $form->get_properties()[\WPCF7_EZDOC_FORM_PROPERTY_DOCUMENT_ID]
    : '';
  if (\is_null($ezdoc_document_id) or \is_null($ezdoc_api_key)) {
    return;
  }

  $wpcf7_submission = \WPCF7_Submission::get_instance();
  if (\is_null($wpcf7_submission)) {
    return;
  }

  $raw_posted_data = $wpcf7_submission->get_posted_data();
  $organized_posted_data = array();
  foreach($raw_posted_data as $key => $value) {
    $attr_id = to_attr_id($key);
    if (\is_string($value) and strlen($value) != 0) {
      $organized_posted_data[$attr_id] = $value;
    } else if (\is_array($value) and \array_key_exists(0, $value)) {
      $organized_posted_data[$attr_id] = $value[0];
    }
  }

  $ezdoc_client = new EZDocClient($ezdoc_api_key);
  try {
    $ezdoc_client->create_form_submission($ezdoc_document_id, $organized_posted_data);
  } catch (\Exception $e) {
    \error_log($e->getMessage());
  }
}
\add_action('wpcf7_submit', 'Wpcf7Ezdoc\on_wpcf7_submit', 10, 2);