document.addEventListener('DOMContentLoaded', function () {
    const sendOtpButton = document.getElementById('send-otp');
    const verifyOtpButton = document.getElementById('verify-otp');
    const otpSection = document.getElementById('otp-section');
    const otpInput = document.getElementById('otp_code');
    const phoneInput = document.getElementById('phone_number');
    const statusOutput = document.getElementById('otp-status');
    const phoneVerifiedInput = document.getElementById('is_phone_verified');
    const submitButton = document.getElementById('register-submit');
    let currentOtp = null;

    function formatPhone(value) {
        return value.replace(/\D/g, '');
    }

    function createOtp() {
        return String(Math.floor(100000 + Math.random() * 900000));
    }

    if (!sendOtpButton || !verifyOtpButton || !otpInput || !phoneInput || !statusOutput || !phoneVerifiedInput || !submitButton) {
        return;
    }

    submitButton.disabled = true;

    sendOtpButton.addEventListener('click', function (event) {
        event.preventDefault();
        const rawPhone = formatPhone(phoneInput.value);

        if (rawPhone.length < 9) {
            statusOutput.textContent = 'Please enter a valid phone number before sending OTP.';
            statusOutput.classList.remove('success');
            return;
        }

        currentOtp = createOtp();
        otpSection.classList.remove('hidden');
        statusOutput.textContent = 'OTP sent to ' + phoneInput.value + '. Use the code from the popup to verify.';
        statusOutput.classList.add('success');
        phoneVerifiedInput.value = '0';
        submitButton.disabled = true;
        console.log('JBeauty simulated OTP:', currentOtp);
        alert('JBeauty simulated OTP has been sent to ' + phoneInput.value + ': ' + currentOtp);
    });

    verifyOtpButton.addEventListener('click', function (event) {
        event.preventDefault();

        if (!currentOtp) {
            statusOutput.textContent = 'Please request an OTP first.';
            statusOutput.classList.remove('success');
            return;
        }

        if (otpInput.value.trim() === currentOtp) {
            phoneVerifiedInput.value = '1';
            submitButton.disabled = false;
            statusOutput.textContent = 'Phone number verified successfully. You may submit the registration form.';
            statusOutput.classList.add('success');
            otpInput.value = '';
            currentOtp = null;
        } else {
            phoneVerifiedInput.value = '0';
            submitButton.disabled = true;
            statusOutput.textContent = 'Incorrect OTP. Please try again or send a new code.';
            statusOutput.classList.remove('success');
        }
    });
});
