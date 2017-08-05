<?php

namespace App\Utils;

class IdxFile
{
    private $indexLanguageArray = [];

    public function __construct($idxFilePath)
    {
        $idxLines = app(\App\Utils\TextFileReader::class)->getLines($idxFilePath);

        foreach($idxLines as $line) {
            if(preg_match('/^id: (?<lang>[a-z]+), index: (?<id>\d+)$/', $line, $match)) {
                $this->indexLanguageArray[$match['id']] = $match['lang'];
            }
        }
    }

    public function getLanguageForIndex($index)
    {
        return $this->indexLanguageArray[$index] ?? "unknown";
    }

}
