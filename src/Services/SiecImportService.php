<?php

namespace iEducar\Packages\PreMatricula\Services;

use Carbon\Carbon;
use iEducar\Packages\PreMatricula\Models\Person;
use iEducar\Packages\PreMatricula\Models\PreRegistration;
use iEducar\Packages\PreMatricula\Models\Process;
use iEducar\Packages\PreMatricula\Models\ProcessStage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Throwable;

class SiecImportService
{
    /**
     * @return array{imported: int, skipped: int, errors: array<int, array{line: int, protocol?: string, message: string}>}
     */
    public function import(UploadedFile $file, int $processId, int $schoolId, int $gradeId): array
    {
        $process = Process::query()->with(['grades', 'periods', 'stages', 'schools'])->findOrFail($processId);

        $stage = $process->stages
            ->firstWhere('process_stage_type_id', ProcessStage::TYPE_REGISTRATION);

        if (!$stage) {
            throw new \InvalidArgumentException('O processo selecionado não possui etapa de Matrícula.');
        }

        $period = $process->periods->first();

        if (!$period) {
            throw new \InvalidArgumentException('O processo selecionado não possui turno configurado.');
        }

        if (!$process->grades->contains('id', $gradeId)) {
            throw new \InvalidArgumentException('A série selecionada não pertence ao processo.');
        }

        $process->schools()->syncWithoutDetaching([$schoolId]);

        $reader = Reader::createFromPath($file->getRealPath());
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $lineNumber = 1;

        foreach ($reader->getRecords() as $record) {
            $lineNumber++;
            $protocol = null;

            try {
                $row = $this->normalizeRecord($record);
                $protocol = trim((string) ($row['Protocolo'] ?? ''));

                if ($protocol === '') {
                    $errors[] = [
                        'line' => $lineNumber,
                        'message' => 'Protocolo não informado.',
                    ];

                    continue;
                }

                if (PreRegistration::query()->where('protocol', $protocol)->exists()) {
                    $skipped++;

                    continue;
                }

                $studentName = trim((string) ($row['Nome da criança'] ?? ''));
                $studentBirth = $this->parseDate($row['Data de Nascimento'] ?? null);
                $motherName = trim((string) ($row['Nome da Mãe'] ?? ''));
                $motherCpf = $this->onlyDigits((string) ($row['CPF da Mãe'] ?? ''));
                $score = (int) preg_replace('/\D/', '', (string) ($row['Pontuação Total'] ?? '0'));

                if ($studentName === '' || !$studentBirth) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'protocol' => $protocol,
                        'message' => 'Nome ou data de nascimento da criança inválidos.',
                    ];

                    continue;
                }

                if ($motherName === '') {
                    $errors[] = [
                        'line' => $lineNumber,
                        'protocol' => $protocol,
                        'message' => 'Nome da mãe não informado.',
                    ];

                    continue;
                }

                $createdAt = $this->parseDateTime($row['Data'] ?? null) ?? now();

                DB::transaction(function () use (
                    $studentName,
                    $studentBirth,
                    $motherName,
                    $motherCpf,
                    $process,
                    $stage,
                    $period,
                    $schoolId,
                    $gradeId,
                    $protocol,
                    $score,
                    $createdAt,
                    &$imported
                ) {
                    $student = Person::create([
                        'name' => $studentName,
                        'date_of_birth' => $studentBirth->format('Y-m-d'),
                    ]);

                    $responsibleData = [
                        'name' => $motherName,
                        'date_of_birth' => '1900-01-01',
                    ];

                    if ($motherCpf !== '') {
                        $responsibleData['cpf'] = $motherCpf;
                    }

                    $responsible = Person::create($responsibleData);

                    $preregistration = PreRegistration::create([
                        'preregistration_type_id' => PreRegistration::REGISTRATION,
                        'process_id' => $process->id,
                        'process_stage_id' => $stage->id,
                        'period_id' => $period->id,
                        'school_id' => $schoolId,
                        'grade_id' => $gradeId,
                        'student_id' => $student->id,
                        'responsible_id' => $responsible->id,
                        'relation_type_id' => PreRegistration::RELATION_MOTHER,
                        'protocol' => $protocol,
                        'code' => md5($protocol),
                        'priority' => $score,
                        'status' => PreRegistration::STATUS_WAITING,
                    ]);

                    $preregistration->forceFill([
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ])->saveQuietly();

                    $imported++;
                });
            } catch (Throwable $e) {
                $errors[] = [
                    'line' => $lineNumber,
                    'protocol' => $protocol,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function normalizeRecord(array $record): array
    {
        $normalized = [];

        foreach ($record as $key => $value) {
            $cleanKey = trim((string) $key, " \t\n\r\0\x0B\"#");
            $normalized[$cleanKey] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', trim((string) $value))->startOfDay();
        } catch (Throwable) {
            try {
                return Carbon::parse($value)->startOfDay();
            } catch (Throwable) {
                return null;
            }
        }
    }

    private function parseDateTime(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        try {
            return Carbon::createFromFormat('d/m/Y H:i:s', $value);
        } catch (Throwable) {
            try {
                return Carbon::createFromFormat('d/m/Y H:i', $value);
            } catch (Throwable) {
                return $this->parseDate($value);
            }
        }
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }
}
