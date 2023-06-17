
//select btn .addspot, btn .reset and init variable for marker.
let addSpot = document.querySelector('.addSpot');
let reset = document.querySelector('.reset');
let marker;    

//if marker is undefined, set form values for longitude and latitude to 0 
//marker undefined : admin hasn't yet clicked on map for configure new spot or have reset marker
if(marker == undefined){
    document.querySelector('#spot_longitude').value = 0;
    document.querySelector('#spot_latitude').value = 0 ;
}

//listening to .addSpot button, which unfold form and init listener 
addSpot.addEventListener('click', function(e){

    //we listen a click on the map
    map.on('click', function(element){
    //stock geolocalisation datas at click     
    let geolocalisation = element.lngLat;
    //if marker is undefined, we create a new marker at clicked place with geolocalisation datas.It can be dragable
    if(marker == undefined){
        marker = new maplibregl.Marker({color:'#6C757D', scale:1.5, draggable: true}).setLngLat([geolocalisation.lng, geolocalisation.lat]).addTo(map);
        
        document.querySelector('#spot_longitude').value = geolocalisation.lng
        document.querySelector('#spot_latitude').value = geolocalisation.lat ; 
        //call onDrag() function for each marker drag action
        marker.on('drag', onDrag);
    }      
    }); 
});

//listener to reset button
reset.addEventListener('click', function(e){
    //delete marker and long and lat datas in form
    marker.remove();
    document.querySelector('#spot_longitude').value = 0;
    document.querySelector('#spot_latitude').value = 0 ;

    //listener to the map
    map.on('click', function(element){
        //remove marker for avoid multiple marker items
        marker.remove();
        //get geolocalisation on click place
        let geolocalisation = element.lngLat;
        
        //new marker based on geolocalisation datas, and set values to the form for each drag action of marker
        marker = new maplibregl.Marker({color:'#6C757D', scale:1.5, draggable: true}).setLngLat([geolocalisation.lng, geolocalisation.lat]).addTo(map);
        document.querySelector('#spot_longitude').value = geolocalisation.lng
        document.querySelector('#spot_latitude').value = geolocalisation.lat ;

        marker.on('drag', onDrag);

    }); 
});

//get marker geolocalisation datas and set values on form
function onDrag() {
    var lngLat = marker.getLngLat();

    document.querySelector('#spot_longitude').value = lngLat.lng;
    document.querySelector('#spot_latitude').value = lngLat.lat ;
                    
};       


//debut geocode api
var geocoder_api = {

    forwardGeocode: async (config) => {
    const features = [];
    try {
        let request =
        'https://nominatim.openstreetmap.org/search?q=' +
        config.query +
        '&format=geojson&polygon_geojson=1&addressdetails=1';

        const response = await fetch(request);
        const geojson = await response.json();

        for (let feature of geojson.features) {

            let center = [
            feature.bbox[0] +
            (feature.bbox[2] - feature.bbox[0]) / 2,
            feature.bbox[1] +
            (feature.bbox[3] - feature.bbox[1]) / 2
            ];

        let point = {
            type: 'Feature',
            geometry: {
                type: 'Point',
                coordinates: center
            },

            place_name: feature.properties.display_name,
            properties: feature.properties,
            text: feature.properties.display_name,
            place_type: ['place'],
            center: center
        };

        features.push(point);
        }

    } catch (e) {
        console.error(`Failed to forwardGeocode with error: ${e}`);
    }
    
        return { features: features };
    }

    };
    map.addControl(
        new MaplibreGeocoder(geocoder_api, {
        maplibregl: maplibregl
        })
    );

// fin geocode
