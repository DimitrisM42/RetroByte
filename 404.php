
<?php
http_response_code(404);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404</title>

    <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/404.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@hackernoon/pixel-icon-library/fonts/iconfont.css">
    <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">
    <script src="assets/js/app.js" defer></script>


</head>
<body>

    <canvas id="grid"></canvas>
    
    <div class="container">
        <h1>404</h1>
        <p>Page not found</p>
        <a href="index.php" class="pixel-button">Go Home</a>
        <!-- <button type="button" onclick="toggleTheme()" class="pixel-button">Toggle theme</button> -->

    </div>
    

    <script>
        function toggleTheme() {
            const r = document.documentElement;
            r.dataset.theme = r.dataset.theme === 'dark' ? '' : 'dark';
            
            if (r.dataset.theme) localStorage.setItem('theme','dark'); 
            else localStorage.removeItem('theme');
        }
        
        (function () {
            if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    
</body>
</html>