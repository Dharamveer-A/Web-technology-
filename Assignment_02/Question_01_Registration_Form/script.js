/**
 * Web Technologies (U21CS501) - Assignment 02: Question 1
 * Client-Side Form Validation & Password Strength Logic
 */

// Global state trackers
let isPasswordStrong = false;
let isPasswordMatched = false;

/**
 * Validates the full form upon submission
 */
function validateForm(event) {
  event.preventDefault();

  let isValid = true;
  clearAllErrors();

  // 1. Full Name Validation (Required, min 3 chars, alphabet only)
  const fullName = document.getElementById("fullName").value.trim();
  const nameRegex = /^[A-Za-z\s.]+$/;
  if (fullName === "") {
    showError("fullName", "nameError", "Full Name is required.");
    isValid = false;
  } else if (fullName.length < 3) {
    showError("fullName", "nameError", "Name must contain at least 3 characters.");
    isValid = false;
  } else if (!nameRegex.test(fullName)) {
    showError("fullName", "nameError", "Name can only contain alphabets and spaces.");
    isValid = false;
  } else {
    showSuccess("fullName");
  }

  // 2. Roll Number Validation (Required, alphanumeric format e.g. 24CS048)
  const rollNo = document.getElementById("rollNo").value.trim();
  const rollRegex = /^[0-9]{2}[A-Za-z]{2,4}[0-9]{3}$/;
  if (rollNo === "") {
    showError("rollNo", "rollError", "Roll Number is required.");
    isValid = false;
  } else if (!rollRegex.test(rollNo)) {
    showError("rollNo", "rollError", "Enter valid roll number format (e.g., 24CS048).");
    isValid = false;
  } else {
    showSuccess("rollNo");
  }

  // 3. Email Validation (Required, standard email regex)
  const email = document.getElementById("email").value.trim();
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (email === "") {
    showError("email", "emailError", "Email address is required.");
    isValid = false;
  } else if (!emailRegex.test(email)) {
    showError("email", "emailError", "Please enter a valid email address (e.g. name@domain.com).");
    isValid = false;
  } else {
    showSuccess("email");
  }

  // 4. Course / Department Selection
  const course = document.getElementById("course").value;
  if (course === "") {
    showError("course", "courseError", "Please select an admitted course / branch.");
    isValid = false;
  } else {
    showSuccess("course");
  }

  // 5. Password Strength Validation
  const password = document.getElementById("password").value;
  if (password === "") {
    showError("password", "passwordError", "Password is required.");
    isValid = false;
  } else if (!isPasswordStrong) {
    showError("password", "passwordError", "Password must be at least 'Medium' or 'Strong'.");
    isValid = false;
  } else {
    showSuccess("password");
  }

  // 6. Confirm Password Validation
  const confirmPassword = document.getElementById("confirmPassword").value;
  if (confirmPassword === "") {
    showError("confirmPassword", "confirmPasswordError", "Please confirm your password.");
    isValid = false;
  } else if (password !== confirmPassword) {
    showError("confirmPassword", "confirmPasswordError", "Passwords do not match.");
    isValid = false;
  } else {
    showSuccess("confirmPassword");
  }

  // 7. Profile Photo Validation
  const profileImg = document.getElementById("profileImage");
  if (profileImg.files.length === 0) {
    showError("profileImage", "profileError", "Profile photograph is required.");
    isValid = false;
  }

  // 8. ID Proof Validation
  const idProof = document.getElementById("idProof");
  if (idProof.files.length === 0) {
    showError("idProof", "idProofError", "Identity proof document is required.");
    isValid = false;
  }

  // 9. Terms and Conditions Checkbox
  const termsCheck = document.getElementById("termsCheck");
  if (!termsCheck.checked) {
    document.getElementById("termsError").innerText = "You must agree to the declaration.";
    isValid = false;
  }

  // If any field is invalid, alert user and focus first error
  const alertBox = document.getElementById("alertBox");
  if (!isValid) {
    alertBox.className = "alert-box alert-danger";
    alertBox.innerText = "Please correct the highlighted errors before submitting.";
    const firstInvalid = document.querySelector(".input-error");
    if (firstInvalid) firstInvalid.focus();
    return false;
  }

  // Submission Success
  alertBox.className = "alert-box alert-success";
  alertBox.innerText = "Application form submitted and verified successfully!";

  // Populate preview summary card
  document.getElementById("resName").innerText = fullName;
  document.getElementById("resRoll").innerText = rollNo.toUpperCase();
  document.getElementById("resEmail").innerText = email;
  document.getElementById("resCourse").innerText = course;
  document.getElementById("resPhoto").innerText = profileImg.files[0].name + " (" + (profileImg.files[0].size / 1024).toFixed(1) + " KB)";
  document.getElementById("resDoc").innerText = idProof.files[0].name + " (" + (idProof.files[0].size / 1024).toFixed(1) + " KB)";
  
  document.getElementById("summaryCard").style.display = "block";
  document.getElementById("summaryCard").scrollIntoView({ behavior: 'smooth' });

  return false;
}

/**
 * Real-time Password Strength Meter Calculation
 */
