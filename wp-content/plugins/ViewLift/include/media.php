<?php

// Add the ViewLift Player tab to the menu of the "Add media" window
function VIEWLIFT_media_menu($tabs) {
    $newtab = array('VIEWLIFT' => 'VIEWLIFT Player');
    return array_merge($tabs, $newtab);
}

// output the contents of the ViewLift Player tab in the "Add media" page
function VIEWLIFT_media_page() {
    media_upload_header();
    ?>
    <form class="media-upload-form type-form validate" id="video-form" enctype="multipart/form-data" method="post" action="">
        <h3 class="media-title avplayer-media-title">
            Choose a <strong>player</strong> and a <strong>video</strong>
        </h3>

        <div class="media-items">
            <div id="avplayer-video-box" class="media-item">
                <?php VIEWLIFT_media_widget_body(true); ?>
            </div>
        </div>
        <input type="hidden" name="_wpnonce-widget" value="<?php echo esc_attr(wp_create_nonce('av-widget-nonce')); ?>">
    </form>
    <?php
}

// Make our iframe show up in the "Add media" page
function VIEWLIFT_media_handle() {
    return wp_iframe('VIEWLIFT_media_page');
}

// Add the page and post section
function VIEWLIFT_media_add_video_box() {

    add_meta_box('VIEWLIFT-video-box', 'Insert media with ViewLift Player', 'VIEWLIFT_media_widget_body', 'post', 'side', 'high');
    add_meta_box('VIEWLIFT-video-box', 'Insert media with ViewLift Player', 'VIEWLIFT_media_widget_body', 'page', 'side', 'high');
}

// The body of the widget
function VIEWLIFT_media_widget_body() {
    ?>

    <div class="avplayer-widget-div" id="avplayer-video-div">


        <div class="viewliftplayer-tab overflow" id="viewliftplayer-tab-choose">

            <div id="exTab2">	
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a  href="#1" data-toggle="tab">Video List</a>
                    </li>
                    <li><a href="#2" data-toggle="tab">Video Upload</a>
                    </li>        
                </ul>

                <div class="tab-content ">
                    <div class="tab-pane active" id="1">
                        <!--Tab One here-->
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
                        
                        ?>
                        <ul class="viewliftplayer-video-list">
                            <?php

                            function curlTest($uid) {
                                $url = 'http://staging3.partners.viewlift.com/demo/content/publish?email=kalyan%2Blax%40viewlift.com';
                                $data = '{"film":[{"id":"' . $uid . '"}],"selectedSocial":[]}';
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

                            if (empty($list)) {
                                echo 'No Record Found!';
                            } else {
                                foreach ($list as $key => $video) {
                                    if (in_array($video['video_status'], array('processing complete', 'publish', 'published')) && !empty($video['mezz_file'])) {

                                        if ($video['video_status'] == 'processing complete') {
                                            curlTest($video['uuid']);
                                        }
                                        $data = explode('_', $video['mezz_file'], 2);
                                        ?>
                                        <li class="av-even" data-value="<?php echo "[VL" . " id ='" . $video['uuid'] . "']"; ?>" >
                                            <div ><?php echo (isset($data[1])) ? $data[1] : ((!empty($data[0]) ? $data[0] : $video['uuid'])); //echo "[av". " id ='".$video['uuid'] ."']"; //echo (isset($data[1]))?$data[1]:$data[0];    ?>
                                                <button type="button" class="btnName">Use</button>
                                            </div>
                                        </li> 
                                        <?php
                                    }
                                }
                            }
                            ?>     
                        </ul>    
                        <!--End Here-->  

                    </div>
                    <div class="tab-pane" id="2">        
                        <button type="button" data-toggle="modal" data-target="#VIEWLIFTModal" class="btn btn-primary ddr btn-upload">Video Upload</button>
                        
                    </div>       
                </div>
            </div>


            <div id="VIEWLIFTModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close closeBtn" data-dismiss="modal" aria-hidden="true">&times;  </button>
                            <h4 class="modal-title" id="myModalLabel">Viewlift</h4>
                        </div>
                        <div class="modal-body">
                            <form id="myform" method="post">

                                <div class="form-group">
                                    <label>Enter Your Media URL: </label>
                                    <input class="form-control" type="text" accept="video/*" id="filepath" /> 
                                </div>
								<div class="form-group" style="text-align:center;font-size:16px;padding-top:8px;">
                                    <label> OR </label>
                                </div>
                                <div class="form-group">
                                    <label>Upload Video: </label>
                                    <input class="form-control" type="file" id="myfile" />
                                </div>
                                <div class="form-group">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success myprogress" role="progressbar" style="width:0%">0%</div>
                                    </div>

                                    <div class="msg"></div>
                                </div>
                                
                                <input type="button" id="btn" class="btn-success btn btn-primary ddr" value="Upload" />
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default closeBtn" data-dismiss="modal">Close</button>        
                        </div>
                    </div>
                </div>
            </div>

			
        </div>

    </div>



    <?php
}
