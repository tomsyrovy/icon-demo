<?php

namespace AdminModule;

use Nette\Application\UI\Form;
use Nette\Utils\Html;

class SpeakerPresenter extends BaseAdminPresenter{

	protected $repSpeaker;

	public $speakers;
	public $speaker;

	public function startup(){
		parent::startup();
		$this->repSpeaker = $this->context->speaker;
	}

	public function actionDefault($speaker_id = NULL){
		$this->speakers = $this->repSpeaker->getSpeakers();
		$this->speaker = $this->repSpeaker->getSpeaker($speaker_id);
	}

	public function renderDefault($speaker_id = NULL){
		$this->template->speakers = $this->speakers;
		$this->template->speaker_id = $speaker_id;
		$this->template->speaker = $this->speaker;
	}

	private function formSpeaker(){
		$f = new Form();
		$f->addText('firstname', 'Jméno:')->addRule(Form::FILLED, 'Jméno řečníka je povinná položka.');
		$f->addText('lastname', 'Příjmení:')->addRule(Form::FILLED, 'Příjmení řečníka je povinná položka.');
		$f->addUpload('image', 'Portrét:')->addCondition(Form::FILLED)->addRule(Form::IMAGE, 'Vkládaný portrét musí být obrázek.');
		$f->addUpload('image_small', 'Portrét malý:')->addCondition(Form::FILLED)->addRule(Form::IMAGE, 'Vkládaný malý portrét musí být obrázek.');
		$f->addText('company', 'Společnost:');
		$f->addText('position', 'Pozice:');
		$f->addTextArea('description', 'Popis:')->getControlPrototype()->class('tinymce');
		$f->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$socials = $f->addContainer('socials');
		foreach($this->repSpeaker->getSocialTypes() as $social){
			$attr = array(
				'class' => $social->class
			);
			if($this->speaker){
				$value = $this->repSpeaker->getSocialOfSpeaker($this->speaker->id, $social->id);
				$socials->addText($social->id, Html::el('i', $attr))->setDefaultValue($value)->addCondition(Form::FILLED)->addRule(Form::PATTERN, 'Odkaz '.$social->type.' nemá správný URL tvar.', 'http(s)?://.*');
			}else{
				$socials->addText($social->id, Html::el('i', $attr))->addCondition(Form::FILLED)->addRule(Form::PATTERN, 'Odkaz '.$social->type.' nemá správný URL tvar.', 'http(s)?://.*');
			}
		}
		return $f;
	}

	public function createComponentFormSpeaker(){
		$f = $this->formSpeaker();
		if($this->speaker){
			$defaults = $this->speaker;
			$f->setDefaults($defaults);
			$f->addSubmit('save', 'Uložit');
		}else{
			$f->addSubmit('create', 'Vytvořit');
		}
		$f->onSuccess[] = $this->formSpeakerSubmitted;
		return $f;
	}

	public function formSpeakerSubmitted(Form $f){
		$values = $f->getValues(TRUE);

		if($values['image']->isOk()){
			$values['image'] = $this->uploadImage($values['image'], '/assets/images/speakers/', $values['firstname'].'_'.$values['lastname']);
		}else{
			unset($values['image']);
		}

		if($values['image_small']->isOk()){
			$values['image_small'] = $this->uploadImage($values['image_small'], '/assets/images/speakers/', $values['firstname'].'_'.$values['lastname'].'_small');
		}else{
			unset($values['image_small']);
		}

		if($this->speaker){
			$result = $this->repSpeaker->update($this->speaker->id, $values);
			$param = $this->speaker->id;
		}else{
			$values['nice_url'] = $this->sanitizeName(trim($values['firstname']).'-'.trim($values['lastname']));
			if(!$this->repSpeaker->getSpeakerByNiceUrl($values['nice_url'])){
				$result = $this->repSpeaker->create($values);
				$param = $result->id;
			}else{
				$result = FALSE;
				$param = NULL;
			}
		}
		if($result){
			$this->flashMessage(BaseAdminPresenter::$MESSAGE_SUCCESS, 'success');
		}else{
			$this->flashMessage(BaseAdminPresenter::$MESSAGE_UNSUCCESS, 'unsuccess');
		}
		$this->redirect('this', $param);
	}

}
