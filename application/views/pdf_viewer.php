
<?php if (isset($images) && !empty($images)): ?>
    <div class="image-slider">
        <div class="slider">
            <?php foreach ($images as $image): ?>
                <div>
                    <img src="<?= base_url('uploads/digit_pdf/' . $product_id . '/' . basename($image)) ?>" alt="Magazine Page">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add slick slider CSS & JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    <style>
        .slick-slide img {
            width:100%;
        }
        .image-slider {
            padding: 0 60px;
        }
        .slick-prev:before, .slick-next:before {
            color:black;
        }
    </style>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            $('.slider').slick({
                slidesToShow: 1,         // Number of slides to show
                slidesToScroll: 1,       // Number of slides to scroll at a time
                arrows: true,            // Show Next and Previous arrows
                autoplay: false,         // Disable autoplay
                responsive: [            // Responsive settings
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 345,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        });

        // Reinitialize slider when tabs are toggled
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $('.slider').slick('setPosition');
        });

        document.addEventListener('keydown', function (e) {

            if (e.key === "PrintScreen") {
                navigator.clipboard.writeText(""); // Clear clipboard to prevent screenshots
                e.preventDefault();
                Optional: alert("Screenshots are disabled to protect content.");
            }
            if (e.key === "PrintScreen" && e.metaKey) {
                e.preventDefault();  // Prevent the default action of the PrintScreen key
                alert("Screenshots are disabled.");
            }
                    // Disable F12
            if (e.key === "F12") {
                e.preventDefault();
                // alert("Inspect is disabled.");
            }

            // Disable Ctrl+Shift+I (Inspect)
            if (e.ctrlKey && e.shiftKey && e.key === "I") {
                e.preventDefault();
                // alert("Inspect is disabled.");
            }

            // Disable Ctrl+Shift+J (Console)
            if (e.ctrlKey && e.shiftKey && e.key === "J") {
                e.preventDefault();
                // alert("Inspect is disabled.");
            }

            // Disable Ctrl+U (View Source)
            if (e.ctrlKey && e.key === "u") {
                e.preventDefault();
                // alert("View source is disabled.");
            }

            // Disable Ctrl+S (Save Page)
            if (e.ctrlKey && e.key === "s") {
                e.preventDefault();
                // alert("Saving the page is disabled.");
            }

            if (e.ctrlKey && e.key === "p") {
                e.preventDefault();
                // alert("Saving the page is disabled.");
            }
            if (e.key === "s" && e.metaKey && e.shiftKey) {
                e.preventDefault();
                alert("Snipping tool is disabled to protect content.");
            }
            });

            // Disable Right-Click
            document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            // alert("Right-click is disabled.");
            });

            // Disable drag selection
            document.addEventListener('selectstart', function (e) {
            e.preventDefault();
            });

            // Block Print Screen
            document.addEventListener("keydown", function (e) {
            if (e.key === "PrintScreen") {
                navigator.clipboard.writeText(""); // Clear clipboard
                e.preventDefault();
                // alert("Screenshots are disabled.");
            }
            });

            // Check for Developer Tools open
            setInterval(function () {
            const startTime = new Date();
            debugger; // Triggers dev tools
            if (new Date() - startTime > 100) {
                // alert("Developer tools are disabled.");
                window.close(); // Close the window if dev tools are detected
            }
        }, 1000);

    </script>
<?php else: ?>
    <p>No images found for this product.</p>
<?php endif; ?>
