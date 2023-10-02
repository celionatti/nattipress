<?php

declare(strict_types=1);

namespace NattiPress\NattiCore\Http;

class Response
{
    const CONTINUE = 101;
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const FOUND = 302;
    const NOT_MODIFIED = 304;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENT_REDIRECT = 308;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const REQUEST_TIMEOUT = 408;
    const INTERNAL_SERVER_ERROR = 500;
    const BAD_GATEWAY = 502;
    const GATEWAY_TIMEOUT = 504;
    const LARAGON_DATABASE = 601;
    const LARAGON_RESULT = 600;
}