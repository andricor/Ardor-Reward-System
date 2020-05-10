<?php
/**
 * Plugin Name: Ardor Reward System
 * Plugin URI: https://ardor.rocks
 * Description: Ardor Reward System allows you to reward users with an asset from the Ardor Blockchain.
 * Version: 1.0
 * Author: TheWireMaster
 * Author URI: https://ardor.rocks
 */
 /**
 * Test if a WordPress plugin is active
 */
 
if ( is_plugin_active('mycred/mycred.php') && is_plugin_active('buddypress/bp-loader.php') ) {
    /*include 'options.php';*/
    /** Step 2 (from text above). */
    add_action( 'admin_menu', 'ardor_reward_menu' );
    add_action( 'admin_init', 'register_mysettings' );

    /** Step 1. */
    function ardor_reward_menu() {
    	add_options_page( 'Ardor Reward System Options', 'Ardor Reward System', 'manage_options', 'ardor-reward-system', 'ardor_reward_options' );
    }
    
    function register_mysettings() { // whitelist options
          register_setting( 'myoption-group', 'ardor_node_url' );
          register_setting( 'myoption-group', 'account_passphrase' );
          register_setting( 'myoption-group', 'asset_id' );
          register_setting( 'myoption-group', 'payout_t' );
          register_setting( 'myoption-group', 'cron_reward' );
        }

    /** Step 3. */
    function ardor_reward_options() {
	    if ( !current_user_can( 'manage_options' ) )  {
		    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	    }
    	echo '<div class="wrap">';
	    echo '<h1>Ardor Reward System Options</h1>';
	    echo '<p>The Ardor Reward System allows you to reward your users with an Asset on the Ardor Blockchain.<br>
	            You need a couple of steps before the system is fully functional:<br>
	            1 - Create an Asset on Ardor blockchain. To know more about it head <a href="https://medium.com/@plumapiedra/how-to-issue-an-asset-on-ardor-in-5-mins-d19e48215bf2" target="_blank">here</a><br>
	            2 - At the moment the plugin does not allow the dynamic encrease of Asset shares. Will be implemented at a later stage, so make sure you have enough Asset shares to send to your users.<br>
	            3 - You need some Ignis in your wallet to pay for the transactions when sending the asset to your users.<br>
	            4 - 1 point equals 1 asset unit. Therefore create the myCred hooks keeping this in mind.<br>
	            5 - The Ardor Reward System can be used with Livenet or Testnet. It all depends on the Ardor Node URL. The format of the URL should be <strong>https://testardor.jelurida.com/nxt</strong> (Jelurida testnet node) or <strong>https://node4.ardor.tools/nxt</strong> (MRV Live node). Make sure to add the /nxt at the end.<br></p>';
	    echo '<form method="post" action="options.php">';
	    settings_fields( 'myoption-group' );
	    do_settings_sections( 'myoption-group' ); ?>
        <table class="form-table">
            <tr valign="top">
            <th scope="row">Ardor Node URL</th>
            <td><input type="text" name="ardor_node_url" value="<?php echo esc_attr( get_option('ardor_node_url') ); ?>" /></td>
            </tr>
             
            <tr valign="top">
            <th scope="row">Account Passphrase</th>
            <td><input type="password" name="account_passphrase" value="<?php echo esc_attr( get_option('account_passphrase') ); ?>" /></td>
            </tr>
            
            <tr valign="top">
            <th scope="row">Asset ID</th>
            <td><input type="text" name="asset_id" value="<?php echo esc_attr( get_option('asset_id') ); ?>" /></td>
            </tr>
            
            <tr valign="top">
            <th scope="row">Payout Threshold</th>
            <td><input type="number" name="payout_t" value="<?php echo esc_attr( get_option('payout_t') ); ?>" /></td>
            </tr>
            
            <tr valign="top">
           <!-- <th scope="row">When the reward will run, intercal in seconds</th> -->
           <th scope="row">When the reward will run, choose interval</th>
           <!-- <td><input type="number" name="cron_reward" value="<?php echo esc_attr( get_option('cron_reward') ); ?>" /></td> -->
            <td><select id="cron_reward" name="cron_reward" >
                <!--<option value="hourly">Hourly</option>-->
                <option value="hourly" <?php if (get_option('cron_reward') == 'hourly') echo "selected"; ?>>Hourly</option>
                <!--<option value="twicedaily">Twice Daily</option>-->
                <option value="twicedaily" <?php if (get_option('cron_reward') == 'twicedaily') echo "selected"; ?>>Twice Daily</option>
                <!--<option value="daily">Daily</option>-->
                <option value="daily" <?php if (get_option('cron_reward') == 'daily') echo "selected"; ?>>Daily</option>
              </select>
            </td>
            </tr>
        </table>
	   
    <?php	   
	    submit_button();
        echo '</form>';
	    echo '</div>';
    }
  
} else {
  trigger_error('MyCred must be active for Ardor Reward System to work',E_USER_ERROR);
}

