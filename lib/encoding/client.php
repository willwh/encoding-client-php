<?php
/**
 * @file
 */

require 'vendor/autoload.php';

/**
 * Class Encoding_Client.
 */

use GuzzleHttp\Client;


class Encoding_Client {

  // API User ID and Key, can be overridden in constructor.
  public static $user_id;
  public static $user_key;

  private $_user_id;
  private $_user_key;

  const API_CLIENT_VERSION = '2.1.2';
  const DEFAULT_ENCODING = 'UTF-8';

  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const DELETE = 'DELETE';
  const HEAD = 'HEAD';

  const ACTION_ADD_MEDIA = 'AddMedia';
  const ACTION_GET_MEDIA_INFO = 'GetMediaInfo';
  const ACTION_GET_STATUS = 'GetStatus';

  private $_acceptLanguage = 'en-US';

  // API endpoint including trailing /..
  // @todo: this should be a {variable}.
  private $api_url = ENCODING_API_URL;

  var $request;
  private $action;
  private $fields = array();
  public $debug = FALSE;

  /**
   * Instantiate a new Encoding_Client.
   *
   * @param string $user_id
   *   Your encoding.com API user ID.
   * @param string $user_key
   *   Your encoding.com API user key.
   */
  public function __construct($user_id, $user_key) {
    $this->request = new SimpleXMLElement('<?xml version="1.0"?><query></query>');
    $this->_user_id = $user_id;
    $this->_user_key = $user_key;
  }

  /**
   * Get current user-agent string.
   *
   * @return string
   *   Current user-agent.
   */
  private static function _userAgent() {
    return "User-Agent: Encoding/" . self::API_CLIENT_VERSION . '; PHP ' . phpversion() . ' [' . php_uname('s') . ']';
  }

  /**
   * Current API User ID.
   *
   * @return string
   *   API User ID,
   */
  public function getUserId() {
    return (empty($this->_user_id) ? Encoding_Client::$user_id : $this->_user_id);
  }

  /**
   * Current API Key.
   *
   * @return string
   *   API Key.
   */
  public function getUserKey() {
    return (empty($this->_user_key) ? Encoding_Client::$user_key : $this->_user_key);
  }

  /**
   * Set the action for this API request.
   *
   * @param string $action
   *   The API action to perform.
   */
  public function setAction($action) {
    $this->request->addChild('action', $action);
  }

  /**
   * Add an arbitrary field + value pair to the request.
   *
   * @param string $field
   *   Name of the field to add.
   * @param string $value
   *   Value to add.
   */
  public function setFieldValue($field, $value) {
    // We assign chid values directly rather than using the addChild() method
    // here because addChild() doesn't propertly handle the & character that is
    // in some URLs.
    $this->request->{$field} = $value;
  }

  /**
   * Send a request to the encoding.com API.
   *
   * @param string $method
   *   HTTP method to use when making this request. Currently only GET or POST
   *   are supported.
   *
   * @return bool|int
   *   Response from the API or FALSE if unable to make a request.
   */
  public function sendRequest($method) {
    // Add the API ID and Key to the request.
    $this->request->addChild('userid', $this->getUserId());
    $this->request->addChild('userkey', $this->getUserKey());

    // In debug mode just print the request out to the screen. You can paste
    // the request here to test it out. https://www.encoding.com/sendXml
    if ($this->debug) {
      print_r($this->request);
      return TRUE;
    }

    if ($this->request) {
      // Let's get rid of cURL and use guzzlehttp/guzzle

      $client = new GuzzleHttp\Client([
        'allow_redirects' => ['max' => 1],
        'verify' => ['verify' => false],
        'connect_timeout' => '10',
        'timeout' => '45',
        'headers' => [
          'Accept' => 'application/xml',
          //'Accept-Language' => $this->_acceptLanguage
        ]
      ]);

      try {

        if ($method == Encoding_Client::POST) {
          $xml = $this->request->asXML();

          $response = $client->request('POST', $this->api_url, [
            'body' => urlencode(($xml))
          ]);
        }
        elseif ($method == Encoding_Client::GET) {
          $response = $client->request($method, $this->api_url);
        }
        var_dump($response->getBody()->getContents());
        return new Encoding_Response($response->getStatusCode(), $response->getBody());
      }
      catch (GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        $responseBodyAsString = $response->getBody()->getContents();
        print_r('throw a fucking error: ' . $responseBodyAsString);
      }
    }
    else {
      return FALSE;
    }
  }

  private function _getHeaders($header_text) {
    $headers = explode("\r\n", $header_text);
    $return_headers = array();
    foreach ($headers as &$header) {
      preg_match('/([^:]+): (.*)/', $header, $matches);
      if (count($matches) > 2) {
        $return_headers[$matches[1]] = $matches[2];
      }
    }
    return $return_headers;
  }

  private function _raiseCurlError($error_number, $message) {
    switch ($error_number) {
      case CURLE_COULDNT_CONNECT:
      case CURLE_COULDNT_RESOLVE_HOST:
      case CURLE_OPERATION_TIMEOUTED:
        throw new Encoding_ConnectionError('Failed to connect to Encoding.com.');
      case CURLE_SSL_CACERT:
      case CURLE_SSL_PEER_CERTIFICATE:
        throw new Encoding_ConnectionError("Could not verify Encoding.com's SSL certificate.");
      default:
        throw new Encoding_ConnectionError('An unexpected error occurred connecting with Encoding.com.');
    }
  }
}
