<?php
/**
 * Created by PhpStorm.
 * User: yus
 * Date: 31/07/18
 * Time: 1:39
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use \DateTime;
use Symfony\Component\Intl\Intl;


class ThrowableExtension extends  AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('dateFormat', array($this, 'stringDate')),
            new TwigFilter('countryName', array($this, 'countryName')),
            new TwigFilter('excerpt',array($this,'excerpt')),
        );
    }

    public function stringDate()
    {
        $date =  new DateTime();
        $mont = array('enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre');
        $days = array('domingo','lunes','martes','miércoles','jueves','viernes','sábado');

        $date = $days[(int)$date->format('w')].', '.$date->format('j').' de '.$mont[(int)$date->format('m')-1].' de '.$date->format('Y');

        return $date;
    }

    public function countryName($countryCode){
        return Intl::getRegionBundle()->getCountryName($countryCode);
    }

    public function getName()
    {
        return 'country_extension';
    }

    public function excerpt($content, $cutOffLength=200) {

        $charAtPosition = "";
        $titleLength = strlen($content);

        do {
            $cutOffLength++;
            $charAtPosition = substr($content, $cutOffLength, 1);
        } while ($cutOffLength < $titleLength && $charAtPosition != " ");

        return strip_tags(substr($content, 0, $cutOffLength)) . '...';

    }


    function strip_tags_content($text, $tags = '', $invert = FALSE) {

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
        $tags = array_unique($tags[1]);

        if(is_array($tags) AND count($tags) > 0) {
            if($invert == FALSE) {
                return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
            }
            else {
                return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
            }
        }
        elseif($invert == FALSE) {
            return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
        }
        return $text;
    }

}