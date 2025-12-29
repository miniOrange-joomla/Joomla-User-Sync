<?php
/**
 * @package     Joomla.Component
 * @subpackage  com_miniorange_usersync
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Document\HtmlDocument;
$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_miniorange_usersync/assets/js/jquery.1.11.0.min.js');
$document->addScript(Uri::base() . 'components/com_miniorange_usersync/assets/js/utilityjs.js');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_usersync/assets/css/miniorange_user_sync.css');

use Joomla\CMS\MVC\Controller\FormController;
class MiniorangeUsersyncControllerAccountsetup extends FormController
{
	function __construct()
	{
		$this->view_list = 'accountsetup';
		parent::__construct();
	}
	
	//TO SAVE CONFIGURATION
	function moSaveConfig(){
		
		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
		$get = ($input && $input->get) ? $input->get->getArray() : [];
		$attribute_mapping = array();
		$app_name = $get['appName'];	

		
		$configuration = array();
		foreach($post as $key =>$value){

			$value = trim($value);
			if(empty($value)){
				$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&&tab-panel=syncconfiguration&addition_option=addconfig&app='.$app_name.'&sub_tab=config', Text::_('COM_MINIORANGE_USERSYNC_EMPTY_CONFIGURATION_ADDED'), 'error');
				return;
			}
			$configuration[$key] = $value;

		}
	
		$conditions = array('app_name'=>$app_name);
		$database_name = '#__miniorange_user_sync_config';
		$updatefieldsarray = array(
			'mo_sync_configuration' 	  	=> json_encode($configuration),
			'app_name'						=>$app_name,
		);

		MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
		$message="";
		$config="";
		if(!empty($app_name)){
			switch($app_name){
				case "Azure": 
					$access_token = Azure::getAccessToken();
					if(empty($access_token)){
						$config= 'INVALID_CONFIGUATION';
					}
					else if(isset($access_token->error)){
						$message=$access_token->error_description;
					}else{
						Azure::getAllUsers();
					}
					break;
				case "AWS" :
					$access_token = MoCognito::getAllUsers();
				   	if(isset($access_token->errorCode)){
						$message = $access_token->errorCode.': '.$access_token->errorSummary;
				   	}
					break;
				case "Keycloak":
					$access_token = MoKeycloak::getAccessToken();
					if(empty($access_token)){
						$config = 'INVALID_CONFIGURATION';
					}
					else if(isset($access_token->error)){
						$message = $access_token->error;
					}
					else{
						MoKeycloak::getAllUsers();
					}
					break;
				case "Salesforce":
					// For Salesforce, we need username and password for OAuth username-password flow
					// Check if username and password are provided in configuration
					$sf_username = isset($configuration['mo_salesforce_username']) ? $configuration['mo_salesforce_username'] : '';
					$sf_password = isset($configuration['mo_salesforce_password']) ? $configuration['mo_salesforce_password'] : '';
					
					if(empty($sf_username) || empty($sf_password)){
						$config = 'INVALID_CONFIGURATION';
						$message = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_USERNAME_PASSWORD_REQUIRED');
					} else {
						$access_token = Salesforce::getAccessToken($sf_username, $sf_password);
						if(empty($access_token)){
							$config = 'INVALID_CONFIGURATION';
						}
						else if(isset($access_token->error)){
							$message = $access_token->error . ': ' . (isset($access_token->error_description) ? $access_token->error_description : Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_AUTHENTICATION_FAILED_SHORT'));
						}else{
							// Store access token, refresh token, and instance URL for future use
							$configuration['mo_salesforce_access_token'] = $access_token->access_token;
							$configuration['mo_salesforce_instance_url'] = isset($access_token->instance_url) ? $access_token->instance_url : '';
							// Store refresh token if available (for token refresh)
							if (isset($access_token->refresh_token)) {
								$configuration['mo_salesforce_refresh_token'] = $access_token->refresh_token;
							}
							$updatefieldsarray['mo_sync_configuration'] = json_encode($configuration);
							MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
							Salesforce::getAllUsers();
						}
					}
					break;
				case "Okta":
				   $access_token = MoOkta::getAllUsers();
				   	if(isset($access_token->errorCode)){
						$message = $access_token->errorCode.': '.$access_token->errorSummary;
				   	}
					break;
				}
		}
		
		if(!empty($config)){
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&&tab-panel=syncconfiguration&addition_option=addconfig&app='.$app_name.'&sub_tab=config', Text::_('COM_MINIORANGE_USERSYNC_UNABLE_TO_CONNECT') . ' ' . $app_name . '. ' . Text::_('COM_MINIORANGE_USERSYNC_RECHECK_CONFIGURATION'), 'error');
			return;
		}
		else if(empty($message)){
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&&tab-panel=syncconfiguration&addition_option=addconfig&app='.$app_name.'&sub_tab=config',Text::_('COM_MINIORANGE_CONNECTION_SUCCESSFULLY_SAVED'));
			return;
		}else{
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&&tab-panel=syncconfiguration&addition_option=addconfig&app='.$app_name.'&sub_tab=config',$message, 'error');
			return;
		}		
	}

	function moResetConfig(){

		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$get = ($input && $input->get) ? $input->get->getArray() : [];
		$app_name = $get['appName'];	
		
		$database_name = '#__miniorange_user_sync_config';
		$updatefieldsarray = array(
			'mo_sync_configuration' 	  	=> "",
			'app_name'						=> "",
		);

		MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);

		$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&&tab-panel=syncconfiguration&addition_option=addconfig&app='.$app_name.'&sub_tab=config', Text::_('COM_MINIORANGE_USERSYNC_APPLICATION_CONFIGURATION_RESET_SUCCESS'));
		return;
	}
	//ON PERFORMING TEST CONFIGURATION
	function moGetClient(){

		$config = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
		$database_username = isset($config['mo_usersync_upn']) ? $config['mo_usersync_upn'] : "";
		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
		$get = ($input && $input->get) ? $input->get->getArray() : [];
		$encodedUsername = $input->getString('username', '');
		$encodedappName = $input->getString('appName', '');
		$username = MoUserSyncUtility::safeBase64Decode($encodedUsername);
		$appName = MoUserSyncUtility::safeBase64Decode($encodedappName);
		$user_details = "";
		$message = "";
		$access_token="";
		if(!$username && !$database_username){
			?>
			<div style="display:flex;justify-content:center;align-items:center;flex-direction:column;border:1px solid #eee;padding:10px;">
				<div style="width:90%;color:#fff;background-color: #ff6e6e;padding: 2%;margin-bottom: 20px;text-align: center;border: 1px solid #b71c1c;font-size: 18pt;">
					<?php echo Text::_('COM_MINIORANGE_TEST_UNSUCCESSFUL');?>
				</div>
				<p class="mo_boot_my-4"><?php echo Text::_('COM_MINIORANGE_USERSYNC_ENTER_TEST_USERNAME');?></p>
			</div>
			<?php exit;
		}
	
		if(!empty($appName)){
			switch($appName){
				case "Azure": 
					$user_details = Azure::getUserDetails($username);
					if(isset($user_details[0]->error)){
						$message = $user_details[0]->error_description;
					}else if(isset($user_details['error'])){
						$message = $user_details['error']->message;
					}
					else{
						Azure::getAllUsers();
					}
					break;
				case "AWS" :
					$user_details = MoCognito::getCognitoUserDetails($username);
					if(isset($user_details['errorCode'])){
						$message = $user_details['errorCode'].': '.$user_details['errorSummary'];
				   	}else{
						$access_token = MoCognito::getAllUsers();
					}
					break;
				case "Keycloak":
					$user_details = MoKeycloak::getUserDetails($username);
					if(isset($user_details[0]->error)){
						$message = $user_details[0]->error;
					}else if(isset($user_details['error'])){
						$message = $user_details['error'];
					}
					else{
						MoKeycloak::getAllUsers();
					}
					break;
				case "Salesforce":
					$user_details = Salesforce::getUserDetails($username);
					if(isset($user_details[0]->error)){
						$message = $user_details[0]->error_description ?? $user_details[0]->error;
					}else if(isset($user_details['error'])){
						$message = isset($user_details['error_description']) ? $user_details['error_description'] : $user_details['error'];
					}
					else{
						Salesforce::getAllUsers();
					}
					break;
				case "Okta":
			   		$user_details = MoOkta::getUserDetails($username);
					if(isset($user_details['errorCode'])){
						$message = $user_details['errorCode'].': '.$user_details['errorSummary'];
				   	}else{
						$access_token = MoOkta::getAllUsers();
					}
					break;
			}

	
			if (!empty($message)){
				?>
				<div style="display:flex;justify-content:center;align-items:center;flex-direction:column;border:1px solid #eee;padding:10px;">
					<div style="width:90%;color:#fff;background-color: #ff6e6e;padding: 2%;margin-bottom: 20px;text-align: center;border: 1px solid #b71c1c;font-size: 18pt;">
						<?php echo Text::_('COM_MINIORANGE_TEST_UNSUCCESSFUL');?>
					</div>
					<p class="my-4"><?php echo $message;?></p>
				</div>
				<?php exit;
			}
			else{
				unset($user_details['configuration_status']);
			?>
				<div style="display:flex;justify-content:center;align-items:center;flex-direction:column;border:1px solid #eee;padding:10px;">
					<div style="width:90%;color: #3c763d;background-color: #dff0d8;padding: 2%;margin-bottom: 20px;text-align: center;border: 1px solid #AEDB9A;font-size: 18pt;">
						<?php echo Text::_('COM_MINIORANGE_TEST_SUCCESSFUL');?>
					</div>
					<div class="test-container my-4 mt-5">
						<table class="mo-ms-tab-content-app-config-table">
							<tr>
								<td class="text-center" colspan="2">
									<span><h2 class="mo_usersync_attributes"><?php echo Text::_('COM_MINIORANGE_TEST_ATTRIBUTES');?></h2></span>
								</td>
							</tr>
							<?php 
								self::mo_test_attr_mapping_config("",$user_details);?>
						</table>
					</div>
				</div>
				<?php 	
				//GET USER LIST
				$userAttributes = Factory::getApplication()->get('attributesNames');
				$database_name = '#__miniorange_sync_to_joomla';
				$updatefieldsarray = array(
					'moUserAttr' 	  	=> trim($userAttributes),	
				);
				MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
				exit;
			}
		}
		else{
		
			?>
			<div style="display:flex;justify-content:center;align-items:center;flex-direction:column;border:1px solid #eee;padding:10px;">
				<div style="width:90%;color:#fff;background-color: #ff6e6e;padding: 2%;margin-bottom: 20px;text-align: center;border: 1px solid #b71c1c;font-size: 18pt;">
					<?php echo Text::_('COM_MINIORANGE_TEST_UNSUCCESSFUL');?>
				</div>
				<p class="my-4"><?php echo Text::_('COM_MINIORANGE_USERSYNC_SELECT_APPLICATION_TEST_CONFIG');?></p>
			</div>
			<?php exit;

		}
	}

	//FOR THE NESTED MAPPING
    public static function mo_test_attr_mapping_config($nestedprefix, $resourceOwnerDetails){
		
        if(!empty($nestedprefix))
            $nestedprefix .= ".";
        
        foreach($resourceOwnerDetails as $key => $resource){
            if(is_array($resource) || is_object($resource)){
				if($nestedprefix=="")
					$x = $nestedprefix.$key;
				else {
					$x = $nestedprefix.'.'.$key;
				}
			
                self::mo_test_attr_mapping_config($x,$resource);
            } else {
                echo "<tr><td style='bold;padding:2%;border:2px solid #949090; word-wrap:break-word;'>";
                if(!empty($nestedprefix))
                     echo $nestedprefix;
    
                echo $key."</td><td style='bold;padding:2%;border:2px solid #949090; word-wrap:break-word;max-width:300px !important'>".$resource."</td></tr>";
    
                $getAttributeName = Factory::getApplication()->get('attributesNames');
				if(empty($nestedprefix) || empty($key))
					$attributeNames = $getAttributeName.$key.',';
				else{
					$attributeNames = $getAttributeName.$nestedprefix.$key.',';				
				}
			
				Factory::getApplication()->set('attributesNames', $attributeNames);
            }
    
        }
    }
	
	//SAVE SALESFORCE PROFILE ID
	public function moSaveSalesforceProfile()
	{
		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
		$get = ($input && $input->get) ? $input->get->getArray() : [];
		$profileId = isset($post['moSalesforceProfileId']) ? $post['moSalesforceProfileId'] : "";
		$appName = isset($get['appName']) ? ($get['appName']) : "";
		
		if (!empty($profileId) && $appName === 'Salesforce') {
			$database_name = '#__miniorange_user_sync_config';
			$config = MoUserSyncUtility::moGetDetails($database_name);
			$mo_salesforce_config = !empty($config['mo_sync_configuration']) ? json_decode($config['mo_sync_configuration'], true) : array();
			
			$mo_salesforce_config['mo_salesforce_profile_id'] = $profileId;
			
			$updatefieldsarray = array(
				'mo_sync_configuration' => json_encode($mo_salesforce_config),
			);
			MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
			
			$message = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_PROFILE_SAVED_SUCCESS');
		} else {
			$message = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_PROFILE_SAVED_ERROR');
		}
		
		$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=mo_joomla_profile_sync', $message);
		return;
	}

	//TO MANUALLY CREATE USER IN PROVIDER
	public function moCreateUserInProvider()	
	{
		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
		$get = ($input && $input->get) ? $input->get->getArray() : [];
		$username = isset($post['moCreateUser']) ? $post['moCreateUser'] : "";
		$appName = isset($get['appName']) ? ($get['appName']) : "";
		$message = "";
		if(!empty($appName)){
			switch($appName){
				case "Azure": 
					$status= Azure::createUserInAzure($username);
					if(isset($status->error)){
						$message = $status->error->message;
					}
					break;
				case "AWS" :
					$status = MoCognito::createCognitoUser($username);
					if(isset($status->errorMessage)){
						$message = $status->errorMessage;
					}
					break;
				case "Keycloak":
					$status = MoKeycloak::createUserInKeycloak($username);
					if(isset($status->errorMessage)){
						$message = $status->errorMessage;
					}
					break;
				case "Salesforce":
					$status = Salesforce::createUserInSalesforce($username);
					if(isset($status->error)){
						$message = isset($status->error_description) ? $status->error_description : $status->error;
					}
					break;
				case "Okta":
					$status = MoOkta::createUserInOkta($username);
					if(isset($status->errorCode)){
						$message = $status->errorCauses[0]->errorSummary;
					}
					break;
				}
		}
		
		if(!empty($message)){
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=mo_joomla_profile_sync', $message, 'error');
			return;
		}
		else{
			$message = Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_CREATE_INDIVIDUAL_USER_SUCCESSFUL1').$username.Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_CREATE_INDIVIDUAL_USER_SUCCESSFUL2');
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=mo_joomla_profile_sync', $message);
			return;
		}
		
	}

	//TO RETRIEVE ALL USERS
	public function moRetriveAllUsers(){
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$get = ($input && $input->get) ? $input->get->getArray() : [];
		$appName = isset($get['appName']) ? $get['appName'] : "";

		if(!empty($appName)){
			switch($appName){
				case "Azure": 
					Azure::getAllUsers();
					break;
				case "AWS" :
					$user_details = MoCognito::getAllUsers();
					break;
				case "Keycloak":
					MoKeycloak::getAllUsers();
					break;
				case "Salesforce":
					Salesforce::getAllUsers();
					break;
				case "Okta":
				   	$user_details = MoOkta::getAllUsers();
					break;
			}
		}

		$message = Text::_('COM_MINIORANGE_USERSYNC_ALL_USERS_RETRIEVED_SUCCESS');
		$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla',$message );
		return;
	
	}

	//CREATE/SYNC SINGLE USER IN JOOMLA
	public function moSyncUserInJoomla(){
		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
		$get = ($input && $input->get) ? $input->get->getArray() : [];
		$username = isset($post['userSyncToJoomla'])? $post['userSyncToJoomla']: '';
		$appName = isset($get['appName']) ? ($get['appName']) : "";
	
		if(!empty($appName)){
			switch($appName){
				case "Azure": 
					$status = Azure::createUserInJoomla(json_decode($username,true));
					break;
				case "AWS" :
					$status = MoCognito::createUserInJoomla(json_decode($username,true));
					break;
				case "Keycloak":
					$status = MoKeycloak::createUserInJoomla(json_decode($username,true));
					break;
				case "Salesforce":
					$status = Salesforce::createUserInJoomla(json_decode($username,true));
					if(is_array($status) && isset($status['error'])){
						$message = isset($status['error_description']) ? $status['error_description'] : $status['error'];
						$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla',$message,'error');
						return;
					}
					break;
				case "Okta":
			   		$status = MoOkta::createUserInJoomla(json_decode($username,true));
					break;
			}
		}
	
		if($status == 'USER_NOT_PRESENT'){
			$message = Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_UNSUCCESSFUL');
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla',$message,'error');	
			return;
		}
		else if($status == 'EMPTY_USERNAME'){
			$message = Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_UNSUCCESSFUL_EMPTY_USERNAME');
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla',$message,'error');	
			return;
		}
		else if($status == 'EMPTY_EMAIL'){
			$message = Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_UNSUCCESSFUL_EMPTY_EMAIL');
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla',$message,'error');	
			return;
		}
		else if($status == 'EMPTY_NAME'){
			$message = Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_UNSUCCESSFUL_EMPTY_NAME');
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla',$message,'error');	
			return;
		}
		else if($status == 'INVALID_EMAIL_ATTRIBUTE'){
			$message = Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_UNSUCCESSFUL_INVALID_EMAIL');
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla',$message,'error');	
			return;
		}
		else if ($status == 'USER_CREATED'){
	
			$usercreated = json_decode($username,true);
		
			$message = Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_SUCCESSFUL1'). $usercreated[0]['Name'].Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_SUCCESSFUL2');
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla', $message);
			return;
		}
		else if ($status == 'USER_NOT_SYNCED'){
			$message = Text::_('COM_MINIORANGE_USERSYNC_USER_ALREADY_CREATED_PREMIUM');
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla', $message, 'warning');
			return;
		}
		else{
			$message = Text::_('COM_MINIORANGE_USERSYNC_USER_ALREADY_CREATED_PREMIUM');
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$appName.'&sub_tab=sync_to_joomla', $message, 'warning');
			return;
		}
	}
	
	//SUPPORT FUNCTION
	function moContactUs()
	{
		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
		$query_email = isset($post['mo_query_email'])? $post['mo_query_email'] : '';
		$query = isset($post['mo_query']) ? $post['mo_query'] : '';

		if (sizeof(array_unique(str_split($query))) < 10) {
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup', Text::_('COM_MINIORANGE_ENTER_PROPER_QUERY'), 'error');
			return;
		}

		if (MoUserSyncUtility::moCheckEmptyOrNull($query_email) || MoUserSyncUtility::moCheckEmptyOrNull($query)) {
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup', Text::_('COM_MINIORANGE_QUERY_WITH_EMAIL'), 'error');
			return;
		} else {
			$query = $post['mo_query'];
			$email = $post['mo_query_email'];
			$phone = $post['mo_query_phone'];
			$contact_us = new MoUserSyncCustomer();
			$submited = json_decode($contact_us->moSubmitContactUs($email, $phone, $query),true);
			if(json_last_error() == JSON_ERROR_NONE) {
				if(is_array($submited) && array_key_exists('status', $submited) && $submited['status'] == 'ERROR'){
					$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_support&app=other', $submited['message'],'error');
					return;
				}else{
					if ( $submited == false ) {
						$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_support&app=other', Text::_('COM_MINIORANGE_QUERY_NOT_SUBMITTED'),'error');
						return;
					} else {
						$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_support&app=other', Text::_('COM_MINIORANGE_QUERY_SENT'));
						return;
					}
				}
			}
		}
	}

	function moSaveSyncAttributeConfig() {
		
		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
		$get = ($input && $input->get) ? $input->get->getArray() : [];
		$moSyncName = isset($post['mo_joomla_name'])?$post['mo_joomla_name']:'';
		$moSyncUsername = isset($post['mo_joomla_username'])? $post['mo_joomla_username'] : '';
		$moSyncEmail =isset($post['mo_joomla_email'])? $post['mo_joomla_email'] : "";
		$appName = isset($get['appName']) ? $get['appName'] : "";
		if( empty($moSyncName)|| empty($moSyncUsername) || empty($moSyncEmail) ){	
			$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&app='.$appName.'&sub_tab=sync_to_joomla',Text::_('COM_MINIORANGE_ENTER_NAME_USERNAME_EMAIL'),'error');
			
			return;
		}		
	
		$database_name = '#__miniorange_sync_to_joomla';
		$updatefieldsarray = array(
			'moName' 	  	=> trim($moSyncName),
			'moUsername' 	=> trim($moSyncUsername),
			'moEmail' 	 	=> trim($moSyncEmail),		
		);
	
		MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
		$this->setRedirect('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&app='.$appName.'&sub_tab=sync_to_joomla' ,Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_ATTRIBUTE_MAPPING_SAVED'));
		return;
	}
}