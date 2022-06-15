<?php
namespace sgpb;


class Updates
{
	private $licenses = array();
	private $licenseClass;

	public function setLicenses($licenses)
	{
		$this->licenses = $licenses;
	}

	public function getLicenses()
	{
		return $this->licenses;
	}

	public function __construct()
	{
		$this->licenseClass = new License();
		$this->init();
	}

	private function init()
	{
		$this->setLicenses($this->licenseClass->getLicenses());
		$licenses = $this->getLicenses();

		if (empty($licenses)) {
			return false;
		}
		add_action('admin_menu', array($this, 'menu'), 22);
		add_action('admin_init', array($this, 'sgpbActivateLicense'));
		add_action('admin_notices', array($this, 'sgpbAdminNotices'));

		return true;
	}

	public function menu()
	{
		add_submenu_page('edit.php?post_type='.SG_POPUP_POST_TYPE, __('License', SG_POPUP_TEXT_DOMAIN), __('License', SG_POPUP_TEXT_DOMAIN), 'sgpb_manage_options', SGPB_POPUP_LICENSE, array($this, 'pluginLicense'));
	}

	public function sanitizeLicense($new)
	{
		$old = get_option('sgpb-license-key-'.$this->licenseKey);

		if ($old && $old != $new) {
			delete_option('sgpb-license-status-'.$this->licenseKey); // new license has been entered, so must reactivate
		}
		update_option('sgpb-license-key-'.$this->licenseKey, $new);

		return $new;
	}

	public function pluginLicense()
	{
		require_once(SG_POPUP_VIEWS_PATH.'license.php');
	}

	public function sgpbActivateLicense()
	{
		$licenses = $this->getLicenses();

		foreach ($licenses as $license) {
			$key = @$license['key'];
			$itemId = @$license['itemId'];
			$itemName = @$license['itemName'];
			$storeURL = @$license['storeURL'];
			$this->licenseKey = $key;

			if (isset($_POST['sgpb-license-key-'.$key])) {
				$this->sanitizeLicense($_POST['sgpb-license-key-'.$key]);
			}

			// listen for our activate button to be clicked
			if (isset($_POST['sgpb-license-activate-'.$key])) {
				// run a quick security check
				if (!check_admin_referer('sgpb_nonce', 'sgpb_nonce')) {
					return; // get out if we didn't click the Activate button
				}
				// retrieve the license from the database
				$license = trim(get_option('sgpb-license-key-'.$key));
				// data to send in our API request
				$apiParams = array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_id'    => $itemId, // The ID of the item in EDD
					'url'        => home_url()
				);
				// Call the custom API.
				$response = wp_remote_post($storeURL, array('timeout' => 15, 'sslverify' => false, 'body' => $apiParams));
				// make sure the response came back okay
				if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
					$errorMessage = $response->get_error_message();
					$message = (is_wp_error($response) && ! empty($errorMessage)) ? $errorMessage : __('An error occurred, please try again.', SG_POPUP_TEXT_DOMAIN);
				}
				else {
					$licenseData = json_decode(wp_remote_retrieve_body($response));
					if (false === $licenseData->success) {
						switch ($licenseData->error) {
							case 'expired' :
								$message = sprintf(
									__('Your license key expired on %s.', SG_POPUP_TEXT_DOMAIN),
									date_i18n(get_option('date_format'), strtotime($licenseData->expires, current_time('timestamp')))
								);
								break;
							case 'revoked' :
								$message = __('Your license key has been disabled.', SG_POPUP_TEXT_DOMAIN);
								break;
							case 'missing' :
								$message = __('Invalid license.', SG_POPUP_TEXT_DOMAIN);
								break;
							case 'invalid' :
							case 'site_inactive' :
								$message = __('Your license is not active for this URL.',SG_POPUP_TEXT_DOMAIN);
								break;
							case 'item_name_mismatch' :
								$message = sprintf(__('This appears to be an invalid license key for %s.', SG_POPUP_TEXT_DOMAIN), $itemName);
								break;
							case 'no_activations_left' :
								$message = __('You\'ve already used the permitted number of this license key!', SG_POPUP_TEXT_DOMAIN);
								break;
							default :
								$message = __('An error occurred, please try again.', SG_POPUP_TEXT_DOMAIN);
								break;
						}
					}
				}
				// Check if anything passed on a message constituting a failure
				if (!empty($message)) {
					$baseUrl = admin_url('edit.php?post_type='.SG_POPUP_POST_TYPE.'&page='.SGPB_POPUP_LICENSE);
					$redirect = add_query_arg(array('sl_activation' => 'false', 'message' => urlencode($message)), $baseUrl);
					wp_redirect($redirect);
					exit();
				}
				// $licenseData->license will be either "valid" or "invalid"
				update_option('sgpb-license-status-'.$key, $licenseData->license);
				$hasInactiveExtensions = AdminHelper::hasInactiveExtensions();
				// all available extensions have active license status
				if (empty($hasInactiveExtensions)) {
					// and if we don't have inactive extensions, remove option, until new one activation
					delete_option('SGPB_INACTIVE_EXTENSIONS', 'inactive');
				}
				wp_redirect(admin_url('edit.php?post_type='.SG_POPUP_POST_TYPE.'&page='.SGPB_POPUP_LICENSE));
				exit();
			}

			if (isset($_POST['sgpb-license-deactivate'.$key])) {
				$license = trim(get_option('sgpb-license-key-'.$key));
				// data to send in our API request
				$apiParams = array(
					'edd_action' => 'deactivate_license',
					'license'    => $license,
					'item_id'    => $itemId, // The ID of the item in EDD
					'url'        => home_url()
				);
				$home = home_url();
				// Send the remote request
				$response = wp_remote_post($storeURL, array('body' => $apiParams, 'timeout' => 15, 'sslverify' => false));
				if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
					$errorMessage = $response->get_error_message();
					$message = (is_wp_error($response) && ! empty($errorMessage)) ? $errorMessage : __('An error occurred, please try again.', SG_POPUP_TEXT_DOMAIN);
					$baseUrl = admin_url('edit.php?post_type='.SG_POPUP_POST_TYPE.'&page='.SGPB_POPUP_LICENSE);
					$redirect = add_query_arg(array('message' => urlencode($message)), $baseUrl);
					wp_redirect($redirect);
					exit();
				}
				else {
					$status = false;
					$licenseData = json_decode(wp_remote_retrieve_body($response));
					if (isset($licenseData->success)) {
						$status = $licenseData->success;
					}
					update_option('sgpb-license-status-'.$key, $status);
					update_option('SGPB_INACTIVE_EXTENSIONS', 'inactive');
					wp_redirect(admin_url('edit.php?post_type='.SG_POPUP_POST_TYPE.'&page='.SGPB_POPUP_LICENSE));
					exit();
				}
			}
		}
	}

	public function sgpbAdminNotices()
	{
		if (isset($_GET['sl_activation']) && !empty($_GET['message'])) {
			switch ($_GET['sl_activation']) {
				case 'false':
					$message = urldecode($_GET['message']);
					?>
					<div class="error">
						<h3><?php echo $message; ?></h3>
					</div>
					<?php
					break;
				case 'true':
					break;
			}
		}
	}
}
