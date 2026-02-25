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

defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Document\HtmlDocument;
$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_miniorange_usersync/assets/js/jquery.1.11.0.min.js');
$document->addScript(Uri::base() . 'components/com_miniorange_usersync/assets/js/countries.js');
$document->addScript(Uri::base() . 'components/com_miniorange_usersync/assets/js/utilityjs.js');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_usersync/assets/css/miniorange_user_sync.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_usersync/assets/css/miniorange_boot.css');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

$user_sync_active_tab = 'syncconfiguration';
$app = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$get = ($input && $input->get) ? $input->get->getArray() : [];
if(isset($get['tab-panel']) && !empty($get['tab-panel'])){
	$user_sync_active_tab = $get['tab-panel'];
}

$appParam = $input->get('app', '', 'STRING');


if (!empty($appParam)) {
    $pannel_display = 'display:none';
}else
	$pannel_display = 'display:block';

$mo_application_details = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
$configured_app = isset($mo_application_details['app_name']) && !empty($mo_application_details['app_name']) ? $mo_application_details['app_name'] : "";
?>
<div class="mo_boot_container-fluid mo_boot_my-4 tabcontent">
    <div class="mo_boot_row mo_boot_p-2 mo_usersync_title">
        <div class="mo_boot_col-lg-9 mo_boot_col-sm-6">
            <h2 class="mo_usersync_text_color"><?php echo Text::_('COM_MINIORANGE_USERSYNC_PLUGIN_TITLE');?></h4>
        </div>
        <div class="mo_boot_col-lg-1 mo_boot_col-sm-2">
            <a href="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_support&app=other')?>" class="btn mo_title_buttons"><?php echo Text::_('COM_MINIORANGE_TAB_SUPPORT');?></a>
        </div> 
		<div class="mo_boot_col-lg-2 mo_boot_col-sm-3">
            <a  href="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_license_plan&app=other')?>" class="btn mo_title_buttons"><?php echo Text::_('COM_MINIORANGE_TAB_LICENSING_PLAN');?></a>
        </div>
    </div>

    <div class="mo_boot_row" style="<?php echo $pannel_display;?>">
		<div class="mo_boot_col-sm-12">
        	<h1 class="mo_sync_heading mo_boot_py-2"><?php echo Text::_('COM_MINIORANGE_USERSYNC_CONFIGURE_PROVIDER');?></h1>
			<div class="mo_boot_row">
				<div class="mo_boot_col-sm-12">    
					<div class="mo-user-sync-tab-content mo_boot_mx-4">
        				<div class="mo_boot_mt-1 mo_usersync_parent">
							<a class="logo-user-sync-cstm mo_usersync_child mo_usersync_text_decoration" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app=Azure&sub_tab=config';?>">
								<img loading="lazy" width="90px" margin-top="0.9rem" src="<?php echo Uri::base().'components\com_miniorange_usersync\assets\images\azure.png';?>">
								<br>
								<h6 class="mo_usersync_titles">Azure</h6>
							</a>
					
							<a class="logo-user-sync-cstm mo_usersync_child mo_usersync_text_decoration" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app=Keycloak&sub_tab=config';?>">
								<img loading="lazy" width="90px" margin-top="0.9rem" src="<?php echo Uri::base().'components\com_miniorange_usersync\assets\images\keycloak.png';?>">
								<br>
								<h6 class="mo_usersync_titles">Keycloak</h6>
							</a>
						
							<a class="logo-user-sync-cstm mo_usersync_child mo_usersync_text_decoration" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app=Okta&sub_tab=config';?>">
								<img loading="lazy" width="90px" margin-top="0.9rem" src="<?php echo Uri::base().'components\com_miniorange_usersync\assets\images\okta.png';?>">
								<br>
								<h6 class="mo_usersync_titles">Okta</h6>
							</a>
							
							<a class="logo-user-sync-cstm mo_usersync_child mo_usersync_text_decoration mo_boot_mb-3" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app=AWS&sub_tab=config';?>">
								<img loading="lazy" width="90px" margin-top="0.9rem" src="<?php echo Uri::base().'components\com_miniorange_usersync\assets\images\cognito.png';?>">
								<br>
								<h6 class="mo_usersync_titles">AWS Cognito</h6>
							</a>

							<a class="logo-user-sync-cstm mo_usersync_child mo_usersync_text_decoration mo_boot_mb-3" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app=Salesforce&sub_tab=config';?>">
								<img loading="lazy" width="90px" margin-top="0.9rem" src="<?php echo Uri::base().'components\com_miniorange_usersync\assets\images\salesforce.png';?>">
								<br>
								<h6 class="mo_usersync_titles">Salesforce</h6>
							</a>

							<a class="logo-user-sync-cstm mo_usersync_child mo_usersync_text_decoration mo_boot_mb-3" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_support&app=other';?>">
								<img loading="lazy" width="90px" margin-top="0.9rem" src="<?php echo Uri::base().'components\com_miniorange_usersync\assets\images\logo.jpg';?>">
								<br>
								<h6 class="mo_usersync_titles">Custom Provider</h6>
							</a>
                    	</div>
    				</div>
       			</div>
			</div>
		</div>
	</div>	
      
    <div class="mo_boot_row">
        <div class="mo_boot_col-sm-12 mo_sync_tab" id="syncconfiguration" style="<?php echo (($user_sync_active_tab=='syncconfiguration')?'display:block;':'display:none;');?>">
            <?php mo_joomla_sync_configuration(); ?>
        </div>
	</div>
	<div class="mo_boot_row">
		<div class="mo_boot_col-sm-12 mo_sync_tab mo_boot_m-0 mo_boot_p-0" id="mo_support" style="<?php echo (($user_sync_active_tab=='mo_support')?'display:block;':'display:none;');?>">
		   	<?php mo_support(); ?>
		</div>
	</div>
	<div class="mo_boot_row">
		<div class="mo_boot_col-sm-12" id="provider_to_joomla" style="<?php echo (($user_sync_active_tab=='mo_license_plan')?'display:block;':'display:none;');?>">
		   	<?php mo_licensingplan(); ?>
		</div>
	</div>


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

<?php


