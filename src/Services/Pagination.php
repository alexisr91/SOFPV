<?php

namespace App\Services;

use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Pagination {


    private $entityClass;
    private $limit = 10;
    private $currentPage = 1;
    private $manager;

    private $order;
    private $property;
    private $value;

    private $twig;
    private $route;

    private $templatePath;

    public function __construct(EntityManagerInterface $manager, Environment $twig, RequestStack $request, $templatePath){

        $this->route = $request->getCurrentRequest()->attributes->get('_route');
        //dump($this->route);die;
        $this->manager = $manager;
        $this->twig = $twig;
        $this->templatePath = $templatePath;
       
    }

    public function display(){
        //appelle le moteur twig et on précise quel template on veut utiliser
        $this->twig->display($this->templatePath, [
            //options nécessaires à l'affichage des données
            //variables : route / page / pages
            'page'=>$this->currentPage,
            'pages'=>$this->getPages(),
            'route'=>$this->route

        ]);
    }


    //pagination à partir de n'importe quelle entité 
    // setter et getter pour chaqeu "option" du findby
    public function setEntityClass($entityClass){
        $this->entityClass = $entityClass;
        return $this;
    }

    public function getEntityClass(){
        return $this->entityClass;
    }

    // Limite des pages
    public function getLimit(){
        return $this->limit;
    }

    public function setLimit($limit){
        $this->limit = $limit;
        return $this;
    }

    // Page actuelle
    public function getPage(){
        return $this->currentPage;
    }

    public function setPage($page){
        $this->currentPage = $page;
        return $this;
    }

    //Choix du tri : DESC ou ASC par date
    public function getOrder(){
        return $this->order;
    }
    public function setOrder($order){
        $this->order = $order;
        return $this;
    }

    //choix de la propriété et valeur du findBy
    public function getProperty(){
        return $this->property;
    }
    public function setProperty($property){
        $this->property = $property;
        return $this;
    }
    public function getValue(){
        return $this->value;
    }
    public function setValue($value){
        $this->value = $value;
        return $this;
    }
    

    //Est ce qu'on a indiqué une propriété et une valeur pour le critère du findBy ?
    private function isThereAProperty(){
        if($this->property == null || $this->value == null){
            return false;
        } else {
            return true;
        }
    }

    
    //On récupère les données de chaque entité (toutes les données accessibles par les getters ) tout en contraignant l'affichage par page
    public function getData(){
       
        //en cas d'oubli de choix de l'entité sur laquelle appliquer la pagination, on renvoie une exception
        if(empty($this->entityClass)){
            throw new \Exception("setEntityClass n'a pas été renseigné dans le controller correspondant.");
        }

        //calcul l'offset ( à partir de quel élément on affiche les resultats)
        $offset =  $this->currentPage * $this->limit - $this->limit;

        //on demande au repository de trouver les éléments
        //on va chercher le bon repository
        $repo = $this->manager->getRepository($this->entityClass);

        $order = $this->order;
        
        //on construit notre requete suivant si le critère propriété et valeur a été indiqué
        if(!$this->isThereAProperty() ){
            $data = $repo->findBy([],['createdAt'=> $order], $this->limit, $offset);
            return $data;
        } else {
           $data = $repo->findBy([$this->property => $this->value],['createdAt'=> $order], $this->limit, $offset); 
           return $data;
        }
    }
    
    // Nombre de pages total
    public function getPages(){

        $repo = $this->manager->getRepository($this->entityClass);

        //si il n'y a pas de propriété indiquée, on récupère tout le repository d'articles (pour les parties Actualités/Blog publiques par exemple)
        if(!$this->isThereAProperty() ){
            $total = count($repo->findAll());
            //dd($total,'property false');
        } else { 
         //si une propriété a été indiquée, on se base sur elle pour compter le nombre total de pages (pour la partie mon profil : mes articles par exemple, où on ne compte que les articles de l'user)
           $total = count($repo->findBy([$this->property => $this->value]));
           //dd($total,'property true');
        }

        $pages = ceil($total / $this->limit);

        return $pages;
    }
    

    public function getRoute(){
       return $this->route;
       
    }

    public function setRoute($route){
        $this->route = $route;
        return $this;
    }

    public function getTemplatePath(){
        return $this->templatePath;
    }

    public function setTemplatePath($templatePath){
        $this->templatePath = $templatePath;
        return $this;
    }
    
}