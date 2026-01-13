<?php
// Helper Functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: " . BASE_PATH . $url);
    exit();
}

function flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function display_flash() {
    $flash = get_flash_message();
    if ($flash) {
        $bgColor = $flash['type'] === 'success' ? 'bg-green-500' : 'bg-red-500';
        echo "<div class='$bgColor text-white px-6 py-4 rounded-lg mb-4'>{$flash['message']}</div>";
    }
}
?>
