<?php
namespace Wpcf7Ezdoc\renderer;

function render_api_key_setting_field() {
  $setting = \get_option('wpcf7_ezdoc_api_key');
	?>
	  <input type="text" name="wpcf7_ezdoc_api_key" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
    <br/>
    <p>
      EZDocとの連携に必要な認証キーです。EZDocの
      <a href="https://ez-doc.net/settings/integration" target="_blank">
        「外部システム連携」ページ
      </a>
      からご確認ください。
    </p>
  <?php
}