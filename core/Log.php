<?php
/**
 * Log them all
 * Log file is store in :
 *     - Cyaneus::config('path')->logs.$name.'.txt'
 *
 * Default path is data/logs.txt
 *
 * It can generate files in logs_server.php if you want
 */
class Log
{
    /**
     * Write a trace
     * @param  String $message Your message to log.
     * @return Boolean
     */
    public static function trace($message)
    {
        return self::write($message);
    }

    /**
     * Write an error
     * @param  String $message Your message to log.
     * @return Boolean
     */
    public static function error($message)
    {
        return self::write($message,'error');
    }

    /**
     * Write a trace or an error for a server request
     * Store in logs_server.txt
     * @param  String $message Your message to log.
     * @return Boolean
     */
    public static function server($message, $type='error')
    {
        return self::write($message, $type,'server');
    }

    /**
     * Log fonction it builds 3 files :
     *  - log.txt
     *  - log_error.txt
     *  - log_server.txt
     * Files are in USERDATA -> data/
     * @param  String $msg  Message to log
     * @param  string $type Type of message
     */
    private static function write($msg, $level = 'trace', $type="")
    {
        // Fetch Stack Trace
        $stack = debug_backtrace();

        // Check if options are defined.
        if(!empty($options)) {
            // Define class
            if(!empty($options['class'])) {
                $class = $options['class'];
            }
            // Define function
            if(!empty($options['function'])) {
                $function = $options['function'];
            }

        }

        // Check if Stack Trace is defined.
        if(isset($stack[2])) {

            // Define class
            if(isset($stack[2]['class']) && empty($class)) {
                $class = $stack[2]['class'];
            }

            // Define function
            if(isset($stack[2]['function']) && empty($function)) {
                $function = $stack[2]['function'];
            }

        }

        // Check if we should define a default value for class
        if(empty($class)) {
            $class = 'Application';
        }

        // Check if we should define a default value for function
        if(empty($function)) {
            $function = 'unknown';
        }

        $log = '['.CDate::datetime().']['.$level.']['.$class.'::'.$function.']: '.$msg."\n";
        $name = ($type === "server") ? 'log_server' : 'log';

        return file_put_contents(Cyaneus::config('path')->logs.$name.'.txt',$log,FILE_APPEND);
    }

}