function checkPasswordStrength() {
  const password = document.getElementById("password").value;
  const strengthBar = document.getElementById("strengthBar");
  const strengthText = document.getElementById("strengthText");
  const passwordError = document.getElementById("passwordError");

  if (password.length === 0) {
    strengthBar.style.width = "0%";
    strengthBar.style.backgroundColor = "#e2e8f0";
    strengthText.innerText = "Password Strength: None";
    strengthText.style.color = "#64748b";
    isPasswordStrong = false;
    return;
  }

  let score = 0;

  // Criteria checks
  if (password.length >= 8) score++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
  if (/\d/.test(password)) score++;
  if (/[^A-Za-z0-9]/.test(password)) score++;

  if (password.length < 8) {
    strengthBar.style.width = "25%";
    strengthBar.style.backgroundColor = "#e74c3c";
    strengthText.innerText = "Password Strength: Weak (Too short, min 8 characters)";
    strengthText.style.color = "#e74c3c";
    isPasswordStrong = false;
  } else if (score <= 2) {
    strengthBar.style.width = "40%";
    strengthBar.style.backgroundColor = "#e67e22";
    strengthText.innerText = "Password Strength: Fair (Add numbers & special symbols)";
    strengthText.style.color = "#e67e22";
    isPasswordStrong = false;
  } else if (score === 3) {
    strengthBar.style.width = "75%";
    strengthBar.style.backgroundColor = "#f39c12";
    strengthText.innerText = "Password Strength: Medium (Good password)";
    strengthText.style.color = "#d68910";
    isPasswordStrong = true;
    passwordError.innerText = "";
  } else {
    strengthBar.style.width = "100%";
    strengthBar.style.backgroundColor = "#27ae60";
    strengthText.innerText = "Password Strength: Strong (Excellent!)";
    strengthText.style.color = "#27ae60";
    isPasswordStrong = true;
    passwordError.innerText = "";
  }

  checkPasswordMatch();
}

/**
 * Real-time Password Matching Checker
 */
function checkPasswordMatch() {
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;
  const confirmError = document.getElementById("confirmPasswordError");

  if (confirmPassword.length === 0) {
    confirmError.innerText = "";
    isPasswordMatched = false;
    return;
  }

  if (password !== confirmPassword) {
    confirmError.innerText = "Passwords do not match.";
    confirmError.style.color = "#e74c3c";
    document.getElementById("confirmPassword").className = "input-error";
    isPasswordMatched = false;
  } else {
    confirmError.innerText = "Passwords match!";
    confirmError.style.color = "#27ae60";
    document.getElementById("confirmPassword").className = "input-success";
    isPasswordMatched = true;
  }
}

/**
 * File size and MIME type validation
 */
function validateFile(inputId, maxMB, allowedMimes, errorSpanId) {
  const fileInput = document.getElementById(inputId);
  const errorSpan = document.getElementById(errorSpanId);

  if (fileInput.files.length === 0) return;

  const file = fileInput.files[0];
  const maxBytes = maxMB * 1024 * 1024;

  if (!allowedMimes.includes(file.type)) {
    errorSpan.innerText = `Invalid file format (${file.type || 'unknown'}). Allowed: ${allowedMimes.map(m => m.split('/')[1]).join(', ')}`;
    fileInput.value = "";
    fileInput.className = "input-error";
    return;
  }

  if (file.size > maxBytes) {
    errorSpan.innerText = `File is too large (${(file.size / (1024 * 1024)).toFixed(2)} MB). Max limit: ${maxMB} MB.`;
    fileInput.value = "";
    fileInput.className = "input-error";
    return;
  }

  errorSpan.innerText = `Accepted: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
  errorSpan.style.color = "#27ae60";
  fileInput.className = "input-success";
}

/**
 * UI Helper: Show Error
 */
function showError(inputId, errorSpanId, message) {
  const input = document.getElementById(inputId);
  const errorSpan = document.getElementById(errorSpanId);
  input.classList.remove("input-success");
  input.classList.add("input-error");
  errorSpan.innerText = message;
  errorSpan.style.color = "#e74c3c";
}

/**
 * UI Helper: Show Success
 */
function showSuccess(inputId) {
  const input = document.getElementById(inputId);
  input.classList.remove("input-error");
  input.classList.add("input-success");
}

/**
 * Clear all error messages and input classes
 */
function clearAllErrors() {
  const errors = document.querySelectorAll(".error-text");
  errors.forEach(el => el.innerText = "");
  const inputs = document.querySelectorAll("input, select");
  inputs.forEach(el => {
    el.classList.remove("input-error");
    el.classList.remove("input-success");
  });
}

/**
 * Reset form action
 */
function resetValidation() {
  clearAllErrors();
  document.getElementById("alertBox").className = "alert-box";
  document.getElementById("alertBox").innerText = "";
  document.getElementById("summaryCard").style.display = "none";
  document.getElementById("strengthBar").style.width = "0%";
  document.getElementById("strengthText").innerText = "Password Strength: None";
  document.getElementById("strengthText").style.color = "#64748b";
}
