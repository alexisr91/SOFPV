
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

    //GESTION DE LA COLLECTION D'IMAGES
    jQuery(document).ready(function () {
        jQuery('#add_images').click(function (e) {
            var list = jQuery(jQuery(this).attr('data-list-selector'));
            // Try to find the counter of the list or use the length of the list
            var counter = list.data('widget-counter') || list.children().length;
    
            // grab the prototype template
            var newWidget = list.attr('data-prototype');
            // replace the "__name__" used in the id and name of the prototype
            // with a number that's unique to your emails
            // end name attribute looks like name="contact[emails][2]"
            newWidget = newWidget.replace(/__name__/g, counter);
            // Increase the counter
            counter++;
            // And store it, the length cannot be used if deleting widgets is allowed
            list.data('widget-counter', counter);
    
            // create a new list element and add it to the list
            var newElem = jQuery(list.attr('data-widget-tags')).html(newWidget);
            newElem.appendTo(list);
        });
    });
