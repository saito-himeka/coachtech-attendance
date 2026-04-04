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
            // 出勤・退勤（必須）
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            
            // 休憩時間（配列形式）
            'rest_times' => 'nullable|array',
            'rest_times.*.start_time' => 'nullable|date_format:H:i',
            'rest_times.*.end_time' => 'nullable|date_format:H:i',
            
            // 備考（必須、最大1000文字）
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
            'start_time.required' => '出勤時刻を入力してください',
            'start_time.date_format' => '出勤時刻は HH:MM 形式で入力してください',
            'end_time.required' => '退勤時刻を入力してください',
            'end_time.date_format' => '退勤時刻は HH:MM 形式で入力してください',
            'rest_times.*.start_time.date_format' => '休憩開始時刻は HH:MM 形式で入力してください',
            'rest_times.*.end_time.date_format' => '休憩終了時刻は HH:MM 形式で入力してください',
            'remarks.required' => '備考を記入してください',
            'remarks.string' => '備考は文字列で入力してください',
            'remarks.max' => '備考は1000文字以内で入力してください',
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

            // 1. 出勤時間が退勤時間より後になっている場合、および退勤時間が出勤時間より前になっている場合
            if ($startTime && $endTime && $startTime >= $endTime) {
                $validator->errors()->add('end_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩時間のチェック
            foreach ($restTimes as $index => $rest) {
                // 空の休憩時間はスキップ
                if (empty($rest['start_time']) && empty($rest['end_time'])) {
                    continue;
                }

                // 開始時刻のみ入力されている場合
                if (!empty($rest['start_time']) && empty($rest['end_time'])) {
                    $validator->errors()->add("rest_times.{$index}.end_time", '休憩終了時刻を入力してください');
                    continue;
                }

                // 終了時刻のみ入力されている場合
                if (empty($rest['start_time']) && !empty($rest['end_time'])) {
                    $validator->errors()->add("rest_times.{$index}.start_time", '休憩開始時刻を入力してください');
                    continue;
                }

                // 両方入力されている場合のチェック
                if (!empty($rest['start_time']) && !empty($rest['end_time'])) {
                    // 休憩終了時刻が開始時刻より前
                    if ($rest['start_time'] >= $rest['end_time']) {
                        $validator->errors()->add("rest_times.{$index}.end_time", '休憩終了時刻は開始時刻より後にしてください');
                    }

                    // 2. 休憩開始時間が出勤時間より前になっている場合
                    if ($startTime && $rest['start_time'] < $startTime) {
                        $validator->errors()->add("rest_times.{$index}.start_time", '休憩時間が勤務時間外です');
                    }

                    // 2. 休憩開始時間が退勤時間より後になっている場合
                    if ($endTime && $rest['start_time'] >= $endTime) {
                        $validator->errors()->add("rest_times.{$index}.start_time", '休憩時間が勤務時間外です');
                    }

                    // 3. 休憩終了時間が退勤時間より後になっている場合
                    if ($endTime && $rest['end_time'] > $endTime) {
                        $validator->errors()->add("rest_times.{$index}.end_time", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}