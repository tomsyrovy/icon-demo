<?php

namespace AdminModule;

use Nette\Application\UI\Form;

class TextPresenter extends BaseAdminPresenter{

	protected $repText;

	public $texts;

	public function startup(){
		parent::startup();
		$this->repText = $this->context->text;
        $this->texts = $this->repText->getTexts();
	}

	public function createComponentFormText(){
		$f = new Form();
		foreach($this->texts as $text){
            $f->addTextArea($text->id, $text->label.":")->setDefaultValue($text->value);
        }
        $f->addSubmit("save", "Uložit");
        $f->onSuccess[] = $this->formTextSubmitted;
		return $f;
	}

	public function formTextSubmitted(Form $f){
		$values = $f->getValues(TRUE);

		if($this->repText->save($values)){
			$this->flashMessage(BaseAdminPresenter::$MESSAGE_SUCCESS, 'success');
		}else{
			$this->flashMessage(BaseAdminPresenter::$MESSAGE_UNSUCCESS, 'unsuccess');
		}
		$this->redirect('this');
	}

}
