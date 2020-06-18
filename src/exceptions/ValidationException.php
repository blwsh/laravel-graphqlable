<?php

namespace UniBen\LaravelGraphQLable\Exceptions;

use Exception;
use GraphQL\Error\ClientAware;

/**
 * Class ValidationException
 *
 * @package UniBen\LaravelGraphQLable\Exceptions
 */
class ValidationException extends \Illuminate\Validation\ValidationException implements ClientAware {
    protected $extensions;

    public function __construct($validator, $response = null, $errorBag = 'default')
    {
        parent::__construct($validator, $response, $errorBag);
        $this->extensions['errors'] = $this->errors();
        $this->message = json_encode($this->errors());
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

        /**
     * @return bool
     */
    public function isClientSafe()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return 'route-validation';
    }
}
