<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>

    <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">
    
    <!-- css -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/contact.css">

    <!-- icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@hackernoon/pixel-icon-library/fonts/iconfont.css">

    <!-- fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">
   
    <!-- js -->
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    
    <?php include 'includes/navbar.php'; ?>

    <canvas id="grid"></canvas>

    <section style="height: 20px;"></section>



    <section id="contact">

        <div class="container">
            <h1 class="title">Contact</h1>
            <p class="subtitle">Have questions? We're here to help.</p>
        </div>



        <div class="container grid-2">

            <form method="POST" class="contact-form">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" class="pixel-input" name="name" required placeholder="ex. John Doe">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="pixel-input" name="email" required placeholder="you@example.com">
                </div>

                <div class="form-group">
                    <label for="reason">Request Type</label>
                    <select id="reason" class="pixel-input pixel-select" name="reason" required>
                    <option value="" selected disabled>Choose one</option>
                    <option value="general">General Question</option>
                    <option value="order">Order Question</option>
                    <option value="service">Service / Repair Request</option>
                    </select>
                </div>

                <div class="service-box" id="serviceBox" aria-live="polite">
                    <div class="service-head">
                    <i class="hn hn-tools-solid" aria-hidden="true"></i>
                    <span>Service Details (optional)</span>
                    </div>

                    <div class="form-group">
                    <label for="device">Device / Console</label>
                    <input type="text" id="device" class="pixel-input" name="device" placeholder="e.g. Nintendo DS / PS2 / Game Boy">
                    </div>

                    <div class="form-group">
                    <label for="issue">Issue Type</label>
                    <select id="issue" class="pixel-input pixel-select" name="issue">
                        <option value="" selected disabled>Select issue</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="battery">Battery replacement</option>
                        <option value="power">Won't power on</option>
                        <option value="read">Disc / cartridge read issue</option>
                        <option value="buttons">Buttons / controller issue</option>
                        <option value="other">Other</option>
                    </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" class="pixel-input" rows="5" style="resize: none;" required
                    placeholder="Write your message (for service: include model, symptoms, and any tests you tried)"></textarea>
                </div>

                <button type="submit" class="pixel-button btn-primary">Submit</button>
                </form>


            
            <div class="contact-info">
                <h2 class="title">RetroByte HQ</h2>

                <p><strong>Location:</strong> Athens, GR</p>
                <p><strong>Phone:</strong> +30 210 1234567</p>
                <p><strong>Working Hours:</strong> Mon – Fri: 09:00 – 17:00</p>

                <div class="social-links">
                    <a href="https://instagram.com" target="_blank"><i class="hn hn-instagram"></i> Instagram</a>
                    <a href="https://tiktok.com" target="_blank"><i class="hn hn-tiktok"></i> TikTok</a>
                    <a href="https://facebook.com" target="_blank"><i class="hn hn-facebook-round"></i>Facebook</a>
                    <a href="https://twitter.com" target="_blank"><i class="hn hn-twitter"></i> Twitter</a>
                </div>
            </div>

        </div>

    </section>    

    <section style="height: 10px"></section>

    <?php include 'includes/footer.php'; ?>


    <script>
    (function () {
        const reason = document.getElementById("reason");
        const box = document.getElementById("serviceBox");

        if (!reason || !box) return;

        function toggle() {
        const isService = reason.value === "service";
        box.style.display = isService ? "block" : "none";
        }

        toggle();
        reason.addEventListener("change", toggle);
    })();
    </script>


</body>
</html>