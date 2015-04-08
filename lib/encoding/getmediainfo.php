<?php
/**
 * @file
 */

/**
 * Class Encoding_GetMediaInfoAction
 */
class Encoding_GetMediaInfoAction extends Encoding_Client {
  /**
   * Constructor.
   */
  public function __construct($user_id, $user_key) {
    parent::__construct($user_id, $user_key);
    $this->setAction(Encoding_Client::ACTION_GET_MEDIA_INFO);
  }

  /**
   * Set the MediaID.
   */
  public function setMediaID($media_id) {
    $this->setFieldValue('mediaid', $media_id);
  }

  /**
   * Implements parent::sendRequest().
   */
  public function sendRequest($method = Encoding_Client::POST) {
    return parent::sendRequest($method);
  }
}
