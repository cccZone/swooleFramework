<?php
namespace Library;
class httpClient
{
        private static $_curl = [];

        private $_httpCode    = 0;
        private $_url         = '';
        private $_timeout     = 5;
	private $_headerOpen  = false;
	private $_response = [];
        private $_httpContypeType = null;

        /**
         * httpClient constructor.
         */
        public function __construct(string $url = '')
        {
                if($url != '') {
                        $this->setUrl($url);
                }
        }

        /**
         * 设置一个访问地址
         * @param string $url
         * @return $this
         */
        public function setUrl(string $url = '')
        {
                if(static::$_curl) {
                        foreach(static::$_curl as $k=>$v) {
                                $this->_colse($k);
                        }

                        unset($k, $v);
                }

                $this->_url = $url;
                $this->_createRequest($url);
                $this->setTimeout($this->_timeout);

                return $this;
        }

        /**
         * curl get 请求
         * @access public
         * @return httpClient
         */
        public function setGet()
        {
                curl_setopt(static::$_curl[$this->_url], CURLOPT_HTTPGET, true);

                return $this;
        }

        /**
         * curl post 请求
         * @access public
         * @param  $args
         * @param  $type
         * @return httpClient
         */
        public function setPost($args, $type = '')
        {
                curl_setopt(static::$_curl[$this->_url], CURLOPT_POST, true);
                /*if(is_array($args)) {
                        array_walk($args, function(&$v){
                                $v = strval($v);
                                if($v{0} == '@') {
                                        $file = substr($v, 1);
                                        if(!is_file($file)) {
                                                throw new \Exception('the '.$file.' is not exist');
                                        }

                                        $v = new \CURLFile($file);
                                }
                        });
                }*/

                curl_setopt(static::$_curl[$this->_url], CURLOPT_POSTFIELDS, $args);
                if( $type == 'json' ) {
                        curl_setopt(static::$_curl[$this->_url], CURLOPT_HTTPHEADER, ['Content-Type:application/json','Content-Length:'.strlen($args)]);
                }

                return $this;
        }

