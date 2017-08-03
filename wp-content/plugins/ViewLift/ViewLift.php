<?php
/*
  Plugin Name: ViewLift
  Description: This plugin allows you to easily upload videos using the ViewLift Player. Uploaded videos can be used by opting 'update' from Pages and Posts options in WordPress Menu.
  Author: ViewLift
  Version: 3.76
 */
define( 'AV_PLUGIN_DIR', dirname( __FILE__ ) );
add_action('admin_menu', 'av_plugin_setup_menu');
add_action( 'admin_enqueue_scripts', 'av_admin_enqueue_scripts' );
add_filter( 'media_upload_tabs', 'av_media_menu' );
add_action( 'admin_menu', 'av_media_add_video_box' );
add_action( 'admin_init', 'register_av_plugin_settings' );

require_once( AV_PLUGIN_DIR . '/include/admin.php' );
require_once( AV_PLUGIN_DIR . '/include/media.php' );

function av_plugin_setup_menu() {
    add_menu_page('ViewLift Plugin Page', 'ViewLift Platform', 'manage_options', 'av-plugin', 'av_handle_post');
    add_menu_page('ViewLift Config Page', 'ViewLift Config', 'manage_options', 'av-plugin-settings', 'av_settings_post');
 
}

function register_av_plugin_settings() {
    //register our settings
    register_setting( 'av-plugin-settings-group', 'av_access_key' );
    register_setting( 'av-plugin-settings-group', 'av_secret_key' );
    //register_setting( 'av-plugin-settings-group', 'av_bucket_name' );
    //register_setting( 'av-plugin-settings-group', 'av_server_url' );
}

function av_settings_post(){ ?>
<div class="wrap">
<h1>ViewLift Settings</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'av-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'av-plugin-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Access Key</th>
            <td><input type="text" name="av_access_key" value="<?php echo esc_attr( get_option('av_access_key') ); ?>" style="width:400px;" /></td>
        </tr>
         
        <tr valign="top">
            <th scope="row">Secret Key</th>
            <td><input type="text" name="av_secret_key" value="<?php echo esc_attr( get_option('av_secret_key') ); ?>" style="width:400px;" /></td>
        </tr>
        
     <!-- <tr valign="top">
            <th scope="row">Bucket Name</th>
            <td><input type="text" name="av_bucket_name" value="<?php echo esc_attr( get_option('av_bucket_name') ); ?>" style="width:400px;" /></td>
        </tr>

        <tr valign="top">
            <th scope="row">Server URL</th>
            <td><input type="text" name="av_server_url" value="<?php echo esc_attr( get_option('av_server_url') ); ?>" style="width:400px;" /></td>
        </tr>-->

    </table>
    
    <?php submit_button(); ?>

</form>
</div>

<?php
}

