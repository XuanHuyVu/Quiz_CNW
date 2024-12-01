<?php
require 'quiz_controller.php';

$currentQuestion = $_GET['q'] ?? 0;
$resultView = isset($_GET['result']);
$questions = loadQuestionsFromTXT('Quiz.txt');
$questionStatus = getQuestionStatus($questions);

if ($resultView) {
    $userAnswers = $_SESSION['userAnswers'] ?? [];
    $correctAnswers = 0;

    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Kết quả</title>
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <h2 class="mb-4">Kết quả</h2>';

    foreach ($questions as $index => $q) {
        $userAnswer = $userAnswers[$index] ?? [];
        sort($userAnswer);
        sort($q['answer']);
        $isCorrect = $userAnswer === $q['answer'];
        $correctAnswers += $isCorrect ? 1 : 0;

        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        echo "<h5 class='card-title'>Câu " . ($index + 1) . ": {$q['question']}</h5>";
        echo "<p>Đáp án của bạn: <strong>" . (empty($userAnswer) ? "Chưa trả lời" : implode(', ', $userAnswer)) . "</strong></p>";
        echo "<p>Đáp án đúng: <strong>" . implode(', ', $q['answer']) . "</strong></p>";
        echo '<p>' . ($isCorrect ? '<span class="text-success">Đúng</span>' : '<span class="text-danger">Sai</span>') . '</p>';
        echo '</div>';
        echo '</div>';
    }

    echo "<p><strong>Số câu đúng: $correctAnswers/" . count($questions) . "</strong></p>";

    echo '<form method="POST" action="quiz_controller.php">';
    echo '<button type="submit" name="action" value="reset" class="btn btn-primary">Làm lại bài thi</button>';
    echo '</form>';

    echo '</div>
    </body>
    </html>';
    exit;
}

$current = getQuestion($currentQuestion, $questions);

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Câu hỏi</title>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <form method="POST" action="quiz_controller.php">
            <input type="hidden" name="currentQuestion" value="' . $currentQuestion . '">
            <div class="mb-4">
                <h5 class="mb-3">Câu ' . ($currentQuestion + 1) . ': ' . $current['question'] . '</h5>';

foreach ($current['options'] as $index => $option) {
    $optionValue = chr(65 + $index);
    $checked = isset($_SESSION['userAnswers'][$currentQuestion]) && in_array($optionValue, $_SESSION['userAnswers'][$currentQuestion]) ? 'checked' : '';
    echo '<div class="form-check">
            <input 
                class="form-check-input" 
                type="checkbox" 
                name="answers[]" 
                value="' . $optionValue . '" 
                id="option' . $index . '" ' . $checked . '>
            <label class="form-check-label" for="option' . $index . '">
                ' . $optionValue . '. ' . $option . '
            </label>
        </div>';
}

echo '      </div>
            <div class="d-flex justify-content-between">
                <button type="submit" name="action" value="prev" class="btn btn-secondary" ' . ($currentQuestion == 0 ? 'disabled' : '') . '>Câu trước</button>
                <button type="submit" name="action" value="' . ($currentQuestion == count($questions) - 1 ? 'finish' : 'next') . '" class="btn btn-primary">' . ($currentQuestion == count($questions) - 1 ? 'Hoàn thành' : 'Câu tiếp') . '</button>
            </div>
        </form>';

echo '<div class="mt-5">';
echo '<h5>Trạng thái Quiz:</h5>';
foreach ($questionStatus as $index => $status) {
    $link = ($status === 'Chưa làm') ? 'class="btn btn-secondary m-1"' : 'class="btn btn-primary m-1"';
    echo '<a href="quiz_user.php?q=' . $index . '" ' . $link . '>' . ($index + 1) . '</a>';
}
echo '</div>';

echo '</div>
</body>
</html>';
