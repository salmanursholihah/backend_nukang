<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVirtualAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // FIX: tambahkan order_id dan name yang dibutuhkan controller
            'order_id'       => 'required|string|max:11',
            'name'           => 'required|string|max:100',
            'customer_email' => 'nullable|email|max:150',
            'amount'         => 'required|numeric|min:10000|max:999999999',
            'description'    => 'nullable|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Order ID wajib diisi',
            'order_id.max'      => 'Order ID maksimal 11 karakter',
            'name.required'     => 'Nama customer wajib diisi',
            'amount.required'   => 'Nominal wajib diisi',
            'amount.min'        => 'Nominal minimum Virtual Account adalah Rp 10.000',
            'amount.max'        => 'Nominal maximum Virtual Account adalah Rp 999.999.999',
        ];
    }
}
