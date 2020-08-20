<?php

namespace JeremyNikolic\Revision\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use JeremyNikolic\Revision\Tests\Models\Book;
use JeremyNikolic\Revision\Traits\DetectChanges;

class DetectChangesTest extends TestCase
{

    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_detects_attributes()
    {
        $book = $this->createBook();
        $book::$attributesToDetect = ['title', 'content'];

        $expects = [
            'attributes' => [
                'title'   => 'An adventurers journey',
                'content' => [],
            ],
        ];

        $this->assertEquals($expects, $book->attributesToStoreInRevision('created'));
    }

    /** @test */
    public function it_detects_changes_when_updated()
    {
        $book = $this->createBook();
        $book::$attributesToDetect = ['title', 'content'];
        $book->title = 'An adventurers journey into the world';
        $expects = [
            'title'   => 'An adventurers journey into the world',
            'content' => [],
        ];
        $this->assertEquals($expects, $book->attributesToStoreInRevision('updated')['attributes']);
    }

    /** @test */
    public function id_returns_old_attributes_when_updated_if_included()
    {
        $book = $this->createBook();
        $book::$attributesToDetect = ['title'];
        $book->title = 'An adventurers journey into the world';
        $expects = [
            'attributes' => [
                'title' => $book->title,
            ],
            'old'        => [
                'title' => $book->getOriginal('title'),
            ],
        ];
        $book->save();
        $this->assertEquals($expects, $book->attributesToStoreInRevision('updated'));
    }

    /** @test */
    public function it_detects_only_dirty_attributes_if_as_only_dirty()
    {
        $book = $this->createReviseOnlyDirtyBook();
        event('eloquent.updating: '.Book::class, $book);

        $book->title = 'An adventurers journey into the world';
        $expects = [
            'attributes' => [
                'title' => $book->title,
            ],
            'old'        => [
                'title' => 'An adventurers journey',
            ],
        ];
        $this->assertEquals($expects, $book->attributesToStoreInRevision('updated'));
    }

    /** @test */
    public function it_serializes_dates()
    {
        $book = $this->createBook();
        $book::$attributesToDetect = ['created_at'];
        event('eloquent.updating: '.Book::class, $book);

        $old_date = $book->created_at;
        $date = now()->addDays(1);
        $book->created_at = $date;

        $expects = [
            'attributes' => [
                'created_at' => $book->created_at->toJSON(),
            ],
            'old'        => [
                'created_at' => $old_date->toJSON(),
            ],
        ];
        $this->assertEquals($expects, $book->attributesToStoreInRevision('updated'));
    }

    /** @test */
    public function it_apply_date_casts()
    {
        $book = $this->createBook();
        $book::$attributesToDetect = ['updated_at'];
        event('eloquent.updating: '.Book::class, $book);

        $old_date = $book->updated_at;
        $date = now()->addDays(1);
        $book->updated_at = $date;

        $expects = [
            'attributes' => [
                'updated_at' => $book->updated_at->format('Y-m-d'),
            ],
            'old'        => [
                'updated_at' => $old_date->format('Y-m-d'),
            ],
        ];
        $this->assertEquals($expects, $book->attributesToStoreInRevision('updated'));
    }

    public function it_apply_casts()
    {
        $book = $this->createBook(['content' => ['test' => 'content']]);
        $book::$attributesToDetect = ['content'];
        event('eloquent.updating: '.Book::class, $book);

        $old_content = $book->content;
        $date = now()->addDays(1);
        $book->content = [];
        $expects = [
            'attributes' => [
                'content' => $book->content,
            ],
            'old'        => [
                'content' => $old_content,
            ],
        ];
        $this->assertEquals($expects, $book->attributesToStoreInRevision('updated'));
    }

    private function createBook($attributes = [])
    {
        $book = new Book(array_merge([
                                         'title'   => 'An adventurers journey',
                                         'content' => [],
                                     ],
                                     $attributes));
        $book->save();

        return $book;
    }

    private function createReviseOnlyDirtyBook()
    {
        Book::$detectOnlyDirty = true;

        $book = new Book();
        $book->title = 'An adventurers journey';
        $book->content = [];
        $book->save();

        return $book;
    }

}
