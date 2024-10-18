// main button clicks
const mainBar = document.getElementsByClassName("mainBar");
const coursesMain = document.getElementsByClassName("coursesMain")[0];
const aboutMain = document.getElementsByClassName("aboutMain")[0];
const homeMain = document.getElementsByClassName("homeMain")[0];

if (mainBar.length > 0) {
    const mainCourses = mainBar[0].addEventListener("click", function () {
        coursesMain.style.display = 'flex';
        aboutMain.style.display = 'none';
        homeMain.style.display = 'none';
    });
    const mainAbout = mainBar[1].addEventListener("click", function () {
        coursesMain.style.display = 'none';
        aboutMain.style.display = 'flex';
        homeMain.style.display = 'none';
    });
    const mainHome = mainBar[2].addEventListener("click", function () {
        coursesMain.style.display = 'none';
        aboutMain.style.display = 'none';
        homeMain.style.display = 'flex';
    });
}

addEventListener("DOMContentLoaded", (event) => {
    const buttons = document.getElementsByClassName("getStartedBtn");
    Array.from(buttons).forEach(button => {
        button.addEventListener('click', function () {
            console.log('Clicked!');
            window.location.assign("./login.html");
        });
    });
    BindRegisterEvents();
    BindLoginEvents();
    BindOtpEvents();
});

function BindLoginEvents() {
    const continueButton = document.getElementsByClassName("btnContinue")[0];
    const mobile = document.getElementById('txtMobile');
    if (continueButton != undefined) {
        continueButton.addEventListener('click', function () {
            if (mobile != undefined) {
                GetOtp(mobile.value);
            }
        });
    }
}

async function GetOtp(mobile) {
    let url = `/otp`
    const body = JSON.stringify({ mobile: mobile });

    try {
        const response = await CallApi(url, body);
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        localStorage.setItem('mob', mobile);
        window.location.assign("./otp.html");

    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

// register page functionality
function BindRegisterEvents() {
    const registerButton = document.getElementById("btnContinue");
    if (registerButton != undefined) {
        registerButton.addEventListener('click', function () {
            const firstName = document.getElementsByClassName("firstName")[0];
            const lastName = document.getElementsByClassName("lastName")[0];
            const mobileNumber = document.getElementsByClassName("mobileNumber")[0];
            const city = document.getElementsByClassName("city")[0];
            const emailAddress = document.getElementsByClassName("emailAddress")[0];
            let isValid = true;

            // Check first name
            if (!firstName.value.trim()) {
                firstName.focus();
                isValid = false;
                return;
            }

            // Check last name
            if (!lastName.value.trim()) {
                lastName.focus();
                isValid = false;
                return;
            }

            // Check mobile number
            if (!mobileNumber.value.trim()) {
                mobileNumber.focus();
                isValid = false;
                return;
            }

            // Check city
            if (!city.value.trim()) {
                city.focus();
                isValid = false;
                return;
            }

            // Check email address
            if (!emailAddress.value.trim()) {
                emailAddress.focus();
                isValid = false;
                return;
            }

            // If everything is filled
            if (isValid) {
                window.location.assign("./app/dashboard.html");
            }
        });
    }
}

//Otp page functionality
function BindOtpEvents() {
    const otpInputs = document.querySelectorAll('.otpInput');
    if (otpInputs.length > 0) {
        otpInputs[0].focus();
        // Add event listeners to each input
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function () {
                // Allow only digits (0-9)
                if (!/^\d$/.test(input.value)) {
                    input.value = '';
                }

                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && input.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
                otpInputs.forEach(input => {
                    input.classList.remove('error');
                });
            });
        });

        const verifyButton = document.getElementById('btnVerify');
        if (verifyButton != undefined) {
            verifyButton.addEventListener('click', function () {
                if (Validate()) {
                    let otpValue = '';

                    Array.from(otpInputs).forEach(otpVal => {
                        otpValue += otpVal.value;
                    });

                    const mobile = localStorage.getItem('mob');
                    console.log(mobile);
                    if (mobile != undefined) {
                        CallL(mobile, otpValue);
                    }
                }
            });
        }
    }

    function Validate() {
        let allFilled = true;

        Array.from(otpInputs).forEach(otp => {
            if (!otp.value.trim() && allFilled) {
                otp.focus();
                allFilled = false;
            }
        });

        return allFilled;
    }

    async function CallL($mobile, $otp){
        let url = `/login`
        const body = JSON.stringify({ mobile: $mobile, otp: $otp });
        
        try {
            const response = await CallApi(url, body);
            console.log(response)
            if (response.status >= 400) {
                console.error(`Error: ${response.status} - ${response.data.message}`);
                if(response.data.message === 'Invalid otp'){
                    IncorrectOtpAnim();
                }
                return;
            }

            await CallApi(`/redirect`, null, 'GET');
            // await CallApi(`/courses`, body);
            // localStorage.removeItem('mob');
            // window.location.assign("./app/dashboard.html");
    
        } catch (error) {
            console.error('Error fetching login:', error);
            return null;
        }
    }

    function IncorrectOtpAnim(){
        otpInputs.forEach(input => {
            input.classList.add('error', 'shake');
        });
        
        // Remove shake animation after it's done
        setTimeout(() => {
            otpInputs.forEach(input => {
                input.classList.remove('shake');
            });
        }, 500);
    }
}