<?php

namespace App\Exceptions;

// use Exception;
Use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use DB;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if($this->shouldReport($exception))
        {
            DB::table('error_logs')->insert([
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'created_at' => now(),
                'updated_at' => now(), 
            ]);
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
	    /*if ($exception->getMessage() ) {
		    // return response()->json(['user_not_found'], 404);
		    return response()->view('503', [], 503);
	    }*/
        return parent::render($request, $exception);
    }
}
