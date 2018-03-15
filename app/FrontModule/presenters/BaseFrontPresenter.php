<?php

namespace FrontModule;

/**
 * Base presenter for all front application presenters.
 */
class BaseFrontPresenter extends \BasePresenter{

//    ICON 2015 už skončil
//    private $ticketsUrl = "https://www.ticketon.cz/2708-icon-prague-2015/14297?p=3esc2gli&amp;utm_source=1216&amp;utm_medium=partner&amp;utm_campaign=14297";

    private $ticketsUrl = "http://www.iconprague.com";

    /**
     * ID bloků, které se mají zobrazit v detailním programu
     * @var array
     */
    public static $iCONtypeShow = array(2, 10, 11, 12);

    /**
     * ID bloků, jejichž přednášky mají zobrazovat čas
     * @var array
     */
    public static $iCONtypeShowTime = array(2, 10, 11, 12);

    public static $iCONtypeSoldOut = array();

    public function beforeRender(){
        parent::beforeRender();
        $this->template->ticketsUrl = $this->ticketsUrl;
//        ICON 2015 už skončil
//        $this->template->ticketsLink = '<a target="_blank" href="'.$this->ticketsUrl.'"><img src="https://klient.ticketon.cz/img/badges/button-dark-bg.png" alt="Online vstupenky" /></a>';
        $this->template->ticketsLink = '';
        $this->template->iCONtypeShow = self::$iCONtypeShow;
    }

}
