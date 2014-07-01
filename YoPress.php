<?php
/*
	Plugin Name: YoPress
	Plugin URI: http://jason.stallin.gs/yopress
	Description: Yo your subscribers when you publish a new post. 
	Author: Jason Stallings
	Version: 0.1.0
	Author URI: http://jason.stallin.gs
 */


add_action('admin_menu', 'yopress_create_menu');
add_action( 'draft_to_publish', 'yopress_do_once_on_publish' );


function yopress_do_once_on_publish( $post ) 
{
  	
    if ( $post->post_type != 'post' ) return;

    $post_id = $post->ID;
	
    if ( !get_post_meta( $post_id, 'yosent', $single = true ) ) 
	{
     
        update_post_meta( $post_id, 'yosent', true );
	  
		$url = 'http://api.justyo.co/yoall/';
	  
		$fields_string="api_token=" . get_option('yopress_apikey') . "&";

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	
		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
	  
    }
}

function yopress_create_menu() 
{

	//create new top-level menu
	$wpeallowheartbeat_settings_page=add_submenu_page('options-general.php', 'YoPress', 'YoPress', 'administrator', __FILE__, 'youpress_settings_page');

	//call register settings function
	add_action( 'admin_init', 'register_yopress_settings' );
}



function register_yopress_settings() 
{
	//register our settings
	register_setting( 'yopress-settings-group', 'yopress_apikey' );
}


function youpress_settings_page() 
{
?>
	<div class="wrap">
		<h2>YoPress Settings</h2>
		<p>
			Signup for an API key here: <a href="http://yoapi.justyo.co">yoapi.justyo.co</a><br>
			Put your API key below and hit save changes. <br>
			Then tell your visitors to Yo your website's username, or use <a href="http://button.justyo.co/">button.justyo.co</a> to generate a button. <br>
			Now when you publish a new post your visitors will get Yo'd!<br>
		</p>

		<form method="post" action="options.php">
    		<?php settings_fields( 'yopress-settings-group' ); ?>
    		<?php do_settings_sections( 'yopress-settings-group' ); ?>
    		<table class="form-table">
        		<tr valign="top">
				  <th scope="row">Please input your Yo API key:</th>
        			<td><input type="text" name="yopress_apikey" value="<?php echo get_option('yopress_apikey'); ?>" /></td>
        		</tr>
    		</table>
    
   		<?php submit_button(); ?>

		</form>
	</div>
<?php 
}
