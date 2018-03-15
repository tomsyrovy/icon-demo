<?php

namespace Repository;

use FrontModule\BaseFrontPresenter;
use Nette;

/**
 * Provádí operace nad databázovou tabulkou.
 */
class OwnProgram extends Repository{

    private $talks;
    private $email;
    private $own_program;

    public function saveOwnProgram($data){
        $this->email = $data["email"];
        $this->own_program = $this->saveOwnProgramMain($this->email);
        if($this->own_program){
            unset($data["email"]);
            $this->talks = array();
            foreach($data as $a){
                foreach($a as $key => $value){
                    if($value == 1){
                        $this->talks[] = $key;
                    }
                }
            }
            if($this->saveTalks($this->own_program, $this->talks)){
                return $this->own_program;
            }
        }
        return false;
    }

    private function saveOwnProgramMain($email){
        return $this->getTable("own_program")->insert(array("email" => $email));
    }

    private function saveTalks($own_program, $talks){
        foreach($talks as $talk){
            if(!$this->getTable("own_program_has_talk")->insert(array("own_program_id" => $own_program->id, "talk_id" => $talk))){
                return false;
            }
        }
        return true;
    }

    public function getOwnProgram($own_program_id){
        return $this->getTable("own_program_has_talk")->where("own_program_id", $own_program_id)->where("talk.active", 1)->where("talk.talk_type_id", BaseFrontPresenter::$iCONtypeShow)->order("talk.starttime");
    }

    public function getStats($type){
        switch($type){
            case "talks" : {
                return $this->getStatsTalks();
            }break;
            case "speakers" : {
                return $this->getStatsSpeakers();
            }break;
            case "subscribers" : {
                return $this->getStatsSubscribers();
            }break;
        }
    }

    private function getStatsTalks(){
        $crate = array();
        $crate["title"] = "Nejvíce žádané přednášky";
        $crate["captions"] = array("Přednáška", "iCON blok", "Počet výskytů");
        $statement = "
          SELECT talk.title as t, talk_type.title as t2, count(own_program_has_talk.talk_id) as t3
          FROM own_program_has_talk
            JOIN talk ON own_program_has_talk.talk_id = talk.id
            JOIN talk_type ON talk_type.id = talk.talk_type_id
          GROUP BY own_program_has_talk.talk_id
          ORDER BY t3 DESC";
        $crate["result"] = $this->connection->query($statement);

        return $crate;
    }

    private function getStatsSpeakers(){
        $crate = array();
        $crate["title"] = "Nejvíce žádaní řečníci";
        $crate["captions"] = array("Jméno", "Příjmení", "Počet výskytů");
        $statement = "
          SELECT speaker.firstname as t, speaker.lastname as t2, COUNT( speaker.lastname ) AS t3
          FROM own_program_has_talk
            JOIN speaker_has_talk ON own_program_has_talk.talk_id = speaker_has_talk.talk_id
            JOIN speaker ON speaker.id = speaker_has_talk.speaker_id
          GROUP BY speaker.lastname
          ORDER BY t3 DESC ";
        $crate["result"] = $this->connection->query($statement);

        return $crate;
    }

    private function getStatsSubscribers(){
        $crate = array();
        $crate["title"] = "E-maily z Vlastního programu";
        $crate["captions"] = array("E-mail", "", "");
        $statement = "
          SELECT DISTINCT(email) AS t, '' AS t2, '' AS t3 FROM  `own_program`
        ";
        $crate["result"] = $this->connection->query($statement);

        return $crate;
    }


}