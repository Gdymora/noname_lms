<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeworkController extends Controller
{
    public function submit(Request $request, Homework $homework)
    {
        // 1. Подготовка данных
        $inputData = $request->input('fields', []);
        $submissionContent = [];

        // 2. Перебираем настройки задания
        foreach ($homework->submission_fields as $field) {
            $label = $field['label'];
            $type = $field['type'];
            $isRequired = $field['required'] ?? false;
            
            // А. Обработка ФАЙЛОВ
            if ($type === 'file') {
                if ($request->hasFile("fields.$label")) {
                    $file = $request->file("fields.$label");
                    $path = $file->store('homeworks', 'public');
                    
                    $submissionContent[$label] = [
                        'type' => 'file',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                    ];
                } elseif ($isRequired) {
                    return back()->withErrors(["fields.$label" => "Поле $label обязательно для загрузки."]);
                }
            } 
            // Б. Обработка МАССИВОВ (Чекбоксы)
            elseif ($type === 'checkboxes') {
                $value = $inputData[$label] ?? [];
                
                if ($isRequired && empty($value)) {
                    return back()->withErrors(["fields.$label" => "Выберите хотя бы один вариант."]);
                }
                
                $submissionContent[$label] = $value; // Сохраняем массив как есть
            }
            // В. Обработка ТЕКСТА
            else {
                $value = $inputData[$label] ?? null;
                
                if ($isRequired && empty($value)) {
                    return back()->withErrors(["fields.$label" => "Поле $label обязательно."]);
                }

                $submissionContent[$label] = $value;
            }
        }

        // 3. Сохраняем
        $submission = HomeworkSubmission::updateOrCreate(
            [
                'homework_id' => $homework->id,
                'student_id' => Auth::id(),
            ],
            [
                'content' => $submissionContent,
                'status' => 'pending',
                'created_at' => now(),
            ]
        );

        // --- ДОБАВЛЯЕМ ОТПРАВКУ ---
        // Ищем админа (в будущем здесь будет логика поиска куратора курса)
        $admin = \App\Models\User::find(1); 
        if ($admin) {
            $admin->notify(new \App\Notifications\NewSubmissionCreated($submission));
        }
        // --------------------------

        // 4. Возвращаем назад с успехом
        return redirect()->back()->with('success', 'Ответ успешно отправлен!');
    }
}