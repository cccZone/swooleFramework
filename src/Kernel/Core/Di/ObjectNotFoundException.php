<?php


namespace Kernel\Core\Di;


use Psr\Container\NotFoundExceptionInterface;

class ObjectNotFoundException extends \Exception implements NotFoundExceptionInterface
{
        public function __construct($code, $message)
        {
                parent::__construct($message, $code);
        }
}