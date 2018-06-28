<?php 
namespace RecognitionGame\Http\Controllers\Pages;

use RecognitionGame\Http\Controllers\Controller;
use Illuminate\Http\Request;

use RecognitionGame\Models\Image;
use RecognitionGame\Models\Imageage;
use RecognitionGame\Models\Theme;
use RecognitionGame\Models\Topic;
use RecognitionGame\Models\Topicage;
use RecognitionGame\Models\Webpagetext;
 
class MasterController extends Controller {
 
    public function changelang(Request $request) {
        session(['rg_lang'=>$request['lang']]);
        return redirect($request['route']);
    }

/************************************** Databaseinfo **********************************/
    public static function databaseinfo_Init_Static() {
        return  [                    
                    [   Theme::count(),
                        Topic::count()+Topicage::count(),
                        Image::count()+Imageage::count()   ],
                    MasterController::webpagetext_FromDB_Static([ 8, 1051, 1052, 9, 17, 16 ]),
                ];
    }

/********************************** Themetopic *******************************/
    public function themesTopics_FromDB(Request $request) {
        return response($this->themesTopics_FromDB_Static($request->all()));
    }

    public static function themesTopics_FromDB_Static($input_Array) {
        $back_Array=[];
        if (in_array(1051, $input_Array)){
            $tmp_Array = Theme::orderBy('name_'.session('rg_lang'))->get(['id','name_'.session('rg_lang')]);
            foreach ($tmp_Array as $item)
                array_push($back_Array,array('id' => "1051;".$item['id'] , 'text' => $item['name_'.session('rg_lang')] ));
        }
        if (in_array(1052, $input_Array)){
            $tmp_Array = Topic::orderBy('name_'.session('rg_lang'))->get(['id','name_'.session('rg_lang')]);
            foreach ($tmp_Array as $item)
                array_push($back_Array,array('id' => "1052;".$item['id'] , 'text' => $item['name_'.session('rg_lang')] ));
        }
        return $back_Array;
    }

/**************************************** Proposal ************************************/
    public static function proposal_Init_Static() {
        return  [
                    MasterController::webpagetext_FromDB_Static([ 1051, 1052, 67, 66, 10 ]),
                    MasterController::proposal_FromDB_Static()
                ];
    }

    public function proposal_FromDB(Request $request) {
        return response($this->proposal_FromDB_Static());
    }

    public static function proposal_FromDB_Static(){
        $name_Field = 'name_'.session('rg_lang');
        $theme_TMP = Theme::inRandomOrder()->get(['id', $name_Field])->first();
        $theme =    array(
                            'id' => '1051;'.$theme_TMP['id'].';'.$theme_TMP[$name_Field],
                            'text' => $theme_TMP[$name_Field]
                        );
        $topic_TMP = Topic::inRandomOrder()->get(['id', $name_Field])->first();
        $topic =    array(
                            'id' => '1052;'.$topic_TMP['id'].';'.$topic_TMP[$name_Field],
                            'text' => $topic_TMP[$name_Field]
                        );
        $lastTopics_TMP = Topic::orderBy('id','desc')->take(3)->get(['id',$name_Field]);
        $lastTopic = [];
        foreach ($lastTopics_TMP as $item)
            array_push($lastTopic,
                array(
                    'id' => '1052;'.$item['id'].';'.$item[$name_Field],
                    'text' => $item[$name_Field]
                )
            );
        
        return  [   $theme,
                    $topic,                    
                    $lastTopic[0],
                    $lastTopic[1],
                    $lastTopic[2]   ];
    }

/**************************************** Quickgame ***********************************/
    public static function quickgame_Init_Static() {
        return  [
                    MasterController::webpagetext_FromDB_Static([ 63, 64, 6, 7, 65, 38 ]),
                    MasterController::quickgame_FromDB_Static()
                ];
    }

    public function quickgame_FromDB(Request $request) {
        return response($this->quickgame_FromDB_Static());
    }

    public static function quickgame_FromDB_Static(){  
        $topic = Topic::whereIn('hungarian',[0, session('rg_lang')=='hu' ? 1 : 0])->inRandomOrder()->first();
        $image_Count = rand(2,$topic['image_to']-$topic['image_from'] + 1 > 10? 10 : $topic['image_to']-$topic['image_from'] + 1);
        $possibleAnswerID_Array = [];
        for($i=0;$i<$image_Count;$i++){
            $id=-1;
            do{
                $exists=false;
                $id = rand($topic->image_from, $topic->image_to);
                if(in_array($id, $possibleAnswerID_Array)) $exists = true;
            } while ($exists);
            $possibleAnswerID_Array[$i] = $id;
        }
        $correctAnswer_ID = $possibleAnswerID_Array[rand(0,$image_Count-1)];
        $answerArray_TMP = Image::whereIn('id',$possibleAnswerID_Array)->inRandomOrder()->get();
        $answer_Array = [];
        foreach($answerArray_TMP as $item){
            $bigImage = get_headers("http://www.felismerojatek.hu/kepek_big/".$topic['id']."/". $item['id'].".png");
            array_push($answer_Array,
                array(
                    'id' => $item['id'],
                    'text' => $item['name_'.session('rg_lang')],
                    'bigImage' => stripos($bigImage[0],"200 OK") ? true : false
                )
            );
        }
        return [    $topic,
                    $answer_Array,
                    $correctAnswer_ID,
                    MasterController::questionCompose_Static($topic->id, 1, null)
                ];
    }

/*************************************** Webpagetext ***********************************/
    public function webpagetext_FromDB(Request $request) {
        return response([$this->webpagetext_FromDB_Static($request->all())]);
    }

