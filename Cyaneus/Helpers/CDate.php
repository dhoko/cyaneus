<?php
namespace Cyaneus\Helpers;
use Cyaneus\Cyaneus;
use \DateTime;
use \DateTimeZone;

class CDate
{
    /**
     * Format a date to Cyaneus default format
     * You can edit this format in your config file
     * @param  String $date
     * @return String
     */
    public static function formated($date)
    {
        $datetime = new DateTime($date, new DateTimeZone(Cyaneus::app()->timezone));
        return $datetime->format(Cyaneus::app()->date_format);
    }

    /**
     * Format a date to a RSS format
     * @param  String $date
     * @return String
     */
    public static function rss($date)
    {
        $datetime = new DateTime($date, new DateTimeZone(Cyaneus::app()->timezone));
        return $datetime->format('D, j M Y H:i:s \G\M\T');
    }

    /**
     * Format a date to ATOM format
     * @param  String $date
     * @return String
     */
    public static function atom($date)
    {
        $datetime = new DateTime($date, new DateTimeZone(Cyaneus::app()->timezone));
        return $datetime->format(DateTime::ATOM);
    }

    /**
     * Get a timestamp from a date
     * @param  String $date
     * @return String
     */
    public static function timestamp($date)
    {
        $datetime = new DateTime($date, new DateTimeZone(Cyaneus::app()->timezone));
        return $datetime->format('U');
    }

    /**
     * Return a datetime from the current date
     * @return String
     */
    public static function datetime()
    {
        return (new DateTime("now",new DateTimeZone(Cyaneus::app()->timezone)))->format('Y-m-d H:i:s');
    }

    /**
     * Return a datetime from the current date
     * @return String
     */
    public static function format($date, $format = 'c')
    {
        return (new DateTime($date,new DateTimeZone(Cyaneus::app()->timezone)))->format($format);
    }
}
