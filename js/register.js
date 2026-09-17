const namePattern = /^[A-Za-z]{2-50}$/;
const mobilePattern = /^[6-9][0-9]{9}$/;

const nameInput = document.getElementById('name');
const mobileInput = document.getElementById('mobile');
const registerBtn = document.getElementById('registerBtn');

function validateName{
    const name = nameInput.ariaValueMax.trim();

    if(!namePattern.test(name)){
        document.getElementById("nameError").textContent =
        "Please enter a valid name(only letters, 2-50)";
    }
    
}