<?php


namespace Kernel;


interface Server
{
        public function start(\Closure $callback) : Server;
        public function shutdown(\Closure $callback) : Server;
}