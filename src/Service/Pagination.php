<?php

namespace App\Service;

use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Pagination {


    private $entityClass;
    private $limit = 10;
    private $currentPage = 1;
    private $manager;

    //permet de selectionner l'ordre de tri par date ( DESC ou ASC )
    private $order;
    //permet de selectionner la propriété recherchée par le findBy si il y a besoin
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


    //  pagination à partir de n'importe quelle entité => création d'un getter et setter
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
    

    //si la propriété ou la valeur est nulle, on retourne false
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
        
        $total = count($repo->findAll());
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