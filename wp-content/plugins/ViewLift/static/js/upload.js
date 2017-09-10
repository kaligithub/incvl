jQuery(function ($) {
$(function () {
    $(".progress").hide();
    $('#btn').click(function () {
        var filepath = $('#filepath').val();
        var myfile = $('#myfile').val();
        if (filepath == '' && myfile == '') {
            alert('Field can not be left blank');
            return;
        }
		$(".progress").show();
        $('.myprogress').css('width', '0');
        $('.msg').text('');
        var formData = new FormData();
        formData.append('myfile', $('#myfile')[0].files[0]);
        formData.append('filepath', filepath);
        $('#btn').attr('disabled', 'disabled');
        $('.msg').text('Uploading in progress...');
        formData.append('action', 'cvf_upload_files');
        $.ajax({
            url: ajaxurl,
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            // this part is progress bar
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        percentComplete = parseInt(percentComplete * 99);
                        $('.myprogress').text(percentComplete + '%');
                        $('.myprogress').css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function (data) {	
				$('.msg').html(data);
				if(data=='failed'){
					$('#btn').removeAttr('disabled');
				} else {
					$('.myprogress').html('100%');
					$('.myprogress').css('width', '100%');
					//$('#btn').removeAttr('disabled');
					//location.reload();
				}

                //$('.msg').text(data);
                //$('#btn').removeAttr('disabled');
            }
        });
    });
    
   
    
});


    $('.btnName').click(function (e) {
        e.preventDefault();
        var v = $(this).parent().parent().data('value');

        //console.log($( '#wp-content-editor-container' ).find( 'textarea' ));
        //$( '#wp-content-editor-container' ).find( 'textarea' ).val(v);   //Value show in Text
        tinyMCE.activeEditor.execCommand('mceInsertContent', false, v);  //Value show in Visual
    });

	$('.btn-upload').click(function (e) {
		$("#filename").val('');
		$("#myfile").val('');
    });
	$('.closeBtn').click(function (e) {
		location.reload();
    });
});