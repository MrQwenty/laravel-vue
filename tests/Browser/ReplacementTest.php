<?php

namespace Tests\Browser;

use App\Models\Replacement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Replacements;
use Tests\DuskTestCase;

class ReplacementTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testTableShown()
    {
        $replacement = Replacement::factory()->create();
        $user = User::factory()->create([
            'email' => 'test@test.it',
        ]);

        $this->browse(function (Browser $browser) use ($user, $replacement) {
            $browser->loginAs($user)
                ->visit(new Replacements)
                ->assertSee('Ricambi')
                ->assertPresent('@replacements-table')
                ->assertSee($replacement->name)
                ->assertNotPresent('@pagination');
        });
    }

    public function testTablePagination()
    {
        Replacement::factory(20)->create();
        $user = User::factory()->create([
            'email' => 'test@test.it',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Replacements)
                ->assertPresent('@pagination')
                ->click('@page-next')
                ->assertQueryStringHas('page', 2);
        });
    }

    public function testNewReplacement()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Replacements)
                ->click('@new-replacement-btn')
                ->whenAvailable('@new-replacement-modal', function (Browser $modal) {
                    $modal->assertSee('Nuovo ricambio')
                        ->waitFor('form')
//                        ->type('name', 'Prova')
                        ->click('[type="submit"]')
                        ->assertPresent('.formkit-messages');
                });
        });
    }
}
