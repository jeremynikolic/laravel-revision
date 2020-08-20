<?php

namespace JeremyNikolic\Revision\Tests;

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
        $book->attributesToDetect = ['title', 'content'];

        $expects = [
            'attributes' => [
                'title'   => 'An adventurers journey',
                'content' => 'the content',
            ],
        ];

        $this->assertEquals($expects, $book->attributesToStoreInRevision('created'));
    }

    /** @test */
    public function it_detects_changes_when_updated()
    {
        $book = $this->createBook();
        $book->attributesToDetect = ['title', 'content'];
        $book->title = 'An adventurers journey into the world';
        $expects = [
            'title'   => 'An adventurers journey into the world',
            'content' => 'the content',
        ];
        $this->assertEquals($expects, $book->attributesToStoreInRevision('updated')['attributes']);
    }

    /** @test */
    public function id_returns_old_attributes_when_updated_if_included()
    {
        $book = $this->createBook();
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

    private function createBook()
    {
        $book = new Book();
        $book->title = 'An adventurers journey';
        $book->content = 'the content';
        $book->save();

        return $book;
    }

    private function createReviseOnlyDirtyBook()
    {
        Book::$detectOnlyDirty = true;

        $book = new Book();
        $book->title = 'An adventurers journey';
        $book->content = 'the content';
        $book->save();

        return $book;
    }

}
