<?php

namespace Cooper\FilamentDcatFilters\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
            'column' => ['required', 'string'],
            'keyColumn' => ['sometimes', 'string'],
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

        // Verify if model class exists
        if (! class_exists($modelClass)) {
            return response()->json([
                'error' => 'Model class not found',
                'labels' => [],
            ], 404);
        }

        try {
            $records = $modelClass::whereIn($keyColumn, $ids)->get();
            $labels = $records->pluck($column)->toArray();

            return response()->json([
                'labels' => $labels,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'labels' => [],
            ], 500);
        }
    }
}
