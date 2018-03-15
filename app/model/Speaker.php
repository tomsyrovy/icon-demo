<?php

namespace Repository;

use Nette;

/**
 * Provádí operace nad databázovou tabulkou.
 */
class Speaker extends Repository{

	/**
	 * Vytvoří řečníka - uloží do databáze
	 * @param $data
	 *
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function create($data){
		$dataSocial = $data['socials'];
		unset($data['socials']);
		$rowSpeaker = $this->getTable('speaker')->insert($data);
		foreach($dataSocial as $key => $value){
			$data = array('speaker_id' => $rowSpeaker->id, 'social_banner_id' => $key, 'link' => $value);
			$this->getTable('speaker_has_social_banner')->insert($data);
		}
		return $rowSpeaker;
	}

	/**
	 * Aktualizuje řečníka
	 * @param $primary_key
	 * @param $data
	 */
	public function update($primary_key, $data){
		$dataSocial = $data['socials'];
		unset($data['socials']);
		$r = $this->getTable('speaker')->get($primary_key)->update($data);
		$r2 = FALSE;
		foreach($dataSocial as $key => $value){
			$data = array('link' => $value);
			if($this->getTable('speaker_has_social_banner')->get(array('speaker_id' => $primary_key, 'social_banner_id' => $key))->update($data)){
				$r2 = TRUE;
			}
		}
		return ($r OR $r2);
	}

	/**
	 * Vrátí seznam všech řečníků
	 * @param array $active
	 *
	 * @return Nette\Database\Table\Selection
	 */
	public function getSpeakers($active = array(0,1)){
		return $this->getTable('speaker')->select("*, CONCAT(firstname, ' ', lastname) AS full_name")->where('active', $active)->order('lastname, firstname');
	}

	/**
	 * Vrátí seznam řečníků, kteří se mají zobrazovat v seznamu řečníků na FrontPage
	 */
	public function getSpeakersInList(){
		return $this->getTable('speaker')->where(array('active' => 1, 'in_list' => 1))->order('lastname, firstname');
	}

	/**
	 * Vrátí řečníka podle primárního klíče
	 * @param $primary_key
	 *
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function getSpeaker($primary_key){
		return $this->getTable('speaker')->get($primary_key);
	}

	/**
	 * Vrátí řečníka podle nice_url
	 * @param $nice_url
	 *
	 * @return Nette\Database\Table\Selection
	 */
	public function getSpeakerByNiceUrl($nice_url){
		$result = $this->getTable('speaker')->where(array('nice_url' => $nice_url, 'active' => 1, 'in_list' => 1));
		if($result->count() == 0){
			return FALSE;
		}else{
			foreach($result as $r){
				$id = $r->id;
			}
			return $this->getSpeaker($r->id);
		}
	}

	/**
	 * Vrátí prvního použitelného řečníka pro seznam
	 * @return Nette\Database\Table\ActiveRow
	 * @throws \Exception
	 */
	public function getSpeakerFirst(){
		$result = $this->getTable('speaker')->where(array('active' => 1, 'in_list' => 1))->order('lastname, firstname')->limit(1);
		if($result->count() == 0){
			throw new \Exception('V databázi není žádný použitelný řečník');
		}else{
			foreach($result as $r){
				$id = $r->id;
			}
			return $this->getSpeaker($r->id);
		}
	}

	/**
	 * Vrátí seznam všech sociálních bannerů
	 * @return Nette\Database\Table\Selection
	 */
	public function getSocialTypes(){
		return $this->getTable('social_banner');
	}

	/**
	 * Vrátí url odkaz sociálního banneru zadaného řečníka - používá se v Backend
	 * @param $speaker_id
	 * @param $social_banner_id
	 *
	 * @return bool|null
	 */
	public function getSocialOfSpeaker($speaker_id, $social_banner_id){
		return $this->getTable('speaker_has_social_banner')->get(array('speaker_id' => $speaker_id, 'social_banner_id' => $social_banner_id))->link;
	}

	/**
	 * Vrátí seznam social banners zadaného řečníka - používá se ve Frontend
	 * @param $speaker_id
	 *
	 * @return Nette\Database\Statement
	 */
	public function getSocialsOfSpeaker($speaker_id){
		$statement = "
			SELECT s.*, ssb.*, sb.*
			FROM speaker s
			JOIN speaker_has_social_banner ssb ON s.id = ssb.speaker_id
			JOIN social_banner sb ON sb.id = ssb.social_banner_id
			WHERE s.id = ".$speaker_id." AND s.active = 1 AND s.in_list = 1 AND ssb.link != ''
			ORDER BY sb.id ASC
		";
		return $this->connection->query($statement);
	}

}