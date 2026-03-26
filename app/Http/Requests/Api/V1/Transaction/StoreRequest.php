<?php

namespace App\Http\Requests\Api\V1\Transaction;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermissionTo('transactions.store', 'api');
       // return  $this->user()->can('store', Transaction::class);
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
            'amount'           => __('transaction.store.request.attributes.amount'),
            'from_currency_id' => __('transaction.store.request.attributes.from_currency_id'),
            'to_currency_id'   => __('transaction.store.request.attributes.to_currency_id'),
        ];
    }
    public function messages(): array
    {
        return [
            'amount.required'           => __('transaction.store.request.messages.amount.required'),
            'amount.numeric'            => __('transaction.store.request.messages.amount.numeric'),
            'amount.min'                => __('transaction.store.request.messages.amount.min'),
            'from_currency_id.required' => __('transaction.store.request.messages.from_currency_id.required'),
            'from_currency_id.exists'   => __('transaction.store.request.messages.from_currency_id.exists'),
            'to_currency_id.required'   => __('transaction.store.request.messages.to_currency_id.required'),
            'to_currency_id.exists'     => __('transaction.store.request.messages.to_currency_id.exists'),
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        Log::error( $validator->errors()->toArray());
        throw new HttpResponseException(response()->json([
            'message' => __('product.store.errors'),
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
