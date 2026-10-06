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

const form = document.getElementById("registrationForm");

if (form) {

    const name = document.getElementById("name");
    const email = document.getElementById("email");
    const mobile = document.getElementById("mobile");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");
    const course = document.getElementById("course");
    const year = document.getElementById("year");
    const terms = document.getElementById("terms");

    const nameError = document.getElementById("nameError");
    const emailError = document.getElementById("emailError");
    const mobileError = document.getElementById("mobileError");
    const passwordError = document.getElementById("passwordError");
    const confirmPasswordError = document.getElementById("confirmPasswordError");
    const courseError = document.getElementById("courseError");
    const yearError = document.getElementById("yearError");
    const genderError = document.getElementById("genderError");
    const termsError = document.getElementById("termsError");

    const passwordStrength = document.getElementById("passwordStrength");
    const successMessage = document.getElementById("successMessage");
    const showPassword = document.getElementById("showPassword");


    function checkName() {

        if (name.value.trim() === "") {
            nameError.textContent = "This field is required.";
            return false;
        }

        const pattern = /^[A-Za-z ]{2,50}$/;

        if (!pattern.test(name.value.trim())) {
            nameError.textContent = "Enter a valid name.";
            return false;
        }

        nameError.textContent = "";
        return true;
    }


    function checkEmail() {

        if (email.value.trim() === "") {
            emailError.textContent = "This field is required.";
            return false;
        }

        if (!email.validity.valid) {
            emailError.textContent = "Enter a valid email address.";
            return false;
        }

        emailError.textContent = "";
        return true;
    }


    function checkMobile() {

        if (mobile.value.trim() === "") {
            mobileError.textContent = "This field is required.";
            return false;
        }

        const pattern = /^[0-9]{10}$/;

        if (!pattern.test(mobile.value.trim())) {
            mobileError.textContent = "Enter a valid 10-digit mobile number.";
            return false;
        }

        mobileError.textContent = "";
        return true;
    }


    function checkPassword() {

        if (password.value === "") {
            passwordError.textContent = "This field is required.";
            passwordStrength.textContent = "";
            return false;
        }

        const pattern =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/;

        if (!pattern.test(password.value)) {
            passwordError.textContent =
                "Use 8+ characters with uppercase, lowercase, number and special character.";
            return false;
        }

        passwordError.textContent = "";
        return true;
    }


    function checkConfirmPassword() {

        if (confirmPassword.value === "") {
            confirmPasswordError.textContent = "This field is required.";
            return false;
        }

        if (confirmPassword.value !== password.value) {
            confirmPasswordError.textContent = "Passwords do not match.";
            return false;
        }

        confirmPasswordError.textContent = "";
        return true;
    }


    function checkCourse() {

        if (course.value.trim() === "") {
            courseError.textContent = "This field is required.";
            return false;
        }

        const pattern = /^[A-Za-z ]{2,50}$/;

        if (!pattern.test(course.value.trim())) {
            courseError.textContent = "Enter a valid course.";
            return false;
        }

        courseError.textContent = "";
        return true;
    }


    function checkYear() {

        if (year.value === "") {
            yearError.textContent = "This field is required.";
            return false;
        }

        yearError.textContent = "";
        return true;
    }


    function checkGender() {

        const gender = document.querySelector(
            'input[name="gender"]:checked'
        );

        if (!gender) {
            genderError.textContent = "This field is required.";
            return false;
        }

        genderError.textContent = "";
        return true;
    }


    function checkTerms() {

        if (!terms.checked) {
            termsError.textContent = "You must accept the terms and conditions.";
            return false;
        }

        termsError.textContent = "";
        return true;
    }


    name.addEventListener("blur", checkName);
    email.addEventListener("blur", checkEmail);
    mobile.addEventListener("blur", checkMobile);
    password.addEventListener("blur", checkPassword);
    confirmPassword.addEventListener("blur", checkConfirmPassword);
    course.addEventListener("blur", checkCourse);
    year.addEventListener("blur", checkYear);
    terms.addEventListener("blur", checkTerms);


    password.addEventListener("input", function () {

        if (password.value === "") {
            passwordStrength.textContent = "";
            return;
        }

        let score = 0;

        if (password.value.length >= 8) {
            score++;
        }

        if (/[a-z]/.test(password.value)) {
            score++;
        }

        if (/[A-Z]/.test(password.value)) {
            score++;
        }

        if (/[0-9]/.test(password.value)) {
            score++;
        }

        if (/[^A-Za-z0-9]/.test(password.value)) {
            score++;
        }

        if (score <= 2) {
            passwordStrength.textContent = "Password Strength: Weak";
        } else if (score <= 4) {
            passwordStrength.textContent = "Password Strength: Medium";
        } else {
            passwordStrength.textContent = "Password Strength: Strong";
        }

    });


    showPassword.addEventListener("change", function () {

        if (showPassword.checked) {
            password.type = "text";
            confirmPassword.type = "text";
        } else {
            password.type = "password";
            confirmPassword.type = "password";
        }

    });


    form.addEventListener("submit", function (event) {

        event.preventDefault();

        const valid =
            checkName() &&
            checkEmail() &&
            checkMobile() &&
            checkPassword() &&
            checkConfirmPassword() &&
            checkCourse() &&
            checkYear() &&
            checkGender() &&
            checkTerms();

        if (valid) {
            successMessage.textContent = "Registration successful.";
        } else {
            successMessage.textContent = "";
        }

    });


    form.addEventListener("reset", function () {

        nameError.textContent = "";
        emailError.textContent = "";
        mobileError.textContent = "";
        passwordError.textContent = "";
        confirmPasswordError.textContent = "";
        courseError.textContent = "";
        yearError.textContent = "";
        genderError.textContent = "";
        termsError.textContent = "";
        passwordStrength.textContent = "";
        successMessage.textContent = "";

        password.type = "password";
        confirmPassword.type = "password";

    });

}

