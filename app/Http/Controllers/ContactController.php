<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $requestPage = $request->query('page');
        $requestSize = $request->query('size');

        $page = ($requestPage) ? $requestPage : 1;
        $size = ($requestSize) ? $requestSize : 20;

        $paginator = Contact::paginate($size, null, 'page', $page);
        return $this->respondResourcesFound($paginator);
    }

    public function store(Request $request)
    {
        $rules = [
            'first-name' => 'max:100',
            'last-name'  => 'max:100',
            'email'      => 'max:100',
            'comments'   => 'required|max:4000'
        ];
        $validation = $this->initializeValidation($request, $rules);

        if ($validation->fails()) {
            return $this->respondValidationFailed($validation->getMessageBag());
        }

        $contact = new Contact();
        $contact->first_name = $request->input('first-name');
        $contact->last_name = $request->input('last-name');
        $contact->email = $request->input('email');
        $contact->comments = $request->input('comments');

        $contact->save();

        return $this->respondResourceCreated($contact);
    }

    public function show($id)
    {
        $contact = Contact::find($id);

        if ($contact) {
            return $this->respondResourceFound($contact);
        } else {
            return $this->respondResourceNotFound();
        }
    }
}