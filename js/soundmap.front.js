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

        init: function (selector, query){
            this.map = L.map(selector,{center: [this.origin.lat, this.origin.lng], zoom: this.origin.zoom, scrollWheelZoom: false});
            this.layers.SATELLITE = new L.Google();
            this.layers.TERRAIN = new L.Google('TERRAIN');
            this.layers.HYBRID = new L.Google('HYBRID');
            this.layers.ROADMAP = new L.Google('ROADMAP');
            this.map.addLayer(this.layers[this.mapType]);
            this.map.addControl(
                new L.Control.Layers(
                    {
                        'Google':this.layers.SATELLITE,
                        'Google Terrain': this.layers.TERRAIN,
                        'Google Hybrid': this.layers.HYBRID,
                        'Google Roadmap': this.layers.ROADMAP
                    }, {}
                )
            );
            _.bindAll(this);

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