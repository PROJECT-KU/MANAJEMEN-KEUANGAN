<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Image on Page Load</title>
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css"> <!-- Include Font Awesome for icons if needed -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;

            /* Prevent horizontal scroll */
        }

        .image-container {
            display: flex;
            justify-content: center;
            /* Center the image horizontally */
            align-items: center;
            /* Center the image vertically if the container has a set height */
            padding: 20px;
            /* Optional: add some padding around the image */
        }

        .image-container img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <div id="imageContainer" class="image-container text-center mt-5">
        <!-- Gambar -->
        <img
            src="{{ asset('assets/img/WebsiteMaintenance.png') }}"
            alt="Website Maintenance"
            class="img-fluid">
    </div>

    <script>
        // SweetAlert triggered automatically on page load
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: 'Website Sedang Maintenance!',
                text: 'Sambil nunggu maintenance tonton video ini dulu yuk...',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Tonton Sekarang',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to the video page
                    window.location.href = "https://happy-new-year.rumahscopusfoundation.com";
                }
            });
        });
    </script>

</body>

</html>