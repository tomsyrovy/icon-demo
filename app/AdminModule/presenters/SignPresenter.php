<?php

namespace AdminModule;

use Nette\Application\UI;
use Nette\Security\AuthenticationException;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends \BasePresenter
{


	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->addText('username', 'Uživatelské jméno: ')
			->setRequired('Zadejte uživatelské jméno.');

		$form->addPassword('password', 'Heslo: ')
			->setRequired('Zadejte heslo.');

		$form->addSubmit('send', 'Přihlásit');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}


	public function signInFormSucceeded($form)
	{
		$values = $form->getValues();

		$this->getUser()->setExpiration('20 minutes', TRUE);

		try {
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');

		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}


	public function actionOut()
	{
		$this->signOut();
	}

	public function renderIn(){
		$pass = 'iC0N2ol4Q#eri';
		$this->template->username = 'admin';
		$this->template->pass = $pass;
		$this->template->hash = \Authenticator::calculateHash($pass);
	}
}
