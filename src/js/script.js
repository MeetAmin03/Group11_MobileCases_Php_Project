$(document).ready(function () {

    $(".product").hover(
        function () {
            $(this).find(".add-to-cart").slideDown(500);
        },
        function () {
            $(this).find(".add-to-cart").slideUp().finish();;
        }
    );



    // Slider


    // Initialize the index to keep track of the displayed image
    let index = 0;

    // Function to display images in a slideshow
    function displayImages() {
        let i;
        const images = $(".image");

        // Hide all images
        for (i = 0; i < images.length; i++) {
            images[i].style.display = "none";
        }

        // Increment the index and reset if it exceeds the number of images
        index++;
        if (index > images.length) {
            index = 1;
        }

        // Display the current image
        images[index - 1].style.display = "block";

        // Set a timeout to call the function again after 3000 milliseconds (3 seconds)
        setTimeout(displayImages, 3000);
    }

    // Initial call to start the image slideshow
    displayImages();
});