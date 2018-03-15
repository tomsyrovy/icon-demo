<?php

namespace Repository;

use Nette;

/**
 * Provádí operace nad databázovou tabulkou.
 */
class Blog extends Repository{

    public function createImage($data){
        return $this->getTable("image")->insert($data);
    }

    public function getImages(){
        return $this->getTable("image")->order("id ASC");
    }

    public function createArticle($data){
        return $this->getTable("blog")->insert($data);
    }

    public function updateArticle($article_id, $data){
        return $this->getTable("blog")->get($article_id)->update($data);
    }

    public function getArticle($article_id){
        return $this->getTable("blog")->get($article_id);
    }

    public function getArticles($active = array(0, 1)){
        if($active == 1){
            $statement = "
                SELECT b.*, i.*
                FROM blog b
                LEFT JOIN image i ON b.image_id = i.id
                WHERE b.active = 1
                ORDER BY sort ASC
            ";
            return $this->connection->query($statement);
        }else{
            return $this->getTable("blog")->where("active", $active)->order("sort ASC");
        }
    }

}