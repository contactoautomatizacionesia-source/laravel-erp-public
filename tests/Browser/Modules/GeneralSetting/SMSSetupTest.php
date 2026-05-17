<?php

namespace Tests\Browser\Modules\GeneralSetting;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use function PHPSTORM_META\type;

class SMSSetupTest extends DuskTestCase
{
    use WithFaker;

    private function envValue(string $key, string $default): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return ($value !== false && $value !== null && $value !== '') ? (string) $value : $default;
    }
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_for_visit_sms_setup_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit('/generalsetting/sms-setting')
                ->assertSee('SMS Settings');
        });
    }

    public function test_twillio_setting_update(){
        $this->test_for_visit_sms_setup_page();
        $this->browse(function (Browser $browser) {
            $browser->click('#main-content > section > div > div > div > form:nth-child(1) > div:nth-child(3) > div > ul > li:nth-child(1) > label > span')
                ->click('#sms_setting > li:nth-child(1) > label > span')
                ->type('#Twilio_Settings > div.row > div:nth-child(1) > div > input.primary_input_field', $this->envValue('TWILIO_SID', 'twilio_sid_placeholder'))
                ->type('#Twilio_Settings > div.row > div:nth-child(2) > div > input.primary_input_field', $this->envValue('TWILIO_TOKEN', 'twilio_token_placeholder'))
                ->type('#Twilio_Settings > div.row > div:nth-child(3) > div > input.primary_input_field', $this->envValue('VALID_TWILLO_NUMBER', '+10000000000'))
                ->click('#Twilio_Settings > div.submit_btn.text-center.mb-100.pt_15 > button')
                ->assertPathIs('/generalsetting/sms-setting')
                ->waitFor('.toast-message',25)
                ->assertSeeIn('.toast-message', 'SMS Gateways Credentials has been updated Successfully');

        });
    }

    public function test_for_test_sms(){
        $this->test_twillio_setting_update();
        $this->browse(function (Browser $browser) {
            $browser->type('#main-content > section > div > div > div > form:nth-child(3) > div.row > div:nth-child(1) > div > input', '+8801875033293')
                ->type('#main-content > section > div > div > div > form:nth-child(3) > div.row > div:nth-child(2) > div > input', 'test sms')
                ->click('#main-content > section > div > div > div > form:nth-child(3) > div.submit_btn.text-center.mb-100.pt_15 > button')
                ->assertPathIs('/generalsetting/sms-setting')
                ->waitFor('.toast-message',25)
                ->assertSeeIn('.toast-message', 'SMS has been sent Successfully');

        });

    }


    public function test_for_text_to_local_update(){
        $this->test_for_visit_sms_setup_page();
        $this->browse(function (Browser $browser) {
            $browser->click('#main-content > section > div > div > div > form:nth-child(1) > div:nth-child(3) > div > ul > li:nth-child(2) > label > span')
                ->click('#sms_setting > li:nth-child(2) > label > span')
                ->type('#TexttoLocal_Settings > div.row > div:nth-child(1) > div > input.primary_input_field', $this->faker->slug)
                ->type('#TexttoLocal_Settings > div.row > div:nth-child(2) > div > input.primary_input_field', $this->faker->slug)
                ->click('#TexttoLocal_Settings > div.submit_btn.text-center.pt_15 > button')
                ->assertPathIs('/generalsetting/sms-setting')
                ->waitFor('.toast-message',25)
                ->assertSeeIn('.toast-message', 'SMS Gateways Credentials has been updated Successfully');
        });
    }


}
