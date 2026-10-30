// StudentHub Portal - Main JavaScript

document.addEventListener("DOMContentLoaded", function () {
    initFaqAccordion();
    initMobileNav();
    initThemeSwitcher();
    initWelcomeModal();
    initHighlightsSlider();
    initRegistrationValidation();
    initEventsManager();
    initFaqJson();
    initStudentDirectory();
});

// =====================================
// 1. FAQ ACCORDION (Static HTML)
// =====================================
function initFaqAccordion() {
    const faqQuestions = document.querySelectorAll(".faq-question");

    faqQuestions.forEach(function (question) {
        question.addEventListener("click", function () {
            const id = question.getAttribute("aria-controls");
            const answer = document.getElementById(id);

            if (answer) {
                const isExpanded = question.getAttribute("aria-expanded") === "true";
                answer.hidden = isExpanded;
                question.setAttribute("aria-expanded", String(!isExpanded));
            }
        });
    });
}

// =====================================
// 2. MOBILE NAVIGATION
// =====================================
function initMobileNav() {
    const menuToggle = document.getElementById("menuToggle");
    const nav = document.getElementById("mainNav");

    if (!menuToggle || !nav) return;

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
// 3. THEME SWITCHER
// =====================================
function initThemeSwitcher() {
    const themeToggle = document.getElementById("themeToggle");
    if (!themeToggle) return;

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
// 4. WELCOME MODAL
// =====================================
function initWelcomeModal() {
    const openModal = document.getElementById("openModal");
    const closeModal = document.getElementById("closeModal");
    const welcomeModal = document.getElementById("welcomeModal");

    if (!openModal || !closeModal || !welcomeModal) return;

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
        if (event.key === "Escape" && welcomeModal.getAttribute("aria-hidden") === "false") {
            hideModal();
        }

        if (event.key === "Tab" && welcomeModal.getAttribute("aria-hidden") === "false") {
            const focusableElements = welcomeModal.querySelectorAll(
                'button, a[href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            if (focusableElements.length === 0) return;

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (event.shiftKey && document.activeElement === firstElement) {
                event.preventDefault();
                lastElement.focus();
            } else if (!event.shiftKey && document.activeElement === lastElement) {
                event.preventDefault();
                firstElement.focus();
            }
        }
    });
}

// =====================================
// 5. STUDENT HIGHLIGHTS SLIDER
// =====================================
function initHighlightsSlider() {
    const slides = document.querySelectorAll(".slide");
    const previousButton = document.getElementById("previousButton");
    const nextButton = document.getElementById("nextButton");
    const slideStatus = document.getElementById("slideStatus");

    if (slides.length === 0) return;

    let currentSlide = 0;

    function showSlide(index) {
        slides.forEach(function (slide, i) {
            slide.style.display = i === index ? "block" : "none";
        });

        if (slideStatus) {
            slideStatus.textContent = "Slide " + (index + 1) + " of " + slides.length;
        }
    }

    showSlide(currentSlide);

    if (nextButton) {
        nextButton.addEventListener("click", function () {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        });
    }

    if (previousButton) {
        previousButton.addEventListener("click", function () {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        });
    }
}

// =====================================
// 6. REGISTRATION FORM VALIDATION (P5 & P9)
// =====================================
function initRegistrationValidation() {
    const form = document.getElementById("registrationForm");
    if (!form) return;

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

    function setFieldState(field, errorEl, isValid, message) {
        if (!isValid) {
            field.setAttribute("aria-invalid", "true");
            errorEl.textContent = message;
        } else {
            field.setAttribute("aria-invalid", "false");
            errorEl.textContent = "";
        }
        return isValid;
    }

    function checkName() {
        if (!name) return true;
        const val = name.value.trim();
        if (val === "") {
            return setFieldState(name, nameError, false, "Full name is required.");
        }
        const pattern = /^[A-Za-z ]{2,50}$/;
        if (!pattern.test(val)) {
            return setFieldState(name, nameError, false, "Name must be 2 to 50 characters, letters and spaces only.");
        }
        return setFieldState(name, nameError, true, "");
    }

    function checkEmail() {
        if (!email) return true;
        const val = email.value.trim();
        if (val === "") {
            return setFieldState(email, emailError, false, "Email address is required.");
        }
        if (!email.validity.valid || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            return setFieldState(email, emailError, false, "Please enter a valid email address.");
        }
        return setFieldState(email, emailError, true, "");
    }

    function checkMobile() {
        if (!mobile) return true;
        const val = mobile.value.trim();
        if (val === "") {
            return setFieldState(mobile, mobileError, false, "Mobile number is required.");
        }
        const pattern = /^[0-9]{10}$/;
        if (!pattern.test(val)) {
            return setFieldState(mobile, mobileError, false, "Please enter a valid 10-digit mobile number.");
        }
        return setFieldState(mobile, mobileError, true, "");
    }

    function checkPassword() {
        if (!password) return true;
        const val = password.value;
        if (val === "") {
            if (passwordStrength) passwordStrength.textContent = "";
            return setFieldState(password, passwordError, false, "Password is required.");
        }
        const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/;
        if (!pattern.test(val)) {
            return setFieldState(
                password,
                passwordError,
                false,
                "Password must be 8+ characters and include uppercase, lowercase, number and special character."
            );
        }
        return setFieldState(password, passwordError, true, "");
    }

    function checkConfirmPassword() {
        if (!confirmPassword || !password) return true;
        const val = confirmPassword.value;
        if (val === "") {
            return setFieldState(confirmPassword, confirmPasswordError, false, "Please confirm your password.");
        }
        if (val !== password.value) {
            return setFieldState(confirmPassword, confirmPasswordError, false, "Passwords do not match.");
        }
        return setFieldState(confirmPassword, confirmPasswordError, true, "");
    }

    function checkCourse() {
        if (!course) return true;
        const val = course.value.trim();
        if (val === "") {
            return setFieldState(course, courseError, false, "Course is required.");
        }
        const pattern = /^[A-Za-z ]{2,50}$/;
        if (!pattern.test(val)) {
            return setFieldState(course, courseError, false, "Course must be 2 to 50 characters, letters and spaces only.");
        }
        return setFieldState(course, courseError, true, "");
    }

    function checkYear() {
        if (!year) return true;
        if (year.value === "") {
            return setFieldState(year, yearError, false, "Please select an academic year.");
        }
        return setFieldState(year, yearError, true, "");
    }

    function checkGender() {
        const radios = form.querySelectorAll('input[name="gender"]');
        const checked = form.querySelector('input[name="gender"]:checked');
        if (!checked) {
            radios.forEach(function (r) { r.setAttribute("aria-invalid", "true"); });
            if (genderError) genderError.textContent = "Please select your gender.";
            return false;
        }
        radios.forEach(function (r) { r.setAttribute("aria-invalid", "false"); });
        if (genderError) genderError.textContent = "";
        return true;
    }

    function checkTerms() {
        if (!terms) return true;
        if (!terms.checked) {
            return setFieldState(terms, termsError, false, "You must accept the terms and conditions.");
        }
        return setFieldState(terms, termsError, true, "");
    }

    if (name) name.addEventListener("blur", checkName);
    if (email) email.addEventListener("blur", checkEmail);
    if (mobile) mobile.addEventListener("blur", checkMobile);
    if (password) password.addEventListener("blur", checkPassword);
    if (confirmPassword) confirmPassword.addEventListener("blur", checkConfirmPassword);
    if (course) course.addEventListener("blur", checkCourse);
    if (year) year.addEventListener("blur", checkYear);
    if (terms) terms.addEventListener("change", checkTerms);

    const genderRadios = form.querySelectorAll('input[name="gender"]');
    genderRadios.forEach(function (radio) {
        radio.addEventListener("change", checkGender);
    });

    if (password && passwordStrength) {
        password.addEventListener("input", function () {
            const val = password.value;
            if (val === "") {
                passwordStrength.textContent = "";
                passwordStrength.className = "strength-msg";
                return;
            }

            let score = 0;
            if (val.length >= 8) score++;
            if (/[a-z]/.test(val)) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            if (score <= 2) {
                passwordStrength.textContent = "Password Strength: Weak — Add uppercase, lowercase, numbers, and symbols.";
                passwordStrength.className = "strength-msg strength-weak";
            } else if (score <= 4) {
                passwordStrength.textContent = "Password Strength: Medium — Add special characters or more length.";
                passwordStrength.className = "strength-msg strength-medium";
            } else {
                passwordStrength.textContent = "Password Strength: Strong — Meets complexity requirements.";
                passwordStrength.className = "strength-msg strength-strong";
            }
        });
    }

    if (showPassword && password && confirmPassword) {
        showPassword.addEventListener("change", function () {
            const type = showPassword.checked ? "text" : "password";
            password.type = type;
            confirmPassword.type = type;
        });
    }

    form.addEventListener("submit", function (event) {
        const isNameValid = checkName();
        const isEmailValid = checkEmail();
        const isMobileValid = checkMobile();
        const isPasswordValid = checkPassword();
        const isConfirmValid = checkConfirmPassword();
        const isCourseValid = checkCourse();
        const isYearValid = checkYear();
        const isGenderValid = checkGender();
        const isTermsValid = checkTerms();

        const valid = isNameValid && isEmailValid && isMobileValid && isPasswordValid &&
                      isConfirmValid && isCourseValid && isYearValid && isGenderValid && isTermsValid;

        if (!valid) {
            event.preventDefault();
            if (successMessage) successMessage.textContent = "";

            // Focus first invalid element for accessibility
            const firstInvalid = form.querySelector('[aria-invalid="true"]');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });

    form.addEventListener("reset", function () {
        const errorSpans = [
            nameError, emailError, mobileError, passwordError,
            confirmPasswordError, courseError, yearError, genderError, termsError
        ];
        errorSpans.forEach(function (span) {
            if (span) span.textContent = "";
        });

        if (passwordStrength) {
            passwordStrength.textContent = "";
            passwordStrength.className = "strength-msg";
        }

        if (successMessage) successMessage.textContent = "";

        const allInputs = form.querySelectorAll("input, select");
        allInputs.forEach(function (input) {
            input.setAttribute("aria-invalid", "false");
        });

        if (password) password.type = "password";
        if (confirmPassword) confirmPassword.type = "password";
        if (showPassword) showPassword.checked = false;
    });
}

// =====================================
// 7. EVENTS MANAGER (P6, P13)
// =====================================
function initEventsManager() {
    const eventsTableBody = document.getElementById("eventsTableBody");
    if (!eventsTableBody) return;

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

    function populateFilterDropdown() {
        if (!filterSelect) return;
        const currentSelection = filterSelect.value || "All";

        while (filterSelect.firstChild) {
            filterSelect.removeChild(filterSelect.firstChild);
        }

        const defaultOption = document.createElement("option");
        defaultOption.value = "All";
        defaultOption.textContent = "All Locations / Categories";
        filterSelect.appendChild(defaultOption);

        const uniqueLocations = Array.from(
            new Set(events.map(function (e) {
                return e.location || e.category;
            }).filter(Boolean))
        ).sort();

        uniqueLocations.forEach(function (loc) {
            const opt = document.createElement("option");
            opt.value = loc;
            opt.textContent = loc;
            if (loc === currentSelection) opt.selected = true;
            filterSelect.appendChild(opt);
        });
    }

    function loadEvents() {
        if (loadingMessage) loadingMessage.textContent = "Loading events...";
        if (errorMessage) errorMessage.textContent = "";

        fetch("events_api.php")
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Unable to load events from API.");
                }
                return response.json();
            })
            .then(function (data) {
                if (!data.success || !Array.isArray(data.events)) {
                    throw new Error("Invalid event data received.");
                }
                events = data.events;
                if (loadingMessage) loadingMessage.textContent = "";
                populateFilterDropdown();
                displayEvents();
            })
            .catch(function (apiError) {
                // Graceful fallback to static events.json if API is unreachable
                fetch("../data/events.json")
                    .then(function (res) {
                        if (!res.ok) throw apiError;
                        return res.json();
                    })
                    .then(function (jsonData) {
                        if (Array.isArray(jsonData)) {
                            events = jsonData.map(function (item) {
                                return {
                                    event_id: item.id,
                                    title: item.event,
                                    description: item.category ? ("Category: " + item.category) : "",
                                    event_date: item.date,
                                    location: item.category || ""
                                };
                            });
                            if (loadingMessage) loadingMessage.textContent = "";
                            populateFilterDropdown();
                            displayEvents();
                        } else {
                            throw apiError;
                        }
                    })
                    .catch(function () {
                        if (loadingMessage) loadingMessage.textContent = "";
                        if (errorMessage) errorMessage.textContent = "Error loading events.";
                    });
            });
    }

    function displayEvents() {
        let result = events.slice();

        const searchText = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const filterVal = filterSelect ? filterSelect.value : "All";
        const sortType = sortSelect ? sortSelect.value : "";

        if (searchText !== "") {
            result = result.filter(function (event) {
                const title = String(event.title || "").toLowerCase();
                const desc = String(event.description || "").toLowerCase();
                const loc = String(event.location || event.category || "").toLowerCase();
                return title.includes(searchText) || desc.includes(searchText) || loc.includes(searchText);
            });
        }

        if (filterVal !== "All") {
            result = result.filter(function (event) {
                const loc = event.location || "";
                const cat = event.category || "";
                return loc === filterVal || cat === filterVal;
            });
        }

        // P6-01: Support dateAsc, dateDesc, nameAsc, with backward-compatibility for date and name
        if (sortType === "dateAsc" || sortType === "date") {
            result.sort(function (a, b) {
                return String(a.event_date || "").localeCompare(String(b.event_date || ""));
            });
        } else if (sortType === "dateDesc") {
            result.sort(function (a, b) {
                return String(b.event_date || "").localeCompare(String(a.event_date || ""));
            });
        } else if (sortType === "nameAsc" || sortType === "name") {
            result.sort(function (a, b) {
                return String(a.title || "").localeCompare(String(b.title || ""));
            });
        }

        const totalPages = Math.max(1, Math.ceil(result.length / eventsPerPage));
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIndex = (currentPage - 1) * eventsPerPage;
        const pageEvents = result.slice(startIndex, startIndex + eventsPerPage);

        // Safe DOM rendering (P6-06)
        while (eventsTableBody.firstChild) {
            eventsTableBody.removeChild(eventsTableBody.firstChild);
        }

        if (pageEvents.length === 0) {
            const emptyRow = document.createElement("tr");
            const emptyCell = document.createElement("td");
            emptyCell.colSpan = 4;
            emptyCell.textContent = "No events found.";
            emptyRow.appendChild(emptyCell);
            eventsTableBody.appendChild(emptyRow);
        } else {
            pageEvents.forEach(function (event) {
                const row = document.createElement("tr");

                const values = [
                    event.title || "",
                    event.description || "",
                    event.event_date || "",
                    event.location || event.category || ""
                ];

                values.forEach(function (val) {
                    const cell = document.createElement("td");
                    cell.textContent = val;
                    row.appendChild(cell);
                });

                eventsTableBody.appendChild(row);
            });
        }

        if (pageNumber) {
            pageNumber.textContent = "Page " + currentPage + " / " + totalPages;
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
// 8. FAQ JSON (P6)
// =====================================
function initFaqJson() {
    const faqList = document.getElementById("faqList");
    if (!faqList) return;

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
            if (faqLoading) faqLoading.textContent = "";

            while (faqList.firstChild) {
                faqList.removeChild(faqList.firstChild);
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
                    const isExpanded = question.getAttribute("aria-expanded") === "true";
                    question.setAttribute("aria-expanded", String(!isExpanded));
                    answer.hidden = isExpanded;
                });

                article.appendChild(question);
                article.appendChild(answer);
                faqList.appendChild(article);
            });
        })
        .catch(function (error) {
            if (faqLoading) faqLoading.textContent = "";
            if (faqError) faqError.textContent = error.message;
        });
}

