<?php
/**
 * S3 wrapper class
 *
 * @since  1.0
 * @package  secure-media
 */

namespace SecureMedia;

use SecureMedia\Utils;
use \Aws\S3\S3Client as AWSS3;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * S3 class
 */
class S3Client {
	/**
	 * S3 client
	 *
	 * @var \Aws\S3\S3Client
	 */
	protected $s3_client = null;

	/**
	 * Setup s3 class
	 */
	public function setup() {
		$settings = Utils\get_settings();

		$params = array( 'version' => 'latest' );

		$params['credentials']['key']    = $settings['s3_access_key_id'];
		$params['credentials']['secret'] = $settings['s3_secret_access_key'];

		$params['signature'] = 'v4';
		$params['region']    = $settings['s3_region'];

		$this->s3_client = AWSS3::factory( $params );
	}

	/**
	 * Delete object
	 *
	 * @param  string $key Object key
	 * @return mixed
	 */
	public function delete( $key ) {
		return $this->s3_client->deleteObject(
			[
				'Bucket' => Utils\get_settings( 's3_bucket' ),
				'Key'    => $key,
			]
		);
	}

	/**
	 * Get object by key
	 *
	 * @param string  $path Path to save file
	 * @param string  $key Object key
	 * @param boolean $delete_remote Delete remote copy or not
	 * @return void
	 */
	public function save( $path, $key, $delete_remote = false ) {
		$this->s3_client->getObject(
			[
				'Bucket' => Utils\get_settings( 's3_bucket' ),
				'Key'    => $key,
				'SaveAs' => $path,
			]
		);

		if ( $delete_remote ) {
			$this->delete( $key );
		}
	}

	/**
	 * Get object by key
	 *
	 * @param  string $key Object key
	 * @return array
	 */
	public function get( $key ) {
		return $this->s3_client->getObject(
			[
				'Bucket' => Utils\get_settings( 's3_bucket' ),
				'Key'    => $key,
			]
		);
	}

	/**
	 * Update object acl
	 *
	 * @param string $acl Permissions
	 * @param string $key Object key
	 * @return mixed
	 */
	public function update_acl( $acl, $key ) {
		return $this->s3_client->putObjectAcl(
			[
				'Bucket' => Utils\get_settings( 's3_bucket' ),
				'Key'    => $key,
				'ACL'    => $acl,
			]
		);
	}

	/**
	 * Get s3 client
	 *
	 * @return \Aws\S3\S3Client
	 */
	public function client() {
		return $this->s3_client;
	}

	/**
	 * Get s3 bucket URL
	 *
	 * @return string
	 */
	public function get_bucket_url() {
		$bucket = strtok( Utils\get_settings( 's3_bucket' ), '/' );
		$path   = substr( Utils\get_settings( 's3_bucket' ), strlen( $bucket ) );

		return 'https://' . $bucket . '.s3.amazonaws.com';
	}

	/**
	 * Return singleton instance of class
	 *
	 * @return self
	 * @since 1.0
	 */
	public static function factory() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
			$instance->setup();
		}

		return $instance;
	}
}
