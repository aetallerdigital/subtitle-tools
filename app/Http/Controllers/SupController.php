<?php

namespace App\Http\Controllers;

use App\Facades\FileName;
use App\Http\Rules\FileNotEmptyRule;
use App\Jobs\SupToSrtJob;
use App\Models\StoredFile;
use App\Models\SupJob;
use Illuminate\Http\Request;

class SupController extends Controller
{
    public function index()
    {
        return view('guest.sup')->with('languages', config('st.tesseract.languages'));
    }

    public function post(Request $request)
    {
        $request->validate([
            'subtitle'    => ['required', 'file', new FileNotEmptyRule],
            'ocrLanguage' => 'required|in:'.implode(',', config('st.tesseract.languages')),
        ]);

        $supFile = $request->file('subtitle');

        $ocrLanguage = $request->get('ocrLanguage');

        $inputFile = StoredFile::getOrCreate($supFile);

        $supJob = SupJob::query()
            ->where('input_stored_file_id', $inputFile->id)
            ->where('ocr_language', $ocrLanguage)
            ->first();

        if($supJob === null) {
            $supJob = SupJob::create([
                'url_key'              => str_random(16),
                'input_stored_file_id' => $inputFile->id,
                'ocr_language'         => $ocrLanguage,
                'original_name'        => basename($supFile->getClientOriginalName()),
            ]);

            dispatch(
                (new SupToSrtJob($supJob))->onQueue('slow-high')
            );
        }

        return redirect()->route('sup.show', $supJob->url_key);
    }

    public function show($urlKey)
    {
        $supJob = SupJob::where('url_key', $urlKey)->firstOrFail();

        return view('guest.supShow', [
            'originalName' => $supJob->original_name,
            'ocrLanguage'  => $supJob->ocr_language,
            'urlKey'       => $urlKey,
            'returnUrl'    => route('sup'),
        ]);
    }

    public function download($urlKey)
    {
        $supJob = SupJob::query()
            ->where('url_key', $urlKey)
            ->whereNull('error_message')
            ->whereNotNull('finished_at')
            ->firstOrFail();

        $filePath = $supJob->outputStoredFile->file_path;

        $fileName = FileName::changeExtension($supJob->original_name, 'srt');

        return response()->download($filePath, $fileName);
    }

}