<?php
/**
 * 利用curl模块调用
 */
class Apidao_Curl
{
	protected $gateway = 'http://pub.apidao.com';
	protected $appkey;
	protected $secretKey;

	public function __construct($appkey, $secretKey, $gateway = NULL)
	{
		$this->appkey = $appkey;
		$this->secretKey = $secretKey;
		empty($gateway) || $this->gateway = $gateway;
	}

	/**
	 * 调用接口
	 *
	 * @param $methodName
	 * @return array|mixed
	 */
	public function execute($methodName)
	{
		$args = func_get_args();
		$methodName = array_shift($args);

		$nonce = uniqid(md5(rand()), true);
		$timestamp = time();

		try {
			$response = $this->post($this->gateway, json_encode([
				'nonce' => $nonce,
				'appkey' => $this->appkey,
				'methodName' => $methodName,
				'timestamp' => $timestamp,
				'args' => $args,
				'signature' => $this->hashArr($this->secretKey, $timestamp, $nonce),
			]));

			$result = json_decode($response, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new Exception(json_last_error_msg());
			}
		} catch (Exception $e) {
			$result = ['errcode' => $e->getCode(), 'errmsg' => $e->getMessage()];
		}

		return $result;
	}

	protected function hashArr()
	{
		$args = func_get_args();
		sort($args, SORT_STRING);
		return sha1(implode($args));
	}

	protected function post($url, $data)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/plain']);

		$return = curl_exec($ch);

		curl_close($ch);
		return $return;
	}


	protected function post2($url, $data)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: text/raw',
			'Content-Length: ' . strlen($data),
		]);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($data));
		$return = curl_exec($ch);

		curl_close($ch);
		return $return;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		array_unshift($arguments, implode('.', explode('_', $name, 2)));
		return call_user_func_array([$this, 'execute'], $arguments);
	}
}