        /**
         * curl put 请求
         * @param $args
         * @return $this
         */
        public function setPut($args)
        {
                curl_setopt(static::$_curl[$this->_url], CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt(static::$_curl[$this->_url], CURLOPT_POSTFIELDS, $args);

                return $this;
        }

        /**
         * curl patch 请求
         * @param $args
         * @return $this
         */
        public function setPatch($args = '')
        {
                curl_setopt(static::$_curl[$this->_url], CURLOPT_CUSTOMREQUEST, 'PATCH');
                if ($args) {
                        curl_setopt(static::$_curl[$this->_url], CURLOPT_POSTFIELDS, $args);
                }

                return $this;
        }

        /**
         * curl delete 请求
         * @return $this
         */
        public function setDelete($args = '')
        {
                curl_setopt(static::$_curl[$this->_url], CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt(static::$_curl[$this->_url], CURLOPT_POSTFIELDS, $args);

                return $this;
        }
	
	/**
	 * 设置其他参数
	 * @param $key
	 * @param $value
	 */
        public function setOpt($key, $value)
        {
        	curl_setopt(static::$_curl[$this->_url], $key, $value);
        	return $this;
        }
        /**
         * 发送请求
         * @access private
         * @return string
         * @throws \exception
         */
        public function send()
        {
                curl_setopt(static::$_curl[$this->_url], CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt(static::$_curl[$this->_url], CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt(static::$_curl[$this->_url], CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec(static::$_curl[$this->_url]);

	        if($this->_headerOpen) {
	        	list(,$header,$response) = explode("\r\n\r\n",$response);
	        	$this->_response = [
				'header'        =>      $header,
		                'response'      =>      $response
		        ];
	        }else{
	        	$this->_response = [
			        'response'      =>      $response
		        ];
	        }

                //设置错误号
                $this->_httpCode = curl_getinfo(static::$_curl[$this->_url], CURLINFO_HTTP_CODE);
	        $this->_httpContypeType = curl_getinfo(static::$_curl[$this->_url], CURLINFO_CONTENT_TYPE);

                $this->_colse($this->_url);
                return $response;
        }

        public function getResponse()
        {
        	return $this->_response['response'];
        }

        public function getResponseHeader()
        {
        	return $this->_response['header'];
        }

        /**
         * 设置auth认证
         * @access public
         * @param  string $user
         * @param  string $pass
         * @return httpClient
         * @throws \Exception
         */
        public function setAuth(string $user = '', string $pass = '')
        {
                if( !$user ) {
                        throw new \Exception('USER 不能为空');
                }

                if( !$pass ) {
                        throw new \Exception('PASS 不能为空');
                }

                curl_setopt(static::$_curl[$this->_url], CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt(static::$_curl[$this->_url], CURLOPT_USERPWD,  "[{$user}]:[{$pass}]");

                return $this;
        }

        /**
         * 设置http请求版本
         * @access public
         * @param  string $version
         * @return httpClient
         * @throws \Exception
         */
        public function setVersion(string $version = '1.1')
        {
                if( !$version ) {
                        throw new \Exception('version 不能为空');
                }

                curl_setopt(static::$_curl[$this->_url], CURLOPT_HTTP_VERSION, $version);

                return $this;
        }

        /**
         * 设置userAgent
         * @param string $userAgent
         * @return $this
         * @throws \Exception
         */
        public function setUserAgent(string $userAgent = '')
        {
                if( !$userAgent ) {
                        throw new \Exception('userAgent 不能为空');
                }

                curl_setopt(static::$_curl[$this->_url], CURLOPT_USERAGENT, $userAgent);

                return $this;
        }

        /**
         * 设置referer
         * @param string $referer
         * @return $this
         * @throws \Exception
         */
        public function setReferer(string $referer = '')
        {
                if( !$referer ) {
                        throw new \Exception('referer 不能为空');
                }

                curl_setopt(static::$_curl[$this->_url], CURLOPT_REFERER, $referer);

                return $this;
        }

        public function setTimeout(int $timeout = 0)
        {
                if( $timeout <= 0 ) {
                        throw new \Exception('timeout 不能为小于等于0');
                }

                curl_setopt(static::$_curl[$this->_url], CURLOPT_TIMEOUT, $timeout);

                return $this;
        }

        /**
         * 关闭头部输出
         */
        public function setCloseHeaderOut($headerOpen = false)
        {
        	$this->_headerOpen = $headerOpen;
                curl_setopt(static::$_curl[$this->_url], CURLOPT_HEADER, $headerOpen);
	        return $this;
        }

        /**
         * 这是请求头部信息
         * @access public
         * @param array $header
         * @return $this
         */
        public function setHeader(array $header = [])
        {
        	curl_setopt(static::$_curl[$this->_url], CURLOPT_HTTPHEADER, $header);
                return $this;
        }

        /**
         * 设置请求不验证证书和域名
         * @param bool $ssl
         * @return httpClient
         */
        public function setSSL($ssl = false)
        {
                curl_setopt(static::$_curl[$this->_url], CURLOPT_SSL_VERIFYPEER, $ssl);
                curl_setopt(static::$_curl[$this->_url], CURLOPT_SSL_VERIFYHOST, $ssl);

                return $this;
        }

        /**
         * 返回http请求状态
         * @return int
         */
        public function getHttpCode()
        {
                return $this->_httpCode;
        }

        /**
         * 返回 http 请求 content type
         * @return null
         */
        public function getContentType()
        {
                return $this->_httpContypeType;
        }

        /**
         * 创建curl请求对象请求
         * @param string $url
         * @return mixed
         * @throws \Exception
         */
        private function _createRequest(string $url = '')
        {
                if( !$url ) {
                        throw new \Exception('url 不能为空');
                }

                if( isset(static::$_curl[$url]) && is_resource(static::$_curl[$url]) ) {
                        return static::$_curl[$url];
                }

                return static::$_curl[$url] = curl_init($url);
        }

        /**
         * 关闭curl链接
         * @access private
         * @param $url
         * @return void
         */
        private function _colse($url)
        {
                if( isset(static::$_curl[$url]) && is_resource(static::$_curl[$url]) ) {
                        curl_close(static::$_curl[$url]);
                }
        }
}