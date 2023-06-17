
//passage en "actif" du bouton de navigation suivant la page visualisée
document.querySelectorAll('.nav-link').forEach( link => {
    if(link.href === window.location.href){
        link.setAttribute('aria-current','page');
        link.classList.add('active');
    } else {
        link.removeAttribute('aria-current','page');
        link.classList.remove('active');
    }
})