<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Page;
use App\Repositories\CacheRepository;
use App\Repositories\PageRepository;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class PageController extends Controller
{
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Gate
     */
    private $gate;
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var CacheRepository
     */
    private $cacheRepository;
    /**
     * Validation Rules
     *
     * @var array
     */
    private $validationRules = [
        'title'   => 'required|max:200|unique:pages',
        'slug'    => 'required|max:255|unique:pages',
        'content' => 'max:100000',
        'is_live' => 'boolean'
    ];

    public function __construct(
        JsonApi $jsonApi,
        LoggerInterface $logger,
        Gate $gate,
        PageRepository $pageRepository,
        CacheRepository $cacheRepository,
        ValidationFactory $validationFactory
    ) {
        parent::__construct($validationFactory);
        $this->jsonApi = $jsonApi;
        $this->logger = $logger;
        $this->gate = $gate;
        $this->pageRepository = $pageRepository;
        $this->cacheRepository = $cacheRepository;
    }

    /**
     * List the pages.
     *
     * @param Request $request
     * @param Response $response
     * @param Page $page
     *
     * @return Response
     */
    public function index(Request $request, Response $response, Page $page)
    {
        $size = $request->query('size', 15);
        $pageQueryParam = $request->query('page', 1);

        $isAllowedToIndexAllPages = $this->gate->allows('indexAllPages', $page);
        $allowedStatus = $isAllowedToIndexAllPages ? 'all' : 'live';

        $paginator = $this->cacheRepository->remember("pages.$allowedStatus.page$pageQueryParam.size$size", 60,
            function () use ($size, $pageQueryParam, $isAllowedToIndexAllPages) {
                return ($isAllowedToIndexAllPages) ? $this->pageRepository->paginateAllPages($pageQueryParam, $size) : $this->pageRepository->paginateLivePages($pageQueryParam,
                    $size);
            });

        return $this->jsonApi->respondResourcesFound($response, $paginator);
    }

    /**
     * Find specific page by slug.
     *
     * @param Response $response
     * @param string $slug
     *
     * @return Response
     */
    public function show(Response $response, $slug)
    {
        $page = $this->cacheRepository->remember("page.$slug", 60, function () use ($slug) {
            return $this->pageRepository->findBySlug($slug);
        });

        if (is_null($page)) {
            $this->logger->debug(PageController::class . " unable to find page with slug: $slug");
            return $this->jsonApi->respondResourceNotFound($response);
        }

        if ($this->gate->denies('showPage', $page)) {
            $this->logger->debug(PageController::class . " unauthorized to show page with slug: $slug");
            return $this->jsonApi->respondForbidden($response);
        }

        return $this->jsonApi->respondResourceFound($response, $page);
    }

    /**
     * Create a new page.
     *
     * @param Request $request
     * @param Response $response
     * @param Page $page
     *
     * @return Response
     */
    public function store(Request $request, Response $response, Page $page)
    {
        if ($this->gate->denies('store', $page)) {
            $this->logger->debug(PageController::class . " unauthorized to create page");
            return $this->jsonApi->respondForbidden($response);
        }

        $validation = $this->initializeValidation($request, $this->validationRules);
        if ($validation->fails()) {
            $this->logger->debug(PageController::class . " validation failed, unable to create page");
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $page = $this->pageRepository->create($request->all());
        } catch (Exception $e) {
            $this->logger->error(PageController::class . " failed to create a page with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to create the page.");
        }

        $this->clearPagesCache();

        return $this->jsonApi->respondResourceCreated($response, $page);
    }

    /**
     * Update a page.
     *
     * @param Request $request
     * @param Response $response
     * @param string $slug
     *
     * @return Response
     */
    public function update(Request $request, Response $response, $slug)
    {
        $page = $this->pageRepository->findBySlug($slug);
        if (is_null($page)) {
            $this->logger->debug(PageController::class . " unable to find page to update with slug: $slug");
            return $this->jsonApi->respondResourceFound($response, $page);
        }

        if ($this->gate->denies('update', $page)) {
            $this->logger->debug(PageController::class . " unauthorized to update page with slug: $slug");
            return $this->jsonApi->respondForbidden($response);
        }

        // Removing the unique validation on some fields if they have not changed
        if ($page->getAttribute('slug') === $request->input('slug')) {
            $this->validationRules['slug'] = 'required|max:255';
        }
        if ($page->getAttribute('title') === $request->input('title')) {
            $this->validationRules['title'] = 'required|max:200';
        }

        $validation = $this->initializeValidation($request, $this->validationRules);
        if ($validation->fails()) {
            $this->logger->debug(PageController::class . " validation failed, unable to update page");
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $this->pageRepository->update($request->all());
        } catch (Exception $e) {
            $this->logger->error(PageController::class . " failed to update a page with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, 'Unable to update page');
        }

        $this->cacheRepository->forget("page.$slug");
        $this->clearPagesCache();

        return $this->jsonApi->respondResourceUpdated($response, $page);
    }

    /**
     * Delete a page.
     *
     * @param Response $response
     * @param string $slug
     *
     * @return Response
     */
    public function delete(Response $response, $slug)
    {
        $page = $this->pageRepository->findBySlug($slug);
        if (is_null($page)) {
            $this->logger->debug(PageController::class . " unable to find page to delete with slug: $slug");
            return $this->jsonApi->respondResourceFound($response, $page);
        }

        if ($this->gate->denies('delete', $page)) {
            $this->logger->debug(PageController::class . " unauthorized to update page with slug: $slug");
            return $this->jsonApi->respondForbidden($response);
        }

        try {
            $page->delete();
        } catch (Exception $e) {
            $this->logger->error(PageController::class . " failed to delete a page with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to delete page");
        }

        $this->cacheRepository->forget("page.$slug");
        $this->clearPagesCache();

        return $this->jsonApi->respondResourceDeleted($response);
    }

    private function clearPagesCache()
    {
        $pageKeys = $this->cacheRepository->keys('pages*');
        $this->cacheRepository->deleteMultiple($pageKeys);
    }
}