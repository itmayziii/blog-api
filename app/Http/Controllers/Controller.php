<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Resolve the validator from the service container with specific rules applied.
     *
     * @param Request $request
     * @param $rules
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function initializeValidation(Request $request, $rules)
    {
        $validator = $this->getValidationFactory();
        $validation = $validator->make($request->all(), $rules);

        return $validation;
    }
}
