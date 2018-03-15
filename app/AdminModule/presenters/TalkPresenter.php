<?php

namespace AdminModule;

use Nette\Application\UI\Form;

class TalkPresenter extends BaseAdminPresenter{

    private $REGEX_DATETIME  = '\d{4}-\d{2}-\d{2} [0-2][0-9]:[0-5][0-9]:?[0-9]{0,2}';

	protected $repTalk;
	protected $repSpeaker;

	public $talks;
	public $talk;

	public $talk_types;

	protected $defaultDataOfColumn;

	public function startup(){
		parent::startup();
		$this->repTalk = $this->context->talk;
		$this->repSpeaker = $this->context->speaker;
	}

	public function actionDefault($talk_id = NULL){
        if($talk_id !== NULL){
            $this->talk = $this->repTalk->getTalk($talk_id);
        }
        $this->talks = $this->repTalk->getTalks(array(0, 1));
	}

	public function renderDefault($talk_id = NULL){
        if($talk_id !== NULL){
            $this->template->talk_id = $this->talk->id;
        }else{
            $this->template->talk_id = 0;
        }
        $this->template->talks = $this->talks;
	}

	private function formTalk(){
		$f = new Form();
		$f->addText('title', 'Název:');

		$speakers = $this->repSpeaker->getSpeakers(1)->fetchPairs('id', 'full_name');

        if($this->talk){
            $selectedSpeakers = array();
            foreach($this->repTalk->getSpeakersOfTalk($this->talk->id) as $s){
                $selectedSpeakers[] = $s->speaker_id;
            }
            $f->addMultiSelect('speakers', 'Řečníci:', $speakers, 1)->setOption('description', 'Hromadný výběr vytvoříte podržením klávesy CTRL (nebo CMD jste-li jablíčkář).')->setDefaultValue($selectedSpeakers);
        }else{
            $f->addMultiSelect('speakers', 'Řečníci:', $speakers, 1)->setOption('description', 'Hromadný výběr vytvoříte podržením klávesy CTRL (nebo CMD jste-li jablíčkář).');
        }
//        $f->setCurrentGroup("Řečníci");
//        foreach($speakers as $id => $name){
//            $f->addCheckbox($id, $name);
//        }
//        $f->setCurrentGroup(NULL);

		$talk_types = $this->repTalk->getTalkTypes(TRUE)->fetchPairs('id', 'full_title');
//        unset($talk_types[2]);
		$f->addSelect('talk_type_id', 'Typ:', $talk_types, 1)->addCondition(Form::EQUAL, 2)->toggle('block_id');
		$f->addText('starttime', 'Začátek:')->addRule(Form::PATTERN, 'Čas začátku musí být ve formátu yyyy-mm-dd hh:mm.', $this->REGEX_DATETIME)->setOption('description', 'yyyy-mm-dd hh:mm');
		$f->addText('endtime', 'Konec:')->addRule(Form::PATTERN, 'Čas konce musí být ve formátu yyyy-mm-dd hh:mm.', $this->REGEX_DATETIME)->setOption('description', 'yyyy-mm-dd hh:mm');
		$f->addTextArea('perex', 'Perex:')->setAttribute('class', 'tinymce perex');
		$f->addTextArea('description', 'Popis:')->setAttribute('class', 'tinymce');

        $f->onSuccess[] = $this->formTalkSubmitted;
		return $f;
	}

	public function createComponentFormTalk(){
		$f = $this->formTalk();
        if($this->talk){
			$defaults = $this->talk;
			$f->setDefaults($defaults);
			$f->addSubmit('save', 'Uložit');
		}else{
			$f->addSubmit('create', 'Vytvořit');
		}
		return $f;
	}

    public function formTalkSubmitted(Form $f){
        $data = $f->getValues("TRUE");
        if($this->talk){
            $result = $this->repTalk->updateTalk($this->talk->id, $data);
            $param = $this->talk->id;
        }else{
            $result = $this->repTalk->createTalk($data);
            $param = $result->id;
        }
        if($result){
			$this->flashMessage(BaseAdminPresenter::$MESSAGE_SUCCESS, 'success');
		}else{
			$this->flashMessage(BaseAdminPresenter::$MESSAGE_UNSUCCESS, 'unsuccess');
		}
		$this->redirect('this', $param);
    }

    public function actionDescription(){
        $this->defaultDataOfColumn = "program_description";
    }

    public function createComponentFormTypeTalk(){
        $f = new Form();
        foreach($this->repTalk->getTalkTypes($this->defaultDataOfColumn == "program_description") as $type){
            switch($this->defaultDataOfColumn){
                case "description" : {
                    $label = "Popis na hlavní stránce u ".$type->title;
                    $default = $type->description;
                }
                    break;
                case "program_description" : {
                    $label = "Popis v rámci programu u ".$type->title;
                    $default = $type->program_description;
                }
                    break;
            }
            $f->addTextArea('t_'.$type->id, $label.': ')->setDefaultValue($default)->setAttribute("class", "tinymce");
        }
        $f->addSubmit('save', 'Uložit');
        $f->onSuccess[] = $this->formTypeTalksSubmitted;
        return $f;
    }

    public function formTypeTalksSubmitted(Form $f){
        $data = $f->getValues(TRUE);
        if($this->repTalk->saveTalkTypes($data, $this->defaultDataOfColumn)){
            $this->flashMessage(BaseAdminPresenter::$MESSAGE_SUCCESS, 'success');
        }else{
            $this->flashMessage(BaseAdminPresenter::$MESSAGE_UNSUCCESS, 'unsuccess');
        }
        $this->redirect('this');
    }

}