// =====================================
// 9. STUDENT DIRECTORY (P6-04)
// =====================================
function initStudentDirectory() {
    const studentList = document.getElementById("studentList");
    if (!studentList) return;

    const studentLoading = document.getElementById("studentLoading");
    const studentError = document.getElementById("studentError");
    const searchInput = document.getElementById("studentSearch");
    const filterSelect = document.getElementById("studentFilter");
    const sortSelect = document.getElementById("studentSort");
    const prevBtn = document.getElementById("studentPrevPage");
    const nextBtn = document.getElementById("studentNextPage");
    const pageNum = document.getElementById("studentPageNumber");

    let students = [];
    let currentPage = 1;
    const studentsPerPage = 6;

    function populateCourseFilter() {
        if (!filterSelect) return;
        while (filterSelect.firstChild) {
            filterSelect.removeChild(filterSelect.firstChild);
        }

        const allOpt = document.createElement("option");
        allOpt.value = "All";
        allOpt.textContent = "All Courses";
        filterSelect.appendChild(allOpt);

        const courses = Array.from(
            new Set(students.map(function (s) { return s.course; }).filter(Boolean))
        ).sort();

        courses.forEach(function (c) {
            const opt = document.createElement("option");
            opt.value = c;
            opt.textContent = c;
            filterSelect.appendChild(opt);
        });
    }

    function renderStudents() {
        let result = students.slice();

        const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const selectedCourse = filterSelect ? filterSelect.value : "All";
        const sortOrder = sortSelect ? sortSelect.value : "";

        if (query !== "") {
            result = result.filter(function (s) {
                const name = String(s.name || "").toLowerCase();
                const email = String(s.email || "").toLowerCase();
                const course = String(s.course || "").toLowerCase();
                return name.includes(query) || email.includes(query) || course.includes(query);
            });
        }

        if (selectedCourse !== "All") {
            result = result.filter(function (s) {
                return s.course === selectedCourse;
            });
        }

        if (sortOrder === "nameAsc") {
            result.sort(function (a, b) {
                return String(a.name || "").localeCompare(String(b.name || ""));
            });
        } else if (sortOrder === "nameDesc") {
            result.sort(function (a, b) {
                return String(b.name || "").localeCompare(String(a.name || ""));
            });
        } else if (sortOrder === "yearAsc") {
            result.sort(function (a, b) {
                return String(a.year || "").localeCompare(String(b.year || ""));
            });
        }

        const totalPages = Math.max(1, Math.ceil(result.length / studentsPerPage));
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIndex = (currentPage - 1) * studentsPerPage;
        const pageItems = result.slice(startIndex, startIndex + studentsPerPage);

        while (studentList.firstChild) {
            studentList.removeChild(studentList.firstChild);
        }

        if (pageItems.length === 0) {
            const emptyNotice = document.createElement("p");
            emptyNotice.textContent = "No students match the criteria.";
            studentList.appendChild(emptyNotice);
        } else {
            pageItems.forEach(function (student) {
                const studentBox = document.createElement("div");
                studentBox.className = "student-card";

                const nameEl = document.createElement("h4");
                nameEl.textContent = student.name;
                studentBox.appendChild(nameEl);

                const courseEl = document.createElement("p");
                courseEl.textContent = "Course: " + (student.course || "N/A");
                studentBox.appendChild(courseEl);

                const yearEl = document.createElement("p");
                yearEl.textContent = "Year: " + (student.year || "N/A");
                studentBox.appendChild(yearEl);

                const emailEl = document.createElement("p");
                emailEl.textContent = "Email: " + (student.email || "N/A");
                studentBox.appendChild(emailEl);

                studentList.appendChild(studentBox);
            });
        }

        if (pageNum) {
            pageNum.textContent = "Page " + currentPage + " / " + totalPages + " (" + result.length + " students)";
        }
        if (prevBtn) {
            prevBtn.disabled = currentPage === 1;
        }
        if (nextBtn) {
            nextBtn.disabled = currentPage >= totalPages;
        }
    }

    fetch("../data/students.json")
        .then(function (response) {
            if (!response.ok) {
                throw new Error("Unable to load students.");
            }
            return response.json();
        })
        .then(function (data) {
            if (studentLoading) studentLoading.textContent = "";
            students = Array.isArray(data) ? data : [];
            populateCourseFilter();
            renderStudents();
        })
        .catch(function (error) {
            if (studentLoading) studentLoading.textContent = "";
            if (studentError) studentError.textContent = "Error loading students.";
            console.error(error);
        });

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            currentPage = 1;
            renderStudents();
        });
    }

    if (filterSelect) {
        filterSelect.addEventListener("change", function () {
            currentPage = 1;
            renderStudents();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener("change", function () {
            currentPage = 1;
            renderStudents();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener("click", function () {
            if (currentPage > 1) {
                currentPage--;
                renderStudents();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", function () {
            currentPage++;
            renderStudents();
        });
    }
}
