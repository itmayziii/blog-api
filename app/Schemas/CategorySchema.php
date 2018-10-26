<?php

namespace App\Schemas;

use Illuminate\Http\Request;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class CategorySchema extends BaseSchema
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(SchemaFactoryInterface $schemaFactory, Request $request)
    {
        parent::__construct($schemaFactory, $schemaFactory);
        $this->request = $request;
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
                self::SHOW_SELF    => true,
                self::SHOW_RELATED => true
            ]
        ];

        if ($category->relationLoaded('webpages')) {
            $relationships['webpages'] = array_merge($relationships['webpages'], [
                self::SHOW_DATA => true,
                self::DATA      => $category->getRelationValue('webpages')
            ]);
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
