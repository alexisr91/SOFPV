<?php

namespace App\Services;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


//Medias' service
class Media
{

    private $slugger;
    //to get path parameters
    private $param;

    public function __construct(SluggerInterface $slugger, ParameterBagInterface $param)
    {
        $this->slugger = $slugger;
        $this->param = $param;
    }
       
    // GESTION IMAGE (needs img source and path to save it)
    public function saveImageAndGetName($image, $path): string
    {
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME); // récupère le nom  - get original name
        $sluggedName = $this->slugger->slug($originalName); // slug the name
        $imgNewName = $sluggedName.'-'.uniqid().'.'.$image->guessExtension(); // nouveau nom + extension voulue - new name + chosen extension

        // try {
            $image->move($path, $imgNewName);
            // } catch (FileException $e) {
            //     // dd($e->getMessage());
            // }

        return $imgNewName;     
    }


    // Convert URL provided by user to an embed Youtube URL
    // Conversion de l'URL fourni par l'user en URL lisible avec Youtube (embed)
    public function convertYT($videoURL): string
    {
        //  from https://www.youtube.com/watch?v=Ojs5cERnQqg
        // or from https://youtu.be/Ojs5cERnQqg?feature=shared
        // or from https://m.youtube.com/watch?v=Ojs5cERnQqg

        // to https://www.youtube.com/embed/Ojs5cERnQqg which is readable

        //if url gotten by option "share" of Youtube
        if(str_contains($videoURL, 'youtu.be')){
            //convert first part of URL string
            $firstConvert = str_replace('youtu.be', 'www.youtube.com', $videoURL);

            //explode url
            $explode = explode('/', $firstConvert);

            //get parts of "https://www.youtube.com/" and recompose it
            $baseOfURL = $explode[0].'//'.$explode[2].'/';

            //get part of URL wich contain video reference ex:"Ojs5cERnQqg?feature=shared"
            $videoRef = $explode[3]; 

            //concatenate with 'embed/' to get valid format
            $convertedURL = $baseOfURL."embed/".$videoRef;
            
            // delete all string after '?'  
            $convertedURL = strtok($convertedURL, '?');    

        //if url is gotten through mobile browser
        } else if(str_contains($videoURL, 'm.youtube')) {
            //convert
            $convertedURL = str_replace('m.youtube', 'www.youtube', $videoURL);
            $convertedURL = str_replace('watch?v=', 'embed/', $videoURL);

            // delete all string after '&' to avoid youtube channel error
            $convertedURL = strtok($convertedURL, '&');

        } else {
             // Difference entre  watch?v= et embed/
            $convertedURL = str_replace('watch?v=', 'embed/', $videoURL);
            // suppression de la partie concernant le channel Youtube (https://www.youtube.com/xxxxxxxxxxxx&ab_channel=LofiGirl)
            $convertedURL = strtok($convertedURL, '&');
        }
        return $convertedURL;
    }

}