<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * @bodyParam shipping_address string required The delivery address for this order. Example: Damascus, Mezzeh, Building 12.
     * @bodyParam shipping_phone string required Contact phone number for the delivery. Example: +963912345678
     * @bodyParam notes string optional Any special instructions for the delivery. Example: Call before arriving.
     * @bodyParam payment_receipt_image file required Screenshot of the Sham Cash payment receipt (JPEG/PNG/JPG, max 5MB).
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address'      => 'required|string|max:500',
            'shipping_phone'        => 'required|string',
            'notes'                 => 'nullable|string',
            'payment_receipt_image' => 'required|file|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_receipt_image.required' => 'Please upload a screenshot of your Sham Cash payment.',
            'payment_receipt_image.image'    => 'The payment receipt must be an image file.',
            'payment_receipt_image.max'      => 'The payment receipt image must not exceed 5MB.',
        ];
    }
}
