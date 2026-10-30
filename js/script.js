
document.addEventListener("DOMContentLoaded", function () {

    // =====================================
    // FAQ QUESTIONS ALREADY IN HTML
    // =====================================

    const faqQuestions = document.querySelectorAll(".faq-question");

    faqQuestions.forEach(function (question) {
        question.addEventListener("click", function () {
            const id = question.getAttribute("aria-controls");
            const answer = document.getElementById(id);

            if (answer) {
                const isExpanded =
                    question.getAttribute("aria-expanded") === "true";

                answer.hidden = isExpanded;
                question.setAttribute("aria-expanded", String(!isExpanded));
            }
        });
    });


    // =====================================
    // HAMBURGER MENU
    // =====================================

    const menuToggle = document.getElementById("menuToggle");
    const nav = document.getElementById("mainNav");

    if (menuToggle && nav) {
        menuToggle.addEventListener("click", function () {
            const isOpen = nav.classList.toggle("nav-open");

            menuToggle.setAttribute("aria-expanded", String(isOpen));
            menuToggle.setAttribute(
                "aria-label",
                isOpen ? "Close navigation menu" : "Open navigation menu"
            );
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && nav.classList.contains("nav-open")) {
                nav.classList.remove("nav-open");
                menuToggle.setAttribute("aria-expanded", "false");
                menuToggle.setAttribute("aria-label", "Open navigation menu");
                menuToggle.focus();
            }
        });
    }


    // =====================================
    // THEME SWITCHER
    // =====================================

    const themeToggle = document.getElementById("themeToggle");

    if (themeToggle) {
        const savedTheme = localStorage.getItem("theme");

        if (savedTheme === "dark") {
            document.body.classList.add("dark-theme");
        }

        function updateThemeButton() {
            const isDark = document.body.classList.contains("dark-theme");

            themeToggle.textContent = isDark ? "Light Mode" : "Dark Mode";
            themeToggle.setAttribute("aria-pressed", String(isDark));
        }

        updateThemeButton();

        themeToggle.addEventListener("click", function () {
            document.body.classList.toggle("dark-theme");

            const isDark = document.body.classList.contains("dark-theme");

            localStorage.setItem("theme", isDark ? "dark" : "light");
            updateThemeButton();
        });
    }


    // =====================================
    // WELCOME MODAL
    // =====================================

    const openModal = document.getElementById("openModal");
    const closeModal = document.getElementById("closeModal");
    const welcomeModal = document.getElementById("welcomeModal");

    if (openModal && closeModal && welcomeModal) {

        function hideModal() {
            welcomeModal.setAttribute("aria-hidden", "true");
            openModal.focus();
        }

        openModal.addEventListener("click", function () {
            welcomeModal.setAttribute("aria-hidden", "false");
            closeModal.focus();
        });

        closeModal.addEventListener("click", hideModal);

        welcomeModal.addEventListener("click", function (event) {
            if (event.target === welcomeModal) {
                hideModal();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (
                event.key === "Escape" &&
                welcomeModal.getAttribute("aria-hidden") === "false"
            ) {
                hideModal();
            }

            if (
                event.key === "Tab" &&
                welcomeModal.getAttribute("aria-hidden") === "false"
            ) {
                const focusableElements = welcomeModal.querySelectorAll(
                    'button, a[href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );

                const firstElement = focusableElements[0];
                const lastElement =
                    focusableElements[focusableElements.length - 1];

                if (
                    event.shiftKey &&
                    document.activeElement === firstElement
                ) {
                    event.preventDefault();
                    lastElement.focus();
                } else if (
                    !event.shiftKey &&
                    document.activeElement === lastElement
                ) {
                    event.preventDefault();
                    firstElement.focus();
                }
            }
        });
    }


    // =====================================
    // STUDENTHUB HIGHLIGHTS SLIDER
    // =====================================

    // Student Highlights Slider
const slides = document.querySelectorAll(".slide");
const previousButton = document.getElementById("previousButton");
const nextButton = document.getElementById("nextButton");
const slideStatus = document.getElementById("slideStatus");

let currentSlide = 0;

function showSlide(index) {
    if (slides.length === 0) return;

    slides.forEach((slide, i) => {
        slide.style.display = i === index ? "block" : "none";
    });

    if (slideStatus) {
        slideStatus.textContent =
            `Slide ${index + 1} of ${slides.length}`;
    }
}

if (slides.length > 0) {
    showSlide(currentSlide);

    if (nextButton) {
        nextButton.addEventListener("click", () => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        });
    }

    if (previousButton) {
        previousButton.addEventListener("click", () => {
            currentSlide =
                (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        });
    }
}


    // =====================================
    // REGISTRATION FORM VALIDATION
    // =====================================

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
        const confirmPasswordError =
            document.getElementById("confirmPasswordError");
        const courseError = document.getElementById("courseError");
        const yearError = document.getElementById("yearError");
        const genderError = document.getElementById("genderError");
        const termsError = document.getElementById("termsError");

        const passwordStrength =
            document.getElementById("passwordStrength");
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
                mobileError.textContent =
                    "Enter a valid 10-digit mobile number.";
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
                confirmPasswordError.textContent =
                    "This field is required.";
                return false;
            }

            if (confirmPassword.value !== password.value) {
                confirmPasswordError.textContent =
                    "Passwords do not match.";
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
                termsError.textContent =
                    "You must accept the terms and conditions.";
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

            if (password.value.length >= 8) score++;
            if (/[a-z]/.test(password.value)) score++;
            if (/[A-Z]/.test(password.value)) score++;
            if (/[0-9]/.test(password.value)) score++;
            if (/[^A-Za-z0-9]/.test(password.value)) score++;

            if (score <= 2) {
                passwordStrength.textContent = "Password Strength: Weak";
            } else if (score <= 4) {
                passwordStrength.textContent = "Password Strength: Medium";
            } else {
                passwordStrength.textContent = "Password Strength: Strong";
            }
        });


        if (showPassword) {
            showPassword.addEventListener("change", function () {
                if (showPassword.checked) {
                    password.type = "text";
                    confirmPassword.type = "text";
                } else {
                    password.type = "password";
                    confirmPassword.type = "password";
                }
            });
        }


        form.addEventListener("submit", function (event) {
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

            if (!valid) {
                event.preventDefault();
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


    // =====================================
    // EVENTS API: SEARCH, FILTER, SORT, PAGINATION
    // =====================================

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


        function loadEvents() {
            if (loadingMessage) {
                loadingMessage.textContent = "Loading events...";
            }

            if (errorMessage) {
                errorMessage.textContent = "";
            }

            fetch("events_api.php")
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error("Unable to load events.");
                    }

                    return response.json();
                })
                .then(function (data) {
                    if (!data.success || !Array.isArray(data.events)) {
                        throw new Error("Invalid event data received.");
                    }

                    events = data.events;

                    if (loadingMessage) {
                        loadingMessage.textContent = "";
                    }

                    displayEvents();
                })
                .catch(function (error) {
                    if (loadingMessage) {
                        loadingMessage.textContent = "";
                    }

                    if (errorMessage) {
                        errorMessage.textContent = "Error loading events.";
                    }

                    console.error(error);
                });
        }


        function displayEvents() {
            let result = events.slice();

            const searchText = searchInput
                ? searchInput.value.toLowerCase().trim()
                : "";

            const category = filterSelect ? filterSelect.value : "All";
            const sortType = sortSelect ? sortSelect.value : "";

            result = result.filter(function (event) {
                return String(event.title || "")
                    .toLowerCase()
                    .includes(searchText);
            });

            result = result.filter(function (event) {
                return category === "All" || event.location === category;
            });

            if (sortType === "dateAsc") {
                result.sort(function (a, b) {
                    return a.event_date.localeCompare(b.event_date);
                });
            } else if (sortType === "dateDesc") {
                result.sort(function (a, b) {
                    return b.event_date.localeCompare(a.event_date);
                });
            } else if (sortType === "nameAsc") {
                result.sort(function (a, b) {
                    return a.title.localeCompare(b.title);
                });
            }

            const totalPages = Math.max(
                1,
                Math.ceil(result.length / eventsPerPage)
            );

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            const startIndex = (currentPage - 1) * eventsPerPage;
            const pageEvents = result.slice(
                startIndex,
                startIndex + eventsPerPage
            );

            eventsTableBody.innerHTML = "";

            if (pageEvents.length === 0) {
                eventsTableBody.innerHTML =
                    '<tr><td colspan="5">No events found.</td></tr>';
            } else {
                pageEvents.forEach(function (event) {
                    const row = document.createElement("tr");

                    const values = [
                        event.title,
                        event.description || "",
                        event.event_date,
                        event.location || ""
                    ];

                    values.forEach(function (value) {
                        const cell = document.createElement("td");
                        cell.textContent = value;
                        row.appendChild(cell);
                    });

                    eventsTableBody.appendChild(row);
                });
            }

            if (pageNumber) {
                pageNumber.textContent =
                    currentPage + " / " + totalPages;
            }

            if (previousPage) {
                previousPage.disabled = currentPage === 1;
            }

            if (nextPage) {
                nextPage.disabled = currentPage >= totalPages;
            }
        }


        if (searchInput) {
            searchInput.addEventListener("input", function () {
                currentPage = 1;
                displayEvents();
            });
        }

        if (filterSelect) {
            filterSelect.addEventListener("change", function () {
                currentPage = 1;
                displayEvents();
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener("change", function () {
                currentPage = 1;
                displayEvents();
            });
        }

        if (previousPage) {
            previousPage.addEventListener("click", function () {
                if (currentPage > 1) {
                    currentPage--;
                    displayEvents();
                }
            });
        }

        if (nextPage) {
            nextPage.addEventListener("click", function () {
                currentPage++;
                displayEvents();
            });
        }

        loadEvents();
    }


    // =====================================
    // FAQ JSON
    // =====================================

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
                if (faqLoading) {
                    faqLoading.textContent = "";
                }

                data.forEach(function (faq, index) {
                    const article = document.createElement("article");
                    article.className = "faq-item";

                    const question = document.createElement("button");
                    question.className = "faq-question";
                    question.type = "button";
                    question.textContent = faq.question;

                    const answer = document.createElement("p");
                    answer.id = "faq-answer-" + index;
                    answer.textContent = faq.answer;
                    answer.hidden = true;

                    question.setAttribute("aria-expanded", "false");
                    question.setAttribute("aria-controls", answer.id);

                    question.addEventListener("click", function () {
                        const isExpanded =
                            question.getAttribute("aria-expanded") === "true";

                        question.setAttribute(
                            "aria-expanded",
                            String(!isExpanded)
                        );

                        answer.hidden = isExpanded;
                    });

                    article.appendChild(question);
                    article.appendChild(answer);
                    faqList.appendChild(article);
                });
            })
            .catch(function (error) {
                if (faqLoading) {
                    faqLoading.textContent = "";
                }

                if (faqError) {
                    faqError.textContent = error.message;
                }
            });
    }


    // =====================================
    // STUDENTS JSON
    // =====================================

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
                if (studentLoading) {
                    studentLoading.textContent = "";
                }

                data.forEach(function (student) {
                    const studentBox = document.createElement("div");

                    const name = document.createElement("p");
                    const course = document.createElement("p");
                    const year = document.createElement("p");
                    const email = document.createElement("p");

                    name.textContent = "Name: " + student.name;
                    course.textContent = "Course: " + student.course;
                    year.textContent = "Year: " + student.year;
                    email.textContent = "Email: " + student.email;

                    studentBox.appendChild(name);
                    studentBox.appendChild(course);
                    studentBox.appendChild(year);
                    studentBox.appendChild(email);

                    studentList.appendChild(studentBox);
                });
            })
            .catch(function (error) {
                if (studentLoading) {
                    studentLoading.textContent = "";
                }

                if (studentError) {
                    studentError.textContent = "Error loading students.";
                }

                console.error(error);
            });
    }

});
