<?php

namespace Tests\Unit\Services;

use App\Services\FormRoutingService;
use Tests\TestCase;

class FormRoutingServiceTest extends TestCase
{
    private FormRoutingService $service;

    private array $rules;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FormRoutingService();
        $this->rules = config('form_routing');
    }

    public function test_address_override_has_highest_priority(): void
    {
        $result = $this->service->resolveRecipients(
            [
                'address'    => 'override@example.com',
                'profession' => 'студент',
                'region'     => 'Москва',
                'product'    => 'promo',
            ],
            $this->rules,
        );

        $this->assertSame('override@example.com', $result['primary']);
        $this->assertSame([], $result['extra']);
    }

    public function test_student_profession_selected_if_no_address(): void
    {
        $result = $this->service->resolveRecipients(
            [
                'profession' => 'студент',
                'region'     => 'Москва',
                'product'    => 'promo',
            ],
            $this->rules,
        );

        $this->assertSame('student@example.com', $result['primary']);
    }

    public function test_region_moscow_or_spb_if_no_address_and_not_student(): void
    {
        $result = $this->service->resolveRecipients(
            [
                'region'  => 'Москва',
                'product' => 'promo',
            ],
            $this->rules,
        );

        $this->assertSame('center@example.com', $result['primary']);

        $result = $this->service->resolveRecipients(
            [
                'region'  => 'Санкт-Петербург',
                'product' => 'promo',
            ],
            $this->rules,
        );

        $this->assertSame('center@example.com', $result['primary']);
    }

    public function test_promo_product_if_no_address_profession_region_rules(): void
    {
        $result = $this->service->resolveRecipients(
            [
                'product' => 'promo',
            ],
            $this->rules,
        );

        $this->assertSame('promo@example.com', $result['primary']);
    }

    public function test_fallback_to_all_if_no_rules_matched(): void
    {
        $result = $this->service->resolveRecipients(
            [
                'name'  => 'Иван',
                'email' => 'ivan@example.com',
            ],
            $this->rules,
        );

        $this->assertSame('all@example.com', $result['primary']);
    }

    public function test_special_product_adds_extra_recipient(): void
    {
        $result = $this->service->resolveRecipients(
            [
                'product' => 'special',
                'region'  => 'Москва',
            ],
            $this->rules,
        );

        // базовый сценарий → region → center@example.com
        $this->assertSame('center@example.com', $result['primary']);

        // дополнительный получатель по special
        $this->assertSame(['special@example.com'], $result['extra']);
    }
}
