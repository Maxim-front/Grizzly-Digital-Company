<?php
declare(strict_types=1);

$phoneCodesData = json_decode(file_get_contents("https://cdn.jsdelivr.net/gh/andr-04/inputmask-multi@master/data/phone-codes.json"));

function extractMaskSymbols(string $mask): array
{
    $mask = sanitizePhoneNumber($mask);
    $symbols = str_split($mask);
    $result = [];

    foreach ($symbols as $index => $symbol) {
        if ($symbol !== '#' && $symbol !== '-') {
            $result[$index] = $symbol;
        }
    }

    return $result;
}

function areStringSizesEqual(string $mask, string $phone): bool
{
    $mask = sanitizePhoneNumber($mask);
    return strlen($phone) === strlen($mask);
}

// Удаляем пробелы, знаки "+" и тире "-"
function sanitizePhoneNumber(string $phoneNumber): array|string|null
{
    return preg_replace('/[\s\+\-]/', '', $phoneNumber);
}

function determineCountry(string $phoneNumber): string
{
    global $phoneCodesData;
    $newPhoneNumber = sanitizePhoneNumber($phoneNumber);
    $phoneSymbols = str_split($newPhoneNumber);

    foreach ($phoneCodesData as $phoneInfo) {
        if (!areStringSizesEqual($phoneInfo->mask, $newPhoneNumber)) {
            continue;
        }
        $mask = extractMaskSymbols($phoneInfo->mask);
        $isMaskedCodeMatch = true;
        foreach ($mask as $position => $symbol) {
            if (!isset($phoneSymbols[$position]) || $phoneSymbols[$position] !== $symbol) {
                $isMaskedCodeMatch = false;
            }
        }
        if ($isMaskedCodeMatch) {
            return $phoneInfo->name_ru;
        }
    }
    return 'Not Found!';
}

// Check if the form has been submitted
$resultMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone_number'])) {
    $inputPhoneNumber = $_POST['phone_number'];
    $country = determineCountry($inputPhoneNumber);
    $resultMessage = "Number $inputPhoneNumber belongs to the country: $country";

    // Clear the global $_POST array
    unset($_POST['phone_number']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Page</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            color: #2E2E2E;
            font-family: Nunito;
        }

        #phoneForm button {
            margin: 15px 0;
            background-color: #78599C;
            color: white;
            display: block;
        }

        .task-comment {
            color: gray;
            font-weight: bold;
            border-bottom: 2px solid red;
            text-align: center;
        }

        .first-task button {
            padding: 0 10px;
            border-radius: 10px;
        }

        #cookie-popup {
            display: none;
            position: fixed;
            bottom: 0px;
            right: 0px;
            max-width: 80%;
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }

        .my-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 0.75rem;
            box-sizing: content-box;
        }
    </style>
</head>

<body>

<div class="first-task my-container">
    <p class="task-comment">////////////////////// Task 1</p>

    <!-- HTML form with input field for phone number -->
    <form method="post" action="" id="phoneForm">
        <label for="phone_number">Введите номер телефона:</label>
        <input type="text" name="phone_number" id="phone_number" required>
        <button type="submit">Проверить страну</button>
    </form>
    <?php
    // Display the result message
    if (!empty($resultMessage)) {
        echo '<p>' . htmlspecialchars($resultMessage) . '</p>';
    }
    ?>
</div>

<!-- Cookie Notification Popup -->
<div id="cookie-popup">
    <p>This website uses cookies. By using this site, you agree to our use of cookies.</p>
    <button id="accept-cookie" class="btn btn-success">Accept</button>
    <button id="close-popup" class="btn btn-danger">Close</button>
</div>

<!-- Include Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous">
</script>

<script type="module">
    $(document).ready(() => {
        // Function to set a cookie
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        }

        // Function to retrieve the value of a cookie
        function getCookie(name) {
            const nameEQ = name + "=";
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                let cookie = cookies[i];
                while (cookie.charAt(0) === ' ') cookie = cookie.substring(1, cookie.length);
                if (cookie.indexOf(nameEQ) === 0) return cookie.substring(nameEQ.length, cookie.length);
            }
            return null;
        }

        // Function to display the popup
        function showCookiePopup() {
            $('#cookie-popup').show();
        }

        // Function to hide the popup
        function hideCookiePopup() {
            $('#cookie-popup').hide();
        }

        // Function to check if the popup has already been shown today
        function isPopupShownToday() {
            return getCookie('popup_shown_date') === date('Y-m-d');
        }

        // Show the popup if it has not been shown today
        if (!isPopupShownToday()) {
            showCookiePopup();
        }

        // Event handler for the "Accept" button
        $('#accept-cookie').on('click', function () {
            setCookie('popup_shown_date', date('Y-m-d'), 1);
            hideCookiePopup();
        });

        // Event handler for the "Close" button
        $('#close-popup').on('click', function () {
            hideCookiePopup();
        });
    });
</script>

</body>

</html>