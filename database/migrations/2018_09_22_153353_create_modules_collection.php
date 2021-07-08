<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Database as MongoDB;

class CreateModulesCollection extends Migration
{
    /**
     * @var MongoDB
     */
    private $mongoDb;

    public function __construct()
    {
        $this->mongoDb = app(MongoDB::class);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->mongoDb->createCollection('webpage_modules', [
            'validator' => [
                'bsonType'   => 'object',
                'required'   => ['webpage_id', 'modules'],
                'properties' => [
                    'webpage_id' => [
                        'bsonType'    => 'int',
                        'description' => 'must be an integer and is required'
                    ],
                    'modules'    => [
                        'bsonType'    => 'array',
                        'description' => 'must be an array and is required'
                    ]
                ]
            ]
        ]);

        $this->mongoDb->selectCollection('webpage_modules')->createIndex(['webpage_id' => 1], [
            'unique' => true,
            'name'   => 'unique_id'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->mongoDb
            ->selectCollection('webpage_modules')
            ->dropIndex('unique_id');

        $this->mongoDb
            ->dropCollection('webpage_modules');
    }
}
