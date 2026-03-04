<?php
namespace App\Core;

class FeedbackEngine
{
    private array $informal = ['kids','stuff','wanna','gonna','cool','a lot of'];
    private array $collocationWarnings = ['do a decision' => 'make a decision', 'strong rain' => 'heavy rain'];

    public function analyze(string $content, array $task): array
    {
        $annotations = [];
        $errorCounts = [];
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($content)) ?: [];
        $words = preg_split('/\s+/', strtolower(strip_tags($content))) ?: [];
        $wordCount = count(array_filter($words));
        $freq = array_count_values(array_filter($words, fn($w)=>strlen($w)>3));

        foreach ($freq as $w => $c) {
            if ($c >= 5) {
                $annotations[] = ['type'=>'LEX_REPETITION','message'=>"Repeated word '$w' ($c times)",'suggestion'=>'Use synonyms'];
                $errorCounts['LEX_REPETITION'] = ($errorCounts['LEX_REPETITION'] ?? 0)+1;
            }
        }

        foreach ($this->informal as $token) {
            if (str_contains(strtolower($content), $token)) {
                $annotations[]=['type'=>'LEX_INFORMAL','message'=>"Informal expression '$token'",'suggestion'=>'Use formal alternative'];
                $errorCounts['LEX_INFORMAL']=($errorCounts['LEX_INFORMAL']??0)+1;
            }
        }

        foreach ($this->collocationWarnings as $wrong => $right) {
            if (str_contains(strtolower($content), $wrong)) {
                $annotations[]=['type'=>'LEX_WRONG_COLLOCATION','message'=>"Collocation warning: $wrong",'suggestion'=>$right];
                $errorCounts['LEX_WRONG_COLLOCATION']=($errorCounts['LEX_WRONG_COLLOCATION']??0)+1;
            }
        }

        foreach ($sentences as $s) {
            $trim = trim($s);
            if (str_word_count($trim) > 35) {
                $annotations[]=['type'=>'COH_PARAGRAPHING','message'=>'Very long sentence may hurt clarity','suggestion'=>'Split into shorter sentences'];
                $errorCounts['COH_PARAGRAPHING']=($errorCounts['COH_PARAGRAPHING']??0)+1;
            }
            if (!preg_match('/\b(is|are|was|were|have|has|do|does|did|can|will|should|must|go|make|take)\b/i', $trim)) {
                $annotations[]=['type'=>'GRA_FRAGMENT','message'=>'Possible sentence fragment','suggestion'=>'Ensure a main verb exists'];
                $errorCounts['GRA_FRAGMENT']=($errorCounts['GRA_FRAGMENT']??0)+1;
            }
            if (preg_match('/\b(he|she|it)\s+(go|do|have)\b/i', $trim)) {
                $annotations[]=['type'=>'GRA_SVA','message'=>'Possible subject-verb agreement issue','suggestion'=>'Check third-person singular verbs'];
                $errorCounts['GRA_SVA']=($errorCounts['GRA_SVA']??0)+1;
            }
        }

        if ($wordCount < (int)$task['min_words'] || $wordCount > (int)$task['max_words']) {
            $annotations[]=['type'=>'TASK_UNDERDEVELOPED','message'=>'Word count outside target range','suggestion'=>'Adjust length to prompt requirement'];
            $errorCounts['TASK_UNDERDEVELOPED']=1;
        }

        $links = preg_match_all('/\b(however|therefore|moreover|furthermore|in addition|on the other hand)\b/i', $content);
        if ($links < 2) {
            $annotations[]=['type'=>'COH_POOR_TRANSITION','message'=>'Few cohesion markers found','suggestion'=>'Add transitions between ideas'];
            $errorCounts['COH_POOR_TRANSITION']=1;
        }

        $keywords = array_unique(array_filter(preg_split('/\W+/', strtolower($task['prompt'])) ?: [], fn($x)=>strlen($x)>5));
        $covered=0;
        foreach (array_slice($keywords,0,8) as $k) {
            if (str_contains(strtolower($content), $k)) $covered++;
        }
        if ($covered < 2) {
            $annotations[]=['type'=>'TASK_OFF_TOPIC','message'=>'Low prompt keyword coverage','suggestion'=>'Address key points from prompt'];
            $errorCounts['TASK_OFF_TOPIC']=1;
        }

        $ttr = count(array_unique($words))/max(count($words),1);
        $summary = [
            'task' => ['score_suggested'=>round(max(4,min(8,5+$covered/2)),1),'rationale'=>'Based on prompt coverage and development'],
            'coh' => ['score_suggested'=>round(max(4,min(8,5+$links/2)),1),'rationale'=>'Based on transitions and sentence lengths'],
            'lex' => ['score_suggested'=>round(max(4,min(8,5+$ttr*2)),1),'rationale'=>'Based on lexical variety and informal use'],
            'gra' => ['score_suggested'=>round(max(4,min(8,6-(($errorCounts['GRA_FRAGMENT']??0)+($errorCounts['GRA_SVA']??0))/2)),1),'rationale'=>'Based on grammar heuristics'],
            'top_errors' => array_slice(array_keys($errorCounts), 0, 7)
        ];

        return [$annotations, $summary, $errorCounts];
    }
}