function mo_support(){
	global $license_tab_link;

	$current_user = Factory::getUser();
	$customer_details = MoUserSyncUtility::moGetDetails('#__miniorange_usersync_customer');
	$admin_email = isset($customer_details['email']) ? $customer_details['email'] : '';
    if ($admin_email == '') $admin_email = $current_user->email;
	$admin_phone = $customer_details['admin_phone'];
?>

	<div class="mo_boot_row">
		<div class="mo_boot_col-sm-12">
        	<div class="mo_boot_col-sm-12 mo_boot_mt-4">
            	<h3 class="mo_sync_heading"><?php echo Text::_('COM_MINIORANGE_SUPPORT_FEATURES');?><a class="btn btn-danger mo_boot_float-right" href="index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_sync_overview"><?php echo Text::_('COM_MINIORANGE_USERSYNC_BACK');?></a></h3>
        	</div>
			<div>
				<form method="post" action="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moContactUs');?>">
					<div class="mo_boot_col-sm-12">
						<div class="mo_boot_row mo_boot_mt-4">
							<div class="mo_boot_col-sm-3 mo_boot_offset-1">
								<strong><?php echo Text::_('COM_MINIORANGE_EMAIL');?>:<span class="mo_usersync_color_red">*</span></strong>
							</div>
							<div class="mo_boot_col-sm-6">
								<input type="email" class="mo-form-control mo_text_box" id="mo_query_email" name="mo_query_email" value="<?php echo $admin_email; ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_SUPPORT_EMAIL');?>" required />	
							</div>
						</div>
						<div class="mo_boot_row mo_boot_mt-2">
							<div class="mo_boot_col-sm-3 mo_boot_offset-1"> <strong><?php echo Text::_('COM_MINIORANGE_PHONE_NUMBER');?></strong></div>
							<div class="mo_boot_col-sm-6">
								<div class="mo_boot_row mo-phone-inline-row">
									<div class="mo_boot_col-4">
										<div class="mo-phone-card">
											<div class="mo-country-select" id="countrySelect">
												<span class="flag flag-in"></span>
												<span class="dial-code">+91</span>
												<span class="arrow">▾</span>
											</div>
											<ul class="mo-country-list" id="countryList"></ul>

											<input type="hidden" name="country_code" id="countryCode" value="91">
											<input type="hidden" name="client_timezone" id="moClientTimezone" value="">
											<input type="hidden" name="client_timezone_offset" id="moClientTimezoneOffset" value="">
										</div>
									</div>
									<div class="mo_boot_col-8">
										<input type="tel" class="mo-form-control mo_text_box" name="mo_query_phone" id="mo_query_phone" value="<?php echo $admin_phone; ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_SUPPORT_PHONE');?>"/>
									</div>
								</div>
							</div>
						</div>
						<div class="mo_boot_row mo_boot_mt-2">
							<div class="mo_boot_col-sm-3 mo_boot_offset-1"><strong><?php echo Text::_('COM_MINIORANGE_QUERY');?>:</strong><span class="mo_usersync_color_red">*</span></div>
							<div class="mo_boot_col-sm-6">
								<textarea id="mo_query" class = "mo_boot_px-3 mo_text_box mo_query" name="mo_query" cols="52" rows="6"  placeholder="<?php echo Text::_('COM_MINIORANGE_SUPPORT_QUERY');?>" required></textarea>
							</div>
						</div>
						<div class="mo_boot_row mo_boot_my-4 mo_boot_text-center">
							<div class="mo_boot_col-sm-12">
								<input type="submit" name="send_query"  value="<?php echo Text::_('COM_MINIORANGE_SUBMIT_QUERY');?>" class="btn mo_blue_buttons"/>
							</div>
						</div>
					</div>
				</form>
				<br/>              
			</div>
		</div>
	</div>	
<?php
}

