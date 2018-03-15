<?php

namespace AdminModule;

use Nette\Environment;
use Nette\Templating\FileTemplate;
use Nette\Caching\Storages\PhpFileStorage;
use Nette\Latte\Engine;
use Nette\Templating\Helpers;

class FeedPresenter extends BaseAdminPresenter{

	protected $repTalk;
	protected $repSpeaker;

    public $rooms;
    public $speakers;
    public $talks;

    public $talk_has_speakers = array();

    protected $output = "";

	public function startup(){
		parent::startup();
		$this->repTalk = $this->context->talk;
		$this->repSpeaker = $this->context->speaker;
	}

	public function actionDefault(){
        $this->speakers = $this->repSpeaker->getSpeakers(1);
        $this->rooms = $this->repTalk->getRooms(FALSE);
        $this->talks = $this->repTalk->getTalks(1);

        foreach($this->repTalk->getTalks(1) as $talk){
            $this->talk_has_speakers[$talk->id] = array();
            foreach($this->repTalk->getSpeakersOfTalk($talk->id) as $speaker){
                $this->talk_has_speakers[$talk->id][] = $speaker->speaker_id;
            }
        }
        $this->createXMLFeed();
        $this->flashMessage("XML feed byl vytvořen.", "success");
	}

	public function renderDefault(){

	}

    private function createXMLFeed(){
        $template = $this->createTemplate();
        $template->setFile(__DIR__."/../templates/Feed/xml.latte");
        $template->registerFilter(new \Nette\Latte\Engine);
        $template->registerHelperLoader("Nette\Templating\Helpers::loader");
        $template->speakers = $this->speakers;
        $template->rooms = $this->rooms;
        $template->talks = $this->talks;
        $template->talk_has_speakers = $this->talk_has_speakers;
        $template->bp = Environment::getHttpRequest()->getUrl()->getHostUrl();
        $template->save("feed/icon.xml");
        $template->render();
        $this->terminate();
    }


}
