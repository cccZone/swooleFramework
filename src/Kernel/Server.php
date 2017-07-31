<?php


namespace Kernel;


interface Server
{
        public function start(\Closure $callback = null) : Server;
        public function shutdown(\Closure $callback = null) : Server;
}