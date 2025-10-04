document.addEventListener("DOMContentLoaded", function () {
    // Color picker sync
    const themeColor = document.getElementById("theme_color");
    const themeColorText = document.getElementById("theme_color_text");
    const backgroundColor = document.getElementById("background_color");
    const backgroundColorText = document.getElementById(
        "background_color_text"
    );

    if (themeColor && themeColorText) {
        themeColor.addEventListener("input", function () {
            themeColorText.value = this.value;
        });
    }

    if (backgroundColor && backgroundColorText) {
        backgroundColor.addEventListener("input", function () {
            backgroundColorText.value = this.value;
        });
    }

    // Remove screenshot functionality
    const removeScreenshotBtn = document.getElementById("remove-screenshot");
    if (removeScreenshotBtn) {
        removeScreenshotBtn.addEventListener("click", function () {
            if (
                confirm(
                    "Are you sure you want to remove the current screenshot?"
                )
            ) {
                // Add a hidden input to indicate screenshot removal
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "remove_screenshot";
                hiddenInput.value = "1";
                document
                    .querySelector(".form-submit-event")
                    .appendChild(hiddenInput);

                // Hide the remove button and current screenshot info
                this.parentElement.style.display = "none";
                const currentScreenshotInfo =
                    document.querySelector(".text-success");
                if (currentScreenshotInfo) {
                    currentScreenshotInfo.style.display = "none";
                }
            }
        });
    }

    // PWA Installation handling
    let deferredPrompt;
    const installBtn = document.getElementById("install-btn");
    const reinstallBtn = document.getElementById("reinstall-btn");
    const installStatus = document.getElementById("install-status");

    if (installStatus) {
        // Check if app is already installed
        if (
            window.matchMedia("(display-mode: standalone)").matches ||
            window.navigator.standalone
        ) {
            installStatus.textContent = "Installed";
            installStatus.className = "badge bg-success";
            if (reinstallBtn) {
                reinstallBtn.style.display = "inline-block";
            }
        } else {
            installStatus.textContent = "Not Installed";
            installStatus.className = "badge bg-warning";
        }
    }

    // Listen for beforeinstallprompt event
    window.addEventListener("beforeinstallprompt", (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (installBtn) {
            installBtn.style.display = "inline-block";
        }
        if (installStatus) {
            installStatus.textContent = "Available for Install";
            installStatus.className = "badge bg-info";
        }
    });

    // Install button click
    if (installBtn) {
        installBtn.addEventListener("click", async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === "accepted") {
                    if (installStatus) {
                        installStatus.textContent = "Installed";
                        installStatus.className = "badge bg-success";
                    }
                    installBtn.style.display = "none";
                    if (reinstallBtn) {
                        reinstallBtn.style.display = "inline-block";
                    }
                }
                deferredPrompt = null;
            }
        });
    }
});
