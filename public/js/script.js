
        //GESTION DES LIKES
        $(document).on('click', '#like-article',function(){
            var article_id = $(this).attr('data-id');

            $.ajax({
                url: Routing.generate('article_like', {id:article_id}),
                type: 'POST',
                success: function(response){
                    if(response[0] == 'erreur'){
                        alert(response[1]);
                    } else {
                        var isLiked = $('#like-video').find('fa-regular').length;
                        if(isLiked > 0){
                            var icon = '<i class="fa-solid fa-thumbs-up fa-2x"></i>';
                            var count = parseInt($('#like-count').html())+1;
                        } else {
                            var icon = '<i class="fa-regular fa-thumbs-up fa-2x"></i>';
                            var count = parseInt($('#like-count').html())-1;
                        }
                        $('#like-article').html(icon);
                        
                        $('#like-count').html(count);

                    }
                
                }
            })
        })

    // //GESTION DE LA COLLECTION D'IMAGES
    // jQuery(document).ready(function () {
    //     jQuery('#add_images').click(function (e) {
    //         var list = jQuery(jQuery(this).attr('data-list-selector'));
    //         // Try to find the counter of the list or use the length of the list
    //         var counter = list.data('widget-counter') || list.children().length;
    
    //         // grab the prototype template
    //         var newWidget = list.attr('data-prototype');
    //         // replace the "__name__" used in the id and name of the prototype
    //         // with a number that's unique to your emails
    //         // end name attribute looks like name="contact[emails][2]"
    //         newWidget = newWidget.replace(/__name__/g, counter);
    //         // Increase the counter
    //         counter++;
    //         // And store it, the length cannot be used if deleting widgets is allowed
    //         list.data('widget-counter', counter);
    
    //         // create a new list element and add it to the list
    //         var newElem = jQuery(list.attr('data-widget-tags')).html(newWidget);
    //         newElem.appendTo(list);
    //     });
    // });

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

   

    



