<?php


namespace Kernel\Core\DB;


use Kernel\Core\Conf\Config;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use MongoDB\Driver\BulkWrite;

class Mongodb extends Client implements DB
{
        private $_database  = null;
        private $_table     = null;

        private $_state     = 0;
        private $_type      = null;

        private $_columns   = null;
        private $_condition = '_id is not null';
        private $_bind      = [];
        private $_aggregate = null;
        private $_distinct  = null;
        private $_group     = '';
        private $_having    = '';
        private $_order     = [];
        private $_offset    = 0;
        private $_count     = 20;

        private $_data      = [];
        private $_record    = true;
        private $_registry  = null;

        const INSERT = 'INSERT';
        const SELECT = 'SELECT';
        const UPDATE = 'UPDATE';
        const DELETE = 'DELETE';
        public function __construct(Config $config)
        {
                $config = $config->get('mongodb');
                parent::__construct($config['uri'], $config['uriOptions']??[], $config['driverOptions']??[]);
        }

        public function from(string $table, string $database = '') : DB
        {
                if(strpos($table, '.') !== false) {
                        $arr = explode('.', $table);
                        $this->_table = $arr[1];
                        $this->_database = $arr[0];
                }else {
                        if($database == '') {
                                { throw new \LogicException('database is null'); }
                        }
                        $this->_database = $database;
                        $this->_table = $table;
                }
                return $this;
        }

        public function insert(array $data): DB
        {
                if($this->_state >= 1) { throw new \LogicException('syntax error'); }

                if(!isset($data['_id'])) {
                        $data['_id'] = new ObjectID();
                }

                $this->_type = self::INSERT;
                $this->_data = $data;
                $this->_state= 7;

                return $this;
        }

        public function delete(array $condition = []): DB
        {
                // TODO: Implement delete() method.
        }

        public function select(string $fields = '*'): DB
        {
                // TODO: Implement select() method.
        }

        public function update(array $data): DB
        {
                if($this->_state >= 1) { throw new \LogicException('syntax error'); }

                $this->_type = static::UPDATE;

                if(isset($data['_id'])) {
                        $key = $data['_id'];
                        unset($data['_id']);

                        $this->_data = $data;

                        return $this->where('_id=?', array($key));
                } else {
                        $this->_data  = $data;
                        $this->_state = 2;

                        return $this;
                }
        }

        public function where(string $where, array $conditions): DB
        {
                // TODO: Implement where() method.
        }

        public function group(string $fields): DB
        {
                // TODO: Implement group() method.
        }

        public function limit(int $limit, int $offset): DB
        {
                // TODO: Implement limit() method.
        }

        public function order(string $field, string $sort = 'desc'): DB
        {
                // TODO: Implement order() method.
        }

        public function having(string $condition, array $bind = null): DB
        {
                // TODO: Implement having() method.
        }

        public function execute($object = false) : string
        {

                $manager    = $this->getManager();
                $collection = $this->_database.'.'.$this->_table;
                $bulk       = new BulkWrite();

                if($this->_type!==static::INSERT) {
                        $tree     = $this->_parse($this->_condition);
                        $criteria = $this->_bind($tree, $this->_bind);
                        $query    = new Mongodb($criteria, array(
                                'projection' => array('_id'=>1),
                                'skip'       => 0,
                                'limit'      => $this->_count,
                        ));
                        $cursor   = $manager->executeQuery($collection, $query)->toArray();

                        if(count($cursor)>0) {
                                $keys = [];
                                foreach($cursor as $row) {
                                        $keys[] = $row->_id;
                                }
                                $criteria = array('_id'=>array('$in'=>$keys));
                        } else {
                                $this->_reset();
                                return '0';
                        }
                }

                switch($this->_type) {
                        case static::INSERT :
                                $bulk->insert($this->_data);
                                $manager->executeBulkWrite($collection, $bulk);
                                $result = $this->_data['_id'];
                                break;
                        case static::UPDATE :
                                $bulk->update($criteria, array('$set'=>$this->_data), array('multi'=>true));
                                $result = $manager->executeBulkWrite($collection, $bulk)->getModifiedCount();
                                break;
                        case static::DELETE :
                                $bulk->delete($criteria, array('limit'=>0));
                                $result = $manager->executeBulkWrite($collection, $bulk)->getDeletedCount();
                                break;
                        default             :
                                $result = '0';
                }

                $this->_reset();

                return strval($result);
        }

}