<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePollRequest extends FormRequest
{
    /**
     * Anyone may create a poll — this is an unauthenticated app.
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
            'question' => ['required', 'string', 'min:5', 'max:255'],
            'options' => ['required', 'array', 'min:2', 'max:10'],
            'options.*' => ['required', 'string', 'max:120', 'distinct:ignore_case'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'options.min' => 'Give voters at least two options to choose from.',
            'options.max' => 'A poll can have at most 10 options.',
            'options.*.required' => 'Option text cannot be empty.',
            'options.*.distinct' => 'Each option must be different.',
        ];
    }

    /**
     * Drop blank option rows before validating so trailing empty inputs
     * from the dynamic form do not trip the "required" rule.
     */
    protected function prepareForValidation(): void
    {
        if (is_array($this->options)) {
            $this->merge([
                'options' => array_values(array_filter(
                    array_map(fn ($o) => is_string($o) ? trim($o) : $o, $this->options),
                    fn ($o) => $o !== null && $o !== '',
                )),
            ]);
        }
    }
}
