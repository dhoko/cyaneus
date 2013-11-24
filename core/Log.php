<?php
class Log {


    public static function trace($message) {
        return self::write($message);
    }

    public static function error($message) {
        return self::write($message,'error');
    }

    public static function server($message, $type='error') {
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
    private static function write($msg, $level = 'trace', $type="") {

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

        $log = '['.CDate::datetime().']['.$level.']['.$class.'::'.$function.'] :'.$msg."\n";

        $name = 'log';
        if($type === "server") {
            $name = 'log_server';
        }
        file_put_contents(Cyaneus::config('path')->logs.$name.'.txt',$log,FILE_APPEND);
    }

}