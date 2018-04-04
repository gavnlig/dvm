<?php

namespace Util\Db;

class PreparedStatementException extends Exception {

    public function __construct($code, $text, $query, Exception $previous = null) {
        parent::__construct(
                $text, $code, $previous
        );
    }

}
