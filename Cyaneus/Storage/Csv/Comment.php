<?php
namespace Cyaneus\Storage\Csv;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;
use Cyaneus\Helpers\Log;
use SplFileObject;

/**
 * Model for a comment
 * Attributes
 *     - name
 *     - content
 *     - mail
 * File storage : Cyaneus::data md5(url)
 */
class Comment
{
    private $file;
    private $datetime;
    private $created_at;
    private $attributes;
    private $previous;

    /**
     * Init the model for a record
     * Load the CSV
     * @param String $url
     */
    public function __construct($url)
    {
        $this->created_at = CDate::datetime();
        $this->previous   = self::find($url);
        $this->file       = new SplFileObject(Cyaneus::path()->data.md5($url).'.csv', 'w');
    }

    /**
     * Fill the attributes for a comment and validate them
     * @param  Array  $attributes
     * @return void
     */
    public function fill(Array $attributes)
    {
        $this->previous[] = [
            'name'       => htmlentities($attributes['name']),
            'mail'       => $attributes['mail'],
            'url'        => $attributes['url'],
            'content'    => htmlentities($attributes['content']),
            'created_at' => $this->created_at,
            ];
        $this->attributes = $this->previous;

    }

    /**
     * Validate the URL and the mail from a comment
     * @param  Array  $attributes
     * @throws UnexpectedValueException If the validation is not valid
     */
    public static function validate(Array $attributes) {

        try {
            if( !filter_var($attributes['mail'], FILTER_VALIDATE_EMAIL) ) {
                throw new \UnexpectedValueException('Invalid email format');
            }

            if( !empty($attributes['url']) && !filter_var($attributes['url'], FILTER_VALIDATE_URL) ) {
                throw new \UnexpectedValueException('Invalid url format');
            }
            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * Make a record
     * @return Boolean
     */
    public function write()
    {
        try {
            foreach ($this->attributes as $comment) {
                $this->file->fputcsv($comment);
            }
            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * List all the comments for an url
     * @param  String $url
     * @return Array
     */
    public static function find($url)
    {
        $_data = [];
        $file  = Cyaneus::path()->data.md5($url).'.csv';

        if( !file_exists($file) ) {
            return [];
        }

        $data = new SplFileObject($file, 'r');
        $data->setFlags(SplFileObject::READ_CSV);

        foreach ($data as $comment) {

            // We have an empty row
            if( empty($comment[0]) ) {
                continue;
            }

            list($name,$mail,$url,$content,$datetime) = $comment;
            if(!empty($name)) {
                $_data[] = [
                    'name'       => html_entity_decode($name),
                    'mail'       => $mail,
                    'url'        => $url,
                    'content'    => html_entity_decode($content),
                    'hash'       => md5($mail),
                    'created_at' => $datetime,
                ];
            }
        }
        return $_data;
    }
}
