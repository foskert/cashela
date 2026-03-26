<?php

namespace App\Http\Requests\Api\V1\Currency;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

       /* if(config('api.defaults.authorization', false)){
            return true;
        }
        if (!Auth::check()) {
            return false;
        }*/
        return $this->user()->can('index', Currency::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_by'           => ['nullable', Rule::in(['ASC', 'DESC', 'asc', 'desc'])],
            'sort_by'            => ['nullable', Rule::in(['id', 'code', 'created_at'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'order_by'           => __('product.index.request.attributes.order_by'),
            'sort_by'            => __('product.index.request.attributes.sort_by'),
        ];
    }

    public function messages(): array
    {
        return [
            'in'           => __('product.index.request.messages.in'),
            'search.max'   => __('product.index.request.messages.search.max'),
            'paginate.min' => __('product.index.request.messages.paginate.min'),
            'paginate.max' => __('product.index.request.messages.paginate.max'),
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
