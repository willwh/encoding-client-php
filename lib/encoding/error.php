<?php
/**
 * @file
 */

/**
 * Class Encoding_Error.
 */
class Encoding_Error extends Exception {}

/**
 * Class Encoding_ConnectionError.
 */
class Encoding_ConnectionError extends Encoding_Error {}

/**
 * Class Encoding_UnauthorizedError.
 */
class Encoding_UnauthorizedError extends Encoding_Error {}

/**
 * Class Encoding_NotFoundError.
 */
class Encoding_NotFoundError extends Encoding_Error {}