function moJoomlaToProvider(){
	$joomla_users = MoUserSyncUtility::moGetTableDetails('#__users', TRUE, 'loadAssocList', '*');
	$app = Factory::getApplication();
	$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
	$app_name = $input->getString('app', '');
	$mo_application_attributes = MoUserSyncUtility::moGetDetails('#__miniorange_sync_to_joomla');
	$mo_application_details = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
	$configured_app = isset($mo_application_details['app_name']) && !empty($mo_application_details['app_name']) ? $mo_application_details['app_name'] : "";
	
	// Get Salesforce profiles if Salesforce is selected
	$salesforce_profiles = array();
	$selected_profile_id = '';
	$profile_error = '';
	if ($app_name === 'Salesforce') {
		require_once JPATH_COMPONENT . '/helpers/Salesforce.php';
		$profiles_result = Salesforce::getAllProfiles();
		
		// Check if result contains error
		if (is_array($profiles_result) && isset($profiles_result['error'])) {
			$profile_error = $profiles_result['error'];
			$salesforce_profiles = array();
		} else {
			$salesforce_profiles = $profiles_result;
		}
		
		$mo_salesforce_config = !empty($mo_application_details['mo_sync_configuration']) ? json_decode($mo_application_details['mo_sync_configuration'], true) : "";
		$selected_profile_id = isset($mo_salesforce_config['mo_salesforce_profile_id']) ? $mo_salesforce_config['mo_salesforce_profile_id'] : '';
	}
	?>

	<div class="mo_boot_row mo_usersync_app_cards">
    	<div class="mo_boot_col-sm-12 mo_boot_mt-3">
			<div class="mo_boot_col-sm-12">
				<h3 class="mo_boot_mx-2 mo_sync_heading"><?php echo Text::_('COM_MINIORANGE_USERSYNC_USER_SYNC_FJOOMLA');?>&ensp;<?php echo $app_name;?></h3>
			</div>
			
			<details class="mo_detail_tag_styles" open>
				<summary class="mo_summary_tag_styles mo_boot_mx-2"><?php echo Text::_('COM_MINIORANGE_USERSYNC_CREATE_IND_USER');?></summary>
				<?php if (!empty($profile_error)): ?>
					<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
						<div class="mo_boot_col-sm-12">
							<div class="alert alert-warning">
								<strong><?php echo Text::_('COM_MINIORANGE_USERSYNC_WARNING');?>:</strong> <?php echo htmlspecialchars($profile_error, ENT_QUOTES, 'UTF-8'); ?>
								<br><small><?php echo Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_PROFILE_REFRESH_HINT');?></small>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<?php if ($app_name === 'Salesforce'): ?>
				<form id="mo_save_salesforce_profile" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moSaveSalesforceProfile&appName=' . $app_name); ?>">
					<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
						<div class="mo_boot_col-sm-3">
							<strong><?php echo Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_PROFILE_ID');?></strong><span class="mo_usersync_color_red">*</span>
						</div>
						<div class="mo_boot_col-sm-5">
							<select id="moSalesforceProfileId" name="moSalesforceProfileId" class="mo-form-control mo-form-control-select" required>
								<option value=""><?php echo Text::_('COM_MINIORANGE_USERSYNC_SELECT_PROFILE');?></option>
								<?php 
									if (!empty($salesforce_profiles) && !isset($salesforce_profiles['error'])) {
										foreach ($salesforce_profiles as $profile) {
											$profile_id = isset($profile['Id']) ? $profile['Id'] : '';
											$profile_name = isset($profile['Name']) ? $profile['Name'] : $profile_id;
											$selected = ($profile_id === $selected_profile_id) ? 'selected' : '';
											echo "<option value='" . htmlspecialchars($profile_id, ENT_QUOTES, 'UTF-8') . "' $selected>" 
												. htmlspecialchars($profile_name, ENT_QUOTES, 'UTF-8') . 
												"</option>";
										}
									} else if (empty($profile_error)) {
										echo "<option value='' disabled>" . Text::_('COM_MINIORANGE_USERSYNC_NO_PROFILES_FOUND') . "</option>";
									}
								?>
							</select>
						</div>
						<div class="mo_boot_col-sm-2">
							<input type="submit" class="btn mo_blue_buttons" value="<?php echo Text::_('COM_MINIORANGE_USERSYNC_SAVE_PROFILE'); ?>">
						</div>
						<div class="mo_boot_col-sm-1 mo_boot_mx-2">
							<a href="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app=' . $app_name . '&sub_tab=mo_joomla_profile_sync'); ?>" 
								class="btn mo_blue_buttons" title="<?php echo Text::_('COM_MINIORANGE_USERSYNC_REFRESH_PROFILES');?>">
								<?php echo Text::_('COM_MINIORANGE_USERSYNC_REFRESH');?>
							</a>
						</div>
					</div>
				</form>
				<?php endif; ?>
				<form id="mo_sync_user_to_provider" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moCreateUserInProvider&appName=' . $app_name); ?>">
					<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
						<div class="mo_boot_col-sm-3">
							<strong><?php echo Text::_('COM_MINIORANGE_USERSYNC_JUSERNAME');?></strong>
						</div>
						<div class="mo_boot_col-sm-5">
							<select id="moCreateUser" name="moCreateUser" class="mo-form-control mo-form-control-select">
								<?php 
									foreach ($joomla_users as $user) {
										// Use email if AWS, username otherwise
										$value = $app_name === 'AWS' ? $user['email'] : $user['username'];
										echo "<option value='" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "'>" 
											. htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . 
											"</option>";
									}
								?>
							</select>
						</div>
						<div class="mo_boot_col-sm-2">
							<input type="submit" class="btn mo_blue_buttons" 
								value="<?php echo Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_CREATE_USER'); ?>">
						</div>
					</div>
				</form>
			</details>


			<details class="mo_detail_tag_styles" open style="position: relative;">
        		<summary class="mo_summary_tag_styles">2 : <?php echo Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_DELETE_USER');?> <sup><img class="crown_img_small mo_boot_ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_usersync/assets/images/crown.webp"></sup></summary>
				<a href="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_license_plan&app=other')?>" class="mo_usersync_summary_a" title="<?php echo Text::_('COM_MINIORANGE_USERSYNC_GO_TO_LICENSING_PLAN');?>"></a>
				<p class="mo_boot_mx-4"><em><?php echo Text::_('COM_MINIORANGE_USERSYNC_DELETE_FEATURE_TEXT');?>&nbsp;<?php echo $app_name;?>.&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_DELETE_FEATURE_TEXT2');?></em></p>
				<form method="post" action="">
					<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
						<div class="mo_boot_col-sm-3">
							<strong><?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERNAME');?></strong>
						</div>
						<div class="mo_boot_col-sm-5">
							<select class="mo-form-control mo_text_box" id="ad_sync_delete_username" name="ad_sync_delete_username" required disabled>
							<option value=""><?php echo Text::_('COM_MINIORANGE_USERSYNC_SELECT');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_USERNAME');?></option>
							</select>
						</div>
						<div class="mo_boot_col-sm-2">
							<input type="submit" class="btn mo_blue_buttons" value="<?php echo Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_DELETE_USER');?>" disabled>
						</div>
					</div>
				</form>
			</details>

			<details class="mo_detail_tag_styles" open style="position: relative;">
        		<summary class="mo_summary_tag_styles"><?php echo Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_AUTOMATIC_PROVISIONING');?> <sup><img class="crown_img_small mo_boot_ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_usersync/assets/images/crown.webp"></sup></summary>
				<a href="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_license_plan&app=other')?>" class="mo_usersync_summary_a" title="<?php echo Text::_('COM_MINIORANGE_USERSYNC_GO_TO_LICENSING_PLAN');?>"></a>
				<form method="post" action="">
					<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
						<div class="mo_boot_col-sm-6">
							<em><p><?php echo Text::_('COM_MINIORANGE_USERSYNC_CREATEUSERIN');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_WHEN_CREATED');?></p></em>
						</div>
						<div class="mo_boot_col-sm-2">
							<input type="checkbox" class="mo_sync_switch" disabled>
						</div>
					</div>
				
					<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
						<div class="mo_boot_col-sm-6">
							<em><p><?php echo Text::_('COM_MINIORANGE_USERSYNC_DELETEUSERIN');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_WHEN_DELETED');?></p></em>
						</div>
						<div class="mo_boot_col-sm-2">
							<input type="checkbox" class="mo_sync_switch" disabled>
						</div>
					</div>
					<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
						<div class="mo_boot_col-sm-6">
							<em><p><?php echo Text::_('COM_MINIORANGE_USERSYNC_UPDATEUSERIN');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_WHEN_UPDATED');?></p></em>
						</div>
						<div class="mo_boot_col-sm-2">
							<input type="checkbox" class="mo_sync_switch" disabled>
						</div>
					</div>
					<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
						<div class="mo_boot_col-sm-6">
							<em><p><?php echo Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_USER_PASSWORD_WHEN_CHANGED_IN_JOOMLA');?></p></em>
						</div>
						<div class="mo_boot_col-sm-2">
							<input type="checkbox" class="mo_sync_switch" disabled>
						</div>
					</div>
				</form>	
			</details>

			<details class="mo_detail_tag_styles" open style="position: relative;">
        		<summary class="mo_summary_tag_styles"><?php echo Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_USER_ATTRIBUTES');?> <sup><img class="crown_img_small mo_boot_ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_usersync/assets/images/crown.webp"></sup></summary>
				<a href="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_license_plan&app=other')?>" class="mo_usersync_summary_a" title="<?php echo Text::_('COM_MINIORANGE_USERSYNC_GO_TO_LICENSING_PLAN');?>"></a>
				<p class="mo_boot_mx-4"><strong><?php echo Text::_('COM_MINIORANGE_USERSYNC_NOTE');?></strong><?php echo Text::_('COM_MINIORANGE_USERSYNC_FEATURE_ALLOW_USER_ATTRIBUTE');?><?php echo $app_name?></p>		
					<table class="mo_config_table mo_boot_my-4">
						<thead>
							<tr>
								<th class="mo_boot_text-center"><h6><?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_GROUPS_SNO');?></h6></th>
								<th><h6>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_NAMEE');?></h6></th>
								<th><h6><?php echo Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_USER_ATTRIBUTES_JOOMLA_ATTRIBUTE');?></h6></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="mo_boot_text-center"> 1</td>
								<td> 
									<input class="mo-form-control mo-search-directory-textbox" style="width: 90%;" type="text"  placeholder="&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_ATTRIBUTES_PLACEHOLDER');?>" value="" readonly/>
								</td>
								<td>
									<input class="mo-form-control mo-search-directory-textbox" style="width: 90%;" type="text"  required placeholder="<?php echo Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_USER_ATTRIBUTES_JOOMLA_ATTRIBUTE_PLACEHOLDER');?>" value="" readonly/>
								</td>
							</tr>
							<tr>
								<td class="mo_boot_text-center">2</td>
								<td>	 
									<input class="mo-form-control mo-search-directory-textbox" style="width: 90%;" type="text"  placeholder="&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_ATTRIBUTES_PLACEHOLDER');?>" value="" readonly/>
								</td>
								<td>
									<input class="mo-form-control mo-search-directory-textbox" style="width: 90%;" type="text" required placeholder="<?php echo Text::_('COM_MINIORANGE_JOOMLA_TO_PROVIDER_SYNC_USER_ATTRIBUTES_JOOMLA_ATTRIBUTE_PLACEHOLDER');?>" value="" readonly/>
								</td>
							</tr>
						</tbody>
					</table>  	
			</details>

			<details class="mo_detail_tag_styles" open style="position: relative;">
				<summary class="mo_summary_tag_styles">5 : <?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_ALL_USERS');?> <sup><img class="crown_img_small mo_boot_ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_usersync/assets/images/crown.webp"></sup></summary>
				<a href="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_license_plan&app=other')?>" class="mo_usersync_summary_a" title="<?php echo Text::_('COM_MINIORANGE_USERSYNC_GO_TO_LICENSING_PLAN');?>"></a>
				<form method="post" action="">
					<div class="mo_boot_row mo_boot_my-4 mo_boot_mx-4">
						<div class="mo_boot_col-sm-7">	
							<p><?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_ALL_USERS');?></p>
						</div>
						<div class="mo_boot_col-sm-3">
							<input type="submit" class="btn mo_blue_buttons" value="<?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_ALL_USERS');?>" disabled>
						</div>
					</div>
				</form>
			</details>

			<?php if (empty($configured_app)) { ?>
			<script>
				jQuery( document ).ready(function() {
					jQuery("#mo_sync_user_to_provider :input[type='submit']").prop("disabled", true);
				});
			</script>
			<?php } ?>
		</div>
	</div>	
<?php 	
}

