<?php


namespace Kernel\Core\Cache;


use Kernel\Core\Conf\Config;

class Redis
{
        private $_socket   = null;
        private $_host     = null;
        private $_port     = null;
        private $_password = null;

        public function __construct(Config $config)
        {
                $config = $config->get('redis');
                $this->_host     = $config['host'];
                $this->_port     = $config['port'];
                $this->_password = $config['password'] ?? null;
                $this->_socket =  new \Redis();
                $this->_socket->connect($this->_host, $this->_port);
                if($this->_password != '') {
                        $this->_socket->auth($this->_password);
                }
        }

       public function get() : \Redis
       {
               return $this->_socket;
       }
}
