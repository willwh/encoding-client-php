# PHP Client for the Encoding.com API.

This library implements portions of the Encoding.com XML API and is intended to make it easier to develop features in your PHP powered application that interact with Encoding.com.

## Requirements

This library depends on PHP 5.3.0 (or higher) and libcurl compiled with OpenSSL support.

You can test that you're environment supports this by opening up a phpinfo(); page and verifying that under the curl section, there's a line that says something like:

    libcurl/7.19.5 OpenSSL/0.9.8g zlib/1.2.3.3 libidn/1.15


## Usage

Include this library in your code as follows:

    <?php
    require_once encoding/lib/encoding.php