register_activation_hook( __FILE__, 'ars_create_db' );
function ars_create_db() {
    	global $wpdb;
    	$charset_collate = $wpdb->get_charset_collate();
    	$table_name = $wpdb->prefix . 'pointspaid';
    
    	$sql = "CREATE TABLE $table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		user_id bigint(20),
    		user_nicename varchar(50),
    		wallet longtext,
    		points int(11),
    		paid int(11),
    		PRIMARY KEY (id)
    	) $charset_collate;";
    
    	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    	dbDelta( $sql );
    }
    
register_activation_hook( __FILE__, 'ars_add_ardor_account_field' );
function ars_add_ardor_account_field() {
        if(!xprofile_get_field_id_from_name('Ardor Account ID')){
            xprofile_insert_field(
                array (
                           'field_group_id'  => 1,
                           'name'            => 'Ardor Account ID',
                           'field_order'     => 1,
                           'is_required'     => false,
                           'type'            => 'textbox'
                    )
                );
        }
    
    }

/**
 * Hook into options page after save.
 */
function options_page_after_save( $old_value, $new_value ) {
    global $new_value2;
    $new_value2 = $new_value;
    //write_log($old_value);
    //write_log($new_value);
    if ( $old_value != $new_value ) {
        
        function ardor_reward_cron( $schedules ) {
            global $new_value2;
            $schedules['every_x_seconds'] = array(
                    'interval'  => $new_value2,
                    'display'  => __('ardor_reward_cron')
                    //'display'  => 'ardor_reward_cron'
            );
        //write_log($schedules['every_x_seconds']['interval']);
        //write_log($schedules['every_x_seconds']['display']);
            return $schedules;
        }
        add_filter( 'cron_schedules', 'ardor_reward_cron' );
        
        // Schedule an action if it's not already scheduled
        if ( wp_next_scheduled( 'ardor_reward_cron_hook' ) ) {
            wp_clear_scheduled_hook( 'ardor_reward_cron_hook');
        }
         wp_schedule_event( time(), $new_value, 'ardor_reward_cron_hook' );
        
        // Hook into that action that'll fire according to schedule
     
        add_action( 'ardor_reward_cron_hook', 'distribute_asset_func' );
        
 }

}
add_action( 'update_option_cron_reward', 'options_page_after_save', 10, 2 );
add_action( 'ardor_reward_cron_hook', 'distribute_asset_func' );

