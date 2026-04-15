<?php

namespace App\Filament\Resources\HomeworkSubmissionResource\Pages;

use App\Filament\Resources\HomeworkSubmissionResource;
use App\Notifications\HomeworkGraded;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;

class EditHomeworkSubmission extends EditRecord
{
    protected static string $resource = HomeworkSubmissionResource::class;

    // 1. АВТО-ЗАПИСЬ КУРАТОРА
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Записываем ID текущего пользователя как проверяющего,
        // если статус меняется на "проверено" (approved/rejected/revision)
        if ($data['status'] !== 'pending') {
            $data['curator_id'] = auth()->id();
            $data['reviewed_at'] = now();
        }

        return $data;
    }

    // 2. УВЕДОМЛЕНИЕ (оно у тебя уже было, оставляем)
    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if ($record->status !== 'pending') {
            $record->student->notify(new HomeworkGraded($record));
        }
    }
}