<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Departments</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
  <h1 class="text-2xl font-bold mb-4">Departments</h1>

  <!-- Add/Edit Department Form -->
  <form id="departmentForm" class="grid grid-cols-1 gap-2 mb-4">
    <input type="hidden" id="departmentId"> <!-- hidden field for edit -->
    <input type="text" id="departmentName" placeholder="Department Name" class="border p-2 rounded" required />
    <button type="submit" id="submitBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Department</button>
  </form>

  <!-- Departments Table -->
  <table class="w-full border-collapse border border-gray-300">
    <thead class="bg-gray-200">
      <tr>
        <th class="border p-2">ID</th>
        <th class="border p-2">Name</th>
        <th class="border p-2">Actions</th>
      </tr>
    </thead>
    <tbody id="departmentsTable"></tbody>
  </table>
</div>

<script>
const baseUrl = "http://127.0.0.1:8000/api/departments";
const form = document.getElementById("departmentForm");
const table = document.getElementById("departmentsTable");
const departmentIdField = document.getElementById("departmentId");
const nameField = document.getElementById("departmentName");
const submitBtn = document.getElementById("submitBtn");

// Fetch and display departments
async function fetchDepartments() {
  const res = await fetch(baseUrl);
  const data = await res.json();
  table.innerHTML = data.map(d => `
    <tr>
      <td class="border p-2">${d.id}</td>
      <td class="border p-2">${d.name}</td>
      <td class="border p-2 space-x-2">
        <button onclick="editDepartment(${d.id})" class="bg-yellow-400 px-2 py-1 rounded">Edit</button>
        <button onclick="deleteDepartment(${d.id})" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
      </td>
    </tr>
  `).join('');
}

// Populate form for edit
async function editDepartment(id) {
  const res = await fetch(`${baseUrl}/${id}`);
  const dept = await res.json();

  departmentIdField.value = dept.id;
  nameField.value = dept.name;
  submitBtn.textContent = "Update Department";
}

// Delete department
async function deleteDepartment(id) {
  await fetch(`${baseUrl}/${id}`, { method: "DELETE" });
  fetchDepartments();
}

// Add or update department
form.addEventListener("submit", async e => {
  e.preventDefault();

  const id = departmentIdField.value;
  const body = { name: nameField.value };

  if(id) {
    // Update
    await fetch(`${baseUrl}/${id}`, { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) });
    submitBtn.textContent = "Add Department";
  } else {
    // Add
    await fetch(baseUrl, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) });
  }

  form.reset();
  departmentIdField.value = "";
  fetchDepartments();
});

// Initialize
fetchDepartments();
</script>

</body>
</html>
