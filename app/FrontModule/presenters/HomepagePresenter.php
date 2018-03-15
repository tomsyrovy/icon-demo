<?php

namespace FrontModule;
use Nette\Application\UI\Form;
use Nette\Latte\Engine;
use Nette\Mail\Message;
use Nette\Templating\FileTemplate;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BaseFrontPresenter{

	private $repTalk;
	protected $talkTypes;

	private $repSpeaker;

	protected $speakers;
	protected $speaker;
	protected $nice_url;
	protected $socials;

    protected $talks;

    protected $headliners = array();
    protected $blocks;

    private $repBlog;
    protected $blog_articles;

    private $repOwnProgram;

	public function startup(){
		parent::startup();
        $this->repBlog = $this->context->blog;
		$this->repTalk = $this->context->talk;
		$this->repSpeaker = $this->context->speaker;
        $this->repOwnProgram = $this->context->ownProgram;
	}

	public function actionPredprogram(){
		$this->talkTypes = $this->repTalk->getTalkTypes(TRUE, 1);
        foreach($this->talkTypes as $t){
            $this->headliners[$t->id] = $this->repTalk->getHeadlinersOfType($t->id);
        }
        $this->blocks = $this->repTalk->getTalkTypesOnlyBlocks();
	}

	public function renderPredprogram(){
		$this->template->talkTypes = $this->talkTypes;
        $this->template->headliners = $this->headliners;
        $this->template->blocks = $this->blocks;
        $this->template->iCONtypeSoldOut = BaseFrontPresenter::$iCONtypeSoldOut;
    }

    public function actionVideo(){
        $this->redirectUrl('https://vimeo.com/ondemand/icon2015');
    }
    public function renderVideo(){}

	public function actionRecnici($recnik = NULL){
		$this->speakers = $this->repSpeaker->getSpeakersInList();
		if($recnik == NULL){
			$this->speaker = $this->repSpeaker->getSpeakerFirst();
			$this->nice_url = $this->speaker->nice_url;
		}else{
			$this->speaker = $this->repSpeaker->getSpeakerByNiceUrl($recnik);
			$this->nice_url = $recnik;
			if(!$this->speaker){
				$this->speaker = $this->repSpeaker->getSpeakerFirst();
				$this->nice_url = $this->speaker->nice_url;
			}
		}
		$this->socials = $this->repSpeaker->getSocialsOfSpeaker($this->speaker->id);
        $this->talks = $this->repTalk->getTalksOfSpeaker($this->speaker->id, BaseFrontPresenter::$iCONtypeShow);
	}

	public function renderRecnici($recnik = NULL){
		$this->template->speakers = $this->speakers;
		$this->template->speaker = $this->speaker;
		$this->template->nice_url = $this->nice_url;
		$this->template->socials = $this->socials;
        $this->template->talks = $this->talks;
	}

    public $countSpeakers = array();
    public $speakers2;
    public function actionProgram(){
        $this->blocks = $this->repTalk->getTalkTypes(TRUE, 1, BaseFrontPresenter::$iCONtypeShow);
        $this->talks = array();
        $this->speakers = array();
        foreach($this->blocks as $block){
            $this->talks[$block->id] = $this->repTalk->getTalksOfTypeTalk($block->id);
            foreach($this->talks[$block->id] as $talk){
                $this->speakers[$talk->id] = $this->repTalk->getSpeakersOfTalk($talk->id);
                $this->speakers2[$talk->id] = $this->repTalk->getSpeakersOfTalk($talk->id);
                $this->countSpeakers[$talk->id] = $this->repTalk->getCountSpeakersOfTalk($talk->id);
            }
        }
    }

    public function renderProgram(){
        $this->template->blocks = $this->blocks;
        $this->template->talks = $this->talks;
        $this->template->speakers = $this->speakers;
        $this->template->speakers2 = $this->speakers2;
        $this->template->countSpeakers = $this->countSpeakers;
        $this->template->iCONtypeShowTime = BaseFrontPresenter::$iCONtypeShowTime;
        $this->template->iCONtypeSoldOut = BaseFrontPresenter::$iCONtypeSoldOut;
    }

	public function actionDefault(){
        $this->talkTypes = array();
		foreach($this->repTalk->getTalkTypes(TRUE) as $t){
			$this->talkTypes[$t->id] = $t->description;
		}
	}

	public function renderDefault(){
		$this->template->talkTypes = $this->talkTypes;
	}

    public function actionFestival(){
        $this->blog_articles = $this->repBlog->getArticlesOfType(1, "iCONfestival CZ");
    }

    public function renderFestival(){
        $this->template->articles = $this->blog_articles;
    }

    public function actionEn(){
        $this->blog_articles = $this->repBlog->getArticlesOfType(1, "iCONfestival EN");
    }

    public function renderEn(){
        $this->template->articles = $this->blog_articles;
    }

//    public function actionIconAtrakce(){
//        $this->blog_articles = $this->repBlog->getArticles(1);
//    }
//
//    public function renderIconAtrakce(){
//        $this->template->articles = $this->blog_articles;
//    }

	public function actionHodnotit($prednaska, $hodnoceni){
	    $this->repTalk->rankTalk($prednaska, $hodnoceni);
	}

	public function renderHodnotit($prednaska, $hodnoceni){

	}

    public function actionVlastniProgram(){
        $this->talkTypes = $this->repTalk->getTalkTypes(TRUE, 1, BaseFrontPresenter::$iCONtypeShow);
        $this->talks = array();
        $this->speakers = array();
        foreach($this->talkTypes as $talk_type){
            $this->talks[$talk_type->id] = $this->repTalk->getTalksOfTypeTalk($talk_type->id);
            foreach($this->talks[$talk_type->id] as $talk){
                $this->speakers[$talk->id] = $this->repTalk->getSpeakersOfTalk($talk->id);
            }
        }
    }

    public function renderVlastniProgram(){
        $this->template->talk_types = $this->talkTypes;
        $this->template->talks = $this->talks;
        $this->template->speakers = $this->speakers;
        $this->template->iCONtypeShowTime = BaseFrontPresenter::$iCONtypeShowTime;
    }

    public function createComponentFormVlastniProgram(){
        $f = new Form();
        foreach($this->talkTypes as $talk_type){
            $talk_type_container = $f->addContainer($talk_type->id);
            foreach($this->talks[$talk_type->id] as $talk){
                $talk_type_container->addCheckbox($talk->id, $talk->title);
            }
        }
        $f->addText("email", NULL)->setType("email")->setAttribute("onfocus", "if (this.value == this.defaultValue) this.value = ''")->setAttribute("onblur", "if (this.value == '') this.value = this.defaultValue")->setDefaultValue("Zadejte Váš e-mail")->addRule(Form::EMAIL, "E-mail nemá správný formát.");
        $f->addSubmit("submit", "Odeslat program na e-mail")->setAttribute("class", "btn_black");
        $f->onSuccess[] = $this->formVlastniProgramSubmitted;
        return $f;
    }

    public function formVlastniProgramSubmitted(Form $f){
        $values = $f->getValues(TRUE);
        if($own_program = $this->repOwnProgram->saveOwnProgram($values)){
            $this->sendOwnProgram($own_program);
            $this->flashMessage("Vlastní program byl odeslán na e-mail.", "success");
        }else{
            $this->flashMessage("Program nebyl odeslán.", "unsuccess");
        }
        $this->redirect("this");
    }

    private function sendOwnProgram($own_program){

        $template = $this->createTemplate();
        $template->setFile(__DIR__."/../templates/Homepage/EmailOwnProgram.latte");
        $template->registerFilter(new Engine());
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');

        $talks = $this->repOwnProgram->getOwnProgram($own_program->id);

        $speakers = array();
        foreach($talks as $talk){
            $speakers[$talk->talk_id] = $this->repTalk->getSpeakersOfTalk($talk->talk_id);
        }

        $template->talks = $talks;
        $template->speakers = $speakers;
        $template->iCONtypeShowTime = BaseFrontPresenter::$iCONtypeShowTime;

        $message = new Message();
        $message->setFrom("program@iconprague.com")->addTo($own_program->email)->setSubject("iCON Prague 2014 - Vlastní program")->setHtmlBody($template)->send();

    }

    public function actionPravidlaSouteze(){

    }

    public function renderPravidlaSouteze(){

    }

    public function actionStats($type = "ranking"){

    }

    public function renderStats($type = "ranking"){

        $this->template->stats = $this->repOwnProgram->getStats($type);

    }

}
