
   

    //FILTRE PAR CATEGORIE (BLOG ACTUALITES)
    
   
    $('#cat').on('change', function(){ 

        let cat = $(this).val() //on select le nom de la catégorie dès qu'un choix se fait sur le menu deroulant
        sessionStorage.setItem('storedCat', cat); //on stocke ce choix

        if(cat === 'all') {
            $('.category').parent().parent().fadeIn('fast') 
            sessionStorage.clear();
                    
        } else {
            $('.category').parent().parent().fadeOut('fast')
            $('.'+ cat).parent().parent().fadeIn('slow') // on fait apparaitre le container grand parent dont la categorie correspond au choix du menu
        }
      
    })


    //récupération de la catégorie précédente lors du changement de page
    window.addEventListener('load', (event) => {
       //au chargement de la page on récupère le choix précédemment fait par l'user 
       let storedCat = sessionStorage.getItem('storedCat');
        // console.log(storedCat);
       if(storedCat !== null){
            $('#cat').val(storedCat);
            //pour le réappliquer sur les pages suivantes
            if(storedCat === 'all') {
                $('.category').parent().parent().fadeIn('fast')
                    
            } else {
                $('.category').parent().parent().fadeOut('fast')
                $('.'+ storedCat).parent().parent().fadeIn('slow') // on fait apparaitre le container grand parent dont la categorie correspond au choix du menu
            }
       } else {
            $('#cat').val('all');
       }
      
       
    });

   

    



