<?php

namespace App\Filament\Resources\QueueNumberResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQueueNumberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
			'queue_id' => 'required',
			'queue_number' => 'required',
			'status' => 'required',
			'called_at' => 'required',
			'finished_at' => 'required'
		];
    }
}
