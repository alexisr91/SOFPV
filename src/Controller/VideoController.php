<?php

namespace App\Controller;

use FFMpeg\FFMpeg;
use App\Entity\Video;
use App\Form\VideoType;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ResizeFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VideoController extends AbstractController
{ 
    //Initialisation de FFMPEG pour l'intégration des vidéos
    private function initFfmpeg(){
        return $ffmpeg = FFMpeg::create(array(
            'ffmpeg.binaries'=> 'C:/Users/Naerys/Desktop/Projet examen/SoFPV/ffmpeg/ffmpeg.exe',
            'ffprobe.binaries' => 'C:/Users/Naerys/Desktop/Projet examen/SoFPV/ffmpeg/ffprobe.exe',
            'timeout'=> 3600, 
            'ffmpeg.threads' => 12
        ));
    }

    #[Route('/profile/video', name: 'profile_video')]
    public function index(): Response
    {
        // $ffmpeg = $this->initFfmpeg();
        // dd($ffmpeg);

        return $this->render('account/video/index.html.twig', [
            'controller_name' => 'VideoController',
        ]);
    }

    //Ajouter une vidéo ( temporaire => TODO : intégrer dans un article)
    #[Route('/profile/video/add', name:'profile_video_add')]
    public function add(Request $request, SluggerInterface $slugger, EntityManagerInterface $manager){

        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid()){

            $ffmpeg = $this->initFfmpeg();
            $source = $form->get('source')->getData();

            if($source){  
                $originalName = $source->getClientOriginalName(); //récupère le nom  
                $upVideo = $ffmpeg->open($source->getRealPath()); //récupère le chemin pour traiter avec ffmpeg

                $sluggedName = $slugger->slug($originalName);
                $newName = $sluggedName.'-'.uniqId().'.mp4'; // nouveau nom + extension voulue
                $thumbName = $sluggedName.'-'.uniqId().'.png';// même principe pour la vignette générée

                //géneration de la video via ffmpeg
                $upVideo
                    ->filters()
                    ->resize(new Dimension(1920, 1080), ResizeFilter::RESIZEMODE_INSET) //redimension de la vidéo
                    ->synchronize(); 
              
                $upVideo->frame(TimeCode::fromSeconds(5))
                        ->save($this->getParameter('upload_thumb').'/'.$thumbName); //sauvegarde et deplacement de la vignette

                $video->setThumbnail($thumbName);

                $duration = $this->transformTime($upVideo->getFormat()->get('duration'));
                $video->setDuration($duration);

                //sauvegarde avec le codex X264
                $upVideo->save(new X264('libmp3lame', 'libopenh264'), $this->getParameter('upload_video').'/'.$newName);
                  
                $video->setSource($newName);
                $video->setUser($this->getUser());
             
                $manager->persist($video);
                $manager->flush();
                
            } else {
                return $this->redirectToRoute('profile_video_add');
            }
            return $this->redirectToRoute('account_myprofile');

        }
        return $this->render('account/video/add.html.twig', [
            'form' => $form->createView(),
            'title' => ' Ajouter une vidéo'
        ]);
    }

    //Gestion de la durée + gestion de l'affichage
    private function transformTime($second){
        $hours = floor($second/36000);
        $mins = floor(($second-($hours*3600))/60);
        $secs = floor($second % 60);
        $hours = ($hours<1)? "" : $hours."h";
        $mins = ($mins<10)? "0".$mins.":" : $mins.":";
        $secs = ($secs<10) ? "0".$secs : $secs;

        $duration = $hours.$mins.$secs;
        return $duration;
    }

    // //visionne la vidéo
    // #[Route('/show/{id}/view', name:'view_video')]
    // public function show(Video $video, EntityManagerInterface $manager){
    //     //incrémentation des views si le viewer n'est pas l'éditeur
    //     $author = $video->getUser()->getNickname();
    //     $title = $video->getTitle();

    //     if($this->getUser()!= $video->getUser()){
    //         $video->setViews($video->getViews() + 1 );
    //         $manager->persist($video);
    //         $manager->flush();
    //     }
    //     return $this->render('article/video/show.html.twig', [
    //         'video'=>$video,
    //         'title'=> $title.' - '.$author
    //     ]);
    // }

}
