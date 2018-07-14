<?php

namespace Tests\Unit\Jobs;

use App\Jobs\FileJobs\ConvertToSrtJob;
use App\Jobs\ZipFileGroupJob;
use App\Models\FileGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesFileGroups;
use Tests\TestCase;

class ZipFileGroupJobTest extends TestCase
{
    use RefreshDatabase, CreatesFileGroups;

    /** @test */
    function it_zips_files_from_a_file_group()
    {
        $fileGroup = $this->createFileGroup();

        dispatch(
            new ConvertToSrtJob($fileGroup, $this->testFilesStoragePath.'text/ass/three-cues.ass')
        );

        $fileGroup = FileGroup::findOrFail(1);

        $this->assertNotNull($fileGroup->file_jobs_finished_at);

        dispatch(
            new ZipFileGroupJob($fileGroup)
        );

        $fileGroup = FileGroup::findOrFail(1);

        $this->assertNotNull($fileGroup->archive_finished_at);
        $this->assertNotNull($fileGroup->archive_stored_file_id);
    }
}