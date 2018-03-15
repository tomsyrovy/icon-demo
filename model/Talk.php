<?php

namespace Repository;

use Nette;

/**
 * Provádí operace nad databázovou tabulkou.
 */
class Talk extends Repository{

    /**
     * Vrátí seznam všech typů přednášek
     * @param bool $all - vrátit všechny (TRUE), nebo pouze přednášky první úrovně (FALSE)
     * @param array $show_in_program - vrátit ty přednášky, které se mají zobrazovat v programu
     * @param null $in - vrátí vyselektované přednášky podle jejich id (parametrem je array)
     * @return Nette\Database\Table\Selection
     */
    public function getTalkTypes($all = FALSE, $show_in_program = array(0, 1), $in = NULL){
        if($in === NULL){
		    return ($all) ? $this->getTable('talk_type')->where("active", 1)->where("show_in_program", $show_in_program)->order('sort') : $this->getTable('talk_type')->where("active", 1)->where("parent_id", NULL)->where("show_in_program", $show_in_program)->order('sort');
        }else{
            return $this->getTable("talk_type")->where("active", 1)->where("show_in_program", $show_in_program)->where("id", $in)->order("sort");
        }
	}

    /**
     * Vrátí seznam všech částí přednášek úrovně 2 (bloky)
     * @return Nette\Database\Table\Selection
     */
    public function getTalkTypesOnlyBlocks(){
        return $this->getTable("talk_type")->where("parent_id IS NOT NULL")->where("active", 1)->order("sort");
    }

    /**
     * Uloží části přednášek. Vrátí výsledek true=provedeno, false=neprovedeno, provedeno z části
     * @param $data - data k uložení.
     * @return bool
     */
    public function saveTalkTypes($data, $column){
		$r = FALSE;
		foreach($data as $key => $value){
			$k = explode('_', $key);
			$key = $k[1];
			if($this->getTable('talk_type')->get($key)->update(array($column => $value))){
				$r = TRUE;
			}
		}
		return $r;
	}

    /**
     * Uloží headlinery pro předpogram. Vrátí výsledek true = provedeno, false = neprovedeno (provedeno z části)
     * @param $data - data k uložení
     * @return bool -
     */
    public function saveHeadline($data){

        $r = FALSE;

        $this->getTable("headliner")->delete();
        foreach($data as $key => $value){
            if($this->getTable("talk_type")->get($key)->update(array("headline_title" => $value["headline_title"], "headline_description" => $value["headline_description"]))){
                $r = TRUE;
            }
            foreach($value["headliners"] as $speakerKey => $speakerValue){
                if(!empty($speakerValue["headliner_info"]) AND !empty($speakerValue["speaker_id"])){
                    if($this->getTable("headliner")->insert(array("sort" => $speakerKey, "headliner_info" => $speakerValue["headliner_info"], "speaker_id" => $speakerValue["speaker_id"], "talk_type_id" => $key, "break" => $speakerValue["break"]))){
                        $r = TRUE;
                    }
                }
            }
        }
        return $r;
    }

    /**
     * Vrátí konkrétní headlinera podle části přednášek a podle umístění
     * @param $sort - pořadí
     * @param $talk_type_id - identifikátor části přednášek
     * @return Nette\Database\Table\ActiveRow
     */
    public function getHeadliner($sort, $talk_type_id){
        return $this->getTable("headliner")->get(array("talk_type_id" => $talk_type_id, "sort" => $sort));
    }

    /**
     * Vrátí headlinery pro konkrétní část přednášek
     * @param $talk_type_id - identifikátor části přednášek
     * @return Nette\Database\Statement
     */
    public function getHeadlinersOfType($talk_type_id){
        $statement = "
            SELECT h.*, s.*
            FROM headliner h
            JOIN speaker s ON h.speaker_id = s.id
            WHERE h.talk_type_id = ".$talk_type_id." AND s.active = 1
            ORDER BY h.sort
        ";
        return $this->connection->query($statement);
    }

    /**
     * Vrátí seznam všech přednášek
     * @param $active - udává, jaké přednášky vybrat (NotORM notace: 1=aktivní, 0=neaktivní, array(0,1)=všechny)
     * @return Nette\Database\Table\Selection
     */
    public function getTalks($active){
        return $this->getTable("talk")->where("active", $active); //TODO - řazení bodů programu do administrace?
    }

