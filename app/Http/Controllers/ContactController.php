<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Lists the existing contacts.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->cannot('index', new Contact())) {
            return $this->respondUnauthorized();
        }

        return $this->respondResourcesFound(new Contact(), $request);
    }

    /**
     * Creates a new contact.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Find specific contacts by id.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
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