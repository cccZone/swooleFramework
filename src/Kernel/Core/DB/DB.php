<?php


namespace Kernel\Core\DB;


interface DB
{
        public function insert(array $data) : DB;
        public function delete(array $condition = []) : DB;
        public function select(string $fields = '*') : DB ;
        public function update(array $data) : DB;
        public function where(string $where, array $conditions) : DB;
        public function group(string $fields) : DB;
        public function limit(int $limit, int $offset) : DB;
        public function order(string $field, string $sort = 'desc') : DB;
        public function having(string $condition, array $bind=null) : DB;
        public function from(string $table) : DB;
        public function execute($object = false) : string ;
}