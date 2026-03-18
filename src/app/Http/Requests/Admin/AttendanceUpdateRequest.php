<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
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
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'rest_times' => 'nullable|array',
            'rest_times.*.start_time' => 'required|date_format:H:i',
            'rest_times.*.end_time' => 'nullable|date_format:H:i|after:rest_times.*.start_time',
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
            'start_time.required' => '出勤時刻を入力してください',
            'start_time.date_format' => '出勤時刻の形式が正しくありません',
            'end_time.required' => '退勤時刻を入力してください',
            'end_time.date_format' => '退勤時刻の形式が正しくありません',
            'end_time.after' => '出勤時刻もしくは退勤時刻が不適切な値です',
            'rest_times.*.start_time.required' => '休憩開始時刻を入力してください',
            'rest_times.*.start_time.date_format' => '休憩開始時刻の形式が正しくありません',
            'rest_times.*.end_time.date_format' => '休憩終了時刻の形式が正しくありません',
            'rest_times.*.end_time.after' => '休憩時間が不適切な値です',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $restTimes = $this->input('rest_times', []);

            // 出勤・退勤時刻のチェック
            if ($startTime && $endTime && $startTime >= $endTime) {
                $validator->errors()->add('end_time', '出勤時刻もしくは退勤時刻が不適切な値です');
            }

            // 休憩時間のチェック
            foreach ($restTimes as $index => $rest) {
                if (!empty($rest['start_time'])) {
                    // 休憩開始時刻が出勤時刻より前
                    if ($startTime && $rest['start_time'] < $startTime) {
                        $validator->errors()->add("rest_times.{$index}.start_time", '休憩時間が不適切な値です');
                    }

                    // 休憩開始時刻が退勤時刻より後
                    if ($endTime && $rest['start_time'] > $endTime) {
                        $validator->errors()->add("rest_times.{$index}.start_time", '休憩時間が不適切な値です');
                    }

                    // 休憩終了時刻が退勤時刻より後
                    if (!empty($rest['end_time']) && $endTime && $rest['end_time'] > $endTime) {
                        $validator->errors()->add("rest_times.{$index}.end_time", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}