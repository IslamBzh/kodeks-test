<?php

namespace App\Services;

final class FormRoutingService
{
    /**
     * @param array{
     *     name?: string|null,
     *     email?: string|null,
     *     phone?: string|null,
     *     profession?: string|null,
     *     region?: string|null,
     *     product?: string|null,
     *     address?: string|null,
     * } $data
     *
     * @param array{
     *     primary: array{
     *          field: string,
     *          recipient: string,
     *          equals?: string|string[],
     *     }[],
     *     extra: array{
     *          field: string,
     *          recipient: string,
     *          equals?: string|string[],
     *     }[],
     *     default: string
     * } $rules
     *
     * @return array{
     *     primary: string,
     *     extra: string[],
     * }
     */
    public function resolveRecipients(
        array $data,
        array $rules,
    ): array
    {
        // если в форме присутствует поле address с указанным email-адресом,
        // данные должны быть отправлены на этот email
        if(!empty($data['address']) && filter_var($data['address'], FILTER_VALIDATE_EMAIL))
            $primary = $data['address'];

        else
            $primary = $this->matchRules($data, $rules['primary'], true);

        $extra = $this->matchRules($data, $rules['extra'], false);

        return [
            'primary' => $primary ?? $rules['default'],
            'extra'   => array_values(array_unique($extra)),
        ];
    }


    /**
     * Проходит по правилам и возвращает подходящий(е)
     *
     * @param array $data
     * @param array{
     *     field: string,
     *     recipient: string,
     *     equals?: string|string[],
     * }[]          $rules
     * @param bool  $firstOnly Если `true` — возвращает первого
     *
     * @return string|string[]|null Первый подходящий или список всех подходящих получателей
     */
    private function matchRules(
        array $data,
        array $rules,
        bool  $firstOnly = false,
    ): array|string|null
    {
        $recipients = [];

        foreach ($rules as $rule) {
            if(!isset($rule['field'], $rule['equals'], $rule['recipient']))
                continue;

            $field = $rule['field'];

            if(!isset($data[$field]))
                continue;

            $value = $data[$field];
            $equals = $rule['equals'];

            if(is_array($equals)) {

                if(!in_array($value, $equals, true))
                    continue;

            } else {

                if($value !== $equals)
                    continue;
            }

            if($firstOnly)
                return $rule['recipient'];

            $recipients[] = $rule['recipient'];
        }

        if($firstOnly)
            return null;

        return array_values(array_unique($recipients));
    }

}
