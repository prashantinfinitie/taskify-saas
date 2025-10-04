<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function generateDescription(Request $request)
    {
        // dd($request);

        $request->validate([
            'prompt' => 'required|string|max:255',
            'existingDescription' => 'nullable|string',
            'isCustomPrompt' => 'required|in:true,false,0,1,TRUE,FALSE'
        ]);

        try {
            $userPrompt = $request->input('prompt');
            $existingDescription = $request->input('existingDescription');
            $isCustomPrompt = $request->input('isCustomPrompt');
            $currentLocale = session('my_locale');

            if ($isCustomPrompt) {
                $fullPrompt = "Generate a professional content.

            Existing Description:
            " . ($existingDescription ?: "No existing description.") . "

            User Request:
            {$userPrompt}



            Instructions:
            - Improve or rewrite the description in clean, mobile-friendly HTML.
            - Use <p> for paragraphs, <ul><li> for bullets, and <strong> for emphasis.
            - Focus on clarity, relevance, and professional tone.
            - Limit to 500 words.
            - Language: {$currentLocale}

            Output only the HTML content—no titles, headers, or extra text.";
            } else {
                $fullPrompt = "Act as a content writer and generate a project/task description.

            Title:
            {$userPrompt}

            " . ($existingDescription ? "Current Description:\n{$existingDescription}" : "No existing description.") . "

            Instructions:
            - Write a clear, value-driven HTML description.
            - Cover purpose, key deliverables, and benefits.
            - Use <p>, <ul><li>, and <strong> as needed.
            - Keep it concise (200-300 words), mobile-friendly, and professional.
            - Language: {$currentLocale}

            Return HTML only—no extra explanations.";
            }


            // Replace this with your actual AI helper integration
            $description = generate_description($fullPrompt);

            if ($description['error']) {
                // dd($description);
                return response()->json([
                    'error' => true,
                    'message' =>  $description['message'],
                ], 200);
            }

            return response()->json([
                'error' => false,
                'message' => 'Description Generated Successfully.',
                'description' => preg_replace(
                    '/^```html\s*|```$/m',
                    '',
                    trim(($description['data']))
                ),
            ]);
        } catch (\Throwable $e) {
            dd($e);
            // Log the error for debugging
            Log::error('AI Description Generation Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'error' => true,
                'message' => 'An error occurred while generating the description. Please contact support.',
            ], 500);
        }
    }
}