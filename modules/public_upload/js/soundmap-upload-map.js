var check_soundmark;
var soundmark;
var map_select_canvas;
var map_check_canvas;

jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function
    var or_latln= new google.maps.LatLng(WP_Params.lat, WP_Params.lng);
    var or_maptype;
    switch (WP_Params.mapType){
	case 'ROADMAP':
	    or_maptype = google.maps.MapTypeId.ROADMAP;
	    break;
	case 'HYBRID':
	    or_maptype = google.maps.MapTypeId.HYBRID;
	    break;
	case 'TERRAIN':
	    or_maptype = google.maps.MapTypeId.TERRAIN;
	    break;
	case 'SATELLITE':
	    or_maptype = google.maps.MapTypeId.SATELLITE;
	    break;	
    }
    
     map_select_canvas = $('#map_select');

     map_check_canvas = $('#check_map');
    
    if (map_check_canvas.length){
	$('#check_map').gmap({'center': or_latln, 'zoom' : Number(WP_Params.zoom), 'mapTypeId': or_maptype}).bind('init', function(event, map){
            check_soundmark = map_check_canvas.gmap('addMarker', {'position': or_latln, 'title':'', 'draggable':true}, function(map, marker){});
            
        });	
    }

  
  
    if (map_select_canvas.length){
             $('#map_select').gmap({'center': or_latln, 'zoom' : Number(WP_Params.zoom),'mapTypeId': or_maptype}).bind('init', function(event, map){
                $(map).click( function (event){
                    if (soundmark == undefined){
                        
                        soundmark = map_select_canvas.gmap('addMarker', {'position': event.latLng, 'title':'', 'draggable':true}, function(map, marker){
                            var marker_position=marker.getPosition();
                            $('#posLat').val(marker_position.lat());
                            $('#posLong').val(marker_position.lng());
                        });
                        map_Change_Update(event);
                        $(soundmark).dragend(map_Change_Update);                        
                        
                    }else{
                        map_Change_Update(event);
                    }                    
                });
                $(map).addEventListener('zoom_changed', function(event){
                    var new_zoom = map.getZoom();
                    map_c = map_check_canvas.gmap('get', 'map');
                    map_c.setZoom(new_zoom);
                });
                $(map).addEventListener('maptypeid_changed',function(event){
		    var new_type = map.getMapTypeId();
                    map_c = map_check_canvas.gmap('get', 'map');
                    map_c.setMapTypeId(new_type);

		});
            });

    }    
});

var map_Change_Update = function (event){
    var new_center = event.latLng;
    
    jQuery('#posLat').val(new_center.lat());
    jQuery('#posLong').val(new_center.lng());
    
    var marker = check_soundmark.get(0);                            
    marker.setPosition(new_center);
    
    var marker = soundmark.get(0);                            
    marker.setPosition(new_center);
    
    map_c = map_check_canvas.gmap('get', 'map');
    map_c.panTo(new_center); 
    
}