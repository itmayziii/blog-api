<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Schema\BaseSchema;

class CategorySchema extends BaseSchema
{
    public function __construct(SchemaFactoryInterface $schemaFactory)
    {
        parent::__construct($schemaFactory);
    }

    protected $resourceType = 'categories';

    /**
     * @inheritdoc
     */
    public function getId($category): ?string
    {
        return $category->getAttribute('id');
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubUrl($category = null): string
    {
        return $this->selfSubUrl . '/' . $category->getAttribute('slug');
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($category, array $fieldKeysFilter = null): ?array
    {
        $attributes = [
            'created_at'      => $category->getAttribute('created_at')->toIso8601String(),
            'updated_at'      => $category->getAttribute('updated_at')->toIso8601String(),
            'created_by'      => $category->getAttribute('created_by'),
            'last_updated_by' => $category->getAttribute('last_updated_by'),
            'name'            => $category->getAttribute('name'),
            'plural_name'     => $category->getAttribute('plural_name'),
            'slug'            => $category->getAttribute('slug')
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($category, bool $isPrimary, array $includeList): ?array
    {
        $relationships = [
            'webpages' => [
                self::SHOW_DATA    => false,
                self::SHOW_SELF    => false,
                self::SHOW_RELATED => true
            ]
        ];

        if ($category->relationLoaded('webpages')) {
            $baseUri = env('API_URI') . "/v1/categories/{$category->getAttribute('slug')}";
            $webPages = $category->getRelationValue('webpages')->setPath($baseUri)->appends('included', 'webpages');
            $relationships['webpages'] = array_merge($relationships['webpages'], [
                self::SHOW_DATA => true,
                self::DATA      => $webPages->items()
            ]);

            $firstUrl = $webPages->url(1);
            $lastUrl = $webPages->url($webPages->lastPage());
            $previousUrl = $webPages->previousPageUrl();
            $nextUrl = $webPages->nextPageUrl();

            if (!is_null($firstUrl)) {
                $relationships['webpages'][self::LINKS][LinkInterface::FIRST] = new Link($firstUrl, null, true);
            }

            if (!is_null($lastUrl)) {
                $relationships['webpages'][self::LINKS][LinkInterface::LAST] = new Link($lastUrl, null, true);
            }

            if (!is_null($previousUrl)) {
                $relationships['webpages'][self::LINKS][LinkInterface::PREV] = new Link($previousUrl, null, true);
            }

            if (!is_null($nextUrl)) {
                $relationships['webpages'][self::LINKS][LinkInterface::NEXT] = new Link($nextUrl, null, true);
            }
        }

        return $relationships;
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths(): array
    {
        return [
            'webpages'
        ];
    }
}
