<?php
namespace EZDocWpcf7\renderer;

use EZDocWpcf7\EZDocClient;
use EZDocWpcf7\EZDocException;
use function EZDocWpcf7\to_form_tag_name;

function render_wpcf7_editor_panel($form) {
  $ezdoc_api_key = \get_option(\EZDOC_WPCF7_OPTION_API_KEY);
  ?>
  <h2>
    <!--EZDocの設定-->
    <?php _e('EZ Doc Settings', 'ez-doc-integration-for-contact-form-7') ?>
  </h2>
  <p style="margin-bottom: 16px;">
    <!--ここでは、フォームへの回答があった時にフォーム回答者に送付するEZDoc資料を設定することができます。-->
    <?php _e('In this page, you can choose which EZ Doc document to be automatically sent to the form responder.', 'ez-doc-integration-for-contact-form-7') ?>
  </p>
  <?php

  if (!is_string($ezdoc_api_key) or strlen($ezdoc_api_key) == 0) {
    _render_no_api_key_content();
    return;
  }

  $ezdoc_client = new EZDocClient($ezdoc_api_key);
  try {
    $ezdoc_documents = $ezdoc_client->list_documents(null);
    $ezdoc_viewer_attributes = $ezdoc_client->list_viewer_attributes();
    _render_content($form, $ezdoc_documents, $ezdoc_viewer_attributes);
  } catch (EZDocException $e) {
    _render_api_error_content($e->getMessage());
  }
}

function _render_content($form, $ezdoc_documents, $ezdoc_viewer_attributes) {
  $ezdoc_document_id = \array_key_exists(\EZDOC_WPCF7_FORM_PROPERTY_DOCUMENT_ID, $form->get_properties())
    ? $form->get_properties()[\EZDOC_WPCF7_FORM_PROPERTY_DOCUMENT_ID]
    : '';
  $form_tags = $form->scan_form_tags();
  ?>

  <div>
    <label for="ezdoc-document-id">
      <!--送付する資料-->
      <?php _e('EZ Doc document to be sent', 'ez-doc-integration-for-contact-form-7') ?>
    </label>
    <br/>
    <select id="ezdoc-document-id" name="ezdoc-document-id">
      <option
        value=""
        <?php $ezdoc_document_id == '' ? 'selected' : '' ?>>
        <!--未選択-->
        <?php _e('None selected', 'ez-doc-integration-for-contact-form-7') ?>
      </option>
      <?php foreach($ezdoc_documents as $doc): ?>
        <option
          value="<?php echo esc_attr($doc->get_id()) ?>"
          <?php echo $ezdoc_document_id == $doc->get_id() ? 'selected' : '' ?>>
          <?php echo esc_html($doc->get_name()) ?>
        </option>
      <?php endforeach ?>
    </select>
  </div>

  <p style="margin-top: 16px; margin-bottom: 0;">
    <!--
      下記のフォームタグを設置することでその情報がEZDocに連携されます。
      必須に「●」がついているフォームタグは必ずフォームに含める必要があり、含めなかった場合EZDocに連携されなくなります。
    -->
    <?php _e('By including form-tags below into the form template, the data will be sent to EZ Doc. You need to include all the required form-tags into the form template. Otherwise, form data will not be sent to EZ Doc.', 'ez-doc-integration-for-contact-form-7') ?>
  </p>
  <table class="ezdoc-wpcf7-form-tag-table">
    <tr>      
      <th>
        <!--必須-->
        <?php _e('Required', 'ez-doc-integration-for-contact-form-7') ?>
      </th>
      <th>
        <!--タグ設置状況-->
        <?php _e('Is tag included in the form template?', 'ez-doc-integration-for-contact-form-7') ?>
      </th>
      <th>
        <!--フォームタグ例-->
        <?php _e('Form-tag example', 'ez-doc-integration-for-contact-form-7') ?>
      </th>
    </tr>
    <?php foreach($ezdoc_viewer_attributes as $attr): ?>
      <tr>
        <?php _render_viewer_attribute_row($attr, $form_tags) ?>
      </tr>
    <?php endforeach ?>
  </table>
<?php
}

