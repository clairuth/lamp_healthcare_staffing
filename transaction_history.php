<?php
// templates/payments/transaction_history.php

$page_title = "Transaction History - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in()) {
    display_error_message("Access Denied: You must be logged in to view your transaction history.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$user_id = get_current_user_id();
$user_type = get_current_user_type();

?>

<div class="container mt-4">
    <h1 class="mb-4">Payment & Transaction History</h1>

    <div id="messageContainerGlobal"></div>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="transactionTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="payments-made-tab" data-toggle="tab" href="#payments-made" role="tab" aria-controls="payments-made" aria-selected="true">Payments Made/Received</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="escrow-status-tab" data-toggle="tab" href="#escrow-status" role="tab" aria-controls="escrow-status" aria-selected="false">Escrow Status</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="transactionTabsContent">
                <div class="tab-pane fade show active" id="payments-made" role="tabpanel" aria-labelledby="payments-made-tab">
                    <h5 class="mb-3">Completed Transactions</h5>
                    <div id="paymentsMadeContainer" class="table-responsive">
                        <div class="loader-container text-center">
                            <div class="loader"></div>
                            <p>Loading completed transactions...</p>
                        </div>
                        <!-- Completed transactions table will be loaded here -->
                    </div>
                </div>
                <div class="tab-pane fade" id="escrow-status" role="tabpanel" aria-labelledby="escrow-status-tab">
                    <h5 class="mb-3">Current Escrowed Payments</h5>
                     <div id="escrowStatusContainer" class="table-responsive">
                        <div class="loader-container text-center">
                            <div class="loader"></div>
                            <p>Loading escrow statuses...</p>
                        </div>
                        <!-- Escrow statuses table will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const paymentsMadeContainer = document.getElementById("paymentsMadeContainer");
    const escrowStatusContainer = document.getElementById("escrowStatusContainer");

    async function loadCompletedTransactions() {
        paymentsMadeContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading completed transactions...</p></div>`;
        try {
            // Endpoint needs to differentiate for professional vs facility if necessary, or return all related
            const response = await fetchData("<?php echo base_url("payment/history/". $user_id); ?>"); 
            paymentsMadeContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                const table = document.createElement("table");
                table.className = "table table-hover table-striped";
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>From/To</th>
                            <th>Shift ID</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `;
                const tbody = table.querySelector("tbody");
                response.data.forEach(txn => {
                    const tr = document.createElement("tr");
                    let description = "Payment for Shift";
                    let fromTo = "N/A";
                    if(txn.shift_title) description += ` \"${sanitizeHTML(txn.shift_title)}\"`;
                    
                    if (txn.payer_username && txn.payee_username) {
                        fromTo = `<?php echo $user_type === "facility" ? "To: " : "From: "; ?> 
                                  ${sanitizeHTML(<?php echo $user_type === "facility" ? "txn.payee_username" : "txn.payer_username"; ?>)}`;
                    }

                    tr.innerHTML = `
                        <td>${formatDateTime(txn.transaction_date || txn.created_at, false)}</td>
                        <td>${description}</td>
                        <td>$${parseFloat(txn.amount).toFixed(2)} ${sanitizeHTML(txn.currency_code || "USD")}</td>
                        <td><span class="badge bg-${getPaymentStatusClass(txn.status)}">${sanitizeHTML(txn.status)}</span></td>
                        <td>${fromTo}</td>
                        <td>${txn.shift_id ? `<a href="<?php echo base_url("shift/view/"); ?>${txn.shift_id}">${txn.shift_id}</a>` : "N/A"}</td>
                        <td><a href="<?php echo base_url("payment/view/"); ?>${txn.id}" class="btn btn-sm btn-outline-info">Details</a></td>
                    `;
                    tbody.appendChild(tr);
                });
                paymentsMadeContainer.appendChild(table);
            } else if (response.status === "success" && (!response.data || response.data.length === 0)) {
                 paymentsMadeContainer.innerHTML = "<p>No completed transactions found.</p>";
            } else {
                paymentsMadeContainer.innerHTML = `<p class="text-danger">Could not load transaction history. ${response.message || "Please try again later."}</p>`;
            }
        } catch (error) {
            console.error("Error fetching completed transactions:", error);
            paymentsMadeContainer.innerHTML = "<p class=\"text-danger\">Could not load transaction history. Please try again later.</p>";
        }
    }

    async function loadEscrowStatuses() {
        escrowStatusContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading escrow statuses...</p></div>`;
        try {
            const response = await fetchData("<?php echo base_url("payment/escrowStatus/". $user_id); ?>"); 
            escrowStatusContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                const table = document.createElement("table");
                table.className = "table table-hover table-striped";
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Shift</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Release Date (Est.)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `;
                const tbody = table.querySelector("tbody");
                response.data.forEach(escrow => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${escrow.shift_title ? `<a href="<?php echo base_url("shift/view/"); ?>${escrow.shift_id}">${sanitizeHTML(escrow.shift_title)}</a>` : "N/A"}</td>
                        <td>$${parseFloat(escrow.amount).toFixed(2)} ${sanitizeHTML(escrow.currency_code || "USD")}</td>
                        <td><span class="badge bg-warning">${sanitizeHTML(escrow.status)}</span></td>
                        <td>${escrow.expected_release_date ? formatDateTime(escrow.expected_release_date, false) : "N/A"}</td>
                        <td>
                            <a href="<?php echo base_url("payment/view/"); ?>${escrow.payment_id}" class="btn btn-sm btn-outline-info">View Payment</a>
                            ${escrow.status === "pending_release" && "<?php echo $user_type; ?>" === "professional" ? 
                                `<button class="btn btn-sm btn-outline-success ml-1 request-early-release-btn" data-payment-id="${escrow.payment_id}">Request Early Release</button>` : ""}
                            ${escrow.status === "disputed" ? `<span class="badge bg-danger">Disputed</span>` : ""}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                escrowStatusContainer.appendChild(table);
                addEscrowActionListeners();
            } else if (response.status === "success" && (!response.data || response.data.length === 0)) {
                 escrowStatusContainer.innerHTML = "<p>No payments currently in escrow.</p>";
            } else {
                escrowStatusContainer.innerHTML = `<p class="text-danger">Could not load escrow statuses. ${response.message || "Please try again later."}</p>`;
            }
        } catch (error) {
            console.error("Error fetching escrow statuses:", error);
            escrowStatusContainer.innerHTML = "<p class=\"text-danger\">Could not load escrow statuses. Please try again later.</p>";
        }
    }
    
    function addEscrowActionListeners() {
        document.querySelectorAll(".request-early-release-btn").forEach(button => {
            button.addEventListener("click", async function() {
                const paymentId = this.dataset.paymentId;
                if (confirm("Are you sure you want to request an early release for this payment? The facility will be notified.")) {
                    // API call to request early release
                    displayMessage("info", "Early release request functionality to be implemented.", "messageContainerGlobal");
                }
            });
        });
    }

    function getPaymentStatusClass(status) {
        status = status.toLowerCase();
        if (status === "completed" || status === "paid" || status === "released") return "success";
        if (status === "pending" || status === "processing" || status === "in_escrow") return "warning";
        if (status === "failed" || status === "cancelled" || status === "refunded") return "danger";
        return "secondary";
    }

    // Initial load for the active tab
    loadCompletedTransactions();

    // Handle tab switching
    const tabs = document.querySelectorAll("#transactionTabs .nav-link");
    tabs.forEach(tab => {
        tab.addEventListener("shown.bs.tab", function(event) {
            if (event.target.id === "payments-made-tab") {
                loadCompletedTransactions();
            } else if (event.target.id === "escrow-status-tab") {
                loadEscrowStatuses();
            }
        });
    });
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
