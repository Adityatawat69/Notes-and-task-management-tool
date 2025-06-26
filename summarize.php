<?php
// Disable output buffering
ob_end_clean();

// Set headers
header('Content-Type: application/json');

// Error handling
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'summarize_error.log');
}

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and decode JSON input
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (!isset($data['content']) || empty($data['content'])) {
        throw new Exception('No content provided');
    }

    function summarizeText($text, $sentenceCount = 3) {
        // Remove special characters and extra whitespace
        $text = preg_replace('/[^\p{L}\p{N}\s\.\!\?]/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Split into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        if (count($sentences) <= $sentenceCount) {
            return $text;
        }
        
        // Simple scoring based on sentence length and position
        $scores = [];
        foreach ($sentences as $i => $sentence) {
            $score = 0;
            $words = str_word_count($sentence);
            
            // Favor sentences between 10-20 words
            if ($words >= 10 && $words <= 20) {
                $score += 2;
            }
            
            // Favor early sentences
            if ($i < 3) {
                $score += (3 - $i);
            }
            
            $scores[$i] = $score;
        }
        
        // Sort sentences by score and get top ones
        arsort($scores);
        $topSentences = array_slice($scores, 0, $sentenceCount, true);
        ksort($topSentences);
        
        // Reconstruct summary
        $summary = '';
        foreach ($topSentences as $i => $score) {
            $summary .= $sentences[$i] . ' ';
        }
        
        return trim($summary);
    }

    // Get the content and summarize it
    $content = $data['content'];
    $summary = summarizeText($content);

    if (empty($summary)) {
        throw new Exception('Failed to generate summary');
    }

    // Return the summary
    echo json_encode(['summary' => $summary]);

} catch (Exception $e) {
    logError('Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}