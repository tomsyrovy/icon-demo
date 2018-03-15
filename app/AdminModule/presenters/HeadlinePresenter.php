<?php

namespace AdminModule;

use Nette\Application\UI\Form;

class HeadlinePresenter extends BaseAdminPresenter{

	protected $repTalk;
	protected $repSpeaker;

	protected $talk_types;
    protected $blocks;
    protected $speakers;

	public function startup(){
		parent::startup();
		$this->repTalk = $this->context->talk;
		$this->repSpeaker = $this->context->speaker;
	}

	public function actionDefault(){
        $this->talk_types = $this->repTalk->getTalkTypes(TRUE);
        $this->speakers = $this->repSpeaker->getSpeakers(1)->fetchPairs("id", "full_name");
        $this->speakers[0] = "žádný řečník";
	}

	public function renderDefault(){

	}

	public function createComponentFormHeadline(){
		$f = $this->formHeadline();
		return $f;
	}

    private function formHeadline(){
        $f = new Form();
        foreach($this->talk_types as $t){
            $f->addGroup($t->title)->setOption('container', "fieldset class='galimatiash_".$t->class." ".$t->color."'");
            $talktypeContainer[$t->id] = $f->addContainer($t->id);
            $talktypeContainer[$t->id]->addText("headline_title", "Úvodní text: ")->setDefaultValue($t->headline_title);
            $talktypeContainer[$t->id]->addTextArea("headline_description", "Úvodní popis: ")->setDefaultValue($t->headline_description)->getControlPrototype()->class('tinymce perex');
            $headliners = $talktypeContainer[$t->id]->addContainer("headliners");
            for($i = 1; $i <= 8; $i++){
                $speakerContainer[$i] = $headliners->addContainer($i);
                $headliner = $this->repTalk->getHeadliner($i, $t->id);
                if($headliner){
                    $speakerContainer[$i]->addSelect("speaker_id", "Headliner ".$i.": ", $this->speakers)->setDefaultValue($headliner->speaker_id);
                    $speakerContainer[$i]->addTextArea("headliner_info", "Popis headlinera ".$i.": ")->setDefaultValue($headliner->headliner_info)->getControlPrototype()->class('tinymce perex');
                    $speakerContainer[$i]->addCheckBox("break", " po tomto headlineru zalomit a další headliner na novém řádku")->setDefaultValue($headliner->break);
                }else{
                    $speakerContainer[$i]->addSelect("speaker_id", "Headliner ".$i.": ", $this->speakers)->setDefaultValue(0);
                    $speakerContainer[$i]->addTextArea("headliner_info", "Popis headlinera ".$i.": ")->getControlPrototype()->class('tinymce perex');
                    $speakerContainer[$i]->addCheckBox("break", " po tomto headlineru zalomit a další headliner na novém řádku");
                }
            }
        }
        $f->setCurrentGroup(NULL);
        $f->addSubmit("save", "Uložit");
        $f->onSuccess[] = $this->formHeadlineSubmitted;
        return $f;
    }

    public function formHeadlineSubmitted(Form $f){
        $data = $f->getValues(TRUE);
        if($this->repTalk->saveHeadline($data)){
            $this->flashMessage(BaseAdminPresenter::$MESSAGE_SUCCESS, "success");
        }else{
            $this->flashMessage(BaseAdminPresenter::$MESSAGE_UNSUCCESS, "unsuccess");
        }
        $this->redirect("this");
    }

}
