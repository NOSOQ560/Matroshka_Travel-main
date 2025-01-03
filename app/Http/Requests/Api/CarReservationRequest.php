<?php

namespace App\Http\Requests\Api;

class CarReservationRequest extends CustomRequest
{
    public function rules(): array
    {
        $rules = [
            'trip_type' => 'required|string|in:accommodation,recreational',
            'pickup_location' => 'required|string',
            'arrival_location' => 'required|string',
            'departing' => 'required|date_format:Y-m-d H:i',
            'packages' => 'required|integer|min:0',
            'childrens' => 'required|integer|min:0',
            'childAge' => 'nullable|array',
            'childAge.*.child' => 'required|integer|min:1',
            'childAge.*.age' => 'required|integer|min:1|max:18',
            'adults' => 'required|integer|min:1',
            'car_type' => 'required|string|in:business,normal',
            'additional_notes' => 'required|string',
        ];

        if ($this->trip_type == 'recreational') {
            unset($rules['arrival_location']);
            $rules = array_merge($rules, [
                'returning' => [
                    'required',
                    'date_format:Y-m-d H:i',
                    'after_or_equal:departing',
                    function ($attribute, $value, $fail) {
                        $departing = strtotime($this->input('departing'));
                        $returning = strtotime($value);

                        if (($returning - $departing) < 5 * 60 * 60) { // أقل من 5 ساعات
                            $fail('The returning time must be at least 5 hours after the departing time.');
                        }
                    }
                ],
            ]);
        }

        return $rules;
    }
}
