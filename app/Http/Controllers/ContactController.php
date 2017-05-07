<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use itmayziii\Laravel\JsonApi;

class ContactController extends Controller
{
    /**
     * @var JsonApi
     */
    private $jsonApi;

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    /**
     * Lists the existing contacts.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('index', new Contact())) {
            return $this->jsonApi->respondUnauthorized();
        }

        return $this->jsonApi->respondResourcesFound(new Contact(), $request);
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
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        $contact = new Contact();
        $contact->first_name = $request->input('first-name');
        $contact->last_name = $request->input('last-name');
        $contact->email = $request->input('email');
        $contact->comments = $request->input('comments');

        $contact->save();

        return $this->jsonApi->respondResourceCreated($contact);
    }

    /**
     * Find specific contacts by id.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (Gate::denies('show', new Contact())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $contact = Contact::find($id);

        if ($contact) {
            return $this->jsonApi->respondResourceFound($contact);
        } else {
            return $this->jsonApi->respondResourceNotFound();
        }
    }
}