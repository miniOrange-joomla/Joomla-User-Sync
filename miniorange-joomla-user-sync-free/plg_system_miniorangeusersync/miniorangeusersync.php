<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  plg_system_miniorangeusersync
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.plugin.plugin' );
jimport('joomla.installer.installer');
jimport('joomla.application.component.helper');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Uri\Uri;
$lang = Factory::getLanguage();
$lang->load('plg_system_miniorangeusersync',JPATH_ADMINISTRATOR);

include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_usersync' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'MoUserSyncUtility.php';

class plgSystemminiorangeUserSync extends CMSPlugin
{

	public function onAfterInitialise()
	{
		  $app = Factory::getApplication();
      $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
      $get = ($input && $input->get) ? $input->get->getArray() : [];
      $post = ($input && $input->post) ? $input->post->getArray() : [];
      $tab = 0;
      $tables = Factory::getDbo()->getTableList();

      foreach ($tables as $table) {
          if ((strpos($table, "miniorange_user_sync_config") !== FALSE) ||(strpos($table, "miniorange_sync_to_joomla") !== FALSE)  )
              $tab = $table;
      }
      if ($tab === 0)
          return;
      
      if (isset($post['mojsp_feedback'])|| isset($post['mojspfree_skip_feedback'])) 
      {
          if($tab)
          {
            $radio = isset($post['deactivate_plugin'])? $post['deactivate_plugin']:'';
            $data = isset($post['query_feedback'])?$post['query_feedback']:'';
            $feedback_email = isset($post['feedback_email'])? $post['feedback_email']:'';
          
            $database_name = '#__miniorange_usersync_customer';
            $updatefieldsarray = array(
            'uninstall_feedback' => 1,
            );
            
            $this->generic_update_query($database_name, $updatefieldsarray);
            $current_user = Factory::getUser();
        
            $customerResult = (new MoUserSyncUtility)->_load_db_values('#__miniorange_usersync_customer');

            $dVar=new JConfig();
            $check_email = $dVar->mailfrom;
            $admin_email = !empty($customerResult['admin_email']) ? $customerResult['admin_email'] :$check_email;
            $admin_email = !empty($admin_email)?$admin_email:self::getSuperUser();
            $admin_phone = $customerResult['admin_phone'];
            $data1 = $radio . ' : ' . $data . '  <br><br><strong>Email:</strong>  ' . $feedback_email;

            if(isset($post['mojspfree_skip_feedback']))
            {
                $data1='Skipped the feedback';
            }

            if(file_exists(JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_usersync' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'MoUserSyncCustomer.php'))
            {
                require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_usersync' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'MoUserSyncCustomer.php';

                MoUserSyncCustomer::mo_user_sync_submit_feedback_form($admin_email, $admin_phone, $data1,'');
            }
            require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Installer.php';
          
            foreach ($post['result'] as $fbkey) 
            {
                $result = (new MoUserSyncUtility)->loadDBValues('#__extensions', 'loadColumn','type',  'extension_id', $fbkey);
                $identifier = $fbkey;
                $type = 0;
                foreach ($result as $results) {
                  $type = $results;
                }
            
                if ($type) {
                  $cid = 0;
                  try {
                      $installer = null;
                      // Try Joomla 4+ dependency injection container first
                      if (method_exists('Joomla\CMS\Factory', 'getContainer')) {
                          try {
                              $container = Factory::getContainer();
                              if ($container && method_exists($container, 'get')) {
                                  $installer = $container->get(Installer::class);
                              }
                          } catch (Exception $e) {
                              // Container approach failed, continue to fallback
                          }
                      }
                      
                      // Fallback: manual instantiation for all versions
                      if (!$installer) {
                          $installer = new Installer();
                          if (method_exists($installer, 'setDatabase')) {
                              $installer->setDatabase(Factory::getDbo());
                          }
                      }
                      
                      $installer->uninstall($type, $identifier, $cid);
                      
                  } catch (Exception $e) {
                      $app = Factory::getApplication();
                      if (method_exists($app, 'enqueueMessage')) {
                          $app->enqueueMessage('Error uninstalling extension: ' . $e->getMessage(), 'warning');
                      }
                  }
              }
            }
          }
        }
	}

  public static function getSuperUser()
  {
      $db = Factory::getDBO();
      $query = $db->getQuery(true)->select('user_id')->from('#__user_usergroup_map')->where('group_id=' . $db->quote(8));
      $db->setQuery($query);
      $results = $db->loadColumn();
      return  $results[0];
  }

  public function generic_update_query($database_name, $updatefieldsarray){  
      $db = Factory::getDbo();

      $query = $db->getQuery(true);
      foreach ($updatefieldsarray as $key => $value)
      {
        $database_fileds[] = $db->quoteName($key) . ' = ' . $db->quote($value);
      }
      $query->update($db->quoteName($database_name))->set($database_fileds)->where($db->quoteName('id')." = 1");
      $db->setQuery($query);
      $db->execute();
  }

	
  function onExtensionBeforeUninstall($id)
  {
    $app = Factory::getApplication();
    $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
    $post = ($input && $input->post) ? $input->post->getArray() : [];
    $tables = Factory::getDbo()->getTableList();
    $result = (new MoUserSyncUtility)->loadDBValues('#__extensions', 'loadColumn', 'extension_id', 'element', 'COM_MINIORANGE_USERSYNC');
    $tables = Factory::getDbo()->getTableList();
    $tab = 0;
    $tables = Factory::getDbo()->getTableList();
    $lang = Factory::getLanguage();
    $lang->load('plg_system_miniorangeusersync',JPATH_ADMINISTRATOR);
    foreach ($tables as $table) {
      if (strpos($table, "miniorange_user_sync_config") !== FALSE)
        $tab = $table;
    }
    if ($tab === 0)
      return;
    if ($tab) {
      $fid = (new MoUserSyncUtility)->_load_db_values('#__miniorange_usersync_customer');
      $fid = $fid['uninstall_feedback'];
      $tpostData = $post;
      $customerResult = (new MoUserSyncUtility)->_load_db_values('#__miniorange_usersync_customer');
      $dVar=new JConfig();
      $check_email = $dVar->mailfrom;
      $feedback_email = !empty($customerResult ['admin_email']) ? $customerResult ['admin_email'] :$check_email;

      if (1) {
        if ($fid == 0) {
          foreach ($result as $results) {
            if ($results == $id) {?>
            <link rel="stylesheet" type="text/css" href="<?php echo Uri::base();?>/components/com_miniorange_usersync/assets/css/miniorange_user_sync.css" />
              <div class="form-style-6 " style="width:35% !important; margin-left:33%; margin-top: 4%;">
                <h1> <?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_14');?></h1>
                <form name="f" method="post" action="" id="mojsp_feedback" style="background: #f3f1f1; padding: 10px;">
                  <h3><?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_15');?> </h3>
                  <input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>
                  <div>
                  <p style="margin-left:2%">
                  <?php
                    $deactivate_reasons = array(
                      Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_1'),
                      Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_2'),
                      Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_3'),
                      Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_4'),
                      Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_5'),
                      Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_6'),
                      Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_7')
                    );
                    foreach ($deactivate_reasons as $deactivate_reasons) { ?>
                      <div class="radio" style="padding:1px;margin-left:2%">
                        <label style="font-weight:normal;font-size:14.6px;font-family: cursive;" for="<?php echo $deactivate_reasons; ?>">
                        <input type="radio" name="deactivate_plugin" value="<?php echo $deactivate_reasons; ?>" required>
                        <?php echo $deactivate_reasons; ?></label>
                      </div>

                    <?php } ?>
                    <br>

                    <textarea id="query_feedback" name="query_feedback" rows="4" style="margin-left:3%;width: 100%" cols="50" placeholder="<?php echo Text::_('PLG_SYSTEM_USER_SYNC_WRITE_QUERY'); ?>"></textarea><br><br><br>
                      <tr>
                        <td width="20%"><strong><?php echo Text::_('PLG_SYSTEM_USER_SYNC_EMAIL'); ?><span style="color: #ff0000;">*</span>:</strong></td>
                        <td><input type="email" name="feedback_email" required value="<?php echo $feedback_email; ?>" placeholder="<?php echo Text::_('PLG_SYSTEM_USER_SYNC_ENTER_EMAIL'); ?>" style="width:80%"/></td>
                      </tr>

                      <?php
                        foreach ($tpostData['cid'] as $key) { ?>
                          <input type="hidden" name="result[]" value=<?php echo $key ?>>
                        <?php } ?>
                        <br><br>
                        <div class="mojsp_modal-footer" style="text-align:center">
                          <input style="cursor: pointer;font-size: large;" type="submit" name="miniorange_feedback_submit" class="mo_boot_btn mo_blue_buttons" value="<?php echo Text::_('PLG_SYSTEM_USER_SYNC_SUBMIT_BTN'); ?>"/>
                        </div>
                      </div>
                    </form>
                    <form name="f" method="post" action="" id="mojspfree_feedback_form_close">
                        <input type="hidden" name="mojspfree_skip_feedback" value="mojspfree_skip_feedback"/>
                        <div style="text-align:center">
                            <button class="mo_boot_btn mo_blue_buttons" onClick="skipUserSyncForm()">Skip Feedback</button>
                        </div>
                        <?php
                            foreach ($tpostData['cid'] as $key) { ?>
                                <input type="hidden" name="result[]" value=<?php echo $key ?>>
                            <?php }
                        ?>
                    </form>
                    </div>
                    <script src="https://code.jquery.com/jquery-3.6.3.js"></script>
                    <script>
                      jQuery('input:radio[name="deactivate_plugin"]').click(function () {
                        var reason = jQuery(this).val();
                        jQuery('#query_feedback').removeAttr('required')
                        if (reason === '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_1'); ?>') {
                          jQuery('#query_feedback').attr("placeholder",'<?php echo  Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_8'); ?>');
                        } else if (reason === '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_2'); ?>') {
                          jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_10'); ?>');
                        } else if (reason === '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_4'); ?>'){
                          jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_11'); ?>');
                        }else if (reason === '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_6'); ?>'){
                          jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_9'); ?>');
                        } else if (reason === '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_7'); ?>' || reason === '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_5'); ?>' ) {
                          jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_12'); ?>');
                          jQuery('#query_feedback').prop('required', true);
                        } else if (reason === '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_3'); ?>') {
                          jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_USER_SYNC_FEEDBACK_13'); ?>');
                        }
                      });

                      function skipUserSyncForm(){
                        jQuery('#mojspfree_feedback_form_close').submit();
                      }
                    </script>
                    <?php
                    exit;
                  }
              }
            }
        }
    }
  }
  }

