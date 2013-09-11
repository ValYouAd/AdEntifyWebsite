<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 17/07/2013
 * Time: 12:41
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Util;

class CommonTools
{
    /**
     * Convertit un nombre entre différentes bases.
     *
     * @param   string      $number     Le nombre à convertir
     * @param   int         $frombase   La base du nombre
     * @param   int         $tobase     La base dans laquelle on doit le convertir
     * @param   string      $map        Eventuellement, l'alphabet à utiliser
     * @return  string|false            Le nombre converti ou FALSE en cas d'erreur
     * @author  Geoffray Warnants
     */
    public static function base_to($number, $frombase, $tobase, $map=false)
    {
        if ($frombase<2 || ($tobase==0 && ($tobase=strlen($map))<2) || $tobase<2) {
            return false;
        }
        if (!$map) {
            $map = implode('',array_merge(range(0,9),range('a','z'),range('A','Z')));
        }
        // conversion en base 10 si nécessaire
        if ($frombase != 10) {
            $number = ($frombase <= 16) ? strtolower($number) : (string)$number;
            $map_base = substr($map,0,$frombase);
            $decimal = 0;
            for ($i=0, $n=strlen($number); $i<$n; $i++) {
                $decimal += strpos($map_base,$number[$i]) * pow($frombase,($n-$i-1));
            }
        } else {
            $decimal = $number;
        }
        // conversion en $tobase si nécessaire
        if ($tobase != 10) {
            $map_base = substr($map,0,$tobase);
            $tobase = strlen($map_base);
            $result = '';
            while ($decimal >= $tobase) {
                $result = $map_base[$decimal%$tobase].$result;
                $decimal /= $tobase;
            }
            return $map_base[$decimal].$result;
        }
        return $decimal;
    }

    /**
     * @return string
     *
     * Generate a random password of 12 caracters
     */
    public static function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789@!#&-_()?!";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 15; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}