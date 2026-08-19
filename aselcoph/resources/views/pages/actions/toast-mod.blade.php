<!-- Notification Message -->
<div id="notification-container"
    style="position: fixed; top: 15px; left: 50%; transform: translateX(-50%); z-index: 1000;"></div>

<style>
    /* Notification Styles */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOutUp {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }

    .notification {
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;
        opacity: 1;
        transition: opacity 0.5s ease-out, transform 0.5s ease-out;
    }
</style>

<script>
    function ktoast_alert(type, icon, response) {

        // Create a new notification element
        let notificationContainer = document.getElementById("notification-container");
        let notification = document.createElement("div");

        notification.className =
            `notification alert alert-${type} alert-dismissible fade show custom-alert-icon shadow-sm flex items-center `;
        notification.innerHTML = `
            <div class="text-dark" style="padding: 8px; padding-left: 3px;">
                <p class="text-sm text-gray-700 text-dark dark:text-textmuted/50"> <span class="bi bi-${icon} px-1 text-${type}"></span> ${response} </p>
            </div>
        `;

        // Append new notification to the container
        notificationContainer.appendChild(notification);

        // Apply fade-in animation
        notification.style.animation = "fadeInDown 0.5s forwards";

        // Remove after timeout
        setTimeout(() => {
            notification.style.animation = "fadeOutUp 0.5s forwards";
            setTimeout(() => {
                notification.remove();
            }, 500); // Wait for fade-out animation to finish
        }, 5000); // Keep visible for 2.5 seconds
    }

    function copyPassword() {
        const passwordField = document.getElementById('password');
        passwordField.select();
        document.execCommand('copy');
        ktoast_alert('success', 'check-circle-fill', 'Password Copied to Clipboard!')
    }

    function generatePassword() {
        const length = 12;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
        let newPassword = "";
        for (let i = 0, n = charset.length; i < length; ++i) {
            newPassword += charset.charAt(Math.floor(Math.random() * n));
        }
        document.getElementById('password').value = newPassword;
    }
</script>
