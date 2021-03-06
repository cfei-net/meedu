<?php


namespace Tests\Feature\Page;


use App\Services\Course\Models\Video;
use App\Services\Member\Models\User;
use App\Services\Member\Models\UserVideo;
use Carbon\Carbon;
use Tests\TestCase;

class VideoBuyPageTest extends TestCase
{

    public function test_member_orders_page()
    {
        $user = factory(User::class)->create();
        $video = factory(Video::class)->create([
            'is_show' => Video::IS_SHOW_YES,
            'published_at' => Carbon::now()->subDays(1),
            'is_ban_sell' => 0,
        ]);
        $this->actingAs($user)
            ->visit(route('member.video.buy', [$video->id]))
            ->see($video->title);
    }

    public function test_member_orders_page_cannot_sold()
    {
        $user = factory(User::class)->create();
        $video = factory(Video::class)->create([
            'is_show' => Video::IS_SHOW_YES,
            'published_at' => Carbon::now()->subDays(1),
            'is_ban_sell' => 1,
        ]);
        $this->actingAs($user)
            ->get(route('member.video.buy', [$video->id]));
        $this->assertTrue(session()->has('warning'));
        $this->assertEquals(__('this video cannot be sold'), session()->get('warning')->first());
    }

    /**
     * @expectedException \Laravel\BrowserKitTesting\HttpException
     */
    public function test_member_orders_page_with_no_published()
    {
        $user = factory(User::class)->create();
        $video = factory(Video::class)->create([
            'is_show' => Video::IS_SHOW_YES,
            'published_at' => Carbon::now()->addDays(1),
        ]);
        $this->actingAs($user)
            ->visitRoute('member.video.buy', [$video->id])
            ->seeStatusCode(404);
    }

    /**
     * @expectedException \Laravel\BrowserKitTesting\HttpException
     */
    public function test_member_orders_page_with_no_show()
    {
        $user = factory(User::class)->create();
        $video = factory(Video::class)->create([
            'is_show' => Video::IS_SHOW_NO,
            'published_at' => Carbon::now()->subDays(1),
        ]);
        $this->actingAs($user)
            ->visitRoute('member.video.buy', [$video->id])
            ->seeStatusCode(404);
    }

    public function test_member_orders_page_with_repeat_buy()
    {
        $user = factory(User::class)->create();
        $video = factory(Video::class)->create([
            'is_show' => Video::IS_SHOW_YES,
            'published_at' => Carbon::now()->subDays(1),
        ]);
        UserVideo::create([
            'user_id' => $user->id,
            'video_id' => $video->id,
            'charge' => 1,
            'created_at' => Carbon::now(),
        ]);
        $this->actingAs($user)
            ->visit(route('member.video.buy', [$video->id]))
            ->seeStatusCode(200);
    }

}