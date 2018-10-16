<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Models\Contact;
use App\Mail\Contact as ContactMail;
use App\Repositories\ContactRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ContactResource implements ResourceInterface
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(ContactRepository $contactRepository, Mailer $mailer)
    {
        $this->contactRepository = $contactRepository;
        $this->mailer = $mailer;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): string
    {
        return Contact::class;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedResourceActions(): array
    {
        return ['index', 'show', 'store'];
    }

    /**
     * @inheritdoc
     */
    public function findResourceObject($urlSegments, $queryParams)
    {
        if (count($urlSegments) !== 1) {
            return null;
        }

        [$id] = $urlSegments;
        return $this->contactRepository->findById($id);
    }

    /**
     * @inheritdoc
     */
    public function findResourceObjects($queryParams): LengthAwarePaginator
    {
        $page = Arr::get($queryParams, 'page', 1);
        $size = Arr::get($queryParams, 'size', 15);

        return $this->contactRepository->paginate($page, $size);
    }

    /**
     * @inheritdoc
     */
    public function storeResourceObject($attributes, Authenticatable $user = null)
    {
        $contact = $this->contactRepository->create($attributes);
        if (is_null($contact)) {
            return null;
        }

        $this->mailer->send(new ContactMail([
            'firstName' => $contact->getAttribute('first_name'),
            'lastName'  => $contact->getAttribute('last_name'),
            'email'     => $contact->getAttribute('email'),
            'comments'  => $contact->getAttribute('comments')
        ]));
        return $contact;
    }

    /**
     * @inheritdoc
     */
    public function updateResourceObject($resourceObject, $attributes, Authenticatable $user = null)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function deleteResourceObject($resourceObject): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getStoreValidationRules($attributes): array
    {
        return [
            'first_name' => 'required|max:255',
            'last_name'  => 'required|max:255',
            'email'      => 'required|max:255|email',
            'comments'   => 'required|max:1000',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function requireIndexAuthorization(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireShowAuthorization($resourceObject): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireStoreAuthorization(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function requireUpdateAuthorization($resourceObject): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireDeleteAuthorization($resourceObject): bool
    {
        return true;
    }
}