function _render_viewer_attribute_row($attr, $form_tags) {
  $attr_id = $attr->get_id();
  $has_form_tag = !\is_null(
    \array_find(
      $form_tags,
      function($tag) use($attr_id) {
        return $tag->name === to_form_tag_name($attr_id);
      }
    )
  );
  $has_form_tag_text = $has_form_tag
    ? '<span style="color: green; font-weight: 700;">' . __('Included', 'ez-doc-integration-for-contact-form-7') . '</span>'
    : '<span style="color: red; font-weight: 700;">' . __('Not Included', 'ez-doc-integration-for-contact-form-7') . '</span>';
  switch($attr_id) {
    case 'firstName':?>
      <td>●</td>
      <td><?php echo $has_form_tag_text ?></td>
      <td>
        &lt;label&gt;<!--名--><?php _e('First Name', 'ez-doc-integration-for-contact-form-7') ?>
        <br/>&nbsp;&nbsp;[text* firstName autocomplete:given-name]&lt;/label&gt;
      </td>
      <?php
      break;
    case 'firstNameKana':?>
      <td></td>
      <td><?php echo $has_form_tag_text ?></td>
      <td>
        &lt;label&gt;<!--名（カナ）--><?php _e('First Name Kana', 'ez-doc-integration-for-contact-form-7') ?>
        <br/>&nbsp;&nbsp;[text firstNameKana]&lt;/label&gt;
      </td>
      <?php
      break;
    case 'lastName':?>
      <td>●</td>
      <td><?php echo $has_form_tag_text ?></td>
      <td>
        &lt;label&gt;<!--姓--><?php _e('Last Name', 'ez-doc-integration-for-contact-form-7') ?>
        <br/>&nbsp;&nbsp;[text* lastName autocomplete:family-name]&lt;/label&gt;
      </td>
      <?php
      break;
    case 'lastNameKana':?>
      <td></td>
      <td><?php echo $has_form_tag_text ?></td>
      <td>
        &lt;label&gt;<!--姓（カナ）--><?php _e('Last Name Kana', 'ez-doc-integration-for-contact-form-7') ?>
        <br/>&nbsp;&nbsp;[text lastNameKana]&lt;/label&gt;
      </td>
      <?php
      break;
    case 'email':?>
      <td>●</td>
      <td><?php echo $has_form_tag_text ?></td>
      <td>
        &lt;label&gt;<!--メールアドレス--><?php _e('Email', 'ez-doc-integration-for-contact-form-7') ?>
        <br/>&nbsp;&nbsp;[email* email autocomplete:email]&lt;/label&gt;
      </td>
      <?php
      break;
    case 'phoneNumber':?>
      <td></td>
      <td><?php echo $has_form_tag_text ?></td>
      <td>
        &lt;label&gt;<!--電話番号--><?php _e('Phone Number', 'ez-doc-integration-for-contact-form-7') ?>
        <br/>&nbsp;&nbsp;[tel phoneNumber autocomplete:tel]&lt;/label&gt;
      </td>
      <?php
      break;
    case 'companyName':?>
      <td></td>
      <td><?php echo $has_form_tag_text ?></td>
      <td>
        &lt;label&gt;<!--貴社名--><?php _e('Organization', 'ez-doc-integration-for-contact-form-7') ?>
        <br/>&nbsp;&nbsp;[text companyName autocomplete:organization]&lt;/label&gt;
      </td>
      <?php
      break;
    case 'departmentName':?>
      <td></td>
      <td><?php echo $has_form_tag_text ?></td>
      <td>
        &lt;label&gt;<!--部署名--><?php _e('Organization Title', 'ez-doc-integration-for-contact-form-7') ?>
        <br/>&nbsp;&nbsp;[text departmentName autocomplete:organization-title]&lt;/label&gt;
      </td>
      <?php
      break;
    default:
      _render_viewer_custom_attribute_row($attr, $has_form_tag_text);
      break;
  }
}

function _render_viewer_custom_attribute_row($attr, $has_form_tag_text) {?>
  <td></td>
  <td><?php echo $has_form_tag_text ?></td>
  <td>
    &lt;label&gt;<?php echo esc_html($attr->get_name()) ?>
  <?php
  $form_tag_id = to_form_tag_name($attr->get_id());
  $form_tag = '[';
  if ($attr->is_text()) {
    $form_tag .= 'text ' . $form_tag_id;
  } else if ($attr->is_long_text()) {
    $form_tag .= 'textarea ' . $form_tag_id;
  } else if ($attr->is_date()) {
    $form_tag .= 'date ' . $form_tag_id;
  } else if ($attr->is_select()) {
    $form_tag .= 'select ' . $form_tag_id;
    $form_tag .= ' ' . \implode(
      " ",
      \array_map(
        function($el) {
          return '"' . $el->get_value() . '"';
        },
        $attr->get_options()
      )
    );
  }
  $form_tag .= ']';
  echo '<br/>&nbsp;&nbsp;';
  echo $form_tag;?>&lt;/label&gt;
  <?php
}

function _render_no_api_key_content() {?>
  <div style="background-color: #fff085; padding: 8px; border-radius: 4px;">
    <!--EZ DocのAPIキーが設定されていません。-->
    <?php _e('EZ Doc API key is not configured.', 'ez-doc-integration-for-contact-form-7') ?>
    <a href="<?php echo esc_url(admin_url('options-general.php')) ?>">
      <!--「設定 -> 一般 -> EZDoc APIキー」から設定を行ってください。-->
      <?php _e('Please configure API key from (Settings -> General -> EZ Doc API key)', 'ez-doc-integration-for-contact-form-7') ?>
    </a>
  </div>
<?php
}

function _render_api_error_content(string $message) {?>
  <div style="background-color: #ffc9c9; padding: 8px; border-radius: 4px; color: #c10007;">
    <?php echo esc_html($message) ?>
  </div>
<?php
}