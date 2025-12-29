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
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\User;
$userList = array();
Factory::getApplication()->set('moUserList', $userList);
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

class MoCognito
{
    private $region = "";
    private $userPoolId = "";
    private $adminUsername = "";
    private $accessKey = "";
    private $secretAccessKey = "";
    private $mapUsername = "";
    private $mapEmail = "";
    private $mapName = "";
    private array $moUserList = [];

    public function __construct()
    {
        $app = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
        $moCognitoAppAttr = !empty($app['mo_sync_configuration']) ? json_decode($app['mo_sync_configuration'], true) : "";

        $this->region = $moCognitoAppAttr['mo_aws_cognito_region'] ? $moCognitoAppAttr['mo_aws_cognito_region'] : "";
        $this->userPoolId = $moCognitoAppAttr['mo_aws_cognito_pool_id'] ? $moCognitoAppAttr['mo_aws_cognito_pool_id'] : "";
        $this->accessKey = $moCognitoAppAttr['mo_aws_cognito_access_key'] ?$moCognitoAppAttr['mo_aws_cognito_access_key'] : "";
        $this->secretAccessKey = $moCognitoAppAttr['mo_aws_cognito_secret_key'] ? $moCognitoAppAttr['mo_aws_cognito_secret_key'] : "";
        $this->adminUsername = $moCognitoAppAttr['mo_usersync_upn'] ? $moCognitoAppAttr['mo_usersync_upn'] : "";

        $mapDetails = MoUserSyncUtility::moGetDetails('#__miniorange_sync_to_joomla');
        $this->mapUsername = $mapDetails['moUsername'] ?? "";
        $this->mapEmail = $mapDetails['moEmail'] ?? "";
        $this->mapName = $mapDetails['moName'] ?? "";
        $this->moUserList = isset($mapDetails['moUserList']) && !empty($mapDetails['moUserList']) ? json_decode($mapDetails['moUserList'], true) : [];
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function getUserPoolId()
    {
        return $this->userPoolId;
    }

    public function getAdminUsername()
    {
        return $this->adminUsername;
    }

    public function getAccessKey()
    {
        return $this->accessKey;
    }

    public function getSecretAccessKey()
    {
        return $this->secretAccessKey;
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

    public function getUserList(){
        return $this->moUserList;
    }

    //Get user details from AWS
    public static function getCognitoUserDetails($username)
    {
        $cognito = new self();
        $userPoolId = $cognito->getUserPoolId();

        $response = $cognito->sendAwsRequest('AWSCognitoIdentityProviderService.AdminGetUser', [
            "UserPoolId" => $userPoolId,
            "Username" => $username
        ]);

        return $response;
    }

    //Retrive all users from AWS to joomla
    public static function getAllUsers() {
        $cognito = new self();
        $userPoolId = $cognito->getUserPoolId();
    
        $response = $cognito->sendAwsRequest('AWSCognitoIdentityProviderService.ListUsers', [
            "UserPoolId" => $userPoolId,
            "Limit" => 50
        ]);
    
        if (isset($response['Users'])) {
            $users = $response['Users'];
            $userList = [];
    
            foreach ($users as $user) {
                $email = '';
                $name = '';
    
                if (isset($user['Attributes'])) {
                    foreach ($user['Attributes'] as $attr) {
                        if ($attr['Name'] === 'email') {
                            $email = $attr['Value'] ?? '';
                        }
                        if ($attr['Name'] === 'name') {
                            $name = $attr['Value'] ?? '';
                        }
                    }
                }
    
                if (!empty($email)) {
                    $userList[] = [
                        'Email' => $email,
                        'Name' => $name ?: $email
                    ];
                }
            }
    
            MoUserSyncUtility::moUpdateQuery('#__miniorange_sync_to_joomla', [
                'moUserList' => json_encode($userList)
            ]);
    
            return $userList;
        }
        return [];
    }

    public static function sendAwsRequest($amzTarget, $postFields, $method = 'POST')
    {
        $cognitoDetails = new MoCognito();

        $accessKey = $cognitoDetails->getAccessKey();
        $secretKey = $cognitoDetails->getSecretAccessKey();
        $region = $cognitoDetails->getRegion();
        $userPoolId = $cognitoDetails->getUserPoolId();
        $service = 'cognito-idp';
        $host = "cognito-idp.$region.amazonaws.com";
        $endpoint = "https://$host/";

        $contentType = 'application/x-amz-json-1.1';
        $post_fields = json_encode($postFields);

        $t = time();
        $amzDate = gmdate('Ymd\THis\Z', $t);
        $dateStamp = gmdate('Ymd', $t);

        $canonicalUri = '/';
        $canonicalQueryString = '';
        $canonicalHeaders = "content-type:$contentType\nhost:$host\nx-amz-date:$amzDate\nx-amz-target:$amzTarget\n";
        $signedHeaders = 'content-type;host;x-amz-date;x-amz-target';
        $payloadHash = hash('sha256', $post_fields);
        $canonicalRequest = "$method\n$canonicalUri\n$canonicalQueryString\n$canonicalHeaders\n$signedHeaders\n$payloadHash";

        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "$dateStamp/$region/$service/aws4_request";
        $stringToSign = "$algorithm\n$amzDate\n$credentialScope\n" . hash('sha256', $canonicalRequest);
        $signingKey = self::getSignatureKey($secretKey, $dateStamp, $region, $service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorizationHeader = "$algorithm Credential=$accessKey/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";

        $headers = [
            "Content-Type: $contentType",
            "X-Amz-Date: $amzDate",
            "X-Amz-Target: $amzTarget",
            "Authorization: $authorizationHeader"
        ];

        $responseData = Authorization::createUserInAWSCognito($endpoint, $headers, $post_fields);

        if ($responseData['error']) {
            return ["error" => "cURL Error: " . $responseData['error']];
        }

        if ($responseData['httpCode'] !== 200) {
            return [
                "error" => "Failed to connect to AWS Cognito. HTTP Code: " . $responseData['httpCode'],
                "response" => $responseData['response']
            ];
        }

        $decodedResponse = json_decode($responseData['response'], true);

        // Format UserAttributes to associative array
        if (isset($decodedResponse['UserAttributes']) && is_array($decodedResponse['UserAttributes'])) {
            $attributes = [];
            array_map(function($obj) use (&$attributes) {
                $attributes[$obj['Name']] = $obj['Value'];
            }, $decodedResponse['UserAttributes']);

            $decodedResponse['UserAttributes'] = $attributes;
        }

        return $decodedResponse;
    }


    public static function getSignatureKey($key, $dateStamp, $regionName, $serviceName)
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $key, true);
        $kRegion = hash_hmac('sha256', $regionName, $kDate, true);
        $kService = hash_hmac('sha256', $serviceName, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        return $kSigning;
    }

    //create user from AWS to joomla
    public static function createUserInJoomla($username) {
        $cognitoDetails = new MoCognito();
        $accessKey = $cognitoDetails->getAccessKey();
        $secretKey = $cognitoDetails->getSecretAccessKey();
        $region = $cognitoDetails->getRegion();
        $userPoolId = $cognitoDetails->getUserPoolId();
        $amzTarget = 'AWSCognitoIdentityProviderService.AdminGetUser';
        if (isset($username['Email'])) {
            $post_fields = [
                'UserPoolId' => $userPoolId,
                'Username' => $username['Email']
            ];
        }
        else {
            return 'EMPTY_USERNAME';// Handle missing value gracefully
        }

        $userDetails = self::sendAwsRequest($amzTarget, $post_fields, $method = 'POST');
        $moUserDetails = array();

        $moUserDetails['username'] = MoCognito::getCognitoAttribute($userDetails, $cognitoDetails->getMapUsername());
        $moUserDetails['email'] = MoCognito::getCognitoAttribute($userDetails,$cognitoDetails->getMapEmail());
        $moUserDetails['name'] = MoCognito::getCognitoAttribute($userDetails, $cognitoDetails->getMapName());
        
        // Fallbacks if needed
        if (empty($moUserDetails['username'])) {
            $moUserDetails['username'] = $userDetails['Username'] ?? '';
        }
        $moUserDetails['enabled'] = ($userDetails['Enabled'] ?? false) ? 1 : 0; 
        $moUserDetails['block'] = ($moUserDetails['enabled']) ? 0 : 1; 
    
        // Validation
        if (empty(trim($moUserDetails['username']))) return 'EMPTY_USERNAME';
        if (empty(trim($moUserDetails['email']))) return 'EMPTY_EMAIL';
        if (empty(trim($moUserDetails['name']))) return 'EMPTY_NAME';
        
        if (!filter_var($moUserDetails['email'], FILTER_VALIDATE_EMAIL)) {
            return 'INVALID_EMAIL_ATTRIBUTE';
        }
    
        // Check Joomla user
        $db = Factory::getDBO();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__users')
            ->where(
                $db->quoteName('email') . ' = ' . $db->quote($moUserDetails['email']) .
                ' OR ' . $db->quoteName('username') . ' = ' . $db->quote($moUserDetails['username']) .
                ' OR ' . strtolower($db->quoteName('name')) . ' = ' . $db->quote(strtolower($moUserDetails['name']))
            );
        $db->setQuery($query);
        $joomla_user = $db->loadAssocList();
    
        if (!$joomla_user) {
            Authorization::moCreateUserInJoomla($moUserDetails, 'Cognito');
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__users'))
                ->set($db->quoteName('block') . ' = 0')
                ->where($db->quoteName('email') . ' = ' . $db->quote($moUserDetails['email']));
            $db->setQuery($query);
            $db->execute();

            return 'USER_CREATED';
        } else {
            return 'USER_ALREADY_PRESENT';
        }
    }
   
    public static function getCognitoAttribute($userDetails, $attributeName) {
        if (strpos($attributeName, '.') !== false) {
            $parts = explode('.', $attributeName);
            $value = $userDetails;
            foreach ($parts as $part) {
                if (!is_array($value) || !isset($value[$part])) {
                    return null;
                }
                $value = $value[$part];
            }
            return $value;
        }
    
        // Otherwise, assume attributeName is a direct key inside UserAttributes
        if (!isset($userDetails['UserAttributes']) || !is_array($userDetails['UserAttributes'])) {
            return null;
        }
    
        return $userDetails['UserAttributes'][$attributeName] ?? null;
    }
    
    
    //Create user from joomla To AWS
    public static function createCognitoUser($username)
    {   
        $cognitoDetails = new MoCognito();
        $userAttributes=[];
        $temporaryPassword=null;
        $accessKey = $cognitoDetails->getAccessKey();
        $secretKey = $cognitoDetails->getSecretAccessKey();
        $region = $cognitoDetails->getRegion();
        $userPoolId = $cognitoDetails->getUserPoolId();

        $amzTarget = 'AWSCognitoIdentityProviderService.AdminCreateUser';

        $post_data = [
            "UserPoolId" => $userPoolId,
            "Username" => $username,
            "UserAttributes" => $userAttributes,
            "MessageAction" => "SUPPRESS",  // Don't send default welcome email
        ];
        

        if ($temporaryPassword) {
            $post_data['TemporaryPassword'] = $temporaryPassword;
        }

        $response = self::sendAwsRequest($amzTarget, $post_data, $method = 'POST' );

        return $response;
    }
}
?>