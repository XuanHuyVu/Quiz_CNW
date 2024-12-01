<?php
try {
    // Kết nối CSDL bằng PDO
    $conn = new PDO("mysql:host=localhost;dbname=quiz;charset=utf8mb4", 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $filePath = 'Quiz.txt';
    if (!file_exists($filePath)) {
        die("File not found: $filePath");
    }

    $content = file_get_contents($filePath);
    $questions = preg_split('/\r?\n\r?\n/', $content); // Tách bằng ký tự newline // Phân tách các câu hỏi theo khoảng trống
    var_dump($questions);  // Xem dữ liệu câu hỏi

    foreach ($questions as $q) {
        // Tách các dòng trong từng câu hỏi
        $lines = explode("\n", $q);

        $question = trim($lines[0]); // Lấy câu hỏi
        $options = []; // Tùy chọn
        $answer = '';

        foreach ($lines as $line) {
            if (strpos($line, 'ANSWER:') === 0) {
                $answer = trim(substr($line, 7)); // Lấy đáp án
            } elseif (preg_match('/^[A-E]\./', $line)) {
                $options[] = trim($line); // Lấy các tùy chọn
            }
        }

        // Chuyển tùy chọn sang dạng JSON
        $optionsJSON = json_encode($options, JSON_UNESCAPED_UNICODE);
        var_dump($optionsJSON);

        // Chèn dữ liệu vào bảng MySQL
        try {
            $stmt = $conn->prepare("INSERT INTO quizs (question, options, answer) VALUES (:question, :options, :answer)");
            $stmt->bindParam(':question', $question);
            $stmt->bindParam(':options', $optionsJSON);
            $stmt->bindParam(':answer', $answer);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Lỗi khi chèn câu hỏi: " . $e->getMessage();
        }
    }
    echo "Dữ liệu đã được lưu vào CSDL thành công!";
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>