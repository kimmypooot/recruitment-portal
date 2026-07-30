<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Services\EmailTemplateMailBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => EmailTemplate::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'greeting' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'action_text' => 'nullable|string|max:100',
            'action_url' => $emailTemplate->action_locked ? 'nullable' : 'nullable|string|max:255',
            'footer' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($emailTemplate->action_locked) {
            unset($data['action_url']);
        }

        $emailTemplate->update($data);

        return response()->json(['data' => $emailTemplate->fresh()]);
    }

    public function preview(EmailTemplate $emailTemplate): JsonResponse
    {
        $sample = $emailTemplate->sample_data ?? [];
        $replacements = [];
        foreach ($sample as $placeholder => $value) {
            $replacements["{{{$placeholder}}}"] = $value;
        }

        $forcedActionUrl = $emailTemplate->action_locked ? '#' : null;

        return response()->json([
            'data' => EmailTemplateMailBuilder::render($emailTemplate, $replacements, $forcedActionUrl),
        ]);
    }
}
