<?php

namespace Util\Http\Exceptions;

    class BadRequestUri extends BadRequest {

        public function __construct($path, $message = null, $code = 0, Exception $previous = null) {
            parent::__construct(
                    $message ?: sprintf('Requested uri "%s" is not implemented, yet', $path), $code, $previous
            );
        }

    }
