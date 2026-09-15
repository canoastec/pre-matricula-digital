<?php

namespace iEducar\Packages\PreMatricula\Http\Controllers;

use iEducar\Packages\PreMatricula\Services\SiecImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class SiecImportController
{
    public function __construct(
        private SiecImportService $service
    ) {}

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
            'process_id' => ['required', 'integer'],
            'school_id' => ['required', 'integer'],
            'grade_id' => ['required', 'integer'],
        ]);

        try {
            $result = $this->service->import(
                $request->file('file'),
                (int) $request->input('process_id'),
                (int) $request->input('school_id'),
                (int) $request->input('grade_id')
            );

            return response()->json($result);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Não foi possível importar o arquivo SIEC.',
            ], 500);
        }
    }
}
