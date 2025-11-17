<?php

namespace Tests\Feature\Api;

use App\Jobs\SendFormEmailJob;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    public function test_form_is_accepted_and_job_dispatched(): void
    {
        Bus::fake();

        $payload = [
            'name'       => 'Иван',
            'email'      => 'ivan@example.com',
            'phone'      => '+7 999 000-00-00',
            'profession' => 'студент',
            'region'     => 'Москва',
            'product'    => 'promo',
            'address'    => 'override@example.com',
            'extraField' => 'should_be_ignored',
        ];

        $response = $this->postJson('/api/form', $payload);

        $response->assertNoContent(202);

        Bus::assertDispatched(SendFormEmailJob::class, function (SendFormEmailJob $job) use ($payload) {
            // по правилам: address имеет приоритет
            $this->assertSame('override@example.com', $job->primaryRecipient);

            // special нет → extra пустой
            $this->assertSame([], $job->extraRecipients);

            // buildPayload: только типовые поля, без address и без extraField
            $this->assertSame([
                'name'       => $payload['name'],
                'email'      => $payload['email'],
                'phone'      => $payload['phone'],
                'profession' => $payload['profession'],
                'region'     => $payload['region'],
                'product'    => $payload['product'],
            ], $job->payload);

            return true;
        });
    }

    public function test_special_product_adds_extra_recipient_in_job(): void
    {
        Bus::fake();

        $payload = [
            'email'   => 'user@example.com',
            'product' => 'special',
            'region'  => 'Москва',
        ];

        $response = $this->postJson('/api/form', $payload);

        $response->assertNoContent(202);

        Bus::assertDispatched(SendFormEmailJob::class, function (SendFormEmailJob $job) {
            // primary по региону
            $this->assertSame('center@example.com', $job->primaryRecipient);

            // special должен добавить extra
            $this->assertSame(['special@example.com'], $job->extraRecipients);

            return true;
        });
    }
}
