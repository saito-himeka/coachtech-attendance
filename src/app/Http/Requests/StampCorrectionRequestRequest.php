<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'attendance_id' => 'required|exists:attendances,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            
            // 休憩1（必須）
            'rest_times.0.start_time' => 'required|date_format:H:i',
            'rest_times.0.end_time' => 'required|date_format:H:i|after:rest_times.0.start_time',
            
            // 休憩2（任意）- バリデーションなし
            'rest_times.1.start_time' => 'nullable|date_format:H:i',
            'rest_times.1.end_time' => 'nullable|date_format:H:i|after:rest_times.1.start_time',
            
            'remarks' => 'required|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'attendance_id.required' => '勤怠IDが必要です',
            'attendance_id.exists' => '指定された勤怠データが存在しません',
            
            'start_time.required' => '出勤時刻を入力してください',
            'start_time.date_format' => '出勤時刻はHH:MM形式で入力してください',
            
            'end_time.required' => '退勤時刻を入力してください',
            'end_time.date_format' => '退勤時刻はHH:MM形式で入力してください',
            'end_time.after' => '退勤時刻は出勤時刻より後にしてください',
            
            'rest_times.0.start_time.required' => '休憩開始時刻を入力してください',
            'rest_times.0.start_time.date_format' => '休憩開始時刻はHH:MM形式で入力してください',
            'rest_times.0.end_time.required' => '休憩終了時刻を入力してください',
            'rest_times.0.end_time.date_format' => '休憩終了時刻はHH:MM形式で入力してください',
            'rest_times.0.end_time.after' => '休憩終了時刻は休憩開始時刻より後にしてください',
            
            'rest_times.1.start_time.date_format' => '休憩2開始時刻はHH:MM形式で入力してください',
            'rest_times.1.end_time.date_format' => '休憩2終了時刻はHH:MM形式で入力してください',
            'rest_times.1.end_time.after' => '休憩2終了時刻は休憩2開始時刻より後にしてください',
            
            'remarks.required' => '備考を入力してください',
            'remarks.string' => '備考は文字列で入力してください',
            'remarks.max' => '備考は1000文字以内で入力してください',
        ];
    }
}