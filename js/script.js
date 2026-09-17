document.addEventListener("DOMContentLoaded", function () {

    const faqQuestions = document.querySelectorAll(".faq-question");

    faqQuestions.forEach(function (question) {

        question.addEventListener("click", function () {

            const id = question.getAttribute("aria-controls");
            const answer = document.getElementById(id);

            if (answer.hidden) {
                answer.hidden = false;
                question.setAttribute("aria-expanded", "true");
            } else {
                answer.hidden = true;
                question.setAttribute("aria-expanded", "false");
            }

        });

    });

    const menuToggle = document.getElementById("menuToggle");
    const nav = document.querySelector("nav");

    if (menuToggle) {

        menuToggle.addEventListener("click", function () {

            nav.classList.toggle("nav-open");

        });

    }

    const themeToggle = document.getElementById("themeToggle");

    if (themeToggle) {

        const savedTheme = localStorage.getItem("theme");

        if (savedTheme === "dark") {
            document.body.classList.add("dark-theme");
            themeToggle.textContent = "Light Mode";
        }

        themeToggle.addEventListener("click", function () {

            document.body.classList.toggle("dark-theme");

            if (document.body.classList.contains("dark-theme")) {
                localStorage.setItem("theme", "dark");
                themeToggle.textContent = "Light Mode";
            } else {
                localStorage.setItem("theme", "light");
                themeToggle.textContent = "Dark Mode";
            }

        });

    }

    const closeNotification = document.getElementById("closeNotification");
    const notificationBanner = document.getElementById("notificationBanner");

    if (closeNotification) {

        closeNotification.addEventListener("click", function () {
            notificationBanner.style.display = "none";
        });

    }

    const openModal = document.getElementById("openModal");
    const closeModal = document.getElementById("closeModal");
    const welcomeModal = document.getElementById("welcomeModal");

    if (openModal) {

        openModal.addEventListener("click", function () {
            welcomeModal.style.display = "block";
        });

    }

    if (closeModal) {

        closeModal.addEventListener("click", function () {
            welcomeModal.style.display = "none";
        });

    }

    const slides = document.querySelectorAll(".slide");
    const previousButton = document.getElementById("previousButton");
    const nextButton = document.getElementById("nextButton");

    let currentSlide = 0;

    function showSlide(number) {

        slides.forEach(function (slide) {
            slide.style.display = "none";
        });

        slides[number].style.display = "block";
    }

    if (slides.length > 0) {

        showSlide(currentSlide);

        nextButton.addEventListener("click", function () {

            currentSlide++;

            if (currentSlide >= slides.length) {
                currentSlide = 0;
            }

            showSlide(currentSlide);

        });

        previousButton.addEventListener("click", function () {

            currentSlide--;

            if (currentSlide < 0) {
                currentSlide = slides.length - 1;
            }

            showSlide(currentSlide);

        });

    }

});