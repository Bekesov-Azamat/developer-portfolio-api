<?php

namespace App\Contracts\Ai;

use App\Data\Ai\AiAnalysisResult;

interface AiAnalyzer
{
    public function analyze(string $comment): AiAnalysisResult;
}
