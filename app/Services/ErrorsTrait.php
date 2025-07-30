<?php

namespace App\Services;

trait ErrorsTrait
{
    /**
     * @var It contains the errors under process
     */
    protected $errors = [];

    /**
     * @var Error Logger instance
     */
    protected $errorLogger = null;

    /**
     * Get the first error message (if it exists)
     */
    public function getMessage(): string
    {
        $messages = $this->getErrorMessages();

        if (empty($messages[0])) {
            return '';
        }

        return $messages[0];
    }

    /**
     * Get the first error message (if it exists)
     */
    public function getCode(): string
    {
        $codes = $this->getErrorCodes();

        if (empty($codes[0])) {
            return '';
        }

        return $codes[0];
    }

    /**
     * Add error to errors with code and message
     *
     * @param array $error Error array with code and message keys
     */
    public function addError($message, $data = [])
    {
        $error = [
            'message' => $message,
            'data' => $data
        ];

        $this->errors[] = $error;
    }

    /**
     * Add batched errors
     *
     * @param array $errors Errors arrray
     */
    public function addErrors(array $errors)
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    /**
     * Get the first error of errors array
     */
    public function getError(): array
    {
        return $this->hasError() ? $this->errors[0] : [];
    }

    /**
     * Get error item list
     *
     * @param string $format
     * @return array|string
     */
    public function getErrors($format = 'array')
    {
        switch ($format) {
            case 'array' :
                $errors = $this->errors;
                break;
            case 'json' :
                $errors = json_encode($this->errors);
                break;
            default:
                $errors = null;
        }

        return $errors;
    }

    /**
     * Get only error messages
     */
    public function getErrorMessages(): array
    {
        $errorMessages = [];

        foreach ($this->errors as $error) {
            $errorMessages[] = $error['message'];
        }

        return $errorMessages;
    }

    /**
     * Get only error codes
     */
    public function getErrorCodes(): array
    {
        $errorCodes = [];

        foreach ($this->errors as $error) {
            $errorCodes[] = $error['code'];
        }

        return $errorCodes;
    }

    /**
     * Check the process has error
     */
    public function hasError(): bool
    {
        return (bool)count($this->errors);
    }

    /**
     * Flush the collected errors
     */
    public function flushErrors()
    {
        $this->errors = [];
    }
}