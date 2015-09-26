<?php
/*
	Plugin Name: YoPress
	Plugin URI: http://jason.stallin.gs/yopress
	Description: Yo your subscribers when you publish a new post. 
	Author: Jason Stallings
	Version: 0.2.2
	Author URI: http://jason.stallin.gs
	Text Domain: yopress
 */


add_action( 'admin_menu', 'yopress_create_menu' );
add_action( 'draft_to_publish', 'yopress_do_once_on_publish' );
add_action( 'init', 'yopress_create_subscribers_list' );
add_action( 'plugins_loaded', 'yopress_pageload' );

function yopress_create_subscribers_list() {
	register_post_type( 'yopress_subscribers',
		array(
			'labels' => array(
				'name' => __( 'Subscribers', 'yopress' ),
				'singular_name' => __( 'Subscriber', 'yopress' )
			),
		'public' => false,
		'has_archive' => false,
		)
	);
}


function yopress_do_once_on_publish( $post ) 
{
  	
    if ( $post->post_type != 'post' ) return;

    $post_id = $post->ID;
	
    if ( !get_post_meta( $post_id, 'yosent', $single = true ) ) 
	{
     
        update_post_meta( $post_id, 'yosent', true );
	  
		$url = 'http://api.justyo.co/yoall/';

		$permalink = get_permalink($post_id);
	  
		$fields_string="api_token=" . get_option('yopress_apikey') . "&" . "link=" . $permalink;

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

function yopress_pageload() 
{
   	$username = $_GET['username'];

    	if (isset($_GET['username'])) 
	{
		$exists=get_page_by_title( $username, NULL, "yopress_subscribers" );
		
		if (!$exists)
		{
		 	$youser = array(
		 	 'post_title'    => $username,
 			 'post_content'  => $username,
 			 'post_status'   => 'publish',
 		 	 'post_author'   => 1, 
		 	 'post_type'	=> 'yopress_subscribers'
			);
			wp_insert_post( $youser );
		}
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
			Signup for an API key at <a href="http://yoapi.justyo.co">yoapi.justyo.co</a>, for "Callback URL" put in your homepage with a trailing slash.<br>
			Put your API key below and hit save changes. <br>
			Then tell your visitors to Yo your website's username, or use <a href="http://button.justyo.co/">button.justyo.co</a> to generate a button. <br>
			Now when you publish a new post your visitors will get a Yo with the link!<br>
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
		<h2>Subscribers</h2>
		<ul>
			<?php
			$args = array( 'posts_per_page' => -1, 'post_type' => 'yopress_subscribers');

			$subscribers = get_posts( $args );

			foreach ( $subscribers as $subscriber )
			{ ?>
				<li>
					<?php echo $subscriber->post_title; ?>
				</li>
			<?php 
			} 
			?>

		</ul>
	</div>
<?php 
}