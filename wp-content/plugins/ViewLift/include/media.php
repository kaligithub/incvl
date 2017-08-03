<?php

// Add the ViewLift Player tab to the menu of the "Add media" window
function av_media_menu($tabs) {   
        $newtab = array('av' => 'AV Player');
        return array_merge($tabs, $newtab);
   
}

// output the contents of the ViewLift Player tab in the "Add media" page
function av_media_page() {
    media_upload_header();
    ?>
    <form class="media-upload-form type-form validate" id="video-form" enctype="multipart/form-data" method="post" action="">
        <h3 class="media-title jwplayer-media-title">
            Choose a <strong>player</strong> and a <strong>video</strong>
        </h3>

        <div class="media-items">
            <div id="jwplayer-video-box" class="media-item">
                <?php av_media_widget_body(true); ?>
            </div>
        </div>
        <input type="hidden" name="_wpnonce-widget" value="<?php echo esc_attr(wp_create_nonce('av-widget-nonce')); ?>">
    </form>
    <?php
}

// Make our iframe show up in the "Add media" page
function av_media_handle() {
    return wp_iframe('av_media_page');
}

// Add the video widget to the authoring page, if enabled in the settings
function av_media_add_video_box() {

    add_meta_box('av-video-box', 'Insert media with ViewLift Player', 'av_media_widget_body', 'post', 'side', 'high');
    add_meta_box('av-video-box', 'Insert media with ViewLift Player', 'av_media_widget_body', 'page', 'side', 'high');
}

// The body of the widget
function av_media_widget_body() {
    ?>

    <div class="jwplayer-widget-div" id="jwplayer-video-div">

    <!--<p id="jwplayer-account-login-link"><span>Choose content from</span> your <a href="<?php //echo esc_url( JWPLAYER_DASHBOARD );  ?>" title="open your dashboard">JW Player Account</a>-->
        <ul class="jwplayer-tab-select">
            <!--<li id="jwplayer-tab-select-choose">Choose</li>
            <li id="jwplayer-tab-select-add" class="jwplayer-off">Add New</li>-->
        </ul>
        <div class="jwplayer-tab overflow" id="jwplayer-tab-choose">

             <?php
    $url = "http://staging3.partners.viewlift.com/demo/content?portalOnly=true&start=0";
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
    //echo "<pre>";
    //print_r($list);

    ?>
  <ul class="jwplayer-video-list">
  <?php
	function curlTest($uid){
		$url = 'http://staging3.partners.viewlift.com/demo/content/publish?email=kalyan%2Blax%40viewlift.com';
		$data = '{"film":[{"id":"'.$uid.'"}],"selectedSocial":[]}';
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
		
	}
	
	if(esc_attr( get_option('av_access_key'))=='' || esc_attr( get_option('av_secret_key'))==''){
		echo 'Please fill the ViewLift config details';
	}elseif(empty($list)){
		echo 'No Record Found!';
	}
	else{
		foreach ($list as $key => $video) {
			if(in_array($video['video_status'],array('processing complete','publish','published')) && !empty($video['mezz_file'])){
			
			if($video['video_status']=='processing complete'){
				curlTest($video['uuid']);
			}
			$data = explode('_', $video['mezz_file'],2);
	?>
			<li class="av-even" data-value="<?php echo "[VL". " id ='".$video['uuid'] ."']";?>" >
				<div ><?php echo (isset($data[1]))?$data[1]:((!empty($data[0])?$data[0]:$video['uuid']));//echo "[av". " id ='".$video['uuid'] ."']"; //echo (isset($data[1]))?$data[1]:$data[0]; ?>
					<button type="button" class="btnName">Use</button>
				</div>
			</li> 
	<?php  } }} ?>     
 </ul>  
        </div>
        <!--<div class="jwplayer-tab jwplayer-off" id="jwplayer-tab-add">
                <p>
                        Which type of content would you like to add?
                </p>
                <div>
                        <a class="jwplayer-button button-primary" id="jwplayer-button-upload">upload</a>
                        <span>or</span>
                        <a class="jwplayer-button button-primary" id="jwplayer-button-url">url</a>
                </div>
        </div>-->
    </div>
	<style>
		.overflow{overflow-y:auto !important;height:150px;}
		.btnName{
		padding: 0px 5px;
		float: right;
		}
		dd, li {
			margin-bottom: 7px;
		}
	</style>
	<script src="http://code.jquery.com/jquery-2.2.4.min.js"></script>
	<script>
		$(document).ready(function(){
			$('.btnName').click(function(e){
			e.preventDefault();
			var v = $(this).parent().parent().data('value');
				
			//console.log($( '#wp-content-editor-container' ).find( 'textarea' ));
			//$( '#wp-content-editor-container' ).find( 'textarea' ).val(v);   //Value show in Text
			tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, v );  //Value show in Visual
			});
		});

	</script>
    <?php
}
