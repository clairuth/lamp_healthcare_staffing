<?php
// templates/payments/manage_payment_methods.php

$page_title = "Manage Payment Methods - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in()) {
    display_error_message("Access Denied: You must be logged in to manage payment methods.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$user_id = get_current_user_id();
$user_type = get_current_user_type(); // To tailor options if needed

// Payment methods will be loaded and managed via JavaScript for a dynamic experience

?>

<div class="container mt-4">
    <h1 class="mb-4">Manage Your Payment Methods</h1>

    <div id="messageContainerGlobal"></div>

    <div class="card mb-4">
        <div class="card-header">Add New Payment Method</div>
        <div class="card-body">
            <form id="addPaymentMethodForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                
                <div class="form-group">
                    <label for="payment_method_type">Payment Method Type</label>
                    <select class="form-control" id="payment_method_type" name="payment_method_type" required>
                        <option value="">Select Type...</option>
                        <option value="paypal">PayPal</option>
                        <option value="cashapp">CashApp</option>
                        <option value="coinbase">Coinbase (Crypto)</option>
                        <option value="zelle">Zelle</option>
                        <option value="bank_account">Bank Account (Direct Deposit)</option>
                    </select>
                </div>

                <!-- PayPal Fields -->
                <div id="paypal_fields" class="payment-method-fields" style="display:none;">
                    <div class="form-group">
                        <label for="paypal_email">PayPal Email or Username (e.g., @cubeloid)</label>
                        <input type="text" class="form-control" id="paypal_email" name="paypal_email">
                    </div>
                </div>

                <!-- CashApp Fields -->
                <div id="cashapp_fields" class="payment-method-fields" style="display:none;">
                    <div class="form-group">
                        <label for="cashapp_cashtag">CashApp $Cashtag (e.g., @clairuth)</label>
                        <input type="text" class="form-control" id="cashapp_cashtag" name="cashapp_cashtag">
                    </div>
                </div>

                <!-- Coinbase Fields -->
                <div id="coinbase_fields" class="payment-method-fields" style="display:none;">
                    <div class="form-group">
                        <label for="coinbase_email">Coinbase Email (e.g., cubeloid@gmail.com)</label>
                        <input type="email" class="form-control" id="coinbase_email" name="coinbase_email">
                    </div>
                    <div class="form-group">
                        <label for="crypto_address">Preferred Crypto for Payout (Optional, e.g., BTC, ETH, USDC)</label>
                        <input type="text" class="form-control" id="crypto_address" name="crypto_address" placeholder="e.g., BTC or your USDC address">
                    </div>
                </div>

                <!-- Zelle Fields -->
                <div id="zelle_fields" class="payment-method-fields" style="display:none;">
                    <div class="form-group">
                        <label for="zelle_email_or_phone">Zelle Registered Email or Phone Number</label>
                        <input type="text" class="form-control" id="zelle_email_or_phone" name="zelle_email_or_phone">
                    </div>
                </div>
                
                <!-- Bank Account Fields -->
                <div id="bank_account_fields" class="payment-method-fields" style="display:none;">
                    <div class="form-group">
                        <label for="account_holder_name">Account Holder Name</label>
                        <input type="text" class="form-control" id="account_holder_name" name="account_holder_name">
                    </div>
                    <div class="form-group">
                        <label for="routing_number">Routing Number</label>
                        <input type="text" class="form-control" id="routing_number" name="routing_number">
                    </div>
                    <div class="form-group">
                        <label for="account_number">Account Number</label>
                        <input type="text" class="form-control" id="account_number" name="account_number">
                    </div>
                     <div class="form-group">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="is_default">Set as Default Payment Method</label>
                    <select class="form-control" id="is_default" name="is_default">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Add Payment Method</button>
            </form>
        </div>
    </div>

    <h2 class="mt-5 mb-3">My Saved Payment Methods</h2>
    <div id="paymentMethodsListContainer">
        <div class="loader-container text-center">
            <div class="loader"></div>
            <p>Loading your payment methods...</p>
        </div>
        <!-- Payment methods will be loaded here by JavaScript -->
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const addPaymentMethodForm = document.getElementById("addPaymentMethodForm");
    const paymentMethodsListContainer = document.getElementById("paymentMethodsListContainer");
    const paymentMethodTypeSelect = document.getElementById("payment_method_type");

    function togglePaymentMethodFields() {
        document.querySelectorAll(".payment-method-fields").forEach(div => div.style.display = "none");
        const selectedType = paymentMethodTypeSelect.value;
        if (selectedType) {
            const fieldsDiv = document.getElementById(selectedType + "_fields");
            if (fieldsDiv) {
                fieldsDiv.style.display = "block";
                // Set required attributes dynamically if needed, or handle in backend
                fieldsDiv.querySelectorAll("input, select").forEach(input => {
                    // Example: make first input required
                    // if (input.name.includes(selectedType)) input.required = true;
                });
            }
        }
    }

    if (paymentMethodTypeSelect) {
        paymentMethodTypeSelect.addEventListener("change", togglePaymentMethodFields);
    }

    async function loadPaymentMethods() {
        paymentMethodsListContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading your payment methods...</p></div>`;
        try {
            const response = await fetchData("<?php echo base_url("payment/listMethods"); ?>"); 
            paymentMethodsListContainer.innerHTML = ""; 

            if (response.status === "success" && response.data && response.data.length > 0) {
                const listGroup = document.createElement("ul");
                listGroup.className = "list-group";
                response.data.forEach(method => {
                    const listItem = document.createElement("li");
                    listItem.className = "list-group-item d-flex justify-content-between align-items-center";
                    let details = "";
                    switch(method.method_type) {
                        case "paypal": details = `PayPal: ${sanitizeHTML(method.details.email || method.details.paypal_email || "N/A")}` ; break;
                        case "cashapp": details = `CashApp: ${sanitizeHTML(method.details.cashtag || method.details.cashapp_cashtag || "N/A")}`; break;
                        case "coinbase": details = `Coinbase: ${sanitizeHTML(method.details.email || method.details.coinbase_email || "N/A")}` + (method.details.crypto_address ? ` (${sanitizeHTML(method.details.crypto_address)})` : ""); break;
                        case "zelle": details = `Zelle: ${sanitizeHTML(method.details.email_or_phone || method.details.zelle_email_or_phone || "N/A")}`; break;
                        case "bank_account": details = `Bank: ...${sanitizeHTML(method.details.account_number_last4 || (method.details.account_number ? method.details.account_number.slice(-4) : "XXXX"))} (${sanitizeHTML(method.details.bank_name || "N/A")})`; break;
                        default: details = "Unknown method";
                    }
                    listItem.innerHTML = `
                        <span>
                            <strong>${sanitizeHTML(method.method_type.replace("_", " ").toUpperCase())}</strong> - ${details}
                            ${method.is_default == 1 ? "<span class=\"badge bg-success ml-2\">Default</span>" : ""}
                        </span>
                        <div>
                            ${method.is_default != 1 ? `<button class="btn btn-sm btn-outline-success mr-2 set-default-btn" data-id="${method.id}">Set Default</button>` : ""}
                            <button class="btn btn-sm btn-danger delete-payment-method-btn" data-id="${method.id}">Delete</button>
                        </div>
                    `;
                    listGroup.appendChild(listItem);
                });
                paymentMethodsListContainer.appendChild(listGroup);
                addEventListenersForListItems();
            } else {
                paymentMethodsListContainer.innerHTML = "<p>You have not added any payment methods yet.</p>";
            }
        } catch (error) {
            console.error("Error fetching payment methods:", error);
            paymentMethodsListContainer.innerHTML = "<p class=\"text-danger\">Could not load your payment methods. Please try again later.</p>";
        }
    }

    if (addPaymentMethodForm) {
        addPaymentMethodForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            // Basic form validation (Bootstrap handles some)
            if (!addPaymentMethodForm.checkValidity()) {
                addPaymentMethodForm.classList.add("was-validated");
                displayMessage("danger", "Please fill in all required fields for the selected payment type.", "messageContainerGlobal");
                return;
            }

            const formData = new FormData(addPaymentMethodForm);
            const data = {};
            data.method_type = formData.get("payment_method_type");
            data.is_default = formData.get("is_default");
            data.csrf_token = formData.get("csrf_token");
            data.details = {};

            document.querySelectorAll(`#${data.method_type}_fields input`).forEach(input => {
                if(input.name) data.details[input.name] = input.value;
            });

            const submitButton = addPaymentMethodForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 
            submitButton.disabled = true;

            try {
                const response = await fetchData("<?php echo base_url("payment/addMethod"); ?>", {
                    method: "POST",
                    body: JSON.stringify(data)
                });

                if (response.status === "success") {
                    displayMessage("success", response.message || "Payment method added successfully!", "messageContainerGlobal");
                    addPaymentMethodForm.reset();
                    addPaymentMethodForm.classList.remove("was-validated");
                    togglePaymentMethodFields(); // Reset fields visibility
                    loadPaymentMethods(); // Refresh the list
                } else {
                    displayMessage("danger", response.message || "Failed to add payment method. Please try again.", "messageContainerGlobal");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred.", "messageContainerGlobal");
            } finally {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }

    function addEventListenersForListItems() {
        document.querySelectorAll(".delete-payment-method-btn").forEach(button => {
            button.addEventListener("click", async function() {
                const methodId = this.dataset.id;
                if (confirm("Are you sure you want to delete this payment method?")) {
                    try {
                        const response = await fetchData("<?php echo base_url("payment/deleteMethod/"); ?>" + methodId, {
                            method: "POST", 
                            body: JSON.stringify({ csrf_token: "<?php echo htmlspecialchars(generate_csrf_token()); ?>" })
                        });
                        if (response.status === "success") {
                            displayMessage("success", response.message || "Payment method deleted.", "messageContainerGlobal");
                            loadPaymentMethods();
                        } else {
                            displayMessage("danger", response.message || "Failed to delete payment method.", "messageContainerGlobal");
                        }
                    } catch (error) {
                        displayMessage("danger", error.message || "Error deleting payment method.", "messageContainerGlobal");
                    }
                }
            });
        });

        document.querySelectorAll(".set-default-btn").forEach(button => {
            button.addEventListener("click", async function() {
                const methodId = this.dataset.id;
                 try {
                    const response = await fetchData("<?php echo base_url("payment/setDefaultMethod/"); ?>" + methodId, {
                        method: "POST",
                        body: JSON.stringify({ csrf_token: "<?php echo htmlspecialchars(generate_csrf_token()); ?>" })
                    });
                    if (response.status === "success") {
                        displayMessage("success", response.message || "Default payment method updated.", "messageContainerGlobal");
                        loadPaymentMethods();
                    } else {
                        displayMessage("danger", response.message || "Failed to set default method.", "messageContainerGlobal");
                    }
                } catch (error) {
                    displayMessage("danger", error.message || "Error setting default method.", "messageContainerGlobal");
                }
            });
        });
    }
    
    function sanitizeHTML(str) {
        if (!str) return "";
        const temp = document.createElement("div");
        temp.textContent = str;
        return temp.innerHTML;
    }

    // Initial load
    loadPaymentMethods();
    togglePaymentMethodFields(); // Initialize fields visibility based on current selection (if any)
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
