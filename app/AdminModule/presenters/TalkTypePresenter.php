<?php

namespace AdminModule;

use Nette\Application\UI\Form;
use Nette\Utils\Html;

class TalkTypePresenter extends TalkPresenter{

    public function actionDefault($talk_id = NULL){
        $this->defaultDataOfColumn = "description";
    }

}
