<?php

namespace App\Jobs;

use App\Facades\TextFileFormat;
use App\Models\StoredFile;
use App\Subtitles\PlainText\Srt;
use App\Subtitles\TransformsToGenericSubtitle;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ConvertToSrtJob extends FileJobJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function handle()
    {
        $this->startFileJob();

        if(!app('TextFileIdentifier')->isTextFile($this->inputStoredFile->filePath)) {
            return $this->abortFileJob('messages.not_a_text_file');
        }

        $inputSubtitle = TextFileFormat::getMatchingFormat($this->inputStoredFile->filePath);

        // Srt input files are allowed because:
        // * Sometimes files are uploaded that are srt files, but have the wrong extension
        // * Sometimes zip files are uploaded that contain srt files (from people who don't understand what an archive file is)
        // * Sometimes people upload srt files, simply not understanding the point of this tool

        if(!$inputSubtitle instanceof TransformsToGenericSubtitle && !$inputSubtitle instanceof Srt) {
            return $this->abortFileJob('messages.cant_convert_file_to_srt');
        }

        $srt = ($inputSubtitle instanceof Srt) ? $inputSubtitle : new Srt($inputSubtitle);

        $srt->stripCurlyBracketsFromCues()
            ->stripAngleBracketsFromCues()
            ->removeDuplicateCues();

        if(!$srt->hasCues()) {
            return $this->abortFileJob('messages.file_has_no_dialogue_to_convert');
        }

        $outputStoredFile = StoredFile::createFromTextFile($srt);

        return $this->finishFileJob($outputStoredFile);
    }

    public function getNewExtension()
    {
        return "srt";
    }
}
