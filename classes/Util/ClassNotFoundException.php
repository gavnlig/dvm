<?php

namespace Util;

    class ClassNotFoundException extends NotImplementedException {

        private $classname = null;

        public function __construct($classname, $message = null, $code = 0, Exception $previous = null) {
            $this->classname = $classname;
            parent::__construct(
                    $message ?: sprintf('Requested class "%s" is not implemented, yet', $classname), $code, $previous
            );
        }

    }
