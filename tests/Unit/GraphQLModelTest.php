<?php

namespace UniBen\LaravelGraphQLable\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use UniBen\LaravelGraphQLable\Tests\Stubs\GraphQLModelStub;

class GraphQLModelTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /**
     * @var GraphQLModelStub
     */
    protected $model;

    protected function setUp()
    {
        parent::setUp();

        $this->model = factory(GraphQLModelStub::class)->create();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests the get queryable object is setting the correct values. The fillable
     * array for the stub class contains the values text, time, created_at and
     * updated_at.
     */
    public function testGetQueryableFields()
    {
        // Because the queryable array for the stub is empty it should return
        // the fillable array.
        $this->assertEquals([
            'text',
            'time',
            'created_at',
            'update_at'
        ], $this->model->getQueryable());
    }

    /**
     * @todo
     */
    public function testEmptyFillableGetQueryFields() {
        /** @var GraphQLModelStub $model */
        $this->model->fillable([]);
    }

    /**
     * @todo
     */
    public function testMapFieldsToQueryable()
    {
        // SoonTM
    }

    public function testMappedFieldsAddedToTypeSchema() {
        dd($this->model->generateQueryObject()->getFields());
    }
}
