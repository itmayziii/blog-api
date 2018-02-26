<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @var ValidationFactory
     */
    private $validationFactory;

    public function __construct(ValidationFactory $validationFactory)
    {
        $this->validationFactory = $validationFactory;
    }

    /**
     * Resolve the validator from the service container with specific rules applied.
     *
     * @param Request $request
     * @param array $rules
     *
     * @return Validator
     */
    protected function initializeValidation(Request $request, $rules)
    {
        $validator = $this->validationFactory->make($request->all(), $rules);

        return $validator;
    }
}
