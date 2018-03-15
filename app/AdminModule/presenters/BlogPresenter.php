<?php

namespace AdminModule;

use Nette\Application\UI\Form;

class BlogPresenter extends BaseAdminPresenter{

    public $repBlog;
    public $images;

    public $article_id;
    public $article = NULL;
    public $articles;

    public function startup(){
        parent::startup();
        $this->repBlog = $this->context->blog;
    }

    public function actionImages(){
        $this->images = $this->repBlog->getImages();
    }

    public function renderImages(){
        $this->template->images = $this->images;
    }

    public function actionDefault($article_id = NULL){
        if($article_id !== NULL){
            $this->article_id = $article_id;
            $this->article = $this->repBlog->getArticle($this->article_id);
        }
        $this->articles = $this->repBlog->getArticles();
    }

    public function renderDefault($article_id = NULL){
        $this->template->article_id = $this->article_id;
        $this->template->articles = $this->articles;
        $this->template->article = $this->article;
    }

    public function createComponentFormImage(){
        $f = new Form();
        $f->addText("caption", "Název obrázku: ")->addRule(Form::FILLED, "Titulek obrázku musí být vyplněn.");
        $f->addUpload("source", "Obrázek: ")->addRule(Form::IMAGE, "Obrázek musí být JPG, PNG nebo GIF.");
        $f->addSubmit("upload", "Nahrát obrázek");
        $f->onSuccess[] = $this->formImageSubmitted;
        return $f;
    }

    public function formImageSubmitted(Form $f){
        $values = $f->getValues(TRUE);
        if($values['source']->isOk()){
            $values['source'] = $this->uploadImage($values['source'], '/assets/images/upload/', $values["caption"]);
            if($this->repBlog->createImage($values)){
                $this->flashMessage(BaseAdminPresenter::$MESSAGE_SUCCESS, "success");
            }else{
                $this->flashMessage(BaseAdminPresenter::$MESSAGE_UNSUCCESS, "unsuccess");
            }
        }else{
            $this->flashMessage(BaseAdminPresenter::$MESSAGE_UNSUCCESS, "unsuccess");
        }
        $this->redirect("this");
    }

    public function createComponentFormArticle(){
        $f = new Form();
        $types = array(
            "iCONfestival CZ" => "iCONfestival CZ",
            "iCONfestival EN" => "iCONfestival EN",
        );
        $f->addRadioList("type", "Typ článku: ", $types)->addRule(Form::FILLED, "Typ článku musí být zvolen.");
        $f->addText("title", "Název: ");
        $f->addText("link", "Odkaz v titulku (vč. http://): ");
        $f->addTextArea("content", "Obsah: ")->addRule(Form::FILLED, "Obsah článku musí být vyplněn.")->getControlPrototype()->class('tinymce');
        $f->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
        $f->addRadioList("has_image", "Má obrázek: ", array(1 => " ano", 0 => " ne"))->addCondition(Form::EQUAL, 1)->toggle("fimage");
        $images = $this->repBlog->getImages()->fetchPairs("id", "caption");
        $f->addSelect("image_id", "", $images)->setHtmlId("fimage");
        $f->addSubmit("upload", "Uložit");
        $f->onSuccess[] = $this->formArticleSubmitted;
        if($this->article){
            $f->setDefaults($this->article);
        }
        return $f;
    }

    public function formArticleSubmitted(Form $f){
        $values = $f->getValues(TRUE);
        if($this->article){
            $result = $this->repBlog->updateArticle($this->article->id, $values);
            $param = $this->article->id;
        }else{
            $values["nice_url"] = $this->sanitizeName($values["title"], TRUE);
            $result = $this->repBlog->createArticle($values);
            $param = $result->id;
        }
        if($result){
            $this->flashMessage(BaseAdminPresenter::$MESSAGE_SUCCESS, "success");
            $this->redirect("this", $param);
        }else{
            $this->flashMessage(BaseAdminPresenter::$MESSAGE_UNSUCCESS, "unsuccess");
            $this->redirect("this");
        }
    }

}