function distribute_asset_func() {
    //write_log('IN THE FUNCTION');
            global $options;
            global $wpdb;
            /*foreach ($options as $value) {
                if (get_option($value['id']) === FALSE) {
                    $$value['id'] = $value['std'];
                }
                else {
                    $$value['id'] = get_option( $value['id'] );
                }
            }*/
            //$options = get_option('myoption-group', array() );
            $options = get_option('myoption-group');
            $table_name = $wpdb->prefix . 'pointspaid';
            $users_table = $wpdb->prefix . 'users';
            $bp_profile_table = $wpdb->prefix . 'bp_xprofile_data';
            $bp_profile_fields = $wpdb->prefix . 'bp_xprofile_fields';
            $usermeta_table = $wpdb->prefix . 'usermeta';
            
            include_once("wp-config.php");
            include_once("wp-includes/wp-db.php");
            
            $url = get_option('ardor_node_url');
            $passphrase = get_option('account_passphrase');
            $asset_id = get_option('asset_id');
            $payout_t = get_option('payout_t');
            $uid = "";
            
            // insert statement below overwrites all table. replaced with following one
            // insert into pointspaid (user_id, user_nicename) select t.id, t.user_nicename from wp_23users t on duplicate key update user_nicename=t.user_nicename;
            
            //insert missing users in the pointspaid table
            $sql1 = "insert into $table_name (user_id, user_nicename) select t.id, t.user_nicename from $users_table t where t.id in (select id from $users_table where id not in (select user_id from $table_name))";
            $resultsql1 = $wpdb->get_results($sql1);
            //update wallet info in pointspaid
            $sql2 = "update $table_name inner join $bp_profile_table on $table_name.user_id = $bp_profile_table.user_id set $table_name.wallet = $bp_profile_table.value where $bp_profile_table.field_id=(select id from $bp_profile_fields where name='Ardor Account ID')";
            $resultsql2 = $wpdb->get_results($sql2);
            //write_log($sql2);
            //update points collect in pointspaid
            $sql3 = "update $table_name inner join $usermeta_table on $table_name.user_id = $usermeta_table.user_id set $table_name.points = $usermeta_table.meta_value where $usermeta_table.meta_key='mycred_default'";
            $resultsql3 = $wpdb->get_results($sql3);
            
            $sql = "SELECT user_id, wallet, points, paid FROM $table_name";
            $result = $wpdb->get_results($sql);
            //write_log($result);
            //write_log($wpdb->num_rows);
            
            if (($wpdb->num_rows) > 0) {
                //write_log('OH OH');
                
                //check asset decimals
                $data_dec="requestType=getAsset&asset=$asset_id";
            		$ch_dec = curl_init();
                            curl_setopt($ch_dec, CURLOPT_URL,$url);
                            curl_setopt($ch_dec, CURLOPT_POSTFIELDS, $data_dec);
                            curl_setopt($ch_dec, CURLOPT_RETURNTRANSFER, true);
                            $server_output_dec = curl_exec($ch_dec);
                            $curl_error_dec = curl_error($ch_dec);
                            curl_close ($ch_dec);
                            $jsoncheck_dec = json_decode($server_output_dec, true);
                            $decimals = $jsoncheck_dec['decimals'];
                            $nqt_multi = "1" . str_repeat("0",$decimals);
                            //write_log($nqt_multi);
                
                // output data of each row
                foreach( $result as $row ) {
            	$topay=$row->points - $row->paid;
            	$topayround= floor($topay/$payout_t)*$payout_t;
                    if ($topayround >= $payout_t) {
            		$wallet=$row->wallet;
            		$uid=$row->user_id;
            		$topayNQT=$topayround*$nqt_multi;
            //check if new account
            		$data="requestType=getAccount&account=$wallet";
            		$ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL,$url);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $server_output = curl_exec($ch);
                            $curl_error = curl_error($ch);
                            curl_close ($ch);
                            $jsoncheck = json_decode($server_output, true);
            		$errorc=0;
            		if (isset($jsoncheck['errorCode'])) {
            			$errorc = $jsoncheck['errorCode'];
            			}
            		if ($errorc != 5) {
            //transfer asset if account is not new - check rate
            		$data="requestType=transferAsset&secretPhrase=$passphrase&chain=ignis&asset=$asset_id&recipient=$wallet&quantityQNT=$topayNQT&feeNQT=-1&feeRateNQTPerFXT=-1&deadline=60";
            		$ch = curl_init();
            		curl_setopt($ch, CURLOPT_URL,$url);
            		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            		$server_output = curl_exec($ch);
            		$curl_error = curl_error($ch);
            		curl_close ($ch);
            		$jsoncheck = json_decode($server_output, true);
            		$rate = $jsoncheck['bundlerRateNQTPerFXT'] * 0.01;
            //transfer asset with best rate
            		$data="requestType=transferAsset&secretPhrase=$passphrase&chain=ignis&asset=$asset_id&recipient=$wallet&quantityQNT=$topayNQT&feeNQT=$rate&deadline=60";
            		$ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL,$url);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $server_output = curl_exec($ch);
                            $curl_error = curl_error($ch);
                            //write_log($server_output);
                            curl_close ($ch);	
            		$jsoncheck = json_decode($server_output, true);
            //update database
            		if (isset($jsoncheck['transactionJSON'])) {
            			$sqlupd = "UPDATE $table_name set paid=ifnull(paid,0)+$topayround where user_id='$uid'";
            			$resultupd = $wpdb->get_results($sqlupd);
            			}
            	}
            	}
                }
            } else {
                echo "0 results";
            }
            //$conn->close();
    
}

function my_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=ardor-reward-system.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'my_plugin_settings_link' );

if (!function_exists('write_log')) {

    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

}
