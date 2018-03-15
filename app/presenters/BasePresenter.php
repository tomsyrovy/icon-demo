<?php

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	protected function signOut(){
		$this->getUser()->logout();
		$this->flashMessage('Odhlášení úspěšné.');
		$this->redirect('in');
	}
}
