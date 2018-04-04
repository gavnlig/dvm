<?php

namespace Util\Db;

class ExecutionException extends Exception {

    public function __construct($code, $text, $query, Exception $previous = null) {
        parent::__construct(
                $text, $code, $previous
        );
    }

}
