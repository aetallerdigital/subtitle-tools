<?php

namespace Tests\Unit\Controllers\FileJobs;

use Tests\CreatesUploadedFiles;
use Tests\PostsFileJobs;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MergeControllerTest extends TestCase
{
    use RefreshDatabase, CreatesUploadedFiles, PostsFileJobs;

    protected $snapshotDirectory = 'merge';

    private function postMergeJob($attributes)
    {
        return $this->postFileJob('merge', [], $attributes + [
            'nearest_cue_threshold' => 1000,
            'mode'                  => 'simple',
        ]);
    }

    /** @test */
    function it_can_simple_merge_subtitles_into_srt_files()
    {
        [$response, $fileGroup] = $this->postMergeJob([
            'subtitles'       => $this->createUploadedFile('text/srt/three-cues.srt'),
            'second-subtitle' => $this->createUploadedFile('text/ass/three-cues.ass'),
            'mode'            => 'simple',
        ]);

        $this->assertSuccessfulFileJobRedirect($response, $fileGroup);

        $this->assertMatchesStoredFileSnapshot(3);
    }

    /** @test */
    function it_can_simple_merge_subtitles_into_ass_files()
    {
        [$response, $fileGroup] = $this->postMergeJob([
            'subtitles'       => $this->createUploadedFile('text/ass/three-cues.ass'),
            'second-subtitle' => $this->createUploadedFile('text/srt/three-cues.srt'),
            'mode'            => 'simple',
        ]);

        $this->assertSuccessfulFileJobRedirect($response, $fileGroup);

        $this->assertMatchesStoredFileSnapshot(3);
    }

    /** @test */
    function it_can_simple_merge_subtitles_into_ssa_files()
    {
        [$response, $fileGroup] = $this->postMergeJob([
            'subtitles'       => $this->createUploadedFile('text/ssa/three-cues.ssa'),
            'second-subtitle' => $this->createUploadedFile('text/srt/three-cues.srt'),
            'mode'            => 'simple',
        ]);

        $this->assertSuccessfulFileJobRedirect($response, $fileGroup);

        $this->assertMatchesStoredFileSnapshot(3);
    }

    /** @test */
    function it_can_simple_merge_subtitles_into_vtt_files()
    {
        [$response, $fileGroup] = $this->postMergeJob([
            'subtitles'       => $this->createUploadedFile('text/vtt/three-cues.vtt'),
            'second-subtitle' => $this->createUploadedFile('text/srt/three-cues.srt'),
            'mode'            => 'simple',
        ]);

        $this->assertSuccessfulFileJobRedirect($response, $fileGroup);

        $this->assertMatchesStoredFileSnapshot(3);
    }

    /** @test */
    function it_can_nearest_cue_merge_subtitles_into_ass_files()
    {
        [$response, $fileGroup] = $this->postMergeJob([
            'subtitles'             => $this->createUploadedFile('text/ass/merge-01.ass'),
            'second-subtitle'       => $this->createUploadedFile('text/srt/merge-01.srt'),
            'nearest_cue_threshold' => 3000,
            'mode'                  => 'nearestCueThreshold',
        ]);

        $this->assertSuccessfulFileJobRedirect($response, $fileGroup);

        $this->assertMatchesStoredFileSnapshot(3);
    }

    /** @test */
    function it_can_top_bottom_merge_subtitles_into_ass_files()
    {
        [$response, $fileGroup] = $this->postMergeJob([
            'subtitles'       => $this->createUploadedFile('text/ass/three-cues.ass'),
            'second-subtitle' => $this->createUploadedFile('text/srt/three-cues.srt'),
            'mode'            => 'topBottom',
        ]);

        $this->assertSuccessfulFileJobRedirect($response, $fileGroup);

        $this->assertMatchesStoredFileSnapshot(3);
    }

    /** @test */
    function it_can_top_bottom_merge_subtitles_into_srt_files()
    {
        [$response, $fileGroup] = $this->postMergeJob([
            'subtitles'       => $this->createUploadedFile('text/srt/three-cues.srt'),
            'second-subtitle' => $this->createUploadedFile('text/ass/three-cues.ass'),
            'mode'            => 'topBottom',
        ]);

        $this->assertSuccessfulFileJobRedirect($response, $fileGroup);

        $this->assertMatchesStoredFileSnapshot(3);
    }
}
