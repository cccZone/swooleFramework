<?php


namespace Kernel\Swoole\Event\Http;


use Kernel\Core;
use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;
use Library\Crawler\Crawler;
use Core\Cache\Type\Hash;

class Task implements Event
{
        use EventTrait;
        /* @var  \swoole_http_server $server*/
        protected $server;
        protected $db;
        protected $redis;
        protected $config;
        const KEY = 'crawler:list:';
        const BASE_NUM = 1000;

        public function __construct(\swoole_http_server $server)
        {
                $this->server = $server;
                $core = Core::getInstant();
                $this->db = $core->get(Core\DB\DB::class);
                $this->redis = $core->get(Core\Cache\Redis::class);
                $this->config = $core->get(Core\Conf\Config::class);
        }

        public function doEvent(\swoole_server $server, $taskId, $fromId, $data)
        {
                //$data = json_encode($data);
                if(is_array($data) and isset($data['action'])) {
                        switch ($data['action']) {
                                case 'crawler':
                                        echo "crawler {$taskId}".PHP_EOL;
                                        $this->_crawler($data, $server, $taskId);
                                        break;
                                case 'reload':
                                        $this->_reload($data, $server, $taskId);
                                        break;
                                case 'stop':
                                        $this->_stop($data, $server);
                                        break;
                                default:
                                        $server->tick(1000, function () {
                                                echo "I'm ok";
                                        });
                                        break;
                        }
                        unset($task);
                }
                $this->doClosure();
        }

        private function _crawler(array $data,  \swoole_server $server, $taskId)
        {
                $task = Crawler::getCrawler($data);
                $task->run($task->getUrl());
                $hash = $this->getHash(self::KEY.$data['flag'], $this->redis);
                $crawlerTickId = $server->tick(self::BASE_NUM, function ($id) use(&$task) {
                        $url = $task->getUrl();
                        echo $url."\r\n";
                        if(!empty($url)) {
                                $task->run($url);
                        }else{
                                $task->clear();
                                $task->reset();
                        }
                });
                $reloadTickId = $server->tick($data['interval'] * self::BASE_NUM, function ($id) use(&$task, $server, $data) {
                        $task->clear();
                });
                $hash->setField('taskId', $taskId);
                $hash->setField('time', time());
                $hash->setField('reloadTickId', $reloadTickId);
                $hash->setField('crawlerTickId', $crawlerTickId);
        }

        private function _reload(array $data, \swoole_server $server, $taskId)
        {
               $this->_stop($data, $server, false, $taskId);
        }

        private function _stop(array $data, \swoole_server $server, bool $finish = true, $taskId = '')
        {
                $hash = $this->getHash(self::KEY.$data['flag'], $this->redis);
                if(!$hash->hasKey()) {
                      $server->finish('');
                }
                if(isset($data['taskId'])) {
                        $oldTaskId = $data['taskId'];
                }else {
                        $oldTaskId = $hash->getField('taskId');
                }
                $oldTaskId = intval($oldTaskId);
                $crawlerTickId = intval($hash->getField('crawlerTickId'));
                $reloadTickId = intval($hash->getField('reloadTickId'));
                if($finish) {
                        $hash->delKey();
                        $server->sendMessage('kill', $oldTaskId);
                        if(!empty($crawlerTickId)) {
                                \swoole_timer_clear($crawlerTickId);
                        }
                        if(!empty($reloadTickId)) {
                                \swoole_timer_clear($crawlerTickId);
                        }
                        $server->finish('stop');
                }
        }

        private function getHash(string $key, Core\Cache\Redis $redis) : Core\Cache\Type\Hash
        {
                $class = new Hash($redis);
                $class->setKey($key);
                return $class;
        }
}