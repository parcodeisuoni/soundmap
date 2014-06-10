(function($){

    var soundmap = window.Soundmap || {}

    _.extend(soundmap,	{

        map: undefined,
        self: this,
        layers:[],
        marker: undefined,
        file_frame:  wp.media.frames.file_frame = wp.media({
            multiple: false,
            library: {
                type: 'audio'
            }
        }),

        attachTemplate: "<div class='soundmap-attach-item'><div class='att-icon'><img src='<%= icon %>'/></div>" +
            "<div class='att-info'><a href='<%= url %>'><strong><%= name %></strong></a><br/>" +
            "<span class='att-length'><%= length %></span><br/>" +
            "<a href='#' class='delete-att-item'>Borrar</a></div><div class='clear'></div><input type='hidden' name='soundmap-att-ids[]' value='<%= id %>' /></div>",


        initMap: function(){
            this.map = L.map('map_canvas',{center: [this.origin.lat, this.origin.lng], zoom: this.origin.zoom});
            this.layers.push(new L.Google());
            this.layers.push(new L.Google('TERRAIN'));
            this.map.addLayer(this.layers[0]);
            this.map.addControl(
                new L.Control.Layers(
                    {
                        'Google':this.layers[0],
                        'Google Terrain': this.layers[1]
                    }, {}
                )
            );

            this.map.addEventListener('click', this.mapClick, this);

            if( $('#soundmap-marker-lat').val() ){
                var _latlng = [parseFloat($('#soundmap-marker-lat').val()), parseFloat($('#soundmap-marker-lng').val())];
                this.marker = L.marker(_latlng,{draggable: true}).addTo(this.map);
                this.map.panTo(_latlng);
                this.marker.addEventListener('dragend', this.markerDrag, this);
            }
            //_.bindAll(this);
            this.initEvents();

        },


        initEvents: function(){
            $('#add_files').bind('click', this.openMediaManager);
            this.file_frame.on('select',this.mediaSelected, this);
            $('.delete-att-item').live('click', this.removeMedia);

        },

        removeMedia: function(event){
            $(event.currentTarget).parents('.soundmap-attach-item').remove();
        },
        openMediaManager: function(event){
            event.preventDefault();
            if ( soundmap.file_frame ){
                soundmap.file_frame.open();
                return;
            };
        },
        mediaSelected: function(event){
            var attachment = this.file_frame.state().get('selection').first().toJSON();
            var _cT = _.template(this.attachTemplate);
            var _o = _cT({
                icon: attachment.icon,
                url: attachment.url,
                name: attachment.title,
                length: attachment.fileLength,
                id: attachment.id
            });
            $('#soundmap-attachments-list').append(_o);

        },

        mapClick: function(event){
            var _latlng = event.latlng;
            if(_.isUndefined(this.marker)){
                this.marker = L.marker(_latlng,{draggable: true}).addTo(this.map);
                this.marker.addEventListener('dragend', this.markerDrag, this);
            }else{
                this.marker.setLatLng(_latlng);
            }
            $('#soundmap-marker-lat').val(_latlng.lat);
            $('#soundmap-marker-lng').val(_latlng.lng);
        },

        markerDrag: function(event){
            var _latlng = this.marker.getLatLng();
            $('#soundmap-marker-lat').val(_latlng.lat);
            $('#soundmap-marker-lng').val(_latlng.lng);
        }

    });




    $(document).ready(function(){
        soundmap.initMap();
    });

}(jQuery));