<?php
/**
 * @file
 */

/**
 * Class Encoding_Response.
 */
class Encoding_Response {
  var $status_code;
  var $body;

  /**
   * Create an Encoding_Response object.
   *
   * @param int $status_code
   *   HTTP status code.
   * @param string $body
   *   XML response from encoding.com API.
   */
  public function __construct($status_code, $body) {
    $this->status_code = $status_code;
    $this->body = $body;
  }

  /**
   * Turn the response XML into something we can deal with in PHP.
   *
   * @return SimpleXMLElement
   */
  public function parseResponse() {
    $xml = new SimpleXMLElement($this->body);
    return $xml;
  }

  /**
   * Convert the XML response to an array.
   *
   * @return array
   *   The XML response transformed into an array.
   */
  public function toArray() {
    return json_decode(json_encode((array) $this->body),1);
  }

  /**
   * Verify a valid response was returned.
   *
   * This should be used to verify any response before using it.
   *
   * @return bool
   * @throws Encoding_Error
   * @throws Encoding_NotFoundError
   * @throws Encoding_UnauthorizedError
   * @throws Encoding_ConnectionError
   */
  public function assertValidResponse() {
    $response = $this->parseResponse();

    if ($this->status_code >= 200 && $this->status_code < 400) {
      if (count($response->errors)) {
        throw new Encoding_Error($response->errors->error);
      }
      else {
        $this->body = $response;
        return TRUE;
      }
    }

    switch ($this->status_code) {
      case 0:
        throw new Encoding_Error('An error occurred while connecting to Encoding.com.');
      case 400:
        $message = (is_null($error) ? '400 - Bad API Request' : $error->description);
        throw new Encoding_Error($message);
      case 401:
        throw new Encoding_UnauthorizedError('401 - Your API Key is not authorized to connect to Encoding.com.');
      case 403:
        throw new Encoding_UnauthorizedError('403 - Access Denied: Please use an API key to connect to Encoding.com.');
      case 404:
        $message = (is_null($error) ? '404 - Object not found' : $error->description);
        throw new Encoding_NotFoundError($message);
      case 422:
        throw new Encoding_Error($message);
      case 429:
        throw new Encoding_Error('429 - You have made too many API requests in the last hour. Future GET API requests will be ignored until the beginning of the next hour.');
      case 500:
        $message = (is_null($error) ? '500 - An error occurred while connecting to Encoding.com' :
          '500 - An error occurred while connecting to Encoding.com: ' . $error->description);
        throw new Encoding_Error($message);
      case 502:
      case 503:
      case 504:
        throw new Encoding_ConnectionError('An error occurred while connecting to Encoding.com.');

    }
  }
}
