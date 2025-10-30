<?php
namespace EZDocWpcf7\renderer;

function render_api_key_setting_field() {
  $setting = \get_option('ezdoc_wpcf7_api_key');
	?>
	  <input type="text" name="ezdoc_wpcf7_api_key" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
    <br/>
    <p>
      <?php _e('API key necessary for EZ Doc integration.', 'ez-doc-integration-for-contact-form-7') ?>
      <a href="https://ez-doc.net/settings/integration" target="_blank">
        <?php _e('Please get the key in "Integrate With External System" page.', 'ez-doc-integration-for-contact-form-7') ?>
      </a>
    </p>
  <?php
}