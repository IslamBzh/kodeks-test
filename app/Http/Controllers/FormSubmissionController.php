<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitFormRequest;
use App\Jobs\SendFormEmailJob;
use App\Services\FormRoutingService;
use Illuminate\Http\Response;

class FormSubmissionController extends Controller
{
    public function __invoke(
        SubmitFormRequest  $request,
        FormRoutingService $routingService,
    ): Response
    {
        $validated = $request->validated();
        $rules = config('form_routing');

        $recipients = $routingService->resolveRecipients($validated, $rules);
        $payload = $this->buildPayload($validated);

        SendFormEmailJob::dispatch(
            $recipients['primary'],
            $recipients['extra'],
            $payload,
        );

        return response()->noContent(202);
    }

    /**
     * Формирует итоговый набор данных для письма.
     * Возвращает только типовые поля ТЗ (если они были переданы).
     *
     * @param array{
     *      name?: string|null,
     *      email?: string|null,
     *      phone?: string|null,
     *      profession?: string|null,
     *      region?: string|null,
     *      product?: string|null,
     *      address?: string|null
     *  } $data
     *
     * @return array{
     *      name?: string,
     *      email?: string,
     *      phone?: string,
     *      profession?: string,
     *      region?: string,
     *      product?: string
     * }
     */
    private function buildPayload(array $data): array
    {
        $allowedKeys = [
            'name',
            'email',
            'phone',
            'profession',
            'region',
            'product',
        ];

        return array_intersect_key($data, array_flip($allowedKeys));
    }
}
