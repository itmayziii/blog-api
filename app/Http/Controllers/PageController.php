<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Page;
use App\Repositories\CacheRepository;
use App\Repositories\PageRepository;
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
}