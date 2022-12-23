
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