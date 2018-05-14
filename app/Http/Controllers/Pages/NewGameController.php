<?php namespace RecognitionGame\Http\Controllers\Pages;
 

use RecognitionGame\Http\Controllers\Controller;
 
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use RecognitionGame\Http\Requests;
use RecognitionGame\Http\Controllers\Pages\MasterController;

use RecognitionGame\Models\Topic;
use RecognitionGame\Models\Image;

class NewGameController extends Controller {
 
    public function index($sessionData_Random = null ) {
        if (!session()->has('rg_newgame_'.$sessionData_Random))
            return redirect('index');
        view()->share('share_pageID', 5);
        view()->share('share_sessionData_Random', $sessionData_Random);
        return view('pages/newgame');
    }    


    public function init(Request $request) {
        return response(
            [
                MasterController::webpagetext_FromDB_Static([27, 28, 29, 24, 16, 17]),
                MasterController::webpagetext_FromDB_Static([31, 63, 64, 35, 6, 7, 36]),
                NewGameController::currentGame_Data_Static($request->all())
            ]
        );
    }

    public function currentGame_Data(Request $request) {
        return response($this->currentGame_Data_Static($request->all()));
    }

    public static function currentGame_Data_Static($input_Array) {
        $session_Array = session('rg_newgame_'.$input_Array[1]);
        switch ($input_Array[0]){
            case -2:
                $session_Array[2][0]++;
                $session_Array[2][1] = -1;
                array_push( $session_Array[3], $session_Array[1]['image_ID']); 
                $session_Array[1] = NewGameController::drawNextQuestion_Static($session_Array[4][$session_Array[2][0]-1], $session_Array[3] );
            break;
            case -1:
            
            break;
            default:
                $topic_Array = $session_Array[1];
                if ($input_Array[0] == $topic_Array['image_ID'] ){
                    MasterController::answerLog_ToDB_Static([$topic_Array['image_ID'], true]);
                    $session_Array[2][2] = $session_Array[2][2] + 1;
                }else
                    MasterController::answerLog_ToDB_Static([$topic_Array['image_ID'], false]);
                $session_Array[2] = [$session_Array[2][0], $input_Array[0], $session_Array[2][2], $session_Array[2][3]];
            break;            
        }
        session(['rg_newgame_'.$input_Array[1] => $session_Array]);
        return $session_Array;
    }

    public static function drawNextQuestion_Static($topic_ID, $answeredQuestionID_Array){
        $topics_Array = [];
        $image_from = Topic::find($topic_ID)->getAttribute('image_from');
        $image_to = Topic::find($topic_ID)->getAttribute('image_to');
        $topic_source = Topic::find($topic_ID)->getAttribute('source');
        // Draw an unused imagePlace
        $image_ID=-1;
        do{
            $image_Exists = false;
            $image_ID = rand( $image_from , $image_to);
            if (in_array($image_ID, $answeredQuestionID_Array)) $image_Exists=true;
        } while ($image_Exists);
        // Draw other images
        $images = [];
        $images_Count = rand (2, $image_to - $image_from + 1 < 10 ? $image_to - $image_from + 1 : 10);
        for($j=0;$j<$images_Count-1;$j++){
            $image=-1;
            do{
                $exists = false;
                $image = rand ($image_from,$image_to);
                $drawed = false;
                foreach($images as $item)
                    if ($item['id']==$image) $drawed = true;
                if ($drawed||($image == $image_ID)) $exists=true;
            } while($exists);
            array_push($images, array(
                'id' => $image, 
                'text' => Image::find($image)->getAttribute('name_'.session('rg_lang'))
            ));
        }
        array_push($images, array(
            'id' => $image_ID, 
            'text' => Image::find($image_ID)->getAttribute('name_'.session('rg_lang'))
        ));
        shuffle($images);
        $bigImages = [];
        foreach($images as $image){
            $bigImage = get_headers("http://www.felismerojatek.hu/kepek_big/".$topic_ID."/".($image['id'] - $image_from + 1).".png");
            $bigImage_Exists = stripos($bigImage[0],"200 OK") ? '_big' : '';
            array_push($bigImages, $bigImage_Exists);
        }
        $topic_Array = [];
        $topic_Array['image_ID'] = $image_ID;
        $topic_Array['images'] = $images;
        $topic_Array['bigImages'] = $bigImages;
        $topic_Array['topic_ID'] = $topic_ID;
        $topic_Array['topic_ImageFrom'] = $image_from;
        $topic_Array['topic_Path'] = MasterController::topicPath_GetStatic( Topic::find($topic_Array['topic_ID'])->getAttribute('theme') );
        $topic_Array['topic_Question'] = MasterController::questionCompose_Static($topic_Array['topic_ID']);
        $topic_Array['topic_Source'] = $topic_source;
        return $topic_Array;
    }
}