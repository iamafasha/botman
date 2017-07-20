<?php

namespace BotMan\BotMan\Exceptions;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\ExceptionHandlerInterface;
use Illuminate\Support\Collection;

class ExceptionHandler implements ExceptionHandlerInterface
{
    protected $exceptions = [];

    public function __construct()
    {
        $this->exceptions = Collection::make();
    }

    /**
     * Handle an exception.
     *
     * @param  \Throwable $e
     * @param BotMan $bot
     * @return mixed
     * @throws \Throwable
     */
    public function handleException($e, BotMan $bot)
    {
        $exceptions = $this->exceptions->where('exception', class_basename($e));

        $exceptions->each(function($handler) use ($e, $bot) {
            call_user_func_array($handler['closure'], [$e, $bot]);
        });

        // No matching custom handler, throw the exception.
        if ($exceptions->isEmpty()) {
            throw $e;
        }
    }

    /**
     * Register a new exception type.
     *
     * @param string $exception
     * @param callable $closure
     * @return mixed
     */
    public function register(string $exception, callable $closure)
    {
        $this->exceptions->push([
            'exception' => $exception,
            'closure' => $closure
        ]);
    }
}