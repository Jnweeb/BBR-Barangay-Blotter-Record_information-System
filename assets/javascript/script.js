// General password toggle for multiple forms
document.addEventListener("DOMContentLoaded", () => {

    const passwordWrappers = document.querySelectorAll(".password-wrapper");

    passwordWrappers.forEach(wrapper => {
        const passField = wrapper.querySelector("input[type='password'], input[type='text']");
        const toggleIcon = wrapper.querySelector(".toggle-password");

        if (!passField || !toggleIcon) return;

        toggleIcon.addEventListener("click", () => {
            if (passField.type === "password") {
                passField.type = "text";
                toggleIcon.src = toggleIcon.dataset.eyeSlash; // use data attribute for paths
            } else {
                passField.type = "password";
                toggleIcon.src = toggleIcon.dataset.eye;
            }
        });
    });

});
