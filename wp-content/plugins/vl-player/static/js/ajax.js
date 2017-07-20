jQuery( document ).ready(function() {

	jQuery("#vluploadbtn").on('click',(function(e){
		//e.preventDefault();
		var form_data = new FormData(this);
		// Display the key/value pairs
		jQuery.ajax({
			url: "http://localhost/viewlift/wp-content/plugins/vl-player/include/upload_video.php",
			type: "POST",
			data:  form_data,
			contentType: false,
			cache: false,
			processData:false,
			success: function(data){
				alert(data);
				console.log(data);
			},
			error: function(error){
                console.log(error);
			} 	        
		});

		return false;

	}));
    
});

