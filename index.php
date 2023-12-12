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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous">
</script>

<!-- Include Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>