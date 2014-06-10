(function($){

    var soundmap = window.Soundmap || {}

    _.extend(soundmap,	{

        map: undefined,
        layers:{},

        initMap: function(){
            this.map = L.map('map_canvas_options',{center: [this.origin.lat, this.origin.lng], zoom: this.origin.zoom});
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

            this.map.addEventListener('dragend', this.mapDrag, this);
            this.map.addEventListener('zoomend', this.mapZoom, this);
            this.map.addEventListener('baselayerchange', this.layerChange, this);

            //_.bindAll(this, 'dragend', 'zoomend', 'baselayerchange');
            //this.initEvents();
        },

        layerChange: function(event){
            var _t = event.layer._type;
            $('#soundmap_op_origin_type').val(_t);
        },

        mapDrag: function(event){
            var _l = this.map.getCenter();
            $('#soundmap_op_origin_lat').val(_l.lat);
            $('#soundmap_op_origin_lng').val(_l.lng);
        },
        mapZoom: function(event){
            $('#soundmap_op_origin_zoom').val(this.map.getZoom());
        },
        initEvents: function(){

        }


    });




    $(document).ready(function(){
        soundmap.initMap();
    });

}(jQuery));