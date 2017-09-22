<?php

namespace Tests\Unit\Pages\Tag;

use Tests\TestCase;
use Tests\TestHelper;
use Tests\CreatesUser;
use Illuminate\Http\Response;
use Canvas\Helpers\CanvasHelper;
use Tests\InteractsWithDatabase;

class TagCreatePageTest extends TestCase
{
    use InteractsWithDatabase, CreatesUser, TestHelper;

    /** @test */
    public function it_can_press_cancel_to_return_to_the_tag_index_page()
    {
        // Actions
        $this->createUser()->actingAs($this->user)
            ->visit(route('canvas.admin.tag.create'))
            ->click('Cancel');

        // Assertions
        $this->assertResponseStatus(Response::HTTP_OK)
            ->seePageIs(route('canvas.admin.tag.index'))
            ->see(e('Tags'));
        $this->assertSessionMissing('errors');
    }

    /** @test */
    public function it_validates_the_tag_create_form()
    {
        $this->createUser()->callRouteAsUser('canvas.admin.tag.store', null, ['title' => 'example'])
            ->assertSessionHasErrors();
    }

    /** @test */
    public function it_can_create_a_tag_and_save_it_to_the_database()
    {
        $this->createUser()->actingAs($this->user)->post(route('canvas.admin.tag.store'), [
            'tag'               => 'example',
            'title'             => 'foo',
            'subtitle'          => 'bar',
            'meta_description'  => 'FooBar',
            'layout'            => config('blog.tag_layout'),
            'reverse_direction' => 0,
        ]);

        $this->seeInDatabase(CanvasHelper::TABLES['tags'], [
            'tag'               => 'example',
            'title'             => 'foo',
            'subtitle'          => 'bar',
            'meta_description'  => 'FooBar',
            'layout'            => config('blog.tag_layout'),
            'reverse_direction' => 0,
        ]);

        $this->assertSessionHas('_new-tag', trans('canvas::messages.create_success', ['entity' => 'tag']));
        $this->assertRedirectedTo(route('canvas.admin.tag.index'));
    }
}
