<?php
namespace SapiStudio\Http;

use SapiStudio\Http\Browser\StreamClient;
use Exception;

class Html
{
    protected $htmlContent;
    protected $domCrawler;
    protected static $urlLinkAttributes     = ['href','title','alt'];
    protected static $urlImageAttributes    = ['src','title','alt'];
    
    /** Html::loadHtml()*/
    public static function loadHtml($html){
        return new static($html);
    }
    
    /** Html::loadHtml()*/
    public static function loadFromUrl($url){
        if(!self::isValidURL($url))
            throw new \Exception('Invalid url format');
        return new static(StreamClient::getPageContent($url));
    }
    
    /** Html::__construct()*/
    public function __construct($html=''){
        $this->htmlContent  = $html;
        $this->domCrawler   = getDomCrawler($html);
    }
    
    /** Html::getHtmlContent()*/
    public function getHtmlContent(){
        return $this->htmlContent;
    }
    
    /** Html::getHtmlCrawler()*/
    public function getHtmlCrawler(){
        return $this->domCrawler;
    }
    
    /** Html::testUris()*/
    public static function testUris($content = null){
        if(!$content)
            return false;
        if(is_array($content))
            return StreamClient::make()->validateLinks($content);
        $crawler    = self::loadHtml($content);
        $images     = $crawler->getDomImages();
        $links      = $crawler->getAllLinks();
        $toCheck    = array_merge($images,$links);
        return StreamClient::make()->validateLinks(array_merge(array_column($toCheck, 'href'),array_column($toCheck, 'src')));
        return array_merge_recursive(StreamClient::make()->validateLinks(array_merge(array_column($toCheck, 'href'),array_column($toCheck, 'src'))),$toCheck);
    }
    
    /** Html::getDomImages()*/
    public function getDomImages($imagesAttributes = []){
        $attributes = array_merge(self::$urlImageAttributes,$imagesAttributes);
        return self::attributesBeautifier($this->domCrawler->filterXpath('//img')->extract($attributes),$attributes);
    }
    
    /** Html::getDomUris() */
    public function getDomUris($urisAttributes = []){
        $attributes = array_merge(self::$urlLinkAttributes,$urisAttributes);
        return self::attributesBeautifier($this->domCrawler->filterXpath('//a')->extract($attributes),$attributes);
    }
    
    /** Html::getDomAreas()*/
    public function getDomAreas($areasAttributes = []){
        $attributes = array_merge(self::$urlLinkAttributes,$areasAttributes);
        return self::attributesBeautifier($this->domCrawler->filterXpath('//area')->extract($attributes),$attributes);
    }
    
    /** Html::getDomBody()*/
    public function getDomBody(){
        return $this->domCrawler->filterXpath('//body')->html();
    }
        
    /** Html::getAllLinks() */
    public function getAllLinks(){
        return array_merge($this->getDomUris(),$this->getDomAreas());
    }
    
    /** Html::attributesBeautifier()*/
    public static function attributesBeautifier($arrayData = [],$arrayAttributes = []){
        $beautified = [];
        array_walk($arrayData, function(&$val) use (&$beautified,$arrayAttributes){$beautified[md5($val[0])] = array_combine($arrayAttributes,$val);});
        return $beautified;
    }
    
    /** Html::isValidURL()*/
    public static function isValidURL($url = null){
        return preg_match('/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$url);
    }
    
    /** Html::urlIsImage() */
    public static function urlIsImage($contentType = null){
        switch ($contentType) {
            case 'image/png' :
            case 'image/jpg' :
            case 'image/jpeg':
            case 'image/gif' :
                $urlIsImage = true;
            break;
        default:
                $urlIsImage = false;
          break;
      }
      return $urlIsImage;
    }
}
