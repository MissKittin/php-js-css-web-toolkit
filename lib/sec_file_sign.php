<?php
	class file_sign_exception extends Exception {}
	final class file_sign
	{
		/*
		 * Easily generate file signatures
		 *
		 * Warning:
		 *  openssl extension is required
		 *
		 * Note:
		 *  you cannot inherit from this class
		 *  the *_file_signature methods use the SHA1 hash for the file id
		 *  throws an file_sign_exception on error
		 *
		 * Methods:
		 *  [static] generate_keys(array_params)
		 *   params:
		 *    'private_key'=>string_path
		 *    'public_key'=>string_path
		 *    'key_bits'=>int (default: 2048)
		 *    'key_type'=>OPENSSL_KEYTYPE_CONSTANT (default: OPENSSL_KEYTYPE_RSA)
		 *  __construct(array_params)
		 *   params:
		 *    'private_key'=>string_path
		 *    'public_key'=>string_path
		 *    'signature_algorithm'=>string_from_openssl_get_md_methods(true) (default: sha256WithRSAEncryption)
		 *  generate_input_signature(string_data) [returns string_signature]
		 *  verify_input_signature(string_data, string_signature) [returns bool]
		 *  generate_file_signature(string_file) [returns string_signature]
		 *  verify_file_signature(string_file, string_signature) [returns bool]
		 *  encrypt_data(string_data) [returns string_encrypted_data]
		 *   bonus method
		 *  decrypt_data(string_encrypted_data) [returns string_decrypted_data|false]
		 *   bonus method
		 *
		 * Generate key pair:
			file_sign::generate_keys([
				'private_key'=>'./private.pem',
				'public_key'=>'./public.pem'
			]);
		 *
		 * Usage:
			$filesign=new file_sign([
				'private_key'=>'./private.pem',
				'public_key'=>'./public.pem'
			]);

			$signature=$filesign->generate_input_signature('Message');
			$verification=$filesign->verify_input_signature('Message', $signature);

			$signature=$filesign->generate_file_signature(__FILE__);
			$verification=$filesign->verify_file_signature(__FILE__, $signature);

			$encrypted=$filesign->encrypt_data('Secret message');
			$decrypted=$filesign->decrypt_data($encrypted);
		 */

		private $private_key;
		private $public_key;
		private $signature_algorithm='sha256WithRSAEncryption';

		public static function generate_keys(array $params)
		{
			if(!extension_loaded('openssl'))
				throw new file_sign_exception('openssl extension is not loaded');

			if(!isset($params['key_bits']))
				$params['key_bits']=2048;

			if(!isset($params['key_type']))
				$params['key_type']=OPENSSL_KEYTYPE_RSA;

			foreach([
				'private_key'=>'string',
				'public_key'=>'string',
				'key_bits'=>'integer',
				'key_type'=>'integer'
			] as $param)
				if(isset($params[$param]) && (gettype($params[$param]) !== $param_type))
					throw new file_sign_exception('The input array parameter '.$param.' is not a '.$param_type);

			foreach(['private_key', 'public_key'] as $param)
			{
				if(!isset($params[$param]))
					throw new file_sign_exception('No '.$param.' parameter');

				if(file_exists($params[$param]))
					throw new file_sign_exception($param.' file exists');
			}

			$keys=openssl_pkey_new([
				'private_key_bits'=>$params['key_bits'],
				'private_key_type'=>$params['key_type']
			]);

			if($keys === false)
				throw new file_sign_exception('openssl_pkey_new() failed');

			openssl_pkey_export($keys, $private_key);

			if(file_put_contents($params['private_key'], $private_key) === false)
				throw new file_sign_exception('Unable to write private key file');

			if(file_put_contents($params['public_key'], openssl_pkey_get_details($keys)['key']) === false)
				throw new file_sign_exception('Unable to write public key file');
		}

		public function __construct(array $params)
		{
			if(!extension_loaded('openssl'))
				throw new file_sign_exception('openssl extension is not loaded');

			foreach(['private_key', 'public_key', 'signature_algorithm'] as $param)
				if(isset($params[$param]))
				{
					if(!is_string($params[$param]))
						throw new file_sign_exception('The input array parameter '.$param.' is not a string');

					$this->$param=$params[$param];
				}

			foreach(['private_key', 'public_key'] as $key)
			{
				if(!isset($params[$key]))
					throw new file_sign_exception('The '.$key.' parameter was not specified for the constructor');

				$this->$key=realpath($this->$key);

				if($this->$key === false)
					throw new file_sign_exception($key.' file does not exists');
			}

			if(!in_array($this->signature_algorithm, openssl_get_md_methods(true)))
				throw new file_sign_exception('Wrong signature algorithm');
		}

		public function generate_input_signature(string $data)
		{
			$private_key=openssl_pkey_get_private('file://'.$this->private_key);

			if($private_key === false)
				throw new file_sign_exception('Invalid private key file');

			openssl_sign($data, $signature, $private_key, $this->signature_algorithm);
			openssl_free_key($private_key);

			return $signature;
		}
		public function verify_input_signature(string $data, string $signature)
		{
			$public_key=openssl_pkey_get_public('file://'.$this->public_key);

			if($public_key === false)
				throw new file_sign_exception('Invalid public key file');

			$result=openssl_verify($data, $signature, $public_key, $this->signature_algorithm);
			openssl_free_key($public_key);

			switch($result)
			{
				case 1:
					return true;
				case 0:
					return false;
				default:
					throw new file_sign_exception('Error checking signature');
			}
		}
		public function generate_file_signature(string $file)
		{
			if(!file_exists($file))
				throw new file_sign_exception($file.' does not exists');

			return $this->generate_input_signature(sha1_file($file, true));
		}
		public function verify_file_signature(string $file, string $signature)
		{
			if(!file_exists($file))
				throw new file_sign_exception($file.' does not exists');

			return $this->verify_input_signature(sha1_file($file, true), $signature);
		}
		public function encrypt_data(string $data)
		{
			$private_key=openssl_pkey_get_private('file://'.$this->private_key);

			if($private_key === false)
				throw new file_sign_exception('Invalid private key file');

			openssl_private_encrypt($data, $encrypted_data, $private_key);
			openssl_free_key($private_key);

			return $encrypted_data;
		}
		public function decrypt_data(string $encrypted_data)
		{
			$public_key=openssl_pkey_get_public('file://'.$this->public_key);

			if($public_key === false)
				throw new file_sign_exception('Invalid public key file');

			openssl_public_decrypt($encrypted_data, $decrypted_data, $public_key);
			openssl_free_key($public_key);

			if($decrypted_data === null)
				return false;

			return $decrypted_data;
		}
	}
?>