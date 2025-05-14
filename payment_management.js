// assets/js/payment_management.js

document.addEventListener("DOMContentLoaded", function() {
    const paymentMethodsContainer = document.getElementById("paymentMethodsContainer");
    const addPaymentMethodForm = document.getElementById("addPaymentMethodForm");
    const paymentMethodTypeSelect = document.getElementById("payment_method_type");
    const paymentMethodDetailsInput = document.getElementById("payment_method_details");
    const paymentMethodDetailsLabel = document.querySelector("label[for=\"payment_method_details\"]");

    if (paymentMethodsContainer) {
        loadUserPaymentMethods();
    }

    if (paymentMethodTypeSelect && paymentMethodDetailsInput && paymentMethodDetailsLabel) {
        paymentMethodTypeSelect.addEventListener("change", function() {
            const selectedType = this.value;
            let placeholder = "Enter details";
            let labelText = "Details";
            let inputType = "text";

            switch (selectedType) {
                case "PayPal":
                    placeholder = "your.paypal.email@example.com";
                    labelText = "PayPal Email";
                    inputType = "email";
                    break;
                case "CashApp":
                    placeholder = "$YourCashtag";
                    labelText = "CashApp $Cashtag";
                    break;
                case "Coinbase":
                    placeholder = "your.coinbase.email@example.com";
                    labelText = "Coinbase Email";
                    inputType = "email";
                    break;
                case "Zelle":
                    placeholder = "zelle.email@example.com or 123-456-7890";
                    labelText = "Zelle Email or Phone";
                    break;
            }
            paymentMethodDetailsInput.placeholder = placeholder;
            paymentMethodDetailsInput.type = inputType;
            paymentMethodDetailsLabel.textContent = labelText;
        });
    }

    if (addPaymentMethodForm) {
        addPaymentMethodForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!addPaymentMethodForm.checkValidity()) {
                addPaymentMethodForm.classList.add("was-validated");
                displayMessage("danger", "Please correct the errors in the form.", "messageContainerPaymentMethods");
                return;
            }

            const formData = new FormData(addPaymentMethodForm);
            const submitButton = addPaymentMethodForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...`;
            submitButton.disabled = true;

            try {
                const response = await fetchData(base_url("payment/addMethod"), {
                    method: "POST",
                    body: formData
                });

                if (response.status === "success") {
                    displayMessage("success", response.message || "Payment method added successfully!", "messageContainerPaymentMethods");
                    addPaymentMethodForm.reset();
                    addPaymentMethodForm.classList.remove("was-validated");
                    paymentMethodTypeSelect.dispatchEvent(new Event("change")); // Reset placeholder
                    loadUserPaymentMethods(); // Refresh the list
                } else {
                    displayMessage("danger", response.message || "Failed to add payment method.", "messageContainerPaymentMethods");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred.", "messageContainerPaymentMethods");
            } finally {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }
});

async function loadUserPaymentMethods() {
    const container = document.getElementById("paymentMethodsContainer");
    if (!container) return;

    container.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading payment methods...</p></div>`;
    try {
        const response = await fetchData(base_url("payment/listMethods")); // Assumes current user context
        container.innerHTML = ""; // Clear loader

        if (response.status === "success" && response.data && response.data.length > 0) {
            const ul = document.createElement("ul");
            ul.className = "list-group payment-methods-list";
            response.data.forEach(method => {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center mb-2 shadow-sm";
                
                let iconClass = "fas fa-credit-card"; // Default icon
                if (method.method_type === "PayPal") iconClass = "fab fa-paypal";
                if (method.method_type === "CashApp") iconClass = "fas fa-dollar-sign"; // No specific CashApp icon in FA free
                if (method.method_type === "Coinbase") iconClass = "fab fa-bitcoin"; // Generic crypto
                if (method.method_type === "Zelle") iconClass = "fas fa-university"; // Generic bank/transfer

                li.innerHTML = `
                    <div>
                        <h5 class="mb-1"><i class="${iconClass} mr-2"></i> ${sanitizeHTML(method.method_type)} ${method.is_default == 1 ? "<span class=\"badge bg-primary ml-2\">Default</span>" : ""}</h5>
                        <p class="mb-1 text-muted">${sanitizeHTML(method.details)}</p>
                        <small>Added: ${formatDate(method.created_at)}</small>
                    </div>
                    <div>
                        ${method.is_default == 0 ? `<button class="btn btn-sm btn-outline-success mr-2 set-default-pm-btn" data-id="${method.id}">Set as Default</button>` : ""}
                        <button class="btn btn-sm btn-outline-danger delete-pm-btn" data-id="${method.id}">Delete</button>
                    </div>
                `;
                ul.appendChild(li);
            });
            container.appendChild(ul);
            addPaymentMethodActionListeners();
        } else if (response.status === "success" && (!response.data || response.data.length === 0)) {
            container.innerHTML = "<p>You have not added any payment methods yet.</p>";
        } else {
            container.innerHTML = `<p class="text-danger">Could not load payment methods. ${response.message || "Please try again."}</p>`;
        }
    } catch (error) {
        console.error("Error fetching payment methods:", error);
        container.innerHTML = "<p class=\"text-danger\">Could not load payment methods. Please try again later.</p>";
    }
}

function addPaymentMethodActionListeners() {
    document.querySelectorAll(".delete-pm-btn").forEach(button => {
        button.addEventListener("click", async function() {
            const methodId = this.dataset.id;
            if (confirm("Are you sure you want to delete this payment method?")) {
                try {
                    const response = await fetchData(base_url("payment/deleteMethod/") + methodId, {
                        method: "POST", 
                        body: JSON.stringify({ csrf_token: document.querySelector("input[name=\"csrf_token\"]") ? document.querySelector("input[name=\"csrf_token\"]").value : "" })
                    });
                    if (response.status === "success") {
                        displayMessage("success", response.message || "Payment method deleted successfully.", "messageContainerPaymentMethods");
                        loadUserPaymentMethods(); // Refresh list
                    } else {
                        displayMessage("danger", response.message || "Failed to delete payment method.", "messageContainerPaymentMethods");
                    }
                } catch (error) {
                    displayMessage("danger", error.message || "Error deleting payment method.", "messageContainerPaymentMethods");
                }
            }
        });
    });

    document.querySelectorAll(".set-default-pm-btn").forEach(button => {
        button.addEventListener("click", async function() {
            const methodId = this.dataset.id;
            try {
                const response = await fetchData(base_url("payment/setDefaultMethod/") + methodId, {
                    method: "POST",
                    body: JSON.stringify({ csrf_token: document.querySelector("input[name=\"csrf_token\"]") ? document.querySelector("input[name=\"csrf_token\"]").value : "" })
                });
                if (response.status === "success") {
                    displayMessage("success", response.message || "Payment method set as default.", "messageContainerPaymentMethods");
                    loadUserPaymentMethods(); // Refresh list
                } else {
                    displayMessage("danger", response.message || "Failed to set default payment method.", "messageContainerPaymentMethods");
                }
            } catch (error) {
                displayMessage("danger", error.message || "Error setting default payment method.", "messageContainerPaymentMethods");
            }
        });
    });
}

// Ensure displayMessage, sanitizeHTML, formatDate are available (e.g., from main.js)
// const base_url = function(path = "") { return document.documentElement.dataset.baseUrl + path; };