//FOR THE APPLICATION DETAILS
function getProviderData(){
    return  '{
        "Azure": {
			"'.Text::_('COM_MINIORANGE_APPLICATION_ID').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_APPLICATION_ID_PLACEHOLDER').'",
				"name": "mo_azure_application_id",
				"id": "mo_azure_application_id"
			},
			"'.Text::_('COM_MINIORANGE_CLIENT_SECRET').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_CLIENT_SECRET_PLACEHOLDER').'",
				"name": "mo_azure_client_secret",
				"id": "mo_azure_client_secret"
			},
			"'.Text::_('COM_MINIORANGE_TENANT_ID').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_TENANT_ID_PLACEHOLDER').'",
				"name": "mo_azure_tenant_id",
				"id": "mo_azure_tenant_id"
			},
			"'.Text::_('COM_MINIORANGE_TENANT_NAME').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_TENANT_NAME_PLACEHOLDER').'",
				"name": "mo_azure_tenant_name",
				"id": "mo_azure_tenant_name"
			},
			"'.Text::_('COM_MINIORANGE_TEST_UPN_ID').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_TEST_USERNAME_PLACEHOLDER').'",
				"name": "mo_usersync_upn",
				"id": "mo_usersync_upn"
			}
		},
		"Keycloak": {
			"'.Text::_('COM_MINIORANGE_KEYCLOAK_DOMAIN').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_KEYCLOAK_DOMAIN_PLACEHOLDER').'",
				"name": "mo_keycloak_domain",
				"id": "mo_keycloak_domain"
			},
			"'.Text::_('COM_MINIORANGE_CLIENT_ID').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_CLIENT_ID_PLACEHOLDER').'",
				"name": "mo_keycloak_client_id",
				"id": "mo_keycloak_client_id"
			},
			"'.Text::_('COM_MINIORANGE_REALM_NAME').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_REALM_NAME_PLACEHOLDER').'",
				"name": "mo_keycloak_realm",
				"id": "mo_keycloak_realm"
			},
			"'.Text::_('COM_MINIORANGE_REALM_USERNAME').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_REALM_USERNAME_PLACEHOLDER').'",
				"name": "mo_keycloak_username",
				"id": "mo_keycloak_username"
			},
			"'.Text::_('COM_MINIORANGE_REALM_USER_PASSWORD').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_REALM_USER_PASSWORD_PLACEHOLDER').'",
				"name": "mo_keycloak_user_password",
				"id": "mo_keycloak_user_password"
			},
			"'.Text::_('COM_MINIORANGE_TEST_UPN_ID').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_TEST_USERNAME_PLACEHOLDER').'",
				"name": "mo_usersync_upn",
				"id": "mo_usersync_upn"
			}
		},
		"Okta": {
			"'.Text::_('COM_MINIORANGE_OKTA_BASE_URL').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_OKTA_BASE_URL_PLACEHOLDER').'",
				"name": "mo_okta_base_url",
				"id": "mo_okta_base_url"
			},
			"'.Text::_('COM_MINIORANGE_OKTA_BEARER_TOKEN').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_OKTA_BEARER_TOKEN_PLACEHOLDER').'",
				"name": "mo_okta_bearer_token",
				"id": "mo_okta_bearer_token"
			},
			"'.Text::_('COM_MINIORANGE_TEST_UPN_ID').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_TEST_USERNAME_PLACEHOLDER').'",
				"name": "mo_usersync_upn",
				"id": "mo_usersync_upn"
			}
		},
		"AWS": {
			"'.Text::_('COM_MINIORANGE_POOL_ID').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_POOL_ID_PLACEHOLDER').'",
				"name": "mo_aws_cognito_pool_id",
				"id": "mo_aws_cognito_pool_id"
			},
			"'.Text::_('COM_MINIORANGE_AWS_REGION').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_AWS_REGION_PLACEHOLDER').'",
				"name": "mo_aws_cognito_region",
				"id": "mo_aws_cognito_region"
			},
			"'.Text::_('COM_MINIORANGE_AWS_ACCESS_KEY').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_AWS_ACCESS_KEY_PLACEHOLDER').'",
				"name": "mo_aws_cognito_access_key",
				"id": "mo_aws_cognito_access_key"
			},
			"'.Text::_('COM_MINIORANGE_AWS_SECRET_KEY').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_AWS_SECRET_KEY_PLACEHOLDER').'",
				"name": "mo_aws_cognito_secret_key",
				"id": "mo_aws_cognito_secret_key"
			},
			"'.Text::_('COM_MINIORANGE_TEST_UPN_ID').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_TEST_USERNAME_PLACEHOLDER').'",
				"name": "mo_usersync_upn",
				"id": "mo_usersync_upn"
			}
		},
		"Salesforce": {
			"'.Text::_('COM_MINIORANGE_USERSYNC_CONSUMER_KEY').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_USERSYNC_CONSUMER_KEY_PLACEHOLDER').'",
				"name": "mo_salesforce_consumer_key",
				"id": "mo_salesforce_consumer_key"
			},
			"'.Text::_('COM_MINIORANGE_USERSYNC_CONSUMER_SECRET').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_USERSYNC_CONSUMER_SECRET_PLACEHOLDER').'",
				"name": "mo_salesforce_consumer_secret",
				"id": "mo_salesforce_consumer_secret"
			},
			"'.Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_USERNAME').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_USERNAME_PLACEHOLDER').'",
				"name": "mo_salesforce_username",
				"id": "mo_salesforce_username"
			},
			"'.Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_PASSWORD').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_USERSYNC_SALESFORCE_PASSWORD_PLACEHOLDER').'",
				"name": "mo_salesforce_password",
				"id": "mo_salesforce_password",
				"type": "password"
			},
			"'.Text::_('COM_MINIORANGE_TEST_UPN_ID').'": {
				"placeholder": "'.Text::_('COM_MINIORANGE_TEST_USERNAME_PLACEHOLDER').'",
				"name": "mo_usersync_upn",
				"id": "mo_usersync_upn"
			}
		}
	}';
}

