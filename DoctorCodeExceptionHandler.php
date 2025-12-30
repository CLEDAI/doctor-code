<?php

namespace DoctorCode\SDK\Exceptions;

use DoctorCode\SDK\DoctorCodeClient;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Throwable;

class DoctorCodeExceptionHandler implements ExceptionHandlerContract
{
    protected ExceptionHandlerContract $baseHandler;
    protected DoctorCodeClient $doctorCode;

    public function __construct(ExceptionHandlerContract $baseHandler, DoctorCodeClient $doctorCode)
    {
        $this->baseHandler = $baseHandler;
        $this->doctorCode = $doctorCode;
    }

    /**
     * Report or log an exception.
     */
    public function report(Throwable $e): void
    {
        // Report to base handler first
        $this->baseHandler->report($e);

        // Only report to DoctorCode if enabled and not in local environment
        if ($this->shouldReportToDoctorCode($e)) {
            $this->doctorCode->reportError($e, [
                'url' => request()->fullUrl() ?? null,
                'method' => request()->method() ?? null,
                'user_id' => auth()->id() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'ip' => request()->ip() ?? null,
            ]);
        }
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        return $this->baseHandler->render($request, $e);
    }

    /**
     * Render an exception to the console.
     */
    public function renderForConsole($output, Throwable $e): void
    {
        $this->baseHandler->renderForConsole($output, $e);
    }

    /**
     * Determine if the exception handler should return JSON.
     */
    public function shouldRenderJsonWhen(callable $callback): void
    {
        if (method_exists($this->baseHandler, 'shouldRenderJsonWhen')) {
            $this->baseHandler->shouldRenderJsonWhen($callback);
        }
    }

    /**
     * Determine if the exception should be reported to DoctorCode
     */
    protected function shouldReportToDoctorCode(Throwable $e): bool
    {
        // Don't report in local environment by default
        if (config('doctorcode.report_local', false) === false && app()->environment('local')) {
            return false;
        }

        // Don't report certain exception types
        $dontReport = config('doctorcode.dont_report', [
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Validation\ValidationException::class,
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        ]);

        foreach ($dontReport as $type) {
            if ($e instanceof $type) {
                return false;
            }
        }

        return true;
    }
}
