<?php
/*
  Plugin Name: ViewLift
  Description: This plugin allows you to easily upload videos using the ViewLift Player. Uploaded videos can be used by opting 'update' from Pages and Posts options in WordPress Menu.
  Author: ViewLift
  Version: 3.76
 */

 
define('VIEWLIFT_PLUGIN_DIR', dirname(__FILE__));
add_action('admin_menu', 'VIEWLIFT_plugin_setup_menu');
add_action('admin_enqueue_scripts', 'VIEWLIFT_admin_enqueue_scripts');
add_action('admin_print_styles', 'VIEWLIFT_stylesheet');
add_filter('media_upload_tabs', 'VIEWLIFT_media_menu');
add_action('admin_menu', 'VIEWLIFT_media_add_video_box');
add_action('admin_init', 'register_VIEWLIFT_plugin_settings');

require_once( VIEWLIFT_PLUGIN_DIR . '/include/admin.php' );
require_once( VIEWLIFT_PLUGIN_DIR . '/include/media.php' );
require_once( VIEWLIFT_PLUGIN_DIR . '/include/S3.php' );

function VIEWLIFT_plugin_setup_menu() {

    add_menu_page('viewlift Config Page', 'viewlift Config', 'manage_options', 'viewlift-plugin-settings', 'viewlift_settings_post');
}

function register_VIEWLIFT_plugin_settings() {
    //register our settings
    register_setting('viewlift-plugin-settings-group', 'viewlift_access_key');
    register_setting('viewlift-plugin-settings-group', 'viewlift_secret_key');
    register_setting('viewlift-plugin-settings-group', 'viewlift_bucket_name');
    register_setting('viewlift-plugin-settings-group', 'viewlift_server_url');
}