function av_handle_post() { 
  if(esc_attr( get_option('av_access_key'))=='' || esc_attr( get_option('av_secret_key'))==''){
      echo '<div class="wrap" style="padding-top:40px;">Please fill the ViewLift config details</div>';
  }else{
?>
 <div class="wrap">
        <h2>Upload Video</h2>       
        <form  method="post" action="" enctype="multipart/form-data">            
            <div><input type="file" accept="video/*" name="fileToUpload" id="fileToUpload"></div>
            <div> OR </div>
            <div>Your URL: <input type="text" name="videourl" style="width: 400px;" /></div> 
            <div><input type="submit" value="Upload" name="video_upload"></div>
        </form>
    </div>
	<?php   
	
	
	// First check if the file appears on the _FILES array
    if (isset($_POST["video_upload"]) || isset($_POST["videourl"])) {
		$dir = explode('plugins',dirname(__FILE__));
		if(!empty($_POST["videourl"])){
			$fileInfo = explode('/',$_POST["videourl"]);
			$l = count($fileInfo);
			$filename = $fileInfo[$l-1];
			$dirM = $fileInfo[$l-2];
			$dirY = $fileInfo[$l-3];
		}
		$dir = str_replace('\\','/',$dir[0]."uploads/".$dirY."/".$dirM."/");
        
		if(isset($filename) && file_exists($dir.$filename)){
			$post_id = get_post_id_by_meta_key_and_value('_wp_attached_file', $dirY."/".$dirM."/".$filename);
			if($post_id){
				$data = get_post_meta( $post_id, '_wp_attachment_metadata', true );
				$_FILES['fileToUpload']['size'] = $data['filesize'];
				$_FILES['fileToUpload']['type'] = $data['fileformat'];

			}
		
			$_FILES['fileToUpload']['tmp_name'] = $dir.$filename;
			$_FILES['fileToUpload']['name'] = $filename;
			$_FILES['fileToUpload']['error'] = 0;
		}
		
        $settingsErr = array();
        if(esc_attr( get_option('av_access_key'))==''){
             $settingsErr[] = 'Access key is blank in player settings';
        }
        if(esc_attr( get_option('av_secret_key'))==''){
             $settingsErr[] = 'Secret key is blank in player settings';
        }
        /*if(esc_attr( get_option('av_bucket_name'))==''){
             $settingsErr[] = 'Bucket name is blank in player settings';
        }*/

        if(!empty($settingsErr)){
           echo '<ul>';
           foreach($settingsErr as $err){
              echo '<li>'.$err.'</li>';
           }
           echo '</ul>';
           exit();
        }

        include('S3.php');
        if (!defined('awsAccessKey'))
            define('awsAccessKey', esc_attr( get_option('av_access_key')));
        if (!defined('awsSecretKey'))
            define('awsSecretKey', esc_attr( get_option('av_secret_key')));

        //$bucket = esc_attr( get_option('av_bucket_name'));
        $bucket = 'winnersview';

        S3::setAuth(awsAccessKey, awsSecretKey);
	
        if (isset($_FILES['fileToUpload']['tmp_name'])) {

            function testcurl($url, $data, $methodPost) {
				$url = 'http://staging3.partners.viewlift.com'.$url;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/x-www-form-urlencoded', 'Content-Type: application/x-www-form-urlencoded'));
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, $methodPost);
                if ($methodPost == 0) {
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $server_output = curl_exec($ch);
                curl_close($ch);
                return json_decode($server_output, TRUE);
            }

            $region = 'eu-central-1';
            $urls = ['http' => 'http://s3-' . $region . '.amazonaws.com/' . $bucket,
                'https' => 'https://s3-' . $region . '.amazonaws.com/' . $bucket];

            S3::setBucket($bucket);
            S3::setUrls($urls);
            S3::setAcl(S3::ACL_PRIVATE);
            S3::setStorage(S3::STORAGE_CLASS_STANDARD);
            $dir = 'MezzFiles/2017/07/';
			$filename = time() . '_' . $_FILES['fileToUpload']['name'];
            $responce = S3::putObject($_FILES['fileToUpload']['tmp_name'], $dir . $filename);
			
            if ($responce['code'] == 200) {
                $data = array(
                    "site" => "demo",
                    "filename" => $filename,
                    "filesize" => $_FILES["fileToUpload"]["size"],
                    "review" => 'true',
                    "author" => "kalyan+lax@viewlift.com",
                    "email" => "kalyan+lax@viewlift.com"
                );
                // generate GUID
                $url = '/content/generateGuid';

                $methodPost = 1;

                $data = array(
                    'site' => 'demo',
                    'filename' => $filename,
                    'filesize' => $_FILES["fileToUpload"]["size"],
                    'review' => 'true',
                    'author' => 'kalyan%2Blax%40viewlift.com',
                    'email' => 'kalyan%2Blax%40viewlift.com'
                );

                $response1 = testcurl($url, $data, $methodPost);
                //echo $response1['filmList'][0]['uuid'];
                //Change status
				$printArray['generateGuid-Request1'] = $data;
				$printArray['Response1'] = $response1;
					
                if (isset($response1['filmList'][0]['uuid'])) {

                    $url = '/content/changeStatus';

                    $methodPost = 1;

                    $data = array(
                        'guid' => $response1['filmList'][0]['uuid'],
                        'video_status' => 'processing',
                    );

                    $response2 = testcurl($url, $data, $methodPost);
                }
				$printArray['changeStatus-Request2'] = $data;
				$printArray['Response2'] = $response2;	
				
                if ($response2['status'] == 'processing') {

                    $url = '/content/completeUpload';

                    $methodPost = 1;

                    $data = array(
                        'uuid' => $response1['filmList'][0]['uuid'],
                        'filename' => $filename,
                        'filesize' => $_FILES["fileToUpload"]["size"],
                        'review' => 'true',
                        'last_modified' => '',
                        'site' => 'demo'
                    );

                    $response3 = testcurl($url, $data, $methodPost);
                    echo 'Video Uploaded Successfully';
					//echo $response1['filmList'][0]['uuid'];
					$printArray['completeUpload-Request3'] = $data;
					$printArray['Response3'] = $response3;	
					$printArray['url'] = $url;	
                }
				
                /*if ($response3['status'] == 'COMPLETED') {

					$url = 'http://staging3.partners.viewlift.com/demo/content/publish?email=kalyan%2Blax%40viewlift.com';
					$data = '{"film":[{"id":"'.$response1['filmList'][0]['uuid'].'"}],"selectedSocial":[]}';
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/x-www-form-urlencoded', 'Content-Type: application/json'));
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$server_output = curl_exec($ch);
					curl_close($ch);
					$response4 = json_decode($server_output, TRUE);
					
					$printArray['publish-Request4'] = $data;
					$printArray['Response4'] = $response4;
                }*/
				//echo "<pre>";
				//print_r($printArray);
            }
        }
    }
  } //end for plugin configuration check  
}

