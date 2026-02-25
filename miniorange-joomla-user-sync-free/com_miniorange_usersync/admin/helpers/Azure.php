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
$userList = array();
Factory::getApplication()->set('UserList', $userList);
class Azure
{
    private $appId="";
	private $clientSecret="";
    private $tenantId="";
    private $tenantName="";
    private $accessTokenEndpoint="";
    private $userDetailsEndpoint="";
    private $getAllUsersAPI="";
    private $mapUsername="";
    private $mapEmail="";
    private $mapName="";

    public function __construct(){

        $app = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
		$moAzureAppAttr = !empty($app['mo_sync_configuration']) ? json_decode($app['mo_sync_configuration'], true) : "";
        $this->appId = isset($moAzureAppAttr['mo_azure_application_id'])? $moAzureAppAttr['mo_azure_application_id']: "";
        $this->clientSecret = isset($moAzureAppAttr['mo_azure_client_secret'])? $moAzureAppAttr['mo_azure_client_secret']: "";
        $this->tenantId = isset($moAzureAppAttr['mo_azure_tenant_id'])? $moAzureAppAttr['mo_azure_tenant_id']: "";
        $this->tenantName = isset($moAzureAppAttr['mo_azure_tenant_name'])? $moAzureAppAttr['mo_azure_tenant_name']: "";
        $this->accessTokenEndpoint = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token';
        $this->userDetailsEndpoint = 'https://graph.microsoft.com/beta/users/';
        $this->getAllUsersAPI = 'https://graph.microsoft.com/v1.0/users';

        $mapDeatils = MoUserSyncUtility::moGetDetails('#__miniorange_sync_to_joomla');
        $this->mapUsername= isset($mapDeatils['moUsername'])	? $mapDeatils['moUsername']	: "";
        $this->mapEmail = isset($mapDeatils['moEmail'])	? $mapDeatils['moEmail']	: "";
        $this->mapName= isset($mapDeatils['moName'])	? $mapDeatils['moName']	: "";
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getTenantId()
    {
        return $this->tenantId;
    }

    public function getTenantName()
    {
        return $this->tenantName;
    }

    public function getAcessTokenEndpoint()
    {
        return $this->accessTokenEndpoint;
    }

    public function getUserDetailsEndpoint()
    {
        return $this->userDetailsEndpoint;
    }

    public function getAllUsersAPI()
    {
        return $this->getAllUsersAPI;
    }

    public function getMapUsername()
    {
        return $this->mapUsername;
    }
    public function getMapName()
    {
        return $this->mapName;
    }
    public function getMapEmail()
    {
        return $this->mapEmail;
    }
    

    public static function getAccessToken(){
        
        $AzureDetails=new Azure();
        $header = array('Accept: application/json', 'Authorization: Basic ' . ($AzureDetails->getAppId() . ":" . $AzureDetails->getClientSecret() ));
        $postFields = '&grant_type=client_credentials'.'&client_id='.urlencode($AzureDetails->getAppId()).'&client_secret='.urlencode($AzureDetails->getClientSecret()).'&scope=https://graph.microsoft.com/.default';
        $post = true;
        $accessToken = Authorization::moGetAccessToken($AzureDetails->getAcessTokenEndpoint(),$header,$post,$postFields);
        return $accessToken;
    }

    public static function getUserDetails($username)
    {
        //TEST CONFIGURATION
        $AzureDetails=new Azure();
        $isError = self::getAccessToken();
        if(isset($isError->error)){
            $error = array($isError);
            $error['configuration_status'] = 'error';
            return $error;
        }
        if(isset($isError->access_token)){

            $userDetailsEndpoint = $AzureDetails->getUserDetailsEndpoint().urlencode($username);
            $userDetails = Authorization::moGetUserDetails($userDetailsEndpoint,$isError);
            if(isset($userDetails['error'])){
                return $userDetails;
            }
            $userDetails = array($userDetails);
            $userDetails['configuration_status'] = 'successful';
            return $userDetails;

        }
        else{
            $error = array($isError);
            $error['configuration_status'] = 'error';
            return $error;
        }

    }

    //RETRIEVE ALL USERS
    public static function getAllUsers(){
        
        $AzureDetails=new Azure();
        $accessToken = self::getAccessToken();
        
        $isError = isset($accessToken->error) ? $accessToken->error_description : '';
        
        if(empty($isError)){
            $users = Authorization::moGetAllUsers($AzureDetails->getAllUsersAPI(),$accessToken);
            $ad_users = json_decode($users,true);
            self::moAzureTestAttrMappingConfig("", $ad_users);

            $AttributeName = Factory::getApplication()->get('UserList');
			$database_name = '#__miniorange_sync_to_joomla';
	        $updatefieldsarray = array(
			    'moUserList' 	  	=> json_encode($AttributeName),	
		    );
		   	MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
        }
    }

     //FOR MAKING THE DETAILS AS PER AZURE STANDARDS
     public static function moAzureTestAttrMappingConfig($nestedprefix, $resourceOwnerDetails)
     {   	
         $keycloak_user_name = "";
         $keycloak_user_id = "";
     
         foreach ($resourceOwnerDetails as $key => $resource) {
        
             if (is_array($resource) || is_object($resource)) {
                 self::moAzureTestAttrMappingConfig($nestedprefix, $resource);
             }else {

                   if($key == "userPrincipalName")
                     $keycloak_user_id =trim($resource);
 
                 if($key == 'displayName')
                     $keycloak_user_name = trim($resource);
             }
         
             if($keycloak_user_id !="" && $keycloak_user_name !=""){
                 
                $users = array();
                array_push($users,array('Name'=>$keycloak_user_name, 'Id' =>$keycloak_user_id));
                $getAttributeName = Factory::getApplication()->get('UserList');
                array_push($getAttributeName, $users);
				Factory::getApplication()->set('UserList', $getAttributeName);
                break;
             }
         }
     }

    //CREATE USER IN AZURE ACTIVE DIRECTORY
    public static function createUserInAzure($username){
        $AzureDetails = new Azure();
        $accessToken= self::getAccessToken();
        $isError = isset($accessToken->error) ? $accessToken->error_description : '';

        if($isError){

            $error = array($is_error);
            array_push($error,'error');
            return $error;
        }
        else{
        

            if (strpos($username, '@')==='false' )
                $username = $username;
            else
                $username = strtok($username,  '@');
            
            
            $mailNickName = $username;
            $userObject = '{
                "accountEnabled":true,
                "passwordProfile" : {
                "password": "Passw0rd.",
                "forceChangePasswordNextSignIn": false
                },
                "mailNickname": "'.$mailNickName.'",
                "passwordPolicies": "DisablePasswordExpiration"
            }';

            $userObject = json_decode($userObject,true);
            $userObject['userPrincipalName'] = $username.'@'.$AzureDetails->getTenantName();
            $userObject['displayName'] = $username;
            $userObject = json_encode($userObject);
            $response = Authorization::createUserInProvider($AzureDetails->getUserDetailsEndpoint(),$accessToken,$userObject);
            return json_decode($response); 
        }
    }

