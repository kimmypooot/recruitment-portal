<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function store(Request $request, Application $application): JsonResponse
    {
        $request->validate([
            'document_type' => 'required|string',
            'file'          => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $storedName   = Str::uuid() . '.' . $file->extension();

        $path = $file->storeAs('documents/' . $application->id, $storedName, 'private');

        $document = $application->documents()->create([
            'document_type'     => $request->document_type,
            'original_filename' => $originalName,
            'stored_filename'   => $storedName,
            'file_path'         => $path,
            'file_size'         => $file->getSize(),
            'mime_type'         => $file->getMimeType(),
        ]);

        return response()->json($document, 201);
    }

    public function verify(Document $document): JsonResponse
    {
        $document->update(['verified_at' => now()]);
        return response()->json(['message' => 'Document verified.']);
    }
}
