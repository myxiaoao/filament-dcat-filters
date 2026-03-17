<?php

namespace Cooper\FilamentDcatFilters\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ModalSelectController extends Controller
{
    /**
     * Fetch labels for model records.
     */
    public function fetchLabels(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'model' => ['required', 'string'],
            'ids' => ['required', 'array'],
            'ids.*' => ['required'],
            'column' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z_][a-zA-Z0-9_.]*$/'],
            'keyColumn' => ['sometimes', 'string', 'max:100', 'regex:/^[a-zA-Z_][a-zA-Z0-9_.]*$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid request',
                'labels' => [],
            ], 400);
        }

        $modelClass = $request->input('model');
        $ids = $request->input('ids');
        $column = $request->input('column');
        $keyColumn = $request->input('keyColumn', 'id');

        // Verify model class exists and is a valid Eloquent model
        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return response()->json([
                'error' => 'Invalid model class',
                'labels' => [],
            ], 400);
        }

        // Check if model is in allowed list
        $allowedModels = config('filament-dcat-filters.allowed_models', []);
        if (empty($allowedModels)) {
            // When no models are explicitly allowed, deny all requests for security
            Log::warning('No allowed_models configured for filament-dcat-filters', ['model' => $modelClass]);

            return response()->json([
                'error' => 'No models configured',
                'labels' => [],
            ], 403);
        }

        if (! in_array($modelClass, $allowedModels, true)) {
            Log::warning('Unauthorized model access attempt', ['model' => $modelClass]);

            return response()->json([
                'error' => 'Model not allowed',
                'labels' => [],
            ], 403);
        }

        // Check authorization using Laravel Gate
        if (Gate::getPolicyFor($modelClass) && Gate::denies('viewAny', $modelClass)) {
            return response()->json([
                'error' => 'Unauthorized',
                'labels' => [],
            ], 403);
        }

        try {
            $labels = $modelClass::query()
                ->whereIn($keyColumn, $ids)
                ->pluck($column, $keyColumn)
                ->toArray();

            return response()->json([
                'labels' => $labels,
            ]);
        } catch (QueryException $e) {
            Log::error('Database error in fetchLabels', [
                'model' => $modelClass,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Database error',
                'labels' => [],
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error in fetchLabels', [
                'model' => $modelClass,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Server error',
                'labels' => [],
            ], 500);
        }
    }
}