    public static function webpagetext_FromDB_Static( $ids ){
        $back_value=[];
        foreach($ids as $id)
            array_push($back_value,
                $id == -1 ? '' : Webpagetext::find($id)->getAttribute('name_'.session('rg_lang')));
        return $back_value;
    }

/********************************** Log the answers ************************************/
    public function answerLog_ToDB(Request $request) {
        return response($this->answerLog_ToDB_Static($request->all()));
    }

    public static function answerLog_ToDB_Static($input_Array){ 
        if ($input_Array[2] == 5){
            Imageage::find($input_Array[0])->increment('answer_total', 1);
            Imageage::find($input_Array[0])->increment('answer_sum', $input_Array[3]);
        }else{
            Image::find($input_Array[0])->increment('answer_total', 1);
            Image::find($input_Array[0])->increment('answer_good', $input_Array[1] ? 1 : 0);
        }
        return [];
    }

/************************************ Topic path **************************************/
    public static function topicPath_GetStatic( int $id ){
        $parent = 
            Theme::where('id',$id)->get(['parent','name_'.session('rg_lang')])->first();
        if ($parent['parent'] == 0)
            return [array('id' => $id, 'text' => $parent['name_'.session('rg_lang')])];
        $back_Array = array_merge(
            MasterController::topicPath_GetStatic($parent['parent']), 
            [array('id' => $id, 'text' => $parent['name_'.session('rg_lang')])]
        );
        return $back_Array;
    }

/*************************** All topics, themes of a theme *****************************/
    public function themesTopicsOfTheme(Request $request) {  
        return response([$this->themesTopicsOfTheme_Static($request->all()[0],$request->all()[1],$request->all()[2],$request->all()[3])]);
    }

    public static function themesTopicsOfTheme_Static( int $type, int $parent, bool $enablehungarian, bool $oddoneout){
        // 0 - Only Topic IDs [ 1, 5, 8, 14 ... ]
        // 1 - Themes and Topics ID and Name [{id: 1, text: 'Geography'},{id: 2, text: 'Art'}]
        $name_Field = 'name_'.session('rg_lang');
        $back_Array = [];
        $subThemes = 
            Theme::where('parent', $parent)->orderBy($name_Field)->get(['id', $name_Field, 'parent']);
        $subThemesTopics =[];
        foreach ($subThemes as $item){
            $subThemesTopics_TMP = MasterController::themesTopicsOfTheme_Static($type, $item['id'], $enablehungarian, $oddoneout) ;
            if (count($subThemesTopics_TMP)>0){
                $subThemesTopics = array_merge($subThemesTopics, $subThemesTopics_TMP);
                if ((count($subThemesTopics)>0)&&($type == 1))
                    array_push($back_Array, array( 'id' => '1051;'.$item['id'], 'text' => $item[$name_Field], 'parent' => $parent));
            }
        }
        if (count($subThemesTopics)>0){
            $back_Array = array_merge($back_Array, $subThemesTopics);
        }
        $subTopics = 
            Topic::where('theme', $parent)->orderBy($name_Field)->whereIn('hungarian', [false, $enablehungarian])->where('oddoneout', '>=', ($oddoneout ? 0 : -1))->get(['id', $name_Field]);
        if ($subTopics->count())
            foreach ($subTopics as $item){
                if ($type==0)
                    array_push($back_Array, $item['id']);
                if ($type==1)
                    array_push($back_Array, array( 'id' => '1052;'.$item['id'], 'text' => $item[$name_Field], 'parent' => $parent));
            }
        return $back_Array;
    }

/******************************** Compose the question text ****************************/
    public static function questionCompose_Static($topic_ID, $questiontype, $image_ID){
        $back_String = '';
        if ($questiontype!=5)
            $topic_String = Topic::find($topic_ID)->getAttribute('name_'.session('rg_lang'));
        else
            $topic_String = Topicage::find($topic_ID)->getAttribute('name_'.session('rg_lang'));
        if (session('rg_lang')=='hu')
            switch ($questiontype){
                case 1:
                    $back_String = "Melyik <i>".$topic_String."</i> látható a képen?";
                break;
                case 2:
                    $back_String = "Melyik <i>".$topic_String."</i> látható a képrészleten?";
                break;
                case 3:
                    $image_String = Image::find($image_ID)->getAttribute('name_'.session('rg_lang'));
                    $back_String = "A képen látható ".$topic_String.":<br /><i>".$image_String."</i>";
                break;
                case 4:
                    $back_String = "Melyik nem <i>".$topic_String."</i>?";
                break;
                case 5:
                    $back_String = "Hány éves a képen látható <i>".$topic_String."</i>?";
                break;
            }
        else
            switch ($questiontype){
                case 1:
                    $back_String = "Which <i>".$topic_String."</i> is this image?";
                break;
                case 2:
                    $back_String = "Which <i>".$topic_String."</i> is this image detail?";
                break;
                case 3:
                    $image_String = Image::find($image_ID)->getAttribute('name_'.session('rg_lang'));
                    $back_String = "The ".$topic_String." on the image:<br /><i>".$image_String."</i>";
                break;
                case 4:
                    $back_String = "Which is the odd one out?";
                break;
                case 5:
                    $back_String = "How old is the <i>".$topic_String."</i> in this image?";
                break;
            }
        return $back_String;
    }

/******************************** Compose the True-False answer text ****************************/
    public static function answerFalseCompose_Static($text){
        if (session('rg_lang')=='hu')
            return "Hamis, mert ".$text;
        else
            return "False, because ".$text;
    }
}