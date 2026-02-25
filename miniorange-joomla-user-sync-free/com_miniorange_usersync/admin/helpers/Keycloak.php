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

/*This class contains all the ldap constants*/
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
$userList = array();
Factory::getApplication()->set('UserList', $userList);

class MoKeycloak
{
    private $domain="";
	private $clientId="";
    private $realm="";
    private $realmManagerUsername="";
    private $realmManagerPassword="";
    private $grantType="";
    private $userDetailsEndpoint="";
    private $accessTokenEndpoint="";
    private $getAllUsersAPI="";
    private $userList = array();
    private $mapUsername="";
    private $mapEmail="";
    private $mapName="";
    public function __construct(){

        $app = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
		$moKeycloakAppAttr = !empty($app['mo_sync_configuration']) ? json_decode($app['mo_sync_configuration'], true) : "";
        $this->domain 					= isset($moKeycloakAppAttr['mo_keycloak_domain']) 		? $moKeycloakAppAttr['mo_keycloak_domain'] 		: "";
        $this->clientId 				= isset($moKeycloakAppAttr['mo_keycloak_client_id'])		? $moKeycloakAppAttr['mo_keycloak_client_id']		: "";
        $this->realm 					= isset($moKeycloakAppAttr['mo_keycloak_realm'])			? $moKeycloakAppAttr['mo_keycloak_realm']			: "";
        $this->realmManagerUsername 	= isset($moKeycloakAppAttr['mo_keycloak_username'])		? $moKeycloakAppAttr['mo_keycloak_username']		: "";
        $this->realmManagerPassword     = isset($moKeycloakAppAttr['mo_keycloak_user_password'])	? $moKeycloakAppAttr['mo_keycloak_user_password']	: "";
        $this->userDetailsEndpoint      =  $this->domain.'/admin/realms/'. $this->realm.'/users?username=';
        $this->accessTokenEndpoint = $this->domain.'/realms/'.$this->realm.'/protocol/openid-connect/token';
        $this->grantType = 'password';
        $this->getAllUsersAPI = $this->domain.'/admin/realms/'.$this->realm.'/users';
        
        
        $mapDeatils = MoUserSyncUtility::moGetDetails('#__miniorange_sync_to_joomla');
        $this->mapUsername= isset($mapDeatils['moUsername'])	? $mapDeatils['moUsername']	: "";
        $this->mapEmail = isset($mapDeatils['moEmail'])	? $mapDeatils['moEmail']	: "";
        $this->mapName= isset($mapDeatils['moName'])	? $mapDeatils['moName']	: "";
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getRealm()
    {
        return $this->realm;
    }
  
    public function getClientId()
    {
        return $this->clientId;
    }

    public function getRealmManagerUsername()
    {
        return $this->realmManagerUsername;
    }

    public function getRealmManagerPassword()
    {
        return $this->realmManagerPassword;
    }

    public function getAcessTokenEndpoint()
    {
        return $this->accessTokenEndpoint;
    }

    public function getGrantType()
    {
        return $this->grantType;
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

        $keycloakDetails=new MoKeycloak();
        $url = $keycloakDetails->getAcessTokenEndpoint(); 
        $header=array('Accept: application/x-www-form-urencoded');
        $post=true;
        $post_fields = '&username='.urlencode($keycloakDetails->getRealmManagerUsername()).'&password='.urlencode($keycloakDetails->getRealmManagerPassword()).'&grant_type='.urlencode($keycloakDetails->getGrantType()).'&client_id='.urlencode($keycloakDetails->getClientId());
        $testConfig = "";
        $accessToken = Authorization::moGetAccessToken($url,$header,$post,$post_fields);
        return $accessToken;
    }
    public static function getUserDetails($username)
    {
        //TEST CONFIGURATION

        $accessToken = self::getAccessToken();
        $isError = isset($accessToken->error) ? $accessToken->error : '';
    
        if(empty($isError)){

            $keycloakDetails=new MoKeycloak();
            $userDetailsEndpoint = $keycloakDetails->getUserDetailsEndpoint().urlencode($username).'&exact=true';
            $userDetails = Authorization::moGetUserDetails($userDetailsEndpoint,$accessToken);
            if(!empty($userDetails)){
                $userDetails = array($userDetails);
                $userDetails['configuration_status'] = 'error';
                return $userDetails;
            }else if(empty($userDetails)){
                $error = array();
                $error['error'] ="MO 01: An unknown error occurred. For more details contact joomlasupport@xecurify.com";
                $error['configuration_status'] = 'error';
                return $error;
            }

        }
        else{
        
            $error = array($accessToken);
            $error['configuration_status'] = 'error';
            return $error;
        }

    }


    public static function getAllUsers(){
        
        $accessToken = self::getAccessToken();
        $isError = isset($accessToken->error) ? $accessToken->error : '';
        
        if(empty($isError)){

            $keycloakDetails=new MoKeycloak();
            $users = self::mo_keycloak_sync_get_request($keycloakDetails->getAllUsersAPI(),$accessToken);
        
            self::moKeycloakTestAttrMappingConfig("", $users);
            
            $userList = Factory::getApplication()->get('UserList');
			$database_name = '#__miniorange_sync_to_joomla';
			$updatefieldsarray = array(
				'moUserList' 	  	=> json_encode($userList),	
			);
			MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
        }
    }

    public static function mo_keycloak_sync_get_request($user_info_url, $agrs){
        
        $user_info = [];
        $user_details = Authorization::moGetDetails($user_info_url, $agrs);
        
        $user_details = json_decode($user_details);
        foreach($user_details as $key=>$value){
            
            if(empty($value) || !isset($value)){continue;}
            else if(!is_array($value)){
            
                $user_info[$key] = $value;
            }
            else{
                  echo '';
            }
        }
        return $user_info;
    }

    //FOR MAKING THE DETAILS AS PER KEYCLOAK STANDARDS
    public static function moKeycloakTestAttrMappingConfig($nestedprefix, $resourceOwnerDetails)
    {   	
	    $keycloak_user_name = "";
	    $keycloak_user_id = "";
	
        foreach ($resourceOwnerDetails as $key => $resource) {

		    if (is_array($resource) || is_object($resource)) {
                self::moKeycloakTestAttrMappingConfig($nestedprefix, $resource);
            }else {
                
		  	    if($key == "id")
				    $keycloak_user_id =trim($resource);

			    if($key == 'username')
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

    public static function createUserInJoomla($username){
        
        $accessToken = self::getAccessToken();
        $isError = isset($accessToken->error) ? $accessToken->error : '';
      
         if(empty($isError)){
            
            $usernameToSync = $username[0]['Name'];

            $db = MoUserSyncUtility::moGetDatabase();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__users')
                ->where(strtolower($db->quoteName('email')) . ' = ' . $db->quote(strtolower($usernameToSync)) . 'OR' . strtolower($db->quoteName('username')) . '=' . $db->quote(strtolower($usernameToSync)).'OR'.strtolower($db->quoteName('name')) . '=' . $db->quote(strtolower($usernameToSync)));
            $db->setQuery($query);
            $joomla_user = $db->loadAssoc();            
            
        
            $keycloakDetails=new MoKeycloak();
            $userDetailsEndpoint = $keycloakDetails->getUserDetailsEndpoint().urlencode($username[0]['Name']).'&exact=true';
 
            $user_Details = Authorization::moGetUserDetails($userDetailsEndpoint,$accessToken);
            $user_Details= array($user_Details);

            $user_not_present = isset($user_Details[0]->error) ? 'user_not_present' : 'user_present';
            $user_in_joomla = isset($joomla_user) ? $joomla_user : '';
            $moUserDetails['keycloak_user_id'] = Authorization::getnestedattribute($user_Details[0], '0.id') ;
            $moUserDetails['username'] = Authorization::getnestedattribute($user_Details, $keycloakDetails->getMapUsername());
            $moUserDetails['email'] = Authorization::getnestedattribute($user_Details, $keycloakDetails->getMapEmail());
            $moUserDetails['name'] = Authorization::getnestedattribute($user_Details, $keycloakDetails->getMapName());
        
            if(empty(trim($moUserDetails['username'])))
                return 'EMPTY_USERNAME';
            else if(empty(trim($moUserDetails['email'])))
                return 'EMPTY_EMAIL';
            else if(empty(trim($moUserDetails['name'])))
                return 'EMPTY_NAME';

            if(!filter_var($moUserDetails['email'], FILTER_VALIDATE_EMAIL))
                return 'INVALID_EMAIL_ATTRIBUTE';

            if(Authorization::getnestedattribute($user_Details, '0..enabled') == true )
                $moUserDetails['enabled'] = 0;
            else
                $moUserDetails['enabled'] = 1;
   
    
            if(!$user_in_joomla){
                $status = Authorization::moCreateUserInJoomla($moUserDetails,'Keycloak');
                return 'USER_CREATED';
            }
            else{

                return 'USER_ALREADY_PRESENT';
            }
        }
        
    }

    //TO CREATE USER IN KEYCLOAK
    public static function createUserInKeycloak($username){

        $accessToken = self::getAccessToken();
        $isError = isset($accessToken->error) ? $accessToken->error : '';
        
        if(empty($isError)){

            $db = MoUserSyncUtility::moGetDatabase();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__users')
                ->where($db->quoteName('email') . ' = ' . $db->quote($username) . 'OR' . $db->quoteName('username') . '=' . $db->quote($username));
            $db->setQuery($query);
            $result = $db->loadAssocList();
        
            if($result[0]['block'] ==1)
                $enabled = 'false';
            else
                $enabled = 'true';
  
            $userObject = '{
                "firstName": "'.$result[0]['username'].'",
                "lastName": "'.$result[0]['username'].'",
                "email": "'.$result[0]['email'].'",
                "enabled": '.$enabled.',
                "emailVerified": true,
                "username": "'.$result[0]['username'].'"
            }';

            $keycloakDetails=new MoKeycloak();
            $response = Authorization::createUserInProvider($keycloakDetails->getAllUsersAPI(),$accessToken,$userObject);
            return json_decode($response); 
        }
    }
}
?>