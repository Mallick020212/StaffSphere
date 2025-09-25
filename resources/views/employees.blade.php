<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employees</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-6xl mx-auto bg-white shadow-md rounded-lg p-6">
  <h1 class="text-2xl font-bold mb-4">Employees Management</h1>

  <!-- Add/Edit Employee Form -->
  <form id="employeeForm" class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
    <input type="hidden" id="employeeId">
    <input type="text" id="firstName" placeholder="First Name" class="border p-2 rounded" required />
    <input type="text" id="lastName" placeholder="Last Name" class="border p-2 rounded" required />
    <input type="email" id="email" placeholder="Email" class="border p-2 rounded" required />

    <!-- Department Dropdown -->
    <select id="departmentId" class="border p-2 rounded" required>
      <option value="">Select Department</option>
    </select>

    <input type="text" id="contacts" placeholder="Contacts (comma separated)" class="border p-2 rounded" />
    <input type="text" id="address" placeholder="Address Line" class="border p-2 rounded" />

    <button type="submit" id="submitBtn" class="col-span-1 sm:col-span-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Employee</button>
  </form>

  <!-- Filter by Employee Dropdown -->
  <div class="flex items-center gap-2 mb-2">
    <select id="filterEmployee" class="border p-2 rounded w-full sm:w-1/2">
      <option value="">Filter by Employee</option>
    </select>
    <button id="filterBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filter</button>
    <button id="clearBtn" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Clear</button>
  </div>

  <!-- Employees Table -->
  <table class="w-full border-collapse border border-gray-300">
    <thead class="bg-gray-200">
      <tr>
        <th class="border p-2">ID</th>
        <th class="border p-2">Name</th>
        <th class="border p-2">Email</th>
        <th class="border p-2">Department</th>
        <th class="border p-2">Contacts</th>
        <th class="border p-2">Address</th>
        <th class="border p-2">Actions</th>
      </tr>
    </thead>
    <tbody id="employeesTable"></tbody>
  </table>
</div>

<script>
const baseUrl = "http://127.0.0.1:8000/api/employees";
const deptUrl = "http://127.0.0.1:8000/api/departments";

const form = document.getElementById("employeeForm");
const table = document.getElementById("employeesTable");
const deptSelect = document.getElementById("departmentId");
const submitBtn = document.getElementById("submitBtn");
const employeeIdField = document.getElementById("employeeId");
const filterEmployee = document.getElementById("filterEmployee");
const filterBtn = document.getElementById("filterBtn");
const clearBtn = document.getElementById("clearBtn");

// Load departments into dropdown
async function loadDepartments() {
  const res = await fetch(deptUrl);
  const depts = await res.json();
  deptSelect.innerHTML = '<option value="">Select Department</option>' + 
    depts.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
}

// Load employees into filter dropdown
async function loadFilterDropdown() {
  const res = await fetch(baseUrl);
  const employees = await res.json();
  filterEmployee.innerHTML = '<option value="">Filter by Employee</option>' +
    employees.map(emp => `<option value="${emp.id}">${emp.first_name} ${emp.last_name}</option>`).join('');
}

// Fetch employees and display in table
async function fetchEmployees(employeeId = "") {
  let url = baseUrl;
  if(employeeId) {
    url = `${baseUrl}/search?query=${encodeURIComponent(employeeId)}`;
  }
  const res = await fetch(url);
  const data = await res.json();

  if(data.length === 0) {
    table.innerHTML = `<tr><td colspan="7" class="border p-2 text-center">No employees found</td></tr>`;
    return;
  }

  table.innerHTML = data.map(emp => `
    <tr>
      <td class="border p-2">${emp.id}</td>
      <td class="border p-2">${emp.first_name} ${emp.last_name}</td>
      <td class="border p-2">${emp.email}</td>
      <td class="border p-2">${emp.department.name}</td>
      <td class="border p-2">${emp.contacts.map(c=>c.phone_number).join(", ")}</td>
      <td class="border p-2">${emp.addresses.map(a=>a.address_line).join(", ")}</td>
      <td class="border p-2 space-x-2">
        <button onclick="editEmployee(${emp.id})" class="bg-yellow-400 px-2 py-1 rounded">Edit</button>
        <button onclick="deleteEmployee(${emp.id})" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
      </td>
    </tr>
  `).join('');
}

// Edit employee
async function editEmployee(id) {
  const res = await fetch(`${baseUrl}/${id}`);
  const emp = await res.json();

  employeeIdField.value = emp.id;
  document.getElementById("firstName").value = emp.first_name;
  document.getElementById("lastName").value = emp.last_name;
  document.getElementById("email").value = emp.email;
  deptSelect.value = emp.department_id;
  document.getElementById("contacts").value = emp.contacts.map(c => c.phone_number).join(", ");
  document.getElementById("address").value = emp.addresses.map(a => a.address_line).join(", ");

  submitBtn.textContent = "Update Employee";
}

// Delete employee
async function deleteEmployee(id) {
  if(confirm("Are you sure you want to delete this employee?")) {
    await fetch(`${baseUrl}/${id}`, { method: "DELETE" });
    fetchEmployees();
    loadFilterDropdown();
  }
}

// Add or update employee
form.addEventListener("submit", async e => {
  e.preventDefault();
  const id = employeeIdField.value;
  const body = {
    first_name: document.getElementById("firstName").value,
    last_name: document.getElementById("lastName").value,
    email: document.getElementById("email").value,
    department_id: parseInt(deptSelect.value),
    contacts: document.getElementById("contacts").value.split(",").map(c => c.trim()),
    addresses: [{ address_line: document.getElementById("address").value }]
  };

  if(id) {
    await fetch(`${baseUrl}/${id}`, { method: "PUT", headers: {"Content-Type":"application/json"}, body: JSON.stringify(body) });
    submitBtn.textContent = "Add Employee";
  } else {
    await fetch(baseUrl, { method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify(body) });
  }

  form.reset();
  employeeIdField.value = "";
  fetchEmployees();
  loadFilterDropdown();
});

// Filter button
filterBtn.addEventListener("click", () => {
  const selectedId = filterEmployee.value;
  fetchEmployees(selectedId);
});

// Clear button
clearBtn.addEventListener("click", () => {
  filterEmployee.value = "";
  fetchEmployees();
});

// Initialize
loadDepartments();
fetchEmployees();
loadFilterDropdown();
</script>

</body>
</html>
