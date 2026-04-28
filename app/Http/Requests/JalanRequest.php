<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JalanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We'll assume the Auth middleware handles broad access
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jalan_propinsi_id' => 'required|integer',
            'jalan_kota_id' => 'required|integer',
            'jalan_kode' => 'nullable|string|max:255',
            'jalan_nama' => 'required|string|max:255',
            'jalan_panjang' => 'required|numeric',
            'jalan_lebar' => 'required|numeric',
            'jalan_akses' => 'nullable|string|max:255',
            'jalan_keterangan' => 'nullable|string',
            'jalan_llh' => 'nullable|string|max:255',
            'jalan_foto' => 'nullable|image|max:2048',
        ];
    }
}
