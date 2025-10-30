<?php
namespace EZDocWpcf7;

require_once __DIR__ . '/ezdoc-exception.php';

use EZDocWpcf7\EZDocException;

class EZDocClient {

  private string $api_key;

  function __construct(string $api_key) {
    $this->api_key = $api_key;
  }

  /**
   * @param string|null $q 検索文字列
   * 
   * @return array DocumentDTOのarray
   */
  public function list_documents(?string $q): array {
    $endpoint =  \is_null($q)
      ? 'https://ez-doc.net/api/ext/document'
      : "https://ez-doc.net/api/ext/document?q=$q";
    
    $api_result = \wp_remote_get(
      $endpoint,
      array(
        'headers' => array(
          'X-Api-Key' => $this->api_key
        )
      )
    );

    $body_obj = $this->get_body_object($api_result);

    $document_list = array();
    for ($i = 0; $i < \count($body_obj); $i++) {
      $el = $body_obj[$i];
      $document_list[$i] = new DocumentDTO($el->id, $el->fileName);
    }
    return $document_list;
  }

  /**
   * フォームで利用できるviewer attributeの一覧を返す。
   */
  public function list_viewer_attributes(): array {
    $endpoint = 'https://ez-doc.net/api/ext/form-viewer-attribute';

    $api_result = \wp_remote_get(
      $endpoint,
      array(
        'headers' => array(
          'X-Api-Key' => $this->api_key
        )
      )
    );

    $body_obj = $this->get_body_object($api_result);
    $attr_list = array();
    for ($i = 0; $i < \count($body_obj); $i++) {
      $el = $body_obj[$i];
      $attr_list[$i] = ViewerAttributeDTO::parse_from($el);
    }
    return $attr_list;
  }

  /**
   * フォームの内容を送信する。
   * 
   * @param string $document_id EZDocのdocumentId
   * @param array $form_values フォームの内容
   */
  public function create_form_submission(string $document_id, array $form_values) {
    $endpoint = 'https://ez-doc.net/api/ext/form-submission';

    $api_result = \wp_remote_post(
      $endpoint,
      array(
        'headers' => array(
          'X-Api-Key' => $this->api_key,
          'Content-Type' => 'application/json'
        ),
        'body' => \json_encode(array(
          'documentId' => $document_id,
          'formValues' => $form_values
        ))
      )
    );

    $this->get_body_object($api_result);
  }

  private function get_body_object(array | \WP_Error $api_result) {
    if (\is_wp_error($api_result)) {
      \error_log(\print_r($api_result, true));
      throw new EZDocException(\__('Could not reach EZ Doc server. Please check your network settings.', 'ez-doc-integration-for-contact-form-7'));
    }

    $status_code = $api_result['response']['code'];
    if (!is_int($status_code)) {
      throw new EZDocException(\__('Could not reach EZ Doc server. Please check your network settings.', 'ez-doc-integration-for-contact-form-7'));
    }
    if (200 <= $status_code and $status_code < 300) {
      return \json_decode($api_result['body']);
    }

    switch ($status_code) {
      case 400:
        $error_body = \json_decode($api_result['body']);
        if (\is_null($error_body)) {
          throw new EZDocException(\__('Unexpected error occurred. Please retry after a little while.', 'ez-doc-integration-for-contact-form-7'));
        } else {
          $error_message = \property_exists($error_body, 'message') ? $error_body->message : '';
          throw new EZDocException(\__('Invalid input.', 'ez-doc-integration-for-contact-form-7'));
        }
      case 401:
        throw new EZDocException(\__('Failed to authenticate to EZ Doc. Please make sure that correct API key is set.', 'ez-doc-integration-for-contact-form-7'));
      case 403:
        throw new EZDocException(\__('You don\'t have necessary permission to perform this action. Please make sure that correct API key is set.', 'ez-doc-integration-for-contact-form-7'));
      default:
        /* translators: %s: status code */
        throw new EZDocException(\__('Unexpected error occurred (status: %s). Please retry after a little while.', 'ez-doc-integration-for-contact-form-7'));
    }
  }

}

/**
 * Represents EZ Doc Document.
 */
class DocumentDTO {

  private string $id;
  private string $name;

  public function __construct(string $id, string $name) {
    $this->id = $id;
    $this->name = $name;
  }

  public function get_id(): string {
    return $this->id;
  }

  public function get_name(): string {
    return $this->name;
  }

}

/**
 * Represents EZ Doc Viewer Attribute Data Type
 */
enum DataType: string {
  case TEXT = 'TEXT';
  case LONG_TEXT = 'LONG_TEXT';
  case DATE = 'DATE';
  case SELECT = 'SELECT';
}

/**
 * Represents EZ Doc Viewer Attribute
 */
class ViewerAttributeDTO {

  private string $id;
  private string $name;
  private DataType $data_type;
  private array $options;

  public function __construct(string $id, string $name, DataType $data_type) {
    $this->id = $id;
    $this->name = $name;
    $this->data_type = $data_type;
    $this->options = array();
  }

  public static function parse_from(object $obj): ViewerAttributeDTO {
    $id = $obj->displayId;
    $name = $obj->displayName;
    $data_type = DataType::from($obj->dataType);
    $dto = new ViewerAttributeDTO($id, $name, $data_type);

    if ($data_type === DataType::SELECT) {
      foreach($obj->data->options as $option_obj) {
        $dto->add_option(
          new ViewerSelectAttributeOptionDTO($option_obj->displayName, $option_obj->value)
        );
      }
    }
    return $dto;
  }

  public function add_option(ViewerSelectAttributeOptionDTO $option) {
    \array_push($this->options, $option);
  }

  public function get_options(): array {
    return $this->options;
  }

  public function get_id(): string {
    return $this->id;
  }

  public function get_name(): string {
    return $this->name;
  }

  public function is_text(): bool {
    return $this->data_type === DataType::TEXT;
  }

  public function is_long_text(): bool {
    return $this->data_type === DataType::LONG_TEXT;
  }

  public function is_date(): bool {
    return $this->data_type === DataType::DATE;
  }

  public function is_select(): bool {
    return $this->data_type === DataType::SELECT;
  }

}

/**
 * Represents EZ Doc Viewer Select Attribute Option
 */
class ViewerSelectAttributeOptionDTO {

  private string $name;
  private string $value;

  public function __construct(string $name, string $value) {
    $this->name = $name;
    $this->value = $value;
  }

  public function get_name(): string {
    return $this->name;
  }

  public function get_value(): string {
    return $this->value;
  }

}