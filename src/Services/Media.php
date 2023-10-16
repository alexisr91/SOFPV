<?php

namespace App\Services;


use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ResizeFilter;
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

    //Video processing and make a thumbnail 
    public function VideoProcessingAndReturnDatas(mixed $videoSource) : array
    {
        // processing with FFMPEG
        // traitement avec FFmpeg
        $ffmpeg = $this->initFfmpeg();

        $originalName = $videoSource->getClientOriginalName(); // récupère le nom  - get original name
        $upVideo = $ffmpeg->open($videoSource->getRealPath()); // récupère le chemin pour traiter avec ffmpeg - get path

        $sluggedName = $this->slugger->slug($originalName); // slug the name
        $videoNewName = $sluggedName.'-'.uniqid().'.mp4'; // nouveau nom + extension voulue - new name + chosen extension
        $thumbName = $sluggedName.'-'.uniqid().'.png'; // même principe pour la vignette générée - same process for thumbnail

        // géneration de la video via ffmpeg, redimensionnement + synchro son
        // generate video through ffmpeg , resizing and synchronize sound
        $upVideo
            ->filters()
            ->resize(new Dimension(1920, 1080), ResizeFilter::RESIZEMODE_INSET)
            ->synchronize();

        // thumbnail created from 5 seconds from video starting
        // vignette créée à partir de la vidéo, 5 secondes après le debut
        $upVideo->frame(TimeCode::fromSeconds(5))
                // sauvegarde et déplacement de la vignette - save and move of thumbnail
                ->save($this->param->get('upload_thumb').'/'.$thumbName); 


        // get video duration
        // récupération du temps de la video
        $duration = $this->transformTime($upVideo->getFormat()->get('duration'));

        // save with x264 codec
        // sauvegarde avec le codec X264
        $upVideo->save(new X264('libmp3lame', 'libx264'), $this->param->get('upload_video').'/'.$videoNewName);

        return compact('duration', 'videoNewName', 'thumbName');
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

    // Initialisation de FFMPEG - FFMPEG init
    private function initFfmpeg(): FFMpeg
    {
        // récupère le chemin vers la racine du projet - path to root of project
        $dir = $this->param->get('project_dir');
        dd($dir);

        return $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => $dir.'/ffmpeg/ffmpeg.exe',
            'ffprobe.binaries' => $dir.'/ffmpeg/ffprobe.exe',
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ]);
    }

    // calcul de la durée de video - duration sum
    private function transformTime(int $second): string
    {
        $hours = floor($second / 36000);
        $mins = floor(($second - ($hours * 3600)) / 60);
        $secs = floor($second % 60);
        $hours = ($hours < 1) ? '' : $hours.'h';
        $mins = ($mins < 10) ? '0'.$mins.':' : $mins.':';
        $secs = ($secs < 10) ? '0'.$secs : $secs;

        $duration = $hours.$mins.$secs;

        return $duration;
    }

}