    /**
     * Vytvoří konkrétní přednášku a vrátí ji
     * @param $data - data k vytvoření přednášky
     * @return bool|Nette\Database\Table\ActiveRow
     */
    public function createTalk($data){
        $speakers = $data["speakers"];
        unset($data["speakers"]);
        $data["created"] = date("Y-m-d H:i");
        if($row = $this->getTable("talk")->insert($data)){
            foreach($speakers as $s){
                $this->getTable("speaker_has_talk")->insert(array("talk_id" => $row->id, "speaker_id" => $s));
            }
            return $row;
        }else {
            return FALSE;
        }
    }

    /**
     * Aktualizuje konkrétní přednášku a vrátí počet ovlivněných řádků
     * @param $talk_id - identifikátor přednášky
     * @param $data - data k editace
     * @return int
     */
    public function updateTalk($talk_id, $data){
        $speakers = $data["speakers"];
        unset($data["speakers"]);
        $row = $this->getTable("talk")->where("id", $talk_id)->update($data);
        $this->getTable("speaker_has_talk")->where("talk_id", $talk_id)->delete();
        foreach($speakers as $s){
            $this->getTable("speaker_has_talk")->insert(array("talk_id" => $talk_id, "speaker_id" => $s));
        }
        return $row;
    }

    public function rankTalk($talk_id, $ranking){
        return $this->getTable("ranking")->insert(array("talk_id" => $talk_id, "ranking" => $ranking));
    }

    /**
     * Vrátí konkrétní přednášku
     * @param $primary_key - identifikátor přednášky
     * @return Nette\Database\Table\ActiveRow
     */
    public function getTalk($primary_key){
        return $this->getTable("talk")->get($primary_key);
    }

    /**
     * Vrátí všechny (aktivní) řečníky dané přednášky
     * @param $talk_id - identifikátor přednášky
     * @param int $speaker_active - určuje jaký typ přednášejících vrátit (aktivní = 1, neaktivní (smazaní) = 0)
     * @return Nette\Database\Statement
     */
    public function getSpeakersOfTalk($talk_id, $speaker_active = 1){
        $statement = "
            SELECT sht.*, s.*
            FROM speaker_has_talk sht
            JOIN speaker s ON sht.speaker_id = s.id
            WHERE sht.talk_id = ".$talk_id." AND s.active = ".$speaker_active."
            ORDER BY s.lastname ASC";
        return $this->connection->query($statement);
    }

    public function getCountSpeakersOfTalk($talk_id, $speaker_active = 1){
        return $this->getTable("speaker_has_talk")->where("talk_id", $talk_id)->where("speaker.active", $speaker_active)->group("talk_id")->count("speaker_id");
    }

    /** Vrátí seznam místnost
     * @param bool $all - vrátit všechny místnosti (=true), nebo jen ty, kde se koná přednáška (=false)
     * @return Nette\Database\Statement|Nette\Database\Table\Selection
     */
    public function getRooms($all = TRUE){
        if($all){
            return $this->getTable("room");
        }else{
            $statement = "SELECT r.* FROM room r JOIN talk_type tt ON tt.room_id = r.id JOIN talk t ON t.talk_type_id = tt.id GROUP BY r.id";
            return $this->connection->query($statement);
        }
    }

    /**
     * Vrátí seznam všech přednášek daného řečníka
     * @param $speaker_id - identifikátor řečníka
     * * @param null $talk_type_ids - pole idček iCONtypů, ze kterých se mají vybrat přednášky
     * @return Nette\Database\Table\Selection
     */
    public function getTalksOfSpeaker($speaker_id, $talk_type_ids = array(6, 7, 8, 9)){
        $statement = "
            SELECT
              t.title as talk_title, t.starttime, t.endtime, t.perex, t.description,
              tt.title as talk_type_title, tt.date, tt.color, tt.class
            FROM speaker_has_talk st
              JOIN talk t ON st.talk_id = t.id
              JOIN talk_type tt ON tt.id = t.talk_type_id
            WHERE t.active = 1
              AND tt.id IN (".implode(",", $talk_type_ids).")
              AND tt.active = 1
              AND tt.show_in_program = 1
              AND st.speaker_id = $speaker_id
            ORDER BY tt.sort ASC, t.starttime ASC
        ";
        return $this->connection->query($statement);
    }

    public function getTalksOfTypeTalk($type_talk_id){
        return $this->getTable("talk")->where("active", 1)->where("talk_type_id", $type_talk_id)->order("starttime");
    }

}