function moGuideList(){

	return  '{
        "Azure": "https://plugins.miniorange.com/azure-ad-user-provisioning-with-joomla",
		"Keycloak": "https://plugins.miniorange.com/user-sync-provisioning-between-keycloak-and-joomla",
		"Okta": "https://plugins.miniorange.com/user-sync-provisioning-between-okta-and-joomla",
		"AWS": "https://plugins.miniorange.com/user-provisioning-between-aws-and-joomla",
		"Salesforce": "https://plugins.miniorange.com/joomla-integrations" 
	}';
}
function mo_joomla_sync_configuration() {

 ?>
<div class="mo_boot_row mo_usersync_app_cards">
    <div class="mo_boot_col-sm-12">
		<?php 
		$sync_active_tab = 'config';
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$get = ($input && $input->get) ? $input->get->getArray() : [];
	
		if(isset($get['sub_tab']) && !empty($get['sub_tab'])){
			$sync_active_tab = $get['sub_tab'];
		}

		$app_name = $input->getString('app', '');
		$app = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
		$mo_application_attributes = !empty($app['mo_sync_configuration']) ? json_decode($app['mo_sync_configuration'], true) : "";
		if(!empty($app_name)){
		?>	
		<div class="mo_boot_row">
			<div class="mo_boot_col-sm-2 mo_sync-row">
           		<div class="mo_boot_row">
					<a id="mo_syncconfiguration"  class="mo_boot_col-sm-12 mo_boot_p-3 mo_usersync_text_decoration border-1 mo_sync-tab <?php echo (($sync_active_tab=='config')?'mo_sync-tab-active':'');?>" 
						href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$app_name.'&sub_tab=config';?>"> 
                   		<strong><?php echo Text::_('COM_MINIORANGE_TAB_CONFIGURE_APP');?>&nbsp;<?php echo $app_name?></strong></a>
               		
               		<a id="mo_sync_to_joomla" class="mo_boot_col-sm-12 mo_boot_p-3 mo_usersync_text_decoration border-1 mo_sync-tab <?php echo (($sync_active_tab=='sync_to_joomla')?'mo_sync-tab-active':'');?>"
						href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$app_name.'&sub_tab=sync_to_joomla';?>"> 
						<strong><span><?php echo $app_name?> <img width="20px" class="m-1" height="20px" src="<?php echo Uri::base();?>/components/com_miniorange_usersync/assets/images/arrow.png"> <?php echo Text::_('COM_MINIORANGE_JOOMLA_TAB_SYNC');?></span></strong>
					</a>
					<a id="mo_joomla_profile_sync" class="mo_usersync_text_decoration mo_boot_col-sm-12 mo_boot_p-3 border-1 mo_sync-tab <?php echo (($sync_active_tab=='mo_joomla_profile_sync')?'mo_sync-tab-active':'');?>" 
						href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration&addition_option=addconfig&app='.$app_name.'&sub_tab=mo_joomla_profile_sync';?>">
						<strong><span><?php echo Text::_('COM_MINIORANGE_JOOMLA_TAB_SYNC');?> <img width="20px" class="m-1" height="20px" src="<?php echo Uri::base();?>/components/com_miniorange_usersync/assets/images/arrow.png">&nbsp;<?php echo $app_name;?>&nbsp;</span></strong>
					</a>
					<a class="mo_usersync_text_decoration mo_boot_col-sm-12 mo_boot_p-3 border-1 mo_sync-tab" 
						href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=syncconfiguration';?>">
						<strong><?php echo Text::_('COM_MINIORANGE_BACK');?></strong>
					</a>
       	 		</div>
       		</div>
			<div class="mo_boot_col-sm-10">
				<div class="mo_boot_row">
        			<div class="mo_boot_col-sm-12 mo_sync_tab" id="sync_to_joomla" style="<?php echo (($sync_active_tab=='sync_to_joomla')?'display:block;':'display:none;');?>">
            			<?php mo_provider_to_joomla(); ?>
        			</div>
    			</div>
				<div class="mo_boot_row">
        			<div class="mo_boot_col-sm-12 mo_sync_tab" id="joomla_profile_sync" style="<?php echo (($sync_active_tab=='mo_joomla_profile_sync')?'display:block;':'display:none;');?>">
            			<?php moJoomlaToProvider(); ?>
        			</div>
    			</div>
				<div class="mo_boot_row">
        			<div class="mo_boot_col-sm-12 mo_sync_tab" id="config" style="<?php echo (($sync_active_tab=='config')?'display:block;':'display:none;');?>">
            			<?php moShowConfig(); ?>
        			</div>
    			</div>
			</div>
		</div>
		<?php }?>
	</div>
</div>
<?php
}
?>

<?php

