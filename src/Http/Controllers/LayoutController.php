<?php

namespace Fazzinipierluigi\LaraccoonLayouts\Http\Controllers;

use Fazzinipierluigi\LaraccoonLayouts\Models\RaccoonLayout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LayoutController extends Controller
{
    public function getByPage(string $pageKey): JsonResponse
    {
        $userId = auth()->id();

        $layouts = RaccoonLayout::where('page_key', $pageKey)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('is_public', true);
            })
            ->orderBy('name')
            ->get(['id', 'user_id', 'name', 'layout_data', 'is_public', 'is_default']);

        return response()->json($layouts);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'page_key' => 'required|string|max:40',
            'layout_data' => 'required|array',
            'is_public' => 'boolean',
        ]);

        $layout = RaccoonLayout::create([
            'user_id' => auth()->id(),
            'page_key' => $data['page_key'],
            'name' => $data['name'],
            'layout_data' => $data['layout_data'],
            'is_public' => $data['is_public'] ?? false,
            'is_default' => false,
        ]);

        return response()->json($layout, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $layout = RaccoonLayout::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'layout_data' => 'sometimes|array',
            'is_public' => 'sometimes|boolean',
        ]);

        $layout->update($data);

        return response()->json($layout);
    }

    public function destroy(int $id): JsonResponse
    {
        $layout = RaccoonLayout::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $layout->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function setDefault(int $id): JsonResponse
    {
        $layout = RaccoonLayout::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        RaccoonLayout::where('user_id', auth()->id())
            ->where('page_key', $layout->page_key)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $layout->update(['is_default' => true]);

        return response()->json($layout);
    }

    public function copy(int $id): JsonResponse
    {
        $layout = RaccoonLayout::where('id', $id)
            ->where(function ($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('is_public', true);
            })
            ->firstOrFail();

        $copy = RaccoonLayout::create([
            'user_id' => auth()->id(),
            'page_key' => $layout->page_key,
            'name' => 'Copia di ' . $layout->name,
            'layout_data' => $layout->layout_data,
            'is_public' => false,
            'is_default' => false,
        ]);

        return response()->json($copy, 201);
    }
}
