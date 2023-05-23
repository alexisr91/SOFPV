

        //mise en place de la map avec Jawg et mapLibre
        const accessToken = 'Q9ah9vp2jsb80zff6WPhCh04KN53KZBOKSw417PFGIttmq5x0w5gYZMgItqFW2Kl';
        const map = new maplibregl.Map({
           container: 'map',
           style: `https://api.jawg.io/styles/jawg-sunny.json?access-token=${accessToken}`,
           zoom: 9,
           center: [1.4364900000002763, 43.59818000000044],
        }).addControl(new maplibregl.NavigationControl(), 'top-right');
    

        //récupération des spots
        let spotsGeolocalisation = document.querySelectorAll('.geolocalisation');

        //on boucle sur chaque spot de session
        spotsGeolocalisation.forEach(function(item){

            //on récupère le html qui correspond à "lat , long"
            let spotGeolocalisation = item.innerHTML;
            //on sépare les data pour les mettre dans la fonction de placement de marker sur la carte
            let recoverLatLong = spotGeolocalisation.split(',');

            //element précédent les données de géolocalisation(<p> placé juste avant qui contient le nom de chaque spot)
            //on récupère le firstChild qui est le #text, puis le contenu du #text qui est un string
            let spotName = item.previousElementSibling.firstChild.textContent;

            const markerPopup = new maplibregl.Popup({
                    closeOnClick: true,
                    focusAfterOpen : false,
                    className : 'popupMap'

            }).setHTML(`${spotName}`);

            new maplibregl.Marker({color:'#B30B00', scale:1.2}).setLngLat([recoverLatLong[0], recoverLatLong[1]]).setPopup(markerPopup).addTo(map);
            
        })
       
        //récupération des boutons pour modifier la vue de la carte suivant le spot selectionné
        let spotsMapChange = document.querySelectorAll('.spotMapChange');

        //pour chaque bouton
        spotsMapChange.forEach(function(item){
            //on écoute le click
            item.addEventListener('click', ()=>{

                //on récupère le container qui regroupe les info du point
                let parent = item.parentNode;

                //on cible les données texte dans le p associé a la geolocalisation
                let spotData = parent.querySelector('p:nth-child(2)').innerHTML;

                //on sépare les données pour les passer à la carte
                let latLongData = spotData.split(',');

                //animation qui "redirige" avec animation + zoom vers le point demandé par l'user
                map.flyTo({
                    center: [latLongData[0],latLongData[1]],
                    essential: true, 
                    zoom:15

                })


            })
          
        });