function moShowConfig(){
	$appJson = json_decode(getProviderData(), true);
	$guideList = json_decode(moGuideList(),true);
	$placeholder="";
	$name="";
	$id="";
	$tag_value="";
	$app = Factory::getApplication();
	$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
	$app_name = $input->getString('app', 'Other');
	$app = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
	$mo_application_attributes = !empty($app['mo_sync_configuration']) ? json_decode($app['mo_sync_configuration'], true) : "";
	$mo_previously_configured_app = !empty($app['app_name']) ? $app['app_name'] : "";
?>
	<div class="mo_boot_p-4" style="<?php //echo $mo_hide_style;?>">
		<div class="mo_boot_col-sm-12 mo_boot_row" >
			<h3 class="mo_boot_col-sm-7 mo_sync_heading"><?php echo Text::_('COM_MINIORANGE_USERSYNC_CONFIGURE');?>&nbsp;<?php echo $app_name;?></h3>
			<div class="mo_boot_col-sm-5 mo_boot_mt-2">
				<input type="button" class="mo_boot_btn mo_blue_buttons mo_boot_float-right mo_boot_mx-3" onclick="window.open('<?php echo $guideList[$app_name]; ?>')" value="<?php echo Text::_('COM_MINIORANGE_USERSYNC_SETUP_GUIDE');?>">
				<form method="post" action="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moResetConfig&appName='.$app_name);?>"> 
					<input type="submit" class="mo_boot_btn mo_red_buttons mo_boot_float-right" id="mo_reset_form" value="<?php echo Text::_('COM_MINIORANGE_USERSYNC_RESET');?>"></h3>
				</form>
			</div>
		</div>
		<form method="post" id="mo_save_configuration" action="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moSaveConfig&appName='.$app_name); ?>">
			<?php
			$appData = $appJson[$app_name];
			foreach ($appData as $fields => $field) {
				$placeholder = $name = $id = $tag_value = $readonly = $input_type = "";
				
				foreach ($field as $key => $value) {
					if ($key == 'placeholder') {
						$placeholder = $value;
					} else if ($key == 'name') {
						$name = $value;
					} else if ($key == 'id') {
						$id = $value;

						if ($app_name === 'Salesforce') {
							if ($id === 'mo_salesforce_redirect_uri') {
								$readonly = 'readonly';
								$tag_value = Uri::root(); 
							} else if ($id === 'mo_salesforce_scopes') {
								$tag_value = 'api refresh_token';
							} else {
								$tag_value = isset($mo_application_attributes[$id]) ? $mo_application_attributes[$id] : "";
							}
						} else {
							$tag_value = isset($mo_application_attributes[$id]) ? $mo_application_attributes[$id] : "";
						}
					} else if ($key == 'type') {
						$input_type = $value;
					}
				} 

				// Default to text type if not specified
				if (empty($input_type)) {
					$input_type = 'text';
				}

				echo '<div class="mo_boot_row mo_boot_mt-2 mo_boot_my-4">
					<div class="mo_boot_col-sm-3 mo_boot_offset-1">' . $fields . '
					</div>
					<div class="mo_boot_col-sm-7">
						<input class="mo-form-control" id="' . $id . '" name="' . $name . '" type="' . $input_type . '" placeholder="' . $placeholder . '" value="' . $tag_value . '" ' . $readonly . ' required>
					</div>
				</div>';
			}
			?>
			<input name="appName" id="moAppName" value="<?php echo $app_name; ?>" hidden>
			<div class="mo_boot_row mo_boot_my-3 mo_boot_text-center">
				<div class="mo_boot_col-sm-12">
					<input type="submit" value="<?php echo Text::_('COM_MINIORANGE_USERSYNC_SAVE_CONFIGURATION'); ?>" class="btn mo_blue_buttons">
					<input type="button" value="<?php echo Text::_('COM_MINIORANGE_TEST_CONNECTION'); ?>" onclick="mo_test_configuration()" class="btn mo_blue_buttons" <?php if (empty($mo_previously_configured_app)) echo 'disabled'; ?>>
				</div>
			</div>
		</form>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var appname = <?php echo json_encode($mo_previously_configured_app);?>;
			var currentappname = <?php echo json_encode($app_name);?>;
					
			if(appname !== "" && (appname.toLowerCase() !== currentappname.toLowerCase()) ){
				var form = document.getElementById('mo_save_configuration');
				form.addEventListener('submit', function(e) {
					e.preventDefault(); 
					var confirmed = confirm("<?php echo Text::_('COM_MINIORANGE_USERSYNC_CONFIG_FOR_YOUR');?>"+appname+" <?php echo Text::_('COM_MINIORANGE_USERSYNC_PREVIOUS_APPDETAILS_ERASE');?>");
					if (confirmed) {
						form.submit(); 
					}
				});
			}
		});
				
	</script>
	<?php
}

