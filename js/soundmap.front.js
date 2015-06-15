(function($){

    var soundmap = window.Soundmap || {}

    _.extend(soundmap,	{

        map: undefined,
        layers:{},
        markers: undefined,
        infos:[],
        ajaxMarker: undefined,
        callbacks:{
            marker_click: undefined
        },

        initMap: function( selector, query ) {
            this.map = L.map('map_canvas',{center: [this.origin.lat, this.origin.lng], zoom: this.origin.zoom, scrollWheelZoom: false});
            this.layers.SATELLITE =     new L.Google();
            this.layers.TERRAIN =       new L.Google('TERRAIN');
            this.layers.HYBRID =        new L.Google('HYBRID');
            this.layers.ROADMAP =       new L.Google('ROADMAP');
			
			this.layers.OSM =           new L.tileLayer.provider('OpenStreetMap.Mapnik');
			this.layers.OSM_HOT =       new L.tileLayer.provider('OpenStreetMap.HOT');
			this.layers.OSM_BW =        new L.tileLayer.provider('OpenStreetMap.BlackAndWhite');
			
			this.layers.GrayScale =     new L.tileLayer.provider('OpenMapSurfer.Grayscale');
			
			this.layers.StamenWater =   new L.tileLayer.provider('Stamen.Watercolor');
			this.layers.StamenTon =     new L.tileLayer.provider('Stamen.Toner');
			this.layers.StamenTonLite = new L.tileLayer.provider('Stamen.TonerLite');
			
			this.layers.ThundLand =     new L.tileLayer.provider('Thunderforest.Landscape');
			
            this.map.addLayer(this.layers[this.mapType]);
            this.map.addControl(
                new L.Control.Layers(
                    {
                        'Google':             this.layers.SATELLITE,
                        'Google Terrain':     this.layers.TERRAIN,
                        'Google Hybrid':      this.layers.HYBRID,
                        'Google Roadmap':     this.layers.ROADMAP,
						'Open Street':        this.layers.OSM,
						'Open Street Hot':    this.layers.OSM_HOT,
						'Open Black & White': this.layers.OSM_BW,
						'GrayScale':          this.layers.GrayScale,
						'Watercolor':         this.layers.StamenWater,
						'Toner':              this.layers.StamenTon,
						'Toner Lite':         this.layers.StamenTonLite,
						'Landscape':          this.layers.ThundLand			
                    }, {}
                )
            );
            _.bindAll(soundmap.initMap);

            options = {
                type:    'POST',
                url:     this.ajaxurl,
                context: this,
                data: {
                    action: 'soundmap-get-markers',
                    query: query
                }
            };
            // Use with PHP's wp_send_json_success() and wp_send_json_error()
            $.ajax( options ).done(this.ajaxDone).fail(this.ajaxFail);
        },

        addMapCallback: function (event, callback){
            this.callbacks[event] = callback;
        },

        onMarkerClick: function(event){
            var id = event.target.feature.properties.id;
            this.ajaxMarker = event.target;
            if(this.infos[id]){
                if (!_.isUndefined(this.callbacks.marker_click)){
                    this.callbacks.marker_click.apply(this, [this.infos[id], this.ajaxMarker]);
                    return;
                }

            }else{
                options = {
                    type:    'POST',
                    url:     this.ajaxurl,
                    context: this,
                    data: {
                        action: 'soundmap-get-content',
                        id: id
                    }
                };
                // Use with PHP's wp_send_json_success() and wp_send_json_error()
                $.ajax( options ).done(this.ajaxMarkerClick).fail(this.ajaxFail);
            }

        },

        ajaxMarkerClick: function (response){

            this.infos[response.data.id] = response.data;

            if (!_.isUndefined(this.callbacks.marker_click)){
                this.callbacks.marker_click.apply(this, [response.data, this.ajaxMarker]);
                return;
            }
            // We have no callback, so we opened the window. In this case we will only do with the player and title.
            this.ajaxMarker.addEventListener('popupopen', this.addPlayer, this);
            this.ajaxMarker.bindPopup(response.data.html).openPopup();
        },

        addPlayer: function(response){
            $('audio.soundmap-audio-player').mediaelementplayer();
        },

        ajaxDone: function(response){
            var self = this;
            this.markers = L.geoJson(response.data,{
                onEachFeature: function(feature, layer){
                    layer.on('click', self.onMarkerClick);
                }
            }).addTo(this.map);
        },
        ajaxFail: function(){
            console.log(this);
        }

    });

    $(document).ready(function(){
        soundmap.initMap();
    });

}(jQuery));