<?php

namespace Util;

    class InvalidConfigurationException extends NotImplementedException {

        public function __construct($classname, $configuration, $message = null, $code = 0, Exception $previous = null) {
            parent::__construct(
                    $message ?: sprintf('Configuration "%s" not set for class "%s"', $configuration, $classname), $code, $previous
            );
        }

    }
