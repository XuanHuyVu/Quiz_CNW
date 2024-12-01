<?php
session_start();

// Đọc câu hỏi từ tệp tin Quiz.txt
function loadQuestionsFromTXT($filename) {
    if (!file_exists($filename)) {
        die('File không tồn tại!');
    }

    $questions = [];
    $content = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $currentQuestion = [];
    foreach ($content as $line) {
        $line = trim($line);
        if (preg_match('/^ANSWER:\s*([\w, ]+)$/i', $line, $matches)) {
            if (!isset($currentQuestion['question'], $currentQuestion['options'])) {
                continue; // Bỏ qua câu hỏi không hợp lệ
            }
            $currentQuestion['answer'] = array_map('trim', explode(',', strtoupper($matches[1])));
            $questions[] = $currentQuestion;
            $currentQuestion = [];
        } elseif (preg_match('/^[ABCD]\.\s*(.+)$/', $line, $matches)) {
            $currentQuestion['options'][] = $matches[1];
        } elseif (!empty($line)) {
            $currentQuestion['question'] = $line;
        }
    }

    return $questions;
}

// Trả về câu hỏi theo chỉ số
function getQuestion($index, $questions) {
    return $questions[$index] ?? null;
}

// Trả về trạng thái câu hỏi (Done/Not Done)
function getQuestionStatus($questions) {
    $userAnswers = $_SESSION['userAnswers'] ?? [];
    return array_map(function ($index) use ($userAnswers) {
        return isset($userAnswers[$index]) && !empty($userAnswers[$index]) ? 'Đã Làm' : 'Chưa làm';
    }, array_keys($questions));
}

// Đường dẫn tệp câu hỏi
$questions = loadQuestionsFromTXT('Quiz.txt');

// Xử lý lưu câu trả lời
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $currentQuestion = $_POST['currentQuestion'] ?? 0;
    $_SESSION['userAnswers'][$currentQuestion] = $_POST['answers'] ?? [];

    if ($action === 'next') {
        $nextQuestion = $currentQuestion + 1;
        header('Location: quiz_user.php?q=' . $nextQuestion);
    } elseif ($action === 'prev') {
        $prevQuestion = max(0, $currentQuestion - 1);
        header('Location: quiz_user.php?q=' . $prevQuestion);
    } elseif ($action === 'finish') {
        header('Location: quiz_user.php?result=1');
    } elseif ($action === 'reset') {
        session_destroy();
        session_start();
        header('Location: quiz_user.php?q=0');
    }
    exit;
}
?>
