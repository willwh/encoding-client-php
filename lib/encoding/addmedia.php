<?php
/**
 * @file
 */

/**
 * Class Encoding_AddMediaAction
 */
class Encoding_AddMediaAction extends Encoding_Client {

  private $formats;
  private $source;
  private $destination;

  /**
   * Constructor.
   */
  public function __construct($user_id, $user_key) {
    parent::__construct($user_id, $user_key);
    $this->setAction(Encoding_Client::ACTION_ADD_MEDIA);
  }

  /**
   * Set the source file.
   *
   * @param string $source
   *   URL of the source file that encoding.com should retrieve and encode.
   */
  public function addSource($source) {
    // We keep this is a variable as well so we can use it when preparing the
    // request.
    $this->source = $source;
    $this->setFieldValue('source', $source);
  }

  /**
   * Set the base destination for encoded files.
   *
   * @param string $destination
   *   URL of the destination to use for an encoded file, filename + the
   *   file-prefix, and file-suffix from the format will be appended to this
   *   string to create the final destination.
   */
  public function addDestination($destination) {
    $this->destination = $destination;
  }

  /**
   * Set the formats to use for encoding.
   *
   * @param array $formats
   *   An array of format descriptors.
   */
  public  function addFormats($formats) {
    $this->formats = $formats;
  }

  /**
   * Preprocess a request and add the filled out format information.
   */
  private function prepareRequest() {
    foreach ($this->formats as $format) {
      $req = $this->request->addChild('format');
      $format = parse_ini_string($format);

      // Destination to save the new encoding.
      $source_info = pathinfo($this->source);
      $prefix = isset($format['file-prefix']) ? $format['file-prefix'] : '';
      unset($format['file-prefix']);
      $format['destination'] = $this->destination . '/' . $source_info['filename'] . '/' . $prefix . $source_info['filename'] .  $format['file-suffix'];
      unset($format['file-suffix']);

      foreach ($format as $key => $value) {
        $req->addChild($key, $value);
      }
    }
  }

  /**
   * Override the sendRequest method so we can add some additional processing.
   */
  public function sendRequest() {
    if (isset($this->destination) && isset($this->formats)) {
      $this->prepareRequest();
      return parent::sendRequest(Encoding_Client::POST);
    }
    else {
      return FALSE;
    }
  }
}