    public static function createUserInJoomla($username){

        $isError = self::getAccessToken();
        if(isset($isError->error)){
            $error = array($isError);
            array_push($error,'error');
            return $error;
        }

        if(isset($isError->access_token)){

            $usernameToSync = $username[0]['Name'];
            $db = MoUserSyncUtility::moGetDatabase();
            $query = $db->getQuery(true)
            ->select('*')
            ->from('#__users')
            ->where($db->quoteName('email') . ' = ' . $db->quote($usernameToSync) . 'OR' . $db->quoteName('username') . '=' . $db->quote($usernameToSync).'OR'.strtolower($db->quoteName('name')) . '=' . $db->quote(strtolower($usernameToSync)));
            $db->setQuery($query);
            $joomla_user = $db->loadAssocList();
            
            $AzureDetails=new Azure();
            $userDetailsEndpoint = $AzureDetails->getUserDetailsEndpoint().urlencode($username[0]['Id']);

            $user_Details = Authorization::moGetUserDetails($userDetailsEndpoint,$isError);
            $user_Details= array($user_Details);
          
            $user_in_joomla = isset($joomla_user) ? $joomla_user : '';
        
       
            $user_not_present = isset($user_Details[0]->error) ? 'user_not_present' : 'user_present';
            if($user_not_present =='user_not_present'){
                return 'USER_NOT_PRESENT';
            }

            $moUserDetails = array();
            $moUserDetails['username'] = Authorization::getnestedattribute($user_Details[0], $AzureDetails->getMapUsername());
            $moUserDetails['email'] = Authorization::getnestedattribute($user_Details[0], $AzureDetails->getMapEmail());
            $moUserDetails['name'] = Authorization::getnestedattribute($user_Details[0], $AzureDetails->getMapName());

            if(Authorization::getnestedattribute($user_Details[0], 'accountEnabled') == true )
                $moUserDetails['enabled'] = 0;
            else
                $moUserDetails['enabled'] = 1;

            if(empty(trim($moUserDetails['username'])))
                return 'EMPTY_USERNAME';
            
            if(empty(trim($moUserDetails['email'])))
                return 'EMPTY_EMAIL';
            
            if(empty(trim($moUserDetails['name'])))
                return 'EMPTY_NAME';

            if(!filter_var($moUserDetails['email'], FILTER_VALIDATE_EMAIL))
                return 'INVALID_EMAIL_ATTRIBUTE';

            if(!$user_in_joomla){
                Authorization::moCreateUserInJoomla($moUserDetails,'Azure');
                return 'USER_CREATED';
            }
            else{
                return 'USER_ALREADY_PRESENT';
            }
        }
        else{
            return $isError;
        }
    }
}