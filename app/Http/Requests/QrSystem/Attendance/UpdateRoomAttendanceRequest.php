<?php

namespace App\Http\Requests\QrSystem\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomAttendanceRequest extends FormRequest
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
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'attendance_date' => ['required', 'date'],
            'time_in' => ['required', 'date'],
            'time_out' => ['nullable', 'date', 'after:time_in'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}
