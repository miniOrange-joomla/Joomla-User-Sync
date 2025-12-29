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
use Joomla\CMS\Language\Text;
$userList = array();
Factory::getApplication()->set('UserList', $userList);

class Salesforce
{
    private $consumerKey = "";
    private $consumerSecret = "";
    private $redirectUri = "";
    private $scopes = "";
    private $accessTokenEndpoint = "";
    private $userDetailsEndpoint = "";
    private $getAllUsersAPI = "";
    private $instanceUrl = "";
    private $mapUsername = "";
    private $mapEmail = "";
    private $mapName = "";

    public function __construct()
    {
        $app = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
        $mo_salesforce_application_attributes = !empty($app['mo_sync_configuration']) ? json_decode($app['mo_sync_configuration'], true) : "";
        
        $this->consumerKey = isset($mo_salesforce_application_attributes['mo_salesforce_consumer_key']) ? $mo_salesforce_application_attributes['mo_salesforce_consumer_key'] : "";
        $this->consumerSecret = isset($mo_salesforce_application_attributes['mo_salesforce_consumer_secret']) ? $mo_salesforce_application_attributes['mo_salesforce_consumer_secret'] : "";
        $this->redirectUri = isset($mo_salesforce_application_attributes['mo_salesforce_redirect_uri']) ? $mo_salesforce_application_attributes['mo_salesforce_redirect_uri'] : "";
        $this->scopes = isset($mo_salesforce_application_attributes['mo_salesforce_scopes']) ? $mo_salesforce_application_attributes['mo_salesforce_scopes'] : "api refresh_token";
        
        // Default Salesforce login endpoint - can be production or sandbox
        // Try to detect if it's a sandbox based on username (sandbox usernames often contain .sandbox or test)
        $isSandbox = false;
        if (!empty($mo_salesforce_application_attributes['mo_salesforce_username'])) {
            $username = $mo_salesforce_application_attributes['mo_salesforce_username'];
            // Check if username contains test or sandbox indicators
            if (stripos($username, 'test') !== false || stripos($username, 'sandbox') !== false || stripos($username, '.cs') !== false) {
                $isSandbox = true;
            }
        }
        
        // Use test.salesforce.com for sandbox, login.salesforce.com for production
        $this->accessTokenEndpoint = $isSandbox ? 'https://test.salesforce.com/services/oauth2/token' : 'https://login.salesforce.com/services/oauth2/token';
        $this->userDetailsEndpoint = '/services/data/v61.0/sobjects/User/';
        $this->getAllUsersAPI = '/services/data/v61.0/query/?q=SELECT+Id,Username,Email,FirstName,LastName,Name+FROM+User';

        $mapDetails = MoUserSyncUtility::moGetDetails('#__miniorange_sync_to_joomla');
        $this->mapUsername = isset($mapDetails['moUsername']) ? $mapDetails['moUsername'] : "";
        $this->mapEmail = isset($mapDetails['moEmail']) ? $mapDetails['moEmail'] : "";
        $this->mapName = isset($mapDetails['moName']) ? $mapDetails['moName'] : "";
    }

    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    public function getConsumerSecret()
    {
        return $this->consumerSecret;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function getScopes()
    {
        return $this->scopes;
    }

    public function getAccessTokenEndpoint()
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

    public function getInstanceUrl()
    {
        return $this->instanceUrl;
    }

    public function setInstanceUrl($url)
    {
        $this->instanceUrl = $url;
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

    /**
     * Get access token using Username-Password OAuth flow
     * This is used for server-to-server authentication
     */
    public static function getAccessToken($username, $password)
    {
        $salesforceDetails = new Salesforce();
        
        if (empty($salesforceDetails->getConsumerKey()) || empty($salesforceDetails->getConsumerSecret())) {
            $error = new stdClass();
            $error->error = 'invalid_client';
            $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_CONSUMER_KEY_SECRET_REQUIRED');
            return $error;
        }

        if (empty($username) || empty($password)) {
            $error = new stdClass();
            $error->error = 'invalid_request';
            $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_USERNAME_PASSWORD_REQUIRED');
            return $error;
        }

        $url = $salesforceDetails->getAccessTokenEndpoint();
        $header = array('Content-Type: application/x-www-form-urlencoded');
        
        $postFields = 'grant_type=password' .
            '&client_id=' . urlencode($salesforceDetails->getConsumerKey()) .
            '&client_secret=' . urlencode($salesforceDetails->getConsumerSecret()) .
            '&username=' . urlencode($username) .
            '&password=' . urlencode($password);
        
        $post = true;
        $accessToken = Authorization::moGetAccessToken($url, $header, $post, $postFields);
        
        // Improve error messages for common Salesforce errors
        if (isset($accessToken->error)) {
            if ($accessToken->error == 'invalid_grant') {
                $errorDescription = isset($accessToken->error_description) ? $accessToken->error_description : '';
                
                // Provide more helpful error messages
                if (stripos($errorDescription, 'authentication failure') !== false || stripos($errorDescription, 'invalid_grant') !== false) {
                    $accessToken->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_AUTHENTICATION_FAILED');
                }
            }

            $error = new stdClass();
            $error->error = ucfirst(str_replace('_', ' ', $accessToken->error));
            
            // Use Salesforce description if available; otherwise generic fallback
            if (isset($accessToken->error_description) && !empty($accessToken->error_description)) {
                $error->error_description = $accessToken->error_description;
            } else {
                $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_GENERIC_ERROR');
            }

            return $error;
        }
        
        return $accessToken;
    }

    /**
     * Refresh access token using refresh token
     */
    public static function refreshAccessToken($refreshToken = null)
    {
        $salesforceDetails = new Salesforce();
        
        if (empty($salesforceDetails->getConsumerKey()) || empty($salesforceDetails->getConsumerSecret())) {
            $error = new stdClass();
            $error->error = 'invalid_client';
            $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_CONSUMER_KEY_SECRET_REQUIRED');
            return $error;
        }

        // Get refresh token from config if not provided
        if (empty($refreshToken)) {
            $config = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
            $mo_salesforce_config = !empty($config['mo_sync_configuration']) ? json_decode($config['mo_sync_configuration'], true) : "";
            $refreshToken = isset($mo_salesforce_config['mo_salesforce_refresh_token']) ? $mo_salesforce_config['mo_salesforce_refresh_token'] : null;
        }

        if (empty($refreshToken)) {
            $error = new stdClass();
            $error->error = 'invalid_refresh_token';
            $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_REFRESH_TOKEN_NOT_AVAILABLE');
            return $error;
        }

        $url = $salesforceDetails->getAccessTokenEndpoint();
        $header = array('Content-Type: application/x-www-form-urlencoded');
        
        $postFields = 'grant_type=refresh_token' .
            '&client_id=' . urlencode($salesforceDetails->getConsumerKey()) .
            '&client_secret=' . urlencode($salesforceDetails->getConsumerSecret()) .
            '&refresh_token=' . urlencode($refreshToken);
        
        $post = true;
        $accessToken = Authorization::moGetAccessToken($url, $header, $post, $postFields);
        
        // If refresh successful, update stored token
        if (!isset($accessToken->error) && isset($accessToken->access_token)) {
            $config = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
            $mo_salesforce_config = !empty($config['mo_sync_configuration']) ? json_decode($config['mo_sync_configuration'], true) : array();
            
            $mo_salesforce_config['mo_salesforce_access_token'] = $accessToken->access_token;
            if (isset($accessToken->instance_url)) {
                $mo_salesforce_config['mo_salesforce_instance_url'] = $accessToken->instance_url;
            }
            // Refresh token might be included in response, update if present
            if (isset($accessToken->refresh_token)) {
                $mo_salesforce_config['mo_salesforce_refresh_token'] = $accessToken->refresh_token;
            }
            
            $database_name = '#__miniorange_user_sync_config';
            $updatefieldsarray = array(
                'mo_sync_configuration' => json_encode($mo_salesforce_config),
            );
            MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
        }
        
        return $accessToken;
    }

    /**
     * Check if API response indicates expired session and refresh token if needed
     */
    private static function checkAndRefreshToken($response, $instanceUrl, &$accessToken)
    {
        // Check if response indicates expired session
        $isExpired = false;
        
        if (is_string($response)) {
            $responseData = json_decode($response, true);
        } else {
            $responseData = $response;
        }
        
        if (isset($responseData['error'])) {
            $errorMsg = isset($responseData['error_description']) ? $responseData['error_description'] : 
                       (isset($responseData['message']) ? $responseData['message'] : '');
            if (stripos($errorMsg, 'Session expired') !== false || 
                stripos($errorMsg, 'invalid') !== false ||
                stripos($responseData['error'], 'INVALID_SESSION_ID') !== false) {
                $isExpired = true;
            }
        }
        
        if (!$isExpired && is_array($responseData) && isset($responseData[0])) {
            if (isset($responseData[0]['errorCode'])) {
                $errorCode = $responseData[0]['errorCode'];
                $errorMsg = isset($responseData[0]['message']) ? $responseData[0]['message'] : '';
                if (stripos($errorCode, 'INVALID_SESSION') !== false || 
                    stripos($errorMsg, 'Session expired') !== false ||
                    stripos($errorMsg, 'invalid') !== false) {
                    $isExpired = true;
                }
            }
        }
        
        // If session expired, try to refresh token
        if ($isExpired) {
            $refreshedToken = self::refreshAccessToken();
            if (!isset($refreshedToken->error) && isset($refreshedToken->access_token)) {
                // Update access token object
                $accessToken->access_token = $refreshedToken->access_token;
                if (isset($refreshedToken->instance_url)) {
                    // Update instance URL if provided
                    return true; // Token refreshed successfully
                }
                return true;
            }
        }
        
        return false; // No refresh needed or refresh failed
    }

    /**
     * Get user details for test connection
     */
    public static function getUserDetails($username)
    {
        $salesforceDetails = new Salesforce();
        
        // For test connection, we need username and password
        // The username parameter here is the Salesforce username/email
        // We'll need to get password from config or use OAuth token if available
        
        // Try to get access token using username-password flow
        // Note: In production, you might want to store credentials securely
        // For now, we'll attempt to get token and then fetch user details
        
        $config = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
        $mo_salesforce_config = !empty($config['mo_sync_configuration']) ? json_decode($config['mo_sync_configuration'], true) : "";
        
        // Check if we have stored access token and instance URL
        $storedToken = isset($mo_salesforce_config['mo_salesforce_access_token']) ? $mo_salesforce_config['mo_salesforce_access_token'] : null;
        $storedInstanceUrl = isset($mo_salesforce_config['mo_salesforce_instance_url']) ? $mo_salesforce_config['mo_salesforce_instance_url'] : null;
        
        if ($storedToken && $storedInstanceUrl) {
            // Use stored token
            $accessToken = new stdClass();
            $accessToken->access_token = $storedToken;
            $salesforceDetails->setInstanceUrl($storedInstanceUrl);
        } else {
            // Try to get new token (this requires username/password in config)
            // For test connection, we'll return an error asking for credentials
            $error = array();
            $error['error'] = 'authentication_required';
            $error['error_description'] = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_SAVE_CONFIG_FIRST');
            $error['configuration_status'] = 'error';
            return $error;
        }

        if (isset($accessToken->error)) {
            $error = array($accessToken);
            $error['configuration_status'] = 'error';
            return $error;
        }

        if (isset($accessToken->access_token)) {
            // Query user by username/email
            $instanceUrl = $salesforceDetails->getInstanceUrl();
            if (empty($instanceUrl)) {
                $instanceUrl = $storedInstanceUrl;
            }
            
            // Get detected API version, but we'll try multiple versions for robustness
            $detectedApiVersion = self::getApiVersion($instanceUrl, $accessToken);
            
            // Escape the username/email for SOQL query to prevent injection
            $escapedUsername = str_replace("'", "\\'", $username);
            
            // Build list of API versions to try, prioritizing detected version and v61.0
            $versionsToTry = [];
            if (!empty($detectedApiVersion)) {
                $versionsToTry[] = $detectedApiVersion;
            }
            if ($detectedApiVersion != 'v61.0') {
                $versionsToTry[] = 'v61.0';
            }
            $fallbackVersions = ['v60.0', 'v59.0', 'v58.0', 'v57.0', 'v56.0', 'v55.0'];
            foreach ($fallbackVersions as $version) {
                if (!in_array($version, $versionsToTry)) {
                    $versionsToTry[] = $version;
                }
            }
            $versionsToTry = array_values(array_unique($versionsToTry));
            
            $userDetails = null;
            $lastError = null;
            $success = false;
            
            // Try each API version until one works
            foreach ($versionsToTry as $tryVersion) {
                $userQueryUrl = $instanceUrl . '/services/data/' . $tryVersion . '/query/?q=' . urlencode("SELECT Id,Username,Email,FirstName,LastName,Name FROM User WHERE Username='" . $escapedUsername . "' OR Email='" . $escapedUsername . "' LIMIT 1");
                
                $rawResponse = Authorization::moGetAllUsers($userQueryUrl, $accessToken);
                $userDetails = json_decode($rawResponse, true);
                
                // Check for Salesforce API errors
                $hasError = false;
                $isResourceNotFound = false;
                
                if (isset($userDetails['error'])) {
                    $hasError = true;
                    $errorMsg = isset($userDetails['error_description']) ? $userDetails['error_description'] : 
                               (isset($userDetails['message']) ? $userDetails['message'] : '');
                    if (stripos($errorMsg, 'does not exist') !== false || 
                        stripos($errorMsg, 'requested resource') !== false ||
                        stripos($userDetails['error'], 'NOT_FOUND') !== false) {
                        $isResourceNotFound = true;
                    }
                }
                
                if (!$hasError && is_array($userDetails) && isset($userDetails[0]) && isset($userDetails[0]['errorCode'])) {
                    $hasError = true;
                    $errorMsg = isset($userDetails[0]['message']) ? $userDetails[0]['message'] : '';
                    $errorCode = $userDetails[0]['errorCode'];
                    if (stripos($errorCode, 'NOT_FOUND') !== false || 
                        stripos($errorMsg, 'does not exist') !== false ||
                        stripos($errorMsg, 'requested resource') !== false) {
                        $isResourceNotFound = true;
                    }
                }
                
                if ($hasError) {
                    $lastError = $userDetails;
                    if ($isResourceNotFound) {
                        continue; // Try next version
                    } else {
                        break; // Other error, stop trying
                    }
                }
                
                // Success - we got a valid response
                if (isset($userDetails['records'])) {
                    $success = true;
                    break;
                }
            }
            
            // Handle errors
            if (!$success && $lastError) {
                if (isset($lastError['error'])) {
                    return $lastError;
                }
                if (is_array($lastError) && isset($lastError[0]) && isset($lastError[0]['errorCode'])) {
                    $error = array();
                    $error['error'] = $lastError[0]['errorCode'];
                    $error['error_description'] = isset($lastError[0]['message']) ? $lastError[0]['message'] : Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_UNKNOWN_API_ERROR');
                    $error['configuration_status'] = 'error';
                    return $error;
                }
            }
            
            if (isset($userDetails['records']) && !empty($userDetails['records'])) {
                $userInfo = $userDetails['records'][0];
                $userInfo['configuration_status'] = 'successful';
                return array($userInfo);
            } else {
                $error = array();
                $error['error'] = 'user_not_found';
                $error['error_description'] = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_USER_NOT_FOUND') . ': ' . $username;
                $error['configuration_status'] = 'error';
                return $error;
            }
        } else {
            $error = array($accessToken);
            $error['configuration_status'] = 'error';
            return $error;
        }
    }

    /**
     * Retrieve all users from Salesforce
     */
    public static function getAllUsers()
    {
        $salesforceDetails = new Salesforce();
        
        $config = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
        $mo_salesforce_config = !empty($config['mo_sync_configuration']) ? json_decode($config['mo_sync_configuration'], true) : "";
        
        $storedToken = isset($mo_salesforce_config['mo_salesforce_access_token']) ? $mo_salesforce_config['mo_salesforce_access_token'] : null;
        $storedInstanceUrl = isset($mo_salesforce_config['mo_salesforce_instance_url']) ? $mo_salesforce_config['mo_salesforce_instance_url'] : null;
        
        if (!$storedToken || !$storedInstanceUrl) {
            return null;
        }

        $accessToken = new stdClass();
        $accessToken->access_token = $storedToken;
        $salesforceDetails->setInstanceUrl($storedInstanceUrl);
        
        $instanceUrl = $salesforceDetails->getInstanceUrl();
        
        // Get detected API version, but try multiple versions for robustness
        $detectedApiVersion = self::getApiVersion($instanceUrl, $accessToken);
        
        // Build list of API versions to try
        $versionsToTry = [];
        if (!empty($detectedApiVersion)) {
            $versionsToTry[] = $detectedApiVersion;
        }
        if ($detectedApiVersion != 'v61.0') {
            $versionsToTry[] = 'v61.0';
        }
        $fallbackVersions = ['v60.0', 'v59.0', 'v58.0', 'v57.0', 'v56.0', 'v55.0'];
        foreach ($fallbackVersions as $version) {
            if (!in_array($version, $versionsToTry)) {
                $versionsToTry[] = $version;
            }
        }
        $versionsToTry = array_values(array_unique($versionsToTry));
        
        $users = null;
        $success = false;
        
        // Try each API version until one works
        foreach ($versionsToTry as $tryVersion) {
            $allUsersUrl = $instanceUrl . '/services/data/' . $tryVersion . '/query/?q=SELECT+Id,Username,Email,FirstName,LastName,Name+FROM+User';
            
            $rawResponse = Authorization::moGetAllUsers($allUsersUrl, $accessToken);
            $users = json_decode($rawResponse, true);
            
            // Check for errors
            $hasError = false;
            $isResourceNotFound = false;
            
            if (isset($users['error'])) {
                $hasError = true;
                $errorMsg = isset($users['error_description']) ? $users['error_description'] : 
                           (isset($users['message']) ? $users['message'] : '');
                if (stripos($errorMsg, 'does not exist') !== false || 
                    stripos($errorMsg, 'requested resource') !== false ||
                    stripos($users['error'], 'NOT_FOUND') !== false) {
                    $isResourceNotFound = true;
                }
            }
            
            if (!$hasError && is_array($users) && isset($users[0]) && isset($users[0]['errorCode'])) {
                $hasError = true;
                $errorMsg = isset($users[0]['message']) ? $users[0]['message'] : '';
                $errorCode = $users[0]['errorCode'];
                if (stripos($errorCode, 'NOT_FOUND') !== false || 
                    stripos($errorMsg, 'does not exist') !== false ||
                    stripos($errorMsg, 'requested resource') !== false) {
                    $isResourceNotFound = true;
                }
            }
            
            if ($hasError && $isResourceNotFound) {
                continue; // Try next version
            }
            
            // Success - we got a valid response (even if empty records)
            if (isset($users['records'])) {
                $success = true;
                break;
            }
        }
        
        if (isset($users['records']) && !empty($users['records'])) {
            self::moSalesforceTestAttrMappingConfig("", $users['records']);
        }
        
        $AttributeName = Factory::getApplication()->get('UserList');
        $database_name = '#__miniorange_sync_to_joomla';
        $updatefieldsarray = array(
            'moUserList' => json_encode($AttributeName),
        );
        MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
        
        return $users;
    }

    /**
     * Map Salesforce user attributes for test configuration
     */
    public static function moSalesforceTestAttrMappingConfig($nestedprefix, $resourceOwnerDetails)
    {
        $salesforce_user_name = "";
        $salesforce_user_id = "";
        $salesforce_user_email = "";

        foreach ($resourceOwnerDetails as $user) {
            if (is_array($user)) {
                $salesforce_user_id = isset($user['Id']) ? trim($user['Id']) : "";
                $salesforce_user_name = isset($user['Name']) ? trim($user['Name']) : (isset($user['FirstName']) && isset($user['LastName']) ? trim($user['FirstName'] . ' ' . $user['LastName']) : "");
                $salesforce_user_email = isset($user['Email']) ? trim($user['Email']) : "";
                $salesforce_user_username = isset($user['Username']) ? trim($user['Username']) : "";

                if ($salesforce_user_id != "" && ($salesforce_user_name != "" || $salesforce_user_email != "")) {
                    $users = array();
                    array_push($users, array(
                        'Name' => $salesforce_user_name,
                        'Id' => $salesforce_user_id,
                        'Email' => $salesforce_user_email,
                        'Username' => $salesforce_user_username
                    ));
                    $getAttributeName = Factory::getApplication()->get('UserList');
                    if (!is_array($getAttributeName)) {
                        $getAttributeName = array();
                    }
                    array_push($getAttributeName, $users);
                    Factory::getApplication()->set('UserList', $getAttributeName);
                }
            }
        }
    }

    /**
     * Get all available profiles from Salesforce
     */
    public static function getAllProfiles()
    {
        $salesforceDetails = new Salesforce();
        
        $config = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
        $mo_salesforce_config = !empty($config['mo_sync_configuration']) ? json_decode($config['mo_sync_configuration'], true) : "";
        
        $storedToken = isset($mo_salesforce_config['mo_salesforce_access_token']) ? $mo_salesforce_config['mo_salesforce_access_token'] : null;
        $storedInstanceUrl = isset($mo_salesforce_config['mo_salesforce_instance_url']) ? $mo_salesforce_config['mo_salesforce_instance_url'] : null;
        
        if (!$storedToken || !$storedInstanceUrl) {
            return array('error' => Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_CONFIGURE_AUTHENTICATE_FIRST'));
        }

        $accessToken = new stdClass();
        $accessToken->access_token = $storedToken;
        $salesforceDetails->setInstanceUrl($storedInstanceUrl);
        
        $instanceUrl = $salesforceDetails->getInstanceUrl();
        
        // Get detected API version, but try multiple versions for robustness
        $detectedApiVersion = self::getApiVersion($instanceUrl, $accessToken);
        
        // Build list of API versions to try
        $versionsToTry = [];
        if (!empty($detectedApiVersion)) {
            $versionsToTry[] = $detectedApiVersion;
        }
        if ($detectedApiVersion != 'v61.0') {
            $versionsToTry[] = 'v61.0';
        }
        $fallbackVersions = ['v60.0', 'v59.0', 'v58.0', 'v57.0', 'v56.0', 'v55.0'];
        foreach ($fallbackVersions as $version) {
            if (!in_array($version, $versionsToTry)) {
                $versionsToTry[] = $version;
            }
        }
        $versionsToTry = array_values(array_unique($versionsToTry));
        
        $profiles = null;
        $success = false;
        
        // Try each API version until one works
        foreach ($versionsToTry as $tryVersion) {
            // Query all profiles - try without UserType filter first, then with it
            $queries = [
                "SELECT Id, Name FROM Profile ORDER BY Name",
                "SELECT Id, Name FROM Profile WHERE UserType='Standard' ORDER BY Name"
            ];
            
            foreach ($queries as $query) {
                $profilesQueryUrl = $instanceUrl . '/services/data/' . $tryVersion . '/query/?q=' . urlencode($query);
                
                $profilesResponse = Authorization::moGetAllUsers($profilesQueryUrl, $accessToken);
                $profilesData = json_decode($profilesResponse, true);
                
                // Check for expired session and refresh token if needed
                $tokenRefreshed = self::checkAndRefreshToken($profilesData, $instanceUrl, $accessToken);
                if ($tokenRefreshed) {
                    // Retry the request with refreshed token
                    $profilesResponse = Authorization::moGetAllUsers($profilesQueryUrl, $accessToken);
                    $profilesData = json_decode($profilesResponse, true);
                }
                
                // Check for errors
                $hasError = false;
                if (isset($profilesData['error'])) {
                    $hasError = true;
                }
                if (!$hasError && is_array($profilesData) && isset($profilesData[0]) && isset($profilesData[0]['errorCode'])) {
                    $hasError = true;
                }
                
                if ($hasError) {
                    continue; // Try next query or version
                }
                
                // Success - we got profiles
                if (isset($profilesData['records']) && !empty($profilesData['records'])) {
                    $profiles = $profilesData['records'];
                    $success = true;
                    break 2; // Break out of both loops
                }
            }
        }
        
        if ($success && !empty($profiles)) {
            return $profiles;
        }
        
        // Return error message if no profiles found
        return array('error' => Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_UNABLE_TO_RETRIEVE_PROFILES'));
    }

    /**
     * Create user in Salesforce from Joomla user data
     */
    public static function createUserInSalesforce($username)
    {
        $salesforceDetails = new Salesforce();
        
        $config = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
        $mo_salesforce_config = !empty($config['mo_sync_configuration']) ? json_decode($config['mo_sync_configuration'], true) : "";
        
        $storedToken = isset($mo_salesforce_config['mo_salesforce_access_token']) ? $mo_salesforce_config['mo_salesforce_access_token'] : null;
        $storedInstanceUrl = isset($mo_salesforce_config['mo_salesforce_instance_url']) ? $mo_salesforce_config['mo_salesforce_instance_url'] : null;
        
        if (!$storedToken || !$storedInstanceUrl) {
            $error = new stdClass();
            $error->error = 'authentication_required';
            $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_SAVE_CONFIG_FIRST');
            return $error;
        }

        // Get Joomla user details
        $db = Factory::getDBO();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__users')
            ->where($db->quoteName('email') . ' = ' . $db->quote($username) . ' OR ' . $db->quoteName('username') . ' = ' . $db->quote($username));
        $db->setQuery($query);
        $joomlaUser = $db->loadAssoc();

        if (!$joomlaUser) {
            $error = new stdClass();
            $error->error = 'user_not_found';
            $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_USER_NOT_FOUND_JOOMLA');
            return $error;
        }

        // Check if user already exists in Salesforce
        $accessToken = new stdClass();
        $accessToken->access_token = $storedToken;
        $salesforceDetails->setInstanceUrl($storedInstanceUrl);
        
        $instanceUrl = $salesforceDetails->getInstanceUrl();
        
        // Get detected API version, but we'll try multiple versions for robustness
        $detectedApiVersion = self::getApiVersion($instanceUrl, $accessToken);
        
        // Build list of API versions to try
        $versionsToTry = [];
        if (!empty($detectedApiVersion)) {
            $versionsToTry[] = $detectedApiVersion;
        }
        if ($detectedApiVersion != 'v61.0') {
            $versionsToTry[] = 'v61.0';
        }
        $fallbackVersions = ['v60.0', 'v59.0', 'v58.0', 'v57.0', 'v56.0', 'v55.0'];
        foreach ($fallbackVersions as $version) {
            if (!in_array($version, $versionsToTry)) {
                $versionsToTry[] = $version;
            }
        }
        $versionsToTry = array_values(array_unique($versionsToTry));
        
        // Check if user exists using multiple API versions
        $existingUser = null;
        $workingApiVersion = null;
        
        foreach ($versionsToTry as $tryVersion) {
            $checkUserUrl = $instanceUrl . '/services/data/' . $tryVersion . '/query/?q=' . urlencode("SELECT Id,Username,Email FROM User WHERE Username='" . $joomlaUser['email'] . "' OR Email='" . $joomlaUser['email'] . "' LIMIT 1");
            
            $existingUserResponse = Authorization::moGetAllUsers($checkUserUrl, $accessToken);
            $existingUser = json_decode($existingUserResponse, true);
            
            // Check for expired session and refresh token if needed
            $tokenRefreshed = self::checkAndRefreshToken($existingUser, $instanceUrl, $accessToken);
            if ($tokenRefreshed) {
                // Retry the request with refreshed token
                $existingUserResponse = Authorization::moGetAllUsers($checkUserUrl, $accessToken);
                $existingUser = json_decode($existingUserResponse, true);
            }
            
            // Check for errors
            $hasError = false;
            $isResourceNotFound = false;
            
            if (isset($existingUser['error'])) {
                $hasError = true;
                $errorMsg = isset($existingUser['error_description']) ? $existingUser['error_description'] : '';
                if (stripos($errorMsg, 'does not exist') !== false || 
                    stripos($errorMsg, 'requested resource') !== false) {
                    $isResourceNotFound = true;
                }
            }
            
            if (!$hasError && is_array($existingUser) && isset($existingUser[0]) && isset($existingUser[0]['errorCode'])) {
                $hasError = true;
                $errorMsg = isset($existingUser[0]['message']) ? $existingUser[0]['message'] : '';
                if (stripos($errorMsg, 'does not exist') !== false || 
                    stripos($errorMsg, 'requested resource') !== false) {
                    $isResourceNotFound = true;
                }
            }
            
            if ($hasError && $isResourceNotFound) {
                continue; // Try next API version
            }
            
            // Success - we got a valid response
            if (isset($existingUser['records'])) {
                $workingApiVersion = $tryVersion;
                break;
            }
        }
        
        if (isset($existingUser['records']) && !empty($existingUser['records'])) {
            $error = new stdClass();
            $error->error = 'user_exists';
            $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_USER_ALREADY_EXISTS') . ': ' . $existingUser['records'][0]['Username'];
            return $error;
        }
        
        // If no working version found, use detected version as fallback
        if (!$workingApiVersion) {
            $workingApiVersion = !empty($detectedApiVersion) ? $detectedApiVersion : 'v61.0';
        }

        // Prepare user data for Salesforce
        // Note: Creating users in Salesforce requires specific fields and a valid ProfileId
        // This is a basic implementation that may need customization based on your Salesforce org
        
        // Split name into FirstName and LastName
        $nameParts = explode(' ', $joomlaUser['name'], 2);
        $firstName = isset($nameParts[0]) ? $nameParts[0] : $joomlaUser['username'];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : $joomlaUser['username'];
        
        // Generate an alias (max 8 characters)
        $alias = substr($joomlaUser['username'], 0, 8);
        
        // Get ProfileId from configuration, or fallback to default
        $profileId = isset($mo_salesforce_config['mo_salesforce_profile_id']) ? $mo_salesforce_config['mo_salesforce_profile_id'] : null;
        
        if (empty($profileId)) {
            // Fallback: Get a valid ProfileId from Salesforce if not configured
            // Try multiple API versions to get profile
            $profileFound = false;
            foreach ($versionsToTry as $tryVersion) {
                $profileQueryUrl = $instanceUrl . '/services/data/' . $tryVersion . '/query/?q=' . urlencode("SELECT Id FROM Profile WHERE Name='Standard User' LIMIT 1");
                $profileResponse = Authorization::moGetAllUsers($profileQueryUrl, $accessToken);
                $profileData = json_decode($profileResponse, true);
                
                // Check for errors
                $hasError = false;
                if (isset($profileData['error']) || (is_array($profileData) && isset($profileData[0]['errorCode']))) {
                    $hasError = true;
                }
                
                if (!$hasError && isset($profileData['records']) && !empty($profileData['records'])) {
                    $profileId = $profileData['records'][0]['Id'];
                    $profileFound = true;
                    break;
                }
            }
            
            if (!$profileFound) {
                // Try to get any available profile
                foreach ($versionsToTry as $tryVersion) {
                    $profileQueryUrl = $instanceUrl . '/services/data/' . $tryVersion . '/query/?q=' . urlencode("SELECT Id FROM Profile WHERE UserType='Standard' LIMIT 1");
                    $profileResponse = Authorization::moGetAllUsers($profileQueryUrl, $accessToken);
                    $profileData = json_decode($profileResponse, true);
                    
                    $hasError = false;
                    if (isset($profileData['error']) || (is_array($profileData) && isset($profileData[0]['errorCode']))) {
                        $hasError = true;
                    }
                    
                    if (!$hasError && isset($profileData['records']) && !empty($profileData['records'])) {
                        $profileId = $profileData['records'][0]['Id'];
                        $profileFound = true;
                        break;
                    }
                }
            }
            
            if (!$profileFound || empty($profileId)) {
                $error = new stdClass();
                $error->error = 'profile_not_found';
                $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_NO_VALID_PROFILE_FOUND');
                return $error;
            }
        }
        
        // Create user object with required Salesforce fields
        $userObject = [
            'Username' => $joomlaUser['email'], // Salesforce usernames must be in email format and globally unique
            'Email' => $joomlaUser['email'],
            'LastName' => $lastName,
            'FirstName' => $firstName,
            'Alias' => $alias,
            'TimeZoneSidKey' => 'America/Los_Angeles', // Default timezone, should be configurable
            'LocaleSidKey' => 'en_US', // Default locale, should be configurable
            'EmailEncodingKey' => 'UTF-8',
            'ProfileId' => $profileId,
            'LanguageLocaleKey' => 'en_US' // Default language, should be configurable
        ];

        $userObjectJson = json_encode($userObject);
        
        // Create user in Salesforce - try multiple API versions
        $result = null;
        $lastError = null;
        $success = false;
        
        foreach ($versionsToTry as $tryVersion) {
            $createUserUrl = $instanceUrl . '/services/data/' . $tryVersion . '/sobjects/User/';
            $response = Authorization::createUserInProvider($createUserUrl, $accessToken, $userObjectJson);
            
            $result = json_decode($response);
            
            // Check for expired session and refresh token if needed
            if (is_object($result) && isset($result->error)) {
                $tokenRefreshed = self::checkAndRefreshToken($result, $instanceUrl, $accessToken);
                if ($tokenRefreshed) {
                    // Retry the request with refreshed token
                    $response = Authorization::createUserInProvider($createUserUrl, $accessToken, $userObjectJson);
                    $result = json_decode($response);
                }
            }
            
            // Check if user creation was successful
            if (isset($result->success) && $result->success === true) {
                $success = true;
                break;
            }
            
            // Check for "resource does not exist" error
            $isResourceNotFound = false;
            if (isset($result->error)) {
                $errorMsg = isset($result->error_description) ? $result->error_description : '';
                if (stripos($errorMsg, 'does not exist') !== false || 
                    stripos($errorMsg, 'requested resource') !== false ||
                    stripos($result->error, 'NOT_FOUND') !== false) {
                    $isResourceNotFound = true;
                }
            }
            
            if (is_array($result) && isset($result[0]->errorCode)) {
                $errorMsg = isset($result[0]->message) ? $result[0]->message : '';
                if (stripos($errorMsg, 'does not exist') !== false || 
                    stripos($errorMsg, 'requested resource') !== false ||
                    stripos($result[0]->errorCode, 'NOT_FOUND') !== false) {
                    $isResourceNotFound = true;
                }
            }
            
            if ($isResourceNotFound) {
                $lastError = $result;
                continue; // Try next API version
            } else {
                // Other error, return it
                break;
            }
        }
        
        // Return result
        if ($success) {
            return $result;
        } elseif (isset($result->error)) {
            return $result;
        } elseif (is_array($result) && isset($result[0]->errorCode)) {
            // Salesforce returns errors as an array
            $error = new stdClass();
            $error->error = $result[0]->errorCode;
            $error->error_description = isset($result[0]->message) ? $result[0]->message : Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_UNKNOWN_ERROR');
            return $error;
        } else {
            $error = new stdClass();
            $error->error = 'unknown_error';
            $error->error_description = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_FAILED_TO_CREATE_USER') . ' ' . Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_TRIED_API_VERSIONS') . ': ' . implode(', ', $versionsToTry) . '. ' . Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_RESPONSE') . ': ' . json_encode($result);
            return $error;
        }
    }

    /**
     * Get the latest API version from Salesforce instance
     */
    private static function getApiVersion($instanceUrl, $accessToken)
    {
        // Try to get available API versions
        $versionsUrl = $instanceUrl . '/services/data/';
        $versionsResponse = Authorization::moGetAllUsers($versionsUrl, $accessToken);
        $versionsData = json_decode($versionsResponse, true);
        
        if (isset($versionsData) && is_array($versionsData)) {
            // Find the latest version, prefer v61.0 if available
            $latestVersion = 'v61.0'; // Default fallback
            foreach ($versionsData as $version) {
                if (isset($version['version']) && isset($version['label'])) {
                    // Extract version number (e.g., "61.0" from "v61.0")
                    $versionNum = str_replace('v', '', $version['version']);
                    $latestVersionNum = str_replace('v', '', $latestVersion);
                    if (version_compare($versionNum, $latestVersionNum, '>')) {
                        $latestVersion = $version['version'];
                    }
                }
            }
            return $latestVersion;
        }
        
        // Fallback: try common versions, prioritizing v61.0
        $commonVersions = ['v61.0', 'v60.0', 'v59.0', 'v58.0', 'v57.0', 'v56.0', 'v55.0'];
        foreach ($commonVersions as $version) {
            $testUrl = $instanceUrl . '/services/data/' . $version . '/sobjects/User/describe';
            $testResponse = Authorization::moGetAllUsers($testUrl, $accessToken);
            $testData = json_decode($testResponse, true);
            if (isset($testData['objectType']) || (isset($testData['fields']) && is_array($testData['fields']))) {
                return $version;
            }
        }
        
        return 'v61.0'; // Default fallback
    }

    /**
     * Create user in Joomla from Salesforce user data
     */
    public static function createUserInJoomla($username)
    {
        $salesforceDetails = new Salesforce();
        
        $config = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
        $mo_salesforce_config = !empty($config['mo_sync_configuration']) ? json_decode($config['mo_sync_configuration'], true) : "";
        
        $storedToken = isset($mo_salesforce_config['mo_salesforce_access_token']) ? $mo_salesforce_config['mo_salesforce_access_token'] : null;
        $storedInstanceUrl = isset($mo_salesforce_config['mo_salesforce_instance_url']) ? $mo_salesforce_config['mo_salesforce_instance_url'] : null;
        
        if (!$storedToken || !$storedInstanceUrl) {
            $error = array();
            $error['error'] = 'authentication_required';
            $error['error_description'] = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_SAVE_CONFIG_FIRST');
            return $error;
        }

        $accessToken = new stdClass();
        $accessToken->access_token = $storedToken;
        $salesforceDetails->setInstanceUrl($storedInstanceUrl);
        
        // Get username/email to sync
        $usernameToSync = '';
        if (is_array($username) && isset($username[0])) {
            if (is_array($username[0])) {
                $usernameToSync = isset($username[0]['Email']) ? $username[0]['Email'] : (isset($username[0]['Username']) ? $username[0]['Username'] : (isset($username[0]['Name']) ? $username[0]['Name'] : ''));
            } else {
                $usernameToSync = $username[0];
            }
        } else {
            $usernameToSync = is_string($username) ? $username : '';
        }

        if (empty($usernameToSync)) {
            return 'EMPTY_USERNAME';
        }

        // Check if user already exists in Joomla
        $db = Factory::getDBO();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__users')
            ->where($db->quoteName('email') . ' = ' . $db->quote($usernameToSync) . ' OR ' . $db->quoteName('username') . ' = ' . $db->quote($usernameToSync) . ' OR ' . $db->quoteName('name') . ' = ' . $db->quote($usernameToSync));
        $db->setQuery($query);
        $joomla_user = $db->loadAssoc();
        
        // Query user from Salesforce
        $instanceUrl = $salesforceDetails->getInstanceUrl();
        
        // Get the detected API version, but we'll try multiple versions for robustness
        $detectedApiVersion = self::getApiVersion($instanceUrl, $accessToken);
        
        // Escape the username/email for SOQL query to prevent injection
        $escapedUsername = str_replace("'", "\\'", $usernameToSync);
        
        // Build list of API versions to try, prioritizing detected version and v61.0
        $versionsToTry = [];
        
        // Add detected version first (if it's not already in the list)
        if (!empty($detectedApiVersion)) {
            $versionsToTry[] = $detectedApiVersion;
        }
        
        // Add v61.0 if not already added
        if ($detectedApiVersion != 'v61.0') {
            $versionsToTry[] = 'v61.0';
        }
        
        // Add other stable versions as fallbacks
        $fallbackVersions = ['v60.0', 'v59.0', 'v58.0', 'v57.0', 'v56.0', 'v55.0'];
        foreach ($fallbackVersions as $version) {
            if (!in_array($version, $versionsToTry)) {
                $versionsToTry[] = $version;
            }
        }
        
        // Remove duplicates while preserving order
        $versionsToTry = array_values(array_unique($versionsToTry));
        
        $userDetailsResponse = null;
        $lastError = null;
        $lastApiVersion = null;
        $success = false;
        
        // Try each API version until one works
        foreach ($versionsToTry as $tryVersion) {
            $userQueryUrl = $instanceUrl . '/services/data/' . $tryVersion . '/query/?q=' . urlencode("SELECT Id,Username,Email,FirstName,LastName,Name FROM User WHERE Username='" . $escapedUsername . "' OR Email='" . $escapedUsername . "' LIMIT 1");
            
            $rawResponse = Authorization::moGetAllUsers($userQueryUrl, $accessToken);
            $userDetailsResponse = json_decode($rawResponse, true);
            
            // Check for expired session and refresh token if needed
            $tokenRefreshed = self::checkAndRefreshToken($userDetailsResponse, $instanceUrl, $accessToken);
            if ($tokenRefreshed) {
                // Retry the request with refreshed token
                $rawResponse = Authorization::moGetAllUsers($userQueryUrl, $accessToken);
                $userDetailsResponse = json_decode($rawResponse, true);
            }
            
            // Check for Salesforce API errors
            $hasError = false;
            $isResourceNotFound = false;
            $isSessionExpired = false;
            
            if (isset($userDetailsResponse['error'])) {
                $hasError = true;
                $errorMsg = isset($userDetailsResponse['error_description']) ? $userDetailsResponse['error_description'] : 
                           (isset($userDetailsResponse['message']) ? $userDetailsResponse['message'] : '');
                // Check if it's a "resource does not exist" error
                if (stripos($errorMsg, 'does not exist') !== false || 
                    stripos($errorMsg, 'requested resource') !== false ||
                    stripos($userDetailsResponse['error'], 'NOT_FOUND') !== false) {
                    $isResourceNotFound = true;
                }
                // Check if it's a session expired error
                if (stripos($errorMsg, 'Session expired') !== false || 
                    stripos($errorMsg, 'invalid') !== false ||
                    stripos($userDetailsResponse['error'], 'INVALID_SESSION_ID') !== false) {
                    $isSessionExpired = true;
                }
            }
            
            // Check if response is an array of errors
            if (!$hasError && is_array($userDetailsResponse) && isset($userDetailsResponse[0]) && isset($userDetailsResponse[0]['errorCode'])) {
                $hasError = true;
                $errorMsg = isset($userDetailsResponse[0]['message']) ? $userDetailsResponse[0]['message'] : '';
                $errorCode = $userDetailsResponse[0]['errorCode'];
                // Check if it's a "resource does not exist" error
                if (stripos($errorCode, 'NOT_FOUND') !== false || 
                    stripos($errorMsg, 'does not exist') !== false ||
                    stripos($errorMsg, 'requested resource') !== false) {
                    $isResourceNotFound = true;
                }
                // Check if it's a session expired error
                if (stripos($errorCode, 'INVALID_SESSION') !== false || 
                    stripos($errorMsg, 'Session expired') !== false ||
                    stripos($errorMsg, 'invalid') !== false) {
                    $isSessionExpired = true;
                }
            }
            
            // If we got an error
            if ($hasError) {
                $lastError = $userDetailsResponse;
                $lastApiVersion = $tryVersion;
                
                // If it's a session expired error and we haven't refreshed yet, try refreshing
                if ($isSessionExpired && !$tokenRefreshed) {
                    $tokenRefreshed = self::checkAndRefreshToken($userDetailsResponse, $instanceUrl, $accessToken);
                    if ($tokenRefreshed) {
                        // Retry the request with refreshed token
                        $rawResponse = Authorization::moGetAllUsers($userQueryUrl, $accessToken);
                        $userDetailsResponse = json_decode($rawResponse, true);
                        // Check again if it succeeded
                        if (isset($userDetailsResponse['records'])) {
                            $success = true;
                            break;
                        }
                        // If still error, continue to check error type
                        if (isset($userDetailsResponse['error']) || (is_array($userDetailsResponse) && isset($userDetailsResponse[0]['errorCode']))) {
                            $lastError = $userDetailsResponse;
                        }
                    }
                }
                
                // If it's a "resource not found" error, try next version
                if ($isResourceNotFound) {
                    continue; // Try next API version
                } else if (!$isSessionExpired || $tokenRefreshed) {
                    // Other error (or session expired but refresh failed), stop trying and return it
                    break;
                }
            }
            
            // Success - we got a valid response
            if (isset($userDetailsResponse['records'])) {
                $success = true;
                break; // Found working version
            }
        }
        
        // If we tried all versions and still have an error
        if (!$success && $lastError) {
            $errorCode = isset($lastError['error']) ? $lastError['error'] : 
                        (isset($lastError[0]['errorCode']) ? $lastError[0]['errorCode'] : 'unknown_error');
            $errorMessage = isset($lastError['error_description']) ? $lastError['error_description'] : 
                           (isset($lastError['message']) ? $lastError['message'] : 
                           (isset($lastError[0]['message']) ? $lastError[0]['message'] : 'Unknown Salesforce API error'));
            
            // Check if it's a session expired error
            $isSessionExpired = false;
            if (stripos($errorMessage, 'Session expired') !== false || 
                stripos($errorMessage, 'invalid') !== false ||
                stripos($errorCode, 'INVALID_SESSION_ID') !== false) {
                $isSessionExpired = true;
            }
            
            $error = array();
            $error['error'] = $errorCode;
            
            if ($isSessionExpired) {
                $error['error_description'] = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_SESSION_EXPIRED') . ' (' . Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_TRIED_API_VERSIONS') . ': ' . implode(', ', $versionsToTry) . ')';
            } else {
                $error['error_description'] = Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_API_ERROR') . ': ' . $errorMessage . ' (' . Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_TRIED_API_VERSIONS') . ': ' . implode(', ', $versionsToTry) . ')';
            }
            
            return $error;
        }
        
        // If no records found after trying all versions
        if (!$success || !isset($userDetailsResponse['records']) || empty($userDetailsResponse['records'])) {
            return 'USER_NOT_PRESENT';
        }
        
        $userDetails = $userDetailsResponse['records'][0];
        
        // Map user details using configured mappings
        $moUserDetails = array();
        $moUserDetails['username'] = isset($userDetails[$salesforceDetails->getMapUsername()]) ? $userDetails[$salesforceDetails->getMapUsername()] : (isset($userDetails['Username']) ? $userDetails['Username'] : '');
        $moUserDetails['email'] = isset($userDetails[$salesforceDetails->getMapEmail()]) ? $userDetails[$salesforceDetails->getMapEmail()] : (isset($userDetails['Email']) ? $userDetails['Email'] : '');
        $moUserDetails['name'] = isset($userDetails[$salesforceDetails->getMapName()]) ? $userDetails[$salesforceDetails->getMapName()] : (isset($userDetails['Name']) ? $userDetails['Name'] : '');
        
        // Fallbacks if mappings are not set
        if (empty($moUserDetails['username'])) {
            $moUserDetails['username'] = isset($userDetails['Username']) ? $userDetails['Username'] : '';
        }
        if (empty($moUserDetails['email'])) {
            $moUserDetails['email'] = isset($userDetails['Email']) ? $userDetails['Email'] : '';
        }
        if (empty($moUserDetails['name'])) {
            $moUserDetails['name'] = isset($userDetails['Name']) ? $userDetails['Name'] : (isset($userDetails['FirstName']) && isset($userDetails['LastName']) ? $userDetails['FirstName'] . ' ' . $userDetails['LastName'] : '');
        }
        
        $moUserDetails['enabled'] = 0; // Enabled by default
        
        // Validation
        if (empty(trim($moUserDetails['username']))) {
            return 'EMPTY_USERNAME';
        }
        if (empty(trim($moUserDetails['email']))) {
            return 'EMPTY_EMAIL';
        }
        if (empty(trim($moUserDetails['name']))) {
            return 'EMPTY_NAME';
        }
        if (!filter_var($moUserDetails['email'], FILTER_VALIDATE_EMAIL)) {
            return 'INVALID_EMAIL_ATTRIBUTE';
        }
        
        // Create user in Joomla if not present
        if (!$joomla_user) {
            Authorization::moCreateUserInJoomla($moUserDetails, 'Salesforce');
            return 'USER_CREATED';
        } else {
            return 'USER_ALREADY_PRESENT';
        }
    }
}

