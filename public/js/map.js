

        //initialize map with MapLibre and Jawg for map style
        const accessToken = 'Q9ah9vp2jsb80zff6WPhCh04KN53KZBOKSw417PFGIttmq5x0w5gYZMgItqFW2Kl';
        const map = new maplibregl.Map({
           container: 'map',
           style: `https://api.jawg.io/styles/jawg-sunny.json?access-token=${accessToken}`,
           zoom: 9,
           center: [1.4364900000002763, 43.59818000000044], 
        }).addControl(new maplibregl.NavigationControl(), 'top-right');
        

        //get spots
        let spotsGeolocalisation = document.querySelectorAll('.geolocalisation');

        //loop on each spot
        spotsGeolocalisation.forEach(function(item){

            //get html wich contains "lat,long"
            let spotGeolocalisation = item.innerHTML;
           
            // split datas to put it on positioning function on map
            let recoverLongLat = spotGeolocalisation.split(',');

            //this element is the parent of geolocalisation datas: we get firstchild which correponds to #text, then his content 
            let spotName = item.previousElementSibling.firstChild.textContent;

            //popup on marker
            const markerPopup = new maplibregl.Popup({
                    closeOnClick: true,
                    focusAfterOpen : false,
                    className : 'popupMap'

            }).setHTML(`${spotName}`);
        
            //we set mapspot name on it, then using lat long datas to create marker and add it to the map
            new maplibregl.Marker({color:'#B30B00', scale:1.2}).setLngLat([recoverLongLat[1], recoverLongLat[0]]).setPopup(markerPopup).addTo(map);
            
        })
       
        //get all spot buttons to make a 'flyTo' animation on click
        let spotsMapChange = document.querySelectorAll('.spotMapChange');

        //for each button
        spotsMapChange.forEach(function(item){
            //we listening to the click
            item.addEventListener('click', ()=>{

                //get container wich contains spot informations
                let parent = item.parentNode;

                //target inner HTML text on <p> which is associated to geolocalisation
                let spotData = parent.querySelector('p:nth-child(2)').innerHTML;

                //split data to set it on map
                let longLatData = spotData.split(',');

                //flyTo animation with zoom on spot
                map.flyTo({
                    center: [longLatData[1],longLatData[0]],
                    essential: true, 
                    zoom:15
                })
            })
        });

        //responsive solution for map
        //select button
        let btnMap = document.querySelector('.btnMap');
       
       //listen click on it
        btnMap.addEventListener('click', function(e){ 
          
            let map = document.querySelector('#map');
            //if map contains "showMap" class, inverted chevron and fold map through CSS
            if(map.classList.contains('showMap')){
                btnMap.innerHTML = 'Carte <i class="fa-solid fa-chevron-down"></i>';
                map.classList.remove('showMap')
                map.classList.add('hideMap'); 
            //if it contains classe "hideMap" , unfold map on click  
            } else if (map.classList.contains('hideMap')){
                btnMap.innerHTML = 'Carte <i class="fa-solid fa-chevron-up"></i>'; 
                map.classList.add('showMap'); 
                map.classList.remove('hideMap'); 
            //if it contains nothing, unfold it and add class "showMap"
            } else {
                btnMap.innerHTML = 'Carte <i class="fa-solid fa-chevron-up"></i>'; 
                map.classList.add('showMap'); 
            }
        });

        //gestion reponsive de la map avec dépliant
        //selection du bouton
        let btnMap = document.querySelector('.btnMap');
       
       //on écoute le click
        btnMap.addEventListener('click', function(e){ 
          
            let map = document.querySelector('#map');
            //si la carte contient showMap on inverse le chevron et on remonte la carte
            if(map.classList.contains('showMap')){
                btnMap.innerHTML = 'Carte <i class="fa-solid fa-chevron-down"></i>';
                map.classList.remove('showMap')
                map.classList.add('hideMap'); 
            //si elle contient hide on deplie la carte au click  
            } else if (map.classList.contains('hideMap')){
                btnMap.innerHTML = 'Carte <i class="fa-solid fa-chevron-up"></i>'; 
                map.classList.add('showMap'); 
                map.classList.remove('hideMap'); 
            //si elle ne contient rien on la déplie
            } else {
                btnMap.innerHTML = 'Carte <i class="fa-solid fa-chevron-up"></i>'; 
                map.classList.add('showMap'); 
            }
        });