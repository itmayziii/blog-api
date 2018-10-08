<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Http\JsonApi;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class ContactController extends Controller
{
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var Gate
     */
    private $gate;
    /**
     * @var LoggerInterface
     */
    private $logger;
    private $validationRules = [
        'first-name' => 'required|max:100',
        'last-name'  => 'required|max:100',
        'email'      => 'required|max:100',
        'comments'   => 'required|max:4000'
    ];

    public function __construct(JsonApi $jsonApi, Gate $gate, LoggerInterface $logger, ValidationFactory $validationFactory)
    {
        parent::__construct($validationFactory);
        $this->jsonApi = $jsonApi;
        $this->gate = $gate;
        $this->logger = $logger;
    }

    /**
     * Lists the existing contacts.
     *
     * @param Request $request
     * @param Response $response
     * @param Contact $contact
     *
     * @return Response
     */
    public function index(Request $request, Response $response, Contact $contact)
    {
        if ($this->gate->denies('index', $contact)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $size = $request->query('size', 15);
        $page = $request->query('page', 1);

        $paginator = $contact
            ->orderBy('created_at', 'desc')
            ->paginate($size, null, 'page', $page);

        return $this->jsonApi->respondResourcesFound($response, $paginator);
    }

    /**
     * Creates a new contact.
     *
     * @param Request $request
     * @param Response $response
     * @param Contact $contact
     *
     * @return Response
     */
    public function store(Request $request, Response $response, Contact $contact)
    {
        $validation = $this->initializeValidation($request, $this->validationRules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $contact = $contact->create([
                'first_name' => $request->input('first-name'),
                'last_name'  => $request->input('last-name'),
                'email'      => $request->input('email'),
                'comments'   => $request->input('comments')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create contact with exception: ' . $e->getMessage());
            return $this->jsonApi->respondServerError($response, 'Unable to create contact.');
        }

        return $this->jsonApi->respondResourceCreated($response, $contact);
    }

    /**
     * Find specific contacts by id.
     *
     * @param Response $response
     * @param Contact $contact
     * @param int $id
     *
     * @return Response
     */
    public function show(Response $response, Contact $contact, $id)
    {
        if ($this->gate->denies('show', $contact)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $contact = $contact->find($id);
        if (is_null($contact)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        return $this->jsonApi->respondResourceFound($response, $contact);
    }
}