function viewlift_settings_post() {
    ?>
    <div class="wrap">
        <h1>ViewLift Settings</h1>
		<?php if( isset($_GET['settings-updated']) ) { ?>
			<div id="message" class="updated">
				<p><strong><?php _e('Settings saved.') ?></strong></p>
			</div>
		<?php } ?>
        <form method="post" action="options.php">
            <?php settings_fields('viewlift-plugin-settings-group'); ?>
            <?php do_settings_sections('viewlift-plugin-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Access Key <span style="color:red">*</span></th>
                    <td><input type="text" name="viewlift_access_key" value="<?php echo esc_attr(get_option('viewlift_access_key')); ?>" style="width:400px;" required />			
					</td>
                </tr>

                <tr valign="top">
                    <th scope="row">Secret Key <span style="color:red">*</span></th>
                    <td><input type="text" name="viewlift_secret_key" value="<?php echo esc_attr(get_option('viewlift_secret_key')); ?>" style="width:400px;" required /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Bucket Name <span style="color:red">*</span></th>
                    <td><input type="text" name="viewlift_bucket_name" value="<?php echo esc_attr(get_option('viewlift_bucket_name')); ?>" style="width:400px;" required /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Server URL <span style="color:red">*</span></th>
                    <td><input type="text" name="viewlift_server_url" value="<?php echo esc_attr(get_option('viewlift_server_url')); ?>" style="width:400px;" required /></td>
                </tr>

            </table>

            <?php submit_button(); ?>

        </form>
    </div>

    <?php
}
function plugin_options_validate($input) {
    $options = get_option('plugin_options');
    $options['text_string'] = trim($input['text_string']);
    if(!preg_match('/^[a-z0-9]{32}$/i', $options['text_string'])) {
        $options['text_string'] = '';
    }
    return $options;
}

add_shortcode("VL", "viewlift_process_shortcode");

function VIEWLIFT_process_shortcode($atts) {
    $a = shortcode_atts(array('id' => '-1'), $atts);
    // No ID value
    if (strcmp($a['id'], '-1') == 0) {
        return "";
    }
    $uid = $a['id'];
    $url = 'http://release.demo.viewlift.com/embed/player?filmId=' . $uid;
    if ($uid && 1 != 1) {
        $url = "http://staging3.partners.viewlift.com/demo/content/" . $uid;
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
    $iframe .= '<iframe src="' . $url . '" frameborder="0" allowfullscreen="true" width="100%" height="100%" name="playerframe_1" id="playerframe_1"></iframe>';
    $iframe .= '</div></div>';

    return $iframe;
}

if (!function_exists('get_post_id_by_meta_key_and_value')) {

    /**
     * Get post id from meta key and value
     * @param string $key
     * @param mixed $value
     * @return int|bool
     * @author viewlift 
     */
    function get_post_id_by_meta_key_and_value($key, $value) {
        global $wpdb;
        $meta = $wpdb->get_results("SELECT * FROM `" . $wpdb->postmeta . "` WHERE meta_key='" . $key . "' AND meta_value='" . $value . "'");
        if (is_array($meta) && !empty($meta) && isset($meta[0])) {
            $meta = $meta[0];
        }
        if (is_object($meta)) {
            return $meta->post_id;
        } else {
            return false;
        }
    }

}

add_action('wp_ajax_cvf_upload_files', 'cvf_upload_files');
add_action('wp_ajax_nopriv_cvf_upload_files', 'cvf_upload_files'); // Allow front-end submission 

function cvf_upload_files() {
    if (esc_attr(get_option('viewlift_access_key')) == '') {
        echo "Please check Access key is not Valid  in viewlift settings \n";
    } else {
        $AccessKey = get_option('viewlift_access_key');
    }

     if (esc_attr(get_option('viewlift_secret_key')) == '') {
        echo "Please check Secret key is not Valid in viewlift settings \n";
    } else {
        $SecretKey = get_option('viewlift_secret_key');
    }
    
     if (esc_attr(get_option('viewlift_bucket_name')) == '') {
         echo "Please check Bucket Name is not Valid in viewlift settings \n";
    } else {
        $bucket = get_option('viewlift_bucket_name');
    }
    
    
       //$serevr_url = get_option('viewlift_server_url');
 

    
    
    

    if (isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST") {

        $path = "uploads/"; //set your folder path
        //set the valid file extensions 
        $valid_formats = array("mp4", "flv", "webm", "acc", "mpeg", "ogg"); //add the formats you want to upload

        //save file from media URL
		if(!empty($_POST['filepath'])) {
			$dir = explode('plugins',dirname(__FILE__));
			$fileInfo = explode('/',$_POST["filepath"]);
			$l = count($fileInfo);
			$filename = $fileInfo[$l-1];
			$uploadDir = explode('uploads/',$_POST["filepath"]);
			$dirM = $fileInfo[$l-2];
			$dirY = $fileInfo[$l-3];
			$dir = str_replace('\\','/',$dir[0]."uploads/".$uploadDir[1]);//$dirY."/".$dirM."/");
			
			if(isset($filename) && file_exists($dir)){
				$post_id = get_post_id_by_meta_key_and_value('_wp_attached_file', $uploadDir[1]);
				if($post_id){
					$data = get_post_meta( $post_id, '_wp_attachment_metadata', true );
					$_FILES['myfile']['size'] = $data['filesize'];
					$_FILES['myfile']['type'] = $data['fileformat'];
				}
				$_FILES['myfile']['tmp_name'] = $dir;
				$_FILES['myfile']['name'] = $filename;
				$_FILES['myfile']['error'] = 0;
			}
		}
		
		$name = $_FILES['myfile']['name']; //get the name of the file
        $size = $_FILES['myfile']['size']; //get the size of the file
		
        if (strlen($name)) { //check if the file is selected or cancelled after pressing the browse button.
            list($txt, $ext) = explode(".", $name); //extract the name and extension of the file
            if (in_array($ext, $valid_formats)) { //if the file is valid go on.
                if ($size < 10000440000000000000000000000000000000000) {
                    $tmp = $_FILES['myfile']['tmp_name'];
					#Check S3 authentication
                    S3::setAuth($AccessKey, $SecretKey);

                    function testcurl($url, $data, $methodPost) {
                        $url = 'http://staging3.partners.viewlift.com' . $url;
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
                    //$dir = 'MezzFiles/2017/07/';
                    $dir = 'MezzFiles/' . date('Y') . '/' . date('m') . '/';
                    $filename = time() . '_' . $name;
                    $responce = S3::putObject($tmp, $dir . $filename);

                    if ($responce['code'] == 200) {
                        $data = array(
                            "site" => "demo",
                            "filename" => $filename,
                            "filesize" => $size,
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
                            'filesize' => $size,
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
                        //$printArray['changeStatus-Request2'] = $data;
                        //$printArray['Response2'] = $response2;	

                        if ($response2['status'] == 'processing') {

                            $url = '/content/completeUpload';

                            $methodPost = 1;

                            $data = array(
                                'uuid' => $response1['filmList'][0]['uuid'],
                                'filename' => $filename,
                                'filesize' => $size,
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
                        } else {
                            echo "failed";
                        }
                    }
                } else {
                    echo "File size max 10 MB";
                }
            } else {
                echo "Invalid file format..";
            }
        } else {
            echo "Please select a file..!";
        }
        exit;
    } else {
        echo "Please Try Again";
    }

    wp_die();
}

function viewlift_upload_url() {

    //print_r($_POST);
}
?>
