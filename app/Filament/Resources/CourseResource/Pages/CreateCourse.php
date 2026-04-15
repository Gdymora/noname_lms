<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    // ЭТОТ МЕТОД: Перехватывает данные перед записью в БД
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Если текущий пользователь НЕ Супер-Админ
        // Мы принудительно ставим teacher_id = его собственный ID
        if (!auth()->user()->hasRole('Super Admin')) {
            $data['teacher_id'] = auth()->id();
        }

        return $data;
    }
}