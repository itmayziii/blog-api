<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed cookie domain
    |--------------------------------------------------------------------------
    |
    | Specifies those hosts to which the cookie will be sent. If not specified,
    | defaults to the host portion of the current document location
    | (but not including subdomains). Contrary to earlier specifications,
    | leading dots in domain names are ignored. If a domain is specified,
    | subdomains are always included.
    |
    */

    'domain' => env('APP_COOKIES_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Secure cookies only
    |--------------------------------------------------------------------------
    |
    | A secure cookie will only be sent to the server when a request is made
    | using SSL and the HTTPS protocol. However, confidential or sensitive
    | information should never be stored or transmitted in HTTP Cookies as the
    | entire mechanism is inherently insecure and this doesn't mean that any
    | information is encrypted, for example.
    |
    */

    'secure' => env('APP_COOKIES_SECURE'),

    /*
    |--------------------------------------------------------------------------
    | HttpOnly
    |--------------------------------------------------------------------------
    |
    | HTTP-only cookies aren't accessible via JavaScript through the
    | Document.cookie property, the XMLHttpRequest and Request APIs to mitigate
    | attacks against cross-site scripting (XSS).
    |
    */

    'http_only' => true
];
