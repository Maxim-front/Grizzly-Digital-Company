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
    <!-- Include Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>

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
            bottom: 0;
            right: 0;
            max-width: 80%;
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            z-index: 9999;
        }

        .my-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 0.75rem;
            box-sizing: content-box;
        }

        .benefits-title {
            font-size: 40px;
            font-weight: 700;
            line-height: 130%;
            margin-bottom: 48px;
        }

        .cards-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;

        }

        .my-card {
            width: 295px;
            height: 289px;
            flex-shrink: 0;
            border-radius: 4px;
            background: #F9F9F9;
            padding: 32px 24px;
        }

        .my-card-title {
            margin-bottom: 16px;
            font-weight: 700;
            font-size: 20px;
            line-height: 150%; /* 30px */
        }

        .my-card-text {
            font-size: 16px;
            font-weight: 400;
            line-height: 130%; /* 20.8px */
        }

        .swiper-container {
            width: 100%;
            overflow-y: visible;
            overflow-x: clip;
        }

        .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .swiper-pagination-bullet {
            background-color: #78599C;
        }

        .swiper-container .my-card {
            height: 202px;
        }

        .my-card-img {
            width: 60px;
            height: 60px;
            margin-bottom: 32px;
        }

        .my-card img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mobile-wrapper {
            display: none;
            position: relative;
        }

        .swiper-pagination {
            bottom: -24px !important;
        }

        @media screen and (max-width: 991px) {
            .desktop-wrapper {
                display: none;
            }

            .my-card-img {
                width: 40px;
                height: 40px;
                margin-bottom: 24px;
            }

            .my-card-text {
                font-size: 14px;
            }

            .my-card-title {
                margin-bottom: 8px;
                font-size: 16px;
            }

            .mobile-wrapper {
                display: block;
            }

            .benefits-title {
                font-size: 24px;
                margin-bottom: 24px;
            }
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

<div class="my-container">
    <p class="task-comment">////////////////////// Task 3</p>
    <h2 class="benefits-title">Korzyści ze współpracy z nami</h2>
    <div class="cards-wrapper desktop-wrapper">
        <div class="my-card col-lg-3">
            <div class="my-card-img">
                <img src="img/oversized-goods-storage.svg" alt="">
            </div>
            <h5 class="my-card-title">Przechowywanie towarów ponadgabarytowych</h5>
            <p class="my-card-text">Opis</p>
        </div>
        <div class="my-card col-lg-3">
            <div class="my-card-img">
                <img src="./img/flexible-cooperation-conditions.svg" alt="">
            </div>
            <h5 class="my-card-title">Elastyczne warunki współpracy</h5>
            <p class="my-card-text">Opis</p>
        </div>
        <div class="my-card col-lg-3">
            <div class="my-card-img">
                <img src="./img/order-integration-management.svg" alt="">
            </div>
            <h5 class="my-card-title">Integracja i zarządzanie zamówieniami</h5>
            <p class="my-card-text">Opis</p>
        </div>
        <div class="my-card col-lg-3">
            <div class="my-card-img">
                <img src="./img/order-shipping-day.svg" alt="">
            </div>
            <h5 class="my-card-title">Wysyłka zamówień
                w dniu kompletacji</h5>
            <p class="my-card-text">Opis</p>
        </div>
        <div class="my-card col-lg-3">
            <div class="my-card-img">
                <img src="./img/low-shipping-costs.svg" alt="">
            </div>
            <h5 class="my-card-title">Niskie koszty dostawy</h5>
            <p class="my-card-text">Opis</p>
        </div>
        <div class="my-card col-lg-3">
            <div class="my-card-img">
                <img src="./img/goods-security-guarantee.svg" alt="">
            </div>
            <h5 class="my-card-title">Gwarancja bezpieczeństwa towarów</h5>
            <p class="my-card-text">Opis</p>
        </div>
        <div class="my-card col-lg-3">
            <div class="my-card-img">
                <img src="./img/international-order-shipping.svg" alt="">
            </div>
            <h5 class="my-card-title">Całodobowy wideo monitoring</h5>
            <p class="my-card-text">Opis</p>
        </div>
        <div class="my-card col-lg-3">
            <div class="my-card-img">
                <img src="./img/international-order-shipping.svg" alt="">
            </div>
            <h5 class="my-card-title">Wysyłka zamówień
                do różnych krajów</h5>
            <p class="my-card-text">Opis</p>
        </div>
    </div>
    <div class="swiper-container mobile-wrapper" data-pagination="true" data-pagination-dynamic-bullets="true">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="my-card">
                    <div class="my-card-img">
                        <img src="img/oversized-goods-storage.svg" alt="">
                    </div>
                    <h5 class="my-card-title">Przechowywanie towarów ponadgabarytowych</h5>
                    <p class="my-card-text">Opis</p>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="my-card">
                    <div class="my-card-img">
                        <img src="./img/flexible-cooperation-conditions.svg" alt="">
                    </div>
                    <h5 class="my-card-title">Elastyczne warunki współpracy</h5>
                    <p class="my-card-text">Opis</p>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="my-card">
                    <div class="my-card-img">
                        <img src="./img/order-integration-management.svg" alt="">
                    </div>
                    <h5 class="my-card-title">Integracja i zarządzanie zamówieniami</h5>
                    <p class="my-card-text">Opis</p>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="my-card">
                    <div class="my-card-img">
                        <img src="./img/order-shipping-day.svg" alt="">
                    </div>
                    <h5 class="my-card-title">Wysyłka zamówień
                        w dniu kompletacji</h5>
                    <p class="my-card-text">Opis</p>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="my-card">
                    <div class="my-card-img">
                        <img src="./img/low-shipping-costs.svg" alt="">
                    </div>
                    <h5 class="my-card-title">Niskie koszty dostawy</h5>
                    <p class="my-card-text">Opis</p>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="my-card">
                    <div class="my-card-img">
                        <img src="./img/goods-security-guarantee.svg" alt="">
                    </div>
                    <h5 class="my-card-title">Gwarancja bezpieczeństwa towarów</h5>
                    <p class="my-card-text">Opis</p>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="my-card">
                    <div class="my-card-img">
                        <img src="./img/video-monitoring.svg" alt="">
                    </div>
                    <h5 class="my-card-title">Całodobowy wideo monitoring</h5>
                    <p class="my-card-text">Opis</p>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="my-card">
                    <div class="my-card-img">
                        <img src="./img/international-order-shipping.svg" alt="">
                    </div>
                    <h5 class="my-card-title">Wysyłka zamówień
                        do różnych krajów</h5>
                    <p class="my-card-text">Opis</p>
                </div>
            </div>
        </div>
        <div class="swiper-pagination">
            <span class="swiper-pagination-bullet"></span>
            <span class="swiper-pagination-bullet"></span>
            <span class="swiper-pagination-bullet"></span>
            <!-- Add more bullets as needed -->
        </div>
    </div>
</div>

<!-- Include Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous">
</script>
<!-- Include Swiper JS -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script type="module">
    $(document).ready(() => {
        // Initialize Swiper
        const swiper = new Swiper('.swiper-container', {
            slidesPerView: 1,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            },
        });

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
            return getCookie('popup_shown_date') === new Date().toDateString();
        }

        // Show the popup if it has not been shown today
        if (!isPopupShownToday()) {
            showCookiePopup();
        }

        // Event handler for the "Accept" button
        $('#accept-cookie').on('click', function () {
            setCookie('popup_shown_date', new Date().toDateString(), 1);
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