function mo_provider_to_joomla(){

	$app = Factory::getApplication();
	$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
	$app_name = $input->getString('app', '');
	$mo_application_details = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
	$configured_app = isset($mo_application_details['app_name']) && !empty($mo_application_details['app_name']) ? $mo_application_details['app_name'] : "";
	$mo_application_attributes = MoUserSyncUtility::moGetDetails('#__miniorange_sync_to_joomla');
	$mo_name = isset($mo_application_attributes['moName'])? $mo_application_attributes['moName']: "";
	$mo_username = isset($mo_application_attributes['moUsername'])? $mo_application_attributes['moUsername']: "";
	$mo_email = isset($mo_application_attributes['moEmail'])? $mo_application_attributes['moEmail']: "";
	$mo_user_attributes = isset($mo_application_attributes['moUserAttr'])? explode(",", $mo_application_attributes['moUserAttr']): "";
	$moUserList = isset($mo_application_attributes['moUserList']) ? json_decode($mo_application_attributes['moUserList'],true) : "";
	$groups = MoUserSyncUtility::moGetJoomlaGroups();
	?>

	<div class="mo_boot_row mo_usersync_app_cards">
    	<div class="mo_boot_col-sm-12 mo_boot_mt-3">
			<div class="mo_boot_col-sm-12 ">
				<h3 class="mo_boot_mx-2 mo_sync_heading"><?php echo Text::_('COM_MINIORANGE_USERSYNC_FROM');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_TO_JOOMLA');?></h3>
			</div>
	
		<details class="mo_detail_tag_styles mo_boot_my-4" open>
        	<summary class="mo_summary_tag_styles">1 : <?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_ATTRIBUTE_MAPPING');?></summary>
			<form method="post" id="mo_sync_save_attributes" action="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moSaveSyncAttributeConfig&appName='.$app_name);?>">
				<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
					<div class="mo_boot_col-sm-4">
						<p class="mo_boot_my-4 mo_boot_mx-4 mo_usersync_provider_name" ><?php echo Text::_('COM_MINIORANGE_PROVIDER_JOOMLA_NAME');?></p>
					</div>
					<div class="mo_boot_col-sm-5">
						<p class="mo_boot_my-4 mo_usersync_provider_name">&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_ATTRIBUTE_NAME');?></p>
					</div>
				</div>
				<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
					
					<div class="mo_boot_col-sm-4">
						<p class="mo_boot_mx-4"><?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_ATTRIBUTE_MAPPING_NAME');?> :</p>
					</div>
					<div class="mo_boot_col-sm-7">
						<select id="mo_joomla_name" name="mo_joomla_name" required class="mo-form-control mo-form-control-select" >
                            <option value="<?php echo Text::_('COM_MINIORANGE_USERSYNC_NONE_SELECTED');?>" selected><?php echo Text::_('COM_MINIORANGE_USERSYNC_SELECT');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_NAME_ATTRIBUTE');?></option>
                            <?php
                                foreach($mo_user_attributes as $key => $value)
                                {
                                    if($value == $mo_name)
                                    {
                                        $checked = "selected";
                                    }
                                    else
                                    {
                                        $checked = "";
                                    }
                                    if($value!="")
                                        echo"<option ".$checked." value='".$value."'>".$value."</option>";
                                    }
                            ?>
                        </select>
					</div>
				</div>
				<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
					<div class="mo_boot_col-sm-4">
						<p class="mo_boot_mx-4"><?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_ATTRIBUTE_MAPPING_USERNAME');?> :</p>
					</div>
					<div class="mo_boot_col-sm-7">
						<select id="mo_joomla_username" name="mo_joomla_username" required class="mo-form-control mo-form-control-select" >
                            <option value="<?php echo Text::_('COM_MINIORANGE_USERSYNC_NONE_SELECTED');?>" selected><?php echo Text::_('COM_MINIORANGE_USERSYNC_SELECT');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_USERNAME_ATTRIBUTE');?></option>
                            <?php
                                foreach($mo_user_attributes as $key => $value)
                                {
                                    if($value == $mo_username)
                                    {
                                        $checked = "selected";
                                    }
                                    else
                                    {
                                        $checked = "";
                                    }
                                    if($value!="")
                                        echo"<option ".$checked." value='".$value."'>".$value."</option>";
                                    }
                            ?>
                        </select>
					</div>
				</div>
				<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
					<div class="mo_boot_col-sm-4">
						<p class="mo_boot_mx-4"><?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_ATTRIBUTE_MAPPING_EMAIL');?> :</p>
					</div>
					<div class="mo_boot_col-sm-7">
						<select id="mo_joomla_email" name="mo_joomla_email" required class="mo-form-control mo-form-control-select" >
                            <option value="<?php echo Text::_('COM_MINIORANGE_USERSYNC_NONE_SELECTED');?>" selected><?php echo Text::_('COM_MINIORANGE_USERSYNC_SELECT');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_EMAIL_ATTRIBUTE');?></option>
                            <?php
                                foreach($mo_user_attributes as $key => $value)
                                {
                                    if($value == $mo_email)
                                    {
                                        $checked = "selected";
                                    }
                                    else
                                    {
                                        $checked = "";
                                    }
                                    if($value!="")
                                        echo"<option ".$checked." value='".$value."'>".$value."</option>";
                                    }
                            ?>
                        </select>
					</div>
				</div>
				<div class="mo_boot_my-4 mo_boot_row mo_boot_mx-4">
					<div class="mo_boot_col-sm-12 mo_boot_mx-3">
					<input type="submit" class="btn mo_blue_buttons mo_boot_offset-6" value="<?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SAVE');?>" >
				</div></div>
			</form>
		</details>

		<details class="mo_detail_tag_styles" open>
        	<summary class="mo_summary_tag_styles"><?php echo Text::_('COM_MINIORANGE_USERSYNC_SECOND_INDIVIDUAL_USER');?></summary>
			<form method="post" id="mo_sync_user_in_joomla" action="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moSyncUserInJoomla&appName='.$app_name);?>">
		
				<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
					<div class="mo_boot_col-sm-7">
						<select class="mo-form-control mo-form-control-select" id="username" name="userSyncToJoomla" required >
							<option value=""><?php echo Text::_('COM_MINIORANGE_USERSYNC_SELECT_USER');?></option>
							<?php
								if (!empty($moUserList)) {
									foreach ($moUserList as $user) {
								
										// If it's a simple string (like just an email)
										if (is_string($user)) {
											$email = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
											echo "<option value='" . json_encode(['Email' => $email]) . "' data-tokens='" . $email . "'>$email</option>";
										
										// If it's an associative array with 'Email' key (like AWS)
										} elseif (is_array($user) && isset($user['Email'])) {
											$email = is_string($user['Email']) ? htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8') : '';
											$name = (!empty($user['Name']) && is_string($user['Name'])) ? htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8') : $email;
								
											echo "<option value='" . htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8') . "' data-tokens='" . $email . "'>$name</option>";
										
										// If it's a nested array like $user[0]['Name'] or $user[0] = email
										} elseif (is_array($user) && isset($user[0])) {
											$first = $user[0];
								
											// If first element is an array with 'Name'
											if (is_array($first) && isset($first['Name'])) {
												$name = is_string($first['Name']) ? htmlspecialchars($first['Name'], ENT_QUOTES, 'UTF-8') : '';
												$tokens = htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8');
												echo "<option value='$tokens' data-tokens='$name'>$name</option>";
								
											// If first element is string (email)
											} elseif (is_string($first)) {
												$email = htmlspecialchars($first, ENT_QUOTES, 'UTF-8');
												echo "<option value='" . htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8') . "' data-tokens='" . $email . "'>$email</option>";
											}
										}
									}
								}					
							?>
						</select>
					</div>
					<div class="mo_boot_col-sm-3">
						<input type="submit" class="btn mo_blue_buttons" value="<?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER');?>" >
					</div>
				</div>
			</form>
			<form id= "mo_retrieve_user_in_joomla_form" name="mo_sync_to_joomla" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moRetriveAllUsers&appName='.$app_name);?>">
				<div class="mo_boot_row mo_boot_my-2 mo_boot_mx-4">
					<div class="mo_boot_col-sm-7">
						<p class="mo_boot_my-2"><em><strong><?php echo Text::_('COM_MINIORANGE_USERSYNC_UNABLE_USER_DROPDOWN');?><br><?php echo Text::_('COM_MINIORANGE_USERSYNC_CLICK_THE');?><span class="mo_usersync_color_red"><?php echo Text::_('COM_MINIORANGE_USERSYNC_RETRIVE_ALL_USERS');?></span><?php echo Text::_('COM_MINIORANGE_USERSYNC_BTN_BUTTON');?></strong></em></p>
					</div>
					<div class="mo_boot_col-sm-3">
						<input type="submit" class="btn mo_blue_buttons mo_boot_my-2" value="<?php echo Text::_('COM_MINIORANGE_USERSYNC_RETRIVE_ALL_USERS');?>" >
						<input name="name" value="mo_sync_to_joomla" hidden>
					</div>
				</div>
			</form>
		</details>

		<details class="mo_detail_tag_styles" open style="position: relative;">
        	<summary class="mo_summary_tag_styles">3 : <?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_ALL_USERS');?> <sup><img class="crown_img_small mo_boot_ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_usersync/assets/images/crown.webp"></sup></summary>
			<a href="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_license_plan&app=other')?>" class="mo_usersync_summary_a" title="<?php echo Text::_('COM_MINIORANGE_USERSYNC_GO_TO_LICENSING_PLAN');?>"></a>
			<form method="post" action="">
				<p class="mo_boot_mx-4"><em><?php echo Text::_('COM_MINIORANGE_USERSYNC_ALL_USER_PRESENT');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_WILL_CREATED_JOOMLA');?></em></p>
				<div class="mo_boot_row mo_boot_my-4 mo_boot_mx-4">
					<div class="mo_boot_col-sm-7">	
						<p><?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_ALL_USERS');?></p>
					</div>
					<div class="mo_boot_col-sm-3">
						<input type="submit" class="btn mo_blue_buttons" value="<?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_ALL_USERS');?>" disabled>
					</div>
				</div>
			</form>
		</details>
	
		<details class="mo_detail_tag_styles" open style="position: relative;">
        	<summary class="mo_summary_tag_styles">4 : <?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_GROUPS');?> <sup><img class="crown_img_small mo_boot_ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_usersync/assets/images/crown.webp"></sup></summary>
			<a href="<?php echo Route::_('index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_license_plan&app=other')?>" class="mo_usersync_summary_a" title="<?php echo Text::_('COM_MINIORANGE_USERSYNC_GO_TO_LICENSING_PLAN');?>"></a>
			<form id="">
				<p class="mo_boot_mx-4"><?php echo Text::_('COM_MINIORANGE_USERSYNC_MAP_USER_GROUPS');?>&nbsp;<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_JUSERGROUPS');?></p>	
				<div class="mo_boot_row mo_boot_my-4 mo_boot_mx-4">
					<div class="mo_boot_col-sm-12">
						<input type="checkbox" id="mo_ldap_grp_enable" name="enable_role_mapping" value="1"  disabled> 
						<span class="mo_boot_mx-2"><?php echo Text::_('COM_MINIORANGE_ENABLE_GROUP_MAPPING');?></span>
						<br>
					</div>	
					<div class="mo_boot_col-sm-12">
						<div class="mo_boot_row mo_boot_my-2">
							<div class="mo_boot_col-sm-7"> <?php echo Text::_('COM_MINIORANGE_SELECT_DEFAULT_GROUPS');?>&nbsp;&nbsp;</div>
  							<div class="mo_boot_col-sm-5">	
								<select class="mo-form-control mo_ldap_server_details" name="mapping_value_default" id="default_group_mapping" readonly>
									<?php							
										foreach ($groups as $group) {
											if($group[4] != 'Super Users' || $group[4] != 'Public' || $group[4] != 'Guest'){
												echo '<option  value = "'. $group[0].'">'.$group[4].'</option>';
											}
										}			
									?>
								</select>
							</div>
						</div>			
					</div>
				</div>	
			</form>	
			<p class="mo_boot_mx-4 mo_usersync_font_weight"><u><?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_GROUP_MAPPING');?></u></p>
			<table class="mo_config_table mo_boot_my-4">
				<thead>
					<tr>
						<th class="mo_boot_text-center"><h6><?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_GROUPS_SNO');?></h6></th>
						<th><h6><?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_GROUPNAME');?></h6></th>
						<th><h6><?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_JOOMLA_GROUP_NAME');?></h6></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="mo_boot_text-center"> 1</td>
						<td> 
							<input class="mo-form-control mo-search-directory-textbox" style="width: 90%;" type="text" placeholder="<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_GROUPNAME');?>" value="" readonly/>
						</td>
						<td>
							<input class=" mo-form-control mo-search-directory-textbox" style="width: 90%;"  type="text" required placeholder="<?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_JOOMLA_GROUP_NAME');?>" value="" readonly/>
						</td>
					</tr>
					<tr>
						<td class="mo_boot_text-center">2</td>
						<td>	 
							<input class="mo-form-control mo-search-directory-textbox" style="width: 90%;" type="text" placeholder="<?php echo $app_name;?>&nbsp;<?php echo Text::_('COM_MINIORANGE_USERSYNC_GROUPNAME');?>" value="" readonly/>
						</td>
						<td>
							<input class=" mo-form-control mo-search-directory-textbox" style="width: 90%;" type="text" required placeholder="<?php echo Text::_('COM_MINIORANGE_PROVIDER_TO_JOOMLA_SYNC_USER_JOOMLA_GROUP_NAME');?>" value="" readonly/>
						</td>
					</tr>
				</tbody>
			</table>   
		</details>

		<?php if (empty($configured_app)) { ?>
		<script>
			jQuery( document ).ready(function() {
				jQuery("#mo_sync_user_in_joomla :input[type='submit']").prop("disabled", true);
				jQuery("#mo_sync_save_attributes :input[type='submit']").prop("disabled", true);
				jQuery("#mo_retrieve_user_in_joomla_form :input[type='submit']").prop("disabled", true);
			});
		</script>
		<?php } ?>
	</div>
</div>
<?php 
}

