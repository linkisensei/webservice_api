<?php namespace webservice_api\handlers;

use \Throwable;
use \League\Route\Http\Exception\HttpExceptionInterface;

class exception_handler {

    public static function register(): void {
        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(Throwable $th) : void {
        $info = [
            'status' => 500,
            'message' => $th->getMessage(),
        ];

        if(self::is_debug_enabled()){
            $info['file'] = $th->getFile();
            $info['line'] = $th->getLine();
            $info['trace'] = $th->getTrace();
        }
    
        if(is_a($th, HttpExceptionInterface::class, true)){
            $info['status'] = $th->getStatusCode();
    
            foreach ($th->getHeaders() as $key => $value) {
                header("$key: $value");
            }
        }

        http_response_code($info['status']);
        echo json_encode($info);
        exit();
    }

    protected static function is_debug_enabled() : bool {
        global $CFG;
        return (bool) $CFG->debugdeveloper;
    }

}