const eventsTableBody = document.getElementById("eventsTableBody");

if (eventsTableBody) {

    const searchInput = document.getElementById("eventSearch");
    const filterSelect = document.getElementById("eventFilter");
    const sortSelect = document.getElementById("eventSort");

    const loadingMessage = document.getElementById("eventLoading");
    const errorMessage = document.getElementById("eventError");

    const previousPage = document.getElementById("previousPage");
    const nextPage = document.getElementById("nextPage");
    const pageNumber = document.getElementById("pageNumber");

    let events = [];
    let currentPage = 1;
    const eventsPerPage = 5;

    fetch("../data/events.json")
        .then(function (response) {

            if (!response.ok) {
                throw new Error("Unable to load events.");
            }

            return response.json();

        })
        .then(function (data) {

            events = data;

            loadingMessage.textContent = "";

            displayEvents();

        })
        .catch(function (error) {

            loadingMessage.textContent = "";
            errorMessage.textContent = "Error loading events.";

            console.log(error);

        });


    function displayEvents() {

        let result = events;

        const searchText = searchInput.value.toLowerCase();
        const category = filterSelect.value;
        const sortType = sortSelect.value;


        result = result.filter(function (event) {

            return event.event.toLowerCase().includes(searchText);

        });


        result = result.filter(function (event) {

            if (category === "All") {
                return true;
            }

            return event.category === category;

        });


        result.sort(function (a, b) {

            if (sortType === "name") {
                return a.event.localeCompare(b.event);
            }

            return new Date(a.date) - new Date(b.date);

        });


        const start = (currentPage - 1) * eventsPerPage;
        const end = start + eventsPerPage;

        const pageEvents = result.slice(start, end);


        eventsTableBody.innerHTML = "";


        pageEvents.forEach(function (event) {

            const row = document.createElement("tr");

            row.innerHTML =
                "<td>" + event.event + "</td>" +
                "<td>" + event.date + "</td>" +
                "<td>" + event.category + "</td>";

            eventsTableBody.appendChild(row);

        });


        pageNumber.textContent = "Page " + currentPage;

        previousPage.disabled = currentPage === 1;

        nextPage.disabled = end >= result.length;

    }


    searchInput.addEventListener("input", function () {

        currentPage = 1;
        displayEvents();

    });


    filterSelect.addEventListener("change", function () {

        currentPage = 1;
        displayEvents();

    });


    sortSelect.addEventListener("change", function () {

        currentPage = 1;
        displayEvents();

    });


    previousPage.addEventListener("click", function () {

        if (currentPage > 1) {
            currentPage--;
            displayEvents();
        }

    });


    nextPage.addEventListener("click", function () {

        currentPage++;
        displayEvents();

    });

}

// FAQ JSON

const faqList = document.getElementById("faqList");

if (faqList) {

    const faqLoading = document.getElementById("faqLoading");
    const faqError = document.getElementById("faqError");

    fetch("../data/faqs.json")
        .then(function (response) {

            if (!response.ok) {
                throw new Error("Unable to load FAQs.");
            }

            return response.json();

        })
        .then(function (data) {

            faqLoading.textContent = "";

            data.forEach(function (faq) {

                const article = document.createElement("article");

                article.className = "faq-item";

                article.innerHTML =
                    '<button class="faq-question" type="button">' +
                    faq.question +
                    '</button>' +
                    '<p hidden>' +
                    faq.answer +
                    '</p>';

                faqList.appendChild(article);

                const question = article.querySelector(".faq-question");
                const answer = article.querySelector("p");

                question.addEventListener("click", function () {

                    answer.hidden = !answer.hidden;

                });

            });

        })
        .catch(function (error) {

            faqLoading.textContent = "";
            faqError.textContent = "Error loading FAQs.";

            console.log(error);

        });

}


// Students JSON

const studentList = document.getElementById("studentList");

if (studentList) {

    const studentLoading = document.getElementById("studentLoading");
    const studentError = document.getElementById("studentError");

    fetch("../data/students.json")
        .then(function (response) {

            if (!response.ok) {
                throw new Error("Unable to load students.");
            }

            return response.json();

        })
        .then(function (data) {

            studentLoading.textContent = "";

            data.forEach(function (student) {

                const studentBox = document.createElement("div");

                studentBox.innerHTML =
                    "<strong>Name:</strong><p>" + student.name + "</p>" +
                    "<strong>Course:</strong><p>" + student.course + "</p>" +
                    "<strong>Year:</strong><p>" + student.year + "</p>" +
                    "<strong>Email:</strong><p>" + student.email + "</p>";

                studentList.appendChild(studentBox);

            });

        })
        .catch(function (error) {

            studentLoading.textContent = "";
            studentError.textContent = "Error loading students.";

            console.log(error);

        });

}