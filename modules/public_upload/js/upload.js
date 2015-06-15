var check_map;
var check_marker
var waitingForSend = false;
var uploadedFile = false;
var fileUploading=false;

jQuery("document").ready(function(){	

	jQuery('#slider').anythingSlider({
		
		width:				'885',
		height:				'430',
		 // Navigation
	  startPanel          : 1,         // This sets the initial panel
	  hashTags            : true,      // Should links change the hashtag in the URL?
	  infiniteSlides      : false,      // if false, the slider will not wrap
	  enableKeyboard      : false,      // if false, keyboard arrow keys will not work for the current panel.
	  buildArrows         : true,      // If true, builds the forwards and backwards buttons
	  toggleArrows        : false,     // if true, side navigation arrows will slide out on hovering & hide @ other times
	  buildNavigation     : false,      // If true, builds a list of anchor links to link to each panel
	  enableNavigation    : false,      // if false, navigation links will still be visible, but not clickable.
	  toggleControls      : false,     // if true, slide in controls (navigation + play/stop button) on hover and slide change, hide @ other times
	  appendControlsTo    : null,      // A HTML element (jQuery Object, selector or HTMLNode) to which the controls will be appended if not null
	  navigationFormatter : null,      // Details at the top of the file on this use (advanced use)
	  forwardText         : "&raquo;", // Link text used to move the slider forward (hidden by CSS, replaced with arrow image)
	  backText            : "&laquo;", // Link text used to move the slider back (hidden by CSS, replace with arrow image)

			// Slideshow options
	  enablePlay          : false,      // if false, the play/stop button will still be visible, but not clickable.
	  autoPlay            : false,      // This turns off the entire slideshow FUNCTIONALY, not just if it starts running or not
	  animationTime       : 600,       // How long the slideshow transition takes (in milliseconds)
	  easing              : "swing",   // Anything other than "linear" or "swing" requires the easing plugin
	  infiniteSlides: false,
 	stopAtEnd: true
	

	});
	
	jQuery("#progress-bar").progressbar({value:59});	
	jQuery( "#date" ).datepicker({dateFormat: 'dd/mm/yy'});
	
	jQuery('#slider').bind('slide_init', function(e, slider){
 		
 		checkValues(slider);	
	});
	
	jQuery('#slider').bind('slide_complete', function(e, slider){
 		jQuery("#crumbs li").removeClass("selected");
 		var panel=slider.currentPage-1;
 		jQuery("#crumbs li:eq(" + panel + ")").addClass("selected");
	});


	jQuery("#data-form").validate();
	jQuery("#user-data-form").validate();	
		
	
	
	jQuery.validator.setDefaults({
		submitHandler: function() {  }
	});
	
	jQuery('#QapTcha').QapTcha({disabledSubmit:true,autoRevert:true,txtLock:WP_Params_up.locked_tag,txtUnlock:WP_Params_up.unlocked_tag, PHPfile:WP_Params_up.ajaxurl});
	
	jQuery("#user-data-form").submit(function(){
		
		var valid_mail=jQuery("#user-data-form").valid();					
		if (valid_mail & uploadedFile){
			jQuery("#savingContent").show();
			executeSaving();						
		}else if(valid_mail){
			jQuery("#savingContent").show();
			waitingForSend = true;
		}
		return false;
	});

 
});


var swfu;
window.onload = function() {	
	var settings = {
			flash_url : WP_Params_up.url_plugin + "/modules/public_upload/js/swfupload/swfupload.swf",
			upload_url: WP_Params_up.url_plugin + "/modules/public_upload/js/file-handler.php",
			post_params: {"PHPSESSID" : WP_Params_up.php_sesion},
			prevent_swf_caching : false,
        	preserve_relative_urls : false,
        	use_query_string : false,
        	http_success : [201, 202],

			// File Upload Settings
			file_size_limit : "15360",
			file_types : "*.mp3",
			file_types_description : "mp3 audio files",
			file_upload_limit : 0,
			
			custom_settings : {
				upload_target : "progress-bar"
			},
			debug: false,
			

			// Button settings
			button_width: "150",
			button_height: "18",
			button_placeholder_id: "spanButtonPlaceHolder",
			button_text: '<span class="uploadButtonText">' + WP_Params_up.upload_button_tag + '</span>',
			button_text_style: '.uploadButtonText { color:#777777; font-weigth:bold; font-size: 14pt; font-family:Arial,"Helvetica Neue","Liberation Sans",FreeSans,sans-serif}',
			button_text_left_padding: 15,
			button_text_top_padding: 1,

			
			// Event Handler Settings - these functions as defined in Handlers.js
			//  The handlers are not part of SWFUpload but are part of my website and control how
			//  my website reacts to the SWFUpload events.
			swfupload_preload_handler : preLoad,
			swfupload_load_failed_handler : loadFailed,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_progress_handler : uploadProgress,
			upload_start_handler : uploadStart,		
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
		};

		swfu = new SWFUpload(settings);
};

function executeSaving(){
	var serial_data1 = serializeJSON(jQuery("#data-form").serializeArray());
	var serial_data2 = serializeJSON(jQuery("#user-data-form").serializeArray());
			var data = {
				action: 'soundmap_save_public_upload',
				info: serial_data1,
				uploader: serial_data2
			};
			jQuery.post(WP_Params_up.ajaxurl, data, function(response){
					if (response == "ok"){
  						
						var win = window.dialogArguments || opener || parent || top;
						win.tb_remove();

  					}
			});

}



function checkValues(slide){
	
	var panel=slide.currentPage;
	var error_c = false;
	var error_t = "";
	
	if(panel==1){
		if (fileUploading==false){
			error_c=true;
			error_t += WP_Params_up.error_not_sound_tag;
		}
	}
	if (panel==2){
				
		if (jQuery("#posLat").val()==""){
			error_c = true;
			error_t += WP_Params_up.error_not_placed_tag;			
		}
	}
	if (panel==3){
		if(jQuery("#data-form").valid()==false){
			error_c = true;
		}
	//	check_marker.setPosition(new google.maps.LatLng(jQuery("#posLat").val, jQuery("#posLong").val));
		
	}
	
	if (error_c){
		alert(error_t);
		slide.cancel=true;
	//	jQuery("#slider").anythingSlider(panel);		

		
	}
}

function checkInfoFields(){
	
}

function putMarkerOnMap(origin, destin){
	
}

function serializeJSON(arrayData){
  var objectData;
  
  objectData = {};

  jQuery.each(arrayData, function() {
    var value;

    if (this.value != null) {
      value = this.value;
    } else {
      value = '';
    }
	name = check_name(this.name);
    if (objectData[name] != null) {
      if (!objectData[name].push) {
        objectData[name] = [objectData[name]];
      }

      objectData[name].push(value);
    } else {
      objectData[name] = value;
    }
  });

  return objectData;

};

function check_name(name){
	
	result=name;
	if (name == null || name == ""){
		
	}
	t_length = name.length;
	if (name.substr(t_length-2)=="[]"){
		result = name.slice(0,(t_length-2));
	}
	return result;
}


 



