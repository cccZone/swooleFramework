<?php


namespace Kernel\Swoole\Event;


trait EventTrait
{
        protected $callback;
        protected $params = [];
        public function setEventCall(\Closure $closure = null, array $params = [])
        {
                $this->callback = $closure;
                $this->params = $params;
                return $this;
        }

        public function doClosure()
        {
                call_user_func_array($this->callback, $this->params);
                return $this;
        }
}