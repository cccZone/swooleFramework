<?php
$data = file_get_contents('https://udn.com/news/story/7241/2618686?from=udn_mobile_indexrecommend');
preg_match('/<TITLE>([\w\W]*?)<\/TITLE>/si', $data, $matches);
if (!empty($matches[1])) {
        $meta['title'] = $matches[1];
}
if(preg_match("/<[meta|META].+?[name|NAME]=['|\"].+?([\w\W]*?)['|\"]/si", $data)) {
        preg_match_all("/<[meta|META].+?[name|NAME]=['|\"]([\w\W]*?)['|\"].+?[content|CONTENT]=['|\"]([\w\W]*?)['|\"]/si", $data, $matches);
        if(isset($matches[1]) and isset($matches[2])) {
                $keys   = $matches[1];
                $values = $matches[2];
                $count = count($keys);
                if($count == count($values)) {
                        for ($i=0;$i<$count;$i++) {
                                $meta[$keys[$i]] = $values[$i];
                        }
                }
        }
}

if(preg_match("/<[meta|META].+?[property|Property]=['|\"].+?([\w\W]*?)['|\"]/si", $data)) {
        preg_match_all("/<[meta|META].+?[property|Property]=['|\"]([\w\W]*?)['|\"].+?[content|CONTENT]=['|\"]([\w\W]*?)['|\"]/si", $data, $matches);
        if(isset($matches[1]) and isset($matches[2])) {
                $keys   = $matches[1];
                $values = $matches[2];
                $count = count($keys);
                if($count == count($values)) {
                        for ($i=0;$i<$count;$i++) {
                                $key = $keys[$i];
                                if(strpos($key,':')!==false){
                                        $arr = explode(':', $key);
                                        if(count($arr)==2) {
                                                $meta[$arr[1]] = $values[$i];
                                        }
                                }
                                //$meta[$keys[$i]] = $values[$i];
                        }
                }
        }
}
var_dump($meta['image']);

//var_dump($meta);