function mo_licensingplan(){
?>	
	<div class="mo_boot_row mo_white_background">
		<div class="mo_boot_col-sm-12">
	
 			<div id="mo_blue_background">
				<h2 class="mo_boot_col-sm-11 mo_feature_comparision mo_boot_my-2 mo_boot_mt-4"><?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISON');?><a class="btn btn-danger mo_boot_float-right" href="index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=mo_sync_overview"><?php echo Text::_('COM_MINIORANGE_USERSYNC_BACK');?></a></h2>
			</div><br>
			
			<div class="mo_boot_col-sm-12 mo_pricing_wrapper mo_boot_my-4">
           		<div class="mo_boot_col-sm-12 mo_pricing_table">
               		<div class="mo_pricing_table_head mo_boot_text-center" >
                    	<h4 class="mo_pricing_table_title"><?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_FREE_PLAN');?></h4>
              	 	</div>
               		<div class="mo_pricing_table_content">
                   		<ul class="mo_boot_offset-1"> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_FREE_PLAN_FEATURES');?></ul>
                   		<div class=" mo_boot_text-center">
							<div class="mo_sign-up">
				   				<input type="button"  onclick="window.open('https://miniorange.com/contact')" target="_blank" value="<?php echo Text::_('COM_MINIORANGE_CONTACT_US');?>"  class="btn mo_blue_buttons bordered radius" />
                   			</div>
						</div>
              	 	</div>
           		</div>
 
            	<div class="mo_boot_col-sm-12 mo_pricing_table">            
                	<div class="mo_pricing_table_head mo_boot_text-center">
                    	<h4 class="mo_pricing_table_title"><?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN');?></h4>
                	</div>
                	<div class="mo_pricing_table_content" >
                    	<ul class="mo_boot_offset-1"> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURES');?> </ul>
                    	<div class="mo_boot_text-center">
							<div class="mo_sign-up mo_boot_text-center">
								<input type="button" onclick= "window.open('https://miniorange.com/contact')" target="_blank" value="<?php echo Text::_('COM_MINIORANGE_UPGRADE');?>"  class="btn mo_blue_buttons bordered radius" />
                    		</div>
						</div>
                	</div>
            	</div>
       		</div>
		</div>
	</div>	
<?php
}
?>