<?php

namespace App\Http\Requests\Api\V1\Currency;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user()->can('check', Currency::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount'           => 'required|numeric|min:0.01',
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id'   => 'required|exists:currencies,id',

        ];
    }

    public function attributes(): array
    {
        return [
            'amount'           => 'monto',
            'from_currency_id' => 'moneda de origen',
            'to_currency_id'   => 'moneda de destino',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required'           => 'El monto es obligatorio.',
            'amount.numeric'            => 'El monto debe ser un número.',
            'amount.min'                => 'El monto debe ser mayor a 0.01.',
            'from_currency_id.required' => 'La moneda de origen es obligatoria.',
            'from_currency_id.exists'   => 'La moneda de origen no es válida.',
            'to_currency_id.required'   => 'La moneda de destino es obligatoria.',
            'to_currency_id.exists'     => 'La moneda de destino no es válida.',

        ];
    }
    protected function failedValidation(Validator $validator)
    {
        Log::error( $validator->errors()->toArray());
        throw new HttpResponseException(response()->json([
            'message' => __('product.destroy.message'),
            'value'   => $validator->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
    protected function failedAuthorization()
    {
        Log::warning( __('auth.request.authorization.message'));
        throw new HttpResponseException(response()->json([
            'message' => __('auth.request.authorization.message'),
            'value'   => [
                'authorization' => [__('auth.request.authorization.value')]
            ],
        ], Response::HTTP_FORBIDDEN));
    }
}
