<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Services are read from the database: only active ones appear anywhere on the site. */
class ActiveServicesTest extends TestCase
{
    use RefreshDatabase;

    private function service(string $title, string $sector, bool $active = true): Service
    {
        return Service::create([
            'title' => $title, 'slug' => \Illuminate\Support\Str::slug($title), 'sector' => $sector,
            'summary' => 'Summary of '.$title, 'icon' => 'building', 'is_active' => $active, 'is_featured' => false, 'sort_order' => 1,
        ]);
    }

    public function test_active_services_appear_in_the_footer_and_the_contact_form_and_hidden_ones_do_not(): void
    {
        $on = $this->service('Caterpillar Services', 'construction');
        $off = $this->service('Agriculture', 'agriculture', false);

        $this->get('/contact')
            ->assertSee('Caterpillar Services')
            ->assertSee('value="service-'.$on->id.'"', false)
            ->assertDontSee('>Agriculture</option>', false)
            ->assertDontSee('value="service-'.$off->id.'"', false);

        $this->get('/')->assertSee('Caterpillar Services')->assertDontSee('>Agriculture</a>', false);
    }

    public function test_a_service_switched_off_disappears_everywhere_and_comes_back_when_switched_on(): void
    {
        $service = $this->service('Hospitality', 'general');
        $this->get('/contact')->assertSee('Hospitality');

        $service->update(['is_active' => false]);
        $this->get('/contact')->assertDontSee('Hospitality')->assertDontSee('value="service-'.$service->id.'"', false);
        $this->get('/services')->assertDontSee('Hospitality');

        $service->update(['is_active' => true]);
        $this->get('/contact')->assertSee('Hospitality');
    }

    public function test_the_contact_form_only_accepts_active_services(): void
    {
        $off = $this->service('Old Service', 'general', false);

        $this->post('/contact', ['name' => 'Ada', 'phone' => '08012345678', 'interest' => 'service-'.$off->id])
            ->assertSessionHasErrors('interest');
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_search_types_only_offer_property_types_with_an_active_service(): void
    {
        $this->service('Bulk Lands', 'real_estate');
        $this->service('Agriculture', 'agriculture', false);

        $this->get('/properties')->assertSee('Real Estate')->assertDontSee('value="agriculture"', false);
    }
}