add_shortcode("VL", "av_process_shortcode");
 
function av_process_shortcode($atts){
        $a = shortcode_atts(array('id'=>'-1'), $atts);
        // No ID value
        if(strcmp($a['id'], '-1') == 0){
                return "";
        }
        $uid=$a['id']; 
		$url = 'http://release.demo.viewlift.com/embed/player?filmId='.$uid;
		if($uid && 1!=1){
			$url = "http://staging3.partners.viewlift.com/demo/content/". $uid;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/x-www-form-urlencoded', 'Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 0);
			curl_setopt($ch, CURLOPT_HTTPGET, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec($ch);
			curl_close($ch);
			$list = json_decode($server_output, TRUE);
			$url = $list['films']['film'][0]['embed'];
		}
	
        $iframe = '<script src="https://embed.snagfilms.com/api/embed/player.js"></script><div class="vl-embed-wrapper"><div class="vl-embed-container">';
		$iframe .= '<iframe src="'. $url .'" frameborder="0" allowfullscreen="true" width="100%" height="100%" name="playerframe_1" id="playerframe_1"></iframe>';
		$iframe .= '</div></div>';

        if(esc_attr( get_option('av_access_key'))=='' || esc_attr( get_option('av_secret_key'))==''){
            return '';
        }else{    
            return $iframe;
        }
}

if (!function_exists('get_post_id_by_meta_key_and_value')) {
    /**
     * Get post id from meta key and value
     * @param string $key
     * @param mixed $value
     * @return int|bool
     * @author Kali <david.martensson@gmail.com>
     */
    function get_post_id_by_meta_key_and_value($key, $value) {
        global $wpdb;
        $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$key."' AND meta_value='".$value."'");
        if (is_array($meta) && !empty($meta) && isset($meta[0])) {
            $meta = $meta[0];
        }       
        if (is_object($meta)) {
            return $meta->post_id;
        }
        else {
            return false;
        }
    }
}


?>