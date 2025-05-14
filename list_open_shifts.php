<?php
// templates/shifts/list_open_shifts.php

$page_title = "Open Shifts - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

?>

<div class="container mt-4">
    <h1 class="mb-4">Available Shifts</h1>

    <div id="messageContainerGlobal"></div>

    <!-- Filters can be added here later (e.g., by date, location, specialty) -->
    <div class="row mb-3">
        <div class="col-md-4 form-group">
            <label for="filterDate">Date</label>
            <input type="date" id="filterDate" class="form-control">
        </div>
        <div class="col-md-4 form-group">
            <label for="filterSpecialty">Specialty</label>
            <select id="filterSpecialty" class="form-control">
                <option value="">All Specialties</option>
                <!-- Populate with actual specialties from DB or config -->
                <option value="1">Registered Nurse (RN)</option>
                <option value="2">Licensed Practical Nurse (LPN)</option>
                <option value="3">Certified Nursing Assistant (CNA)</option>
                <option value="4">Medical Assistant (MA)</option>
            </select>
        </div>
        <div class="col-md-4 form-group d-flex align-items-end">
            <button id="applyFiltersBtn" class="btn btn-primary">Apply Filters</button>
        </div>
    </div>

    <div id="shiftsListContainer" class="dashboard-grid">
        <!-- Shifts will be loaded here by JavaScript -->
        <div class="loader-container text-center">
            <div class="loader"></div>
            <p>Loading open shifts...</p>
        </div>
    </div>

    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center" id="paginationControls">
            <!-- Pagination controls will be inserted here by JavaScript -->
        </ul>
    </nav>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const shiftsListContainer = document.getElementById("shiftsListContainer");
    const paginationControls = document.getElementById("paginationControls");
    const filterDate = document.getElementById("filterDate");
    const filterSpecialty = document.getElementById("filterSpecialty");
    const applyFiltersBtn = document.getElementById("applyFiltersBtn");

    let currentPage = 1;
    const shiftsPerPage = 10; // Or get from config

    async function loadShifts(page = 1, date = "", specialty = "") {
        shiftsListContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading open shifts...</p></div>`;
        
        let apiUrl = `<?php echo base_url("shift/listOpen"); ?>?page=${page}&limit=${shiftsPerPage}`;
        if (date) apiUrl += `&date=${date}`;
        if (specialty) apiUrl += `&specialty_id=${specialty}`;

        try {
            const response = await fetchData(apiUrl);
            shiftsListContainer.innerHTML = ""; // Clear loader/previous content

            if (response.status === "success" && response.data && response.data.shifts && response.data.shifts.length > 0) {
                response.data.shifts.forEach(shift => {
                    const shiftCard = `
                        <div class="card shift-list-item">
                            <div class="card-body">
                                <h5 class="shift-title">${sanitizeHTML(shift.shift_title)}</h5>
                                <p class="shift-meta">
                                    <strong>Facility:</strong> ${sanitizeHTML(shift.facility_name || "N/A")} <br>
                                    <strong>Location:</strong> ${sanitizeHTML(shift.location || "N/A")} <br>
                                    <strong>Date:</strong> ${new Date(shift.shift_date).toLocaleDateString()} <br>
                                    <strong>Time:</strong> ${sanitizeHTML(shift.start_time)} - ${sanitizeHTML(shift.end_time)}
                                </p>
                                <p class="shift-description">${sanitizeHTML(shift.description ? shift.description.substring(0, 100) + "..." : "No description available.")}</p>
                                <a href="<?php echo base_url("shift/view/"); ?>${shift.id}" class="btn btn-sm btn-primary">View Details & Apply</a>
                            </div>
                        </div>
                    `;
                    shiftsListContainer.insertAdjacentHTML("beforeend", shiftCard);
                });
                renderPagination(response.data.total_shifts, page, shiftsPerPage);
            } else {
                shiftsListContainer.innerHTML = "<p class=\"text-center\">No open shifts found matching your criteria.</p>";
                paginationControls.innerHTML = ""; // Clear pagination if no results
            }
        } catch (error) {
            console.error("Error fetching shifts:", error);
            shiftsListContainer.innerHTML = "<p class=\"text-center text-danger\">Could not load shifts. Please try again later.</p>";
            paginationControls.innerHTML = "";
        }
    }

    function renderPagination(totalItems, currentPage, itemsPerPage) {
        paginationControls.innerHTML = "";
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (totalPages <= 1) return;

        // Previous Button
        const prevLi = document.createElement("li");
        prevLi.className = `page-item ${currentPage === 1 ? "disabled" : ""}`;
        const prevLink = document.createElement("a");
        prevLink.className = "page-link";
        prevLink.href = "#";
        prevLink.textContent = "Previous";
        prevLink.addEventListener("click", (e) => { e.preventDefault(); if (currentPage > 1) loadShifts(currentPage - 1, filterDate.value, filterSpecialty.value); });
        prevLi.appendChild(prevLink);
        paginationControls.appendChild(prevLi);

        // Page Number Links (simplified for brevity)
        for (let i = 1; i <= totalPages; i++) {
            const pageLi = document.createElement("li");
            pageLi.className = `page-item ${i === currentPage ? "active" : ""}`;
            const pageLink = document.createElement("a");
            pageLink.className = "page-link";
            pageLink.href = "#";
            pageLink.textContent = i;
            pageLink.addEventListener("click", (e) => { e.preventDefault(); loadShifts(i, filterDate.value, filterSpecialty.value); });
            pageLi.appendChild(pageLink);
            paginationControls.appendChild(pageLi);
        }

        // Next Button
        const nextLi = document.createElement("li");
        nextLi.className = `page-item ${currentPage === totalPages ? "disabled" : ""}`;
        const nextLink = document.createElement("a");
        nextLink.className = "page-link";
        nextLink.href = "#";
        nextLink.textContent = "Next";
        nextLink.addEventListener("click", (e) => { e.preventDefault(); if (currentPage < totalPages) loadShifts(currentPage + 1, filterDate.value, filterSpecialty.value); });
        nextLi.appendChild(nextLink);
        paginationControls.appendChild(nextLi);
    }

    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener("click", function() {
            currentPage = 1; // Reset to first page on new filter
            loadShifts(currentPage, filterDate.value, filterSpecialty.value);
        });
    }

    // Initial load
    loadShifts(currentPage);

    function sanitizeHTML(str) {
        if (!str) return "";
        const temp = document.createElement("div");
        temp.textContent = str;
        return temp.innerHTML;
    }
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
