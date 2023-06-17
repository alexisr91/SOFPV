let btn = document.querySelector('.collapsedMenuBtn');

//ouverture et fermeture de la sidebar + changement d'icone pour le bouton

btn.addEventListener('click', function(e){
    let bar = document.querySelector('.collapseMenu');

    if(bar.classList.contains('open')){
        btn.innerHTML = '<i class="fa-solid fa-caret-right"></i>';
        bar.classList.remove('open');
        bar.classList.add('close');
   

    } else if (bar.classList.contains('close')){  
        btn.innerHTML = '<i class="fa-solid fa-caret-left"></i>';
        bar.classList.remove('close');
        bar.classList.add('open');
      
    }  
     else {  
        btn.innerHTML = '<i class="fa-solid fa-caret-left"></i>';
        bar.classList.add('open');
      
    }  
    
    e.preventDefault();
})
